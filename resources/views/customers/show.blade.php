@extends('layouts.app')

@section('title', 'Detail Pelanggan')

@section('content')
    <h1>{{ $customer->nama }}</h1>

    <table>
        <tr><th>Nomor Telepon</th><td>{{ $customer->nomor_telepon }}</td></tr>
        <tr><th>Alamat</th><td>{{ $customer->alamat }}</td></tr>
    </table>

    <h2>Riwayat Sewa</h2>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal Sewa</th>
                <th>Tanggal Kembali</th>
                <th>Total Sewa</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($customer->rentals as $rental)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $rental->tanggal_sewa }}</td>
                    <td>{{ $rental->tanggal_kembali }}</td>
                    <td>Rp {{ number_format($rental->total_sewa, 0, ',', '.') }}</td>
                    <td><span class="badge badge-{{ $rental->status }}">{{ $rental->status }}</span></td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">Belum ada riwayat sewa.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <a href="{{ route('customers.edit', $customer->id) }}" class="btn">Edit</a>
    <a href="{{ route('customers.index') }}">Kembali ke daftar</a>
@endsection