<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rental extends Model
{
    protected $fillable = [
        'customer_id',
        'user_id',
        'tanggal_sewa',
        'tanggal_kembali',
        'tanggal_dikembalikan',
        'jaminan_jenis',
        'total_sewa',
        'dp',
        'denda',
        'total_ganti_rugi',
        'sisa_bayar',
        'status',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function rentalItems()
    {
        return $this->hasMany(RentalItem::class);
    }
}