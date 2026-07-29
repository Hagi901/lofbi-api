<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Aset;
use App\Models\JenisBarang;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AsetController extends Controller
{
    public function ringkas(Request $request)
    {
        $query = Aset::query()
            ->join('jenis_barangs', 'jenis_barangs.id', '=', 'asets.jenis_barang_id')
            ->leftJoin('kategoris', 'kategoris.id', '=', 'jenis_barangs.kategori_id');

        $query->when($request->kategori_id, fn ($q, $id) => $q->where('jenis_barangs.kategori_id', $id))
            ->when($request->kondisi, fn ($q, $kondisi) => $q->where('asets.kondisi', $kondisi))
            ->when($request->ruangan_id, fn ($q, $id) => $q->where('asets.ruangan_id', $id))
            ->when($request->search, fn ($q, $search) => $q->where(function ($inner) use ($search) {
                $inner->where('jenis_barangs.nama_generik', 'like', "%{$search}%")
                    ->orWhere('asets.kode_aset', 'like', "%{$search}%")
                    ->orWhere('asets.merk', 'like', "%{$search}%")
                    ->orWhere('asets.model', 'like', "%{$search}%");
            }));

        return $query
            ->selectRaw('jenis_barangs.id as jenis_barang_id, jenis_barangs.nama_generik, kategoris.nama as kategori, count(asets.id) as jumlah_unit, coalesce(sum(asets.nilai_buku), 0) as total_nilai_buku')
            ->groupBy('jenis_barangs.id', 'jenis_barangs.nama_generik', 'kategoris.nama')
            ->orderBy('jenis_barangs.nama_generik')
            ->get();
    }

    public function unit(JenisBarang $jenisBarang)
    {
        return Aset::with(['jenisBarang.kategori', 'ruangan'])
            ->where('jenis_barang_id', $jenisBarang->id)
            ->orderBy('kode_aset')
            ->get();
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['nilai_buku'] = $data['nilai_buku'] ?? $data['nilai_perolehan'];

        return response()->json(Aset::create($data)->load(['jenisBarang.kategori', 'ruangan']), 201);
    }

    public function show(Aset $aset)
    {
        return $aset->load(['jenisBarang.kategori.masaManfaat', 'ruangan', 'riwayat']);
    }

    public function update(Request $request, Aset $aset)
    {
        $aset->update($this->validated($request, $aset->id));

        return $aset->load(['jenisBarang.kategori', 'ruangan']);
    }

    public function destroy(Aset $aset)
    {
        $aset->delete();

        return response()->noContent();
    }

    public function riwayat(Aset $aset)
    {
        return $aset->riwayat()->latest('tanggal')->get();
    }

    private function validated(Request $request, ?int $asetId = null): array
    {
        return $request->validate([
            'jenis_barang_id' => ['required', 'exists:jenis_barangs,id'],
            'kode_aset' => ['required', 'string', Rule::unique('asets', 'kode_aset')->ignore($asetId)],
            'merk' => ['nullable', 'string'],
            'model' => ['nullable', 'string'],
            'kondisi' => ['required', Rule::in(['baik', 'rusak_ringan', 'rusak_berat'])],
            'ruangan_id' => ['nullable', 'exists:ruangans,id'],
            'nilai_perolehan' => ['required', 'numeric', 'min:0'],
            'tanggal_perolehan' => ['nullable', 'date'],
            'akumulasi_penyusutan' => ['sometimes', 'numeric', 'min:0'],
            'nilai_buku' => ['sometimes', 'numeric', 'min:0'],
            'terakhir_dihitung_semester' => ['nullable', 'string'],
        ]);
    }
}
