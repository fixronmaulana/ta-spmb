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
    // Sesuai mockup React: ArsipLaporanPage
    //
    // Karena tidak ada tabel tersendiri untuk arsip file,
    // kita generate "arsip virtual" dari data periodes +
    // statistik pendaftaran per periode.
    // =========================================================
    public function arsip()
    {
        $db        = db_connect();
        $periodes  = $this->periodeModel->orderBy('tahun_ajaran', 'DESC')->findAll();
        $jurusans  = $this->jurusanModel->getAllActive();

        // Filter dari query string
        $searchQ   = $this->request->getGet('search') ?? '';
        $filterTA  = $this->request->getGet('tahun_ajaran') ?? 'semua';
        $filterTipe = $this->request->getGet('tipe') ?? 'semua';

        // ── Buat daftar arsip virtual dari periodes ─────────────
        // Setiap periode menghasilkan beberapa "entry" laporan:
        //   1. Laporan Rekapitulasi         (tipe: rekapitulasi)
        //   2. Laporan per Jurusan / Excel  (tipe: jurusan)
        //   3. Laporan Akhir (jika periode sudah selesai) (tipe: akhir)
        $arsipList = [];

        // Ambil statistik per periode sekaligus
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

        // Index by periode_id
        $statsIdx = [];
        foreach ($statsPerPeriode as $s) {
            $statsIdx[$s->periode_id] = $s;
        }

        foreach ($periodes as $p) {
            $stat       = $statsIdx[$p->id] ?? null;
            $tPendaftar = (int) ($stat->total_pendaftar   ?? 0);
            $tDiterima  = (int) ($stat->total_diterima    ?? 0);
            $tAktif     = (int) ($stat->total_siswa_aktif ?? 0);

            // Tanggal generate = tanggal_selesai atau created_at
            $tglGenerate = $p->tanggal_selesai ?? $p->created_at ?? date('Y-m-d');

            // Entry 1: Rekapitulasi (pdf)
            $arsipList[] = [
                'id'             => 'rekap-' . $p->id,
                'periode_id'     => $p->id,
                'tahun_ajaran'   => $p->tahun_ajaran,
                'judul'          => 'Laporan Rekapitulasi SPMB TA ' . $p->tahun_ajaran,
                'tipe'           => 'rekapitulasi',
                'format'         => 'pdf',
                'tanggal'        => $tglGenerate,
                'total_pendaftar' => $tPendaftar,
                'total_diterima' => $tDiterima,
                'total_siswa_aktif' => $tAktif,
                'url_download'   => base_url('kepala-sekolah/laporan/ekspor-pdf'),
            ];

            // Entry 2: Per Jurusan (excel)
            $arsipList[] = [
                'id'             => 'jurusan-' . $p->id,
                'periode_id'     => $p->id,
                'tahun_ajaran'   => $p->tahun_ajaran,
                'judul'          => 'Laporan Rekapitulasi per Jurusan TA ' . $p->tahun_ajaran,
                'tipe'           => 'jurusan',
                'format'         => 'excel',
                'tanggal'        => $tglGenerate,
                'total_pendaftar' => $tPendaftar,
                'total_diterima' => $tDiterima,
                'total_siswa_aktif' => $tAktif,
                'url_download'   => base_url('kepala-sekolah/laporan/ekspor-excel'),
            ];

            // Entry 3: Laporan Akhir (pdf) — hanya jika periode sudah selesai
            if ($p->tanggal_selesai && $p->tanggal_selesai < date('Y-m-d')) {
                $arsipList[] = [
                    'id'             => 'akhir-' . $p->id,
                    'periode_id'     => $p->id,
                    'tahun_ajaran'   => $p->tahun_ajaran,
                    'judul'          => 'Laporan Akhir SPMB TA ' . $p->tahun_ajaran,
                    'tipe'           => 'akhir',
                    'format'         => 'pdf',
                    'tanggal'        => $p->tanggal_selesai,
                    'total_pendaftar' => $tPendaftar,
                    'total_diterima' => $tDiterima,
                    'total_siswa_aktif' => $tAktif,
                    'url_download'   => base_url('kepala-sekolah/laporan/ekspor-pdf'),
                ];
            }
        }

        // ── Filter ────────────────────────────────────────────
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
        $arsipList = array_values($arsipList); // re-index

        // ── Summary KPI untuk cards ────────────────────────────
        $totalLaporan    = count($arsipList);
        $totalTA         = count(array_unique(array_column($arsipList, 'tahun_ajaran')));
        $totalAkhir      = count(array_filter($arsipList, fn($a) => $a['tipe'] === 'akhir'));

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
    // EKSPOR EXCEL — Rekap Pendaftaran
    // =========================================================
    public function eksporExcel()
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
            'Tgl Submit'
        ];

        foreach ($headers as $i => $header) {
            $col = chr(65 + $i);
            $sheet->setCellValue($col . '1', $header);
            $sheet->getStyle($col . '1')->applyFromArray([
                'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1D4ED8']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);
        }
        foreach (range('A', chr(64 + count($headers))) as $col) {
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

        $writer   = new Xlsx($spreadsheet);
        $filename = 'rekap_ppdb_' . date('Ymd_His') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    // =========================================================
    // EKSPOR EXCEL — Buku Induk
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
            'Tahun Masuk'
        ];

        foreach ($headers as $i => $h) {
            $col = chr(65 + $i);
            $sheet->setCellValue($col . '1', $h);
            $sheet->getStyle($col . '1')->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '065F46']],
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

        $writer   = new Xlsx($spreadsheet);
        $filename = 'buku_induk_' . date('Ymd') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    // =========================================================
    // EKSPOR PDF — Rekap PPDB
    // =========================================================
    public function eksporPdf()
    {
        $stats     = $this->pendaftaranModel->getStatistikByStatus();
        $byJurusan = $this->pendaftaranModel->getStatsByJurusan();
        $periode   = $this->periodeModel->getPeriodeAktif();

        $html = view('App\Modules\Laporan\Views\pdf_rekap', [
            'stats'    => $stats,
            'byJurusan' => $byJurusan,
            'periode'  => $periode,
            'tglCetak' => date('d/m/Y H:i'),
        ]);

        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream('rekap_ppdb_' . date('Ymd') . '.pdf', ['Attachment' => true]);
    }
}
