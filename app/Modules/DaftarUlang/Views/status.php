<?php

/**
 * View: App\Modules\DaftarUlang\Views\status.php
 *
 * PERBAIKAN sesuai mockup React DaftarUlangPage.tsx:
 *  1. Timeline 4-step: Diterima → Upload Bukti → Verifikasi → Siswa Aktif
 *     (mockup: Clock card di bagian bawah halaman DaftarUlangPage)
 *  2. Tampilkan status card lebih informatif
 *  3. FIX: config status pakai key 'pending' bukan 'menunggu'
 */

$du = $daftarUlang;

// FIX: key 'menunggu' → 'pending'
$statusCfg = [
    'pending' => [
        'border'    => 'hsl(38,92%,50%,.35)',
        'iconBg'    => 'hsl(38,92%,50%,.15)',
        'iconColor' => 'hsl(38,60%,38%)',
        'badgeBg'   => 'hsl(38,92%,50%,.15)',
        'badgeText' => 'hsl(38,60%,32%)',
        'label'     => 'Menunggu Konfirmasi',
        'icon'      => 'clock',
    ],
    'dikonfirmasi' => [
        'border'    => 'hsl(142,71%,45%,.35)',
        'iconBg'    => 'hsl(142,71%,45%,.15)',
        'iconColor' => 'hsl(142,60%,35%)',
        'badgeBg'   => 'hsl(142,71%,45%,.15)',
        'badgeText' => 'hsl(142,55%,28%)',
        'label'     => 'Dikonfirmasi — Anda Resmi Siswa!',
        'icon'      => 'check',
    ],
    'ditolak' => [
        'border'    => 'hsl(0,72%,51%,.35)',
        'iconBg'    => 'hsl(0,72%,51%,.12)',
        'iconColor' => 'hsl(0,55%,45%)',
        'badgeBg'   => 'hsl(0,72%,51%,.1)',
        'badgeText' => 'hsl(0,55%,40%)',
        'label'     => 'Ditolak — Perlu Upload Ulang',
        'icon'      => 'x',
    ],
];

$cfg = $du ? ($statusCfg[$du->status] ?? $statusCfg['pending']) : null;
?>

