<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOpnameRequest;
use App\Models\Aset;
use App\Models\OpnameSesi;
use App\Models\Persediaan;
use App\Models\Ruangan;
use Illuminate\Support\Facades\DB;

class OpnameController extends Controller
{
    /**
     * Ambil daftar aset dan persediaan yang terdaftar di ruangan,
     * sebagai referensi untuk pengisian form opname.
     */
    public function ruangan(Ruangan $ruangan)
    {
        return [
            'ruangan' => $ruangan,
            'aset' => Aset::with('jenisBarang')
                ->where('ruangan_id', $ruangan->id)
                ->orderBy('kode_aset')
                ->get(),
            'persediaan' => Persediaan::with(['jenisBarang', 'batches'])
                ->where('ruangan_id', $ruangan->id)
                ->get()
                ->map(fn ($item) => $item->setAttribute('total_stok', $item->batches->sum('sisa_stok'))),
        ];
    }

    /**
     * Simpan hasil sesi opname fisik.
     * Setiap detail wajib memiliki aset_id atau persediaan_id (tidak boleh keduanya null).
     */
    public function store(StoreOpnameRequest $request)
    {
        $data = $request->validated();

        return DB::transaction(function () use ($data, $request) {
            $sesi = OpnameSesi::create([
                'ruangan_id' => $data['ruangan_id'],
                'admin_id' => $request->user()->id,
                'tanggal' => $data['tanggal'],
                'status' => $data['status'] ?? 'selesai',
            ]);

            $sesi->details()->createMany($data['details']);

            return response()->json($sesi->load(['details', 'ruangan']), 201);
        });
    }

    /**
     * Riwayat sesi opname. Dipaginasi 15 per halaman.
     * Detail tidak di-eager-load di sini untuk efisiensi;
     * gunakan endpoint show per sesi jika detail diperlukan.
     */
    public function riwayat()
    {
        return OpnameSesi::with('ruangan')
            ->latest('tanggal')
            ->paginate(15);
    }
}
