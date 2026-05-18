<?php

namespace App\Modules\BukuInduk\Libraries;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

/**
 * ExcelExporter — Buku Induk Siswa
 *
 * Mendukung dua mode:
 *   exportBulk(array $siswas, array $filters)  → satu file, semua siswa dalam satu sheet
 *   exportSingle(object $siswa)                 → satu file, satu siswa dengan layout kartu buku induk
 */
class ExcelExporter
{
    // ═══════════════════════════════════════════════════════════
    // WARNA (ARGB — tanpa #)
    // ═══════════════════════════════════════════════════════════
    private const CLR_HEADER_BG   = 'FF1E3A6E'; // biru tua navy
    private const CLR_HEADER_FG   = 'FFFFFFFF'; // putih
    private const CLR_SUBHEAD_BG  = 'FF2D5FA8'; // biru medium
    private const CLR_SUBHEAD_FG  = 'FFFFFFFF';
    private const CLR_ALT_ROW     = 'FFF0F4FA'; // biru muda sangat pucat
    private const CLR_WHITE       = 'FFFFFFFF';
    private const CLR_ACCENT      = 'FFEED97C'; // kuning emas
    private const CLR_ACCENT_BG   = 'FFFFF9E6'; // kuning sangat pucat
    private const CLR_BORDER      = 'FFCBD5E8'; // abu-abu kebiruan
    private const CLR_BORDER_DARK = 'FF7B93B8';
    private const CLR_SECTION_BG  = 'FFE8EFF8'; // seksi label
    private const CLR_LABEL_FG    = 'FF2D4A7A'; // teks label

    // ═══════════════════════════════════════════════════════════
    // EXPORT BULK — banyak siswa, satu tabel
    // ═══════════════════════════════════════════════════════════

    /**
     * @param object[] $siswas  — hasil BukuIndukModel::getAllForExport()
     * @param array    $filters — untuk info di header sheet
     */
    public function exportBulk(array $siswas, array $filters = []): void
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setTitle('Buku Induk Siswa')
            ->setSubject('Data Buku Induk SMK Al-Munawwir IIBS')
            ->setCreator('SPMB SMK Al-Munawwir IIBS')
            ->setCompany('SMK Al-Munawwir IIBS');

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Buku Induk');

        $this->buildBulkSheet($sheet, $siswas, $filters);

        $filename = 'buku-induk-' . date('Ymd-His') . '.xlsx';
        $this->sendHeaders($filename);

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    // ═══════════════════════════════════════════════════════════
    // EXPORT SINGLE — satu siswa, layout kartu
    // ═══════════════════════════════════════════════════════════

    /**
     * @param object $siswa — hasil BukuIndukModel::getWithRelations()
     */
    public function exportSingle(object $siswa): void
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setTitle('Buku Induk — ' . ($siswa->nama_lengkap ?? ''))
            ->setSubject('Data Buku Induk SMK Al-Munawwir IIBS')
            ->setCreator('SPMB SMK Al-Munawwir IIBS')
            ->setCompany('SMK Al-Munawwir IIBS');

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Buku Induk Siswa');

        $this->buildSingleSheet($sheet, $siswa);

        $safe     = preg_replace('/[^a-z0-9]/i', '-', $siswa->nama_lengkap ?? 'siswa');
        $filename = 'buku-induk-' . strtolower($safe) . '-' . date('Ymd') . '.xlsx';
        $this->sendHeaders($filename);

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    // ═══════════════════════════════════════════════════════════
    // PRIVATE — BUILD BULK SHEET
    // ═══════════════════════════════════════════════════════════

