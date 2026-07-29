<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiwayatAset extends Model
{
    protected $fillable = ['aset_id', 'jenis', 'keterangan', 'tanggal'];

    protected function casts(): array
    {
        return ['tanggal' => 'date'];
    }
}
