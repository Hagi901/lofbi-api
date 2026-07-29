<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Aset;
use App\Models\OpnameSesi;
use App\Models\Persediaan;
use App\Models\Ruangan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OpnameController extends Controller
{
    public function ruangan(Ruangan $ruangan)
    {
        return [
            'ruangan' => $ruangan,
            'aset' => Aset::with('jenisBarang')->where('ruangan_id', $ruangan->id)->orderBy('kode_aset')->get(),
            'persediaan' => Persediaan::with(['jenisBarang', 'batches'])
                ->where('ruangan_id', $ruangan->id)
                ->get()
                ->map(fn ($item) => $item->setAttribute('total_stok', $item->batches->sum('sisa_stok'))),
        ];
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'ruangan_id' => ['required', 'exists:ruangans,id'],
            'tanggal' => ['required', 'date'],
            'status' => ['sometimes', 'string'],
            'details' => ['required', 'array', 'min:1'],
            'details.*.aset_id' => ['nullable', 'exists:asets,id'],
            'details.*.persediaan_id' => ['nullable', 'exists:persediaans,id'],
            'details.*.kondisi_aktual' => ['nullable', 'string'],
            'details.*.jumlah_aktual' => ['nullable', 'integer', 'min:0'],
            'details.*.catatan' => ['nullable', 'string'],
        ]);

        return DB::transaction(function () use ($data, $request) {
            $sesi = OpnameSesi::create([
                'ruangan_id' => $data['ruangan_id'],
                'admin_id' => $request->user()->id,
                'tanggal' => $data['tanggal'],
                'status' => $data['status'] ?? 'selesai',
            ]);

            $sesi->details()->createMany($data['details']);

            return response()->json($sesi->load('details'), 201);
        });
    }

    public function riwayat()
    {
        return OpnameSesi::with(['details'])->latest('tanggal')->get();
    }
}
