<?php

namespace App\Modules\Laporan\Controllers;

use App\Controllers\BaseController;
use App\Modules\Pendaftaran\Models\PendaftaranModel;
use App\Modules\BukuInduk\Models\BukuIndukModel;
use App\Modules\MasterData\Models\JurusanModel;
use App\Modules\MasterData\Models\PeriodeModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Dompdf\Dompdf;
use Dompdf\Options;

class LaporanController extends BaseController
{
    protected PendaftaranModel $pendaftaranModel;
    protected BukuIndukModel   $bukuIndukModel;
    protected JurusanModel     $jurusanModel;
    protected PeriodeModel     $periodeModel;

    public function __construct()
    {
        $this->pendaftaranModel = new PendaftaranModel();
        $this->bukuIndukModel   = new BukuIndukModel();
        $this->jurusanModel     = new JurusanModel();
        $this->periodeModel     = new PeriodeModel();
    }

    // =========================================================
    // INDEX — Laporan Rekapitulasi
    // Route: kepala-sekolah/laporan
    // =========================================================
    public function index()
    {
        $db          = db_connect();
        $stats       = $this->pendaftaranModel->getStatistikByStatus();
        $byJurusan   = $this->pendaftaranModel->getStatsByJurusan();
        $periode     = $this->periodeModel->getPeriodeAktif();
        $periodes    = $this->periodeModel->orderBy('tahun_ajaran', 'DESC')->findAll();

        // ── KPI summary ────────────────────────────────────────
        $kpi = ['pendaftar' => 0, 'diterima' => 0, 'daftar_ulang' => 0, 'siswa_aktif' => 0];
        foreach ($byJurusan as $row) {
            $kpi['pendaftar']    += (int) ($row->total_daftar        ?? 0);
            $kpi['diterima']     += (int) ($row->total_lulus         ?? 0);
            $kpi['daftar_ulang'] += (int) ($row->total_daftar_ulang  ?? 0);
            $kpi['siswa_aktif']  += (int) ($row->total_siswa_aktif   ?? 0);
        }

        // ── Tab: Per Gelombang ─────────────────────────────────
        $byGelombang = $db->query("
            SELECT
                p.nama                                                                          AS gelombang,
                p.id                                                                            AS periode_id,
                COUNT(pend.id)                                                                  AS pendaftar,
                SUM(CASE WHEN pend.status IN ('lulus','daftar_ulang','siswa_aktif') THEN 1 ELSE 0 END) AS diterima,
                SUM(CASE WHEN pend.status = 'tidak_lulus'                           THEN 1 ELSE 0 END) AS ditolak,
                SUM(CASE WHEN pend.status IN ('submitted','verifikasi','seleksi')   THEN 1 ELSE 0 END) AS menunggu
            FROM periode p
            LEFT JOIN pendaftaran pend
                ON  pend.periode_id = p.id
                AND pend.deleted_at IS NULL
            GROUP BY p.id
            ORDER BY p.tanggal_mulai ASC
        ")->getResultObject();

        // ── Tab: Demografi ─────────────────────────────────────
        $genderData = $db->query("
            SELECT
                CASE dds.jenis_kelamin WHEN 'L' THEN 'Laki-laki' ELSE 'Perempuan' END AS nama,
                COUNT(*) AS total
            FROM data_diri_siswas dds
            INNER JOIN pendaftaran p ON p.id = dds.pendaftaran_id AND p.deleted_at IS NULL
            WHERE dds.jenis_kelamin IS NOT NULL
            GROUP BY dds.jenis_kelamin
        ")->getResultObject();

        $asalSekolah = $db->query("
            SELECT dds.asal_sekolah AS nama, COUNT(*) AS total
            FROM data_diri_siswas dds
            INNER JOIN pendaftaran p ON p.id = dds.pendaftaran_id AND p.deleted_at IS NULL
            WHERE dds.asal_sekolah IS NOT NULL AND dds.asal_sekolah != ''
            GROUP BY dds.asal_sekolah
            ORDER BY total DESC
            LIMIT 5
        ")->getResultObject();

        // ── Tab: Tren Tahunan ──────────────────────────────────
        $trenTahunan = $db->query("
            SELECT
                p.tahun_ajaran,
                COUNT(pend.id)                                                                  AS pendaftar,
                SUM(CASE WHEN pend.status IN ('lulus','daftar_ulang','siswa_aktif') THEN 1 ELSE 0 END) AS diterima
            FROM periode p
            LEFT JOIN pendaftaran pend ON pend.periode_id = p.id AND pend.deleted_at IS NULL
            GROUP BY p.id
            ORDER BY p.tanggal_mulai ASC
        ")->getResultObject();

        return $this->render('App\Modules\Laporan\Views\index', [
            'title'       => 'Laporan Rekapitulasi',
            'stats'       => $stats,
            'byJurusan'   => $byJurusan,
            'byGelombang' => $byGelombang,
            'genderData'  => $genderData,
            'asalSekolah' => $asalSekolah,
            'trenTahunan' => $trenTahunan,
            'periode'     => $periode,
            'periodes'    => $periodes,
            'kpi'         => $kpi,
        ]);
    }

    // =========================================================
    // ARSIP LAPORAN
    // Route: kepala-sekolah/laporan/arsip
    // =========================================================
    public function arsip()
    {
        $db        = db_connect();
        $periodes  = $this->periodeModel->orderBy('tahun_ajaran', 'DESC')->findAll();
        $jurusans  = $this->jurusanModel->getAllActive();

        $searchQ    = $this->request->getGet('search') ?? '';
        $filterTA   = $this->request->getGet('tahun_ajaran') ?? 'semua';
        $filterTipe = $this->request->getGet('tipe') ?? 'semua';

        $arsipList = [];

        $statsPerPeriode = $db->query("
            SELECT
                pend.periode_id,
                COUNT(pend.id)                                                                  AS total_pendaftar,
                SUM(CASE WHEN pend.status IN ('lulus','daftar_ulang','siswa_aktif') THEN 1 ELSE 0 END) AS total_diterima,
                SUM(CASE WHEN pend.status = 'siswa_aktif'                           THEN 1 ELSE 0 END) AS total_siswa_aktif
            FROM pendaftaran pend
            WHERE pend.deleted_at IS NULL
            GROUP BY pend.periode_id
        ")->getResultObject();

        $statsIdx = [];
        foreach ($statsPerPeriode as $s) {
            $statsIdx[$s->periode_id] = $s;
        }

        foreach ($periodes as $p) {
            $stat       = $statsIdx[$p->id] ?? null;
            $tPendaftar = (int) ($stat->total_pendaftar   ?? 0);
            $tDiterima  = (int) ($stat->total_diterima    ?? 0);
            $tAktif     = (int) ($stat->total_siswa_aktif ?? 0);
            $tglGenerate = $p->tanggal_selesai ?? $p->created_at ?? date('Y-m-d');

            $arsipList[] = [
                'id'               => 'rekap-' . $p->id,
                'periode_id'       => $p->id,
                'tahun_ajaran'     => $p->tahun_ajaran,
                'judul'            => 'Laporan Rekapitulasi SPMB TA ' . $p->tahun_ajaran,
                'tipe'             => 'rekapitulasi',
                'format'           => 'pdf',
                'tanggal'          => $tglGenerate,
                'total_pendaftar'  => $tPendaftar,
                'total_diterima'   => $tDiterima,
                'total_siswa_aktif' => $tAktif,
                'url_download'     => base_url('kepala-sekolah/laporan/ekspor-pdf?tab=jurusan'),
            ];

            $arsipList[] = [
                'id'               => 'jurusan-' . $p->id,
                'periode_id'       => $p->id,
                'tahun_ajaran'     => $p->tahun_ajaran,
                'judul'            => 'Laporan Rekapitulasi per Jurusan TA ' . $p->tahun_ajaran,
                'tipe'             => 'jurusan',
                'format'           => 'excel',
                'tanggal'          => $tglGenerate,
                'total_pendaftar'  => $tPendaftar,
                'total_diterima'   => $tDiterima,
                'total_siswa_aktif' => $tAktif,
                'url_download'     => base_url('kepala-sekolah/laporan/ekspor-excel?tab=jurusan'),
            ];

            if ($p->tanggal_selesai && $p->tanggal_selesai < date('Y-m-d')) {
                $arsipList[] = [
                    'id'               => 'akhir-' . $p->id,
                    'periode_id'       => $p->id,
                    'tahun_ajaran'     => $p->tahun_ajaran,
                    'judul'            => 'Laporan Akhir SPMB TA ' . $p->tahun_ajaran,
                    'tipe'             => 'akhir',
                    'format'           => 'pdf',
                    'tanggal'          => $p->tanggal_selesai,
                    'total_pendaftar'  => $tPendaftar,
                    'total_diterima'   => $tDiterima,
                    'total_siswa_aktif' => $tAktif,
                    'url_download'     => base_url('kepala-sekolah/laporan/ekspor-pdf?tab=jurusan'),
                ];
            }
        }

        if ($searchQ !== '') {
            $arsipList = array_filter(
                $arsipList,
                fn($a) => str_contains(mb_strtolower($a['judul']), mb_strtolower($searchQ))
            );
        }
        if ($filterTA !== 'semua') {
            $arsipList = array_filter($arsipList, fn($a) => $a['tahun_ajaran'] === $filterTA);
        }
        if ($filterTipe !== 'semua') {
            $arsipList = array_filter($arsipList, fn($a) => $a['tipe'] === $filterTipe);
        }
        $arsipList = array_values($arsipList);

        $totalLaporan = count($arsipList);
        $totalTA      = count(array_unique(array_column($arsipList, 'tahun_ajaran')));
        $totalAkhir   = count(array_filter($arsipList, fn($a) => $a['tipe'] === 'akhir'));

        return $this->render('App\Modules\Laporan\Views\arsip', [
            'title'        => 'Arsip Laporan',
            'periodes'     => $periodes,
            'jurusans'     => $jurusans,
            'arsipList'    => $arsipList,
            'searchQ'      => $searchQ,
            'filterTA'     => $filterTA,
            'filterTipe'   => $filterTipe,
            'totalLaporan' => $totalLaporan,
            'totalTA'      => $totalTA,
            'totalAkhir'   => $totalAkhir,
        ]);
    }

    // =========================================================
    // HELPER — Ambil semua data yang dibutuhkan untuk ekspor
    // =========================================================
    private function getDataEkspor(): array
    {
        $db = db_connect();

        $stats     = $this->pendaftaranModel->getStatistikByStatus();
        $byJurusan = $this->pendaftaranModel->getStatsByJurusan();
        $periode   = $this->periodeModel->getPeriodeAktif();

        $byGelombang = $db->query("
            SELECT
                p.nama AS gelombang,
                p.id   AS periode_id,
                COUNT(pend.id) AS pendaftar,
                SUM(CASE WHEN pend.status IN ('lulus','daftar_ulang','siswa_aktif') THEN 1 ELSE 0 END) AS diterima,
                SUM(CASE WHEN pend.status = 'tidak_lulus' THEN 1 ELSE 0 END) AS ditolak,
                SUM(CASE WHEN pend.status IN ('submitted','verifikasi','seleksi') THEN 1 ELSE 0 END) AS menunggu
            FROM periode p
            LEFT JOIN pendaftaran pend ON pend.periode_id = p.id AND pend.deleted_at IS NULL
            GROUP BY p.id
            ORDER BY p.tanggal_mulai ASC
        ")->getResultObject();

        $genderData = $db->query("
            SELECT
                CASE dds.jenis_kelamin WHEN 'L' THEN 'Laki-laki' ELSE 'Perempuan' END AS nama,
                COUNT(*) AS total
            FROM data_diri_siswas dds
            INNER JOIN pendaftaran p ON p.id = dds.pendaftaran_id AND p.deleted_at IS NULL
            WHERE dds.jenis_kelamin IS NOT NULL
            GROUP BY dds.jenis_kelamin
        ")->getResultObject();

        $asalSekolah = $db->query("
            SELECT dds.asal_sekolah AS nama, COUNT(*) AS total
            FROM data_diri_siswas dds
            INNER JOIN pendaftaran p ON p.id = dds.pendaftaran_id AND p.deleted_at IS NULL
            WHERE dds.asal_sekolah IS NOT NULL AND dds.asal_sekolah != ''
            GROUP BY dds.asal_sekolah
            ORDER BY total DESC
            LIMIT 10
        ")->getResultObject();

        $agamaData = $db->query("
            SELECT dds.agama AS nama, COUNT(*) AS total
            FROM data_diri_siswas dds
            INNER JOIN pendaftaran p ON p.id = dds.pendaftaran_id AND p.deleted_at IS NULL
            WHERE dds.agama IS NOT NULL AND dds.agama != ''
            GROUP BY dds.agama
            ORDER BY total DESC
        ")->getResultObject();

        $trenTahunan = $db->query("
            SELECT
                p.tahun_ajaran,
                COUNT(pend.id) AS pendaftar,
                SUM(CASE WHEN pend.status IN ('lulus','daftar_ulang','siswa_aktif') THEN 1 ELSE 0 END) AS diterima,
                SUM(CASE WHEN pend.status = 'tidak_lulus' THEN 1 ELSE 0 END) AS ditolak
            FROM periode p
            LEFT JOIN pendaftaran pend ON pend.periode_id = p.id AND pend.deleted_at IS NULL
            GROUP BY p.id
            ORDER BY p.tanggal_mulai ASC
        ")->getResultObject();

        return compact(
            'stats',
            'byJurusan',
            'byGelombang',
            'genderData',
            'asalSekolah',
            'agamaData',
            'trenTahunan',
            'periode'
        );
    }

    // =========================================================
    // EKSPOR PDF — Tab-aware
    // Route : kepala-sekolah/laporan/ekspor-pdf?tab=jurusan|gelombang|demografi|tren
    // =========================================================
    public function eksporPdf()
    {
        $tab  = $this->request->getGet('tab') ?? 'jurusan';
        $data = $this->getDataEkspor();
        $data['tglCetak'] = date('d/m/Y H:i');
        $data['tab']      = $tab;

        // Pilih view sesuai tab
        $viewMap = [
            'jurusan'   => 'App\Modules\Laporan\Views\pdf_jurusan',
            'gelombang' => 'App\Modules\Laporan\Views\pdf_gelombang',
            'demografi' => 'App\Modules\Laporan\Views\pdf_demografi',
            'tren'      => 'App\Modules\Laporan\Views\pdf_tren',
        ];

        $viewName = $viewMap[$tab] ?? $viewMap['jurusan'];
        $html     = view($viewName, $data);

        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'laporan_' . $tab . '_' . date('Ymd_His') . '.pdf';
        $dompdf->stream($filename, ['Attachment' => true]);
    }

    // =========================================================
    // EKSPOR EXCEL — Tab-aware
    // Route : kepala-sekolah/laporan/ekspor-excel?tab=jurusan|gelombang|demografi|tren
    // =========================================================
    public function eksporExcel()
    {
        $tab  = $this->request->getGet('tab') ?? 'jurusan';
        $data = $this->getDataEkspor();

        switch ($tab) {
            case 'gelombang':
                $this->eksporExcelGelombang($data);
                break;
            case 'demografi':
                $this->eksporExcelDemografi($data);
                break;
            case 'tren':
                $this->eksporExcelTren($data);
                break;
            default: // jurusan
                $this->eksporExcelJurusan($data);
                break;
        }
    }

    // ---------------------------------------------------------
    // Excel: Per Jurusan
    // ---------------------------------------------------------
    private function eksporExcelJurusan(array $data): void
    {
        $byJurusan = $data['byJurusan'];
        $periode   = $data['periode'];

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Per Jurusan');

        // Judul
        $sheet->mergeCells('A1:H1');
        $sheet->setCellValue('A1', 'SMK Al-Munawwir IIBS — Rekap Per Program Keahlian');
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 13, 'color' => ['rgb' => '1D4ED8']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $sheet->mergeCells('A2:H2');
        $sheet->setCellValue('A2', 'Periode: ' . ($periode->nama ?? '-') . ' (' . ($periode->tahun_ajaran ?? '-') . ')   |   Dicetak: ' . date('d/m/Y H:i'));
        $sheet->getStyle('A2')->applyFromArray([
            'font'      => ['size' => 9, 'color' => ['rgb' => '6B7280']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Header kolom
        $headers = ['No', 'Program Keahlian', 'Kode', 'Kuota', 'Pendaftar', 'Diterima', 'Daftar Ulang', '% Terisi'];
        foreach ($headers as $i => $h) {
            $col = chr(65 + $i);
            $sheet->setCellValue($col . '4', $h);
            $sheet->getStyle($col . '4')->applyFromArray([
                'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1D4ED8']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']]],
            ]);
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Data
        $totKuota = $totPdft = $totDtrm = $totDU = 0;
        foreach ($byJurusan as $i => $row) {
            $r   = $i + 5;
            $pct = $row->kuota > 0 ? round($row->total_lulus / $row->kuota * 100) : 0;
            $sheet->setCellValue('A' . $r, $i + 1);
            $sheet->setCellValue('B' . $r, $row->jurusan ?? '-');
            $sheet->setCellValue('C' . $r, $row->kode ?? '-');
            $sheet->setCellValue('D' . $r, (int) ($row->kuota ?? 0));
            $sheet->setCellValue('E' . $r, (int) ($row->total_daftar ?? 0));
            $sheet->setCellValue('F' . $r, (int) ($row->total_lulus ?? 0));
            $sheet->setCellValue('G' . $r, (int) ($row->total_daftar_ulang ?? 0));
            $sheet->setCellValue('H' . $r, $pct . '%');

            if ($i % 2 === 1) {
                $sheet->getStyle('A' . $r . ':H' . $r)->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F3F4F6']],
                ]);
            }

            $totKuota += (int) ($row->kuota ?? 0);
            $totPdft  += (int) ($row->total_daftar ?? 0);
            $totDtrm  += (int) ($row->total_lulus ?? 0);
            $totDU    += (int) ($row->total_daftar_ulang ?? 0);
        }

        // Total row
        $rTotal = count($byJurusan) + 5;
        $sheet->setCellValue('A' . $rTotal, '');
        $sheet->setCellValue('B' . $rTotal, 'TOTAL');
        $sheet->setCellValue('D' . $rTotal, $totKuota);
        $sheet->setCellValue('E' . $rTotal, $totPdft);
        $sheet->setCellValue('F' . $rTotal, $totDtrm);
        $sheet->setCellValue('G' . $rTotal, $totDU);
        $sheet->setCellValue('H' . $rTotal, ($totKuota > 0 ? round($totDtrm / $totKuota * 100) : 0) . '%');
        $sheet->getStyle('A' . $rTotal . ':H' . $rTotal)->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DBEAFE']],
        ]);

        $sheet->freezePane('A5');
        $this->outputExcel($spreadsheet, 'laporan_per_jurusan_' . date('Ymd_His'));
    }

    // ---------------------------------------------------------
    // Excel: Per Gelombang
    // ---------------------------------------------------------
    private function eksporExcelGelombang(array $data): void
    {
        $byGelombang = $data['byGelombang'];
        $periode     = $data['periode'];

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Per Gelombang');

        $sheet->mergeCells('A1:F1');
        $sheet->setCellValue('A1', 'SMK Al-Munawwir IIBS — Rekap Per Gelombang Pendaftaran');
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 13, 'color' => ['rgb' => '059669']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $sheet->mergeCells('A2:F2');
        $sheet->setCellValue('A2', 'Dicetak: ' . date('d/m/Y H:i'));
        $sheet->getStyle('A2')->applyFromArray([
            'font'      => ['size' => 9, 'color' => ['rgb' => '6B7280']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $headers = ['No', 'Nama Gelombang', 'Pendaftar', 'Diterima', 'Ditolak', 'Menunggu'];
        foreach ($headers as $i => $h) {
            $col = chr(65 + $i);
            $sheet->setCellValue($col . '4', $h);
            $sheet->getStyle($col . '4')->applyFromArray([
                'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '059669']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $totPdft = $totDtrm = $totDtlk = $totMngg = 0;
        foreach ($byGelombang as $i => $row) {
            $r = $i + 5;
            $sheet->setCellValue('A' . $r, $i + 1);
            $sheet->setCellValue('B' . $r, $row->gelombang ?? '-');
            $sheet->setCellValue('C' . $r, (int) ($row->pendaftar ?? 0));
            $sheet->setCellValue('D' . $r, (int) ($row->diterima  ?? 0));
            $sheet->setCellValue('E' . $r, (int) ($row->ditolak   ?? 0));
            $sheet->setCellValue('F' . $r, (int) ($row->menunggu  ?? 0));

            if ($i % 2 === 1) {
                $sheet->getStyle('A' . $r . ':F' . $r)->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F0FDF4']],
                ]);
            }

            $totPdft += (int) ($row->pendaftar ?? 0);
            $totDtrm += (int) ($row->diterima  ?? 0);
            $totDtlk += (int) ($row->ditolak   ?? 0);
            $totMngg += (int) ($row->menunggu  ?? 0);
        }

        $rTotal = count($byGelombang) + 5;
        $sheet->setCellValue('B' . $rTotal, 'TOTAL');
        $sheet->setCellValue('C' . $rTotal, $totPdft);
        $sheet->setCellValue('D' . $rTotal, $totDtrm);
        $sheet->setCellValue('E' . $rTotal, $totDtlk);
        $sheet->setCellValue('F' . $rTotal, $totMngg);
        $sheet->getStyle('A' . $rTotal . ':F' . $rTotal)->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D1FAE5']],
        ]);

        $sheet->freezePane('A5');
        $this->outputExcel($spreadsheet, 'laporan_per_gelombang_' . date('Ymd_His'));
    }

    // ---------------------------------------------------------
    // Excel: Demografi
    // ---------------------------------------------------------
    private function eksporExcelDemografi(array $data): void
    {
        $genderData  = $data['genderData'];
        $asalSekolah = $data['asalSekolah'];
        $agamaData   = $data['agamaData'];

        $spreadsheet = new Spreadsheet();

        // --- Sheet 1: Jenis Kelamin ---
        $sheet1 = $spreadsheet->getActiveSheet();
        $sheet1->setTitle('Jenis Kelamin');

        $sheet1->mergeCells('A1:C1');
        $sheet1->setCellValue('A1', 'SMK Al-Munawwir IIBS — Demografi Pendaftar');
        $sheet1->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 13, 'color' => ['rgb' => '7C3AED']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet1->mergeCells('A2:C2');
        $sheet1->setCellValue('A2', 'Dicetak: ' . date('d/m/Y H:i'));
        $sheet1->getStyle('A2')->applyFromArray([
            'font'      => ['size' => 9, 'color' => ['rgb' => '6B7280']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        foreach (['A' => 'Jenis Kelamin', 'B' => 'Jumlah', 'C' => 'Persentase'] as $col => $h) {
            $sheet1->setCellValue($col . '4', $h);
            $sheet1->getStyle($col . '4')->applyFromArray([
                'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '7C3AED']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);
            $sheet1->getColumnDimension($col)->setAutoSize(true);
        }

        $totalGender = array_sum(array_map(fn($r) => (int) $r->total, $genderData));
        foreach ($genderData as $i => $row) {
            $r   = $i + 5;
            $pct = $totalGender > 0 ? round($row->total / $totalGender * 100, 1) : 0;
            $sheet1->setCellValue('A' . $r, $row->nama);
            $sheet1->setCellValue('B' . $r, (int) $row->total);
            $sheet1->setCellValue('C' . $r, $pct . '%');
        }

        // --- Sheet 2: Asal Sekolah ---
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Asal Sekolah');

        $sheet2->mergeCells('A1:C1');
        $sheet2->setCellValue('A1', 'SMK Al-Munawwir IIBS — Top Asal Sekolah Pendaftar');
        $sheet2->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 13, 'color' => ['rgb' => '7C3AED']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet2->mergeCells('A2:C2');
        $sheet2->setCellValue('A2', 'Dicetak: ' . date('d/m/Y H:i'));
        $sheet2->getStyle('A2')->applyFromArray([
            'font'      => ['size' => 9, 'color' => ['rgb' => '6B7280']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        foreach (['A' => 'No', 'B' => 'Asal Sekolah', 'C' => 'Jumlah Pendaftar'] as $col => $h) {
            $sheet2->setCellValue($col . '4', $h);
            $sheet2->getStyle($col . '4')->applyFromArray([
                'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '7C3AED']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);
            $sheet2->getColumnDimension($col)->setAutoSize(true);
        }

        foreach ($asalSekolah as $i => $row) {
            $r = $i + 5;
            $sheet2->setCellValue('A' . $r, $i + 1);
            $sheet2->setCellValue('B' . $r, $row->nama ?? '-');
            $sheet2->setCellValue('C' . $r, (int) $row->total);
            if ($i % 2 === 1) {
                $sheet2->getStyle('A' . $r . ':C' . $r)->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F5F3FF']],
                ]);
            }
        }

        // --- Sheet 3: Agama ---
        $sheet3 = $spreadsheet->createSheet();
        $sheet3->setTitle('Agama');

        $sheet3->mergeCells('A1:C1');
        $sheet3->setCellValue('A1', 'SMK Al-Munawwir IIBS — Distribusi Agama Pendaftar');
        $sheet3->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 13, 'color' => ['rgb' => '7C3AED']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet3->mergeCells('A2:C2');
        $sheet3->setCellValue('A2', 'Dicetak: ' . date('d/m/Y H:i'));
        $sheet3->getStyle('A2')->applyFromArray([
            'font'      => ['size' => 9, 'color' => ['rgb' => '6B7280']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        foreach (['A' => 'Agama', 'B' => 'Jumlah', 'C' => 'Persentase'] as $col => $h) {
            $sheet3->setCellValue($col . '4', $h);
            $sheet3->getStyle($col . '4')->applyFromArray([
                'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '7C3AED']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);
            $sheet3->getColumnDimension($col)->setAutoSize(true);
        }

        $totalAgama = array_sum(array_map(fn($r) => (int) $r->total, $agamaData));
        foreach ($agamaData as $i => $row) {
            $r   = $i + 5;
            $pct = $totalAgama > 0 ? round($row->total / $totalAgama * 100, 1) : 0;
            $sheet3->setCellValue('A' . $r, ucfirst(strtolower($row->nama ?? '-')));
            $sheet3->setCellValue('B' . $r, (int) $row->total);
            $sheet3->setCellValue('C' . $r, $pct . '%');
        }

        $spreadsheet->setActiveSheetIndex(0);
        $this->outputExcel($spreadsheet, 'laporan_demografi_' . date('Ymd_His'));
    }

    // ---------------------------------------------------------
    // Excel: Tren Tahunan
    // ---------------------------------------------------------
    private function eksporExcelTren(array $data): void
    {
        $trenTahunan = $data['trenTahunan'];

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Tren Tahunan');

        $sheet->mergeCells('A1:F1');
        $sheet->setCellValue('A1', 'SMK Al-Munawwir IIBS — Tren Pendaftaran Tahunan');
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 13, 'color' => ['rgb' => 'B45309']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->mergeCells('A2:F2');
        $sheet->setCellValue('A2', 'Dicetak: ' . date('d/m/Y H:i'));
        $sheet->getStyle('A2')->applyFromArray([
            'font'      => ['size' => 9, 'color' => ['rgb' => '6B7280']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $headers = ['No', 'Tahun Ajaran', 'Pendaftar', 'Diterima', 'Ditolak', 'Pertumbuhan (%)'];
        foreach ($headers as $i => $h) {
            $col = chr(65 + $i);
            $sheet->setCellValue($col . '4', $h);
            $sheet->getStyle($col . '4')->applyFromArray([
                'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'B45309']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        foreach ($trenTahunan as $i => $row) {
            $r    = $i + 5;
            $prev = $trenTahunan[$i - 1] ?? null;

            if ($prev && (int) $prev->pendaftar > 0) {
                $growth = round(((int) $row->pendaftar - (int) $prev->pendaftar) / (int) $prev->pendaftar * 100, 1);
                $growthStr = ($growth >= 0 ? '+' : '') . $growth . '%';
            } else {
                $growthStr = '-';
            }

            $sheet->setCellValue('A' . $r, $i + 1);
            $sheet->setCellValue('B' . $r, $row->tahun_ajaran ?? '-');
            $sheet->setCellValue('C' . $r, (int) ($row->pendaftar ?? 0));
            $sheet->setCellValue('D' . $r, (int) ($row->diterima  ?? 0));
            $sheet->setCellValue('E' . $r, (int) ($row->ditolak   ?? 0));
            $sheet->setCellValue('F' . $r, $growthStr);

            if ($i % 2 === 1) {
                $sheet->getStyle('A' . $r . ':F' . $r)->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FEF3C7']],
                ]);
            }
        }

        $sheet->freezePane('A5');
        $this->outputExcel($spreadsheet, 'laporan_tren_tahunan_' . date('Ymd_His'));
    }

    // ---------------------------------------------------------
    // Helper: output xlsx ke browser
    // ---------------------------------------------------------
    private function outputExcel(Spreadsheet $spreadsheet, string $filenameBase): void
    {
        $writer   = new Xlsx($spreadsheet);
        $filename = $filenameBase . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    // =========================================================
    // EKSPOR EXCEL — Rekap Pendaftaran (daftar lengkap)
    // =========================================================
    public function eksporRekapExcel()
    {
        $rows = $this->pendaftaranModel
            ->select('pendaftaran.*, u.nama_lengkap as nama_akun, u.email,
                      dds.nama_lengkap, dds.nisn, dds.jenis_kelamin, dds.tanggal_lahir,
                      dds.asal_sekolah, dds.no_hp,
                      j1.nama as jurusan1, j2.nama as jurusan2, jd.nama as jurusan_diterima')
            ->join('users u', 'u.id = pendaftaran.user_id')
            ->join('data_diri_siswas dds', 'dds.pendaftaran_id = pendaftaran.id', 'left')
            ->join('jurusan j1', 'j1.id = pendaftaran.jurusan_pilihan1_id', 'left')
            ->join('jurusan j2', 'j2.id = pendaftaran.jurusan_pilihan2_id', 'left')
            ->join('jurusan jd', 'jd.id = pendaftaran.jurusan_diterima_id', 'left')
            ->where('pendaftaran.deleted_at IS NULL')
            ->orderBy('pendaftaran.created_at', 'ASC')
            ->findAll();

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Rekap Pendaftaran');

        $headers = [
            'No',
            'No. Pendaftaran',
            'Nama Lengkap',
            'NISN',
            'L/P',
            'Asal Sekolah',
            'Pilihan 1',
            'Pilihan 2',
            'Jurusan Diterima',
            'Status',
            'Tgl Submit',
        ];
        foreach ($headers as $i => $header) {
            $col = chr(65 + $i);
            $sheet->setCellValue($col . '1', $header);
            $sheet->getStyle($col . '1')->applyFromArray([
                'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1D4ED8']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        foreach ($rows as $i => $row) {
            $r = $i + 2;
            $sheet->setCellValue('A' . $r, $i + 1);
            $sheet->setCellValue('B' . $r, $row->no_pendaftaran ?? '-');
            $sheet->setCellValue('C' . $r, $row->nama_lengkap ?? $row->nama_akun);
            $sheet->setCellValue('D' . $r, $row->nisn ?? '-');
            $sheet->setCellValue('E' . $r, $row->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan');
            $sheet->setCellValue('F' . $r, $row->asal_sekolah ?? '-');
            $sheet->setCellValue('G' . $r, $row->jurusan1 ?? '-');
            $sheet->setCellValue('H' . $r, $row->jurusan2 ?? '-');
            $sheet->setCellValue('I' . $r, $row->jurusan_diterima ?? '-');
            $sheet->setCellValue('J' . $r, ucwords(str_replace('_', ' ', $row->status)));
            $sheet->setCellValue('K' . $r, $row->submitted_at ? date('d/m/Y', strtotime($row->submitted_at)) : '-');
        }
        $sheet->freezePane('A2');
        $this->outputExcel($spreadsheet, 'rekap_pendaftaran_' . date('Ymd_His'));
    }

    // =========================================================
    // EKSPOR EXCEL — Buku Induk (tidak berubah)
    // =========================================================
    public function eksporBukuInduk()
    {
        $siswas = $this->bukuIndukModel->getAllWithRelations(['status_siswa' => 'aktif']);

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Buku Induk Siswa');

        $headers = [
            'No',
            'NIS',
            'NISN',
            'Nama Lengkap',
            'L/P',
            'Tempat Lahir',
            'Tgl Lahir',
            'Agama',
            'Jurusan',
            'Kelas',
            'Nama Ayah',
            'Nama Ibu',
            'No HP Ortu',
            'Tahun Masuk',
        ];
        foreach ($headers as $i => $h) {
            $col = chr(65 + $i);
            $sheet->setCellValue($col . '1', $h);
            $sheet->getStyle($col . '1')->applyFromArray([
                'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '065F46']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        foreach ($siswas as $i => $s) {
            $r = $i + 2;
            $sheet->setCellValue('A' . $r, $i + 1);
            $sheet->setCellValue('B' . $r, $s->nis);
            $sheet->setCellValue('C' . $r, $s->nisn ?? '-');
            $sheet->setCellValue('D' . $r, $s->nama_lengkap);
            $sheet->setCellValue('E' . $r, $s->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan');
            $sheet->setCellValue('F' . $r, $s->tempat_lahir ?? '-');
            $sheet->setCellValue('G' . $r, $s->tanggal_lahir ?? '-');
            $sheet->setCellValue('H' . $r, $s->agama ?? '-');
            $sheet->setCellValue('I' . $r, $s->jurusan_nama ?? '-');
            $sheet->setCellValue('J' . $r, $s->kelas_nama ?? '-');
            $sheet->setCellValue('K' . $r, $s->nama_ayah ?? '-');
            $sheet->setCellValue('L' . $r, $s->nama_ibu ?? '-');
            $sheet->setCellValue('M' . $r, $s->no_hp_ortu ?? '-');
            $sheet->setCellValue('N' . $r, $s->tahun_masuk ?? '-');
        }
        $sheet->freezePane('A2');
        $this->outputExcel($spreadsheet, 'buku_induk_' . date('Ymd'));
    }
}
