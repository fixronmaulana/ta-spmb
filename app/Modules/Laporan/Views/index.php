<!--
    File : app/Modules/Laporan/Views/index.php
    Route: kepala-sekolah/laporan
    Sesuai mockup React: LaporanRekapitulasiPage

    Struktur:
      1. Page Header        — judul + filter tahun ajaran + filter gelombang
      2. KPI Cards (4)      — Total Pendaftar, Diterima, Daftar Ulang, Siswa Aktif
      3. Tombol Export      — PDF | Excel | Print
      4. Tabs               — Per Jurusan | Per Gelombang | Demografi | Tren Tahunan
         Tab Jurusan        — Bar Chart + Doughnut Chart + Tabel rekap (+ kolom % Terisi)
         Tab Gelombang      — Bar Chart + Summary Cards + Tabel rekap
         Tab Demografi      — Doughnut Jenis Kelamin + Bar Horizontal Top-5 Asal Sekolah
         Tab Tren Tahunan   — Bar Chart perbandingan + Growth Cards
-->

<?php
// ── Palet warna chart ─────────────────────────────────────────────────────────
$chartColors = [
    'hsl(220,54%,40%)',   // chart-1  ~ primary
    'hsl(142,71%,45%)',   // chart-2  ~ success
    'hsl(38,92%,50%)',    // chart-3  ~ warning
    'hsl(199,89%,48%)',   // chart-4  ~ info
    'hsl(262,70%,58%)',   // chart-5  ~ purple
    'hsl(350,72%,55%)',   // chart-6  ~ red-ish
];

$tahunAjaran  = $periode->tahun_ajaran ?? date('Y') . '/' . (date('Y') + 1);

// ── Hitung total untuk tabel jurusan ─────────────────────────────────────────
$totalKuota       = 0;
$totalPendaftar   = 0;
$totalDiterima    = 0;
$totalDaftarUlang = 0;
$totalAktif       = 0;
foreach ($byJurusan as $r) {
    $totalKuota       += (int) ($r->kuota              ?? 0);
    $totalPendaftar   += (int) ($r->total_daftar        ?? 0);
    $totalDiterima    += (int) ($r->total_lulus         ?? 0);
    $totalDaftarUlang += (int) ($r->total_daftar_ulang  ?? 0);
    $totalAktif       += (int) ($r->total_siswa_aktif   ?? 0);
}
$pctTerisiTotal = $totalKuota > 0 ? round($totalAktif / $totalKuota * 100) : 0;

// ── Total gelombang ───────────────────────────────────────────────────────────
$totGelPendaftar = 0;
$totGelDiterima = 0;
$totGelDitolak = 0;
$totGelMenunggu = 0;
foreach ($byGelombang as $g) {
    $totGelPendaftar += (int) ($g->pendaftar ?? 0);
    $totGelDiterima  += (int) ($g->diterima  ?? 0);
    $totGelDitolak   += (int) ($g->ditolak   ?? 0);
    $totGelMenunggu  += (int) ($g->menunggu  ?? 0);
}
?>

