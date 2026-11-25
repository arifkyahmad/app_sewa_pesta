<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Item;
use Illuminate\Database\Seeder;

class ItemSeeder extends Seeder
{
    public function run(): void
    {
        // [nama_barang, harga_sewa per hari, harga_ganti per unit, stok]
        $daftar = [
            'Tenda' => [
                ['Tenda Sarnafil 3x3',  150000, 1500000, 10],
                ['Tenda Sarnafil 4x6',  300000, 3000000, 6],
                ['Tenda Kerucut',       500000, 5000000, 3],
            ],
            'Kursi dan Meja' => [
                ['Kursi Futura',        2000,   50000,   200],
                ['Kursi Lipat Besi',    1500,   40000,   100],
                ['Meja Bulat',          15000,  350000,  20],
                ['Meja Prasmanan',      25000,  500000,  10],
            ],
            'Sound System' => [
                ['Sound System Set Kecil', 350000, 4000000, 4],
                ['Speaker Aktif 15 Inci',  150000, 2500000, 6],
                ['Mic Wireless',           50000,  800000,  8],
            ],
            'Dekorasi' => [
                ['Backdrop Pelaminan',  750000, 5000000, 3],
                ['Karpet Merah',        100000, 1000000, 5],
                ['Standing Flower',     50000,  600000,  10],
            ],
            'Peralatan Makan' => [
                ['Piring Makan',        1000,   25000,   300],
                ['Gelas Kaca',          500,    15000,   300],
                ['Chafing Dish',        25000,  600000,  15],
            ],
        ];

        foreach ($daftar as $namaKategori => $barang) {
            $categoryId = Category::where('nama_kategori', $namaKategori)->value('id');

            foreach ($barang as [$nama, $hargaSewa, $hargaGanti, $stok]) {
                Item::updateOrCreate(
                    ['nama_barang' => $nama],
                    [
                        'category_id' => $categoryId,
                        'harga_sewa' => $hargaSewa,
                        'harga_ganti' => $hargaGanti,
                        'stok' => $stok,
                        'stok_rusak' => 0,
                        'stok_hilang' => 0,
                    ]
                );
            }
        }
    }
}