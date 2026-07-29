<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kategori extends Model
{
    protected $fillable = ['nama', 'tipe'];

    public function masaManfaat()
    {
        return $this->hasOne(MasaManfaatKategori::class);
    }
}
