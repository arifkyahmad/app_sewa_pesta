@extends('layouts.app')

@section('title', 'Pengembalian Sewa')

@section('content')
    <h1>Pengembalian Sewa #{{ $rental->id }}</h1>

    <table>
        <tr><th>Pelanggan</th><td>{{ $rental->customer->nama }} ({{ $rental->customer->nomor_telepon }})</td></tr>
        <tr><th>Tanggal Sewa</th><td>{{ $rental->tanggal_sewa }}</td></tr>
        <tr><th>Tanggal Kembali (janji)</th><td>{{ $rental->tanggal_kembali }}</td></tr>
        <tr><th>Dikembalikan Hari Ini</th><td>{{ now()->toDateString() }}</td></tr>
        <tr>
            <th>Keterlambatan</th>
            <td>
                @if ($hariTelat > 0)
                    <span class="badge badge-terlambat">Telat {{ $hariTelat }} hari</span>
                @else
                    Tepat waktu
                @endif
            </td>
        </tr>
        <tr><th>Denda Telat</th><td>Rp {{ number_format($denda, 0, ',', '.') }}</td></tr>
    </table>

    <form action="{{ route('rentals.kembalikan', $rental->id) }}" method="POST" style="max-width: none;">
        @csrf
        @method('PUT')

        <h2>Kondisi Barang</h2>
        <p>
            Isi jumlah yang rusak dan hilang. Jumlah bagus dihitung otomatis (disewa dikurangi rusak dan hilang).
            Nominal kerusakan diisi total untuk barang tersebut. Biaya barang hilang dihitung otomatis dari harga ganti.
        </p>

        @if ($errors->any())
            <div class="alert-error">
                <ul style="margin: 0; padding-left: 18px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <table>
            <thead>
                <tr>
                    <th>Barang</th>
                    <th>Disewa</th>
                    <th>Harga Ganti/unit</th>
                    <th>Rusak</th>
                    <th>Hilang</th>
                    <th>Nominal Kerusakan (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rental->rentalItems as $ri)
                    <tr>
                        <td>{{ $ri->item->nama_barang }}</td>
                        <td>{{ $ri->qty }}</td>
                        <td>Rp {{ number_format($ri->item->harga_ganti, 0, ',', '.') }}</td>
                        <td>
                            <input type="number" name="items[{{ $ri->id }}][qty_rusak]" min="0" max="{{ $ri->qty }}"
                                value="{{ old('items.' . $ri->id . '.qty_rusak', 0) }}" style="width: 80px;">
                        </td>
                        <td>
                            <input type="number" name="items[{{ $ri->id }}][qty_hilang]" min="0" max="{{ $ri->qty }}"
                                value="{{ old('items.' . $ri->id . '.qty_hilang', 0) }}" style="width: 80px;">
                        </td>
                        <td>
                            <input type="number" name="items[{{ $ri->id }}][nominal_rusak]" min="0"
                                value="{{ old('items.' . $ri->id . '.nominal_rusak', 0) }}" style="width: 130px;">
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <p><small>Total ganti rugi dan sisa bayar dihitung saat disimpan.</small></p>

        <button type="submit" class="btn">Simpan Pengembalian</button>
        <a href="{{ route('rentals.show', $rental->id) }}">Kembali ke detail</a>
    </form>
@endsection