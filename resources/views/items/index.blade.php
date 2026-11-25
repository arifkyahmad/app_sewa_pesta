@extends('layouts.app')

@section('title', 'Barang')

@section('content')
    <h1>Daftar Barang</h1>

    @if (auth()->user()->role === 'admin')
        <a href="{{ route('items.create') }}" class="btn">Tambah Barang</a>
    @endif

    <form method="GET" action="{{ route('items.index') }}" style="display:flex; gap:8px; margin:12px 0;">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama barang...">
        <button type="submit" class="btn">Cari</button>
        @if (request('search'))
            <a href="{{ route('items.index') }}">Reset</a>
        @endif
    </form>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Barang</th>
                <th>Kategori</th>
                <th>Harga Sewa/hari</th>
                <th>Stok</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($items as $item)
                <tr>
                    <td>{{ $items->firstItem() + $loop->index }}</td>
                    <td>{{ $item->nama_barang }}</td>
                    <td>{{ $item->category->nama_kategori }}</td>
                    <td>Rp {{ number_format($item->harga_sewa, 0, ',', '.') }}</td>
                    <td>{{ $item->stok }}</td>
                    <td>
                        <a href="{{ route('items.show', $item->id) }}" class="btn btn-sm">Detail</a>

                        @if (auth()->user()->role === 'admin')
                            <a href="{{ route('items.edit', $item->id) }}" class="btn btn-sm">Edit</a>
                            <form action="{{ route('items.destroy', $item->id) }}" method="POST" class="inline"
                                onsubmit="return confirm('Hapus barang ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">
                        {{ request('search') ? 'Barang tidak ditemukan.' : 'Belum ada barang.' }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{ $items->links() }}
@endsection