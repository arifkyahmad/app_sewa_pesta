@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<style>
    .dash-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 28px; }
    .dash-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 18px; }
    .dash-card .label { font-size: 13px; color: #6b7280; margin-bottom: 6px; }
    .dash-card .value { font-size: 26px; font-weight: 700; color: #111827; }
    .dash-card.danger .value { color: #dc2626; }
    .dash-warn { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 10px 14px; border-radius: 6px; margin-bottom: 20px; }
    .dash-table { width: 100%; border-collapse: collapse; background: #fff; }
    .dash-table th, .dash-table td { padding: 10px 12px; border-bottom: 1px solid #e5e7eb; text-align: left; }
    .dash-table th { background: #f9fafb; font-size: 13px; color: #374151; }
    @media (max-width: 700px) { .dash-grid { grid-template-columns: 1fr; } }
</style>

<h1>Dashboard</h1>

@if ($apiError)
    <div class="dash-warn">
        Data statistik tidak bisa dimuat. Pastikan server API internal (port 8011) sedang berjalan.
    </div>
@endif

<div class="dash-grid">
    <div class="dash-card">
        <div class="label">Sewa Aktif</div>
        <div class="value">{{ $stats['sewa_aktif'] }}</div>
    </div>
    <div class="dash-card danger">
        <div class="label">Sewa Terlambat</div>
        <div class="value">{{ $stats['sewa_terlambat'] }}</div>
    </div>
    <div class="dash-card">
        <div class="label">Sewa Selesai</div>
        <div class="value">{{ $stats['sewa_selesai'] }}</div>
    </div>
    <div class="dash-card">
        <div class="label">Jenis Barang</div>
        <div class="value">{{ $stats['total_barang'] }}</div>
    </div>
    <div class="dash-card">
        <div class="label">Pelanggan</div>
        <div class="value">{{ $stats['total_pelanggan'] }}</div>
    </div>
    <div class="dash-card">
        <div class="label">Pendapatan</div>
        <div class="value">Rp {{ number_format($stats['pendapatan'], 0, ',', '.') }}</div>
    </div>
</div>

<h2>Sewa Terlambat</h2>

<table class="dash-table">
    <thead>
        <tr>
            <th>Nota</th>
            <th>Pelanggan</th>
            <th>Jatuh Tempo</th>
            <th>Telat</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($terlambat as $sewa)
            <tr>
                <td>#{{ $sewa['id'] }}</td>
                <td>{{ $sewa['pelanggan']['nama'] }}</td>
                <td>{{ \Carbon\Carbon::parse($sewa['tanggal_kembali'])->format('d/m/Y') }}</td>
                <td>{{ (int) \Carbon\Carbon::parse($sewa['tanggal_kembali'])->diffInDays(today()) }} hari</td>
                <td><a href="{{ route('rentals.show', $sewa['id']) }}">Detail</a></td>
            </tr>
        @empty
            <tr>
                <td colspan="5">Tidak ada sewa terlambat.</td>
            </tr>
        @endforelse
    </tbody>
</table>
@endsection