# Skenario Demo: Sewa Perlengkapan Pesta

Durasi: sekitar 15 menit (browser + Postman).

## Persiapan (sebelum demo)

- [ ] Dua server jalan di dua terminal:
  - `php artisan serve --port=8000`
  - `php artisan serve --port=8011`
- [ ] Data bersih: `php artisan migrate:fresh --seed`
- [ ] Login ulang setelah migrate (sesi terhapus). Jika 419: Ctrl+F5 di `/login`
- [ ] Buka http://127.0.0.1:8000 (bukan `localhost`)
- [ ] Postman siap, header `Accept: application/json`, base URL `http://127.0.0.1:8011/api`
- [ ] Branch `dev` berisi commit `[P-2]` sampai `[P-7]`, `README.md` dan `API.md` sudah ada

Akun (password `password`): `admin@sewapesta.test`, `petugas1@sewapesta.test`

## Bagian A: Browser

### 1. Login sebagai admin
- Login `admin@sewapesta.test`.
- Hasil: masuk ke Dashboard, navbar menampilkan nama + role.

### 2. Dashboard
- Tunjukkan 6 kartu dan tabel Sewa Terlambat.
- Hasil acuan (data seeder murni): Sewa Aktif 3, Sewa Terlambat 1, Sewa Selesai 2, Jenis Barang 16, Pelanggan 12, Pendapatan Rp 2.920.000.
- Jelaskan: angka diambil lewat API internal (`/api/stats`), makanya ada 2 server.

### 3. Data master (admin)
- Kategori: tambah satu kategori baru.
- Barang: tambah satu barang baru, pilih kategori. Tunjukkan field `stok`.
- Pelanggan: tambah satu pelanggan baru (nomor telepon hanya angka dan `+`).
- Tunjukkan validasi: kosongkan form, tampil pesan error bahasa Indonesia.
- Tunjukkan hapus data master yang masih dipakai ditolak (mis. hapus kategori yang punya barang).

### 4. Buat sewa baru (transaksi)
- Menu Sewa, Buat Sewa. Pilih pelanggan, tanggal sewa hari ini, tanggal kembali 2 hari lagi, jaminan KTP, DP 100.000.
- Isi qty: Tenda Sarnafil 3x3 = 1, Kursi Futura = 20.
- Hasil: nota tersimpan, `total_sewa` = 380.000 (2 hari), petugas otomatis dari akun login.
- Buka Barang: stok Tenda 3x3 turun 1 dan Kursi Futura turun 20 dari stok sebelumnya.
- Tunjukkan validasi: isi qty melebihi stok, atau DP melebihi total. Hasil: ditolak, stok tidak berubah.

### 5. Sewa terlambat
- Menu Sewa: banner merah dan badge `terlambat` pada nota #3.
- Tunjukkan denda berjalan dan tombol WhatsApp.

### 6. Pengembalian dengan denda dan kerusakan
- Buka nota #3 (terlambat 2 hari), klik Kembalikan.
- Isi: 1 Meja Bulat rusak dengan nominal 50.000, sisanya 0.
- Hasil: status `dikembalikan`, denda terisi (50% sewa per hari x 2 hari telat), ganti rugi 50.000, sisa bayar terhitung.
- Menu Barang: Meja Bulat `stok_rusak` = 1, stok bagus bertambah sesuai yang bagus.
- Coba kembalikan nota yang sama lagi: ditolak (sudah dikembalikan).

### 7. Laporan Kerusakan
- Menu Laporan Kerusakan. Hasil: barang rusak/hilang per nota + pelanggan, termasuk Gelas Kaca hilang 3 dan Chafing Dish rusak 1 dari data seeder, ditambah Meja Bulat dari langkah 6.

### 8. Perbedaan admin vs petugas
- Logout, login `petugas1@sewapesta.test`.
- Navbar: menu Kategori tidak ada.
- Buka langsung `/categories` atau `/items/create`: muncul 403.
- Menu Barang: petugas hanya bisa Detail, tidak ada Edit/Hapus.

## Bagian B: Postman (API)

Header: `Accept: application/json`.

| No | Request | Hasil yang harus muncul |
|---|---|---|
| 1 | `GET /api/stats` | 200, objek `data` berisi 6 angka |
| 2 | `GET /api/items?search=tenda` | 200, daftar barang tenda + pagination |
| 3 | `GET /api/rentals?status=disewa` | 200, daftar sewa, field `terlambat` |
| 4 | `POST /api/rentals` (body di `API.md` bagian 6) | 201, `status` = `disewa`, stok berkurang |
| 5 | `PUT /api/rentals/{id_nota_baru}/kembalikan` (body kosong) | 200, `status` = `dikembalikan`, stok kembali |
| 6 | `PUT` endpoint yang sama sekali lagi | 422, "Sewa ini sudah dikembalikan." |
| 7 | `POST /api/rentals` dengan `qty` 9999 | 422, stok tidak cukup |
| 8 | `GET /api/items/9999` | 404 |

## Poin yang Perlu Dijelaskan Saat Tanya Jawab

- **Transaksi header-detail**: `rentals` (nota) + `rental_items` (barang), bukan sekadar master berelasi.
- **`DB::transaction()`**: simpan nota, simpan item, dan kurangi stok dalam satu paket. Gagal di tengah, semuanya dibatalkan.
- **`harga_satuan` disalin** ke `rental_items` agar harga lama tidak ikut berubah kalau harga barang diubah.
- **Logika dipusatkan di Model `Rental`** (`buatSewa`, `prosesPengembalian`) agar web dan API memakai aturan yang sama.
- **Role**: proteksi di route (middleware `auth` + `admin`), bukan sekadar menyembunyikan tombol.
- **Dua server**: `artisan serve` hanya melayani satu request sekali jalan, jadi panggilan API internal dari dashboard butuh server kedua.
- **Eager loading** (`with`) dipakai di daftar sewa, barang, dan API supaya tidak terjadi N+1 query.