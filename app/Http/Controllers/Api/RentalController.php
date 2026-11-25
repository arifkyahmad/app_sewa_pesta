<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RentalResource;
use App\Models\Rental;
use Illuminate\Http\Request;

class RentalController extends Controller
{
    public function index()
    {
        $rentals = Rental::with(['customer', 'user', 'rentalItems.item'])
            ->when(request('status'), fn ($q, $s) => $q->where('status', $s))
            ->orderByDesc('id')
            ->paginate(10);

        return RentalResource::collection($rentals);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id'     => 'required|integer|exists:customers,id',
            'user_id'         => 'required|integer|exists:users,id',
            'tanggal_sewa'    => 'required|date',
            'tanggal_kembali' => 'required|date|after_or_equal:tanggal_sewa',
            'jaminan_jenis'   => 'required|in:KTP,SIM,lainnya',
            'dp'              => 'required|integer|min:0',
            'items'           => 'required|array|min:1',
            'items.*.item_id' => 'required|integer|distinct|exists:items,id',
            'items.*.qty'     => 'required|integer|min:1',
        ], [
            'customer_id.exists'         => 'Pelanggan tidak ditemukan.',
            'user_id.exists'             => 'Petugas tidak ditemukan.',
            'tanggal_kembali.after_or_equal' => 'Tanggal kembali tidak boleh sebelum tanggal sewa.',
            'jaminan_jenis.in'           => 'Jenis jaminan harus KTP, SIM, atau lainnya.',
            'items.required'             => 'Pilih minimal satu barang.',
            'items.*.item_id.exists'     => 'Barang tidak ditemukan.',
            'items.*.item_id.distinct'   => 'Barang yang sama tidak boleh dikirim dua kali.',
            'items.*.qty.min'            => 'Jumlah barang minimal 1.',
        ]);

        // Ubah [{item_id, qty}] jadi [id_barang => qty], format yang dipakai Model
        $validated['items'] = collect($validated['items'])
            ->mapWithKeys(fn ($i) => [$i['item_id'] => $i['qty']])
            ->all();

        $rental = Rental::buatSewa($validated, $validated['user_id']);
        $rental->load(['customer', 'user', 'rentalItems.item']);

        return (new RentalResource($rental))->response()->setStatusCode(201);
    }

    public function kembalikan(Request $request, $id)
    {
        $request->validate([
            'items'                  => 'sometimes|array',
            'items.*.rental_item_id' => 'required|integer',
            'items.*.qty_rusak'      => 'sometimes|integer|min:0',
            'items.*.qty_hilang'     => 'sometimes|integer|min:0',
            'items.*.nominal_rusak'  => 'sometimes|integer|min:0',
        ]);

        $rental = Rental::with('rentalItems')->findOrFail($id);

        // Barang yang tidak dikirim dianggap kembali bagus (rusak 0, hilang 0)
        $kiriman = collect($request->input('items', []))->keyBy('rental_item_id');

        $input = [];
        foreach ($rental->rentalItems as $ri) {
            $b = $kiriman->get($ri->id, []);
            $input[$ri->id] = [
                'qty_rusak'     => $b['qty_rusak'] ?? 0,
                'qty_hilang'    => $b['qty_hilang'] ?? 0,
                'nominal_rusak' => $b['nominal_rusak'] ?? 0,
            ];
        }

        $rental = Rental::prosesPengembalian($id, $input);
        $rental->load(['customer', 'user', 'rentalItems.item']);

        return new RentalResource($rental);
    }
}