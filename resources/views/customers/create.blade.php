@extends('layouts.app')

@section('title', 'Tambah Pelanggan')

@section('content')
    <h1>Tambah Pelanggan</h1>

    <form action="{{ route('customers.store') }}" method="POST">
        @csrf

        <label for="nama">Nama</label>
        <input type="text" id="nama" name="nama" value="{{ old('nama') }}">
        @error('nama')
            <div class="error">{{ $message }}</div>
        @enderror

        <label for="nomor_telepon">Nomor Telepon</label>
        <input type="tel" id="nomor_telepon" name="nomor_telepon" value="{{ old('nomor_telepon') }}">
        @error('nomor_telepon')
            <div class="error">{{ $message }}</div>
        @enderror

        <label for="alamat">Alamat</label>
        <textarea id="alamat" name="alamat" rows="3">{{ old('alamat') }}</textarea>
        @error('alamat')
            <div class="error">{{ $message }}</div>
        @enderror

        <button type="submit" class="btn">Simpan</button>
        <a href="{{ route('customers.index') }}">Kembali ke daftar</a>
    </form>
@endsection