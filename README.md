# Sewa Perlengkapan Pesta/Event

Aplikasi web untuk mengelola penyewaan perlengkapan pesta dan event (tenda, kursi, sound system, dekorasi, peralatan makan). Final Project Pemrograman Framework (Laravel 12).

## Fitur

- Login petugas/admin (session, tanpa starter kit) dengan pembagian role
- CRUD Kategori, Barang, dan Pelanggan
- Transaksi sewa: banyak barang per nota, total dihitung otomatis, stok berkurang otomatis
- Pengembalian per barang (bagus / rusak / hilang): denda keterlambatan, ganti rugi, stok kembali otomatis
- Notifikasi sewa terlambat (banner, badge, tombol WhatsApp ke pelanggan)
- Laporan Kerusakan (barang rusak/hilang per nota dan pelanggan)
- Dashboard statistik yang mengonsumsi API internal
- REST API (8 endpoint), dokumentasi di [API.md](API.md)

## Teknologi

Laravel 12, PHP >= 8.2, PostgreSQL, Blade + CSS biasa. Tanpa starter kit, Livewire, atau package tambahan.

## Struktur Database

6 tabel: `users`, `categories`, `items`, `customers`, `rentals`, `rental_items`.
Transaksi memakai pola header-detail: `rentals` (nota) + `rental_items` (barang yang disewa).

## Hak Akses

| Role | Akses |
|---|---|
| admin | Semua fitur, termasuk kelola Kategori dan tambah/ubah/hapus Barang |
| petugas | Pelanggan, Sewa, Pengembalian, Laporan Kerusakan, lihat daftar dan detail Barang |

Pelanggan tidak punya akun. Petugas yang membuka halaman khusus admin akan mendapat 403.

## Menjalankan Aplikasi (WAJIB 2 SERVER)

Dashboard memanggil API milik aplikasi sendiri. `php artisan serve` hanya melayani satu request dalam satu waktu, jadi diperlukan server kedua agar tidak deadlock. Buka dua terminal:

```
php artisan serve --port=8000
php artisan serve --port=8011
```

- Aplikasi: http://127.0.0.1:8000
- API internal: http://127.0.0.1:8011/api

Gunakan `127.0.0.1` di semua tempat, jangan dicampur dengan `localhost`.
Jika server 8011 mati, halaman lain tetap normal, tetapi dashboard menampilkan angka 0 dan banner peringatan.

## Akun Demo

Password semua akun: `password`

| Email | Role |
|---|---|
| admin@sewapesta.test | admin |
| petugas1@sewapesta.test | petugas |
| petugas2@sewapesta.test | petugas |

## Data Demo

`migrate:fresh --seed` mengisi 3 akun, 5 kategori, 16 barang, 12 pelanggan, dan 5 sewa contoh (sewa berjalan, sewa terlambat, dikembalikan bersih, dikembalikan telat dengan barang rusak dan hilang).

## Aturan Bisnis Singkat

- Lama sewa = tanggal kembali - tanggal sewa (minimal 1 hari)
- Subtotal = qty x harga satuan x lama sewa
- Denda = 50% x (total sewa / lama sewa) x hari telat
- Barang hilang = qty hilang x harga ganti; barang rusak = nominal diisi petugas
- Sisa bayar = total sewa + denda + ganti rugi - DP

## Dokumentasi API

Lihat [API.md](API.md).

## Troubleshooting

- **419 Page Expired**: terjadi setelah `migrate:fresh` karena sesi terhapus. Tekan Ctrl+F5 di halaman login lalu login ulang.
- **Dashboard menampilkan angka 0**: pastikan server port 8011 berjalan dan `INTERNAL_API_URL` di `.env` benar. Jalankan `php artisan config:clear` setelah mengubah `.env`.