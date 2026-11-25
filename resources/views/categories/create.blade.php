@extends('layouts.app')

@section('title', 'Tambah Kategori')

@section('content')
    <h1>Tambah Kategori</h1>

    <form action="{{ route('categories.store') }}" method="POST">
        @csrf

        <label for="nama_kategori">Nama Kategori</label>
        <input type="text" id="nama_kategori" name="nama_kategori" value="{{ old('nama_kategori') }}">
        @error('nama_kategori')
            <div class="error">{{ $message }}</div>
        @enderror

        <label for="deskripsi">Deskripsi (opsional)</label>
        <textarea id="deskripsi" name="deskripsi" rows="3">{{ old('deskripsi') }}</textarea>
        @error('deskripsi')
            <div class="error">{{ $message }}</div>
        @enderror

        <button type="submit" class="btn">Simpan</button>
        <a href="{{ route('categories.index') }}">Kembali ke daftar</a>
    </form>
@endsection