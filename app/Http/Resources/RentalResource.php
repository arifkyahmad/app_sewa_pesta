<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RentalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'pelanggan' => [
                'id' => $this->customer?->id,
                'nama' => $this->customer?->nama,
            ],
            'petugas' => [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
            ],
            'tanggal_sewa' => $this->tanggal_sewa,
            'tanggal_kembali' => $this->tanggal_kembali,
            'tanggal_dikembalikan' => $this->tanggal_dikembalikan,
            'jaminan_jenis' => $this->jaminan_jenis,
            'total_sewa' => $this->total_sewa,
            'dp' => $this->dp,
            'denda' => $this->denda,
            'total_ganti_rugi' => $this->total_ganti_rugi,
            'sisa_bayar' => $this->sisa_bayar,
            'status' => $this->status,
            'terlambat' => $this->isTerlambat(),
            'barang' => $this->rentalItems->map(fn ($ri) => [
                'rental_item_id' => $ri->id,
                'item_id' => $ri->item?->id,
                'nama_barang' => $ri->item?->nama_barang,
                'qty' => $ri->qty,
                'harga_satuan' => $ri->harga_satuan,
                'subtotal' => $ri->subtotal,
                'qty_rusak' => $ri->qty_rusak,
                'qty_hilang' => $ri->qty_hilang,
                'biaya_kerusakan' => $ri->biaya_kerusakan,
            ]),
        ];
    }
}