<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Rental extends Model
{
    // Persentase denda per hari telat, dari sewa per hari
    public const PERSEN_DENDA = 50;

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

    // Lama sewa dalam hari, minimal 1
    public function lamaSewa(): int
    {
        return max(1, (int) Carbon::parse($this->tanggal_sewa)
            ->diffInDays(Carbon::parse($this->tanggal_kembali)));
    }

    // Denda = persen x sewa per hari x hari telat
    public function hitungDenda(int $hariTelat): int
    {
        $sewaPerHari = intdiv($this->total_sewa, $this->lamaSewa());

        return intdiv($sewaPerHari * self::PERSEN_DENDA * $hariTelat, 100);
    }

    // Hari telat: dihitung sampai tanggal dikembalikan, atau sampai hari ini kalau belum dikembalikan
    public function hariTelat(): int
    {
        $akhir = $this->tanggal_dikembalikan
            ? Carbon::parse($this->tanggal_dikembalikan)
            : Carbon::today();

        return max(0, (int) Carbon::parse($this->tanggal_kembali)->diffInDays($akhir, false));
    }

    // Terlambat = belum dikembalikan dan sudah lewat tanggal janji
    public function isTerlambat(): bool
    {
        return $this->status === 'disewa' && $this->hariTelat() > 0;
    }

    // Link WhatsApp ke pelanggan dengan pesan siap kirim
    public function linkWhatsapp(): string
    {
        $pesan = "Halo {$this->customer->nama}, sewa perlengkapan pesta Anda (nota #{$this->id}) "
            . "seharusnya dikembalikan pada {$this->tanggal_kembali}. "
            . "Saat ini sudah telat {$this->hariTelat()} hari. "
            . "Mohon segera dikembalikan. Terima kasih.";

        return 'https://wa.me/' . $this->customer->nomorWhatsapp() . '?text=' . rawurlencode($pesan);
    }
}