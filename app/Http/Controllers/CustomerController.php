<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerRequest;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = Customer::when(request('search'), function ($query, $search) {
                $query->where('nama', 'ilike', "%{$search}%");
            })
            ->orderBy('nama')
            ->paginate(10);

        return view('customers.index', compact('customers'));
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(StoreCustomerRequest $request)
    {
        Customer::create($request->validated());

        return redirect()->route('customers.index')
            ->with('success', 'Pelanggan berhasil ditambahkan.');
    }

    public function show($id)
    {
        $customer = Customer::with('rentals')->findOrFail($id);

        return view('customers.show', compact('customer'));
    }

    public function edit($id)
    {
        $customer = Customer::findOrFail($id);

        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);

        $validated = $request->validate([
            'nama'          => 'required|string|max:100',
            'nomor_telepon' => 'required|string|max:20|regex:/^[0-9+]+$/',
            'alamat'        => 'required|string',
        ], [
            'nama.required'          => 'Nama pelanggan wajib diisi.',
            'nama.max'               => 'Nama pelanggan maksimal 100 karakter.',
            'nomor_telepon.required' => 'Nomor telepon wajib diisi.',
            'nomor_telepon.max'      => 'Nomor telepon maksimal 20 karakter.',
            'nomor_telepon.regex'    => 'Nomor telepon hanya boleh berisi angka dan tanda +.',
            'alamat.required'        => 'Alamat wajib diisi.',
        ]);

        $customer->update($validated);

        return redirect()->route('customers.index')
            ->with('success', 'Pelanggan berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $customer = Customer::findOrFail($id);

        if ($customer->rentals()->exists()) {
            return redirect()->route('customers.index')
                ->with('error', 'Pelanggan punya riwayat sewa, tidak bisa dihapus.');
        }

        $customer->delete();

        return redirect()->route('customers.index')
            ->with('success', 'Pelanggan berhasil dihapus.');
    }
}