<div class="space-y-6">

    <!-- ══════════════════════════════════════════════════════════
         1. PAGE HEADER
    ══════════════════════════════════════════════════════════ -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold font-serif">Laporan Rekapitulasi</h1>
            <p class="text-sm text-gray-500">Data lengkap penerimaan siswa baru</p>
        </div>

        <div class="flex flex-wrap gap-2">
            <!-- Filter Tahun Ajaran -->
            <div class="relative">
                <select id="filterTahun" onchange="filterChange()"
                    class="appearance-none w-full sm:w-[140px] px-3 py-2 pr-8 border border-gray-300 rounded-xl text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <?php foreach ($periodes as $p): ?>
                        <option value="<?= esc($p->tahun_ajaran) ?>"
                            <?= ($p->tahun_ajaran === $tahunAjaran) ? 'selected' : '' ?>>
                            <?= esc($p->tahun_ajaran) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <svg class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400"
                    fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                </svg>
            </div>

            <!-- Filter Gelombang -->
            <div class="relative">
                <svg class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400"
                    fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z" />
                </svg>
                <select id="filterGelombang" onchange="filterChange()"
                    class="appearance-none w-full sm:w-[160px] pl-9 pr-8 py-2 border border-gray-300 rounded-xl text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="semua">Semua Gelombang</option>
                    <?php foreach ($byGelombang as $i => $g): ?>
                        <option value="<?= $i + 1 ?>"><?= esc($g->gelombang) ?></option>
                    <?php endforeach; ?>
                </select>
                <svg class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400"
                    fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                </svg>
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════
         2. KPI CARDS
         Sesuai mockup: grid 2×2 → lg:4 kolom, tanpa gradient (plain card)
    ══════════════════════════════════════════════════════════ -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">

        <!-- Total Pendaftar -->
        <div class="bg-white rounded-2xl border border-gray-200 p-4">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-full flex items-center justify-center flex-shrink-0"
                    style="background:hsl(220,54%,20%,.10);">
                    <svg class="h-5 w-5" style="color:hsl(220,54%,30%);"
                        fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Total Pendaftar</p>
                    <p class="text-2xl font-bold text-gray-900"><?= number_format($kpi['pendaftar']) ?></p>
                </div>
            </div>
        </div>

        <!-- Diterima -->
        <div class="bg-white rounded-2xl border border-gray-200 p-4">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-full flex items-center justify-center flex-shrink-0"
                    style="background:hsl(142,71%,45%,.10);">
                    <svg class="h-5 w-5" style="color:hsl(142,60%,36%);"
                        fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75l2.25 2.25 3.75-4.5" />
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Diterima</p>
                    <p class="text-2xl font-bold text-gray-900"><?= number_format($kpi['diterima']) ?></p>
                </div>
            </div>
        </div>

        <!-- Daftar Ulang -->
        <div class="bg-white rounded-2xl border border-gray-200 p-4">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-full flex items-center justify-center flex-shrink-0"
                    style="background:hsl(199,89%,48%,.10);">
                    <svg class="h-5 w-5" style="color:hsl(199,70%,40%);"
                        fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Daftar Ulang</p>
                    <p class="text-2xl font-bold text-gray-900"><?= number_format($kpi['daftar_ulang']) ?></p>
                </div>
            </div>
        </div>

        <!-- Siswa Aktif -->
        <div class="bg-white rounded-2xl border border-gray-200 p-4">
            <div class="flex items-center gap-3">
                <div class="h-10 w-10 rounded-full flex items-center justify-center flex-shrink-0"
                    style="background:hsl(43,70%,47%,.12);">
                    <svg class="h-5 w-5" style="color:hsl(43,60%,35%);"
                        fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342" />
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Siswa Aktif</p>
                    <p class="text-2xl font-bold text-gray-900"><?= number_format($kpi['siswa_aktif']) ?></p>
                </div>
            </div>
        </div>

    </div><!-- /KPI cards -->

    <!-- ══════════════════════════════════════════════════════════
         3. TOMBOL EXPORT (standalone row, sesuai mockup)
    ══════════════════════════════════════════════════════════ -->
    <div class="flex flex-wrap gap-2">
        <a href="<?= base_url('kepala-sekolah/laporan/ekspor-pdf') ?>" target="_blank"
            class="inline-flex items-center gap-2 px-3 py-1.5 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
            </svg>
            Export PDF
        </a>
        <a href="<?= base_url('kepala-sekolah/laporan/ekspor-excel') ?>"
            class="inline-flex items-center gap-2 px-3 py-1.5 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
            </svg>
            Export Excel
        </a>
        <button onclick="window.print()"
            class="inline-flex items-center gap-2 px-3 py-1.5 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.056 48.056 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659" />
            </svg>
            Print
        </button>
    </div>

    <!-- ══════════════════════════════════════════════════════════
         4. TABS — Alpine.js tab switcher
         Tab IDs: jurusan | gelombang | demografi | tren
    ══════════════════════════════════════════════════════════ -->
    <div x-data="{ tab: 'jurusan' }" class="space-y-4">

        <!-- Tab List -->
        <div class="flex overflow-x-auto gap-1 p-1 bg-gray-100 rounded-xl">
            <?php
            $tabs = [
                ['id' => 'jurusan',   'label' => 'Per Jurusan'],
                ['id' => 'gelombang', 'label' => 'Per Gelombang'],
                ['id' => 'demografi', 'label' => 'Demografi'],
                ['id' => 'tren',      'label' => 'Tren Tahunan'],
            ];
            foreach ($tabs as $t):
            ?>
                <button type="button" @click="tab = '<?= $t['id'] ?>'"
                    :class="tab === '<?= $t['id'] ?>'
                        ? 'bg-white text-gray-900 shadow-sm'
                        : 'text-gray-500 hover:text-gray-700'"
                    class="px-4 py-2 text-sm font-medium rounded-lg transition-all whitespace-nowrap">
                    <?= $t['label'] ?>
                </button>
            <?php endforeach; ?>
        </div>

        <!-- ╔══════════════════════════════════════════════════════
             TAB: PER JURUSAN
        ═══════════════════════════════════════════════════════╗ -->
        <div x-show="tab === 'jurusan'" x-transition class="space-y-6">

            <div class="grid lg:grid-cols-2 gap-6">

                <!-- Bar Chart: Pendaftar vs Kuota per Jurusan -->
                <div class="bg-white rounded-2xl border border-gray-200">
                    <div class="px-5 py-4 border-b border-gray-100">
                        <h3 class="font-semibold text-gray-900 flex items-center gap-2 text-base">
                            <svg class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                            </svg>
                            Pendaftar vs Kuota per Jurusan
                        </h3>
                    </div>
                    <div class="p-5">
                        <div class="relative w-full" style="height:min(260px,55vw)"><canvas id="chartBarJurusan" height="260"></div></canvas>
                    </div>
                </div>

                <!-- Doughnut: Distribusi Siswa Aktif -->
                <div class="bg-white rounded-2xl border border-gray-200">
                    <div class="px-5 py-4 border-b border-gray-100">
                        <h3 class="font-semibold text-gray-900 text-base">Distribusi Siswa Aktif</h3>
                    </div>
                    <div class="p-5">
                        <div class="relative w-full" style="height:min(260px,55vw)"><canvas id="chartDonutJurusan" height="260"></div></canvas>
                    </div>
                </div>
            </div>

            <!-- Tabel Rekap Per Jurusan -->
            <div class="bg-white rounded-2xl border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-900 text-base">Tabel Rekapitulasi per Jurusan</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[680px] text-sm">
                        <thead>
                            <tr class="border-b bg-gray-50">
                                <th class="py-3 px-4 text-left font-medium text-gray-600">Jurusan</th>
                                <th class="py-3 px-4 text-center font-medium text-gray-600">Kuota</th>
                                <th class="py-3 px-4 text-center font-medium text-gray-600">Pendaftar</th>
                                <th class="py-3 px-4 text-center font-medium text-gray-600">Diterima</th>
                                <th class="py-3 px-4 text-center font-medium text-gray-600">Daftar Ulang</th>
                                <th class="py-3 px-4 text-center font-medium text-gray-600">Siswa Aktif</th>
                                <th class="py-3 px-4 text-center font-medium text-gray-600">% Terisi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <?php foreach ($byJurusan as $row):
                                $kuota      = (int) ($row->kuota              ?? 0);
                                $pdft       = (int) ($row->total_daftar        ?? 0);
                                $diterima   = (int) ($row->total_lulus         ?? 0);
                                $daftarUlang = (int) ($row->total_daftar_ulang ?? 0);
                                $aktif      = (int) ($row->total_siswa_aktif   ?? 0);
                                $pct        = $kuota > 0 ? round($aktif / $kuota * 100) : 0;
                                $badgeColor = $pct >= 90
                                    ? 'background:hsl(142,71%,45%,.12);color:hsl(142,60%,28%);'
                                    : ($pct >= 70
                                        ? 'background:hsl(38,92%,50%,.12);color:hsl(38,60%,32%);'
                                        : 'background:hsl(0,72%,51%,.10);color:hsl(0,55%,40%);');
                            ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="py-3 px-4 font-medium text-gray-900">
                                        <?= esc($row->kode ?? '') ?> — <?= esc($row->jurusan ?? '') ?>
                                    </td>
                                    <td class="py-3 px-4 text-center text-gray-600"><?= $kuota ?></td>
                                    <td class="py-3 px-4 text-center text-gray-600"><?= $pdft ?></td>
                                    <td class="py-3 px-4 text-center text-gray-600"><?= $diterima ?></td>
                                    <td class="py-3 px-4 text-center text-gray-600"><?= $daftarUlang ?></td>
                                    <td class="py-3 px-4 text-center font-semibold"
                                        style="color:hsl(142,60%,30%);"><?= $aktif ?></td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold"
                                            style="<?= $badgeColor ?>"><?= $pct ?>%</span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>

                            <?php if (empty($byJurusan)): ?>
                                <tr>
                                    <td colspan="7" class="py-12 text-center text-sm text-gray-400">Belum ada data</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                        <?php if (!empty($byJurusan)): ?>
                            <tfoot>
                                <tr class="border-t-2 border-gray-200 font-bold" style="background:hsl(220,54%,20%,.04);">
                                    <td class="py-3 px-4 text-gray-900">TOTAL</td>
                                    <td class="py-3 px-4 text-center text-gray-900"><?= $totalKuota ?></td>
                                    <td class="py-3 px-4 text-center text-gray-900"><?= $totalPendaftar ?></td>
                                    <td class="py-3 px-4 text-center text-gray-900"><?= $totalDiterima ?></td>
                                    <td class="py-3 px-4 text-center text-gray-900"><?= $totalDaftarUlang ?></td>
                                    <td class="py-3 px-4 text-center font-bold" style="color:hsl(142,60%,30%);"><?= $totalAktif ?></td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold"
                                            style="background:hsl(142,71%,45%,.12);color:hsl(142,60%,28%);">
                                            <?= $pctTerisiTotal ?>%
                                        </span>
                                    </td>
                                </tr>
                            </tfoot>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
        </div><!-- /tab jurusan -->

        <!-- ╔══════════════════════════════════════════════════════
             TAB: PER GELOMBANG
        ═══════════════════════════════════════════════════════╗ -->
        <div x-show="tab === 'gelombang'" x-transition class="space-y-6">

            <?php if (empty($byGelombang)): ?>
                <div class="bg-white rounded-2xl border border-gray-200 py-16 text-center text-sm text-gray-400">
                    Belum ada data gelombang / pendaftar belum dihubungkan ke periode
                </div>
            <?php else: ?>

                <div class="grid lg:grid-cols-2 gap-6">

                    <!-- Bar Chart per Gelombang -->
                    <div class="bg-white rounded-2xl border border-gray-200">
                        <div class="px-5 py-4 border-b border-gray-100">
                            <h3 class="font-semibold text-gray-900 text-base">Statistik per Gelombang</h3>
                        </div>
                        <div class="p-5">
                            <div class="relative w-full" style="height:min(260px,55vw)"><canvas id="chartBarGelombang" height="260"></div></canvas>
                        </div>
                    </div>

                    <!-- Summary Cards per Gelombang -->
                    <div class="space-y-3">
                        <?php foreach ($byGelombang as $g):
                            $pctLulus = ($g->pendaftar ?? 0) > 0
                                ? round(($g->diterima ?? 0) / ($g->pendaftar) * 100) : 0;
                        ?>
                            <div class="bg-white rounded-2xl border border-gray-200 p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <h4 class="font-semibold text-gray-900 text-sm"><?= esc($g->gelombang) ?></h4>
                                    <span class="text-xs px-2 py-0.5 rounded-full font-semibold"
                                        style="background:hsl(142,71%,45%,.12);color:hsl(142,60%,28%);">
                                        <?= $pctLulus ?>% lulus
                                    </span>
                                </div>
                                <div class="grid grid-cols-2 gap-2 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-gray-500">Pendaftar</span>
                                        <span class="font-medium text-gray-900"><?= $g->pendaftar ?? 0 ?></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-500">Diterima</span>
                                        <span class="font-semibold" style="color:hsl(142,60%,30%);"><?= $g->diterima ?? 0 ?></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-500">Ditolak</span>
                                        <span class="font-medium" style="color:hsl(0,55%,45%);"><?= $g->ditolak ?? 0 ?></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-500">Menunggu</span>
                                        <span class="font-medium" style="color:hsl(38,60%,38%);"><?= $g->menunggu ?? 0 ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Tabel Per Gelombang -->
                <div class="bg-white rounded-2xl border border-gray-200">
                    <div class="px-5 py-4 border-b border-gray-100">
                        <h3 class="font-semibold text-gray-900 text-base">Tabel Rekapitulasi per Gelombang</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[580px] text-sm">
                            <thead>
                                <tr class="border-b bg-gray-50">
                                    <th class="py-3 px-4 text-left font-medium text-gray-600">Gelombang</th>
                                    <th class="py-3 px-4 text-center font-medium text-gray-600">Pendaftar</th>
                                    <th class="py-3 px-4 text-center font-medium text-gray-600">Diterima</th>
                                    <th class="py-3 px-4 text-center font-medium text-gray-600">Ditolak</th>
                                    <th class="py-3 px-4 text-center font-medium text-gray-600">Menunggu</th>
                                    <th class="py-3 px-4 text-center font-medium text-gray-600">% Kelulusan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                <?php foreach ($byGelombang as $g):
                                    $pctL = ($g->pendaftar ?? 0) > 0 ? round(($g->diterima ?? 0) / ($g->pendaftar) * 100) : 0;
                                ?>
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="py-3 px-4 font-medium text-gray-900"><?= esc($g->gelombang) ?></td>
                                        <td class="py-3 px-4 text-center text-gray-600"><?= $g->pendaftar ?? 0 ?></td>
                                        <td class="py-3 px-4 text-center font-semibold" style="color:hsl(142,60%,30%);"><?= $g->diterima ?? 0 ?></td>
                                        <td class="py-3 px-4 text-center" style="color:hsl(0,55%,45%);"><?= $g->ditolak ?? 0 ?></td>
                                        <td class="py-3 px-4 text-center" style="color:hsl(38,60%,38%);"><?= $g->menunggu ?? 0 ?></td>
                                        <td class="py-3 px-4 text-center">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold"
                                                style="background:hsl(142,71%,45%,.12);color:hsl(142,60%,28%);">
                                                <?= $pctL ?>%
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr class="border-t-2 border-gray-200 font-bold" style="background:hsl(220,54%,20%,.04);">
                                    <td class="py-3 px-4 text-gray-900">TOTAL</td>
                                    <td class="py-3 px-4 text-center text-gray-900"><?= $totGelPendaftar ?></td>
                                    <td class="py-3 px-4 text-center font-bold" style="color:hsl(142,60%,30%);"><?= $totGelDiterima ?></td>
                                    <td class="py-3 px-4 text-center" style="color:hsl(0,55%,45%);"><?= $totGelDitolak ?></td>
                                    <td class="py-3 px-4 text-center" style="color:hsl(38,60%,38%);"><?= $totGelMenunggu ?></td>
                                    <td class="py-3 px-4 text-center">
                                        <?php $pctTotL = $totGelPendaftar > 0 ? round($totGelDiterima / $totGelPendaftar * 100) : 0; ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold"
                                            style="background:hsl(142,71%,45%,.12);color:hsl(142,60%,28%);">
                                            <?= $pctTotL ?>%
                                        </span>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

            <?php endif; ?>
        </div><!-- /tab gelombang -->

        <!-- ╔══════════════════════════════════════════════════════
             TAB: DEMOGRAFI
        ═══════════════════════════════════════════════════════╗ -->
        <div x-show="tab === 'demografi'" x-transition class="space-y-6">
            <div class="grid lg:grid-cols-2 gap-6">

                <!-- Doughnut: Distribusi Jenis Kelamin -->
                <div class="bg-white rounded-2xl border border-gray-200">
                    <div class="px-5 py-4 border-b border-gray-100">
                        <h3 class="font-semibold text-gray-900 text-base">Distribusi Jenis Kelamin</h3>
                    </div>
                    <div class="p-5">
                        <?php if (empty($genderData)): ?>
                            <div class="py-12 text-center text-sm text-gray-400">Belum ada data</div>
                        <?php else: ?>
                            <div class="relative w-full" style="height:min(260px,55vw)"><canvas id="chartGender" height="260"></div></canvas>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Horizontal Bar: Top 5 Asal Sekolah -->
                <div class="bg-white rounded-2xl border border-gray-200">
                    <div class="px-5 py-4 border-b border-gray-100">
                        <h3 class="font-semibold text-gray-900 text-base">Top 5 Asal Sekolah</h3>
                    </div>
                    <div class="p-5">
                        <?php if (empty($asalSekolah)): ?>
                            <div class="py-12 text-center text-sm text-gray-400">Belum ada data</div>
                        <?php else: ?>
                            <div class="relative w-full" style="height:min(260px,55vw)"><canvas id="chartAsalSekolah" height="260"></div></canvas>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div><!-- /tab demografi -->

        <!-- ╔══════════════════════════════════════════════════════
             TAB: TREN TAHUNAN
        ═══════════════════════════════════════════════════════╗ -->
        <div x-show="tab === 'tren'" x-transition class="space-y-6">

            <!-- Bar Chart perbandingan antar tahun -->
            <div class="bg-white rounded-2xl border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-900 text-base">
                        Perbandingan Pendaftar &amp; Diterima Antar Tahun
                    </h3>
                </div>
                <div class="p-5">
                    <?php if (empty($trenTahunan)): ?>
                        <div class="py-12 text-center text-sm text-gray-400">Belum ada data tren</div>
                    <?php else: ?>
                        <div class="relative w-full" style="height:min(260px,55vw)"><div class="relative w-full" style="height:min(240px,55vw)"><canvas id="chartTren" height="240"></div></div></canvas>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Growth cards per tahun -->
            <?php if (!empty($trenTahunan)): ?>
                <div class="grid sm:grid-cols-3 gap-4">
                    <?php foreach ($trenTahunan as $i => $t):
                        $prev   = $trenTahunan[$i - 1] ?? null;
                        $growth = ($prev && $prev->pendaftar > 0)
                            ? round(($t->pendaftar - $prev->pendaftar) / $prev->pendaftar * 100)
                            : null;
                    ?>
                        <div class="bg-white rounded-2xl border border-gray-200 p-4 text-center">
                            <p class="text-sm text-gray-500 mb-1">TA <?= esc($t->tahun_ajaran) ?></p>
                            <p class="text-3xl font-bold text-gray-900"><?= number_format($t->pendaftar) ?></p>
                            <p class="text-sm text-gray-400">pendaftar</p>
                            <?php if ($growth !== null): ?>
                                <span class="inline-flex items-center gap-1 mt-2 px-2.5 py-0.5 rounded-full text-xs font-semibold"
                                    style="<?= $growth >= 0
                                                ? 'background:hsl(142,71%,45%,.12);color:hsl(142,60%,28%);'
                                                : 'background:hsl(0,72%,51%,.10);color:hsl(0,55%,40%);' ?>">
                                    <?php if ($growth >= 0): ?>
                                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941" />
                                        </svg>
                                    <?php else: ?>
                                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6L9 12.75l4.306-4.307a11.95 11.95 0 015.814 5.519l2.74 1.22m0 0l-5.94 2.28m5.94-2.28l-2.28-5.941" />
                                        </svg>
                                    <?php endif; ?>
                                    <?= ($growth >= 0 ? '+' : '') . $growth ?>%
                                </span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        </div><!-- /tab tren -->

    </div><!-- /x-data tabs -->

