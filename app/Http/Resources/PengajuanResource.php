<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PengajuanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'persediaan_id' => $this->persediaan_id,
            'persediaan' => $this->whenLoaded('persediaan', fn () => [
                'id' => $this->persediaan->id,
                'jenis_barang' => $this->persediaan->jenisBarang?->nama_generik,
                'satuan' => $this->persediaan->satuan,
            ]),
            'jenis' => $this->jenis,
            'jumlah' => $this->jumlah,
            'tanggal' => $this->tanggal?->toDateString(),
            'unit_kerja_penerima' => $this->unit_kerja_penerima,
            'status' => $this->status,
            'diajukan_oleh' => $this->diajukan_oleh,
            'diputuskan_oleh' => $this->diputuskan_oleh,
            'catatan_penolakan' => $this->catatan_penolakan,
            'tanggal_keputusan' => $this->tanggal_keputusan?->toDateTimeString(),
            'detail_pemotongan' => $this->whenLoaded('detailPemotongan'),
            'created_at' => $this->created_at?->toDateTimeString(),
        ];
    }
}
