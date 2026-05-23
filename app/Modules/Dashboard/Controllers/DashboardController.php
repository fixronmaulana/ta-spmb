<?php

namespace App\Modules\Dashboard\Controllers;

use App\Controllers\BaseController;
use App\Modules\Pendaftaran\Models\PendaftaranModel;
use App\Modules\MasterData\Models\PeriodeModel;
use App\Modules\Notifikasi\Models\NotifikasiModel;
use App\Modules\Verifikasi\Models\VerifikasiModel;
use App\Modules\BukuInduk\Models\BukuIndukModel;
use App\Modules\MasterData\Models\JurusanModel;

class DashboardController extends BaseController
{
    // =========================================================
    // CALON SISWA DASHBOARD
    // =========================================================
    public function calonSiswa()
    {
        $userId        = $this->userId();
        $pendaftaranM  = new PendaftaranModel();
        $periodeM      = new PeriodeModel();
        $notifikasiM   = new NotifikasiModel();

        $pendaftaran   = $pendaftaranM->getByUserId($userId);
        $periodeAktif  = $periodeM->getPeriodeAktif();
        $unreadCount   = $notifikasiM->countUnread($userId);
        $notifikasis   = $notifikasiM->getLatest($userId, 5);
        $progressSteps = $this->getProgressSteps($pendaftaran);

        return $this->render('App\Modules\Dashboard\Views\calon_siswa', [
            'title'         => 'Dashboard Saya',
            'pendaftaran'   => $pendaftaran,
            'periodeAktif'  => $periodeAktif,
            'unreadCount'   => $unreadCount,
            'notifikasis'   => $notifikasis,
            'progressSteps' => $progressSteps,
            'periodeInfo'   => $this->getPeriodeInfo($periodeAktif),
        ]);
    }

    // =========================================================
    // ADMIN TU DASHBOARD
    // =========================================================
    public function adminTu()
    {
        $pendaftaranM = new PendaftaranModel();
        $notifikasiM  = new NotifikasiModel();
        $periodeM     = new PeriodeModel();

        // Ambil semua periode untuk dropdown filter
        $allPeriode   = $periodeM->orderBy('tanggal_mulai', 'DESC')->findAll();
        $periodeAktif = $periodeM->getPeriodeAktif();

        // Periode yang dipilih: dari query string, default ke periode aktif
        $periodeIdFilter = (int) ($this->request->getGet('periode_id') ?? 0);
        if ($periodeIdFilter === 0 && $periodeAktif) {
            $periodeIdFilter = (int) $periodeAktif->id;
        }
        // null = tampilkan semua (jika tidak ada periode aktif maupun pilihan)
        $filterParam = $periodeIdFilter > 0 ? $periodeIdFilter : null;

        // Objek periode yang sedang difilter
        $periodeTerpilih = null;
        foreach ($allPeriode as $p) {
            if ((int) $p->id === $periodeIdFilter) {
                $periodeTerpilih = $p;
                break;
            }
        }

        $stats              = $pendaftaranM->getStatistikByStatusPerPeriode($filterParam);
        $pendaftaranTerbaru = $pendaftaranM->getPendaftaranTerbaru(10, $filterParam);
        $needVerifikasi     = $filterParam
            ? $pendaftaranM->where('periode_id', $filterParam)->where('status', 'submitted')->countAllResults()
            : $pendaftaranM->countByStatus('submitted');
        $unreadCount        = $notifikasiM->countUnread($this->userId());
        $statsByJurusan     = $pendaftaranM->getStatsByJurusanPerPeriode($filterParam);

        return $this->render('App\Modules\Dashboard\Views\admin_tu', [
            'title'              => 'Dashboard Admin',
            'stats'              => $stats,
            'pendaftaranTerbaru' => $pendaftaranTerbaru,
            'needVerifikasi'     => $needVerifikasi,
            'unreadCount'        => $unreadCount,
            'statsByJurusan'     => $statsByJurusan,
            // Filter periode
            'allPeriode'         => $allPeriode,
            'periodeAktif'       => $periodeAktif,
            'periodeIdFilter'    => $periodeIdFilter,
            'periodeTerpilih'    => $periodeTerpilih,
        ]);
    }

