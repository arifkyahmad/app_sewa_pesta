# Sewa Pesta

Aplikasi web penyewaan perlengkapan pesta/event (tenda, kursi, sound system, dekorasi). Dibangun dengan Laravel 12 dan PostgreSQL sebagai Final Project mata kuliah Pemrograman Framework.

## Fitur (rencana)

- Autentikasi petugas/admin dengan role (admin dan petugas)
- Data master: kategori, barang, pelanggan
- Transaksi sewa: banyak barang per sewa, perhitungan total, DP, stok otomatis
- Pengembalian: kondisi barang (bagus/rusak/hilang), denda keterlambatan, ganti rugi
- Laporan kerusakan barang
- REST API, dashboard, dan laporan

## Teknologi

- PHP >= 8.2
- Laravel 12
- PostgreSQL
- Composer, Node.js & NPM, Git

## Cara Menjalankan

1. Clone repository:
   ```
   git clone https://github.com/arifkyahmad/app_sewa_pesta.git
   cd app_sewa_pesta
   ```
2. Install dependency:
   ```
   composer install
   ```
3. Salin file environment:
   ```
   copy .env.example .env
   ```
   (Mac/Linux: `cp .env.example .env`)
4. Buat database `sewa_pesta` di PostgreSQL, lalu isi `.env`:
   ```
   DB_CONNECTION=pgsql
   DB_HOST=127.0.0.1
   DB_PORT=5432
   DB_DATABASE=sewa_pesta
   DB_USERNAME=postgres
   DB_PASSWORD=password_postgres_anda
   ```
5. Generate app key dan jalankan migration:
   ```
   php artisan key:generate
   php artisan migrate
   ```
6. Jalankan server:
   ```
   php artisan serve
   ```
   Buka `http://127.0.0.1:8000`.

## Alur Git

- `main`: versi stabil
- `dev`: pengembangan harian
- Format commit: `[P-X] deskripsi` (X = nomor tahap)