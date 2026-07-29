<?php

namespace App\Console\Commands;

use App\Models\Aset;
use Illuminate\Support\Carbon;
use Illuminate\Console\Command;

class HitungPenyusutanAset extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lofbi:hitung-penyusutan {--tanggal= : Tanggal acuan YYYY-MM-DD}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Hitung ulang akumulasi penyusutan dan nilai buku aset per semester.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $date = $this->option('tanggal') ? Carbon::parse($this->option('tanggal')) : now();
        $semester = $date->month <= 6 ? 1 : 2;
        $semesterKey = $date->year.'-S'.$semester;
        $processed = 0;

        Aset::with('jenisBarang.kategori.masaManfaat')
            ->where(function ($query) use ($semesterKey) {
                $query->whereNull('terakhir_dihitung_semester')
                    ->orWhere('terakhir_dihitung_semester', '!=', $semesterKey);
            })
            ->chunkById(100, function ($asets) use ($semesterKey, &$processed) {
                foreach ($asets as $aset) {
                    $masaManfaat = $aset->jenisBarang?->kategori?->masaManfaat?->masa_manfaat_tahun;

                    if (! $masaManfaat || $masaManfaat <= 0) {
                        continue;
                    }

                    $penyusutanSemester = ((float) $aset->nilai_perolehan / $masaManfaat) / 2;
                    $akumulasi = min((float) $aset->nilai_perolehan, (float) $aset->akumulasi_penyusutan + $penyusutanSemester);

                    $aset->update([
                        'akumulasi_penyusutan' => $akumulasi,
                        'nilai_buku' => max(0, (float) $aset->nilai_perolehan - $akumulasi),
                        'terakhir_dihitung_semester' => $semesterKey,
                    ]);

                    $processed++;
                }
            });

        $this->info("Penyusutan {$semesterKey} selesai. {$processed} aset diproses.");

        return self::SUCCESS;
    }
}
