<?php

/**
 * File: app/Views/Layouts/Partials/sidebar.php
 *
 * PERBAIKAN:
 *  - [admin_tu] Tambah menu "Verifikasi Daftar Ulang" (url: admin/daftar-ulang)
 *    sesuai mockup React AdminLayout.tsx adminTuNavItems:
 *    { to: '/admin/verifikasi-daftar-ulang', icon: CreditCard, label: 'Verifikasi Daftar Ulang' }
 *  - Tambah SVG path 'credit-card' untuk ikon menu baru
 *  - Urutkan menu sesuai mockup:
 *    Dashboard → Verifikasi Dokumen → Penetapan Kelulusan →
 *    Verifikasi Daftar Ulang → Data Master → Konversi Buku Induk → Buku Induk Siswa
 */

$role    = session()->get('user_role');
$name    = session()->get('user_name');
$userId  = session()->get('user_id');
$current = service('uri')->getPath();
$baseUrl = base_url();

// ── Hitung badge notifikasi untuk calon siswa ─────────────────────────────────
$notifBadge = 0;
if ($role === 'calon_siswa' && $userId) {
    try {
        $notifBadge = (new \App\Modules\Notifikasi\Models\NotifikasiModel())->countUnread((int) $userId);
    } catch (\Throwable $e) {
        $notifBadge = 0;
    }
}

// ── Hitung badge daftar ulang pending untuk admin ──────────────────────────────
$daftarUlangBadge = 0;
if ($role === 'admin_tu' && $userId) {
    try {
        $duModel = new \App\Modules\DaftarUlang\Models\DaftarUlangModel();
        $stats   = $duModel->getStatsByStatus();
        $daftarUlangBadge = (int) ($stats['pending'] ?? 0);
    } catch (\Throwable $e) {
        $daftarUlangBadge = 0;
    }
}

// ── Menu items per role ───────────────────────────────────────────────────────

$menuItems = [];

if ($role === 'calon_siswa') {
    $menuItems = [
        ['icon' => 'layout-dashboard', 'label' => 'Dashboard',             'url' => 'dashboard'],
        ['icon' => 'file-edit',        'label' => 'Formulir Pendaftaran',  'url' => 'dashboard/formulir'],
        ['icon' => 'bar-chart-3',      'label' => 'Status Pendaftaran',    'url' => 'dashboard/status'],
        ['icon' => 'party-popper',     'label' => 'Pengumuman Hasil',      'url' => 'dashboard/pengumuman'],
        ['icon' => 'file-check',       'label' => 'Daftar Ulang',          'url' => 'dashboard/daftar-ulang'],
        ['icon' => 'bell',             'label' => 'Notifikasi',            'url' => 'dashboard/notifikasi', 'badge' => $notifBadge],
    ];
} elseif ($role === 'admin_tu') {
    /**
     * Sesuai mockup React AdminLayout.tsx (adminTuNavItems):
     *   Dashboard
     *   Verifikasi Dokumen
     *   Penetapan Kelulusan
     *   Verifikasi Daftar Ulang  ← BARU ditambahkan (icon: CreditCard)
     *   Data Master
     *   Konversi Buku Induk
     *   Buku Induk Siswa
     */
    $menuItems = [
        ['icon' => 'layout-dashboard', 'label' => 'Dashboard',                 'url' => 'admin'],
        ['icon' => 'file-check',       'label' => 'Verifikasi Dokumen',        'url' => 'admin/verifikasi'],
        ['icon' => 'user-check',       'label' => 'Penetapan Kelulusan',       'url' => 'admin/seleksi'],
        ['icon' => 'credit-card',      'label' => 'Verifikasi Daftar Ulang',   'url' => 'admin/daftar-ulang', 'badge' => $daftarUlangBadge],
        ['icon' => 'database',         'label' => 'Data Master',               'url' => 'admin/master-data'],
        ['icon' => 'book-open',        'label' => 'Konversi Buku Induk',       'url' => 'admin/buku-induk/konversi'],
        ['icon' => 'users',            'label' => 'Buku Induk Siswa',          'url' => 'admin/buku-induk'],
    ];
} elseif ($role === 'kepala_sekolah') {
    $menuItems = [
        ['icon' => 'layout-dashboard', 'label' => 'Dashboard Monitoring',  'url' => 'kepala-sekolah'],
        ['icon' => 'bar-chart-3',      'label' => 'Laporan Rekapitulasi',  'url' => 'kepala-sekolah/laporan'],
        ['icon' => 'folder-archive',   'label' => 'Arsip Laporan',         'url' => 'kepala-sekolah/laporan/arsip'],
    ];
}

