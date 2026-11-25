@extends('layouts.app')

@section('title', 'Buat Sewa')

@section('content')
    <h1>Buat Sewa</h1>

    <form action="{{ route('rentals.store') }}" method="POST" style="max-width: none;">
        @csrf

        <div style="max-width: 500px;">
            <label for="customer_id">Pelanggan</label>
            <select id="customer_id" name="customer_id">
                <option value="">-- Pilih pelanggan --</option>
                @foreach ($customers as $customer)
                    <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                        {{ $customer->nama }} - {{ $customer->nomor_telepon }}
                    </option>
                @endforeach
            </select>
            @error('customer_id')
                <div class="error">{{ $message }}</div>
            @enderror

            <label for="tanggal_sewa">Tanggal Sewa</label>
            <input type="date" id="tanggal_sewa" name="tanggal_sewa" value="{{ old('tanggal_sewa', now()->toDateString()) }}">
            @error('tanggal_sewa')
                <div class="error">{{ $message }}</div>
            @enderror

            <label for="tanggal_kembali">Tanggal Kembali</label>
            <input type="date" id="tanggal_kembali" name="tanggal_kembali" value="{{ old('tanggal_kembali') }}">
            @error('tanggal_kembali')
                <div class="error">{{ $message }}</div>
            @enderror

            <label for="jaminan_jenis">Jaminan</label>
            <select id="jaminan_jenis" name="jaminan_jenis">
                <option value="">-- Pilih jaminan --</option>
                @foreach (['KTP', 'SIM', 'lainnya'] as $jaminan)
                    <option value="{{ $jaminan }}" {{ old('jaminan_jenis') == $jaminan ? 'selected' : '' }}>
                        {{ $jaminan }}
                    </option>
                @endforeach
            </select>
            @error('jaminan_jenis')
                <div class="error">{{ $message }}</div>
            @enderror

            <label for="dp">DP (Rp)</label>
            <input type="number" id="dp" name="dp" min="0" value="{{ old('dp', 0) }}">
            @error('dp')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <h2>Barang yang Disewa</h2>
        <p>Isi jumlah pada barang yang disewa. Biarkan 0 untuk barang yang tidak disewa.</p>

        @error('items')
            <div class="error">{{ $message }}</div>
        @enderror

        <table>
            <thead>
                <tr>
                    <th>Barang</th>
                    <th>Kategori</th>
                    <th>Harga/hari</th>
                    <th>Stok</th>
                    <th>Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($items as $item)
                    <tr>
                        <td>{{ $item->nama_barang }}</td>
                        <td>{{ $item->category->nama_kategori }}</td>
                        <td>Rp {{ number_format($item->harga_sewa, 0, ',', '.') }}</td>
                        <td>{{ $item->stok }}</td>
                        <td>
                            <input type="number" name="items[{{ $item->id }}]" min="0" max="{{ $item->stok }}"
                                value="{{ old('items.' . $item->id, 0) }}" style="width: 90px;">
                            @error('items.' . $item->id)
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <button type="submit" class="btn">Simpan Sewa</button>
        <a href="{{ route('rentals.index') }}">Kembali ke daftar</a>
    </form>
@endsection