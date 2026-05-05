REPLACED
<!--
    File : app/Modules/Dashboard/Views/kepala_sekolah.php
    Route: kepala-sekolah/
    Sesuai mockup React: KepalaSekolahDashboardPage

    Struktur halaman:
      1. Page Header  (judul + filter tahun ajaran)
      2. KPI Cards    (4 kartu: Total Pendaftar, Diterima, Daftar Ulang Valid, Siswa Aktif)
      3. Charts row   (Line Chart gelombang | Pie Chart distribusi jurusan)
      4. Status Chart (Horizontal Bar Chart status verifikasi)
      5. Tabel Rekap  (per jurusan + total + tombol export)
-->

<?php
// ── Chart colors (sesuai CSS variable di app.php) ────────────────────────────
// Disamakan dengan palet hsl dari tailwind.config di app.php
$chartColors = [
    'hsl(220,54%,40%)',   // --chart-1  ~ primary
    'hsl(142,71%,45%)',   // --chart-2  ~ success/green
    'hsl(38,92%,50%)',    // --chart-3  ~ warning/gold
    'hsl(199,89%,48%)',   // --chart-4  ~ info/cyan
    'hsl(262,70%,58%)',   // --chart-5  ~ purple
];

$tahunAjaran = $periodeAktif->tahun_ajaran ?? date('Y') . '/' . (date('Y') + 1);

// Tabel rekap: gabungkan statsByJurusan dengan jurusans
// Buat lookup dari statsByJurusan berdasarkan jurusan_id
$statsLookup = [];
foreach ($statsByJurusan as $row) {
    $statsLookup[$row->jurusan_id ?? 0] = $row;
}

// Total row untuk tfoot
$totalKuota       = 0;
$totalPendaftar   = 0;
$totalDiterima    = 0;
$totalDaftarUlang = 0;
$totalAktif       = 0;
?>

