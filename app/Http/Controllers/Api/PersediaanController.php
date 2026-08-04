<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BarangMasukRequest;
use App\Http\Requests\PengajuanKeluarRequest;
use App\Http\Requests\StorePersediaanRequest;
use App\Http\Resources\PengajuanResource;
use App\Models\BatchPersediaan;
use App\Models\DetailPemotonganBatch;
use App\Models\JenisBarang;
use App\Models\Persediaan;
use App\Models\TransaksiPersediaan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PersediaanController extends Controller
{
    /**
     * Ringkasan persediaan dikelompokkan per jenis barang.
     * Dipaginasi 20 item per halaman.
     */
    public function ringkas()
    {
        return Persediaan::query()
            ->join('jenis_barangs', 'jenis_barangs.id', '=', 'persediaans.jenis_barang_id')
            ->leftJoin('kategoris', 'kategoris.id', '=', 'jenis_barangs.kategori_id')
            ->leftJoin('batch_persediaans', 'batch_persediaans.persediaan_id', '=', 'persediaans.id')
            ->selectRaw('jenis_barangs.id as jenis_barang_id, jenis_barangs.nama_generik, kategoris.nama as kategori, count(distinct persediaans.id) as jumlah_varian, coalesce(sum(batch_persediaans.sisa_stok), 0) as total_stok, min(persediaans.stok_minimum) as stok_minimum')
            ->groupBy('jenis_barangs.id', 'jenis_barangs.nama_generik', 'kategoris.nama')
            ->orderBy('jenis_barangs.nama_generik')
            ->paginate(20);
    }

    public function detailByJenis(JenisBarang $jenisBarang)
    {
        return Persediaan::with(['jenisBarang.kategori', 'ruangan', 'batches' => fn ($q) => $q->orderBy('no_batch')])
            ->where('jenis_barang_id', $jenisBarang->id)
            ->get();
    }

    public function store(StorePersediaanRequest $request)
    {
        return response()->json(
            Persediaan::create($request->validated())->load(['jenisBarang.kategori', 'ruangan']),
            201
        );
    }

    public function update(StorePersediaanRequest $request, Persediaan $persediaan)
    {
        $persediaan->update($request->validated());

        return $persediaan->load(['jenisBarang.kategori', 'ruangan']);
    }

    public function barangMasuk(BarangMasukRequest $request, Persediaan $persediaan)
    {
        $data = $request->validated();

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

    /**
     * Buat pengajuan barang keluar.
     * Validasi kecukupan stok dilakukan SEBELUM pengajuan dibuat,
     * bukan saat approval, sehingga pengajuan yang pasti gagal tidak lolos masuk.
     */
    public function pengajuanKeluar(PengajuanKeluarRequest $request, Persediaan $persediaan)
    {
        $request->ensureStokCukup($persediaan);

        $data = $request->validated();

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

    /**
     * Daftar pengajuan barang keluar. Dapat difilter dengan ?status=menunggu
     * Dipaginasi 20 item per halaman.
     */
    public function pengajuan(Request $request)
    {
        $pengajuans = TransaksiPersediaan::with(['persediaan.jenisBarang', 'detailPemotongan.batch'])
            ->where('jenis', 'keluar')
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->latest()
            ->paginate(20);

        return PengajuanResource::collection($pengajuans);
    }

    /**
     * Kasubbag menyetujui pengajuan barang keluar.
     * Stok dipotong secara FIFO dari batch paling awal.
     * Proteksi role dilakukan oleh middleware 'role:kasubbag' di route.
     */
    public function setujui(TransaksiPersediaan $transaksi)
    {
        if ($transaksi->status !== 'menunggu' || $transaksi->jenis !== 'keluar') {
            return response()->json(['message' => 'Pengajuan tidak dapat diproses.'], 422);
        }

        return DB::transaction(function () use ($transaksi) {
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
                'diputuskan_oleh' => request()->user()->id,
                'tanggal_keputusan' => now(),
            ]);

            return new PengajuanResource(
                $transaksi->load(['detailPemotongan.batch', 'persediaan.jenisBarang'])
            );
        });
    }

    /**
     * Kasubbag menolak pengajuan barang keluar.
     * Catatan penolakan wajib diisi.
     * Proteksi role dilakukan oleh middleware 'role:kasubbag' di route.
     */
    public function tolak(Request $request, TransaksiPersediaan $transaksi)
    {
        $data = $request->validate([
            'catatan_penolakan' => ['required', 'string'],
        ]);

        $transaksi->update([
            'status' => 'ditolak',
            'diputuskan_oleh' => $request->user()->id,
            'catatan_penolakan' => $data['catatan_penolakan'],
            'tanggal_keputusan' => now(),
        ]);

        return new PengajuanResource($transaksi);
    }

    public function batch(Persediaan $persediaan)
    {
        return $persediaan->batches()->orderBy('no_batch')->get();
    }
}
