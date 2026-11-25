<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama'          => 'required|string|max:100',
            'nomor_telepon' => 'required|string|max:20|regex:/^[0-9+]+$/',
            'alamat'        => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'nama.required'          => 'Nama pelanggan wajib diisi.',
            'nama.max'               => 'Nama pelanggan maksimal 100 karakter.',
            'nomor_telepon.required' => 'Nomor telepon wajib diisi.',
            'nomor_telepon.max'      => 'Nomor telepon maksimal 20 karakter.',
            'nomor_telepon.regex'    => 'Nomor telepon hanya boleh berisi angka dan tanda +.',
            'alamat.required'        => 'Alamat wajib diisi.',
        ];
    }
}