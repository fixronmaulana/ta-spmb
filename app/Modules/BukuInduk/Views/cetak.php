<?php
/**
 * File   : app/Modules/BukuInduk/Views/cetak.php
 * Route  : GET admin/buku-induk/{id}/cetak
 * Desc   : Template cetak PDF Buku Induk Siswa (Dompdf)
 *          Sesuai mockup — format dokumen resmi bernomor item,
 *          header sekolah, tabel penempatan, TTD.
 *          Juga handles ?mode=kartu untuk cetak kartu ID siswa.
 *
 * Dipakai oleh BukuIndukController::cetak() dan ::cetakKartu()
 */

$mode = $mode ?? 'buku'; // 'buku' | 'kartu'
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<?php if ($mode === 'kartu'): ?>
<!-- ============================================================
     MODE KARTU SISWA — ID Card 85.6 × 53.98 mm
     Paper size ditetapkan di controller: [0,0,242.4,153.1]
     ============================================================ -->
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body {
    font-family: 'DejaVu Sans', sans-serif;
    font-size: 8.5px;
    color: #111827;
    width: 242px;
    height: 153px;
    overflow: hidden;
}
.kartu {
    width: 242px;
    height: 153px;
    display: table;
    table-layout: fixed;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    overflow: hidden;
}
/* Stripe kiri hijau tua */
.stripe {
    display: table-cell;
    width: 46px;
    background: #1e3a5f;
    vertical-align: top;
    text-align: center;
    padding: 8px 0 6px 0;
}
.stripe-logo {
    width: 30px; height: 30px; border-radius: 50%;
    background: rgba(255,255,255,.15);
    line-height: 30px; text-align: center;
    font-size: 12px; font-weight: bold; color: #fff;
    margin: 0 auto 5px auto; display: block;
}
.stripe-title {
    color: rgba(255,255,255,.7);
    font-size: 5.5px; font-weight: bold;
    letter-spacing: 1px;
    writing-mode: vertical-rl; text-orientation: mixed;
    transform: rotate(180deg); display: inline-block;
    margin-top: 6px;
}
/* Konten kanan */
.konten {
    display: table-cell;
    vertical-align: top;
    padding: 7px 8px 5px 8px;
}
.k-sekolah  { font-size: 7.5px; font-weight: bold; color: #1e3a5f; }
.k-sub      { font-size: 5.5px; color: #9ca3af; margin-bottom: 4px; }
.nis-wrap   { background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 3px; padding: 2px 5px; margin-bottom: 4px; }
.nis-lbl    { font-size: 5.5px; color: #6b7280; }
.nis-val    { font-size: 12px; font-weight: bold; color: #1e3a5f; letter-spacing: 2px; }
.info-tbl   { width: 100%; border-collapse: collapse; }
.info-tbl td{ padding: 1px 0; font-size: 6px; vertical-align: top; }
.info-tbl .lbl { color: #9ca3af; width: 38%; }
.info-tbl .val { color: #111827; font-weight: 600; }
.k-footer   { margin-top: 4px; border-top: 1px solid #e5e7eb; padding-top: 3px; display: table; width: 100%; }
.kf-l       { display: table-cell; vertical-align: bottom; }
.kf-r       { display: table-cell; text-align: right; vertical-align: bottom; }
.badge      { background: #1e3a5f; color: #fff; font-size: 5.5px; padding: 1px 5px; border-radius: 8px; display: inline-block; }
.tgl-cetak  { font-size: 5px; color: #d1d5db; }
</style>
</head>
<body>
<div class="kartu">
    <div class="stripe">
        <div class="stripe-logo">M</div>
        <div class="stripe-title">KARTU SISWA</div>
    </div>
    <div class="konten">
        <div class="k-sekolah">SMK Al-Munawwir IIBS</div>
        <div class="k-sub">Banyuwangi &nbsp;|&nbsp; Kartu Tanda Siswa</div>
        <div class="nis-wrap">
            <div class="nis-lbl">Nomor Induk Siswa</div>
            <div class="nis-val"><?= esc($siswa->nis) ?></div>
        </div>
        <table class="info-tbl">
            <tr><td class="lbl">Nama</td><td class="val">: <?= esc($siswa->nama_lengkap) ?></td></tr>
            <tr><td class="lbl">NISN</td><td class="val">: <?= esc($siswa->nisn ?? '-') ?></td></tr>
            <tr><td class="lbl">Program Keahlian</td><td class="val">: <?= esc($siswa->jurusan_nama) ?></td></tr>
            <tr><td class="lbl">Kelas</td><td class="val">: <?= esc($siswa->kelas_nama ?? '-') ?></td></tr>
            <tr><td class="lbl">Tahun Masuk</td><td class="val">: <?= esc($siswa->tahun_masuk ?? '-') ?></td></tr>
        </table>
        <div class="k-footer">
            <div class="kf-l"><span class="badge"><?= ucfirst(str_replace('_', ' ', $siswa->status_siswa ?? 'aktif')) ?></span></div>
            <div class="kf-r"><span class="tgl-cetak">Dicetak: <?= $tglCetak ?></span></div>
        </div>
    </div>
</div>
</body>
</html>
<?php return; /* stop — jangan render konten buku induk */ ?>

<?php else: /* ===== MODE BUKU INDUK (default) ===== */ ?>

<style>
/* ─────────────────────────────────────────────────────────────
   RESET & BASE
   ───────────────────────────────────────────────────────────── */
* { box-sizing: border-box; margin: 0; padding: 0; }
body {
    font-family: 'DejaVu Sans', sans-serif;
    font-size: 10.5px;
    color: #111827;
    background: #fff;
    line-height: 1.45;
    padding: 22px 26px 18px 26px;
}

/* ─────────────────────────────────────────────────────────────
   HEADER SEKOLAH
   ───────────────────────────────────────────────────────────── */
.header-wrap {
    text-align: center;
    border-bottom: 2.5px solid #1e3a5f;
    padding-bottom: 10px;
    margin-bottom: 12px;
}
.sekolah-nama  { font-size: 15px; font-weight: bold; color: #1e3a5f; letter-spacing: .5px; }
.sekolah-judul { font-size: 11.5px; font-weight: bold; color: #1e3a5f; margin-top: 1px; }
.sekolah-alamat{ font-size: 8.5px; color: #6b7280; margin-top: 2px; }
.sekolah-hr    { height: 1px; background: #9ca3af; margin: 6px 0; }

/* ─────────────────────────────────────────────────────────────
   SECTION HEADER (row navy)
   ───────────────────────────────────────────────────────────── */
.sec-header {
    background: #1e3a5f;
    color: #fff;
    font-size: 9.5px;
    font-weight: bold;
    letter-spacing: .5px;
    padding: 5px 10px;
    margin-top: 10px;
    margin-bottom: 0;
}

/* ─────────────────────────────────────────────────────────────
   DATA TABLE — item bernomor
   ───────────────────────────────────────────────────────────── */
.data-tbl {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 0;
}
.data-tbl td {
    padding: 3.5px 8px;
    vertical-align: top;
    border-bottom: 1px solid #e5e7eb;
    font-size: 10px;
}
.data-tbl .no  { width: 26px; color: #374151; font-weight: bold; white-space: nowrap; }
.data-tbl .lbl { width: 44%; color: #374151; font-weight: bold; }
.data-tbl .sep { width: 12px; text-align: center; color: #6b7280; }
.data-tbl .val { color: #111827; }
.data-tbl tr:nth-child(even) td { background: #f9fafb; }

/* Foto pas di samping blok A */
.foto-box {
    float: right;
    width: 80px; height: 105px;
    border: 1px solid #9ca3af;
    margin-left: 10px; margin-bottom: 4px;
    text-align: center;
    display: table;
}
.foto-label {
    display: table-cell;
    vertical-align: middle;
    font-size: 8.5px;
    color: #9ca3af;
    line-height: 1.4;
}

/* ─────────────────────────────────────────────────────────────
   TABEL PENEMPATAN (section F)
   ───────────────────────────────────────────────────────────── */
.penempatan-tbl {
    width: 100%;
    border-collapse: collapse;
    margin-top: 0;
}
.penempatan-tbl th {
    background: #1e3a5f;
    color: #fff;
    font-size: 9px;
    font-weight: bold;
    padding: 5px 6px;
    text-align: center;
    border: 1px solid #1e3a5f;
}
.penempatan-tbl td {
    padding: 4.5px 6px;
    font-size: 9.5px;
    text-align: center;
    border: 1px solid #d1d5db;
    color: #374151;
}
.penempatan-tbl tr:nth-child(even) td { background: #f9fafb; }
.penempatan-tbl td.no { width: 34px; }

/* ─────────────────────────────────────────────────────────────
   TTD FOOTER
   ───────────────────────────────────────────────────────────── */
.ttd-wrap {
    display: table;
    width: 100%;
    margin-top: 20px;
}
.ttd-left, .ttd-right { display: table-cell; width: 50%; vertical-align: top; }
.ttd-right { text-align: right; }
.ttd-kota  { font-size: 10px; color: #374151; margin-bottom: 2px; }
.ttd-jabatan { font-size: 10px; color: #374151; margin-bottom: 40px; }
.ttd-garis { border-top: 1px solid #374151; padding-top: 3px; display: inline-block; min-width: 160px; text-align: center; font-size: 10px; font-weight: bold; color: #1e3a5f; }
.ttd-nip   { font-size: 8.5px; color: #6b7280; text-align: center; margin-top: 1px; }

.footer-info {
    margin-top: 12px;
    text-align: right;
    font-size: 8px;
    color: #9ca3af;
    border-top: 1px solid #e5e7eb;
    padding-top: 5px;
}

/* ─────────────────────────────────────────────────────────────
   UTILS
   ───────────────────────────────────────────────────────────── */
.clearfix::after { content: ''; display: table; clear: both; }
</style>
</head>
<body>

<?php
/* ── helpers ── */
$jk   = ($siswa->jenis_kelamin ?? '') === 'L' ? 'Laki-laki' : 'Perempuan';
$tgl  = fn($d) => $d ? date('d/m/Y', strtotime($d)) : '-';
$dash = fn($v) => ($v !== null && $v !== '') ? $v : '-';

$ta_masuk   = $siswa->tahun_masuk ?? null;
$ta_label   = $ta_masuk ? $ta_masuk . '/' . ($ta_masuk + 1) : '-';
?>

<!-- ══ HEADER ══ -->
<div class="header-wrap">
    <img src="data:image/png;base64,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" alt="Logo SMK Al-Munawwir IIBS"
         style="width:62px;height:62px;object-fit:contain;margin-bottom:5px;display:block;margin-left:auto;margin-right:auto;">
    <div class="sekolah-nama">SMK AL MUNAWWIR IIBS</div>
    <div class="sekolah-judul">BUKU INDUK SISWA</div>
    <div class="sekolah-hr"></div>
    <div class="sekolah-alamat">
        Jl. Kedungliwung No.35, Kemiri, Singojuruh, Banyuwangi, Jawa Timur<br>
        Email: smkalmunawwiriibs@gmail.com &nbsp;|&nbsp; Tahun Ajaran: <?= esc($ta_label) ?>
    </div>
</div>

<!-- ══ A. KETERANGAN DIRI SISWA ══ -->
<div class="sec-header">A. KETERANGAN TENTANG DIRI SISWA</div>
<div class="clearfix">
    <!-- Pas Foto -->
    <div class="foto-box">
        <div class="foto-label">Pas Foto<br>3x4</div>
    </div>

    <table class="data-tbl">
        <?php
        $itemA = [
            [1,  'Nama Lengkap',               esc($dash($siswa->nama_lengkap))],
            [2,  'Nama Panggilan',              esc($dash($siswa->nama_panggilan))],
            [3,  'NIS',                         esc($dash($siswa->nis))],
            [4,  'NISN',                        esc($dash($siswa->nisn))],
            [5,  'NIK',                         esc($dash($siswa->nik))],
            [6,  'Jenis Kelamin',               $jk],
            [7,  'Tempat, Tanggal Lahir',       esc($dash($siswa->tempat_lahir)) . ', ' . $tgl($siswa->tanggal_lahir)],
            [8,  'Agama',                       esc($dash($siswa->agama))],
            [9,  'Kewarganegaraan',             esc($dash($siswa->kewarganegaraan ?? 'Indonesia'))],
            [10, 'Anak Ke',                     '-'],
            [11, 'Status dalam Keluarga',       '-'],
        ];
        foreach ($itemA as [$no, $lbl, $val]):
        ?>
        <tr>
            <td class="no"><?= $no ?>.</td>
            <td class="lbl"><?= $lbl ?></td>
            <td class="sep">:</td>
            <td class="val"><?= $val ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>

<!-- ══ B. TEMPAT TINGGAL ══ -->
<div class="sec-header">B. TEMPAT TINGGAL</div>
<table class="data-tbl">
    <?php
    $itemB = [
        [12, 'Alamat',          esc($dash($siswa->alamat))],
        [13, 'No. Telepon / HP', esc($dash($siswa->no_hp))],
        [14, 'Email',           esc($dash($siswa->email_siswa))],
    ];
    foreach ($itemB as [$no, $lbl, $val]):
    ?>
    <tr>
        <td class="no"><?= $no ?>.</td>
        <td class="lbl"><?= $lbl ?></td>
        <td class="sep">:</td>
        <td class="val"><?= $val ?></td>
    </tr>
    <?php endforeach; ?>
</table>

<!-- ══ C. KETERANGAN KESEHATAN ══ -->
<div class="sec-header">C. KETERANGAN KESEHATAN</div>
<table class="data-tbl">
    <?php
    $tbBb = '-';
    if ($siswa->tinggi_badan && $siswa->berat_badan) {
        $tbBb = esc($siswa->tinggi_badan) . ' cm / ' . esc($siswa->berat_badan) . ' kg';
    } elseif ($siswa->tinggi_badan) {
        $tbBb = esc($siswa->tinggi_badan) . ' cm';
    } elseif ($siswa->berat_badan) {
        $tbBb = esc($siswa->berat_badan) . ' kg';
    }
    $itemC = [
        [15, 'Golongan Darah',    esc($dash($siswa->golongan_darah))],
        [16, 'Tinggi / Berat Badan', $tbBb],
        [17, 'Riwayat Penyakit',  esc($dash($siswa->riwayat_penyakit ?? 'Tidak Ada'))],
    ];
    foreach ($itemC as [$no, $lbl, $val]):
    ?>
    <tr>
        <td class="no"><?= $no ?>.</td>
        <td class="lbl"><?= $lbl ?></td>
        <td class="sep">:</td>
        <td class="val"><?= $val ?></td>
    </tr>
    <?php endforeach; ?>
</table>

<!-- ══ D. KETERANGAN ORANG TUA / WALI ══ -->
<div class="sec-header">D. KETERANGAN ORANG TUA / WALI</div>
<table class="data-tbl" style="margin-bottom:0">
    <tr>
        <td class="no" style="font-weight:bold;text-decoration:underline;border-bottom:1px solid #e5e7eb">Ayah</td>
        <td class="lbl" style="border-bottom:1px solid #e5e7eb"></td>
        <td class="sep" style="border-bottom:1px solid #e5e7eb"></td>
        <td class="val" style="border-bottom:1px solid #e5e7eb;font-weight:bold;text-decoration:underline">Ibu</td>
    </tr>
    <?php
    $itemD_pairs = [
        [18, 'Nama Ayah',    esc($dash($siswa->nama_ayah)),    21, 'Nama Ibu',    esc($dash($siswa->nama_ibu))],
        [19, 'Pekerjaan',    esc($dash($siswa->pekerjaan_ayah)), 22, 'Pekerjaan', esc($dash($siswa->pekerjaan_ibu))],
        [20, 'No. HP',       esc($dash($siswa->no_hp_ayah)),   23, 'No. HP',     esc($dash($siswa->no_hp_ibu))],
    ];
    foreach ($itemD_pairs as [$no1, $lbl1, $val1, $no2, $lbl2, $val2]):
    ?>
    <tr>
        <td class="no"><?= $no1 ?>.</td>
        <td class="lbl"><?= $lbl1 ?></td>
        <td class="sep">:</td>
        <td class="val" style="width:50%">
            <?= $val1 ?>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <strong style="color:#374151"><?= $no2 ?>. <?= $lbl2 ?></strong>
            &nbsp;:&nbsp; <?= $val2 ?>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

<!-- ══ E. KETERANGAN PENDIDIKAN ══ -->
<div class="sec-header">E. KETERANGAN PENDIDIKAN</div>
<table class="data-tbl">
    <?php
    $itemE = [
        [24, 'Asal Sekolah',        esc($dash($siswa->asal_sekolah))],
        [25, 'Alamat Sekolah Asal', '-'],
        [26, 'Tahun Lulus',         esc($dash($siswa->tahun_lulus_smp))],
    ];
    foreach ($itemE as [$no, $lbl, $val]):
    ?>
    <tr>
        <td class="no"><?= $no ?>.</td>
        <td class="lbl"><?= $lbl ?></td>
        <td class="sep">:</td>
        <td class="val"><?= $val ?></td>
    </tr>
    <?php endforeach; ?>
</table>

<!-- ══ F. PENEMPATAN DI SMK AL MUNAWWIR IIBS ══ -->
<div class="sec-header">F. PENEMPATAN DI SMK AL MUNAWWIR IIBS</div>
<table class="penempatan-tbl">
    <thead>
        <tr>
            <th class="no">NO</th>
            <th>TAHUN AJARAN</th>
            <th>KELAS</th>
            <th>JURUSAN</th>
            <th>NAMA KELAS</th>
            <th>STATUS</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="no">1</td>
            <td><?= esc($ta_label) ?></td>
            <td><?= esc($siswa->kelas_tingkat ?? 'X') ?></td>
            <td><?= esc($siswa->jurusan_kode ?? $siswa->jurusan_nama ?? '-') ?></td>
            <td><?= esc($siswa->kelas_nama ?? '-') ?></td>
            <td><?= ucfirst(str_replace('_', ' ', $siswa->status_siswa ?? 'Aktif')) ?></td>
        </tr>
        <tr><td class="no">2</td><td></td><td></td><td></td><td></td><td></td></tr>
        <tr><td class="no">3</td><td></td><td></td><td></td><td></td><td></td></tr>
    </tbody>
</table>

<!-- ══ TTD ══ -->
<div class="ttd-wrap">
    <div class="ttd-left">
        <div class="ttd-jabatan">Orang Tua / Wali,</div>
        <div class="ttd-garis">&nbsp;</div>
        <div style="text-align:center;margin-top:2px">
            <div style="font-size:9.5px;color:#374151">( <?= esc($siswa->nama_ayah ?? '...........................') ?> )</div>
        </div>
    </div>
    <div class="ttd-right">
        <div class="ttd-kota">Banyuwangi, .....................................</div>
        <div class="ttd-jabatan">Kepala Sekolah,</div>
        <div class="ttd-garis">Ahmad Azmi Khoirul Umam, S.Pt., M.Pt., M.Sc</div>
        <div class="ttd-nip">&nbsp;</div>
    </div>
</div>

<!-- ══ FOOTER INFO ══ -->
<div class="footer-info">
    Dicetak: <?= $tglCetak ?> WIB &nbsp;|&nbsp;
    NIS: <?= esc($siswa->nis) ?> &nbsp;|&nbsp;
    NISN: <?= esc($siswa->nisn ?? '-') ?>
</div>

</body>
</html>
<?php endif; ?>