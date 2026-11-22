<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRentalRequest;
use App\Models\Customer;
use App\Models\Item;
use App\Models\Rental;
use App\Models\User;
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

        return view('rentals.index', compact('rentals'));
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
}