    private function buildBulkSheet(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, array $siswas, array $filters): void
    {
        // ── Freeze & zoom ──
        $sheet->freezePane('A5');
        $sheet->getSheetView()->setZoomScale(90);

        // ── Lebar kolom ──
        $colWidths = [
            'A' => 5,   // No
            'B' => 14,  // NIS
            'C' => 14,  // NISN
            'D' => 32,  // Nama Lengkap
            'E' => 10,  // JK
            'F' => 22,  // Tempat Lahir
            'G' => 14,  // Tgl Lahir
            'H' => 16,  // Jurusan
            'I' => 16,  // Kelas
            'J' => 14,  // Tahun Masuk
            'K' => 18,  // Asal Sekolah
            'L' => 14,  // Tahun Lulus SMP
            'M' => 28,  // Alamat
            'N' => 16,  // No HP Siswa
            'O' => 30,  // Nama Ayah
            'P' => 20,  // Pekerjaan Ayah
            'Q' => 16,  // No HP Ayah
            'R' => 30,  // Nama Ibu
            'S' => 20,  // Pekerjaan Ibu
            'T' => 16,  // No HP Ibu
            'U' => 10,  // Gol Darah
            'V' => 10,  // TB (cm)
            'W' => 10,  // BB (kg)
            'X' => 25,  // Riwayat Penyakit
            'Y' => 14,  // Status
        ];
        foreach ($colWidths as $col => $w) {
            $sheet->getColumnDimension($col)->setWidth($w);
        }

        $lastCol = 'Y';
        $totalCols = 25;

        // ── Baris 1: Judul sekolah ──
        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->setCellValue('A1', 'SMK AL-MUNAWWIR IIBS');
        $this->applyStyle($sheet, "A1:{$lastCol}1", [
            'font'      => ['bold' => true, 'size' => 14, 'color' => self::CLR_HEADER_FG, 'name' => 'Arial'],
            'fill'      => self::CLR_HEADER_BG,
            'alignment' => ['h' => Alignment::HORIZONTAL_CENTER, 'v' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(28);

        // ── Baris 2: Sub-judul ──
        $totalSiswa = count($siswas);
        $filterDesc = [];
        if (! empty($filters['jurusan_nama'])) $filterDesc[] = 'Jurusan: ' . $filters['jurusan_nama'];
        if (! empty($filters['status_siswa'])) $filterDesc[] = 'Status: ' . ucfirst($filters['status_siswa']);
        if (! empty($filters['search']))       $filterDesc[] = 'Cari: ' . $filters['search'];

        $subTitle = 'Data Buku Induk Siswa';
        if ($filterDesc) $subTitle .= ' — ' . implode(' | ', $filterDesc);

        $sheet->mergeCells("A2:{$lastCol}2");
        $sheet->setCellValue('A2', $subTitle);
        $this->applyStyle($sheet, "A2:{$lastCol}2", [
            'font'      => ['bold' => false, 'size' => 10, 'color' => self::CLR_SUBHEAD_FG, 'name' => 'Arial'],
            'fill'      => self::CLR_SUBHEAD_BG,
            'alignment' => ['h' => Alignment::HORIZONTAL_CENTER, 'v' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(2)->setRowHeight(18);

        // ── Baris 3: Info tanggal & jumlah ──
        $sheet->mergeCells("A3:{$lastCol}3");
        $sheet->setCellValue('A3', 'Dicetak: ' . date('d/m/Y H:i') . ' WIB   |   Total Siswa: ' . number_format($totalSiswa));
        $this->applyStyle($sheet, "A3:{$lastCol}3", [
            'font'      => ['italic' => true, 'size' => 9, 'color' => 'FF555555', 'name' => 'Arial'],
            'fill'      => 'FFF5F7FB',
            'alignment' => ['h' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getRowDimension(3)->setRowHeight(16);

        // ── Baris 4: Header kolom ──
        $headers = [
            'A4' => 'No',
            'B4' => 'NIS',
            'C4' => 'NISN',
            'D4' => 'Nama Lengkap',
            'E4' => 'JK',
            'F4' => 'Tempat Lahir',
            'G4' => 'Tgl Lahir',
            'H4' => 'Jurusan',
            'I4' => 'Kelas',
            'J4' => 'Tahun Masuk',
            'K4' => 'Asal Sekolah',
            'L4' => 'Th Lulus SMP',
            'M4' => 'Alamat',
            'N4' => 'No HP Siswa',
            'O4' => 'Nama Ayah',
            'P4' => 'Pekerjaan Ayah',
            'Q4' => 'No HP Ayah',
            'R4' => 'Nama Ibu',
            'S4' => 'Pekerjaan Ibu',
            'T4' => 'No HP Ibu',
            'U4' => 'Gol Darah',
            'V4' => 'TB (cm)',
            'W4' => 'BB (kg)',
            'X4' => 'Riwayat Penyakit',
            'Y4' => 'Status',
        ];
        foreach ($headers as $cell => $label) {
            $sheet->setCellValue($cell, $label);
        }
        $this->applyStyle($sheet, "A4:{$lastCol}4", [
            'font'      => ['bold' => true, 'size' => 9, 'color' => self::CLR_HEADER_FG, 'name' => 'Arial'],
            'fill'      => self::CLR_HEADER_BG,
            'alignment' => ['h' => Alignment::HORIZONTAL_CENTER, 'v' => Alignment::VERTICAL_CENTER, 'wrap' => true],
            'border'    => self::CLR_BORDER_DARK,
        ]);
        $sheet->getRowDimension(4)->setRowHeight(28);

        // ── Data rows ──
        if (empty($siswas)) {
            $sheet->mergeCells("A5:{$lastCol}5");
            $sheet->setCellValue('A5', 'Tidak ada data siswa.');
            $this->applyStyle($sheet, "A5:{$lastCol}5", [
                'font'      => ['italic' => true, 'color' => 'FF888888', 'name' => 'Arial'],
                'alignment' => ['h' => Alignment::HORIZONTAL_CENTER],
            ]);
        } else {
            foreach ($siswas as $i => $s) {
                $row    = $i + 5;
                $isAlt  = ($i % 2 === 1);
                $bgFill = $isAlt ? self::CLR_ALT_ROW : self::CLR_WHITE;

                $tgl = ! empty($s->tanggal_lahir)
                    ? \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel(strtotime($s->tanggal_lahir))
                    : '';

                $rowData = [
                    'A' => $i + 1,
                    'B' => $s->nis          ?? '',
                    'C' => $s->nisn         ?? '',
                    'D' => $s->nama_lengkap ?? '',
                    'E' => $s->jenis_kelamin === 'L' ? 'L' : ($s->jenis_kelamin === 'P' ? 'P' : ($s->jenis_kelamin ?? '')),
                    'F' => $s->tempat_lahir ?? '',
                    'G' => $tgl,
                    'H' => ($s->jurusan_kode ?? '') . ' — ' . ($s->jurusan_nama ?? ''),
                    'I' => $s->kelas_nama   ?? '-',
                    'J' => $s->tahun_masuk  ?? '',
                    'K' => $s->asal_sekolah    ?? '',
                    'L' => $s->tahun_lulus_smp ?? '',
                    'M' => $s->alamat       ?? '',
                    'N' => $s->no_hp        ?? '',
                    'O' => $s->nama_ayah       ?? '',
                    'P' => $s->pekerjaan_ayah  ?? '',
                    'Q' => $s->no_hp_ayah      ?? '',
                    'R' => $s->nama_ibu        ?? '',
                    'S' => $s->pekerjaan_ibu   ?? '',
                    'T' => $s->no_hp_ibu       ?? '',
                    'U' => $s->golongan_darah   ?? '-',
                    'V' => $s->tinggi_badan     !== null ? (int) $s->tinggi_badan : '',
                    'W' => $s->berat_badan      !== null ? (int) $s->berat_badan  : '',
                    'X' => $s->riwayat_penyakit ?? '',
                    'Y' => ucfirst($s->status_siswa ?? ''),
                ];

                foreach ($rowData as $col => $val) {
                    if ($col === 'G' && $val !== '') {
                        $sheet->setCellValue("{$col}{$row}", $val);
                        $sheet->getStyle("{$col}{$row}")->getNumberFormat()
                            ->setFormatCode('DD/MM/YYYY');
                    } else {
                        $sheet->setCellValueExplicit("{$col}{$row}", (string) $val, DataType::TYPE_STRING);
                        if (in_array($col, ['V', 'W', 'J', 'L']) && is_numeric($val) && $val !== '') {
                            $sheet->setCellValue("{$col}{$row}", (int) $val);
                        }
                    }
                }

                // Style row
                $this->applyStyle($sheet, "A{$row}:{$lastCol}{$row}", [
                    'font'      => ['size' => 9, 'name' => 'Arial'],
                    'fill'      => $bgFill,
                    'alignment' => ['v' => Alignment::VERTICAL_TOP, 'wrap' => true],
                    'border'    => self::CLR_BORDER,
                ]);

                // Kolom tertentu: rata tengah
                foreach (['A', 'E', 'G', 'J', 'L', 'U', 'V', 'W', 'Y'] as $centerCol) {
                    $sheet->getStyle("{$centerCol}{$row}")
                        ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }

                // Nama: bold
                $sheet->getStyle("D{$row}")->getFont()->setBold(true);

                // Status: warna
                $status = strtolower($s->status_siswa ?? '');
                if ($status === 'aktif') {
                    $sheet->getStyle("Y{$row}")->getFont()->setColor(
                        (new \PhpOffice\PhpSpreadsheet\Style\Color('FF1A6B3A'))
                    );
                } elseif (in_array($status, ['keluar', 'pindah', 'lulus'])) {
                    $sheet->getStyle("Y{$row}")->getFont()->setColor(
                        (new \PhpOffice\PhpSpreadsheet\Style\Color('FF8B4513'))
                    );
                }

                $sheet->getRowDimension($row)->setRowHeight(24);
            }

            // ── Baris total ──
            $totalRow = count($siswas) + 5;
            $sheet->mergeCells("A{$totalRow}:C{$totalRow}");
            $sheet->setCellValue("A{$totalRow}", 'Total: ' . number_format($totalSiswa) . ' siswa');
            $this->applyStyle($sheet, "A{$totalRow}:{$lastCol}{$totalRow}", [
                'font'      => ['bold' => true, 'size' => 9, 'color' => self::CLR_HEADER_FG, 'name' => 'Arial'],
                'fill'      => self::CLR_HEADER_BG,
                'alignment' => ['h' => Alignment::HORIZONTAL_LEFT, 'v' => Alignment::VERTICAL_CENTER],
            ]);
            $sheet->getRowDimension($totalRow)->setRowHeight(20);
        }

        // ── Auto filter ──
        $lastDataRow = max(5, count($siswas) + 4);
        $sheet->setAutoFilter("A4:{$lastCol}{$lastDataRow}");

        // ── Print setup ──
        $sheet->getPageSetup()
            ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE)
            ->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A3)
            ->setFitToPage(true)
            ->setFitToWidth(1)
            ->setFitToHeight(0);
        $sheet->getHeaderFooter()
            ->setOddHeader('&C&B&14Buku Induk Siswa — SMK Al-Munawwir IIBS')
            ->setOddFooter('&L&D &T&R&P dari &N');
        $sheet->getPageMargins()
            ->setTop(0.75)->setBottom(0.75)->setLeft(0.5)->setRight(0.5);
        $sheet->setPrintGridlines(false);
        $sheet->setShowGridlines(true);
    }

    // ═══════════════════════════════════════════════════════════
    // PRIVATE — BUILD SINGLE SHEET
    // ═══════════════════════════════════════════════════════════

    private function buildSingleSheet(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, object $s): void
    {
        $sheet->getSheetView()->setZoomScale(95);

        // Lebar kolom: A label | B value | C label | D value
        $sheet->getColumnDimension('A')->setWidth(4);
        $sheet->getColumnDimension('B')->setWidth(28);
        $sheet->getColumnDimension('C')->setWidth(28);
        $sheet->getColumnDimension('D')->setWidth(20);
        $sheet->getColumnDimension('E')->setWidth(28);

        $r = 1;

        // ── Header ──────────────────────────────────────────────
        $sheet->mergeCells("A{$r}:E{$r}");
        $sheet->setCellValue("A{$r}", 'SMK AL-MUNAWWIR IIBS');
        $this->applyStyle($sheet, "A{$r}:E{$r}", [
            'font'      => ['bold' => true, 'size' => 16, 'color' => self::CLR_HEADER_FG, 'name' => 'Arial'],
            'fill'      => self::CLR_HEADER_BG,
            'alignment' => ['h' => Alignment::HORIZONTAL_CENTER, 'v' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension($r)->setRowHeight(36);
        $r++;

        $sheet->mergeCells("A{$r}:E{$r}");
        $sheet->setCellValue("A{$r}", 'BUKU INDUK SISWA');
        $this->applyStyle($sheet, "A{$r}:E{$r}", [
            'font'      => ['bold' => true, 'size' => 13, 'color' => self::CLR_ACCENT, 'name' => 'Arial'],
            'fill'      => self::CLR_SUBHEAD_BG,
            'alignment' => ['h' => Alignment::HORIZONTAL_CENTER, 'v' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension($r)->setRowHeight(26);
        $r++;

        // ── NIS + Nama besar ──────────────────────────────────
        $sheet->mergeCells("A{$r}:E{$r}");
        $sheet->setCellValue("A{$r}", ($s->nama_lengkap ?? '') . '   •   NIS: ' . ($s->nis ?? '-') . '   •   ' . ($s->jurusan_kode ?? '') . ' — ' . ($s->jurusan_nama ?? ''));
        $this->applyStyle($sheet, "A{$r}:E{$r}", [
            'font'      => ['bold' => true, 'size' => 12, 'color' => self::CLR_LABEL_FG, 'name' => 'Arial'],
            'fill'      => self::CLR_ACCENT_BG,
            'alignment' => ['h' => Alignment::HORIZONTAL_CENTER, 'v' => Alignment::VERTICAL_CENTER],
            'border'    => self::CLR_ACCENT,
        ]);
        $sheet->getRowDimension($r)->setRowHeight(28);
        $r++;

        // ── Info cetak ──────────────────────────────────────────
        $sheet->mergeCells("A{$r}:E{$r}");
        $sheet->setCellValue("A{$r}", 'Dicetak: ' . date('d/m/Y H:i') . ' WIB   |   Kelas: ' . ($s->kelas_nama ?? '-') . '   |   Tahun Masuk: ' . ($s->tahun_masuk ?? '-'));
        $this->applyStyle($sheet, "A{$r}:E{$r}", [
            'font'      => ['italic' => true, 'size' => 9, 'color' => 'FF666666', 'name' => 'Arial'],
            'fill'      => 'FFF5F7FB',
            'alignment' => ['h' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getRowDimension($r)->setRowHeight(16);
        $r++;

        $r++; // spasi

        // ── Seksi helper ──────────────────────────────────────
        $addSection = function (string $title) use ($sheet, &$r) {
            $sheet->mergeCells("A{$r}:E{$r}");
            $sheet->setCellValue("A{$r}", "  $title");
            $this->applyStyle($sheet, "A{$r}:E{$r}", [
                'font'      => ['bold' => true, 'size' => 10, 'color' => self::CLR_SUBHEAD_FG, 'name' => 'Arial'],
                'fill'      => self::CLR_SUBHEAD_BG,
                'alignment' => ['v' => Alignment::VERTICAL_CENTER],
            ]);
            $sheet->getRowDimension($r)->setRowHeight(22);
            $r++;
        };

        $addRow = function (string $label, string $val1, string $label2 = '', string $val2 = '') use ($sheet, &$r) {
            $sheet->setCellValue("B{$r}", $label);
            $sheet->setCellValue("C{$r}", $val1);
            if ($label2 !== '') {
                $sheet->setCellValue("D{$r}", $label2);
                $sheet->setCellValue("E{$r}", $val2);
            }

            // Label style
            foreach (['B', 'D'] as $lc) {
                if ($sheet->getCell("{$lc}{$r}")->getValue() !== '') {
                    $this->applyStyle($sheet, "{$lc}{$r}", [
                        'font'      => ['bold' => true, 'size' => 9, 'color' => self::CLR_LABEL_FG, 'name' => 'Arial'],
                        'fill'      => self::CLR_SECTION_BG,
                        'alignment' => ['v' => Alignment::VERTICAL_TOP],
                        'border'    => self::CLR_BORDER,
                    ]);
                }
            }
            // Value style
            foreach (['C', 'E'] as $vc) {
                $this->applyStyle($sheet, "{$vc}{$r}", [
                    'font'      => ['size' => 9, 'name' => 'Arial'],
                    'fill'      => self::CLR_WHITE,
                    'alignment' => ['v' => Alignment::VERTICAL_TOP, 'wrap' => true],
                    'border'    => self::CLR_BORDER,
                ]);
            }

            // Nomor baris
            $sheet->setCellValue("A{$r}", $r - 5); // placeholder — hide col A
            $sheet->getStyle("A{$r}")->getFont()->setSize(8)->setColor(
                new \PhpOffice\PhpSpreadsheet\Style\Color('FFCCCCCC')
            );

            $sheet->getRowDimension($r)->setRowHeight(20);
            $r++;
        };

        // ══ SEKSI 1: DATA PRIBADI ══════════════════════════════
        $addSection('📋  DATA PRIBADI');

        $tglLahir = ! empty($s->tanggal_lahir)
            ? date('d/m/Y', strtotime($s->tanggal_lahir))
            : '-';

        $addRow('Nama Lengkap',    $s->nama_lengkap   ?? '-',  'Nama Panggilan',  $s->nama_panggilan  ?? '-');
        $addRow('NIS',             $s->nis             ?? '-',  'NISN',            $s->nisn            ?? '-');
        $addRow('NIK',             $s->nik             ?? '-',  'Jenis Kelamin',   $s->jenis_kelamin === 'L' ? 'Laki-laki' : ($s->jenis_kelamin === 'P' ? 'Perempuan' : ($s->jenis_kelamin ?? '-')));
        $addRow('Tempat Lahir',    $s->tempat_lahir    ?? '-',  'Tanggal Lahir',   $tglLahir);
        $addRow('Agama',           $s->agama           ?? '-',  'Kewarganegaraan', $s->kewarganegaraan ?? '-');
        $addRow('Asal Sekolah',    $s->asal_sekolah    ?? '-',  'Th. Lulus SMP',   $s->tahun_lulus_smp ?? '-');
        $addRow('Jurusan',         ($s->jurusan_kode ?? '') . ' — ' . ($s->jurusan_nama ?? '-'), 'Kelas', $s->kelas_nama ?? '-');
        $addRow('Tahun Masuk',     $s->tahun_masuk     ?? '-',  'Wali Kelas',      $s->wali_kelas      ?? '-');
        $addRow('Status Siswa',    ucfirst($s->status_siswa ?? '-'), '', '');

        $r++; // spasi

        // ══ SEKSI 2: KONTAK & ALAMAT ═══════════════════════════
        $addSection('📍  KONTAK & ALAMAT');
        $addRow('Alamat Lengkap',  $s->alamat       ?? '-',  'No HP Siswa',  $s->no_hp       ?? '-');
        $addRow('Email Siswa',     $s->email_siswa  ?? '-',  '',             '');

        $r++;

        // ══ SEKSI 3: DATA ORANG TUA ════════════════════════════
        $addSection('👨‍👩‍👧  DATA ORANG TUA / WALI');
        $addRow('Nama Ayah',       $s->nama_ayah      ?? '-',  'Pekerjaan Ayah',   $s->pekerjaan_ayah  ?? '-');
        $addRow('No HP Ayah',      $s->no_hp_ayah     ?? '-',  '',                 '');
        $addRow('Nama Ibu',        $s->nama_ibu       ?? '-',  'Pekerjaan Ibu',    $s->pekerjaan_ibu   ?? '-');
        $addRow('No HP Ibu',       $s->no_hp_ibu      ?? '-',  '',                 '');

        $r++;

        // ══ SEKSI 4: DATA KESEHATAN ════════════════════════════
        $addSection('🏥  DATA KESEHATAN');
        $addRow('Golongan Darah',  $s->golongan_darah ?? '-',  'Tinggi Badan',     ($s->tinggi_badan ?? '-') . ($s->tinggi_badan ? ' cm' : ''));
        $addRow('Berat Badan',     ($s->berat_badan ?? '-') . ($s->berat_badan ? ' kg' : ''), 'Catatan Kesehatan', $s->catatan_kesehatan ?? '-');
        $addRow('Riwayat Penyakit', $s->riwayat_penyakit ?? '-', '', '');

        $r += 2;

        // ── Tanda tangan ─────────────────────────────────────
        $sheet->mergeCells("C{$r}:E{$r}");
        $sheet->setCellValue("C{$r}", date('d/m/Y'));
        $this->applyStyle($sheet, "C{$r}:E{$r}", [
            'font'      => ['size' => 9, 'name' => 'Arial'],
            'alignment' => ['h' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getRowDimension($r)->setRowHeight(16);
        $r++;

        $sheet->mergeCells("C{$r}:E{$r}");
        $sheet->setCellValue("C{$r}", 'Wali Kelas / Admin TU');
        $this->applyStyle($sheet, "C{$r}:E{$r}", [
            'font'      => ['size' => 9, 'italic' => true, 'color' => 'FF666666', 'name' => 'Arial'],
            'alignment' => ['h' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getRowDimension($r)->setRowHeight(16);
        $r += 4;

        $sheet->mergeCells("C{$r}:E{$r}");
        $sheet->setCellValue("C{$r}", '( _________________________ )');
        $this->applyStyle($sheet, "C{$r}:E{$r}", [
            'font'      => ['size' => 10, 'name' => 'Arial'],
            'alignment' => ['h' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getRowDimension($r)->setRowHeight(16);

        // ── Sembunyikan kolom A (baris nomor) ──
        $sheet->getColumnDimension('A')->setVisible(false);

        // ── Print setup ──
        $sheet->getPageSetup()
            ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT)
            ->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4)
            ->setFitToPage(true)
            ->setFitToWidth(1)
            ->setFitToHeight(0);
        $sheet->getHeaderFooter()
            ->setOddHeader('&C&BBuku Induk Siswa — SMK Al-Munawwir IIBS')
            ->setOddFooter('&LDicetak: &D &T&RHalaman &P dari &N');
        $sheet->getPageMargins()
            ->setTop(1)->setBottom(1)->setLeft(0.75)->setRight(0.75);
    }

    // ═══════════════════════════════════════════════════════════
    // PRIVATE HELPERS
    // ═══════════════════════════════════════════════════════════

    /**
     * Terapkan style ringkas ke range sel.
     * Hanya key yang ada di $opts yang di-apply.
     *
     * @param array $opts {
     *   font:      array{bold?,italic?,size?,color?,name?}
     *   fill:      string ARGB
     *   alignment: array{h?,v?,wrap?}
     *   border:    string ARGB  → thin border all sides
     * }
     */
    private function applyStyle(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, string $range, array $opts): void
    {
        $style = $sheet->getStyle($range);

        // Font
        if (! empty($opts['font'])) {
            $f    = $opts['font'];
            $font = $style->getFont();
            if (isset($f['bold']))   $font->setBold($f['bold']);
            if (isset($f['italic'])) $font->setItalic($f['italic']);
            if (isset($f['size']))   $font->setSize($f['size']);
            if (isset($f['name']))   $font->setName($f['name']);
            if (isset($f['color']))  $font->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color($f['color']));
        }

        // Fill
        if (! empty($opts['fill'])) {
            $style->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB($opts['fill']);
        }

        // Alignment
        if (! empty($opts['alignment'])) {
            $a   = $opts['alignment'];
            $aln = $style->getAlignment();
            if (isset($a['h']))    $aln->setHorizontal($a['h']);
            if (isset($a['v']))    $aln->setVertical($a['v']);
            if (isset($a['wrap'])) $aln->setWrapText($a['wrap']);
        }

        // Border (thin all sides)
        if (! empty($opts['border'])) {
            $borderStyle = [
                'borderStyle' => Border::BORDER_THIN,
                'color'       => ['argb' => $opts['border']],
            ];
            $style->getBorders()->applyFromArray([
                'allBorders' => $borderStyle,
            ]);
        }
    }

    private function sendHeaders(string $filename): void
    {
        // Bersihkan output buffer agar tidak ada karakter sebelum binary
        if (ob_get_level()) ob_end_clean();

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: cache, must-revalidate');
        header('Pragma: public');
    }
}