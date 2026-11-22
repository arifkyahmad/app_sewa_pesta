@extends('layouts.app')

@section('title', 'Detail Sewa')

@section('content')
    <h1>Detail Sewa #{{ $rental->id }}</h1>

    <table>
        <tr><th>Pelanggan</th><td>{{ $rental->customer->nama }} ({{ $rental->customer->nomor_telepon }})</td></tr>
        <tr><th>Petugas</th><td>{{ $rental->user->name }}</td></tr>
        <tr><th>Tanggal Sewa</th><td>{{ $rental->tanggal_sewa }}</td></tr>
        <tr><th>Tanggal Kembali</th><td>{{ $rental->tanggal_kembali }}</td></tr>
        <tr><th>Lama Sewa</th><td>{{ $jumlahHari }} hari</td></tr>
        <tr><th>Jaminan</th><td>{{ $rental->jaminan_jenis }}</td></tr>
        <tr>
            <th>Status</th>
            <td><span class="badge badge-{{ $rental->status }}">{{ $rental->status }}</span></td>
        </tr>
    </table>

    <h2>Barang yang Disewa</h2>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Barang</th>
                <th>Jumlah</th>
                <th>Harga/hari</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rental->rentalItems as $ri)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $ri->item->nama_barang }}</td>
                    <td>{{ $ri->qty }}</td>
                    <td>Rp {{ number_format($ri->harga_satuan, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($ri->subtotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2>Pembayaran</h2>

    <table>
        <tr><th>Total Sewa</th><td>Rp {{ number_format($rental->total_sewa, 0, ',', '.') }}</td></tr>
        <tr><th>DP</th><td>Rp {{ number_format($rental->dp, 0, ',', '.') }}</td></tr>
        <tr>
            <th>Sisa Sementara</th>
            <td>Rp {{ number_format($rental->total_sewa - $rental->dp, 0, ',', '.') }}
                <small>(belum termasuk denda dan ganti rugi, dihitung saat pengembalian)</small>
            </td>
        </tr>
    </table>

    <a href="{{ route('rentals.index') }}" class="btn">Kembali ke daftar</a>
@endsection