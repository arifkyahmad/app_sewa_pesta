<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nama' => $this->nama,
            'nomor_telepon' => $this->nomor_telepon,
            'alamat' => $this->alamat,
            'riwayat_sewa' => $this->whenLoaded('rentals', fn () => $this->rentals->map(fn ($r) => [
                'id' => $r->id,
                'tanggal_sewa' => $r->tanggal_sewa,
                'tanggal_kembali' => $r->tanggal_kembali,
                'total_sewa' => $r->total_sewa,
                'status' => $r->status,
            ])),
        ];
    }
}