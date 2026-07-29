<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Aset;
use App\Models\OpnameSesi;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    public function baop(Request $request)
    {
        return OpnameSesi::with(['details'])
            ->when($request->ruangan_id, fn ($q, $id) => $q->where('ruangan_id', $id))
            ->when($request->tanggal_mulai, fn ($q, $date) => $q->whereDate('tanggal', '>=', $date))
            ->when($request->tanggal_selesai, fn ($q, $date) => $q->whereDate('tanggal', '<=', $date))
            ->latest('tanggal')
            ->get();
    }

    public function dbr(Request $request)
    {
        return Aset::with(['jenisBarang.kategori', 'ruangan'])
            ->when($request->ruangan_id, fn ($q, $id) => $q->where('ruangan_id', $id))
            ->orderBy('ruangan_id')
            ->orderBy('kode_aset')
            ->get();
    }

    public function nilaiBuku(Request $request)
    {
        return Aset::with(['jenisBarang.kategori'])
            ->when($request->kategori_id, fn ($q, $id) => $q->whereHas('jenisBarang', fn ($inner) => $inner->where('kategori_id', $id)))
            ->select('id', 'jenis_barang_id', 'kode_aset', 'nilai_perolehan', 'akumulasi_penyusutan', 'nilai_buku', 'terakhir_dihitung_semester')
            ->orderBy('kode_aset')
            ->get();
    }

    public function export(Request $request)
    {
        $format = $request->query('format', 'json');
        $data = match ($request->query('jenis', 'dbr')) {
            'baop' => $this->baop($request),
            'nilai-buku' => $this->nilaiBuku($request),
            default => $this->dbr($request),
        };

        if ($format === 'csv' || $format === 'excel') {
            $rows = $data->map(fn ($row) => collect($row->toArray())->flatten()->implode(','));
            return response($rows->implode("\n"), 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="laporan.csv"',
            ]);
        }

        return response()->json([
            'message' => 'Export PDF belum di-render server-side; gunakan data JSON ini untuk template frontend.',
            'data' => $data,
        ]);
    }
}
