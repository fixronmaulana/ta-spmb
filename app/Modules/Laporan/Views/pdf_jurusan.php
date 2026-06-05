<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
  body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #1a202c; margin: 0; padding: 20px; }
  h1   { font-size: 16px; font-weight: bold; margin: 0; }
  h2   { font-size: 13px; font-weight: bold; margin: 0; }
  .header     { border-bottom: 2px solid #1d4ed8; padding-bottom: 10px; margin-bottom: 15px; }
  .sekolah    { color: #1d4ed8; }
  .subtitle   { color: #6b7280; font-size: 10px; }
  table       { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
  th          { background-color: #1d4ed8; color: white; padding: 6px 8px; text-align: center; font-size: 10px; }
  td          { padding: 5px 8px; border-bottom: 1px solid #e5e7eb; font-size: 10px; }
  tr:nth-child(even) td { background-color: #f0f4ff; }
  .total-row  { background-color: #dbeafe !important; font-weight: bold; }
  .total-row td { background-color: #dbeafe; font-weight: bold; }
  .stat-grid  { display: table; width: 100%; margin-bottom: 15px; }
  .stat-box   { display: table-cell; width: 25%; padding: 8px; text-align: center; border: 1px solid #e5e7eb; }
  .stat-val   { font-size: 22px; font-weight: bold; color: #1d4ed8; }
  .stat-label { font-size: 9px; color: #6b7280; }
  .section-title { font-size: 12px; font-weight: bold; color: #374151; margin: 12px 0 6px 0; border-left: 4px solid #1d4ed8; padding-left: 8px; }
  .footer     { text-align: right; color: #9ca3af; font-size: 9px; margin-top: 20px; border-top: 1px solid #e5e7eb; padding-top: 8px; }
  .badge-ok   { color: #065f46; background: #d1fae5; padding: 2px 6px; border-radius: 10px; font-size: 9px; }
  .badge-warn { color: #92400e; background: #fef3c7; padding: 2px 6px; border-radius: 10px; font-size: 9px; }
</style>
</head>
<body>

<div class="header">
  <table style="border:none;margin:0;">
    <tr>
      <td style="width:65%;border:none;padding:0;">
        <h1 class="sekolah">SMK Al-Munawwir IIBS</h1>
        <h2>Laporan Rekapitulasi Per Program Keahlian</h2>
        <p class="subtitle">
          <?php if ($periode): ?>Periode: <?= esc($periode->nama) ?> (<?= esc($periode->tahun_ajaran) ?>)<br><?php endif; ?>
          Dicetak: <?= $tglCetak ?>
        </p>
      </td>
      <td style="text-align:right;border:none;padding:0;vertical-align:top;">
        <p class="subtitle" style="color:#1d4ed8;font-size:11px;font-weight:bold;">PER JURUSAN</p>
      </td>
    </tr>
  </table>
</div>

<?php
$totKuota = $totPdft = $totDtrm = $totDU = $totAktif = 0;
foreach ($byJurusan as $r) {
    $totKuota += (int) ($r->kuota              ?? 0);
    $totPdft  += (int) ($r->total_daftar        ?? 0);
    $totDtrm  += (int) ($r->total_lulus         ?? 0);
    $totDU    += (int) ($r->total_daftar_ulang  ?? 0);
    $totAktif += (int) ($r->total_siswa_aktif   ?? 0);
}
?>

<!-- KPI -->
<div class="stat-grid">
  <div class="stat-box"><div class="stat-val"><?= $totPdft ?></div><div class="stat-label">Total Pendaftar</div></div>
  <div class="stat-box"><div class="stat-val" style="color:#065f46;"><?= $totDtrm ?></div><div class="stat-label">Diterima / Lulus</div></div>
  <div class="stat-box"><div class="stat-val" style="color:#0369a1;"><?= $totDU ?></div><div class="stat-label">Daftar Ulang</div></div>
  <div class="stat-box"><div class="stat-val" style="color:#b45309;"><?= $totAktif ?></div><div class="stat-label">Siswa Aktif</div></div>
</div>

<!-- Tabel Per Jurusan -->
<p class="section-title">Rekap Per Program Keahlian</p>
<table>
  <thead>
    <tr>
      <th style="text-align:left;">Program Keahlian</th>
      <th>Kode</th>
      <th>Kuota</th>
      <th>Pendaftar</th>
      <th>Diterima</th>
      <th>Daftar Ulang</th>
      <th>Siswa Aktif</th>
      <th>% Terisi</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($byJurusan as $row):
      $pct = $row->kuota > 0 ? round($row->total_lulus / $row->kuota * 100) : 0;
    ?>
    <tr>
      <td><?= esc($row->jurusan) ?></td>
      <td style="text-align:center;"><?= esc($row->kode) ?></td>
      <td style="text-align:center;"><?= $row->kuota ?></td>
      <td style="text-align:center;"><?= $row->total_daftar ?></td>
      <td style="text-align:center;color:#065f46;font-weight:bold;"><?= $row->total_lulus ?></td>
      <td style="text-align:center;"><?= $row->total_daftar_ulang ?? 0 ?></td>
      <td style="text-align:center;"><?= $row->total_siswa_aktif ?? 0 ?></td>
      <td style="text-align:center;">
        <span class="<?= $pct >= 80 ? 'badge-ok' : 'badge-warn' ?>"><?= $pct ?>%</span>
      </td>
    </tr>
    <?php endforeach; ?>
    <tr class="total-row">
      <td><strong>TOTAL</strong></td>
      <td></td>
      <td style="text-align:center;"><?= $totKuota ?></td>
      <td style="text-align:center;"><?= $totPdft ?></td>
      <td style="text-align:center;color:#065f46;"><?= $totDtrm ?></td>
      <td style="text-align:center;"><?= $totDU ?></td>
      <td style="text-align:center;"><?= $totAktif ?></td>
      <td style="text-align:center;"><?= $totKuota > 0 ? round($totDtrm / $totKuota * 100) : 0 ?>%</td>
    </tr>
  </tbody>
</table>

<div class="footer">
  SMK Al-Munawwir IIBS &mdash; Sistem PPDB Digital &mdash; Laporan Per Jurusan &mdash; Dicetak <?= $tglCetak ?>
</div>
</body>
</html>