</div><!-- /space-y-6 -->

<!-- ════════════════════════════════════════════════════════════════
     CHART.JS — Semua chart diinisialisasi di bawah
     Chart.js sudah di-load via CDN di app.php layout
════════════════════════════════════════════════════════════════ -->
<?php
// ── Inject data PHP → JSON ───────────────────────────────────────────────────

// Tab Jurusan
$jLabels   = array_map(fn($r) => $r->kode ?? '', $byJurusan);
$jKuota    = array_map(fn($r) => (int)($r->kuota ?? 0), $byJurusan);
$jPdft     = array_map(fn($r) => (int)($r->total_daftar ?? 0), $byJurusan);
$jAktif    = array_map(fn($r) => (int)($r->total_siswa_aktif ?? 0), $byJurusan);

// Tab Gelombang
$gLabels   = array_map(fn($r) => $r->gelombang ?? '', $byGelombang);
$gPdft     = array_map(fn($r) => (int)($r->pendaftar ?? 0), $byGelombang);
$gDiterima = array_map(fn($r) => (int)($r->diterima ?? 0), $byGelombang);
$gDitolak  = array_map(fn($r) => (int)($r->ditolak ?? 0), $byGelombang);

// Tab Demografi
$gdrLabels = array_map(fn($r) => $r->nama ?? '', $genderData);
$gdrValues = array_map(fn($r) => (int)($r->total ?? 0), $genderData);
$sklLabels = array_map(fn($r) => $r->nama ?? '', $asalSekolah);
$sklValues = array_map(fn($r) => (int)($r->total ?? 0), $asalSekolah);

