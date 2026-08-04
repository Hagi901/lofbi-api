<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AsetResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'jenis_barang_id' => $this->jenis_barang_id,
            'jenis_barang' => $this->whenLoaded('jenisBarang', fn () => [
                'id' => $this->jenisBarang->id,
                'nama' => $this->jenisBarang->nama_generik,
                'kategori' => $this->jenisBarang->kategori?->nama,
            ]),
            'kode_aset' => $this->kode_aset,
            'merk' => $this->merk,
            'model' => $this->model,
            'kondisi' => $this->kondisi,
            'ruangan_id' => $this->ruangan_id,
            'ruangan' => $this->whenLoaded('ruangan', fn () => [
                'id' => $this->ruangan?->id,
                'nama' => $this->ruangan?->nama,
                'gedung' => $this->ruangan?->gedung,
            ]),
            'nilai_perolehan' => (float) $this->nilai_perolehan,
            'tanggal_perolehan' => $this->tanggal_perolehan?->toDateString(),
            'akumulasi_penyusutan' => (float) $this->akumulasi_penyusutan,
            'nilai_buku' => (float) $this->nilai_buku,
            'terakhir_dihitung_semester' => $this->terakhir_dihitung_semester,
            'riwayat' => $this->whenLoaded('riwayat'),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
