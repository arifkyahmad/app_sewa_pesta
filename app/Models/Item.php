<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $fillable = [
        'category_id',
        'nama_barang',
        'harga_sewa',
        'harga_ganti',
        'stok',
        'stok_rusak',
        'stok_hilang',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function rentalItems()
    {
        return $this->hasMany(RentalItem::class);
    }
}