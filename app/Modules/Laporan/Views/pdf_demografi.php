<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
  body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #1a202c; margin: 0; padding: 20px; }
  h1   { font-size: 16px; font-weight: bold; margin: 0; }
  h2   { font-size: 13px; font-weight: bold; margin: 0; }
  .header     { border-bottom: 2px solid #7c3aed; padding-bottom: 10px; margin-bottom: 15px; }
  .sekolah    { color: #7c3aed; }
  .subtitle   { color: #6b7280; font-size: 10px; }
  table       { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
  th          { background-color: #7c3aed; color: white; padding: 6px 8px; text-align: center; font-size: 10px; }
  td          { padding: 5px 8px; border-bottom: 1px solid #e5e7eb; font-size: 10px; }
  tr:nth-child(even) td { background-color: #faf5ff; }
  .section-title { font-size: 12px; font-weight: bold; color: #374151; margin: 14px 0 6px 0; border-left: 4px solid #7c3aed; padding-left: 8px; }
  .footer     { text-align: right; color: #9ca3af; font-size: 9px; margin-top: 20px; border-top: 1px solid #e5e7eb; padding-top: 8px; }
  .two-col    { display: table; width: 100%; }
  .col-left   { display: table-cell; width: 48%; padding-right: 10px; vertical-align: top; }
  .col-right  { display: table-cell; width: 48%; padding-left: 10px; vertical-align: top; }
  .bar-wrap   { background: #ede9fe; border-radius: 3px; height: 9px; width: 100%; margin-top: 2px; }
  .bar-fill   { background: #7c3aed; border-radius: 3px; height: 9px; }
</style>
</head>
<body>

<div class="header">
  <table style="border:none;margin:0;">
    <tr>
      <td style="width:65%;border:none;padding:0;">
        <h1 class="sekolah">SMK Al-Munawwir IIBS</h1>
        <h2>Laporan Demografi Pendaftar</h2>
        <p class="subtitle">Dicetak: <?= $tglCetak ?></p>
      </td>
      <td style="text-align:right;border:none;padding:0;vertical-align:top;">
        <p class="subtitle" style="color:#7c3aed;font-size:11px;font-weight:bold;">DEMOGRAFI</p>
      </td>
    </tr>
  </table>
</div>

<div class="two-col">
  <!-- Jenis Kelamin -->
  <div class="col-left">
    <p class="section-title">Jenis Kelamin</p>
    <?php
    $totalGender = array_sum(array_map(fn($r) => (int) $r->total, $genderData));
    ?>
    <table>
      <thead>
        <tr>
          <th style="text-align:left;">Jenis Kelamin</th>
          <th>Jumlah</th>
          <th>%</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($genderData as $row):
          $pct = $totalGender > 0 ? round($row->total / $totalGender * 100, 1) : 0;
        ?>
        <tr>
          <td><?= esc($row->nama) ?></td>
          <td style="text-align:center;font-weight:bold;"><?= $row->total ?></td>
          <td style="text-align:center;"><?= $pct ?>%</td>
        </tr>
        <?php endforeach; ?>
        <tr>
          <td style="font-weight:bold;">Total</td>
          <td style="text-align:center;font-weight:bold;"><?= $totalGender ?></td>
          <td style="text-align:center;">100%</td>
        </tr>
      </tbody>
    </table>
  </div>

  <!-- Asal Sekolah -->
  <div class="col-right">
    <p class="section-title">Top Asal Sekolah</p>
    <?php
    $maxAsal = max(array_map(fn($r) => (int) $r->total, $asalSekolah ?: [['total' => 1]]));
    ?>
    <table>
      <thead>
        <tr>
          <th style="text-align:left;">Asal Sekolah</th>
          <th>Jumlah</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($asalSekolah as $i => $row): ?>
        <tr>
          <td><?= esc($row->nama) ?></td>
          <td style="text-align:center;font-weight:bold;"><?= $row->total ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($asalSekolah)): ?>
        <tr><td colspan="2" style="text-align:center;color:#9ca3af;">Belum ada data</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Distribusi Agama (jika ada) -->
<?php if (!empty($agamaData)): ?>
<p class="section-title">Distribusi Agama</p>
<?php $totalAgama = array_sum(array_map(fn($r) => (int) $r->total, $agamaData)); ?>
<table>
  <thead>
    <tr>
      <th style="text-align:left;">Agama</th>
      <th>Jumlah</th>
      <th>Persentase</th>
      <th style="width:40%;">Visualisasi</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($agamaData as $row):
      $pct = $totalAgama > 0 ? round($row->total / $totalAgama * 100, 1) : 0;
    ?>
    <tr>
      <td><?= esc(ucfirst(strtolower($row->nama ?? '-'))) ?></td>
      <td style="text-align:center;font-weight:bold;"><?= $row->total ?></td>
      <td style="text-align:center;"><?= $pct ?>%</td>
      <td>
        <div class="bar-wrap">
          <div class="bar-fill" style="width:<?= $pct ?>%;"></div>
        </div>
      </td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<?php endif; ?>

<div class="footer">
  SMK Al-Munawwir IIBS &mdash; Sistem PPDB Digital &mdash; Laporan Demografi &mdash; Dicetak <?= $tglCetak ?>
</div>
</body>
</html>