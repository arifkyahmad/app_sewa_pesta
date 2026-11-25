<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nama_barang' => $this->nama_barang,
            'kategori' => [
                'id' => $this->category?->id,
                'nama_kategori' => $this->category?->nama_kategori,
            ],
            'harga_sewa' => $this->harga_sewa,
            'harga_ganti' => $this->harga_ganti,
            'stok' => $this->stok,
            'stok_rusak' => $this->stok_rusak,
            'stok_hilang' => $this->stok_hilang,
        ];
    }
}