// ── Helper: active check ──────────────────────────────────────────────────────

function isActive(string $url, string $current): bool
{
    $uri = ltrim($url, '/');

    // Exact match untuk root routes
    $roots = ['admin', 'kepala-sekolah', 'dashboard'];
    if (in_array($uri, $roots, true)) {
        return $current === $uri;
    }

    // Untuk buku-induk: pastikan tidak konflik dengan konversi
    if ($uri === 'admin/buku-induk') {
        return $current === 'admin/buku-induk'
            || (strpos($current, 'admin/buku-induk/') === 0
                && strpos($current, 'admin/buku-induk/konversi') === false);
    }

    return $current === $uri || strpos($current, $uri . '/') === 0;
}

// ── Helper: SVG icons ─────────────────────────────────────────────────────────

function svgIcon(string $name, string $cls = 'w-5 h-5 flex-shrink-0'): string
{
    $paths = [
        // Existing icons
        'file-text'        => '<path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>',
        'clock'            => '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
        'megaphone'        => '<path d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>',
        'refresh-cw'       => '<polyline points="23 4 23 10 17 10"/><polyline points="1 20 1 14 7 14"/><path d="M3.51 9a9 9 0 0114.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0020.49 15"/>',
        'bell'             => '<path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9M13.73 21a2 2 0 01-3.46 0"/>',
        'book-open'        => '<path d="M2 3h6a4 4 0 014 4v14a3 3 0 00-3-3H2z"/><path d="M22 3h-6a4 4 0 00-4 4v14a3 3 0 013-3h7z"/>',
        'database'         => '<ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/>',
        'check-square'     => '<polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/>',
        'archive'          => '<polyline points="21 8 21 21 3 21 3 8"/><rect x="1" y="3" width="22" height="5"/><line x1="10" y1="12" x2="14" y2="12"/>',
        'log-out'          => '<path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>',
        'x'                => '<line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>',

        // Dashboard layout icons
        'layout-dashboard' => '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/>',
        'file-edit'        => '<path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>',
        'bar-chart-3'      => '<path d="M18 20V10"/><path d="M12 20V4"/><path d="M6 20v-6"/>',
        'party-popper'     => '<path d="M5.8 11.3L2 22l10.7-3.79"/><path d="M4 3h.01"/><path d="M22 8h.01"/><path d="M15 2h.01"/><path d="M22 20h.01"/><path d="M22 2l-2.24.75a2.9 2.9 0 00-1.96 3.12v0c.1.86-.57 1.63-1.45 1.63h-.38c-.86 0-1.6.6-1.76 1.44L14 10"/><path d="M22 13l-.82-.33c-.86-.34-1.82.2-1.98 1.11v0c-.11.7-.72 1.22-1.43 1.22H17"/><path d="M11 2l.33.82c.34.86-.2 1.82-1.11 1.98v0C9.52 4.9 9 5.52 9 6.23V7"/><path d="M11 13c1.93 1.93 2.83 4.17 2 5-.83.83-3.07-.07-5-2-1.93-1.93-2.83-4.17-2-5 .83-.83 3.07.07 5 2z"/>',
        'file-check'       => '<path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><polyline points="9 15 11 17 15 13"/>',

        // Admin icons
        'user-check'       => '<path d="M16 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="8.5" cy="7" r="4"/><polyline points="17 11 19 13 23 9"/>',
        'users'            => '<path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/>',
        'folder-archive'   => '<path d="M22 20V8a2 2 0 00-2-2h-7.93a2 2 0 01-1.66-.9l-.82-1.2A2 2 0 008.93 3H4a2 2 0 00-2 2v13a2 2 0 002 2z"/><path d="M16 19v-2"/><path d="M13 16h6"/><path d="M16 13v-2"/>',

        // ── BARU: CreditCard — sesuai mockup React adminTuNavItems icon ───────
        // Verifikasi Daftar Ulang (icon CreditCard dari lucide-react)
        'credit-card'      => '<rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/>',
    ];

    $d = $paths[$name] ?? '<circle cx="12" cy="12" r="10"/>';
    return '<svg class="' . $cls . '" fill="none" stroke="currentColor" stroke-width="2"
        stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">'
        . $d
        . '</svg>';
}

