<?php
// ── Shorthand & status map (mirror React getStatusDisplay) ─────
$p = $pendaftaran;
$currentStatus = $p->status ?? 'draft';

$statusMap = [
    'draft'        => ['label' => 'Belum Submit',          'badge' => 'status-draft',    'pct' => 20],
    'submitted'    => ['label' => 'Menunggu Verifikasi',   'badge' => 'status-pending',  'pct' => 40],
    'verifikasi'   => ['label' => 'Menunggu Verifikasi',   'badge' => 'status-pending',  'pct' => 60],
    'revisi'       => ['label' => 'Perlu Perbaikan',       'badge' => 'status-rejected', 'pct' => 40],
    'seleksi'      => ['label' => 'Menunggu Verifikasi',   'badge' => 'status-pending',  'pct' => 75],
    'lulus'        => ['label' => 'Diterima',              'badge' => 'status-verified', 'pct' => 100],
    'tidak_lulus'  => ['label' => 'Tidak Diterima',        'badge' => 'status-rejected', 'pct' => 100],
    'daftar_ulang' => ['label' => 'Terverifikasi',         'badge' => 'status-verified', 'pct' => 100],
    'siswa_aktif'  => ['label' => 'Terverifikasi',         'badge' => 'status-verified', 'pct' => 100],
];

$sm         = $statusMap[$currentStatus] ?? $statusMap['draft'];
$progress   = $sm['pct'];

// ── Progress steps (mirror React progressSteps array) ──────────
// Step completed logic mirrors: completed: progress >= threshold
$steps = [
    ['id' => 1, 'label' => 'Registrasi Akun', 'completed' => true,              'current' => false],
    ['id' => 2, 'label' => 'Isi Formulir',    'completed' => $progress >= 40,   'current' => $progress >= 20 && $progress < 40],
    ['id' => 3, 'label' => 'Upload Dokumen',  'completed' => $progress >= 60,   'current' => $progress >= 40 && $progress < 60],
    ['id' => 4, 'label' => 'Verifikasi Admin', 'completed' => $progress >= 80,   'current' => $progress >= 60 && $progress < 80],
    ['id' => 5, 'label' => 'Pengumuman',      'completed' => $progress >= 100,  'current' => $progress >= 80 && $progress < 100],
];

// Current step label (for progress bar text)
$currentStepLabel = 'Selesai';
foreach ($steps as $s) {
    if ($s['current']) {
        $currentStepLabel = $s['label'];
        break;
    }
}

// ── Quick action (mirror React getQuickAction) ─────────────────
if (!$p || $currentStatus === 'draft') {
    $qa = ['label' => 'Lanjutkan Pengisian Formulir', 'url' => base_url('dashboard/formulir'), 'variant' => 'primary', 'icon' => 'file-edit'];
} elseif (in_array($currentStatus, ['submitted', 'verifikasi', 'seleksi'])) {
    $qa = ['label' => 'Lihat Status Verifikasi',       'url' => base_url('dashboard/status'),   'variant' => 'secondary', 'icon' => 'file-check'];
} elseif (in_array($currentStatus, ['lulus', 'daftar_ulang', 'siswa_aktif'])) {
    $qa = ['label' => 'Cetak Bukti Pendaftaran',       'url' => base_url('dashboard/status'),   'variant' => 'success',   'icon' => 'printer'];
} else {
    $qa = ['label' => 'Lihat Status',                  'url' => base_url('dashboard/status'),   'variant' => 'primary',   'icon' => 'file-check'];
}

// Variant colors
$variantStyle = [
    'primary'   => 'background:hsl(220,54%,20%);color:white;',
    'secondary' => 'background:hsl(43,70%,47%);color:hsl(220,54%,10%);',
    'success'   => 'background:hsl(142,71%,40%);color:white;',
];
$qaStyle = $variantStyle[$qa['variant']] ?? $variantStyle['primary'];

// ── Validasi periode (FE): apakah tombol ini mengarah ke formulir
//    pengisian, dan apakah periode pendaftaran sedang dibuka? ────
$isFormulirCta = ($qa['url'] === base_url('dashboard/formulir'));
$periodeBuka   = ($periodeInfo['status'] ?? null) === 'open';


