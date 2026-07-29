<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BatchPersediaan;
use App\Models\DetailPemotonganBatch;
use App\Models\JenisBarang;
use App\Models\Persediaan;
use App\Models\TransaksiPersediaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PersediaanController extends Controller
{
    public function ringkas()
    {
        return Persediaan::query()
            ->join('jenis_barangs', 'jenis_barangs.id', '=', 'persediaans.jenis_barang_id')
            ->leftJoin('kategoris', 'kategoris.id', '=', 'jenis_barangs.kategori_id')
            ->leftJoin('batch_persediaans', 'batch_persediaans.persediaan_id', '=', 'persediaans.id')
            ->selectRaw('jenis_barangs.id as jenis_barang_id, jenis_barangs.nama_generik, kategoris.nama as kategori, count(distinct persediaans.id) as jumlah_varian, coalesce(sum(batch_persediaans.sisa_stok), 0) as total_stok, min(persediaans.stok_minimum) as stok_minimum')
            ->groupBy('jenis_barangs.id', 'jenis_barangs.nama_generik', 'kategoris.nama')
            ->orderBy('jenis_barangs.nama_generik')
            ->get();
    }

    public function detailByJenis(JenisBarang $jenisBarang)
    {
        return Persediaan::with(['jenisBarang.kategori', 'ruangan', 'batches' => fn ($q) => $q->orderBy('no_batch')])
            ->where('jenis_barang_id', $jenisBarang->id)
            ->get();
    }

    public function store(Request $request)
    {
        return response()->json(Persediaan::create($this->validated($request))->load(['jenisBarang.kategori', 'ruangan']), 201);
    }

    public function update(Request $request, Persediaan $persediaan)
    {
        $persediaan->update($this->validated($request));

        return $persediaan->load(['jenisBarang.kategori', 'ruangan']);
    }

    public function barangMasuk(Request $request, Persediaan $persediaan)
    {
        $data = $request->validate([
            'jumlah' => ['required', 'integer', 'min:1'],
            'tanggal' => ['required', 'date'],
            'harga_satuan' => ['required', 'numeric', 'min:0'],
        ]);

        return DB::transaction(function () use ($data, $persediaan) {
            $lastBatch = BatchPersediaan::where('persediaan_id', $persediaan->id)->lockForUpdate()->max('no_batch') ?? 0;
            $batch = BatchPersediaan::create([
                'persediaan_id' => $persediaan->id,
                'no_batch' => $lastBatch + 1,
                'tanggal_masuk' => $data['tanggal'],
                'jumlah_masuk' => $data['jumlah'],
                'harga_satuan' => $data['harga_satuan'],
                'sisa_stok' => $data['jumlah'],
            ]);

            $transaksi = TransaksiPersediaan::create([
                'persediaan_id' => $persediaan->id,
                'jenis' => 'masuk',
                'jumlah' => $data['jumlah'],
                'tanggal' => $data['tanggal'],
                'status' => 'disetujui',
                'tanggal_keputusan' => now(),
            ]);

            return response()->json(['batch' => $batch, 'transaksi' => $transaksi], 201);
        });
    }

    public function pengajuanKeluar(Request $request, Persediaan $persediaan)
    {
        $data = $request->validate([
            'jumlah' => ['required', 'integer', 'min:1'],
            'tanggal' => ['required', 'date'],
            'unit_kerja_penerima' => ['required', 'string'],
        ]);

        return response()->json(TransaksiPersediaan::create([
            'persediaan_id' => $persediaan->id,
            'jenis' => 'keluar',
            'jumlah' => $data['jumlah'],
            'tanggal' => $data['tanggal'],
            'unit_kerja_penerima' => $data['unit_kerja_penerima'],
            'diajukan_oleh' => $request->user()->id,
            'status' => 'menunggu',
        ]), 201);
    }

    public function pengajuan(Request $request)
    {
        return TransaksiPersediaan::with(['persediaan.jenisBarang', 'detailPemotongan.batch'])
            ->where('jenis', 'keluar')
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->latest()
            ->get();
    }

    public function setujui(Request $request, TransaksiPersediaan $transaksi)
    {
        if ($request->user()->role !== 'kasubbag') {
            return response()->json(['message' => 'Hanya Kasubbag yang dapat menyetujui.'], 403);
        }

        if ($transaksi->status !== 'menunggu' || $transaksi->jenis !== 'keluar') {
            return response()->json(['message' => 'Pengajuan tidak dapat diproses.'], 422);
        }

        return DB::transaction(function () use ($transaksi, $request) {
            $remaining = $transaksi->jumlah;
            $batches = BatchPersediaan::where('persediaan_id', $transaksi->persediaan_id)
                ->where('sisa_stok', '>', 0)
                ->orderBy('no_batch')
                ->lockForUpdate()
                ->get();

            if ($batches->sum('sisa_stok') < $remaining) {
                return response()->json(['message' => 'Stok tidak mencukupi untuk menyetujui pengajuan.'], 422);
            }

            foreach ($batches as $batch) {
                if ($remaining <= 0) {
                    break;
                }

                $taken = min($remaining, $batch->sisa_stok);
                $batch->decrement('sisa_stok', $taken);
                DetailPemotonganBatch::create([
                    'transaksi_persediaan_id' => $transaksi->id,
                    'batch_id' => $batch->id,
                    'jumlah_diambil' => $taken,
                    'harga_satuan_saat_itu' => $batch->harga_satuan,
                ]);
                $remaining -= $taken;
            }

            $transaksi->update([
                'status' => 'disetujui',
                'disetujui_oleh' => $request->user()->id,
                'tanggal_keputusan' => now(),
            ]);

            return $transaksi->load(['detailPemotongan.batch', 'persediaan.jenisBarang']);
        });
    }

    public function tolak(Request $request, TransaksiPersediaan $transaksi)
    {
        if ($request->user()->role !== 'kasubbag') {
            return response()->json(['message' => 'Hanya Kasubbag yang dapat menolak.'], 403);
        }

        $data = $request->validate(['catatan_penolakan' => ['required', 'string']]);
        $transaksi->update([
            'status' => 'ditolak',
            'disetujui_oleh' => $request->user()->id,
            'catatan_penolakan' => $data['catatan_penolakan'],
            'tanggal_keputusan' => now(),
        ]);

        return $transaksi;
    }

    public function batch(Persediaan $persediaan)
    {
        return $persediaan->batches()->orderBy('no_batch')->get();
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'jenis_barang_id' => ['required', 'exists:jenis_barangs,id'],
            'merk' => ['nullable', 'string'],
            'satuan' => ['required', 'string'],
            'stok_minimum' => ['required', 'integer', 'min:0'],
            'ruangan_id' => ['nullable', 'exists:ruangans,id'],
        ]);
    }
}
