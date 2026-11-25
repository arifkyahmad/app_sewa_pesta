<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Item;
use App\Models\Rental;
use App\Models\RentalItem;
use App\Models\User;
use Illuminate\Database\Seeder;

class RentalSeeder extends Seeder
{
    public function run(): void
    {
        $pelanggan = Customer::orderBy('id')->take(5)->get();
        $petugas1 = User::where('email', 'petugas1@sewapesta.test')->firstOrFail();
        $petugas2 = User::where('email', 'petugas2@sewapesta.test')->firstOrFail();

        // $hari(-3) = 3 hari lalu, $hari(2) = 2 hari lagi
        $hari = fn (int $n) => now()->startOfDay()->addDays($n);

        // 1. Masih disewa (belum jatuh tempo)
        $this->buatSewa([
            'customer_id' => $pelanggan[0]->id,
            'user_id' => $petugas1->id,
            'sewa' => $hari(-1),
            'kembali' => $hari(2),
            'dikembalikan' => null,
            'jaminan' => 'KTP',
            'dp' => 500000,
            'barang' => [
                ['nama' => 'Tenda Sarnafil 3x3', 'qty' => 2],
                ['nama' => 'Kursi Futura', 'qty' => 50],
            ],
        ]);

        // 2. Masih disewa (baru mulai hari ini)
        $this->buatSewa([
            'customer_id' => $pelanggan[1]->id,
            'user_id' => $petugas2->id,
            'sewa' => $hari(0),
            'kembali' => $hari(1),
            'dikembalikan' => null,
            'jaminan' => 'SIM',
            'dp' => 200000,
            'barang' => [
                ['nama' => 'Sound System Set Kecil', 'qty' => 1],
                ['nama' => 'Mic Wireless', 'qty' => 2],
            ],
        ]);

        // 3. Terlambat (belum dikembalikan, jatuh tempo 2 hari lalu)
        $this->buatSewa([
            'customer_id' => $pelanggan[2]->id,
            'user_id' => $petugas1->id,
            'sewa' => $hari(-5),
            'kembali' => $hari(-2),
            'dikembalikan' => null,
            'jaminan' => 'KTP',
            'dp' => 300000,
            'barang' => [
                ['nama' => 'Meja Bulat', 'qty' => 10],
                ['nama' => 'Kursi Lipat Besi', 'qty' => 40],
            ],
        ]);

        // 4. Dikembalikan bersih (tepat waktu, tanpa kerusakan)
        $this->buatSewa([
            'customer_id' => $pelanggan[3]->id,
            'user_id' => $petugas2->id,
            'sewa' => $hari(-12),
            'kembali' => $hari(-10),
            'dikembalikan' => $hari(-10),
            'jaminan' => 'lainnya',
            'dp' => 1000000,
            'barang' => [
                ['nama' => 'Backdrop Pelaminan', 'qty' => 1],
                ['nama' => 'Karpet Merah', 'qty' => 2],
            ],
        ]);

        // 5. Dikembalikan telat 2 hari + 1 chafing dish rusak + 3 gelas hilang
        $this->buatSewa([
            'customer_id' => $pelanggan[4]->id,
            'user_id' => $petugas1->id,
            'sewa' => $hari(-9),
            'kembali' => $hari(-7),
            'dikembalikan' => $hari(-5),
            'jaminan' => 'SIM',
            'dp' => 200000,
            'barang' => [
                ['nama' => 'Piring Makan', 'qty' => 100],
                ['nama' => 'Gelas Kaca', 'qty' => 100, 'hilang' => 3],
                ['nama' => 'Chafing Dish', 'qty' => 5, 'rusak' => 1, 'nominal_rusak' => 150000],
            ],
        ]);
    }

    private function buatSewa(array $d): void
    {
        $lama = max(1, (int) $d['sewa']->diffInDays($d['kembali'], true));
        $kembali = $d['dikembalikan'] !== null;

        // Hitung tiap baris barang
        $baris = [];
        $totalSewa = 0;
        $totalGantiRugi = 0;

        foreach ($d['barang'] as $b) {
            $item = Item::where('nama_barang', $b['nama'])->firstOrFail();
            $qty = $b['qty'];
            $rusak = $kembali ? ($b['rusak'] ?? 0) : 0;
            $hilang = $kembali ? ($b['hilang'] ?? 0) : 0;
            $nominalRusak = $kembali ? ($b['nominal_rusak'] ?? 0) : 0;

            $subtotal = $qty * $item->harga_sewa * $lama;
            $biayaKerusakan = $kembali ? $nominalRusak + ($hilang * $item->harga_ganti) : 0;

            $totalSewa += $subtotal;
            $totalGantiRugi += $biayaKerusakan;

            $baris[] = [
                'item' => $item,
                'qty' => $qty,
                'harga_satuan' => $item->harga_sewa,
                'subtotal' => $subtotal,
                'qty_rusak' => $rusak,
                'qty_hilang' => $hilang,
                'biaya_kerusakan' => $biayaKerusakan,
            ];
        }

        // Denda dan sisa bayar hanya terisi kalau sudah dikembalikan
        $denda = 0;
        $sisaBayar = 0;

        if ($kembali) {
            $hariTelat = max(0, (int) $d['kembali']->diffInDays($d['dikembalikan'], false));
            $denda = (int) round(0.5 * ($totalSewa / $lama) * $hariTelat);
            $sisaBayar = $totalSewa + $denda + $totalGantiRugi - $d['dp'];
        }

        $sewa = Rental::create([
            'customer_id' => $d['customer_id'],
            'user_id' => $d['user_id'],
            'tanggal_sewa' => $d['sewa']->toDateString(),
            'tanggal_kembali' => $d['kembali']->toDateString(),
            'tanggal_dikembalikan' => $kembali ? $d['dikembalikan']->toDateString() : null,
            'jaminan_jenis' => $d['jaminan'],
            'total_sewa' => $totalSewa,
            'dp' => $d['dp'],
            'denda' => $denda,
            'total_ganti_rugi' => $totalGantiRugi,
            'sisa_bayar' => $sisaBayar,
            'status' => $kembali ? 'dikembalikan' : 'disewa',
        ]);

        foreach ($baris as $r) {
            RentalItem::create([
                'rental_id' => $sewa->id,
                'item_id' => $r['item']->id,
                'qty' => $r['qty'],
                'harga_satuan' => $r['harga_satuan'],
                'subtotal' => $r['subtotal'],
                'qty_rusak' => $r['qty_rusak'],
                'qty_hilang' => $r['qty_hilang'],
                'biaya_kerusakan' => $r['biaya_kerusakan'],
            ]);

            // Stok keluar saat disewa
            $r['item']->decrement('stok', $r['qty']);

            // Stok balik saat dikembalikan: bagus ke stok, rusak dan hilang dicatat terpisah
            if ($kembali) {
                $bagus = $r['qty'] - $r['qty_rusak'] - $r['qty_hilang'];
                $r['item']->increment('stok', $bagus);
                $r['item']->increment('stok_rusak', $r['qty_rusak']);
                $r['item']->increment('stok_hilang', $r['qty_hilang']);
            }
        }
    }
}