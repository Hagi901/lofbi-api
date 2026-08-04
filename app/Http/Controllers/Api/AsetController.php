<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAsetRequest;
use App\Http\Resources\AsetResource;
use App\Models\Aset;
use App\Models\JenisBarang;
use Illuminate\Http\Request;

class AsetController extends Controller
{
    /**
     * Ringkasan aset dikelompokkan per jenis barang.
     * Mendukung filter: kategori_id, kondisi, ruangan_id, search.
     * Response dipaginasi 20 item per halaman.
     */
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
            ->paginate(20);
    }

    /**
     * Daftar unit aset per jenis barang. Dipaginasi 20 item per halaman.
     */
    public function unit(JenisBarang $jenisBarang)
    {
        $asets = Aset::with(['jenisBarang.kategori', 'ruangan'])
            ->where('jenis_barang_id', $jenisBarang->id)
            ->orderBy('kode_aset')
            ->paginate(20);

        return AsetResource::collection($asets);
    }

    public function store(StoreAsetRequest $request)
    {
        $data = $request->validated();
        $data['nilai_buku'] = $data['nilai_buku'] ?? $data['nilai_perolehan'];

        $aset = Aset::create($data)->load(['jenisBarang.kategori', 'ruangan']);

        return (new AsetResource($aset))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Aset $aset)
    {
        return new AsetResource(
            $aset->load(['jenisBarang.kategori.masaManfaat', 'ruangan', 'riwayat'])
        );
    }

    public function update(StoreAsetRequest $request, Aset $aset)
    {
        $aset->update($request->validated());

        return new AsetResource($aset->load(['jenisBarang.kategori', 'ruangan']));
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
}
