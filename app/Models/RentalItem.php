<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RentalItem extends Model
{
    protected $fillable = [
        'rental_id',
        'item_id',
        'qty',
        'harga_satuan',
        'subtotal',
        'qty_rusak',
        'qty_hilang',
        'biaya_kerusakan',
    ];

    public function rental()
    {
        return $this->belongsTo(Rental::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}