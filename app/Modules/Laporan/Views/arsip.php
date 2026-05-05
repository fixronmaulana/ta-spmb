<!--
    File : app/Modules/Laporan/Views/arsip.php
    Route: kepala-sekolah/laporan/arsip
    Sesuai mockup React: ArsipLaporanPage

    Struktur:
      1. Page Header    — judul + deskripsi
      2. Summary Cards  — Total Laporan | Tahun Ajaran | Laporan Akhir | Total File
      3. Filter Bar     — Search + Filter Tahun Ajaran + Filter Tipe
      4. Tabel Arsip    — kolom responsif sesuai mockup
      5. Modal Detail   — Alpine.js dialog sesuai mockup React Dialog
-->

<?php
// ── Label dan warna badge per tipe ───────────────────────────────────────────
$tipeLabel = [
    'rekapitulasi' => 'Rekapitulasi',
    'gelombang'    => 'Per Gelombang',
    'jurusan'      => 'Per Jurusan',
    'akhir'        => 'Laporan Akhir',
];

// Badge style per tipe
$tipeBadge = [
    'rekapitulasi' => 'background:hsl(220,54%,20%,.10);color:hsl(220,54%,30%);',
    'gelombang'    => 'background:hsl(220,20%,92%);color:hsl(220,15%,40%);',
    'jurusan'      => 'background:transparent;color:hsl(220,15%,40%);border:1px solid hsl(220,20%,80%);',
    'akhir'        => 'background:hsl(142,71%,45%,.12);color:hsl(142,60%,28%);',
];

// Format badge
$formatBadge = [
    'pdf'   => 'background:hsl(0,72%,51%,.10);color:hsl(0,55%,40%);border:1px solid hsl(0,72%,51%,.2);',
    'excel' => 'background:hsl(142,71%,45%,.10);color:hsl(142,60%,28%);border:1px solid hsl(142,71%,45%,.2);',
];

// Tahun ajaran unik untuk dropdown filter
$tahunList = array_unique(array_column($arsipList, 'tahun_ajaran'));
rsort($tahunList);

// Juga ambil dari semua periodes supaya dropdown tidak kosong saat filtered
$allTA = [];
foreach ($periodes as $p) {
    $allTA[] = $p->tahun_ajaran;
}
$allTA = array_unique($allTA);
rsort($allTA);
?>

<!--
    Alpine.js x-data: mengelola modal detail.
    sidebarOpen sudah dikelola oleh mainLayout() di app.php.
