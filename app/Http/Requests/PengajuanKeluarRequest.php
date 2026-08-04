<?php

namespace App\Http\Requests;

use App\Models\Persediaan;
use Illuminate\Foundation\Http\FormRequest;

class PengajuanKeluarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'jumlah' => ['required', 'integer', 'min:1'],
            'tanggal' => ['required', 'date'],
            'unit_kerja_penerima' => ['required', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'jumlah.required' => 'Jumlah barang keluar wajib diisi.',
            'jumlah.min' => 'Jumlah minimal 1.',
            'tanggal.required' => 'Tanggal pengajuan wajib diisi.',
            'unit_kerja_penerima.required' => 'Unit kerja penerima wajib diisi.',
        ];
    }

    /**
     * Validasi kecukupan stok setelah validasi field lulus.
     * Dipanggil manual dari controller setelah ->validated().
     */
    public function ensureStokCukup(Persediaan $persediaan): void
    {
        $totalStok = $persediaan->batches()->sum('sisa_stok');
        $jumlah = (int) $this->input('jumlah');

        if ($totalStok < $jumlah) {
            abort(response()->json([
                'message' => "Stok tidak mencukupi. Stok tersedia: {$totalStok}, diminta: {$jumlah}.",
            ], 422));
        }
    }
}
