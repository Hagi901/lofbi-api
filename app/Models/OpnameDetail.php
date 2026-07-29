<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OpnameDetail extends Model
{
    protected $fillable = ['opname_sesi_id', 'aset_id', 'persediaan_id', 'kondisi_aktual', 'jumlah_aktual', 'catatan'];
}
