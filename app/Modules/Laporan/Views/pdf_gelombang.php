<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
  body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #1a202c; margin: 0; padding: 20px; }
  h1   { font-size: 16px; font-weight: bold; margin: 0; }
  h2   { font-size: 13px; font-weight: bold; margin: 0; }
  .header     { border-bottom: 2px solid #059669; padding-bottom: 10px; margin-bottom: 15px; }
  .sekolah    { color: #059669; }
  .subtitle   { color: #6b7280; font-size: 10px; }
  table       { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
  th          { background-color: #059669; color: white; padding: 6px 8px; text-align: center; font-size: 10px; }
  td          { padding: 5px 8px; border-bottom: 1px solid #e5e7eb; font-size: 10px; }
  tr:nth-child(even) td { background-color: #f0fdf4; }
  .total-row td { background-color: #d1fae5; font-weight: bold; }
  .stat-grid  { display: table; width: 100%; margin-bottom: 15px; }
  .stat-box   { display: table-cell; width: 25%; padding: 8px; text-align: center; border: 1px solid #e5e7eb; }
  .stat-val   { font-size: 22px; font-weight: bold; color: #059669; }
  .stat-label { font-size: 9px; color: #6b7280; }
  .section-title { font-size: 12px; font-weight: bold; color: #374151; margin: 12px 0 6px 0; border-left: 4px solid #059669; padding-left: 8px; }
  .footer     { text-align: right; color: #9ca3af; font-size: 9px; margin-top: 20px; border-top: 1px solid #e5e7eb; padding-top: 8px; }
  .bar-wrap   { background: #e5e7eb; border-radius: 4px; height: 10px; width: 100%; }
  .bar-fill   { background: #059669; border-radius: 4px; height: 10px; }
</style>
</head>
<body>

<div class="header">
  <table style="border:none;margin:0;">
    <tr>
      <td style="width:65%;border:none;padding:0;">
        <h1 class="sekolah">SMK Al-Munawwir IIBS</h1>
        <h2>Laporan Rekapitulasi Per Gelombang Pendaftaran</h2>
        <p class="subtitle">Dicetak: <?= $tglCetak ?></p>
      </td>
      <td style="text-align:right;border:none;padding:0;vertical-align:top;">
        <p class="subtitle" style="color:#059669;font-size:11px;font-weight:bold;">PER GELOMBANG</p>
      </td>
    </tr>
  </table>
</div>

<?php
$totPdft = $totDtrm = $totDtlk = $totMngg = 0;
foreach ($byGelombang as $g) {
    $totPdft += (int) ($g->pendaftar ?? 0);
    $totDtrm += (int) ($g->diterima  ?? 0);
    $totDtlk += (int) ($g->ditolak   ?? 0);
    $totMngg += (int) ($g->menunggu  ?? 0);
}
?>

<!-- KPI -->
<div class="stat-grid">
  <div class="stat-box"><div class="stat-val"><?= $totPdft ?></div><div class="stat-label">Total Pendaftar</div></div>
  <div class="stat-box"><div class="stat-val" style="color:#065f46;"><?= $totDtrm ?></div><div class="stat-label">Diterima</div></div>
  <div class="stat-box"><div class="stat-val" style="color:#991b1b;"><?= $totDtlk ?></div><div class="stat-label">Ditolak</div></div>
  <div class="stat-box"><div class="stat-val" style="color:#92400e;"><?= $totMngg ?></div><div class="stat-label">Menunggu</div></div>
</div>

<!-- Tabel Per Gelombang -->
<p class="section-title">Rekap Per Gelombang / Periode</p>
<table>
  <thead>
    <tr>
      <th style="text-align:left;">Gelombang / Periode</th>
      <th>Pendaftar</th>
      <th>Diterima</th>
      <th>Ditolak</th>
      <th>Menunggu</th>
      <th>Tingkat Penerimaan</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($byGelombang as $row):
      $pct = (int) ($row->pendaftar ?? 0) > 0
        ? round((int) ($row->diterima ?? 0) / (int) ($row->pendaftar ?? 0) * 100)
        : 0;
    ?>
    <tr>
      <td><?= esc($row->gelombang) ?></td>
      <td style="text-align:center;font-weight:bold;"><?= $row->pendaftar ?></td>
      <td style="text-align:center;color:#065f46;font-weight:bold;"><?= $row->diterima ?></td>
      <td style="text-align:center;color:#991b1b;"><?= $row->ditolak ?></td>
      <td style="text-align:center;color:#92400e;"><?= $row->menunggu ?></td>
      <td style="text-align:center;"><?= $pct ?>%</td>
    </tr>
    <?php endforeach; ?>
    <tr class="total-row">
      <td><strong>TOTAL</strong></td>
      <td style="text-align:center;"><?= $totPdft ?></td>
      <td style="text-align:center;color:#065f46;"><?= $totDtrm ?></td>
      <td style="text-align:center;color:#991b1b;"><?= $totDtlk ?></td>
      <td style="text-align:center;color:#92400e;"><?= $totMngg ?></td>
      <td style="text-align:center;"><?= $totPdft > 0 ? round($totDtrm / $totPdft * 100) : 0 ?>%</td>
    </tr>
  </tbody>
</table>

<div class="footer">
  SMK Al-Munawwir IIBS &mdash; Sistem PPDB Digital &mdash; Laporan Per Gelombang &mdash; Dicetak <?= $tglCetak ?>
</div>
</body>
</html>