// ── Announcements (static, matches React array) ────────────────
$announcements = [
    ['text' => 'Batas waktu pengumpulan berkas gelombang 1: 31 Maret 2026', 'date' => '25 Jan 2026'],
    ['text' => 'Pastikan foto yang diupload jelas dan sesuai ketentuan',     'date' => '20 Jan 2026'],
    ['text' => 'Jika ada kesulitan, hubungi panitia SPMB melalui WhatsApp', 'date' => '15 Jan 2026'],
];
// Override with live notifs if available
if (!empty($notifikasis)) {
    $announcements = [];
    foreach (array_slice($notifikasis, 0, 3) as $n) {
        $announcements[] = [
            'text' => $n->title ?? $n->message,
            'date' => date('d M Y', strtotime($n->created_at)),
        ];
    }
}

// ── SVG icon helper (inline, no CDN) ──────────────────────────
function dashIcon(string $name, string $cls = 'w-6 h-6'): string
{
    $paths = [
        'hash'       => '<line x1="4" y1="9" x2="20" y2="9"/><line x1="4" y1="15" x2="20" y2="15"/><line x1="10" y1="3" x2="8" y2="21"/><line x1="16" y1="3" x2="14" y2="21"/>',
        'clock'      => '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
        'graduation' => '<path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/>',
        'megaphone'  => '<path d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>',
        'file-edit'  => '<path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>',
        'file-check' => '<path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><path d="M9 15l2 2 4-4"/>',
        'printer'    => '<polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8"/>',
        'arrow-right' => '<line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>',
        'check'      => '<polyline points="20 6 9 17 4 12"/>',
        'plus'       => '<line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>',
        'file-plus'  => '<path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/>',
    ];
    $d = $paths[$name] ?? '<circle cx="12" cy="12" r="10"/>';
    return '<svg class="' . $cls . '" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">' . $d . '</svg>';
}
?>

