<?php

namespace Database\Seeders;

use App\Models\JenisBarang;
use App\Models\Kategori;
use App\Models\MasaManfaatKategori;
use App\Models\Ruangan;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(['email' => 'admin@lofbi.test'], [
            'name' => 'Admin LOFBI',
            'role' => 'admin',
            'password' => Hash::make('password'),
        ]);

        User::updateOrCreate(['email' => 'kasubbag@lofbi.test'], [
            'name' => 'Kasubbag',
            'role' => 'kasubbag',
            'password' => Hash::make('password'),
        ]);

        $atk = Kategori::updateOrCreate(['nama' => 'ATK', 'tipe' => 'persediaan']);
        $elektronik = Kategori::updateOrCreate(['nama' => 'Elektronik', 'tipe' => 'aset']);
        MasaManfaatKategori::updateOrCreate(['kategori_id' => $elektronik->id], ['masa_manfaat_tahun' => 4]);

        Ruangan::updateOrCreate(['nama' => 'Ruang Tata Usaha'], ['gedung' => 'Gedung Utama']);
        Ruangan::updateOrCreate(['nama' => 'Gudang Persediaan'], ['gedung' => 'Gedung Utama']);

        JenisBarang::updateOrCreate(['nama_generik' => 'Laptop'], ['kategori_id' => $elektronik->id]);
        JenisBarang::updateOrCreate(['nama_generik' => 'Pulpen'], ['kategori_id' => $atk->id]);
    }
}
