<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $kategori = [
            ['nama_kategori' => 'Tenda',            'deskripsi' => 'Tenda untuk acara indoor maupun outdoor'],
            ['nama_kategori' => 'Kursi dan Meja',   'deskripsi' => 'Kursi, meja bulat, dan meja prasmanan'],
            ['nama_kategori' => 'Sound System',     'deskripsi' => 'Speaker, mixer, dan mikrofon'],
            ['nama_kategori' => 'Dekorasi',         'deskripsi' => 'Backdrop, karpet, dan hiasan acara'],
            ['nama_kategori' => 'Peralatan Makan',  'deskripsi' => 'Piring, gelas, dan perlengkapan katering'],
        ];

        foreach ($kategori as $data) {
            Category::updateOrCreate(
                ['nama_kategori' => $data['nama_kategori']],
                ['deskripsi' => $data['deskripsi']]
            );
        }
    }
}