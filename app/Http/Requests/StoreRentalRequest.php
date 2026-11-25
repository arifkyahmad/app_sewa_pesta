<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRentalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id'     => 'required|integer|exists:customers,id',
            'tanggal_sewa'    => 'required|date',
            'tanggal_kembali' => 'required|date|after_or_equal:tanggal_sewa',
            'jaminan_jenis'   => 'required|in:KTP,SIM,lainnya',
            'dp'              => 'required|integer|min:0',
            'items'           => 'required|array',
            'items.*'         => 'required|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'customer_id.required'        => 'Pelanggan wajib dipilih.',
            'customer_id.integer'         => 'Pelanggan tidak valid.',
            'customer_id.exists'          => 'Pelanggan tidak valid.',
            'tanggal_sewa.required'       => 'Tanggal sewa wajib diisi.',
            'tanggal_sewa.date'           => 'Tanggal sewa tidak valid.',
            'tanggal_kembali.required'    => 'Tanggal kembali wajib diisi.',
            'tanggal_kembali.date'        => 'Tanggal kembali tidak valid.',
            'tanggal_kembali.after_or_equal' => 'Tanggal kembali tidak boleh sebelum tanggal sewa.',
            'jaminan_jenis.required'      => 'Jaminan wajib dipilih.',
            'jaminan_jenis.in'            => 'Jenis jaminan tidak valid.',
            'dp.required'                 => 'DP wajib diisi (isi 0 jika tanpa DP).',
            'dp.integer'                  => 'DP harus berupa angka bulat.',
            'dp.min'                      => 'DP tidak boleh negatif.',
            'items.required'              => 'Pilih minimal satu barang.',
            'items.array'                 => 'Data barang tidak valid.',
            'items.*.required'            => 'Jumlah barang wajib diisi (isi 0 jika tidak disewa).',
            'items.*.integer'             => 'Jumlah barang harus berupa angka bulat.',
            'items.*.min'                 => 'Jumlah barang tidak boleh negatif.',
        ];
    }
}