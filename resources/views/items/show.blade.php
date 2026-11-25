@extends('layouts.app')

@section('title', 'Detail Barang')

@section('content')
    <h1>{{ $item->nama_barang }}</h1>

    <table>
        <tr><th>Kategori</th><td>{{ $item->category->nama_kategori }}</td></tr>
        <tr><th>Harga Sewa per Hari</th><td>Rp {{ number_format($item->harga_sewa, 0, ',', '.') }}</td></tr>
        <tr><th>Harga Ganti per Unit</th><td>Rp {{ number_format($item->harga_ganti, 0, ',', '.') }}</td></tr>
        <tr><th>Stok Tersedia</th><td>{{ $item->stok }}</td></tr>
        <tr><th>Stok Rusak</th><td>{{ $item->stok_rusak }}</td></tr>
        <tr><th>Stok Hilang</th><td>{{ $item->stok_hilang }}</td></tr>
    </table>

    @if (auth()->user()->role === 'admin')
        <a href="{{ route('items.edit', $item->id) }}" class="btn">Edit</a>
    @endif
    <a href="{{ route('items.index') }}">Kembali ke daftar</a>
@endsection