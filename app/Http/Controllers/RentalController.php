<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRentalRequest;
use App\Http\Requests\ReturnRentalRequest;
use App\Models\Customer;
use App\Models\Item;
use App\Models\Rental;
use App\Models\User;
use App\Models\RentalItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RentalController extends Controller
{
        public function index()
        {
            $rentals = Rental::with(['customer', 'user'])
                ->orderByDesc('id')
                ->paginate(10);

            // Jumlah semua sewa terlambat (bukan hanya di halaman ini)
            $jumlahTerlambat = Rental::where('status', 'disewa')
                ->where('tanggal_kembali', '<', Carbon::today()->toDateString())
                ->count();

            return view('rentals.index', compact('rentals', 'jumlahTerlambat'));
        }

    public function create()
    {
        $customers = Customer::orderBy('nama')->get();
        $items = Item::with('category')
            ->where('stok', '>', 0)
            ->orderBy('nama_barang')
            ->get();

        return view('rentals.create', compact('customers', 'items'));
    }

    public function store(StoreRentalRequest $request)
    {
        $validated = $request->validated();

        // Ambil hanya barang dengan jumlah > 0 (kunci = id barang, nilai = qty)
        $qtyPerItem = collect($validated['items'])
            ->map(fn ($qty) => (int) $qty)
            ->filter(fn ($qty) => $qty > 0);

        if ($qtyPerItem->isEmpty()) {
            throw ValidationException::withMessages([
                'items' => 'Isi jumlah minimal satu barang lebih dari 0.',
            ]);
        }

        // Jumlah hari = selisih tanggal, minimal 1
        $jumlahHari = max(1, (int) Carbon::parse($validated['tanggal_sewa'])
            ->diffInDays(Carbon::parse($validated['tanggal_kembali'])));

        DB::transaction(function () use ($validated, $qtyPerItem, $jumlahHari) {
            // Baca stok terbaru dari database
            $items = Item::whereIn('id', $qtyPerItem->keys()->all())->get()->keyBy('id');

            $baris = [];
            $totalSewa = 0;

            foreach ($qtyPerItem as $itemId => $qty) {
                $item = $items->get($itemId);

                if (! $item) {
                    throw ValidationException::withMessages([
                        "items.$itemId" => 'Barang tidak ditemukan.',
                    ]);
                }

                if ($qty > $item->stok) {
                    throw ValidationException::withMessages([
                        "items.$itemId" => "Stok {$item->nama_barang} tidak cukup (tersedia {$item->stok}).",
                    ]);
                }

                $subtotal = $qty * $item->harga_sewa * $jumlahHari;
                $totalSewa += $subtotal;

                $baris[] = [
                    'item'         => $item,
                    'qty'          => $qty,
                    'harga_satuan' => $item->harga_sewa,
                    'subtotal'     => $subtotal,
                ];
            }

            if ($validated['dp'] > $totalSewa) {
                throw ValidationException::withMessages([
                    'dp' => 'DP tidak boleh melebihi total sewa (Rp ' . number_format($totalSewa, 0, ',', '.') . ').',
                ]);
            }

            // 1. Simpan kepala nota
            $rental = Rental::create([
                'customer_id'     => $validated['customer_id'],
                'user_id'         => User::query()->value('id'), // SEMENTARA, jadi Auth::id() di Tahap 6
                'tanggal_sewa'    => $validated['tanggal_sewa'],
                'tanggal_kembali' => $validated['tanggal_kembali'],
                'jaminan_jenis'   => $validated['jaminan_jenis'],
                'total_sewa'      => $totalSewa,
                'dp'              => $validated['dp'],
            ]);

            foreach ($baris as $b) {
                // 2. Simpan isi nota
                $rental->rentalItems()->create([
                    'item_id'      => $b['item']->id,
                    'qty'          => $b['qty'],
                    'harga_satuan' => $b['harga_satuan'],
                    'subtotal'     => $b['subtotal'],
                ]);

                // 3. Kurangi stok
                $b['item']->decrement('stok', $b['qty']);
            }
        });

        return redirect()->route('rentals.index')
            ->with('success', 'Sewa berhasil disimpan.');
    }

        public function show($id)
    {
        $rental = Rental::with(['customer', 'user', 'rentalItems.item'])
            ->findOrFail($id);

        $jumlahHari = max(1, (int) Carbon::parse($rental->tanggal_sewa)
            ->diffInDays(Carbon::parse($rental->tanggal_kembali)));

        return view('rentals.show', compact('rental', 'jumlahHari'));
    }
    
    public function formKembali($id)
    {
        $rental = Rental::with(['customer', 'rentalItems.item'])->findOrFail($id);

        if ($rental->status === 'dikembalikan') {
            return redirect()->route('rentals.show', $rental->id)
                ->with('error', 'Sewa ini sudah dikembalikan.');
        }

        // Hari telat = hari ini - tanggal kembali, minimal 0
        $hariTelat = max(0, (int) Carbon::parse($rental->tanggal_kembali)
            ->diffInDays(Carbon::today(), false));

        $denda = $rental->hitungDenda($hariTelat);

        return view('rentals.return', compact('rental', 'hariTelat', 'denda'));
    }

        public function kembalikan(ReturnRentalRequest $request, $id)
    {
        $input = $request->validated()['items'];

        DB::transaction(function () use ($input, $id) {
            // Kunci baris sewa supaya klik ganda tidak diproses dua kali
            $rental = Rental::with('rentalItems.item')->lockForUpdate()->findOrFail($id);

            if ($rental->status === 'dikembalikan') {
                throw ValidationException::withMessages([
                    'status' => 'Sewa ini sudah dikembalikan.',
                ]);
            }

            // Hari telat dan denda
            $hariTelat = max(0, (int) Carbon::parse($rental->tanggal_kembali)
                ->diffInDays(Carbon::today(), false));
            $denda = $rental->hitungDenda($hariTelat);

            $totalGantiRugi = 0;

            foreach ($rental->rentalItems as $ri) {
                $baris = $input[$ri->id] ?? null;

                if (! $baris) {
                    throw ValidationException::withMessages([
                        'items' => 'Data barang tidak lengkap.',
                    ]);
                }

                $rusak   = (int) $baris['qty_rusak'];
                $hilang  = (int) $baris['qty_hilang'];
                $nominal = (int) $baris['nominal_rusak'];
                $nama    = $ri->item->nama_barang;

                if ($rusak + $hilang > $ri->qty) {
                    throw ValidationException::withMessages([
                        "items.{$ri->id}" => "{$nama}: jumlah rusak + hilang melebihi jumlah disewa ({$ri->qty}).",
                    ]);
                }

                if ($rusak === 0 && $nominal > 0) {
                    throw ValidationException::withMessages([
                        "items.{$ri->id}" => "{$nama}: nominal kerusakan diisi, tapi jumlah rusak 0.",
                    ]);
                }

                $bagus          = $ri->qty - $rusak - $hilang;
                $biayaHilang    = $hilang * $ri->item->harga_ganti;
                $biayaKerusakan = $nominal + $biayaHilang;
                $totalGantiRugi += $biayaKerusakan;

                // Catat kondisi barang di nota
                $ri->update([
                    'qty_rusak'       => $rusak,
                    'qty_hilang'      => $hilang,
                    'biaya_kerusakan' => $biayaKerusakan,
                ]);

                // Kembalikan stok: bagus ke stok, rusak ke stok_rusak, hilang ke stok_hilang
                $ri->item->increment('stok', $bagus);
                $ri->item->increment('stok_rusak', $rusak);
                $ri->item->increment('stok_hilang', $hilang);
            }

            // Tagihan akhir dan tutup sewa
            $rental->update([
                'tanggal_dikembalikan' => Carbon::today()->toDateString(),
                'denda'                => $denda,
                'total_ganti_rugi'     => $totalGantiRugi,
                'sisa_bayar'           => $rental->total_sewa + $denda + $totalGantiRugi - $rental->dp,
                'status'               => 'dikembalikan',
            ]);
        });

        return redirect()->route('rentals.show', $id)
            ->with('success', 'Pengembalian berhasil disimpan.');
    }

    public function laporanKerusakan()
    {
        // Baris nota yang ada barang rusak atau hilang
        $adaKerusakan = function ($query) {
            $query->where('qty_rusak', '>', 0)->orWhere('qty_hilang', '>', 0);
        };

        $rows = RentalItem::with(['rental.customer', 'item'])
            ->where($adaKerusakan)
            ->orderByDesc('rental_id')
            ->paginate(10);

        // Ringkasan seluruh data, bukan hanya halaman ini
        $ringkasan = RentalItem::where($adaKerusakan)
            ->selectRaw('COALESCE(SUM(qty_rusak), 0) as total_rusak, COALESCE(SUM(qty_hilang), 0) as total_hilang, COALESCE(SUM(biaya_kerusakan), 0) as total_biaya')
            ->first();

        return view('rentals.damage-report', compact('rows', 'ringkasan'));
    }
}