<div class="space-y-6 max-w-2xl mx-auto">

    <!-- ══ BACK BUTTON ════════════════════════════════════════════════════ -->
    <a href="<?= base_url('dashboard') ?>"
        class="inline-flex items-center gap-2 text-sm transition"
        style="color:hsl(220,15%,50%);"
        onmouseover="this.style.color='hsl(220,54%,20%)'"
        onmouseout="this.style.color='hsl(220,15%,50%)'">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <polyline points="19 12 5 12" />
            <polyline points="12 19 5 12 12 5" />
        </svg>
        Kembali ke Dashboard
    </a>

    <!-- ══ PAGE HEADER ════════════════════════════════════════════════════ -->
    <div>
        <h1 class="text-2xl font-bold font-serif" style="color:hsl(220,54%,15%);">Status Daftar Ulang</h1>
        <p class="text-sm mt-1" style="color:hsl(220,15%,50%);">Pantau status pengajuan daftar ulang Anda</p>
    </div>

    <?php if (! $du): ?>
        <!-- ══ BELUM ADA PENGAJUAN ════════════════════════════════════════════ -->
        <div class="bg-white rounded-2xl p-12 text-center"
            style="border:1px solid hsl(220,20%,88%);box-shadow:0 4px 6px -1px hsl(220 54% 20%/0.07);">
            <div class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4"
                style="background:hsl(220,20%,95%);">
                <svg class="w-8 h-8" style="color:hsl(220,20%,72%);" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" />
                    <polyline points="14 2 14 8 20 8" />
                </svg>
            </div>
            <p class="font-semibold mb-1" style="color:hsl(220,54%,15%);">Belum Ada Pengajuan</p>
            <p class="text-sm mb-6" style="color:hsl(220,15%,55%);">Anda belum mengajukan daftar ulang. Segera lengkapi proses penerimaan Anda.</p>
            <a href="<?= base_url('dashboard/daftar-ulang') ?>"
                class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-semibold text-white rounded-xl transition"
                style="background:hsl(220,54%,20%);"
                onmouseover="this.style.background='hsl(220,54%,28%)'"
                onmouseout="this.style.background='hsl(220,54%,20%)'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <line x1="12" y1="5" x2="12" y2="19" />
                    <line x1="5" y1="12" x2="19" y2="12" />
                </svg>
                Ajukan Daftar Ulang
            </a>
        </div>

    <?php else: ?>

        <!-- ══ STATUS CARD ════════════════════════════════════════════════════ -->
        <div class="bg-white rounded-2xl p-6"
            style="border:2px solid <?= $cfg['border'] ?>;box-shadow:0 4px 6px -1px hsl(220 54% 20%/0.07);">

            <div class="flex items-start gap-4 mb-6">
                <div class="w-14 h-14 rounded-full flex items-center justify-center flex-shrink-0"
                    style="background:<?= $cfg['iconBg'] ?>;">
                    <?php if ($du->status === 'pending'): ?>
                        <svg class="w-7 h-7" style="color:<?= $cfg['iconColor'] ?>;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10" />
                            <polyline points="12 6 12 12 16 14" />
                        </svg>
                    <?php elseif ($du->status === 'dikonfirmasi'): ?>
                        <svg class="w-7 h-7" style="color:<?= $cfg['iconColor'] ?>;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M22 11.08V12a10 10 0 11-5.93-9.14" />
                            <polyline points="22 4 12 14.01 9 11.01" />
                        </svg>
                    <?php else: ?>
                        <svg class="w-7 h-7" style="color:<?= $cfg['iconColor'] ?>;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10" />
                            <line x1="15" y1="9" x2="9" y2="15" />
                            <line x1="9" y1="9" x2="15" y2="15" />
                        </svg>
                    <?php endif; ?>
                </div>
                <div class="flex-1">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold"
                        style="background:<?= $cfg['badgeBg'] ?>;color:<?= $cfg['badgeText'] ?>;">
                        <?= $cfg['label'] ?>
                    </span>
                    <p class="text-xs mt-2" style="color:hsl(220,15%,55%);">
                        Diajukan: <?= date('d F Y, H:i', strtotime($du->created_at)) ?> WIB
                    </p>
                    <?php if ($du->dikonfirmasi_pada): ?>
                        <p class="text-xs mt-0.5" style="color:hsl(220,15%,55%);">
                            Dikonfirmasi: <?= date('d F Y, H:i', strtotime($du->dikonfirmasi_pada)) ?> WIB
                        </p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- NIS dan Kelas (jika sudah dikonfirmasi) -->
            <?php if ($du->status === 'dikonfirmasi' && ($du->nis ?? null)): ?>
                <div class="mb-4 p-4 rounded-xl" style="background:hsl(142,71%,45%,.06);border:1px solid hsl(142,71%,45%,.3);">
                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div>
                            <p class="text-xs" style="color:hsl(142,55%,40%);">NIS Anda</p>
                            <p class="font-bold font-mono mt-0.5" style="color:hsl(142,55%,28%);"><?= esc($du->nis) ?></p>
                        </div>
                        <?php if ($du->nama_kelas ?? null): ?>
                            <div>
                                <p class="text-xs" style="color:hsl(142,55%,40%);">Kelas</p>
                                <p class="font-bold mt-0.5" style="color:hsl(142,55%,28%);"><?= esc($du->nama_kelas) ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Detail pembayaran -->
            <div class="rounded-xl p-4 space-y-3 mb-4" style="background:hsl(220,20%,97%);border:1px solid hsl(220,20%,92%);">
                <div class="flex flex-wrap justify-between items-center gap-1">
                    <span class="text-xs" style="color:hsl(220,15%,55%);">Nominal Pembayaran</span>
                    <span class="text-sm font-bold" style="color:hsl(220,54%,15%);">
                        Rp <?= number_format((int)$du->nominal_pembayaran, 0, ',', '.') ?>
                    </span>
                </div>
                <?php if ($du->catatan_siswa): ?>
                    <div class="flex flex-wrap justify-between items-start gap-2 pt-2 border-t" style="border-color:hsl(220,20%,90%);">
                        <span class="text-xs flex-shrink-0" style="color:hsl(220,15%,55%);">Catatan Siswa</span>
                        <span class="text-xs text-right" style="color:hsl(220,54%,15%);"><?= esc($du->catatan_siswa) ?></span>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Catatan admin -->
            <?php if ($du->catatan_admin): ?>
                <div class="flex items-start gap-3 p-4 rounded-xl mb-4"
                    style="background:hsl(38,92%,50%,.08);border:1px solid hsl(38,92%,50%,.25);">
                    <svg class="w-4 h-4 flex-shrink-0 mt-0.5" style="color:hsl(38,60%,38%);" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z" />
                    </svg>
                    <div>
                        <p class="text-xs font-semibold mb-0.5" style="color:hsl(38,60%,32%);">Catatan dari Admin</p>
                        <p class="text-sm" style="color:hsl(38,50%,30%);"><?= esc($du->catatan_admin) ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Bukti pembayaran -->
            <?php if ($du->bukti_pembayaran_path): ?>
                <div class="flex flex-wrap items-center justify-between gap-2 p-3 rounded-xl" style="background:hsl(220,20%,97%);border:1px solid hsl(220,20%,92%);">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg flex items-center justify-center" style="background:hsl(199,89%,48%,.12);">
                            <svg class="w-4 h-4" style="color:hsl(199,89%,48%);" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" />
                                <polyline points="14 2 14 8 20 8" />
                            </svg>
                        </div>
                        <span class="text-sm" style="color:hsl(220,54%,15%);">
                            <?= esc($du->nama_file_bukti ?? 'Bukti Pembayaran') ?>
                        </span>
                    </div>
                    <a href="<?= base_url($du->bukti_pembayaran_path) ?>" target="_blank"
                        class="inline-flex items-center gap-1 text-xs font-medium transition px-3 py-1.5 rounded-lg"
                        style="background:hsl(199,89%,48%,.1);color:hsl(199,60%,35%);"
                        onmouseover="this.style.background='hsl(199,89%,48%,.2)'"
                        onmouseout="this.style.background='hsl(199,89%,48%,.1)'">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6" />
                            <polyline points="15 3 21 3 21 9" />
                            <line x1="10" y1="14" x2="21" y2="3" />
                        </svg>
                        Lihat File
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- ══ BANNER DIKONFIRMASI ════════════════════════════════════════════ -->
        <?php if ($du->status === 'dikonfirmasi'): ?>
            <div class="text-center p-8 rounded-2xl"
                style="background:hsl(142,71%,45%,.06);border:1px solid hsl(142,71%,45%,.3);">
                <div class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4"
                    style="background:hsl(142,71%,45%,.15);">
                    <svg class="w-8 h-8" style="color:hsl(142,60%,35%);" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M22 10v6M2 10l10-5 10 5-10 5z" />
                        <path d="M6 12v5c3 3 9 3 12 0v-5" />
                    </svg>
                </div>
                <h3 class="text-lg font-bold font-serif mb-1" style="color:hsl(142,55%,28%);">
                    Selamat Bergabung! 🎉
                </h3>
                <p class="text-sm" style="color:hsl(142,60%,35%);">
                    Anda resmi menjadi siswa <strong>SMK Al-Munawwir IIBS</strong>.
                </p>
                <p class="text-xs mt-2" style="color:hsl(142,55%,42%);">
                    Informasi orientasi dan jadwal masuk akan disampaikan oleh pihak sekolah.
                </p>
            </div>
        <?php endif; ?>

        <!-- ══ AJUKAN ULANG (jika ditolak) ═══════════════════════════════════ -->
        <?php if ($du->status === 'ditolak'): ?>
            <div class="bg-white rounded-2xl p-6 text-center"
                style="border:1px solid hsl(220,20%,88%);">
                <p class="text-sm mb-4" style="color:hsl(220,15%,50%);">
                    Pengajuan Anda ditolak. Silakan perbaiki data dan kirim ulang.
                </p>
                <a href="<?= base_url('dashboard/daftar-ulang') ?>"
                    class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-semibold text-white rounded-xl transition"
                    style="background:hsl(220,54%,20%);"
                    onmouseover="this.style.background='hsl(220,54%,28%)'"
                    onmouseout="this.style.background='hsl(220,54%,20%)'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <polyline points="1 4 1 10 7 10" />
                        <path d="M3.51 15a9 9 0 102.13-9.36L1 10" />
                    </svg>
                    Ajukan Ulang
                </a>
            </div>
        <?php endif; ?>

        <!-- ══ TIMELINE 4-STEP (sesuai mockup DaftarUlangPage.tsx) ══════════ -->
        <div class="bg-white rounded-2xl p-6"
            style="border:1px solid hsl(220,20%,88%);box-shadow:0 4px 6px -1px hsl(220 54% 20%/0.07);">

            <h3 class="font-semibold mb-6 flex items-center gap-2" style="color:hsl(220,54%,15%);">
                <svg class="w-5 h-5" style="color:hsl(220,54%,20%);" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10" />
                    <polyline points="12 6 12 12 16 14" />
                </svg>
                Status Daftar Ulang
            </h3>

            <?php
            // Tentukan step state berdasarkan status
            // 1=Diterima, 2=Upload Bukti, 3=Verifikasi, 4=Siswa Aktif
            if ($du->status === 'pending') {
                // Step 1 done, step 2 done (sudah upload), step 3 current, step 4 pending
                $stepStates = ['done', 'done', 'current', 'pending'];
            } elseif ($du->status === 'dikonfirmasi') {
                // Semua done
                $stepStates = ['done', 'done', 'done', 'done'];
            } elseif ($du->status === 'ditolak') {
                // Step 1 done, step 2 rejected, step 3 & 4 pending
                $stepStates = ['done', 'rejected', 'pending', 'pending'];
            } else {
                // Default: hanya step 1 done
                $stepStates = ['done', 'current', 'pending', 'pending'];
            }

            $timelineSteps = [
                ['label' => 'Diterima',    'sublabel' => $pendaftaran->tanggal_kelulusan ?? null],
                ['label' => 'Upload Bukti', 'sublabel' => null],
                ['label' => 'Verifikasi',  'sublabel' => null],
                ['label' => 'Siswa Aktif', 'sublabel' => null],
            ];
            ?>

            <div class="relative">
                <!-- Garis penghubung background -->
                <div class="absolute top-5 left-0 right-0 h-0.5" style="background:hsl(220,20%,90%);margin:0 2.5rem;z-index:0;"></div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-2 relative" style="z-index:1;">
                    <?php foreach ($timelineSteps as $i => $step):
                        $state = $stepStates[$i];
                        switch ($state) {
                            case 'done':
                                $dotStyle   = 'background:hsl(142,71%,45%);border:2px solid hsl(142,71%,45%);';
                                $textColor  = 'hsl(220,54%,15%)';
                                $badgeBg    = 'hsl(142,71%,45%,.12)';
                                $badgeColor = 'hsl(142,55%,28%)';
                                $badgeLabel = 'Selesai';
                                $dotIcon    = '<svg class="w-4 h-4" fill="none" stroke="white" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>';
                                break;
                            case 'current':
                                $dotStyle   = 'background:hsl(38,92%,50%);border:2px solid hsl(38,92%,50%);';
                                $textColor  = 'hsl(220,54%,15%)';
                                $badgeBg    = 'hsl(38,92%,50%,.15)';
                                $badgeColor = 'hsl(38,60%,32%)';
                                $badgeLabel = 'Menunggu';
                                $dotIcon    = '<span class="w-2.5 h-2.5 rounded-full inline-block animate-pulse" style="background:white;"></span>';
                                break;
                            case 'rejected':
                                $dotStyle   = 'background:hsl(0,72%,51%);border:2px solid hsl(0,72%,51%);';
                                $textColor  = 'hsl(0,55%,40%)';
                                $badgeBg    = 'hsl(0,72%,51%,.1)';
                                $badgeColor = 'hsl(0,55%,40%)';
                                $badgeLabel = 'Ditolak';
                                $dotIcon    = '<svg class="w-4 h-4" fill="none" stroke="white" stroke-width="2.5" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>';
                                break;
                            default: // pending
                                $dotStyle   = 'background:hsl(220,20%,90%);border:2px solid hsl(220,20%,82%);';
                                $textColor  = 'hsl(220,15%,65%)';
                                $badgeBg    = 'hsl(220,20%,92%)';
                                $badgeColor = 'hsl(220,15%,55%)';
                                $badgeLabel = 'Belum';
                                $dotIcon    = '';
                                break;
                        }
                    ?>
                        <div class="flex flex-col items-center gap-2">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center"
                                style="<?= $dotStyle ?>">
                                <?= $dotIcon ?>
                            </div>
                            <div class="text-center">
                                <p class="font-medium text-xs leading-tight" style="color:<?= $textColor ?>;"><?= $step['label'] ?></p>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold mt-1"
                                    style="background:<?= $badgeBg ?>;color:<?= $badgeColor ?>;">
                                    <?= $badgeLabel ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

    <?php endif; ?>
</div>