// Tab Tren
$trenLabels = array_map(fn($r) => $r->tahun_ajaran ?? '', $trenTahunan);
$trenPdft   = array_map(fn($r) => (int)($r->pendaftar ?? 0), $trenTahunan);
$trenDtrm   = array_map(fn($r) => (int)($r->diterima ?? 0), $trenTahunan);
?>

<script>
    (function() {
        'use strict';

        Chart.defaults.font.family = "'Plus Jakarta Sans', sans-serif";
        Chart.defaults.font.size = 12;
        Chart.defaults.color = 'hsl(220,15%,50%)';

        const PRIMARY = 'hsl(220,54%,40%)';
        const SUCCESS = 'hsl(142,71%,45%)';
        const WARNING = 'hsl(38,92%,50%)';
        const DANGER = 'hsl(0,72%,51%)';
        const INFO = 'hsl(199,89%,48%)';
        const COLORS = [PRIMARY, SUCCESS, WARNING, INFO, 'hsl(262,70%,58%)', 'hsl(350,72%,55%)'];

        const tooltipDefaults = {
            backgroundColor: '#fff',
            borderColor: 'hsl(220,20%,88%)',
            borderWidth: 1,
            titleColor: 'hsl(220,54%,15%)',
            bodyColor: 'hsl(220,15%,50%)',
            padding: 12,
        };

        // ── 1. BAR CHART — Pendaftar vs Kuota per Jurusan ─────────────────────────
        new Chart(document.getElementById('chartBarJurusan'), {
            type: 'bar',
            data: {
                labels: <?= json_encode($jLabels) ?>,
                datasets: [{
                        label: 'Kuota',
                        data: <?= json_encode($jKuota) ?>,
                        backgroundColor: INFO,
                        borderRadius: 4,
                    },
                    {
                        label: 'Pendaftar',
                        data: <?= json_encode($jPdft) ?>,
                        backgroundColor: PRIMARY,
                        borderRadius: 4,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'top'
                    },
                    tooltip: tooltipDefaults
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        border: {
                            display: false
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'hsl(220,20%,94%)'
                        },
                        border: {
                            display: false
                        }
                    },
                },
            },
        });

        // ── 2. DOUGHNUT — Distribusi Siswa Aktif per Jurusan ─────────────────────
        new Chart(document.getElementById('chartDonutJurusan'), {
            type: 'doughnut',
            data: {
                labels: <?= json_encode($jLabels) ?>,
                datasets: [{
                    data: <?= json_encode($jAktif) ?>,
                    backgroundColor: COLORS,
                    borderColor: '#fff',
                    borderWidth: 3,
                    hoverOffset: 8
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                cutout: '55%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 16,
                            usePointStyle: true,
                            pointStyleWidth: 8
                        }
                    },
                    tooltip: {
                        ...tooltipDefaults,
                        callbacks: {
                            label: ctx => ` ${ctx.label}: ${ctx.raw} siswa`
                        }
                    },
                },
            },
        });

        // ── 3. BAR CHART — Statistik per Gelombang ───────────────────────────────
        <?php if (!empty($byGelombang)): ?>
            new Chart(document.getElementById('chartBarGelombang'), {
                type: 'bar',
                data: {
                    labels: <?= json_encode($gLabels) ?>,
                    datasets: [{
                            label: 'Pendaftar',
                            data: <?= json_encode($gPdft) ?>,
                            backgroundColor: PRIMARY,
                            borderRadius: 4
                        },
                        {
                            label: 'Diterima',
                            data: <?= json_encode($gDiterima) ?>,
                            backgroundColor: SUCCESS,
                            borderRadius: 4
                        },
                        {
                            label: 'Ditolak',
                            data: <?= json_encode($gDitolak) ?>,
                            backgroundColor: DANGER,
                            borderRadius: 4
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            position: 'top'
                        },
                        tooltip: tooltipDefaults
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            },
                            border: {
                                display: false
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'hsl(220,20%,94%)'
                            },
                            border: {
                                display: false
                            }
                        },
                    },
                },
            });
        <?php endif; ?>

        // ── 4. DOUGHNUT — Jenis Kelamin ──────────────────────────────────────────
        <?php if (!empty($genderData)): ?>
            new Chart(document.getElementById('chartGender'), {
                type: 'doughnut',
                data: {
                    labels: <?= json_encode($gdrLabels) ?>,
                    datasets: [{
                        data: <?= json_encode($gdrValues) ?>,
                        backgroundColor: [PRIMARY, SUCCESS],
                        borderColor: '#fff',
                        borderWidth: 3,
                        hoverOffset: 8
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    cutout: '55%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 16,
                                usePointStyle: true
                            }
                        },
                        tooltip: {
                            ...tooltipDefaults,
                            callbacks: {
                                label: ctx => ` ${ctx.label}: ${ctx.raw} orang`
                            }
                        },
                    },
                },
            });
        <?php endif; ?>

        // ── 5. HORIZONTAL BAR — Top 5 Asal Sekolah ───────────────────────────────
        <?php if (!empty($asalSekolah)): ?>
            new Chart(document.getElementById('chartAsalSekolah'), {
                type: 'bar',
                data: {
                    labels: <?= json_encode($sklLabels) ?>,
                    datasets: [{
                        label: 'Jumlah',
                        data: <?= json_encode($sklValues) ?>,
                        backgroundColor: PRIMARY,
                        borderRadius: {
                            topRight: 4,
                            bottomRight: 4
                        },
                        barThickness: 28
                    }],
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: tooltipDefaults
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            grid: {
                                color: 'hsl(220,20%,94%)'
                            },
                            border: {
                                display: false
                            }
                        },
                        y: {
                            grid: {
                                display: false
                            },
                            border: {
                                display: false
                            }
                        },
                    },
                },
            });
        <?php endif; ?>

        // ── 6. BAR CHART — Tren Tahunan ──────────────────────────────────────────
        <?php if (!empty($trenTahunan)): ?>
            new Chart(document.getElementById('chartTren'), {
                type: 'bar',
                data: {
                    labels: <?= json_encode($trenLabels) ?>,
                    datasets: [{
                            label: 'Pendaftar',
                            data: <?= json_encode($trenPdft) ?>,
                            backgroundColor: 'hsl(220,54%,40%)',
                            borderRadius: 4
                        },
                        {
                            label: 'Diterima',
                            data: <?= json_encode($trenDtrm) ?>,
                            backgroundColor: 'hsl(142,71%,45%)',
                            borderRadius: 4
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            position: 'top'
                        },
                        tooltip: tooltipDefaults
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            },
                            border: {
                                display: false
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'hsl(220,20%,94%)'
                            },
                            border: {
                                display: false
                            }
                        },
                    },
                },
            });
        <?php endif; ?>

    })();
</script>