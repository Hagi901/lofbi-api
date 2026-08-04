<?php

namespace Tests\Feature;

use App\Models\BatchPersediaan;
use App\Models\JenisBarang;
use App\Models\Kategori;
use App\Models\Persediaan;
use App\Models\TransaksiPersediaan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LofbiApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_and_access_me_endpoint(): void
    {
        User::factory()->create([
            'email' => 'admin@lofbi.test',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        $login = $this->postJson('/api/login', [
            'email' => 'admin@lofbi.test',
            'password' => 'password',
        ])->assertOk();

        $this->withToken($login->json('access_token'))
            ->getJson('/api/me')
            ->assertOk()
            ->assertJsonPath('data.email', 'admin@lofbi.test');
    }

    public function test_kasubbag_approval_cuts_inventory_fifo_across_batches(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $kasubbag = User::factory()->create(['role' => 'kasubbag']);
        $kategori = Kategori::create(['nama' => 'ATK', 'tipe' => 'persediaan']);
        $jenis = JenisBarang::create(['nama_generik' => 'Pulpen', 'kategori_id' => $kategori->id]);
        $persediaan = Persediaan::create([
            'jenis_barang_id' => $jenis->id,
            'satuan' => 'pcs',
            'stok_minimum' => 5,
        ]);

        $batch1 = BatchPersediaan::create([
            'persediaan_id' => $persediaan->id,
            'no_batch' => 1,
            'tanggal_masuk' => '2026-01-01',
            'jumlah_masuk' => 10,
            'harga_satuan' => 1000,
            'sisa_stok' => 3,
        ]);
        $batch2 = BatchPersediaan::create([
            'persediaan_id' => $persediaan->id,
            'no_batch' => 2,
            'tanggal_masuk' => '2026-01-02',
            'jumlah_masuk' => 10,
            'harga_satuan' => 1200,
            'sisa_stok' => 10,
        ]);

        $pengajuan = TransaksiPersediaan::create([
            'persediaan_id' => $persediaan->id,
            'jenis' => 'keluar',
            'jumlah' => 8,
            'tanggal' => '2026-07-29',
            'unit_kerja_penerima' => 'TU',
            'diajukan_oleh' => $admin->id,
            'status' => 'menunggu',
        ]);

        $token = $this->postJson('/api/login', [
            'email' => $kasubbag->email,
            'password' => 'password',
        ])->json('access_token');

        $this->withToken($token)
            ->postJson("/api/persediaan/pengajuan/{$pengajuan->id}/setujui")
            ->assertOk()
            ->assertJsonPath('data.status', 'disetujui')
            ->assertJsonCount(2, 'data.detail_pemotongan');

        $this->assertSame(0, $batch1->fresh()->sisa_stok);
        $this->assertSame(5, $batch2->fresh()->sisa_stok);
    }
}
