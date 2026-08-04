<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransaksiPersediaan extends Model
{
    protected $fillable = [
        'persediaan_id', 'jenis', 'jumlah', 'tanggal', 'unit_kerja_penerima',
        'diajukan_oleh', 'status', 'diputuskan_oleh', 'catatan_penolakan',
        'tanggal_keputusan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'tanggal_keputusan' => 'datetime',
        ];
    }

    public function persediaan()
    {
        return $this->belongsTo(Persediaan::class);
    }

    public function detailPemotongan()
    {
        return $this->hasMany(DetailPemotonganBatch::class);
    }
}
