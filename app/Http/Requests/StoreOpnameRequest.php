<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOpnameRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ruangan_id' => ['required', 'exists:ruangans,id'],
            'tanggal' => ['required', 'date'],
            'status' => ['sometimes', 'string', 'in:selesai,draft'],
            'details' => ['required', 'array', 'min:1'],
            // Salah satu dari aset_id atau persediaan_id WAJIB diisi, tidak boleh keduanya null
            'details.*.aset_id' => ['required_without:details.*.persediaan_id', 'nullable', 'exists:asets,id'],
            'details.*.persediaan_id' => ['required_without:details.*.aset_id', 'nullable', 'exists:persediaans,id'],
            'details.*.kondisi_aktual' => ['nullable', 'string', 'max:100'],
            'details.*.jumlah_aktual' => ['nullable', 'integer', 'min:0'],
            'details.*.catatan' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'ruangan_id.required' => 'Ruangan wajib dipilih.',
            'ruangan_id.exists' => 'Ruangan tidak ditemukan.',
            'tanggal.required' => 'Tanggal opname wajib diisi.',
            'details.required' => 'Detail opname wajib diisi.',
            'details.min' => 'Minimal satu item detail opname.',
            'details.*.aset_id.required_without' => 'Setiap detail harus memiliki aset_id atau persediaan_id.',
            'details.*.persediaan_id.required_without' => 'Setiap detail harus memiliki aset_id atau persediaan_id.',
            'details.*.jumlah_aktual.min' => 'Jumlah aktual tidak boleh negatif.',
        ];
    }
}