    // =========================================================
    // KEPALA SEKOLAH DASHBOARD — monitoring saja, tanpa approval
    // =========================================================
    public function kepalaSekolah()
    {
        $pendaftaranM = new PendaftaranModel();
        $bukuIndukM   = new BukuIndukModel();
        $periodeM     = new PeriodeModel();
        $jurusanM     = new JurusanModel();

        // ── KPI data ──────────────────────────────────────────
        $stats            = $pendaftaranM->getStatistikByStatus();
        $statsByJurusan   = $pendaftaranM->getStatsByJurusan();
        $statsByGelombang = $pendaftaranM->getStatsByGelombang();
        $totalSiswaAktif  = $bukuIndukM->countByStatus('aktif');
        $periodeAktif     = $periodeM->getPeriodeAktif();
        $jurusans         = $jurusanM->getAllActive();

        // ── KPI cards ─────────────────────────────────────────
        $totalPendaftar   = $stats['total'] ?? 0;
        $totalDiterima    = $stats['lulus'] ?? 0;
        $totalDaftarUlang = $stats['daftar_ulang'] ?? 0;

        $pctDiterima    = $totalPendaftar > 0
            ? number_format($totalDiterima / $totalPendaftar * 100, 1) . '%'
            : '0%';
        $pctDaftarUlang = $totalDiterima > 0
            ? number_format($totalDaftarUlang / $totalDiterima * 100, 1) . '%'
            : '0%';

        // ── Status verifikasi untuk horizontal bar chart ──────
        $statusVerifikasi = [
            ['name' => 'Terverifikasi', 'value' => ($stats['verifikasi'] ?? 0) + ($stats['seleksi'] ?? 0) + ($stats['lulus'] ?? 0)],
            ['name' => 'Menunggu',      'value' => $stats['submitted'] ?? 0],
            ['name' => 'Ditolak',       'value' => $stats['tidak_lulus'] ?? 0],
        ];

        // ── Distribusi per jurusan untuk pie chart ────────────
        $distribusiJurusan = [];
        foreach ($statsByJurusan as $row) {
            $distribusiJurusan[] = [
                'name'  => $row->kode ?? '',
                'value' => (int) ($row->total_daftar ?? 0),
            ];
        }

        // ── Gelombang untuk line chart ────────────────────────
        $gelombangData = [];
        foreach ($statsByGelombang as $row) {
            $gelombangData[] = [
                'name'  => $row->nama ?? 'Gelombang',
                'value' => (int) ($row->total ?? 0),
            ];
        }
        if (empty($gelombangData)) {
            $gelombangData = [
                ['name' => 'Gelombang 1', 'value' => 0],
            ];
        }

        return $this->render('App\Modules\Dashboard\Views\kepala_sekolah', [
            'title'             => 'Dashboard Monitoring',
            'stats'             => $stats,
            'statsByJurusan'    => $statsByJurusan,
            'gelombangData'     => $gelombangData,
            'distribusiJurusan' => $distribusiJurusan,
            'statusVerifikasi'  => $statusVerifikasi,
            'periodeAktif'      => $periodeAktif,
            'jurusans'          => $jurusans,
            // KPI values
            'totalPendaftar'    => $totalPendaftar,
            'totalDiterima'     => $totalDiterima,
            'pctDiterima'       => $pctDiterima,
            'totalDaftarUlang'  => $totalDaftarUlang,
            'pctDaftarUlang'    => $pctDaftarUlang,
            'totalSiswaAktif'   => $totalSiswaAktif,
        ]);
    }

    // =========================================================
    // PRIVATE HELPERS
    // =========================================================

    private function getProgressSteps(?object $pendaftaran): array
    {
        $steps = [
            1 => ['label' => 'Data Pribadi',   'done' => false, 'current' => false],
            2 => ['label' => 'Data Orang Tua', 'done' => false, 'current' => false],
            3 => ['label' => 'Data Akademik',  'done' => false, 'current' => false],
            4 => ['label' => 'Upload Dokumen', 'done' => false, 'current' => false],
            5 => ['label' => 'Review & Submit', 'done' => false, 'current' => false],
        ];

        if (! $pendaftaran) {
            $steps[1]['current'] = true;
            return $steps;
        }

        $step = $pendaftaran->step_terakhir ?? 1;
        for ($i = 1; $i < $step; $i++) {
            $steps[$i]['done'] = true;
        }
        if ($step <= 5) {
            $steps[$step]['current'] = true;
        }

        if (in_array($pendaftaran->status, ['submitted', 'verifikasi', 'seleksi', 'lulus', 'tidak_lulus', 'daftar_ulang', 'siswa_aktif'])) {
            foreach ($steps as &$s) {
                $s['done']    = true;
                $s['current'] = false;
            }
        }

        return $steps;
    }

    private function getPeriodeInfo(?object $periode): array
    {
        if (! $periode) {
            return ['status' => 'closed', 'message' => 'Tidak ada periode aktif saat ini.'];
        }

        $today   = date('Y-m-d');
        $mulai   = $periode->tanggal_mulai;
        $selesai = $periode->tanggal_selesai;

        if ($today < $mulai) {
            return ['status' => 'soon', 'message' => 'PPDB akan dibuka pada ' . format_tanggal($mulai)];
        }

        if ($today > $selesai) {
            return ['status' => 'closed', 'message' => 'Periode pendaftaran telah berakhir.'];
        }

        $sisa = (strtotime($selesai) - strtotime($today)) / 86400;
        return [
            'status'  => 'open',
            'message' => 'Periode pendaftaran sedang berjalan. Berakhir ' . format_tanggal($selesai),
            'sisa'    => (int) $sisa,
        ];
    }
}
