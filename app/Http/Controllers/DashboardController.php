<?php

namespace App\Http\Controllers;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class DashboardController extends Controller
{
    public function index()
    {
        $baseUrl = config('services.internal_api.base_url');

        $stats = [
            'sewa_aktif'      => 0,
            'sewa_terlambat'  => 0,
            'sewa_selesai'    => 0,
            'total_barang'    => 0,
            'total_pelanggan' => 0,
            'pendapatan'      => 0,
        ];
        $terlambat = [];
        $apiError = false;

        try {
            $response = Http::acceptJson()->get($baseUrl . '/api/stats');
            if ($response->successful()) {
                $stats = array_merge($stats, $response->json('data', []));
            } else {
                $apiError = true;
            }

            $rentals = Http::acceptJson()->get($baseUrl . '/api/rentals', [
                'status' => 'disewa',
            ]);
            if ($rentals->successful()) {
                $terlambat = collect($rentals->json('data', []))
                    ->where('terlambat', true)
                    ->values()
                    ->all();
            }
        } catch (ConnectionException $e) {
            $apiError = true;
        }

        return view('dashboard', compact('stats', 'terlambat', 'apiError'));
    }
}