<div class="space-y-6">

    <!-- ═══════════════════════════════════════════════════════════
         1. PAGE HEADER
    ═══════════════════════════════════════════════════════════ -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold font-serif">Dashboard Monitoring</h1>
            <p class="text-sm text-gray-500">
                Rekapitulasi SPMB Tahun Ajaran <?= esc($tahunAjaran) ?>
            </p>
        </div>

        <!-- Filter tahun ajaran (sesuai mockup: Select component) -->
        <form method="get" class="flex flex-wrap items-center gap-2">
            <div class="relative">
                <select name="tahun_ajaran" onchange="this.form.submit()"
                    class="appearance-none w-40 px-3 py-2 pr-8 border border-gray-300 rounded-xl text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer">
                    <option value="<?= esc($tahunAjaran) ?>" selected><?= esc($tahunAjaran) ?></option>
                    <?php
                    $tahunAwal = (int) explode('/', $tahunAjaran)[0] ?? (int) date('Y');
                    for ($y = $tahunAwal - 1; $y >= $tahunAwal - 3; $y--):
                        $ta = $y . '/' . ($y + 1);
                    ?>
                        <option value="<?= $ta ?>"><?= $ta ?></option>
                    <?php endfor; ?>
                </select>
                <!-- Chevron icon -->
                <svg class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400"
                    fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                </svg>
            </div>
        </form>
    </div>

    <!-- ═══════════════════════════════════════════════════════════
         2. KPI CARDS (4 kolom sesuai mockup)
    ═══════════════════════════════════════════════════════════ -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

        <!-- Card 1: Total Pendaftar -->
        <div class="bg-white rounded-2xl border p-4"
            style="border-color:hsl(220,54%,20%,.2);background:linear-gradient(135deg,hsl(220,54%,20%,.06),hsl(220,54%,20%,.02));">
            <div class="flex items-center justify-between">
                <div class="min-w-0 flex-1">
                    <p class="text-sm text-gray-500">Total Pendaftar</p>
                    <p class="text-3xl font-bold text-gray-900 mt-0.5"><?= number_format($totalPendaftar) ?></p>
                    <!-- Badge trend sesuai mockup -->
                    <span class="inline-flex items-center gap-1 mt-1.5 px-2 py-0.5 rounded-full text-xs font-semibold"
                        style="background:hsl(142,71%,45%,.12);color:hsl(142,60%,30%);">
                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941" />
                        </svg>
                        Tahun Ajaran <?= esc($tahunAjaran) ?>
                    </span>
                </div>
                <div class="h-12 w-12 rounded-full flex items-center justify-center flex-shrink-0 ml-3"
                    style="background:hsl(220,54%,20%,.12);">
                    <svg class="h-6 w-6" style="color:hsl(220,54%,30%);" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Card 2: Diterima -->
        <div class="bg-white rounded-2xl border p-4"
            style="border-color:hsl(142,71%,45%,.2);background:linear-gradient(135deg,hsl(142,71%,45%,.06),hsl(142,71%,45%,.02));">
            <div class="flex items-center justify-between">
                <div class="min-w-0 flex-1">
                    <p class="text-sm text-gray-500">Diterima</p>
                    <p class="text-3xl font-bold text-gray-900 mt-0.5"><?= number_format($totalDiterima) ?></p>
                    <p class="text-sm text-gray-400 mt-1"><?= esc($pctDiterima) ?> dari total</p>
                </div>
                <div class="h-12 w-12 rounded-full flex items-center justify-center flex-shrink-0 ml-3"
                    style="background:hsl(142,71%,45%,.12);">
                    <svg class="h-6 w-6" style="color:hsl(142,60%,36%);" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75l2.25 2.25 3.75-4.5" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Card 3: Daftar Ulang Valid -->
        <div class="bg-white rounded-2xl border p-4"
            style="border-color:hsl(199,89%,48%,.2);background:linear-gradient(135deg,hsl(199,89%,48%,.06),hsl(199,89%,48%,.02));">
            <div class="flex items-center justify-between">
                <div class="min-w-0 flex-1">
                    <p class="text-sm text-gray-500">Daftar Ulang Valid</p>
                    <p class="text-3xl font-bold text-gray-900 mt-0.5"><?= number_format($totalDaftarUlang) ?></p>
                    <p class="text-sm text-gray-400 mt-1"><?= esc($pctDaftarUlang) ?> dari diterima</p>
                </div>
                <div class="h-12 w-12 rounded-full flex items-center justify-center flex-shrink-0 ml-3"
                    style="background:hsl(199,89%,48%,.12);">
                    <svg class="h-6 w-6" style="color:hsl(199,70%,40%);" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Card 4: Siswa Aktif -->
        <div class="bg-white rounded-2xl border p-4"
            style="border-color:hsl(43,70%,47%,.2);background:linear-gradient(135deg,hsl(43,70%,47%,.06),hsl(43,70%,47%,.02));">
            <div class="flex items-center justify-between">
                <div class="min-w-0 flex-1">
                    <p class="text-sm text-gray-500">Siswa Aktif</p>
                    <p class="text-3xl font-bold text-gray-900 mt-0.5"><?= number_format($totalSiswaAktif) ?></p>
                    <p class="text-sm text-gray-400 mt-1">Sudah masuk Buku Induk</p>
                </div>
                <div class="h-12 w-12 rounded-full flex items-center justify-center flex-shrink-0 ml-3"
                    style="background:hsl(43,70%,47%,.14);">
                    <svg class="h-6 w-6" style="color:hsl(43,60%,35%);" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342" />
                    </svg>
                </div>
            </div>
        </div>

    </div><!-- /KPI cards -->

    <!-- Approval alert dihapus: Admin TU langsung menetapkan kelulusan tanpa persetujuan kepsek -->

    <!-- ═══════════════════════════════════════════════════════════
         3. CHARTS ROW 1: Line + Pie  (sesuai mockup)
    ═══════════════════════════════════════════════════════════ -->
    <div class="grid lg:grid-cols-2 gap-6">

        <!-- Line Chart — Jumlah Pendaftar per Gelombang -->
        <div class="bg-white rounded-2xl border border-gray-200">
            <div class="px-5 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900 flex items-center gap-2">
                    <svg class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                    </svg>
                    Jumlah Pendaftar per Gelombang
                </h3>
            </div>
            <div class="p-5">
                <div class="relative w-full" style="height:min(220px,55vw)"><canvas id="chartGelombang" height="220"></div></canvas>
            </div>
        </div>

        <!-- Pie / Doughnut Chart — Distribusi Pendaftar per Jurusan -->
        <div class="bg-white rounded-2xl border border-gray-200">
            <div class="px-5 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">Distribusi Pendaftar per Jurusan</h3>
            </div>
            <div class="p-5">
                <div class="relative w-full" style="height:min(220px,55vw)"><canvas id="chartJurusan" height="220"></div></canvas>
            </div>
        </div>

    </div><!-- /charts row 1 -->

    <!-- ═══════════════════════════════════════════════════════════
         4. STATUS VERIFIKASI — Horizontal Bar Chart
    ═══════════════════════════════════════════════════════════ -->
    <div class="bg-white rounded-2xl border border-gray-200">
        <div class="px-5 py-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-900">Status Verifikasi</h3>
        </div>
        <div class="p-5">
            <div class="relative w-full" style="height:min(120px,35vw)"><canvas id="chartStatus" height="120"></div></canvas>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════
         5. TABEL REKAPITULASI
    ═══════════════════════════════════════════════════════════ -->
    <div class="bg-white rounded-2xl border border-gray-200">

        <!-- Card header -->
        <div class="px-5 py-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <h3 class="font-semibold text-gray-900">Tabel Rekapitulasi</h3>

            <!-- Export buttons sesuai mockup -->
            <div class="flex flex-wrap gap-2">
                <a href="<?= base_url('kepala-sekolah/laporan/ekspor-pdf') ?>" target="_blank"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                    </svg>
                    <span class="hidden sm:inline">Export PDF</span>
                </a>
                <a href="<?= base_url('kepala-sekolah/laporan/ekspor-excel') ?>"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                    </svg>
                    <span class="hidden sm:inline">Export Excel</span>
                </a>
                <button onclick="window.print()"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.056 48.056 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659" />
                    </svg>
                    <span class="hidden sm:inline">Print</span>
                </button>
            </div>
        </div>

        <!-- Table (sesuai kolom mockup React: Jurusan, Kuota, Pendaftar, Diterima, Daftar Ulang, Siswa Aktif) -->
        <div class="overflow-x-auto">
            <table class="w-full min-w-[600px] text-sm">
                <thead>
                    <tr class="border-b bg-gray-50">
                        <th class="py-3 px-4 text-left font-medium text-gray-600">Jurusan</th>
                        <th class="py-3 px-4 text-center font-medium text-gray-600">Kuota</th>
                        <th class="py-3 px-4 text-center font-medium text-gray-600">Pendaftar</th>
                        <th class="py-3 px-4 text-center font-medium text-gray-600">Diterima</th>
                        <th class="py-3 px-4 text-center font-medium text-gray-600">Daftar Ulang</th>
                        <th class="py-3 px-4 text-center font-medium text-gray-600">Siswa Aktif</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <?php foreach ($statsByJurusan as $row):
                        $kuota      = (int) ($row->kuota ?? 0);
                        $pendaftar  = (int) ($row->total_daftar ?? 0);
                        $diterima   = (int) ($row->total_lulus ?? 0);
                        $daftarUlang = (int) ($row->total_daftar_ulang ?? 0);
                        $aktif      = (int) ($row->total_siswa_aktif ?? 0);

                        $totalKuota       += $kuota;
                        $totalPendaftar   += $pendaftar;
                        $totalDiterima    += $diterima;
                        $totalDaftarUlang += $daftarUlang;
                        $totalAktif       += $aktif;
                    ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="py-3 px-4 font-medium text-gray-900">
                                <?= esc($row->kode ?? '') ?> — <?= esc($row->jurusan ?? '') ?>
                            </td>
                            <td class="py-3 px-4 text-center text-gray-600"><?= $kuota ?></td>
                            <td class="py-3 px-4 text-center text-gray-600"><?= $pendaftar ?></td>
                            <td class="py-3 px-4 text-center text-gray-600"><?= $diterima ?></td>
                            <td class="py-3 px-4 text-center text-gray-600"><?= $daftarUlang ?></td>
                            <td class="py-3 px-4 text-center font-semibold" style="color:hsl(142,60%,30%);">
                                <?= $aktif ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (empty($statsByJurusan)): ?>
                        <tr>
                            <td colspan="6" class="py-12 text-center text-sm text-gray-400">
                                Belum ada data pendaftaran
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>

                <?php if (!empty($statsByJurusan)): ?>
                    <tfoot>
                        <tr class="border-t-2 border-gray-100 font-bold"
                            style="background:hsl(220,54%,20%,.04);">
                            <td class="py-3 px-4 text-gray-900">TOTAL</td>
                            <td class="py-3 px-4 text-center text-gray-900"><?= $totalKuota ?></td>
                            <td class="py-3 px-4 text-center text-gray-900"><?= $totalPendaftar ?></td>
                            <td class="py-3 px-4 text-center text-gray-900"><?= $totalDiterima ?></td>
                            <td class="py-3 px-4 text-center text-gray-900"><?= $totalDaftarUlang ?></td>
                            <td class="py-3 px-4 text-center font-bold" style="color:hsl(142,60%,30%);">
                                <?= $totalAktif ?>
                            </td>
                        </tr>
                    </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div><!-- /tabel rekap -->

