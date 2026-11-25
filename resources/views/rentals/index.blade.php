@extends('layouts.app')

@section('title', 'Sewa')

@section('content')
    <h1>Daftar Sewa</h1>

    @if ($jumlahTerlambat > 0)
        <div class="alert-error">Ada {{ $jumlahTerlambat }} sewa terlambat dan belum dikembalikan.</div>
    @endif

    <a href="{{ route('rentals.create') }}" class="btn">+ Buat Sewa</a>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Pelanggan</th>
                <th>Tanggal Sewa</th>
                <th>Tanggal Kembali</th>
                <th>Total Sewa</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rentals as $rental)
                @php $terlambat = $rental->isTerlambat(); @endphp
                <tr>
                    <td>{{ $rentals->firstItem() + $loop->index }}</td>
                    <td>{{ $rental->customer->nama }}</td>
                    <td>{{ $rental->tanggal_sewa }}</td>
                    <td>{{ $rental->tanggal_kembali }}</td>
                    <td>Rp {{ number_format($rental->total_sewa, 0, ',', '.') }}</td>
                    <td>
                        @if ($terlambat)
                            <span class="badge badge-terlambat">terlambat</span>
                            <br>
                            <small>
                                Telat {{ $rental->hariTelat() }} hari,
                                denda Rp {{ number_format($rental->hitungDenda($rental->hariTelat()), 0, ',', '.') }}
                            </small>
                        @else
                            <span class="badge badge-{{ $rental->status }}">{{ $rental->status }}</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('rentals.show', $rental->id) }}" class="btn btn-sm">Detail</a>
                        @if ($rental->status !== 'dikembalikan')
                            <a href="{{ route('rentals.formKembali', $rental->id) }}" class="btn btn-sm">Kembalikan</a>
                        @endif
                        @if ($terlambat)
                            <a href="{{ $rental->linkWhatsapp() }}" target="_blank" class="btn btn-sm btn-danger">WhatsApp</a>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">Belum ada sewa.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{ $rentals->links() }}
@endsection