<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class Rental extends Model
{
    // Persentase denda per hari telat, dari sewa per hari
    public const PERSEN_DENDA = 50;

    protected $fillable = [
        'customer_id',
        'user_id',
        'tanggal_sewa',
        'tanggal_kembali',
        'tanggal_dikembalikan',
        'jaminan_jenis',
        'total_sewa',
        'dp',
        'denda',
        'total_ganti_rugi',
        'sisa_bayar',
        'status',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function rentalItems()
    {
        return $this->hasMany(RentalItem::class);
    }

    // Buat sewa baru: simpan nota + isi nota + kurangi stok (satu transaksi)
    public static function buatSewa(array $validated, int $userId): self
    {
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

        return DB::transaction(function () use ($validated, $qtyPerItem, $jumlahHari, $userId) {
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
            $rental = self::create([
                'customer_id'     => $validated['customer_id'],
                'user_id'         => $userId,
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

            return $rental;
        });
    }

    // Proses pengembalian: denda, ganti rugi, stok kembali, tutup sewa (satu transaksi)
    // $input: [id_rental_item => ['qty_rusak' => .., 'qty_hilang' => .., 'nominal_rusak' => ..]]
    public static function prosesPengembalian($id, array $input): self
    {
        return DB::transaction(function () use ($id, $input) {
            // Kunci baris sewa supaya klik ganda tidak diproses dua kali
            $rental = self::with('rentalItems.item')->lockForUpdate()->findOrFail($id);

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

            return $rental;
        });
    }
    
    // Lama sewa dalam hari, minimal 1
    public function lamaSewa(): int
    {
        return max(1, (int) Carbon::parse($this->tanggal_sewa)
            ->diffInDays(Carbon::parse($this->tanggal_kembali)));
    }

    // Denda = persen x sewa per hari x hari telat
    public function hitungDenda(int $hariTelat): int
    {
        $sewaPerHari = intdiv($this->total_sewa, $this->lamaSewa());

        return intdiv($sewaPerHari * self::PERSEN_DENDA * $hariTelat, 100);
    }

    // Hari telat: dihitung sampai tanggal dikembalikan, atau sampai hari ini kalau belum dikembalikan
    public function hariTelat(): int
    {
        $akhir = $this->tanggal_dikembalikan
            ? Carbon::parse($this->tanggal_dikembalikan)
            : Carbon::today();

        return max(0, (int) Carbon::parse($this->tanggal_kembali)->diffInDays($akhir, false));
    }

    // Terlambat = belum dikembalikan dan sudah lewat tanggal janji
    public function isTerlambat(): bool
    {
        return $this->status === 'disewa' && $this->hariTelat() > 0;
    }

    // Link WhatsApp ke pelanggan dengan pesan siap kirim
    public function linkWhatsapp(): string
    {
        $pesan = "Halo {$this->customer->nama}, sewa perlengkapan pesta Anda (nota #{$this->id}) "
            . "seharusnya dikembalikan pada {$this->tanggal_kembali}. "
            . "Saat ini sudah telat {$this->hariTelat()} hari. "
            . "Mohon segera dikembalikan. Terima kasih.";

        return 'https://wa.me/' . $this->customer->nomorWhatsapp() . '?text=' . rawurlencode($pesan);
    }
}