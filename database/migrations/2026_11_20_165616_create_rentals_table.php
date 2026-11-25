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
        Schema::create('rentals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers');
            $table->foreignId('user_id')->constrained('users');
            $table->date('tanggal_sewa');
            $table->date('tanggal_kembali');
            $table->date('tanggal_dikembalikan')->nullable();
            $table->enum('jaminan_jenis', ['KTP', 'SIM', 'lainnya']);
            $table->integer('total_sewa');
            $table->integer('dp')->default(0);
            $table->integer('denda')->default(0);
            $table->integer('total_ganti_rugi')->default(0);
            $table->integer('sisa_bayar')->default(0);
            $table->enum('status', ['disewa', 'dikembalikan', 'terlambat'])->default('disewa');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rentals');
    }
};
