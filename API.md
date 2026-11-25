# Dokumentasi REST API: Sewa Perlengkapan Pesta

Base URL: `http://127.0.0.1:8011/api`

Catatan umum:
- API tanpa autentikasi (sesuai materi modul).
- Header wajib: `Accept: application/json`.
- Untuk POST/PUT dengan body: `Content-Type: application/json`, body tipe raw JSON.
- Daftar (index) memakai pagination 10 data per halaman, parameter `?page=`.

## Ringkasan Endpoint

| No | Method | URL | Keterangan | Status Sukses |
|---|---|---|---|---|
| 1 | GET | `/api/items` | Daftar barang (filter `search`, `category_id`) | 200 |
| 2 | GET | `/api/items/{id}` | Detail barang | 200 |
| 3 | GET | `/api/customers` | Daftar pelanggan (filter `search`) | 200 |
| 4 | GET | `/api/customers/{id}` | Detail pelanggan + riwayat sewa | 200 |
| 5 | GET | `/api/rentals` | Daftar sewa (filter `status`) | 200 |
| 6 | POST | `/api/rentals` | Buat sewa baru | 201 |
| 7 | PUT | `/api/rentals/{id}/kembalikan` | Proses pengembalian | 200 |
| 8 | GET | `/api/stats` | Angka ringkasan dashboard | 200 |

## Status Code

| Kode | Arti |
|---|---|
| 200 | Sukses |
| 201 | Data berhasil dibuat |
| 404 | ID tidak ditemukan |
| 422 | Validasi gagal (stok tidak cukup, DP melebihi total, sewa sudah dikembalikan, dll.) |

---

## 1. GET /api/items

Query (opsional): `search` (nama barang), `category_id`, `page`.

Contoh: `GET /api/items?search=tenda`

Contoh response (200):
```json
{
  "data": [
    {
      "id": 1,
      "nama_barang": "Tenda Sarnafil 3x3",
      "kategori": { "id": 1, "nama_kategori": "Tenda" },
      "harga_sewa": 150000,
      "harga_ganti": 1500000,
      "stok": 8,
      "stok_rusak": 0,
      "stok_hilang": 0
    }
  ],
  "links": { "...": "..." },
  "meta": { "current_page": 1, "last_page": 1, "total": 3 }
}
```

## 2. GET /api/items/{id}

Contoh: `GET /api/items/1`

Response (200): satu objek barang dalam `data` (bentuk sama seperti di atas).
Response (404): ID tidak ada.

## 3. GET /api/customers

Query (opsional): `search` (nama), `page`.

Contoh response (200):
```json
{
  "data": [
    {
      "id": 1,
      "nama": "...",
      "nomor_telepon": "08...",
      "alamat": "..."
    }
  ],
  "links": { "...": "..." },
  "meta": { "current_page": 1, "last_page": 2, "total": 12 }
}
```

## 4. GET /api/customers/{id}

Contoh: `GET /api/customers/1`

Response (200): data pelanggan + `riwayat_sewa` (daftar sewa milik pelanggan itu).
Response (404): ID tidak ada.

## 5. GET /api/rentals

Query (opsional): `status` (`disewa` / `dikembalikan`), `page`.

Contoh: `GET /api/rentals?status=disewa`

Contoh response (200):
```json
{
  "data": [
    {
      "id": 3,
      "pelanggan": { "id": 3, "nama": "..." },
      "petugas": { "id": 2, "name": "..." },
      "tanggal_sewa": "2026-11-20",
      "tanggal_kembali": "2026-11-23",
      "tanggal_dikembalikan": null,
      "jaminan_jenis": "KTP",
      "total_sewa": 0,
      "dp": 0,
      "denda": 0,
      "total_ganti_rugi": 0,
      "sisa_bayar": 0,
      "status": "disewa",
      "terlambat": true,
      "barang": [
        {
          "rental_item_id": 0,
          "item_id": 0,
          "nama_barang": "Meja Bulat",
          "qty": 10,
          "harga_satuan": 15000,
          "subtotal": 0,
          "qty_rusak": 0,
          "qty_hilang": 0,
          "biaya_kerusakan": 0
        }
      ]
    }
  ],
  "links": { "...": "..." },
  "meta": { "...": "..." }
}
```

## 6. POST /api/rentals

Body (raw JSON):
```json
{
  "customer_id": 6,
  "user_id": 2,
  "tanggal_sewa": "2026-11-26",
  "tanggal_kembali": "2026-11-28",
  "jaminan_jenis": "KTP",
  "dp": 100000,
  "items": [
    { "item_id": 1, "qty": 1 },
    { "item_id": 4, "qty": 20 }
  ]
}
```

Aturan:
- `user_id` diminta di body karena API tanpa login.
- `items` minimal 1, `item_id` tidak boleh dobel, `qty` minimal 1.
- `jaminan_jenis`: `KTP`, `SIM`, atau `lainnya`.
- `total_sewa` dihitung server: qty x harga x jumlah hari (minimal 1 hari).

Response sukses (201): objek sewa (bentuk sama seperti nomor 5), `status` = `disewa`, `total_sewa` = 380000 untuk contoh di atas.

Response gagal (422), contoh stok tidak cukup:
```json
{
  "message": "...",
  "errors": { "items": ["..."] }
}
```

## 7. PUT /api/rentals/{id}/kembalikan

Body boleh kosong: semua barang dianggap kembali bagus.

Untuk barang rusak/hilang, kirim hanya barang bermasalah. Kunci memakai `rental_item_id` (bukan `item_id`):
```json
{
  "items": [
    { "rental_item_id": 12, "qty_rusak": 1, "nominal_rusak": 100000 }
  ]
}
```

Response sukses (200): objek sewa dengan `status` = `dikembalikan`, `tanggal_dikembalikan` terisi, serta `denda`, `total_ganti_rugi`, `sisa_bayar` sudah dihitung.

Response gagal:
- 404: ID sewa tidak ada.
- 422: sewa sudah dikembalikan, atau jumlah rusak + hilang melebihi qty disewa.

## 8. GET /api/stats

Response (200):
```json
{
  "data": {
    "sewa_aktif": 3,
    "sewa_terlambat": 1,
    "sewa_selesai": 2,
    "total_barang": 16,
    "total_pelanggan": 12,
    "pendapatan": 2920000
  }
}
```

| Field | Arti |
|---|---|
| `sewa_aktif` | sewa berstatus `disewa` (termasuk yang terlambat) |
| `sewa_terlambat` | sewa `disewa` yang tanggal kembalinya sudah lewat |
| `sewa_selesai` | sewa berstatus `dikembalikan` |
| `total_barang` | jumlah jenis barang |
| `total_pelanggan` | jumlah pelanggan |
| `pendapatan` | total sewa + denda + ganti rugi dari sewa yang sudah dikembalikan |