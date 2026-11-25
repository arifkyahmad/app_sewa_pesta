@extends('layouts.app')

@section('title', 'Laporan Kerusakan')

@section('content')
    <h1>Laporan Kerusakan</h1>

    <table>
        <tr><th>Total Unit Rusak</th><td>{{ $ringkasan->total_rusak }}</td></tr>
        <tr><th>Total Unit Hilang</th><td>{{ $ringkasan->total_hilang }}</td></tr>
        <tr><th>Total Biaya Kerusakan</th><td>Rp {{ number_format($ringkasan->total_biaya, 0, ',', '.') }}</td></tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal Dikembalikan</th>
                <th>Nota</th>
                <th>Pelanggan</th>
                <th>Barang</th>
                <th>Rusak</th>
                <th>Hilang</th>
                <th>Biaya Kerusakan</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td>{{ $rows->firstItem() + $loop->index }}</td>
                    <td>{{ $row->rental->tanggal_dikembalikan }}</td>
                    <td><a href="{{ route('rentals.show', $row->rental_id) }}">#{{ $row->rental_id }}</a></td>
                    <td>{{ $row->rental->customer->nama }}<br><small>{{ $row->rental->customer->nomor_telepon }}</small></td>
                    <td>{{ $row->item->nama_barang }}</td>
                    <td>{{ $row->qty_rusak }}</td>
                    <td>{{ $row->qty_hilang }}</td>
                    <td>Rp {{ number_format($row->biaya_kerusakan, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8">Belum ada barang rusak atau hilang.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{ $rows->links() }}
@endsection