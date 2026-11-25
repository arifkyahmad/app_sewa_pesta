<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ItemResource;
use App\Models\Item;

class ItemController extends Controller
{
    public function index()
    {
        $items = Item::with('category')
            ->when(request('search'), fn ($q, $s) => $q->where('nama_barang', 'ilike', "%{$s}%"))
            ->when(request('category_id'), fn ($q, $c) => $q->where('category_id', $c))
            ->orderBy('nama_barang')
            ->paginate(10);

        return ItemResource::collection($items);
    }

    public function show($id)
    {
        $item = Item::with('category')->findOrFail($id);

        return new ItemResource($item);
    }
}