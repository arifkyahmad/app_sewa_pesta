<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreItemRequest;
use App\Models\Category;
use App\Models\Item;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    public function index()
    {
        $items = Item::with('category')
            ->when(request('search'), fn ($q, $s) => $q->where('nama_barang', 'ilike', "%{$s}%"))
            ->paginate(10)
            ->withQueryString();

        return view('items.index', compact('items'));
    }

    public function create()
    {
        $categories = Category::orderBy('nama_kategori')->get();

        return view('items.create', compact('categories'));
    }

    public function store(StoreItemRequest $request)
    {
        Item::create($request->validated());

        return redirect()->route('items.index')
            ->with('success', 'Barang berhasil ditambahkan.');
    }

    public function show($id)
    {
        $item = Item::with('category')->findOrFail($id);

        return view('items.show', compact('item'));
    }

    public function edit($id)
    {
        $item = Item::findOrFail($id);
        $categories = Category::orderBy('nama_kategori')->get();

        return view('items.edit', compact('item', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $item = Item::findOrFail($id);

        $validated = $request->validate([
            'category_id' => 'required|integer|exists:categories,id',
            'nama_barang' => 'required|string|max:150',
            'harga_sewa'  => 'required|integer|min:0',
            'harga_ganti' => 'required|integer|min:0',
            'stok'        => 'required|integer|min:0',
        ], [
            'category_id.required' => 'Kategori wajib dipilih.',
            'category_id.exists'   => 'Kategori tidak valid.',
            'nama_barang.required' => 'Nama barang wajib diisi.',
            'nama_barang.max'      => 'Nama barang maksimal 150 karakter.',
            'harga_sewa.required'  => 'Harga sewa wajib diisi.',
            'harga_sewa.integer'   => 'Harga sewa harus berupa angka bulat.',
            'harga_sewa.min'       => 'Harga sewa tidak boleh negatif.',
            'harga_ganti.required' => 'Harga ganti wajib diisi.',
            'harga_ganti.integer'  => 'Harga ganti harus berupa angka bulat.',
            'harga_ganti.min'      => 'Harga ganti tidak boleh negatif.',
            'stok.required'        => 'Stok wajib diisi.',
            'stok.integer'         => 'Stok harus berupa angka bulat.',
            'stok.min'             => 'Stok tidak boleh negatif.',
        ]);

        $item->update($validated);

        return redirect()->route('items.index')
            ->with('success', 'Barang berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $item = Item::findOrFail($id);

        if ($item->rentalItems()->exists()) {
            return redirect()->route('items.index')
                ->with('error', 'Barang sudah pernah disewa, tidak bisa dihapus.');
        }

        $item->delete();

        return redirect()->route('items.index')
            ->with('success', 'Barang berhasil dihapus.');
    }
}