<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Aset;
use App\Models\OpnameSesi;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class LaporanController extends Controller
{
    public function baop(Request $request)
    {
        return OpnameSesi::with(['ruangan', 'details'])
            ->when($request->ruangan_id, fn ($q, $id) => $q->where('ruangan_id', $id))
            ->when($request->tanggal_mulai, fn ($q, $date) => $q->whereDate('tanggal', '>=', $date))
            ->when($request->tanggal_selesai, fn ($q, $date) => $q->whereDate('tanggal', '<=', $date))
            ->latest('tanggal')
            ->paginate(20);
    }

    public function dbr(Request $request)
    {
        return Aset::with(['jenisBarang.kategori', 'ruangan'])
            ->when($request->ruangan_id, fn ($q, $id) => $q->where('ruangan_id', $id))
            ->orderBy('ruangan_id')
            ->orderBy('kode_aset')
            ->paginate(50);
    }

    public function nilaiBuku(Request $request)
    {
        return Aset::with(['jenisBarang.kategori'])
            ->when($request->kategori_id, fn ($q, $id) => $q->whereHas('jenisBarang', fn ($inner) => $inner->where('kategori_id', $id)))
            ->select('id', 'jenis_barang_id', 'kode_aset', 'nilai_perolehan', 'akumulasi_penyusutan', 'nilai_buku', 'terakhir_dihitung_semester')
            ->orderBy('kode_aset')
            ->paginate(50);
    }

    /**
     * Export data laporan ke CSV atau JSON.
     *
     * CSV yang dihasilkan kini memiliki header baris pertama dan
     * nilai yang mengandung koma/newline dibungkus tanda kutip ganda (RFC 4180).
     */
    public function export(Request $request)
    {
        $jenis = $request->query('jenis', 'dbr');
        $format = $request->query('format', 'json');

        // Ambil data tanpa pagination untuk keperluan export
        $data = match ($jenis) {
            'baop' => OpnameSesi::with(['ruangan', 'details'])->latest('tanggal')->get(),
            'nilai-buku' => Aset::with(['jenisBarang.kategori'])
                ->select('id', 'jenis_barang_id', 'kode_aset', 'nilai_perolehan', 'akumulasi_penyusutan', 'nilai_buku', 'terakhir_dihitung_semester')
                ->orderBy('kode_aset')
                ->get(),
            default => Aset::with(['jenisBarang.kategori', 'ruangan'])->orderBy('kode_aset')->get(),
        };

        if ($format === 'csv' || $format === 'excel') {
            return $this->buildCsvResponse($data, $jenis);
        }

        return response()->json(['data' => $data]);
    }

    /**
     * Membangun response CSV yang proper:
     * - Baris pertama adalah header kolom
     * - Nilai yang mengandung koma, kutip, atau newline dibungkus kutip ganda
     * - Kutip ganda di dalam nilai di-escape dengan double-quote
     */
    private function buildCsvResponse($data, string $jenis): Response
    {
        if ($data->isEmpty()) {
            return response('', 204);
        }

        // Flatten satu level agar nested relation tidak menjadi array bertingkat
        $rows = $data->map(fn ($item) => $this->flattenForCsv($item->toArray()));

        // Header dari kunci baris pertama
        $headers = array_keys($rows->first());

        $lines = [];
        $lines[] = $this->toCsvLine($headers);
        foreach ($rows as $row) {
            $lines[] = $this->toCsvLine(array_values($row));
        }

        return response(implode("\n", $lines), 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"laporan_{$jenis}.csv\"",
        ]);
    }

    /**
     * Flatten array satu level: nested array dikonversi ke JSON string.
     */
    private function flattenForCsv(array $data): array
    {
        $result = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $result[$key] = json_encode($value, JSON_UNESCAPED_UNICODE);
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * Mengubah array nilai menjadi satu baris CSV berstandar RFC 4180.
     */
    private function toCsvLine(array $fields): string
    {
        return implode(',', array_map(function ($field) {
            $value = (string) ($field ?? '');
            // Bungkus dengan kutip jika mengandung koma, kutip, atau newline
            if (str_contains($value, ',') || str_contains($value, '"') || str_contains($value, "\n")) {
                $value = '"'.str_replace('"', '""', $value).'"';
            }

            return $value;
        }, $fields));
    }
}