</div><!-- /space-y-6 -->

<!-- ═══════════════════════════════════════════════════════════════
     CHART.JS SCRIPTS
     Chart.js sudah di-load via CDN di app.php (layout)
     Semua data di-inject via PHP → JS JSON
═══════════════════════════════════════════════════════════════ -->
<?php
// Siapkan data JSON untuk Chart.js
$gelombangLabels = array_column($gelombangData, 'name');
$gelombangValues = array_column($gelombangData, 'value');

$jurusanLabels   = array_column($distribusiJurusan, 'name');
$jurusanValues   = array_column($distribusiJurusan, 'value');

$statusLabels    = array_column($statusVerifikasi, 'name');
$statusValues    = array_column($statusVerifikasi, 'value');
?>

<script>
    (function() {
        'use strict';

        // ── Warna palet ────────────────────────────────────────────────────────────
        const PRIMARY = 'hsl(220,54%,30%)';
        const COLORS = [
            'hsl(220,54%,40%)',
            'hsl(142,71%,45%)',
            'hsl(38,92%,50%)',
            'hsl(199,89%,48%)',
            'hsl(262,70%,58%)',
            'hsl(350,72%,55%)',
        ];
        const STATUS_COLORS = [
            'hsl(142,71%,45%)', // Terverifikasi  → hijau
            'hsl(38,92%,50%)', // Menunggu       → kuning
            'hsl(0,72%,51%)', // Ditolak        → merah
        ];

        // ── Shared defaults ────────────────────────────────────────────────────────
        Chart.defaults.font.family = "'Plus Jakarta Sans', sans-serif";
        Chart.defaults.font.size = 12;
        Chart.defaults.color = 'hsl(220,15%,50%)';

        // ── 1. LINE CHART — Pendaftar per Gelombang ────────────────────────────────
        new Chart(document.getElementById('chartGelombang'), {
            type: 'line',
            data: {
                labels: <?= json_encode($gelombangLabels) ?>,
                datasets: [{
                    label: 'Pendaftar',
                    data: <?= json_encode($gelombangValues) ?>,
                    borderColor: PRIMARY,
                    backgroundColor: 'hsl(220,54%,30%,.08)',
                    borderWidth: 3,
                    pointBackgroundColor: PRIMARY,
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    fill: true,
                    tension: 0.35,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: '#fff',
                        borderColor: 'hsl(220,20%,88%)',
                        borderWidth: 1,
                        titleColor: 'hsl(220,54%,15%)',
                        bodyColor: 'hsl(220,15%,50%)',
                        padding: 12,
                        callbacks: {
                            label: ctx => ` ${ctx.raw} pendaftar`,
                        },
                    },
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        border: {
                            display: false
                        },
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'hsl(220,20%,94%)'
                        },
                        border: {
                            display: false
                        },
                        ticks: {
                            stepSize: 10
                        },
                    },
                },
            },
        });

        // ── 2. DOUGHNUT CHART — Distribusi per Jurusan ────────────────────────────
        new Chart(document.getElementById('chartJurusan'), {
            type: 'doughnut',
            data: {
                labels: <?= json_encode($jurusanLabels) ?>,
                datasets: [{
                    data: <?= json_encode($jurusanValues) ?>,
                    backgroundColor: COLORS,
                    borderColor: '#fff',
                    borderWidth: 3,
                    hoverOffset: 8,
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
                            pointStyleWidth: 8,
                        },
                    },
                    tooltip: {
                        backgroundColor: '#fff',
                        borderColor: 'hsl(220,20%,88%)',
                        borderWidth: 1,
                        titleColor: 'hsl(220,54%,15%)',
                        bodyColor: 'hsl(220,15%,50%)',
                        padding: 12,
                        callbacks: {
                            label: ctx => ` ${ctx.label}: ${ctx.raw} pendaftar`,
                        },
                    },
                },
            },
        });

        // ── 3. HORIZONTAL BAR CHART — Status Verifikasi ───────────────────────────
        new Chart(document.getElementById('chartStatus'), {
            type: 'bar',
            data: {
                labels: <?= json_encode($statusLabels) ?>,
                datasets: [{
                    data: <?= json_encode($statusValues) ?>,
                    backgroundColor: STATUS_COLORS,
                    borderRadius: 6,
                    barThickness: 28,
                }],
            },
            options: {
                indexAxis: 'y', // horizontal bar
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: '#fff',
                        borderColor: 'hsl(220,20%,88%)',
                        borderWidth: 1,
                        titleColor: 'hsl(220,54%,15%)',
                        bodyColor: 'hsl(220,15%,50%)',
                        padding: 12,
                        callbacks: {
                            label: ctx => ` ${ctx.raw} pendaftar`,
                        },
                    },
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        grid: {
                            color: 'hsl(220,20%,94%)'
                        },
                        border: {
                            display: false
                        },
                    },
                    y: {
                        grid: {
                            display: false
                        },
                        border: {
                            display: false
                        },
                    },
                },
            },
        });

    })();
</script>