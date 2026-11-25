<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('rental_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rental_id')->constrained('rentals');
            $table->foreignId('item_id')->constrained('items');
            $table->integer('qty');
            $table->integer('harga_satuan');
            $table->integer('subtotal');
            $table->integer('qty_rusak')->default(0);
            $table->integer('qty_hilang')->default(0);
            $table->integer('biaya_kerusakan')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rental_items');
    }
};
