<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OpnameSesi extends Model
{
    protected $fillable = ['ruangan_id', 'admin_id', 'tanggal', 'status'];

    protected function casts(): array
    {
        return ['tanggal' => 'date'];
    }

    public function details()
    {
        return $this->hasMany(OpnameDetail::class);
    }
}
