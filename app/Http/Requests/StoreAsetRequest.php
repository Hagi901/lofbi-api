<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAsetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $asetId = $this->route('aset')?->id;

        return [
            'jenis_barang_id' => ['required', 'exists:jenis_barangs,id'],
            'kode_aset' => ['required', 'string', Rule::unique('asets', 'kode_aset')->ignore($asetId)],
            'merk' => ['nullable', 'string', 'max:100'],
            'model' => ['nullable', 'string', 'max:100'],
            'kondisi' => ['required', Rule::in(['baik', 'rusak_ringan', 'rusak_berat'])],
            'ruangan_id' => ['nullable', 'exists:ruangans,id'],
            'nilai_perolehan' => ['required', 'numeric', 'min:0'],
            'tanggal_perolehan' => ['nullable', 'date'],
            'akumulasi_penyusutan' => ['sometimes', 'numeric', 'min:0'],
            'nilai_buku' => ['sometimes', 'numeric', 'min:0'],
            'terakhir_dihitung_semester' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'jenis_barang_id.required' => 'Jenis barang wajib dipilih.',
            'jenis_barang_id.exists' => 'Jenis barang tidak ditemukan.',
            'kode_aset.required' => 'Kode aset wajib diisi.',
            'kode_aset.unique' => 'Kode aset sudah digunakan.',
            'kondisi.in' => 'Kondisi harus salah satu: baik, rusak_ringan, atau rusak_berat.',
            'nilai_perolehan.required' => 'Nilai perolehan wajib diisi.',
            'nilai_perolehan.min' => 'Nilai perolehan tidak boleh negatif.',
        ];
    }
}
