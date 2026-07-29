<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Persediaan extends Model
{
    protected $fillable = ['jenis_barang_id', 'merk', 'satuan', 'stok_minimum', 'ruangan_id'];

    public function jenisBarang()
    {
        return $this->belongsTo(JenisBarang::class);
    }

    public function ruangan()
    {
        return $this->belongsTo(Ruangan::class);
    }

    public function batches()
    {
        return $this->hasMany(BatchPersediaan::class);
    }

    public function transaksi()
    {
        return $this->hasMany(TransaksiPersediaan::class);
    }
}
