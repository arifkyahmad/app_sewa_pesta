<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Item;
use App\Models\Rental;
use Carbon\Carbon;

class StatsController extends Controller
{
    public function index()
    {
        $hariIni = Carbon::today()->toDateString();

        $sewaAktif = Rental::where('status', 'disewa')->count();

        $sewaTerlambat = Rental::where('status', 'disewa')
            ->where('tanggal_kembali', '<', $hariIni)
            ->count();

        $sewaSelesai = Rental::where('status', 'dikembalikan')->count();

        // Pendapatan hanya dari sewa yang sudah dikembalikan (tagihan akhirnya sudah final)
        $pendapatan = (int) Rental::where('status', 'dikembalikan')
            ->selectRaw('COALESCE(SUM(total_sewa + denda + total_ganti_rugi), 0) as total')
            ->value('total');

        return response()->json([
            'data' => [
                'sewa_aktif' => $sewaAktif,
                'sewa_terlambat' => $sewaTerlambat,
                'sewa_selesai' => $sewaSelesai,
                'total_barang' => Item::count(),
                'total_pelanggan' => Customer::count(),
                'pendapatan' => $pendapatan,
            ],
        ]);
    }
}