<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
  body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #1a202c; margin: 0; padding: 20px; }
  h1   { font-size: 16px; font-weight: bold; margin: 0; }
  h2   { font-size: 13px; font-weight: bold; margin: 0; }
  .header     { border-bottom: 2px solid #b45309; padding-bottom: 10px; margin-bottom: 15px; }
  .sekolah    { color: #b45309; }
  .subtitle   { color: #6b7280; font-size: 10px; }
  table       { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
  th          { background-color: #b45309; color: white; padding: 6px 8px; text-align: center; font-size: 10px; }
  td          { padding: 5px 8px; border-bottom: 1px solid #e5e7eb; font-size: 10px; }
  tr:nth-child(even) td { background-color: #fffbeb; }
  .section-title { font-size: 12px; font-weight: bold; color: #374151; margin: 14px 0 6px 0; border-left: 4px solid #b45309; padding-left: 8px; }
  .footer     { text-align: right; color: #9ca3af; font-size: 9px; margin-top: 20px; border-top: 1px solid #e5e7eb; padding-top: 8px; }
  .badge-up   { color: #065f46; background: #d1fae5; padding: 2px 6px; border-radius: 10px; font-size: 9px; }
  .badge-dn   { color: #991b1b; background: #fee2e2; padding: 2px 6px; border-radius: 10px; font-size: 9px; }
  .badge-na   { color: #6b7280; background: #f3f4f6; padding: 2px 6px; border-radius: 10px; font-size: 9px; }
  .bar-wrap   { background: #fde68a; border-radius: 3px; height: 9px; width: 100%; margin-top: 2px; }
  .bar-fill   { background: #b45309; border-radius: 3px; height: 9px; }
  .summary-box { border: 1px solid #e5e7eb; padding: 10px 14px; margin-bottom: 14px; border-radius: 4px; background: #fffbeb; }
  .summary-box p { margin: 2px 0; font-size: 10px; }
</style>
</head>
<body>

<div class="header">
  <table style="border:none;margin:0;">
    <tr>
      <td style="width:65%;border:none;padding:0;">
        <h1 class="sekolah">SMK Al-Munawwir IIBS</h1>
        <h2>Laporan Tren Pendaftaran Tahunan</h2>
        <p class="subtitle">Dicetak: <?= $tglCetak ?></p>
      </td>
      <td style="text-align:right;border:none;padding:0;vertical-align:top;">
        <p class="subtitle" style="color:#b45309;font-size:11px;font-weight:bold;">TREN TAHUNAN</p>
      </td>
    </tr>
  </table>
</div>

<?php
// Hitung max pendaftar untuk bar chart proporsional
$maxPdft = max(array_map(fn($r) => (int) ($r->pendaftar ?? 0), $trenTahunan ?: [['pendaftar' => 1]]));
$maxPdft = max($maxPdft, 1);

$totalAllPdft = array_sum(array_map(fn($r) => (int) ($r->pendaftar ?? 0), $trenTahunan));
$totalAllDtrm = array_sum(array_map(fn($r) => (int) ($r->diterima  ?? 0), $trenTahunan));
$jumlahPeriode = count($trenTahunan);

// Hitung tren terakhir
$first = $trenTahunan[0]                      ?? null;
$last  = $trenTahunan[$jumlahPeriode - 1]     ?? null;
$overallGrowth = ($first && (int) $first->pendaftar > 0 && $jumlahPeriode > 1)
    ? round(((int) $last->pendaftar - (int) $first->pendaftar) / (int) $first->pendaftar * 100)
    : null;
?>

<!-- Summary -->
<div class="summary-box">
  <p><strong>Total Periode Tercatat:</strong> <?= $jumlahPeriode ?> tahun ajaran</p>
  <p><strong>Total Kumulatif Pendaftar:</strong> <?= number_format($totalAllPdft) ?> siswa</p>
  <p><strong>Total Kumulatif Diterima:</strong> <?= number_format($totalAllDtrm) ?> siswa</p>
  <?php if ($overallGrowth !== null): ?>
  <p><strong>Pertumbuhan Keseluruhan:</strong>
    <span class="<?= $overallGrowth >= 0 ? 'badge-up' : 'badge-dn' ?>"><?= ($overallGrowth >= 0 ? '+' : '') . $overallGrowth ?>%</span>
    (<?= esc($first->tahun_ajaran) ?> → <?= esc($last->tahun_ajaran) ?>)
  </p>
  <?php endif; ?>
</div>

<!-- Tabel Tren -->
<p class="section-title">Data Tren Per Tahun Ajaran</p>
<table>
  <thead>
    <tr>
      <th>No</th>
      <th>Tahun Ajaran</th>
      <th>Pendaftar</th>
      <th>Diterima</th>
      <th>Ditolak</th>
      <th>% Diterima</th>
      <th>Pertumbuhan</th>
      <th style="width:25%;">Visualisasi Pendaftar</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($trenTahunan as $i => $row):
      $pdft = (int) ($row->pendaftar ?? 0);
      $dtrm = (int) ($row->diterima  ?? 0);
      $dtlk = (int) ($row->ditolak   ?? 0);
      $pctDtrm = $pdft > 0 ? round($dtrm / $pdft * 100) : 0;
      $barPct  = round($pdft / $maxPdft * 100);

      $prev = $trenTahunan[$i - 1] ?? null;
      if ($prev && (int) $prev->pendaftar > 0) {
          $growth    = round(((int) $row->pendaftar - (int) $prev->pendaftar) / (int) $prev->pendaftar * 100, 1);
          $growthStr = ($growth >= 0 ? '+' : '') . $growth . '%';
          $growthClass = $growth >= 0 ? 'badge-up' : 'badge-dn';
      } else {
          $growthStr   = '-';
          $growthClass = 'badge-na';
      }
    ?>
    <tr>
      <td style="text-align:center;"><?= $i + 1 ?></td>
      <td style="font-weight:bold;"><?= esc($row->tahun_ajaran) ?></td>
      <td style="text-align:center;font-weight:bold;"><?= $pdft ?></td>
      <td style="text-align:center;color:#065f46;font-weight:bold;"><?= $dtrm ?></td>
      <td style="text-align:center;color:#991b1b;"><?= $dtlk ?></td>
      <td style="text-align:center;"><?= $pctDtrm ?>%</td>
      <td style="text-align:center;"><span class="<?= $growthClass ?>"><?= $growthStr ?></span></td>
      <td>
        <div class="bar-wrap">
          <div class="bar-fill" style="width:<?= $barPct ?>%;"></div>
        </div>
        <div style="font-size:8px;color:#6b7280;margin-top:1px;"><?= $pdft ?> orang</div>
      </td>
    </tr>
    <?php endforeach; ?>
    <?php if (empty($trenTahunan)): ?>
    <tr><td colspan="8" style="text-align:center;color:#9ca3af;padding:20px;">Belum ada data tren</td></tr>
    <?php endif; ?>
  </tbody>
</table>

<div class="footer">
  SMK Al-Munawwir IIBS &mdash; Sistem PPDB Digital &mdash; Laporan Tren Tahunan &mdash; Dicetak <?= $tglCetak ?>
</div>
</body>
</html>