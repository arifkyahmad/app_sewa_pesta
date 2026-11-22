@extends('layouts.app')

@section('title', 'Sewa')

@section('content')
    <h1>Daftar Sewa</h1>

    <a href="{{ route('rentals.create') }}" class="btn">+ Buat Sewa</a>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Pelanggan</th>
                <th>Petugas</th>
                <th>Tanggal Sewa</th>
                <th>Tanggal Kembali</th>
                <th>Total Sewa</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rentals as $rental)
                <tr>
                    <td>{{ $rentals->firstItem() + $loop->index }}</td>
                    <td>{{ $rental->customer->nama }}</td>
                    <td>{{ $rental->user->name }}</td>
                    <td>{{ $rental->tanggal_sewa }}</td>
                    <td>{{ $rental->tanggal_kembali }}</td>
                    <td>Rp {{ number_format($rental->total_sewa, 0, ',', '.') }}</td>
                    <td><span class="badge badge-{{ $rental->status }}">{{ $rental->status }}</span></td>
                    <td>
                        <a href="{{ route('rentals.show', $rental->id) }}" class="btn btn-sm">Detail</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8">Belum ada sewa.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{ $rentals->links() }}
@endsection