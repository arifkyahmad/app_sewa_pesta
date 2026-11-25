@extends('layouts.app')

@section('title', 'Detail Sewa')

@section('content')
    @php
        $sudahKembali = $rental->status === 'dikembalikan';
        $terlambat = $rental->isTerlambat();
        $hariTelat = $rental->hariTelat();
    @endphp

    <h1>Detail Sewa #{{ $rental->id }}</h1>

    <table>
        <tr><th>Pelanggan</th><td>{{ $rental->customer->nama }} ({{ $rental->customer->nomor_telepon }})</td></tr>
        <tr><th>Petugas</th><td>{{ $rental->user->name }}</td></tr>
        <tr><th>Tanggal Sewa</th><td>{{ $rental->tanggal_sewa }}</td></tr>
        <tr><th>Tanggal Kembali (janji)</th><td>{{ $rental->tanggal_kembali }}</td></tr>
        <tr><th>Lama Sewa</th><td>{{ $jumlahHari }} hari</td></tr>
        @if ($sudahKembali)
            <tr><th>Tanggal Dikembalikan</th><td>{{ $rental->tanggal_dikembalikan }}</td></tr>
        @endif
        <tr>
            <th>Keterlambatan</th>
            <td>
                @if ($sudahKembali)
                    {{ $hariTelat > 0 ? 'Telat ' . $hariTelat . ' hari' : 'Tepat waktu' }}
                @elseif ($terlambat)
                    <span class="badge badge-terlambat">Telat {{ $hariTelat }} hari (belum dikembalikan)</span>
                @else
                    -
                @endif
            </td>
        </tr>
        <tr><th>Jaminan</th><td>{{ $rental->jaminan_jenis }}</td></tr>
        <tr>
            <th>Status</th>
            <td>
                <span class="badge badge-{{ $terlambat ? 'terlambat' : $rental->status }}">
                    {{ $terlambat ? 'terlambat' : $rental->status }}
                </span>
            </td>
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
                @if ($sudahKembali)
                    <th>Rusak</th>
                    <th>Hilang</th>
                    <th>Biaya Kerusakan</th>
                @endif
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
                    @if ($sudahKembali)
                        <td>{{ $ri->qty_rusak }}</td>
                        <td>{{ $ri->qty_hilang }}</td>
                        <td>Rp {{ number_format($ri->biaya_kerusakan, 0, ',', '.') }}</td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2>Pembayaran</h2>

    <table>
        <tr><th>Total Sewa</th><td>Rp {{ number_format($rental->total_sewa, 0, ',', '.') }}</td></tr>
        @if ($sudahKembali)
            <tr><th>Denda Telat</th><td>Rp {{ number_format($rental->denda, 0, ',', '.') }}</td></tr>
            <tr><th>Total Ganti Rugi</th><td>Rp {{ number_format($rental->total_ganti_rugi, 0, ',', '.') }}</td></tr>
            <tr><th>DP (sudah dibayar)</th><td>Rp {{ number_format($rental->dp, 0, ',', '.') }}</td></tr>
            <tr>
                <th>Sisa Bayar</th>
                <td><strong>Rp {{ number_format($rental->sisa_bayar, 0, ',', '.') }}</strong></td>
            </tr>
        @else
            <tr><th>DP</th><td>Rp {{ number_format($rental->dp, 0, ',', '.') }}</td></tr>
            @if ($terlambat)
                <tr>
                    <th>Denda Berjalan</th>
                    <td>Rp {{ number_format($rental->hitungDenda($hariTelat), 0, ',', '.') }}
                        <small>(bertambah tiap hari)</small>
                    </td>
                </tr>
            @endif
            <tr>
                <th>Sisa Sementara</th>
                <td>Rp {{ number_format($rental->total_sewa - $rental->dp, 0, ',', '.') }}
                    <small>(belum termasuk denda dan ganti rugi, dihitung saat pengembalian)</small>
                </td>
            </tr>
        @endif
    </table>

    @if (! $sudahKembali)
        <a href="{{ route('rentals.formKembali', $rental->id) }}" class="btn">Kembalikan Barang</a>
    @endif
    @if ($terlambat)
        <a href="{{ $rental->linkWhatsapp() }}" target="_blank" class="btn btn-danger">Hubungi via WhatsApp</a>
    @endif
    <a href="{{ route('rentals.index') }}" class="btn">Kembali ke daftar</a>
@endsection