<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kategori;
use App\Models\Ruangan;

class MasterDataController extends Controller
{
    public function ruangan()
    {
        return Ruangan::orderBy('gedung')->orderBy('nama')->get();
    }

    public function kategori()
    {
        return Kategori::with('masaManfaat')->orderBy('tipe')->orderBy('nama')->get();
    }
}