// ── Role badge config ─────────────────────────────────────────────────────────

$roleBadge = [
    'calon_siswa'    => ['label' => 'Calon Siswa',    'bg' => 'hsl(142,71%,40%)',  'fg' => 'white'],
    'admin_tu'       => ['label' => 'Admin TU',       'bg' => 'hsl(43,70%,47%)',   'fg' => 'hsl(220,54%,10%)'],
    'kepala_sekolah' => ['label' => 'Kepala Sekolah', 'bg' => 'hsl(262,83%,58%)',  'fg' => 'white'],
];
$badge = $roleBadge[$role] ?? ['label' => ucfirst($role ?? 'User'), 'bg' => 'hsl(220,30%,40%)', 'fg' => 'white'];
?>

<!-- ═══════════════════════════════════════════════════════════════
     DESKTOP SIDEBAR
═══════════════════════════════════════════════════════════════ -->
<aside class="hidden lg:fixed lg:inset-y-0 lg:z-40 lg:flex lg:w-64 lg:flex-col"
    style="font-family:'Plus Jakarta Sans',sans-serif;">
    <div class="flex flex-col flex-grow overflow-y-auto"
        style="background:hsl(220,54%,18%);">

        <!-- ── Logo ──────────────────────────────────────────────────── -->
        <div class="flex items-center gap-3 px-6 py-5"
            style="border-bottom:1px solid hsl(220,40%,25%);">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 font-bold text-lg"
                style="background:hsl(43,70%,47%);color:hsl(220,54%,12%);font-family:'Playfair Display',serif;">
                M
            </div>
            <div class="min-w-0">
                <p class="font-bold text-sm leading-tight truncate"
                    style="color:hsl(45,70%,95%);">SMK Al-Munawwir IIBS</p>
                <p class="text-xs" style="color:hsl(220,25%,60%);">Sistem SPMB Online</p>
            </div>
        </div>

        <!-- ── User info ──────────────────────────────────────────────── -->
        <div class="px-4 py-3" style="border-bottom:1px solid hsl(220,40%,25%);">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full flex items-center justify-center font-semibold text-sm flex-shrink-0"
                    style="background:hsl(220,54%,28%);color:hsl(43,70%,57%);">
                    <?= strtoupper(substr($name ?? 'U', 0, 1)) ?>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium truncate" style="color:hsl(45,70%,95%);"><?= esc($name ?? 'User') ?></p>
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold mt-0.5"
                        style="background:<?= $badge['bg'] ?>;color:<?= $badge['fg'] ?>;">
                        <?= $badge['label'] ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- ── Navigation ────────────────────────────────────────────── -->
        <nav class="flex-1 px-3 py-4 space-y-0.5">
            <?php foreach ($menuItems as $item):
                $active   = isActive($item['url'], $current);
                $hasBadge = !empty($item['badge']) && $item['badge'] > 0;
            ?>
                <a href="<?= $baseUrl . $item['url'] ?>"
                    class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150"
                    style="<?= $active
                                ? 'background:hsl(43,70%,47%,.14);color:hsl(43,70%,65%);'
                                : 'color:hsl(220,20%,62%);' ?>"
                    onmouseover="if(!this.dataset.active){ this.style.background='hsl(220,54%,26%)'; this.style.color='hsl(45,70%,95%)'; }"
                    onmouseout="if(!this.dataset.active){ this.style.background='transparent'; this.style.color='hsl(220,20%,62%)'; }"
                    <?= $active ? 'data-active="1"' : '' ?>>
                    <span style="color:<?= $active ? 'hsl(43,70%,57%)' : 'hsl(220,20%,50%)' ?>;">
                        <?= svgIcon($item['icon']) ?>
                    </span>
                    <span class="flex-1"><?= esc($item['label']) ?></span>
                    <?php if ($hasBadge): ?>
                        <span class="inline-flex items-center justify-center w-5 h-5 rounded-full text-xs font-bold flex-shrink-0"
                            style="background:hsl(0,72%,51%);color:white;">
                            <?= (int) $item['badge'] ?>
                        </span>
                    <?php elseif ($active): ?>
                        <span class="ml-auto w-1.5 h-1.5 rounded-full flex-shrink-0"
                            style="background:hsl(43,70%,57%);"></span>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <!-- ── Logout ─────────────────────────────────────────────────── -->
        <div class="px-3 py-4" style="border-top:1px solid hsl(220,40%,25%);">
            <form action="<?= base_url('auth/logout') ?>" method="post">
                <?= csrf_field() ?>
                <button type="submit"
                    class="flex w-full items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150"
                    style="color:hsl(220,20%,62%);"
                    onmouseover="this.style.background='hsl(0,60%,40%,.14)'; this.style.color='hsl(0,72%,70%)';"
                    onmouseout="this.style.background='transparent'; this.style.color='hsl(220,20%,62%)';">
                    <span style="color:hsl(220,20%,50%);"><?= svgIcon('log-out') ?></span>
                    Logout
                </button>
            </form>
        </div>

    </div>
