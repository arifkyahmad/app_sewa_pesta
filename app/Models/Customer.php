<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Customer extends Model
{
    use HasFactory;
    
    protected $fillable = ['nama', 'nomor_telepon', 'alamat'];

    public function rentals()
    {
        return $this->hasMany(Rental::class);
    }

    // Ubah 08xxx menjadi 628xxx supaya bisa dipakai di link wa.me
    public function nomorWhatsapp(): string
    {
        $nomor = preg_replace('/[^0-9]/', '', $this->nomor_telepon);

        return str_starts_with($nomor, '0') ? '62' . substr($nomor, 1) : $nomor;
    }
}