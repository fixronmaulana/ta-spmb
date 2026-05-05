<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
  body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #1a202c; margin: 0; padding: 20px; }
  h1 { font-size: 16px; font-weight: bold; margin: 0; }
  h2 { font-size: 13px; font-weight: bold; margin: 0; }
  .header { border-bottom: 2px solid #1d4ed8; padding-bottom: 10px; margin-bottom: 15px; }
  .sekolah { color: #1d4ed8; }
  .subtitle { color: #6b7280; font-size: 10px; }
  table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
  th { background-color: #1d4ed8; color: white; padding: 6px 8px; text-align: center; font-size: 10px; }
  td { padding: 5px 8px; border-bottom: 1px solid #e5e7eb; font-size: 10px; }
  tr:nth-child(even) td { background-color: #f9fafb; }
  .stat-grid { display: table; width: 100%; margin-bottom: 15px; }
  .stat-box { display: table-cell; width: 25%; padding: 8px; text-align: center; border: 1px solid #e5e7eb; }
  .stat-val { font-size: 22px; font-weight: bold; color: #1d4ed8; }
  .stat-label { font-size: 9px; color: #6b7280; }
  .section-title { font-size: 12px; font-weight: bold; color: #374151; margin: 12px 0 6px 0; border-left: 4px solid #1d4ed8; padding-left: 8px; }
  .footer { text-align: right; color: #9ca3af; font-size: 9px; margin-top: 20px; border-top: 1px solid #e5e7eb; padding-top: 8px; }
  .badge-lulus { color: #065f46; background: #d1fae5; padding: 2px 6px; border-radius: 10px; font-size: 9px; }
  .badge-tidak { color: #991b1b; background: #fee2e2; padding: 2px 6px; border-radius: 10px; font-size: 9px; }
</style>
</head>
<body>

<div class="header">
  <table style="border: none; margin: 0;">
    <tr>
      <td style="width: 60%; border: none; padding: 0;">
        <h1 class="sekolah">SMK Al-Munawwir IIBS</h1>
        <h2>Rekap Laporan PPDB</h2>
        <p class="subtitle">
            <?php if ($periode): ?>
            Periode: <?= esc($periode->nama) ?> (<?= esc($periode->tahun_ajaran) ?>)<br>
            <?php endif; ?>
            Dicetak: <?= $tglCetak ?>
        </p>
      </td>
      <td style="text-align: right; border: none; padding: 0; vertical-align: top;">
        <p class="subtitle" style="color: #1d4ed8; font-size: 11px; font-weight: bold;">PPDB REPORT</p>
      </td>
    </tr>
  </table>
</div>

<!-- Statistik Ringkas -->
<p class="section-title">Statistik Ringkasan</p>
<div class="stat-grid">
    <div class="stat-box"><div class="stat-val"><?= $stats['total'] ?? 0 ?></div><div class="stat-label">Total Pendaftar</div></div>
    <div class="stat-box"><div class="stat-val" style="color: #065f46;"><?= $stats['lulus'] ?? 0 ?></div><div class="stat-label">Diterima (Lulus)</div></div>
    <div class="stat-box"><div class="stat-val" style="color: #991b1b;"><?= $stats['tidak_lulus'] ?? 0 ?></div><div class="stat-label">Tidak Diterima</div></div>
    <div class="stat-box"><div class="stat-val" style="color: #047857;"><?= $stats['siswa_aktif'] ?? 0 ?></div><div class="stat-label">Siswa Aktif</div></div>
</div>

<!-- Per Jurusan -->
<p class="section-title">Rekap per Program Keahlian</p>
<table>
    <thead>
        <tr>
            <th style="text-align: left;">Program Keahlian</th>
            <th>Kode</th>
            <th>Kuota</th>
            <th>Pendaftar</th>
            <th>Lulus</th>
            <th>Tidak Lulus</th>
            <th>% Terisi</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($byJurusan as $row): ?>
        <?php $pct = $row->kuota > 0 ? round($row->total_lulus / $row->kuota * 100) : 0; ?>
        <tr>
            <td><?= esc($row->jurusan) ?></td>
            <td style="text-align: center;"><?= esc($row->kode) ?></td>
            <td style="text-align: center;"><?= $row->kuota ?></td>
            <td style="text-align: center;"><?= $row->total_daftar ?></td>
            <td style="text-align: center; color: #065f46; font-weight: bold;"><?= $row->total_lulus ?></td>
            <td style="text-align: center; color: #991b1b;"><?= $row->total_daftar - $row->total_lulus ?></td>
            <td style="text-align: center;">
                <span class="<?= $pct >= 80 ? 'badge-lulus' : 'badge-tidak' ?>"><?= $pct ?>%</span>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- Status Breakdown -->
<p class="section-title">Rincian Status Pendaftaran</p>
<table>
    <thead>
        <tr>
            <th style="text-align: left;">Status</th>
            <th>Jumlah</th>
            <th>Persentase</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $total = max(1, $stats['total'] ?? 1);
        $statusLabels = [
            'draft'=>'Draft','submitted'=>'Menunggu Verifikasi','verifikasi'=>'Dalam Verifikasi',
            'seleksi'=>'Dalam Seleksi','lulus'=>'Lulus','tidak_lulus'=>'Tidak Lulus',
            'daftar_ulang'=>'Daftar Ulang','siswa_aktif'=>'Siswa Aktif',
        ];
        foreach ($statusLabels as $key => $label):
        $count = $stats[$key] ?? 0;
        if ($count === 0) continue;
        ?>
        <tr>
            <td><?= $label ?></td>
            <td style="text-align: center; font-weight: bold;"><?= $count ?></td>
            <td style="text-align: center;"><?= round($count / $total * 100, 1) ?>%</td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<div class="footer">
    SMK Al-Munawwir IIBS &mdash; Sistem PPDB Digital &mdash; Dicetak <?= $tglCetak ?>
</div>
</body>
</html>
