<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => 'required|integer|exists:categories,id',
            'nama_barang' => 'required|string|max:150',
            'harga_sewa'  => 'required|integer|min:0',
            'harga_ganti' => 'required|integer|min:0',
            'stok'        => 'required|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
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
        ];
    }
}