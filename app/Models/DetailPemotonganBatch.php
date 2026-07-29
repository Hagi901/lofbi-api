<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailPemotonganBatch extends Model
{
    protected $fillable = ['transaksi_persediaan_id', 'batch_id', 'jumlah_diambil', 'harga_satuan_saat_itu'];

    protected function casts(): array
    {
        return ['harga_satuan_saat_itu' => 'decimal:2'];
    }

    public function batch()
    {
        return $this->belongsTo(BatchPersediaan::class, 'batch_id');
    }
}
