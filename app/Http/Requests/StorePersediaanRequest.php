<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePersediaanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'jenis_barang_id' => ['required', 'exists:jenis_barangs,id'],
            'merk' => ['nullable', 'string', 'max:100'],
            'satuan' => ['required', 'string', 'max:50'],
            'stok_minimum' => ['required', 'integer', 'min:0'],
            'ruangan_id' => ['nullable', 'exists:ruangans,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'jenis_barang_id.required' => 'Jenis barang wajib dipilih.',
            'jenis_barang_id.exists' => 'Jenis barang tidak ditemukan.',
            'satuan.required' => 'Satuan wajib diisi.',
            'stok_minimum.required' => 'Stok minimum wajib diisi.',
            'stok_minimum.min' => 'Stok minimum tidak boleh negatif.',
        ];
    }
}
