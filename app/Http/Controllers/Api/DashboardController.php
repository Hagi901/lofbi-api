<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Aset;
use App\Models\Persediaan;
use App\Models\TransaksiPersediaan;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function summary()
    {
        $stokMenipis = Persediaan::query()
            ->leftJoin('batch_persediaans', 'batch_persediaans.persediaan_id', '=', 'persediaans.id')
            ->selectRaw('persediaans.id, persediaans.stok_minimum, coalesce(sum(batch_persediaans.sisa_stok), 0) as total_stok')
            ->groupBy('persediaans.id', 'persediaans.stok_minimum')
            ->havingRaw('coalesce(sum(batch_persediaans.sisa_stok), 0) < persediaans.stok_minimum')
            ->count();

        return [
            'total_aset' => Aset::count(),
            'total_nilai_buku' => Aset::sum('nilai_buku'),
            'total_jenis_persediaan' => Persediaan::distinct('jenis_barang_id')->count('jenis_barang_id'),
            'alert_barang_rusak' => Aset::whereIn('kondisi', ['rusak_ringan', 'rusak_berat'])->count(),
            'alert_stok_menipis' => $stokMenipis,
            'alert_pengajuan_menunggu' => TransaksiPersediaan::where('jenis', 'keluar')->where('status', 'menunggu')->count(),
            'distribusi_kondisi' => Aset::select('kondisi', DB::raw('count(*) as total'))->groupBy('kondisi')->get(),
            'distribusi_lokasi' => Aset::leftJoin('ruangans', 'ruangans.id', '=', 'asets.ruangan_id')
                ->selectRaw("coalesce(ruangans.nama, 'Tanpa lokasi') as ruangan, count(asets.id) as total")
                ->groupBy('ruangans.nama')
                ->get(),
        ];
    }
}
