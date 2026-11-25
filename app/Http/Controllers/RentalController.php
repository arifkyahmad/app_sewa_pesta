<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRentalRequest;
use App\Http\Requests\ReturnRentalRequest;
use App\Models\Customer;
use App\Models\Item;
use App\Models\Rental;
use App\Models\RentalItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class RentalController extends Controller
{
    public function index()
    {
        $rentals = Rental::with(['customer', 'user'])
            ->orderByDesc('id')
            ->paginate(10);

        // Jumlah semua sewa terlambat (bukan hanya di halaman ini)
        $jumlahTerlambat = Rental::where('status', 'disewa')
            ->where('tanggal_kembali', '<', Carbon::today()->toDateString())
            ->count();

        return view('rentals.index', compact('rentals', 'jumlahTerlambat'));
    }

    public function create()
    {
        $customers = Customer::orderBy('nama')->get();
        $items = Item::with('category')
            ->where('stok', '>', 0)
            ->orderBy('nama_barang')
            ->get();

        return view('rentals.create', compact('customers', 'items'));
    }

    public function store(StoreRentalRequest $request)
    {
        Rental::buatSewa($request->validated(), Auth::id());

        return redirect()->route('rentals.index')
            ->with('success', 'Sewa berhasil disimpan.');
    }

    public function show($id)
    {
        $rental = Rental::with(['customer', 'user', 'rentalItems.item'])
            ->findOrFail($id);

        $jumlahHari = max(1, (int) Carbon::parse($rental->tanggal_sewa)
            ->diffInDays(Carbon::parse($rental->tanggal_kembali)));

        return view('rentals.show', compact('rental', 'jumlahHari'));
    }

    public function formKembali($id)
    {
        $rental = Rental::with(['customer', 'rentalItems.item'])->findOrFail($id);

        if ($rental->status === 'dikembalikan') {
            return redirect()->route('rentals.show', $rental->id)
                ->with('error', 'Sewa ini sudah dikembalikan.');
        }

        // Hari telat = hari ini - tanggal kembali, minimal 0
        $hariTelat = max(0, (int) Carbon::parse($rental->tanggal_kembali)
            ->diffInDays(Carbon::today(), false));

        $denda = $rental->hitungDenda($hariTelat);

        return view('rentals.return', compact('rental', 'hariTelat', 'denda'));
    }

    public function kembalikan(ReturnRentalRequest $request, $id)
    {
        Rental::prosesPengembalian($id, $request->validated()['items']);

        return redirect()->route('rentals.show', $id)
            ->with('success', 'Pengembalian berhasil disimpan.');
    }

    public function laporanKerusakan()
    {
        // Baris nota yang ada barang rusak atau hilang
        $adaKerusakan = function ($query) {
            $query->where('qty_rusak', '>', 0)->orWhere('qty_hilang', '>', 0);
        };

        $rows = RentalItem::with(['rental.customer', 'item'])
            ->where($adaKerusakan)
            ->orderByDesc('rental_id')
            ->paginate(10);

        // Ringkasan seluruh data, bukan hanya halaman ini
        $ringkasan = RentalItem::where($adaKerusakan)
            ->selectRaw('COALESCE(SUM(qty_rusak), 0) as total_rusak, COALESCE(SUM(qty_hilang), 0) as total_hilang, COALESCE(SUM(biaya_kerusakan), 0) as total_biaya')
            ->first();

        return view('rentals.damage-report', compact('rows', 'ringkasan'));
    }
}