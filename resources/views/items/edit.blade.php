@extends('layouts.app')

@section('title', 'Edit Barang')

@section('content')
    <h1>Edit Barang</h1>

    <form action="{{ route('items.update', $item->id) }}" method="POST">
        @csrf
        @method('PUT')

        <label for="category_id">Kategori</label>
        <select id="category_id" name="category_id">
            <option value="">-- Pilih kategori --</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}"
                    {{ old('category_id', $item->category_id) == $category->id ? 'selected' : '' }}>
                    {{ $category->nama_kategori }}
                </option>
            @endforeach
        </select>
        @error('category_id')
            <div class="error">{{ $message }}</div>
        @enderror

        <label for="nama_barang">Nama Barang</label>
        <input type="text" id="nama_barang" name="nama_barang" value="{{ old('nama_barang', $item->nama_barang) }}">
        @error('nama_barang')
            <div class="error">{{ $message }}</div>
        @enderror

        <label for="harga_sewa">Harga Sewa per Hari (Rp)</label>
        <input type="number" id="harga_sewa" name="harga_sewa" min="0" value="{{ old('harga_sewa', $item->harga_sewa) }}">
        @error('harga_sewa')
            <div class="error">{{ $message }}</div>
        @enderror

        <label for="harga_ganti">Harga Ganti per Unit (Rp)</label>
        <input type="number" id="harga_ganti" name="harga_ganti" min="0" value="{{ old('harga_ganti', $item->harga_ganti) }}">
        @error('harga_ganti')
            <div class="error">{{ $message }}</div>
        @enderror

        <label for="stok">Stok Tersedia</label>
        <input type="number" id="stok" name="stok" min="0" value="{{ old('stok', $item->stok) }}">
        @error('stok')
            <div class="error">{{ $message }}</div>
        @enderror

        <button type="submit" class="btn">Simpan Perubahan</button>
        <a href="{{ route('items.index') }}">Kembali ke daftar</a>
    </form>
@endsection