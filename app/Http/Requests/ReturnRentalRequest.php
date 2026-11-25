<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReturnRentalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'items'                 => 'required|array',
            'items.*.qty_rusak'     => 'required|integer|min:0',
            'items.*.qty_hilang'    => 'required|integer|min:0',
            'items.*.nominal_rusak' => 'required|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'items.required'                 => 'Data barang tidak lengkap.',
            'items.array'                    => 'Data barang tidak valid.',
            'items.*.qty_rusak.required'     => 'Jumlah rusak wajib diisi (isi 0 jika tidak ada).',
            'items.*.qty_rusak.integer'      => 'Jumlah rusak harus berupa angka bulat.',
            'items.*.qty_rusak.min'          => 'Jumlah rusak tidak boleh negatif.',
            'items.*.qty_hilang.required'    => 'Jumlah hilang wajib diisi (isi 0 jika tidak ada).',
            'items.*.qty_hilang.integer'     => 'Jumlah hilang harus berupa angka bulat.',
            'items.*.qty_hilang.min'         => 'Jumlah hilang tidak boleh negatif.',
            'items.*.nominal_rusak.required' => 'Nominal kerusakan wajib diisi (isi 0 jika tidak ada).',
            'items.*.nominal_rusak.integer'  => 'Nominal kerusakan harus berupa angka bulat.',
            'items.*.nominal_rusak.min'      => 'Nominal kerusakan tidak boleh negatif.',
        ];
    }
}