<div class="space-y-6">

    <!-- ══════════════════════════════════════
         1. WELCOME HEADER
         Mirrors: <h1>Selamat datang…</h1>
         ══════════════════════════════════════ -->
    <div>
        <h1 class="text-2xl lg:text-3xl font-bold font-serif">
            Selamat datang, <?= esc(explode(' ', session()->get('user_name') ?? 'Anda')[0]) ?>! 👋
        </h1>
        <p class="mt-1 text-sm" style="color:hsl(220,15%,45%);">
            Pantau progres pendaftaran SPMB Anda di sini
        </p>
    </div>

    <!-- ══════════════════════════════════════
         2. PROGRESS SECTION
         Mirrors: card-elevated "Progress Pendaftaran SPMB"
         ══════════════════════════════════════ -->
    <div class="card-elevated p-6">
        <h2 class="font-semibold mb-4" style="color:hsl(220,54%,15%);">Progress Pendaftaran SPMB</h2>

        <!-- Step indicators (mirrors progressSteps.map) -->
        <div class="flex items-center justify-between mb-4">
            <?php foreach ($steps as $idx => $step): ?>
                <div class="flex items-center flex-1">
                    <div class="flex flex-col items-center flex-1">

                        <!-- Circle -->
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold
                                <?= $step['completed'] ? 'step-completed' : ($step['current'] ? 'step-active' : 'step-pending') ?>">
                            <?php if ($step['completed']): ?>
                                <?= dashIcon('check', 'w-4 h-4') ?>
                            <?php else: ?>
                                <?= $step['id'] ?>
                            <?php endif; ?>
                        </div>

                        <!-- Label (hidden on mobile, mirrors hidden sm:block) -->
                        <p class="text-xs mt-2 text-center hidden sm:block"
                            style="color:<?= $step['completed'] ? 'hsl(142,60%,35%)' : ($step['current'] ? 'hsl(220,54%,20%)' : 'hsl(220,15%,55%)') ?>;
                              <?= $step['current'] ? 'font-weight:600;' : '' ?>">
                            <?= esc($step['label']) ?>
                        </p>
                    </div>

                    <!-- Connector (mirrors h-1 flex-1 mx-1 bg-success / bg-muted) -->
                    <?php if ($idx < count($steps) - 1): ?>
                        <div class="h-1 flex-1 mx-1 rounded"
                            style="background:<?= $step['completed'] ? 'hsl(142,71%,45%)' : 'hsl(220,20%,90%)' ?>;"></div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Progress bar (mirrors progress-track / progress-fill) -->
        <div class="progress-track">
            <div class="progress-fill" style="width:<?= $progress ?>%;"></div>
        </div>
        <p class="text-sm mt-2" style="color:hsl(220,15%,50%);">
            Progress: <strong><?= $progress ?>%</strong>
            — <?= esc($currentStepLabel) ?>
        </p>
    </div>

    <!-- ══════════════════════════════════════
         3. STATUS CARDS (3 kolom)
         Mirrors: grid md:grid-cols-3 gap-4
         ══════════════════════════════════════ -->
    <div class="grid md:grid-cols-3 gap-4">

        <!-- Nomor Pendaftaran (mirrors Hash icon + secondary/10) -->
        <div class="card-elevated p-5">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0"
                    style="background:hsl(43,70%,47%,.10);">
                    <span style="color:hsl(43,70%,47%);">
                        <?= dashIcon('hash') ?>
                    </span>
                </div>
                <div class="min-w-0">
                    <p class="text-sm" style="color:hsl(220,15%,50%);">Nomor Pendaftaran</p>
                    <p class="font-bold text-lg truncate">
                        <?= $p ? esc($p->no_pendaftaran ?? '-') : '-' ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Status Verifikasi (mirrors Clock icon + info/10) -->
        <div class="card-elevated p-5">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0"
                    style="background:hsl(199,89%,48%,.10);">
                    <span style="color:hsl(199,89%,48%);">
                        <?= dashIcon('clock') ?>
                    </span>
                </div>
                <div class="min-w-0">
                    <p class="text-sm" style="color:hsl(220,15%,50%);">Status Verifikasi</p>
                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold mt-1 <?= $sm['badge'] ?>">
                        <?= esc($sm['label']) ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Jurusan Pilihan (mirrors GraduationCap icon + accent/10) -->
        <div class="card-elevated p-5">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0"
                    style="background:hsl(160,60%,40%,.10);">
                    <span style="color:hsl(160,60%,40%);">
                        <?= dashIcon('graduation') ?>
                    </span>
                </div>
                <div class="min-w-0">
                    <p class="text-sm" style="color:hsl(220,15%,50%);">Jurusan Pilihan</p>
                    <p class="font-bold text-lg truncate">
                        <?= $p ? esc($p->jurusan_pilihan_1 ?? '-') : '-' ?>
                    </p>
                </div>
            </div>
        </div>

    </div>

    <!-- ══════════════════════════════════════
         4. PENGUMUMAN + AKSI CEPAT
         Mirrors: grid lg:grid-cols-3 gap-6
         ══════════════════════════════════════ -->
    <div class="grid lg:grid-cols-3 gap-6">

        <!-- Pengumuman Terbaru (lg:col-span-2) -->
        <div class="lg:col-span-2 card-elevated p-6">
            <div class="flex items-center gap-2 mb-4">
                <span style="color:hsl(43,70%,47%);"><?= dashIcon('megaphone', 'w-5 h-5') ?></span>
                <h2 class="font-semibold" style="color:hsl(220,54%,15%);">Pengumuman Terbaru</h2>
            </div>

            <div class="space-y-3">
                <?php foreach ($announcements as $item): ?>
                    <div class="flex items-start gap-3 p-3 rounded-lg transition-colors"
                        style="background:hsl(220,20%,96%);"
                        onmouseover="this.style.background='hsl(220,20%,92%)'"
                        onmouseout="this.style.background='hsl(220,20%,96%)'">
                        <!-- Dot (mirrors w-2 h-2 rounded-full bg-secondary) -->
                        <div class="w-2 h-2 rounded-full mt-1.5 flex-shrink-0"
                            style="background:hsl(43,70%,47%);"></div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm" style="color:hsl(220,54%,15%);"><?= esc($item['text']) ?></p>
                            <p class="text-xs mt-1" style="color:hsl(220,15%,55%);"><?= esc($item['date']) ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Aksi Cepat (1/3, mirrors flex flex-col justify-center) -->
        <div class="card-elevated p-6 flex flex-col justify-center">
            <h2 class="font-semibold mb-4" style="color:hsl(220,54%,15%);">Aksi Cepat</h2>

            <!-- Button (mirrors <Button size="lg" variant={...}>) -->
            <?php if ($isFormulirCta && ! $periodeBuka): ?>
                <div class="flex items-center gap-2 w-full py-3 px-4 rounded-xl font-semibold text-sm"
                    style="background:hsl(220,20%,93%);color:hsl(220,15%,50%);cursor:not-allowed;"
                    title="<?= esc($periodeInfo['message'] ?? 'Pendaftaran belum/tidak dibuka') ?>">
                    <span class="flex-shrink-0"><?= dashIcon('file-edit', 'w-5 h-5') ?></span>
                    <span class="flex-1">Pendaftaran Belum/Tidak Dibuka</span>
                </div>
            <?php else: ?>
                <a href="<?= $qa['url'] ?>"
                    class="flex items-center gap-2 w-full py-3 px-4 rounded-xl font-semibold text-sm transition-all"
                    style="<?= $qaStyle ?>"
                    onmouseover="this.style.filter='brightness(1.1)'"
                    onmouseout="this.style.filter='brightness(1)'">
                    <span class="flex-shrink-0"><?= dashIcon($qa['icon'], 'w-5 h-5') ?></span>
                    <span class="flex-1"><?= esc($qa['label']) ?></span>
                    <!-- ArrowRight ml-auto -->
                    <span class="ml-auto flex-shrink-0"><?= dashIcon('arrow-right', 'w-4 h-4') ?></span>
                </a>
            <?php endif; ?>

            <!-- Period info pill (bonus: tidak ada di React mockup tapi berguna) -->
            <?php if (!empty($periodeInfo)): ?>
                <div class="mt-4 p-3 rounded-xl text-xs"
                    style="background:<?= $periodeInfo['status'] === 'open' ? 'hsl(142,71%,45%,.08)' : 'hsl(220,20%,96%)' ?>;
                        border:1px solid <?= $periodeInfo['status'] === 'open' ? 'hsl(142,71%,45%,.25)' : 'hsl(220,20%,88%)' ?>;
                        color:<?= $periodeInfo['status'] === 'open' ? 'hsl(142,60%,28%)' : 'hsl(220,15%,50%)' ?>;">
                    <?= esc($periodeInfo['message']) ?>
                    <?php if (!empty($periodeInfo['sisa'])): ?>
                        — <strong><?= $periodeInfo['sisa'] ?> hari lagi</strong>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

    </div>

    <!-- ══════════════════════════════════════
         5. CTA JIKA BELUM ADA PENDAFTARAN
         (Extra state: bukan di React karena mock
          selalu ada user, tapi perlu di PHP)
         ══════════════════════════════════════ -->
    <?php if (!$p): ?>
        <div class="card-elevated p-8 text-center">
            <div class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4"
                style="background:hsl(220,54%,20%,.08);">
                <span style="color:hsl(220,54%,20%);"><?= dashIcon('file-plus', 'w-8 h-8') ?></span>
            </div>
            <h3 class="text-base font-bold font-serif mb-2">Belum Ada Formulir Pendaftaran</h3>
            <p class="text-sm mb-5" style="color:hsl(220,15%,50%);">
                Mulai isi formulir pendaftaran untuk mendaftar ke SMK Al-Munawwir IIBS
            </p>
            <?php if ($periodeBuka): ?>
                <a href="<?= base_url('dashboard/formulir') ?>"
                    class="inline-flex items-center gap-2 px-6 py-3 text-sm font-semibold text-white rounded-xl transition-all"
                    style="background:hsl(220,54%,20%);"
                    onmouseover="this.style.background='hsl(220,54%,30%)'"
                    onmouseout="this.style.background='hsl(220,54%,20%)'">
                    <?= dashIcon('plus', 'w-4 h-4') ?>
                    Mulai Isi Formulir
                </a>
            <?php else: ?>
                <div class="inline-flex items-center gap-2 px-6 py-3 text-sm font-semibold rounded-xl"
                    style="background:hsl(220,20%,93%);color:hsl(220,15%,50%);cursor:not-allowed;">
                    <?= dashIcon('plus', 'w-4 h-4') ?>
                    Pendaftaran Belum/Tidak Dibuka
                </div>
                <p class="text-xs mt-3" style="color:hsl(220,15%,55%);">
                    <?= esc($periodeInfo['message'] ?? 'Tidak ada periode pendaftaran yang aktif saat ini.') ?>
                </p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

</div>