<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Aset extends Model
{
    protected $fillable = [
        'jenis_barang_id', 'kode_aset', 'merk', 'model', 'kondisi', 'ruangan_id',
        'nilai_perolehan', 'tanggal_perolehan', 'akumulasi_penyusutan',
        'nilai_buku', 'terakhir_dihitung_semester',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_perolehan' => 'date',
            'nilai_perolehan' => 'decimal:2',
            'akumulasi_penyusutan' => 'decimal:2',
            'nilai_buku' => 'decimal:2',
        ];
    }

    public function jenisBarang()
    {
        return $this->belongsTo(JenisBarang::class);
    }

    public function ruangan()
    {
        return $this->belongsTo(Ruangan::class);
    }

    public function riwayat()
    {
        return $this->hasMany(RiwayatAset::class);
    }
}
