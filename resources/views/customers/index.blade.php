@extends('layouts.app')

@section('title', 'Pelanggan')

@section('content')
    <h1>Daftar Pelanggan</h1>

    <a href="{{ route('customers.create') }}" class="btn">+ Tambah Pelanggan</a>

    <form action="{{ route('customers.index') }}" method="GET" style="display: flex; gap: 8px; align-items: center; margin-top: 16px;">
        <input type="text" name="search" placeholder="Cari nama pelanggan..." value="{{ request('search') }}">
        <button type="submit" class="btn btn-sm">Cari</button>
        @if (request('search'))
            <a href="{{ route('customers.index') }}">Reset</a>
        @endif
    </form>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama</th>
                <th>Nomor Telepon</th>
                <th>Alamat</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($customers as $customer)
                <tr>
                    <td>{{ $customers->firstItem() + $loop->index }}</td>
                    <td>{{ $customer->nama }}</td>
                    <td>{{ $customer->nomor_telepon }}</td>
                    <td>{{ $customer->alamat }}</td>
                    <td>
                        <a href="{{ route('customers.show', $customer->id) }}" class="btn btn-sm">Detail</a>
                        <a href="{{ route('customers.edit', $customer->id) }}" class="btn btn-sm">Edit</a>
                        <form action="{{ route('customers.destroy', $customer->id) }}" method="POST" class="inline"
                            onsubmit="return confirm('Hapus pelanggan ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">Tidak ada pelanggan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{ $customers->appends(request()->query())->links() }}
@endsection