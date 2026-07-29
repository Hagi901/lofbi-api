<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BatchPersediaan extends Model
{
    protected $fillable = ['persediaan_id', 'no_batch', 'tanggal_masuk', 'jumlah_masuk', 'harga_satuan', 'sisa_stok'];

    protected function casts(): array
    {
        return [
            'tanggal_masuk' => 'date',
            'harga_satuan' => 'decimal:2',
        ];
    }
}