-->
<div class="space-y-6"
    x-data="{
        modalOpen: false,
        selected: null,
        openModal(item) { this.selected = item; this.modalOpen = true; },
        closeModal() { this.modalOpen = false; this.selected = null; }
    }">

    <!-- ══════════════════════════════════════════════════════════
         1. PAGE HEADER
    ══════════════════════════════════════════════════════════ -->
    <div>
        <h1 class="text-2xl font-bold font-serif flex items-center gap-2">
            <!-- FolderArchive icon -->
            <svg class="h-6 w-6 text-gray-700" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0l-3-3m3 3l3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
            </svg>
            Arsip Laporan
        </h1>
        <p class="text-sm text-gray-500 mt-0.5">Riwayat laporan SPMB dari semua tahun ajaran</p>
    </div>

    <!-- ══════════════════════════════════════════════════════════
         2. SUMMARY CARDS (4 kartu sesuai mockup)
    ══════════════════════════════════════════════════════════ -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">

        <!-- Total Laporan -->
        <div class="bg-white rounded-2xl border border-gray-200 p-4 text-center">
            <svg class="h-6 w-6 mx-auto mb-1" style="color:hsl(220,54%,30%);"
                fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
            </svg>
            <p class="text-2xl font-bold text-gray-900"><?= $totalLaporan ?></p>
            <p class="text-xs text-gray-500 mt-0.5">Total Laporan</p>
        </div>

        <!-- Tahun Ajaran -->
        <div class="bg-white rounded-2xl border border-gray-200 p-4 text-center">
            <svg class="h-6 w-6 mx-auto mb-1" style="color:hsl(199,70%,40%);"
                fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
            </svg>
            <p class="text-2xl font-bold text-gray-900"><?= count($allTA) ?></p>
            <p class="text-xs text-gray-500 mt-0.5">Tahun Ajaran</p>
        </div>

        <!-- Laporan Akhir -->
        <div class="bg-white rounded-2xl border border-gray-200 p-4 text-center">
            <svg class="h-6 w-6 mx-auto mb-1" style="color:hsl(142,60%,36%);"
                fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
            </svg>
            <p class="text-2xl font-bold text-gray-900"><?= $totalAkhir ?></p>
            <p class="text-xs text-gray-500 mt-0.5">Laporan Akhir</p>
        </div>

        <!-- Total File (download icon) -->
        <div class="bg-white rounded-2xl border border-gray-200 p-4 text-center">
            <svg class="h-6 w-6 mx-auto mb-1" style="color:hsl(43,60%,35%);"
                fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
            </svg>
            <?php
            // Hitung total item sebelum filter
            $totalAllLaporan = count($arsipList);
            ?>
            <p class="text-2xl font-bold text-gray-900"><?= $totalAllLaporan ?></p>
            <p class="text-xs text-gray-500 mt-0.5">File Tersedia</p>
        </div>

    </div>

    <!-- ══════════════════════════════════════════════════════════
         3. FILTER BAR — Search + Tahun Ajaran + Tipe
    ══════════════════════════════════════════════════════════ -->
    <div class="bg-white rounded-2xl border border-gray-200 p-4">
        <form method="get" class="flex flex-col sm:flex-row gap-3">

            <!-- Search -->
            <div class="relative flex-1">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400"
                    fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                </svg>
                <input type="text" name="search" value="<?= esc($searchQ) ?>"
                    placeholder="Cari laporan..."
                    class="w-full pl-9 pr-4 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Filter Tahun Ajaran -->
            <div class="relative">
                <select name="tahun_ajaran" onchange="this.form.submit()"
                    class="appearance-none w-full sm:w-[150px] px-3 py-2 pr-8 border border-gray-300 rounded-xl text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="semua" <?= $filterTA === 'semua' ? 'selected' : '' ?>>Semua Tahun</option>
                    <?php foreach ($allTA as $ta): ?>
                        <option value="<?= esc($ta) ?>" <?= $filterTA === $ta ? 'selected' : '' ?>>
                            <?= esc($ta) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <svg class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400"
                    fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                </svg>
            </div>

            <!-- Filter Tipe -->
            <div class="relative">
                <select name="tipe" onchange="this.form.submit()"
                    class="appearance-none w-full sm:w-[160px] px-3 py-2 pr-8 border border-gray-300 rounded-xl text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="semua" <?= $filterTipe === 'semua'        ? 'selected' : '' ?>>Semua Tipe</option>
                    <option value="rekapitulasi" <?= $filterTipe === 'rekapitulasi' ? 'selected' : '' ?>>Rekapitulasi</option>
                    <option value="jurusan" <?= $filterTipe === 'jurusan'      ? 'selected' : '' ?>>Per Jurusan</option>
                    <option value="akhir" <?= $filterTipe === 'akhir'        ? 'selected' : '' ?>>Laporan Akhir</option>
                    <option value="gelombang" <?= $filterTipe === 'gelombang'    ? 'selected' : '' ?>>Per Gelombang</option>
                </select>
                <svg class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400"
                    fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                </svg>
            </div>

            <!-- Submit Search -->
            <button type="submit"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium text-white transition-colors"
                style="background:hsl(220,54%,20%);"
                onmouseover="this.style.background='hsl(220,54%,16%)'"
                onmouseout="this.style.background='hsl(220,54%,20%)'">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                </svg>
                Cari
            </button>

        </form>
    </div>

    <!-- ══════════════════════════════════════════════════════════
         4. TABEL ARSIP
    ══════════════════════════════════════════════════════════ -->
    <div class="bg-white rounded-2xl border border-gray-200">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[600px] text-sm">
                <thead>
                    <tr class="border-b bg-gray-50">
                        <th class="py-3 px-4 text-left font-medium text-gray-600">Judul Laporan</th>
                        <!-- hidden sm: -->
                        <th class="py-3 px-4 text-center font-medium text-gray-600 hidden sm:table-cell">Tahun Ajaran</th>
                        <!-- hidden md: -->
                        <th class="py-3 px-4 text-center font-medium text-gray-600 hidden md:table-cell">Tipe</th>
                        <th class="py-3 px-4 text-center font-medium text-gray-600 hidden md:table-cell">Format</th>
                        <!-- hidden lg: -->
                        <th class="py-3 px-4 text-center font-medium text-gray-600 hidden lg:table-cell">Tanggal</th>
                        <th class="py-3 px-4 text-center font-medium text-gray-600">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">

                    <?php if (empty($arsipList)): ?>
                        <tr>
                            <td colspan="6" class="py-12 text-center text-sm text-gray-400">
                                <svg class="mx-auto h-10 w-10 text-gray-200 mb-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0l-3-3m3 3l3-3" />
                                </svg>
                                Tidak ada laporan ditemukan.
                            </td>
                        </tr>
                    <?php else: ?>

                        <?php foreach ($arsipList as $lap):
                            // Encode data untuk Alpine modal
                            $lapJson = json_encode([
                                'judul'             => $lap['judul'],
                                'tahun_ajaran'      => $lap['tahun_ajaran'],
                                'tipe'              => $lap['tipe'],
                                'tipe_label'        => $tipeLabel[$lap['tipe']] ?? $lap['tipe'],
                                'format'            => strtoupper($lap['format']),
                                'tanggal'           => $lap['tanggal']
                                    ? date('d F Y', strtotime($lap['tanggal'])) : '-',
                                'total_pendaftar'   => $lap['total_pendaftar'],
                                'total_diterima'    => $lap['total_diterima'],
                                'total_siswa_aktif' => $lap['total_siswa_aktif'],
                                'url_download'      => $lap['url_download'],
                                'tipe_badge'        => $tipeBadge[$lap['tipe']] ?? '',
                            ]);
                        ?>
                            <tr class="hover:bg-gray-50 transition-colors">

                                <!-- Judul (selalu tampil) -->
                                <td class="py-3.5 px-4">
                                    <p class="font-medium text-gray-900 text-sm"><?= esc($lap['judul']) ?></p>
                                    <!-- Info tambahan di mobile (hidden sm) -->
                                    <div class="flex flex-wrap gap-2 mt-1 sm:hidden">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold"
                                            style="<?= $tipeBadge[$lap['tipe']] ?? '' ?>">
                                            <?= $tipeLabel[$lap['tipe']] ?? $lap['tipe'] ?>
                                        </span>
                                        <span class="text-xs text-gray-400"><?= esc($lap['tahun_ajaran']) ?></span>
                                    </div>
                                </td>

                                <!-- Tahun Ajaran (hidden sm) -->
                                <td class="py-3.5 px-4 text-center text-gray-600 hidden sm:table-cell">
                                    <?= esc($lap['tahun_ajaran']) ?>
                                </td>

                                <!-- Tipe Badge (hidden md) -->
                                <td class="py-3.5 px-4 text-center hidden md:table-cell">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold"
                                        style="<?= $tipeBadge[$lap['tipe']] ?? '' ?>">
                                        <?= $tipeLabel[$lap['tipe']] ?? $lap['tipe'] ?>
                                    </span>
                                </td>

                                <!-- Format Badge (hidden md) -->
                                <td class="py-3.5 px-4 text-center hidden md:table-cell">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded border text-xs font-semibold uppercase"
                                        style="<?= $formatBadge[$lap['format']] ?? '' ?>">
                                        <?= strtoupper($lap['format']) ?>
                                    </span>
                                </td>

                                <!-- Tanggal (hidden lg) -->
                                <td class="py-3.5 px-4 text-center text-gray-500 text-xs hidden lg:table-cell">
                                    <?= $lap['tanggal'] ? date('d M Y', strtotime($lap['tanggal'])) : '-' ?>
                                </td>

                                <!-- Aksi -->
                                <td class="py-3.5 px-4">
                                    <div class="flex justify-center items-center gap-1">
                                        <!-- Tombol lihat detail (Eye) → buka modal Alpine -->
                                        <button type="button"
                                            @click="openModal(<?= htmlspecialchars($lapJson, ENT_QUOTES) ?>)"
                                            title="Lihat Detail"
                                            class="p-2 rounded-lg transition-colors text-gray-500 hover:text-gray-900"
                                            onmouseover="this.style.background='hsl(220,20%,94%)'"
                                            onmouseout="this.style.background='transparent'">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                        </button>

                                        <!-- Tombol download -->
                                        <a href="<?= esc($lap['url_download']) ?>" target="_blank"
                                            title="Download"
                                            class="p-2 rounded-lg transition-colors text-gray-500 hover:text-gray-900"
                                            onmouseover="this.style.background='hsl(220,20%,94%)'"
                                            onmouseout="this.style.background='transparent'">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                            </svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>

                </tbody>
            </table>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════
         5. MODAL DETAIL (Alpine.js — sesuai mockup React Dialog)
    ══════════════════════════════════════════════════════════ -->

    <!-- Backdrop -->
    <div x-show="modalOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="closeModal()"
        class="fixed inset-0 z-50 flex items-end sm:items-center justify-center sm:p-4"
        style="background:rgba(0,0,0,.5);"
        x-cloak>
    </div>

    <!-- Panel -->
    <div x-show="modalOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        @keydown.escape.window="closeModal()"
        class="fixed inset-0 z-50 flex items-end sm:items-center justify-center sm:p-4 pointer-events-none"
        x-cloak>

        <div class="bg-white rounded-t-2xl sm:rounded-2xl shadow-xl w-full sm:max-w-lg pointer-events-auto max-h-[90vh] overflow-y-auto"
            style="border:1px solid hsl(220,20%,88%);"
            @click.stop>

            <!-- Modal Header -->
            <div class="flex items-center justify-between px-6 py-4"
                style="border-bottom:1px solid hsl(220,20%,92%);">
                <h3 class="text-lg font-semibold text-gray-900">Detail Laporan</h3>
                <button type="button" @click="closeModal()"
                    class="p-1.5 rounded-lg transition-colors text-gray-400 hover:text-gray-600"
                    onmouseover="this.style.background='hsl(220,20%,94%)'"
                    onmouseout="this.style.background='transparent'">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="px-6 py-5 space-y-4" x-show="selected">

                <!-- Judul -->
                <div>
                    <p class="text-xs text-gray-500 mb-0.5">Judul</p>
                    <p class="font-medium text-gray-900 text-sm" x-text="selected?.judul"></p>
                </div>

                <!-- Grid info 2 kolom -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs text-gray-500 mb-0.5">Tahun Ajaran</p>
                        <p class="font-medium text-gray-900 text-sm" x-text="selected?.tahun_ajaran"></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-0.5">Tipe</p>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold"
                            :style="selected?.tipe_badge" x-text="selected?.tipe_label"></span>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-0.5">Tanggal Generate</p>
                        <p class="font-medium text-gray-900 text-sm" x-text="selected?.tanggal"></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-0.5">Format</p>
                        <p class="font-medium text-gray-900 text-sm" x-text="selected?.format"></p>
                    </div>
                </div>

                <!-- Ringkasan data (sesuai mockup: 3 mini cards) -->
                <div style="border-top:1px solid hsl(220,20%,92%);padding-top:1rem;">
                    <p class="text-sm font-semibold text-gray-900 mb-3">Ringkasan Data</p>
                    <div class="grid grid-cols-3 gap-2 sm:gap-3">

                        <!-- Pendaftar -->
                        <div class="bg-white rounded-xl border border-gray-200 p-3 text-center">
                            <svg class="h-5 w-5 mx-auto mb-1" style="color:hsl(220,54%,30%);"
                                fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                            </svg>
                            <p class="text-lg font-bold text-gray-900" x-text="selected?.total_pendaftar"></p>
                            <p class="text-xs text-gray-500">Pendaftar</p>
                        </div>

                        <!-- Diterima -->
                        <div class="bg-white rounded-xl border border-gray-200 p-3 text-center">
                            <svg class="h-5 w-5 mx-auto mb-1" style="color:hsl(142,60%,36%);"
                                fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="text-lg font-bold text-gray-900" x-text="selected?.total_diterima"></p>
                            <p class="text-xs text-gray-500">Diterima</p>
                        </div>

                        <!-- Siswa Aktif -->
                        <div class="bg-white rounded-xl border border-gray-200 p-3 text-center">
                            <svg class="h-5 w-5 mx-auto mb-1" style="color:hsl(43,60%,35%);"
                                fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342" />
                            </svg>
                            <p class="text-lg font-bold text-gray-900" x-text="selected?.total_siswa_aktif"></p>
                            <p class="text-xs text-gray-500">Siswa Aktif</p>
                        </div>

                    </div>
                </div>

                <!-- Tombol Download -->
                <a :href="selected?.url_download" target="_blank"
                    class="flex w-full items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white transition-colors"
                    style="background:hsl(220,54%,20%);"
                    onmouseover="this.style.background='hsl(220,54%,16%)'"
                    onmouseout="this.style.background='hsl(220,54%,20%)'">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                    </svg>
                    Download Laporan
                </a>

            </div><!-- /modal body -->
        </div><!-- /panel -->
    </div><!-- /modal -->

</div><!-- /space-y-6 -->