</aside>

<!-- ═══════════════════════════════════════════════════════════════
     MOBILE SIDEBAR
═══════════════════════════════════════════════════════════════ -->
<aside class="lg:hidden fixed inset-y-0 left-0 z-40 w-64 flex flex-col transform transition-transform duration-300 ease-in-out"
    style="background:hsl(220,54%,18%);font-family:'Plus Jakarta Sans',sans-serif;"
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">

    <!-- Mobile header -->
    <div class="flex items-center justify-between px-6 py-5"
        style="border-bottom:1px solid hsl(220,40%,25%);">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-xl flex items-center justify-center font-bold"
                style="background:hsl(43,70%,47%);color:hsl(220,54%,12%);font-family:'Playfair Display',serif;">
                M
            </div>
            <span class="font-bold text-sm" style="color:hsl(45,70%,95%);">SMK Al-Munawwir</span>
        </div>
        <button @click="sidebarOpen = false"
            class="p-1.5 rounded-lg transition-colors"
            style="color:hsl(220,20%,60%);"
            onmouseover="this.style.background='hsl(220,54%,26%)'"
            onmouseout="this.style.background='transparent'">
            <?= svgIcon('x', 'w-5 h-5') ?>
        </button>
    </div>

    <!-- Mobile user info -->
    <div class="px-4 py-3" style="border-bottom:1px solid hsl(220,40%,25%);">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-full flex items-center justify-center font-semibold text-sm flex-shrink-0"
                style="background:hsl(220,54%,28%);color:hsl(43,70%,57%);">
                <?= strtoupper(substr($name ?? 'U', 0, 1)) ?>
            </div>
            <div class="min-w-0">
                <p class="text-sm font-medium truncate" style="color:hsl(45,70%,95%);"><?= esc($name ?? 'User') ?></p>
                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold"
                    style="background:<?= $badge['bg'] ?>;color:<?= $badge['fg'] ?>;">
                    <?= $badge['label'] ?>
                </span>
            </div>
        </div>
    </div>

    <!-- Mobile nav -->
    <nav class="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto">
        <?php foreach ($menuItems as $item):
            $active   = isActive($item['url'], $current);
            $hasBadge = !empty($item['badge']) && $item['badge'] > 0;
        ?>
            <a href="<?= $baseUrl . $item['url'] ?>"
                @click="sidebarOpen = false"
                class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all"
                style="<?= $active
                            ? 'background:hsl(43,70%,47%,.14);color:hsl(43,70%,65%);'
                            : 'color:hsl(220,20%,62%);' ?>">
                <span style="color:<?= $active ? 'hsl(43,70%,57%)' : 'hsl(220,20%,50%)' ?>;">
                    <?= svgIcon($item['icon']) ?>
                </span>
                <span class="flex-1"><?= esc($item['label']) ?></span>
                <?php if ($hasBadge): ?>
                    <span class="inline-flex items-center justify-center w-5 h-5 rounded-full text-xs font-bold flex-shrink-0"
                        style="background:hsl(0,72%,51%);color:white;">
                        <?= (int) $item['badge'] ?>
                    </span>
                <?php elseif ($active): ?>
                    <span class="ml-auto w-1.5 h-1.5 rounded-full flex-shrink-0"
                        style="background:hsl(43,70%,57%);"></span>
                <?php endif; ?>
            </a>
        <?php endforeach; ?>
    </nav>

    <!-- Mobile logout -->
    <div class="px-3 py-4" style="border-top:1px solid hsl(220,40%,25%);">
        <form action="<?= base_url('auth/logout') ?>" method="post">
            <?= csrf_field() ?>
            <button type="submit"
                class="flex w-full items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition"
                style="color:hsl(0,72%,65%);">
                <?= svgIcon('log-out') ?>
                Logout
            </button>
        </form>
    </div>
</aside>