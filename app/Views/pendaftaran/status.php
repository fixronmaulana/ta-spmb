<?php

/**
 * View: pendaftaran/status.php
 *
 * Variabel yang tersedia dari PendaftaranController::status():
 *   $pendaftaran  — object (dengan relasi jurusan & periode via getWithRelations)
 *   $dataDiri     — object|null
 *   $dokumens     — array of objects (DokumenModel)
 *   $timeline     — array (dari buildTimeline())
 *
 * PERUBAHAN:
 *   - Tombol "Upload Ulang" (di tabel & section catatan admin) diubah dari
 *     <a href="...step/4"> menjadi <button @click="openUpload(...)"> yang
 *     membuka modal inline dengan progress bar upload real-time (XHR).
 *   - Modal catatan admin & modal upload ulang digabung dalam satu x-data
 *     "statusModals()" agar openNote() dan openUpload() bisa dipanggil
 *     dari manapun dalam halaman tanpa konflik scope Alpine.js.
 *   - Ditambahkan toast notifikasi sukses (kanan bawah) dengan auto-dismiss
 *     4.5 detik dan progress bar shrink, persis seperti gambar referensi.
 */

$p = $pendaftaran;
$d = $dataDiri;

// ── Tentukan apakah status "terverifikasi" (lulus / siswa_aktif / daftar_ulang) ──
$statusVerified  = in_array($p->status, ['lulus', 'daftar_ulang', 'siswa_aktif']);
$statusSubmitted = ! in_array($p->status, ['draft', 'revisi']);

// ── Helper: warna & label badge status utama ─────────────────────────────────
$statusMap = [
    'draft'        => ['label' => 'Draft',                'dot' => 'hsl(220,15%,55%)',   'bg' => 'hsl(220,20%,96%)',   'border' => 'hsl(220,20%,85%)'],
    'revisi'       => ['label' => 'Perlu Revisi',         'dot' => 'hsl(38,92%,50%)',    'bg' => 'hsl(38,92%,50%,.1)', 'border' => 'hsl(38,92%,50%,.3)'],
    'submitted'    => ['label' => 'Menunggu Verifikasi',  'dot' => 'hsl(199,89%,48%)',   'bg' => 'hsl(199,89%,48%,.1)', 'border' => 'hsl(199,89%,48%,.3)'],
    'verifikasi'   => ['label' => 'Dalam Verifikasi',     'dot' => 'hsl(38,92%,50%)',    'bg' => 'hsl(38,92%,50%,.1)', 'border' => 'hsl(38,92%,50%,.3)'],
    'seleksi'      => ['label' => 'Proses Seleksi',       'dot' => 'hsl(262,70%,60%)',   'bg' => 'hsl(262,70%,60%,.1)', 'border' => 'hsl(262,70%,60%,.3)'],
    'lulus'        => ['label' => 'Diterima / Lulus',     'dot' => 'hsl(142,71%,45%)',   'bg' => 'hsl(142,71%,45%,.1)', 'border' => 'hsl(142,71%,45%,.3)'],
    'tidak_lulus'  => ['label' => 'Tidak Diterima',       'dot' => 'hsl(0,72%,51%)',     'bg' => 'hsl(0,72%,51%,.1)',  'border' => 'hsl(0,72%,51%,.3)'],
    'daftar_ulang' => ['label' => 'Daftar Ulang',         'dot' => 'hsl(160,60%,40%)',   'bg' => 'hsl(160,60%,40%,.1)', 'border' => 'hsl(160,60%,40%,.3)'],
    'siswa_aktif'  => ['label' => 'Siswa Aktif',          'dot' => 'hsl(142,71%,45%)',   'bg' => 'hsl(142,71%,45%,.1)', 'border' => 'hsl(142,71%,45%,.3)'],
];
$statusInfo = $statusMap[$p->status] ?? ['label' => ucfirst($p->status), 'dot' => 'hsl(220,15%,55%)', 'bg' => 'hsl(220,20%,96%)', 'border' => 'hsl(220,20%,85%)'];

// ── Helper: status verifikasi dokumen ───────────────────────────────────────
$dokStatus = [
    'pending'  => ['label' => 'Menunggu Verifikasi', 'dot' => 'hsl(38,92%,50%)',  'bg' => 'hsl(38,92%,50%,.1)', 'border' => 'hsl(38,92%,50%,.3)',  'icon' => 'clock'],
    'approved' => ['label' => 'Valid',               'dot' => 'hsl(142,71%,45%)', 'bg' => 'hsl(142,71%,45%,.1)', 'border' => 'hsl(142,71%,45%,.3)', 'icon' => 'check'],
    'rejected' => ['label' => 'Perlu Perbaikan',     'dot' => 'hsl(0,72%,51%)',   'bg' => 'hsl(0,72%,51%,.1)', 'border' => 'hsl(0,72%,51%,.3)',   'icon' => 'x'],
];

// ── Cek apakah ada dokumen rejected ─────────────────────────────────────────
$hasRejected = false;
$rejectedDocs = [];
foreach ($dokumens as $dok) {
    if ($dok->status_verifikasi === 'rejected') {
        $hasRejected = true;
        $rejectedDocs[] = $dok;
    }
}
?>

<div class="max-w-4xl mx-auto space-y-6" x-data="statusModals()">

    <!-- ══ BACK BUTTON ══════════════════════════════════════════════════ -->
    <div>
        <a href="<?= base_url('dashboard') ?>"
            class="inline-flex items-center gap-2 text-sm font-medium transition-colors rounded-xl px-4 py-2"
            style="color:hsl(220,54%,20%);background:transparent;"
            onmouseover="this.style.background='hsl(220,20%,92%)'"
            onmouseout="this.style.background='transparent'">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <polyline points="19 12 5 12" />
                <polyline points="12 19 5 12 12 5" />
            </svg>
            Kembali ke Dashboard
        </a>
    </div>

    <!-- ══ STATUS CARD UTAMA ══════════════════════════════════════════════ -->
    <div class="bg-white rounded-2xl overflow-hidden"
        style="border:1px solid <?= $statusVerified ? 'hsl(142,71%,45%,.3)' : 'hsl(38,92%,50%,.3)' ?>;
               box-shadow:0 4px 6px -1px hsl(220 54% 20%/0.07),0 2px 4px -2px hsl(220 54% 20%/0.05);">

        <div class="p-6 text-center">
            <!-- Icon Bulat -->
            <div class="w-16 h-16 rounded-full mx-auto mb-4 flex items-center justify-center"
                style="background:<?= $statusVerified ? 'hsl(142,71%,45%,.1)' : 'hsl(38,92%,50%,.1)' ?>;">
                <?php if ($statusVerified): ?>
                    <svg class="w-8 h-8" style="color:hsl(142,71%,45%);" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M22 11.08V12a10 10 0 11-5.93-9.14" />
                        <polyline points="22 4 12 14.01 9 11.01" />
                    </svg>
                <?php else: ?>
                    <svg class="w-8 h-8" style="color:hsl(38,92%,50%);" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10" />
                        <polyline points="12 6 12 12 16 14" />
                    </svg>
                <?php endif; ?>
            </div>

            <!-- Judul Status -->
            <h1 class="text-2xl font-bold font-serif mb-3" style="color:hsl(220,54%,15%);">
                Status Pendaftaran:
                <span style="color:<?= $statusVerified ? 'hsl(142,60%,30%)' : 'hsl(38,60%,35%)' ?>;">
                    <?= strtoupper($statusInfo['label']) ?>
                </span>
            </h1>

            <!-- Meta info: No. Pendaftaran + Tanggal -->
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4 text-sm"
                style="color:hsl(220,15%,50%);">
                <span class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" />
                        <polyline points="14 2 14 8 20 8" />
                    </svg>
                    Nomor Pendaftaran:
                    <strong style="color:hsl(220,54%,15%);">
                        <?= esc($p->no_pendaftaran ?? '—') ?>
                    </strong>
                </span>
                <?php if ($p->submitted_at): ?>
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                            <line x1="16" y1="2" x2="16" y2="6" />
                            <line x1="8" y1="2" x2="8" y2="6" />
                            <line x1="3" y1="10" x2="21" y2="10" />
                        </svg>
                        Tanggal Submit: <?= format_tanggal($p->submitted_at) ?>, <?= date('H:i', strtotime($p->submitted_at)) ?> WIB
                    </span>
                <?php endif; ?>
            </div>

            <!-- Tanggal terverifikasi (jika sudah) -->
            <?php if ($p->verified_at && $statusVerified): ?>
                <p class="text-sm mt-2" style="color:hsl(142,60%,30%);">
                    Tanggal Terverifikasi: <?= format_tanggal($p->verified_at) ?>, <?= date('H:i', strtotime($p->verified_at)) ?> WIB
                </p>
            <?php endif; ?>
        </div>
    </div>

    <!-- ══ TABEL VERIFIKASI DOKUMEN ═══════════════════════════════════════ -->
    <div class="bg-white rounded-2xl overflow-hidden"
        style="border:1px solid hsl(220,20%,88%);box-shadow:0 4px 6px -1px hsl(220 54% 20%/0.07),0 2px 4px -2px hsl(220 54% 20%/0.05);">

        <!-- Header Tabel -->
        <div class="px-5 py-4 border-b flex items-center gap-2"
            style="border-color:hsl(220,20%,92%);background:hsl(220,20%,98%);">
            <svg class="w-5 h-5" style="color:hsl(220,54%,20%);" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" />
                <polyline points="14 2 14 8 20 8" />
            </svg>
            <h2 class="font-semibold" style="color:hsl(220,54%,15%);">Status Verifikasi Dokumen</h2>
        </div>

        <!-- Tabel -->
        <div class="overflow-x-auto">
            <table class="w-full min-w-[500px]">
                <thead>
                    <tr style="border-bottom:1px solid hsl(220,20%,92%);background:hsl(220,20%,98%);">
                        <th class="text-left px-5 py-3 text-xs font-semibold" style="color:hsl(220,15%,50%);">No</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold" style="color:hsl(220,15%,50%);">Dokumen</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold" style="color:hsl(220,15%,50%);">Status</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold" style="color:hsl(220,15%,50%);">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($dokumens)): ?>
                        <tr>
                            <td colspan="4" class="px-5 py-8 text-center text-sm" style="color:hsl(220,15%,55%);">
                                Belum ada dokumen yang diupload.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($dokumens as $i => $dok):
                            $sv   = $dok->status_verifikasi ?? 'pending';
                            $info = $dokStatus[$sv] ?? $dokStatus['pending'];
                        ?>
                            <tr style="border-bottom:1px solid hsl(220,20%,94%);"
                                onmouseover="this.style.background='hsl(220,20%,98%)'"
                                onmouseout="this.style.background='transparent'">

                                <!-- No -->
                                <td class="px-5 py-4 text-sm" style="color:hsl(220,15%,55%);"><?= $i + 1 ?></td>

                                <!-- Nama Dokumen + Icon status -->
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-2.5">
                                        <?php if ($sv === 'approved'): ?>
                                            <svg class="w-5 h-5 flex-shrink-0" style="color:hsl(142,71%,45%);" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path d="M22 11.08V12a10 10 0 11-5.93-9.14" />
                                                <polyline points="22 4 12 14.01 9 11.01" />
                                            </svg>
                                        <?php elseif ($sv === 'rejected'): ?>
                                            <svg class="w-5 h-5 flex-shrink-0" style="color:hsl(0,72%,51%);" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <circle cx="12" cy="12" r="10" />
                                                <line x1="15" y1="9" x2="9" y2="15" />
                                                <line x1="9" y1="9" x2="15" y2="15" />
                                            </svg>
                                        <?php else: ?>
                                            <svg class="w-5 h-5 flex-shrink-0" style="color:hsl(220,15%,60%);" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <circle cx="12" cy="12" r="10" />
                                                <polyline points="12 6 12 12 16 14" />
                                            </svg>
                                        <?php endif; ?>
                                        <span class="text-sm font-medium" style="color:hsl(220,54%,15%);">
                                            <?= esc(jenis_dokumen_label($dok->jenis_dokumen)) ?>
                                        </span>
                                    </div>
                                </td>

                                <!-- Badge Status -->
                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium"
                                        style="background:<?= $info['bg'] ?>;border:1px solid <?= $info['border'] ?>;color:<?= $info['dot'] ?>;">
                                        <span class="w-1.5 h-1.5 rounded-full flex-shrink-0" style="background:<?= $info['dot'] ?>;"></span>
                                        <?= $info['label'] ?>
                                    </span>
                                </td>

                                <!-- Aksi -->
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-2">
                                        <!-- Lihat dokumen -->
                                        <?php if (! empty($dok->path_file)): ?>
                                            <a href="<?= base_url('dashboard/dokumen/lihat/' . $dok->id) ?>"
                                                target="_blank"
                                                title="Lihat dokumen"
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg transition"
                                                style="color:hsl(220,54%,20%);background:hsl(220,20%,96%);"
                                                onmouseover="this.style.background='hsl(220,20%,88%)'"
                                                onmouseout="this.style.background='hsl(220,20%,96%)'">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                                    <circle cx="12" cy="12" r="3" />
                                                </svg>
                                            </a>
                                        <?php endif; ?>

                                        <!-- Catatan admin (jika rejected) + Upload Ulang -->
                                        <?php if ($sv === 'rejected'): ?>
                                            <?php if (! empty($dok->catatan_verifikasi)): ?>
                                                <!-- Tombol lihat catatan -->
                                                <button type="button"
                                                    @click="openNote(<?= $dok->id ?>, '<?= esc(jenis_dokumen_label($dok->jenis_dokumen)) ?>', '<?= esc(addslashes($dok->catatan_verifikasi)) ?>')"
                                                    title="Lihat catatan admin"
                                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg transition"
                                                    style="color:hsl(38,60%,35%);background:hsl(38,92%,50%,.12);"
                                                    onmouseover="this.style.background='hsl(38,92%,50%,.22)'"
                                                    onmouseout="this.style.background='hsl(38,92%,50%,.12)'">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z" />
                                                    </svg>
                                                </button>
                                            <?php endif; ?>

                                            <!-- ✅ FIX: Tombol Upload Ulang — buka modal, bukan redirect -->
                                            <?php if (in_array($p->status, ['verifikasi', 'revisi', 'submitted'])): ?>
                                                <button type="button"
                                                    @click="openUpload('<?= esc($dok->jenis_dokumen) ?>', '<?= esc(jenis_dokumen_label($dok->jenis_dokumen)) ?>', '<?= esc(addslashes($dok->catatan_verifikasi ?? '')) ?>')"
                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg transition"
                                                    style="background:hsl(0,72%,51%,.1);color:hsl(0,55%,40%);border:1px solid hsl(0,72%,51%,.25);"
                                                    onmouseover="this.style.background='hsl(0,72%,51%,.18)'"
                                                    onmouseout="this.style.background='hsl(0,72%,51%,.1)'">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <polyline points="16 16 12 12 8 16" />
                                                        <line x1="12" y1="12" x2="12" y2="21" />
                                                        <path d="M20.39 18.39A5 5 0 0018 9h-1.26A8 8 0 103 16.3" />
                                                    </svg>
                                                    Upload Ulang
                                                </button>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ══ CATATAN ADMIN (jika ada dokumen rejected) ═══════════════════════ -->
    <?php if ($hasRejected): ?>
        <div class="rounded-2xl p-5 animate-scale-in"
            style="background:hsl(0,72%,51%,.06);border:1px solid hsl(0,72%,51%,.2);">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 flex-shrink-0 mt-0.5" style="color:hsl(0,55%,45%);" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10" />
                    <line x1="12" y1="8" x2="12" y2="12" />
                    <line x1="12" y1="16" x2="12.01" y2="16" />
                </svg>
                <div class="flex-1">
                    <p class="font-semibold text-sm mb-2" style="color:hsl(0,55%,40%);">Catatan Admin — Perlu Perbaikan</p>
                    <?php foreach ($rejectedDocs as $rd): ?>
                        <div class="mt-2 pl-3" style="border-left:2px solid hsl(0,72%,51%,.4);">
                            <p class="text-sm font-semibold" style="color:hsl(220,54%,15%);">
                                <?= esc(jenis_dokumen_label($rd->jenis_dokumen)) ?>:
                            </p>
                            <p class="text-sm mt-0.5" style="color:hsl(220,15%,50%);">
                                <?= esc($rd->catatan_verifikasi ?? 'Silakan upload ulang dokumen ini.') ?>
                            </p>
                        </div>
                    <?php endforeach; ?>

                    <!-- ✅ FIX: Tombol besar — buka modal upload, bukan redirect -->
                    <?php if (in_array($p->status, ['verifikasi', 'revisi', 'submitted']) && ! empty($rejectedDocs)): ?>
                        <button type="button"
                            @click="openUpload('<?= esc($rejectedDocs[0]->jenis_dokumen) ?>', '<?= esc(jenis_dokumen_label($rejectedDocs[0]->jenis_dokumen)) ?>', '<?= esc(addslashes($rejectedDocs[0]->catatan_verifikasi ?? '')) ?>')"
                            class="mt-4 inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-white rounded-xl transition"
                            style="background:hsl(0,55%,45%);"
                            onmouseover="this.style.background='hsl(0,55%,38%)'"
                            onmouseout="this.style.background='hsl(0,55%,45%)'">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <polyline points="16 16 12 12 8 16" />
                                <line x1="12" y1="12" x2="12" y2="21" />
                                <path d="M20.39 18.39A5 5 0 0018 9h-1.26A8 8 0 103 16.3" />
                            </svg>
                            Upload Ulang Dokumen
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- ══ CATATAN ADMIN / ALASAN PENOLAKAN (dari pendaftaran) ═══════════ -->
    <?php if (! empty($p->catatan_admin) || ! empty($p->alasan_penolakan)): ?>
        <div class="rounded-2xl p-5"
            style="background:hsl(38,92%,50%,.07);border:1px solid hsl(38,92%,50%,.25);">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 flex-shrink-0 mt-0.5" style="color:hsl(38,60%,35%);" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z" />
                </svg>
                <div>
                    <p class="font-semibold text-sm" style="color:hsl(38,60%,30%);">
                        <?= ! empty($p->alasan_penolakan) ? 'Alasan Penolakan' : 'Catatan Admin' ?>
                    </p>
                    <p class="text-sm mt-1" style="color:hsl(220,15%,45%);">
                        <?= esc($p->alasan_penolakan ?? $p->catatan_admin) ?>
                    </p>
                    <?php if ($p->status === 'revisi'): ?>
                        <a href="<?= base_url('dashboard/formulir') ?>"
                            class="mt-3 inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white rounded-xl transition"
                            style="background:hsl(38,70%,45%);"
                            onmouseover="this.style.background='hsl(38,70%,38%)'"
                            onmouseout="this.style.background='hsl(38,70%,45%)'">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7" />
                                <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                            </svg>
                            Perbaiki Formulir
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- ══ PILIHAN JURUSAN ════════════════════════════════════════════════ -->
    <div class="bg-white rounded-2xl p-5"
        style="border:1px solid hsl(220,20%,88%);box-shadow:0 4px 6px -1px hsl(220 54% 20%/0.07),0 2px 4px -2px hsl(220 54% 20%/0.05);">
        <h3 class="font-semibold text-sm mb-4 flex items-center gap-2" style="color:hsl(220,54%,15%);">
            <svg class="w-5 h-5" style="color:hsl(220,54%,20%);" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M22 10v6M2 10l10-5 10 5-10 5z" />
                <path d="M6 12v5c3 3 9 3 12 0v-5" />
            </svg>
            Pilihan Jurusan
        </h3>
        <div class="space-y-2.5">
            <div class="flex items-center gap-3">
                <span class="w-6 h-6 rounded flex items-center justify-center text-xs font-bold flex-shrink-0"
                    style="background:hsl(220,54%,20%,.1);color:hsl(220,54%,20%);">1</span>
                <span class="text-sm font-medium" style="color:hsl(220,54%,15%);">
                    <?= esc($p->jurusan_pilihan1_nama ?? '—') ?>
                </span>
                <?php if ($p->jurusan_pilihan1_kode): ?>
                    <span class="text-xs px-2 py-0.5 rounded font-semibold"
                        style="background:hsl(43,70%,47%,.12);color:hsl(43,58%,33%);">
                        <?= esc($p->jurusan_pilihan1_kode) ?>
                    </span>
                <?php endif; ?>
            </div>
            <?php if (! empty($p->jurusan_pilihan2_nama)): ?>
                <div class="flex items-center gap-3">
                    <span class="w-6 h-6 rounded flex items-center justify-center text-xs font-bold flex-shrink-0"
                        style="background:hsl(220,20%,92%);color:hsl(220,15%,50%);">2</span>
                    <span class="text-sm" style="color:hsl(220,15%,50%);">
                        <?= esc($p->jurusan_pilihan2_nama) ?>
                    </span>
                    <?php if ($p->jurusan_pilihan2_kode): ?>
                        <span class="text-xs px-2 py-0.5 rounded font-semibold"
                            style="background:hsl(43,70%,47%,.08);color:hsl(43,58%,40%);">
                            <?= esc($p->jurusan_pilihan2_kode) ?>
                        </span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <?php if (! empty($p->jurusan_diterima_nama)): ?>
                <div class="mt-3 flex items-center gap-2 p-3 rounded-xl"
                    style="background:hsl(142,71%,45%,.08);border:1px solid hsl(142,71%,45%,.2);">
                    <svg class="w-4 h-4 flex-shrink-0" style="color:hsl(142,60%,35%);" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M22 11.08V12a10 10 0 11-5.93-9.14" />
                        <polyline points="22 4 12 14.01 9 11.01" />
                    </svg>
                    <p class="text-sm font-semibold" style="color:hsl(142,60%,30%);">
                        Diterima di: <?= esc($p->jurusan_diterima_nama) ?>
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ══ TIMELINE PROGRESS ═════════════════════════════════════════════ -->
    <div class="bg-white rounded-2xl p-6"
        style="border:1px solid hsl(220,20%,88%);box-shadow:0 4px 6px -1px hsl(220 54% 20%/0.07),0 2px 4px -2px hsl(220 54% 20%/0.05);">

        <h2 class="font-semibold mb-6 flex items-center gap-2" style="color:hsl(220,54%,15%);">
            <svg class="w-5 h-5" style="color:hsl(220,54%,20%);" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10" />
                <polyline points="12 6 12 12 16 14" />
            </svg>
            Timeline Progress
        </h2>

        <div class="relative">
            <?php foreach ($timeline as $i => $step):
                $isDone    = $step['state'] === 'done';
                $isCurrent = $step['state'] === 'current';
                $isPending = $step['state'] === 'pending';
                $isLast    = $i === count($timeline) - 1;
            ?>
                <div class="flex gap-4 <?= ! $isLast ? 'pb-6' : '' ?>">

                    <!-- Dot & Vertical Line -->
                    <div class="relative flex flex-col items-center flex-shrink-0">
                        <div class="w-4 h-4 rounded-full border-2 z-10 flex-shrink-0"
                            style="<?php
                                    if ($isDone)         echo 'background:hsl(142,71%,45%);border-color:hsl(142,71%,45%);';
                                    elseif ($isCurrent)  echo 'background:hsl(38,92%,50%);border-color:hsl(38,92%,50%);';
                                    else                 echo 'background:white;border-color:hsl(220,20%,82%);';
                                    ?>">
                        </div>
                        <?php if (! $isLast): ?>
                            <div class="w-0.5 flex-1 mt-1"
                                style="background:<?= $isDone ? 'hsl(142,71%,45%)' : 'hsl(220,20%,88%)' ?>;min-height:1.5rem;">
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Content -->
                    <div class="flex-1 pb-1 flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-medium"
                                style="color:<?php
                                                if ($isCurrent)  echo 'hsl(38,60%,35%)';
                                                elseif ($isDone) echo 'hsl(220,54%,15%)';
                                                else             echo 'hsl(220,15%,55%)';
                                                ?>;">
                                <?= esc($step['label']) ?>
                            </p>
                            <?php if ($isCurrent): ?>
                                <p class="text-xs mt-0.5" style="color:hsl(38,60%,45%);">Sedang berlangsung...</p>
                            <?php endif; ?>
                        </div>

                        <!-- Icon kanan -->
                        <?php if ($isDone): ?>
                            <svg class="w-5 h-5 flex-shrink-0 mt-0.5" style="color:hsl(142,71%,45%);" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M22 11.08V12a10 10 0 11-5.93-9.14" />
                                <polyline points="22 4 12 14.01 9 11.01" />
                            </svg>
                        <?php elseif ($isCurrent): ?>
                            <svg class="w-5 h-5 flex-shrink-0 mt-0.5 animate-spin" style="color:hsl(38,92%,50%);" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="10" />
                                <polyline points="12 6 12 12 16 14" />
                            </svg>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- ══ INFO BOX ══════════════════════════════════════════════════════ -->
    <div class="rounded-2xl p-5"
        style="background:hsl(199,89%,48%,.07);border:1px solid hsl(199,89%,48%,.2);">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 flex-shrink-0 mt-0.5" style="color:hsl(199,89%,48%);" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10" />
                <line x1="12" y1="8" x2="12" y2="12" />
                <line x1="12" y1="16" x2="12.01" y2="16" />
            </svg>
            <div class="text-sm space-y-1" style="color:hsl(220,15%,45%);">
                <p>Proses verifikasi membutuhkan waktu <strong>3–5 hari kerja</strong>.</p>
                <p>Anda akan menerima notifikasi melalui sistem ini jika ada pembaruan status.</p>
                <p>Jika ada pertanyaan, hubungi panitia SPMB melalui
                    <a href="https://wa.me/62812xxxx" class="font-semibold" style="color:hsl(199,89%,40%);">
                        WhatsApp: 0812-xxxx-xxxx
                    </a>
                </p>
            </div>
        </div>
    </div>

    <!-- ══ ACTION BUTTONS ════════════════════════════════════════════════ -->
    <div class="flex flex-col sm:flex-row gap-3">

        <!-- Kembali ke Dashboard -->
        <a href="<?= base_url('dashboard') ?>"
            class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-medium rounded-xl border transition"
            style="border-color:hsl(220,20%,82%);color:hsl(220,54%,20%);background:white;"
            onmouseover="this.style.background='hsl(220,20%,96%)'"
            onmouseout="this.style.background='white'">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <polyline points="19 12 5 12" />
                <polyline points="12 19 5 12 12 5" />
            </svg>
            Kembali ke Dashboard
        </a>

        <!-- Spacer -->
        <div class="flex-1"></div>

        <!-- Unduh PDF (hanya jika verified) -->
        <?php if ($statusVerified): ?>
            <a href="<?= base_url('dashboard/cetak-bukti') ?>"
                class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold rounded-xl transition"
                style="background:hsl(142,71%,45%,.12);color:hsl(142,60%,30%);border:1px solid hsl(142,71%,45%,.3);"
                onmouseover="this.style.background='hsl(142,71%,45%,.2)'"
                onmouseout="this.style.background='hsl(142,71%,45%,.12)'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4" />
                    <polyline points="7 10 12 15 17 10" />
                    <line x1="12" y1="15" x2="12" y2="3" />
                </svg>
                Unduh PDF
            </a>
        <?php else: ?>
            <button disabled
                class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold rounded-xl cursor-not-allowed"
                style="background:hsl(220,20%,92%);color:hsl(220,15%,60%);border:1px solid hsl(220,20%,85%);">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4" />
                    <polyline points="7 10 12 15 17 10" />
                    <line x1="12" y1="15" x2="12" y2="3" />
                </svg>
                Unduh PDF
            </button>
        <?php endif; ?>

        <!-- Cetak Bukti -->
        <?php if ($statusSubmitted): ?>
            <a href="<?= base_url('dashboard/cetak-bukti') ?>"
                class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white rounded-xl transition"
                style="background:hsl(220,54%,20%);"
                onmouseover="this.style.background='hsl(220,54%,28%)'"
                onmouseout="this.style.background='hsl(220,54%,20%)'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <polyline points="6 9 6 2 18 2 18 9" />
                    <path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2" />
                    <rect x="6" y="14" width="12" height="8" />
                </svg>
                Cetak Bukti Pendaftaran
            </a>
        <?php else: ?>
            <button disabled
                class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold rounded-xl cursor-not-allowed"
                style="background:hsl(220,20%,92%);color:hsl(220,15%,60%);border:1px solid hsl(220,20%,85%);">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <polyline points="6 9 6 2 18 2 18 9" />
                    <path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2" />
                    <rect x="6" y="14" width="12" height="8" />
                </svg>
                Cetak Bukti Pendaftaran
            </button>
        <?php endif; ?>

        <!-- Daftar Ulang (jika lulus) -->
        <?php if (in_array($p->status, ['lulus', 'daftar_ulang'])): ?>
            <a href="<?= base_url('dashboard/daftar-ulang') ?>"
                class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white rounded-xl transition"
                style="background:hsl(160,60%,38%);"
                onmouseover="this.style.background='hsl(160,60%,30%)'"
                onmouseout="this.style.background='hsl(160,60%,38%)'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <polyline points="23 4 23 10 17 10" />
                    <path d="M20.49 15a9 9 0 11-2.12-9.36L23 10" />
                </svg>
                Daftar Ulang
            </a>
        <?php endif; ?>
    </div>

    <!-- ══════════════════════════════════════════════════════════════════════
         MODAL CATATAN ADMIN
    ════════════════════════════════════════════════════════════════════════ -->
    <div x-show="catatanOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-40 flex items-end sm:items-center justify-center sm:p-4"
        style="background:rgba(0,0,0,.4);"
        @click.self="catatanOpen = false"
        x-cloak>

        <div x-show="catatanOpen"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="w-full sm:max-w-md bg-white rounded-t-2xl sm:rounded-2xl shadow-xl max-h-[90vh] overflow-y-auto z-50"
            style="border:1px solid hsl(220,20%,88%);">

            <!-- Header -->
            <div class="px-6 py-4 border-b flex items-center justify-between"
                style="border-color:hsl(220,20%,92%);background:hsl(220,20%,98%);">
                <div>
                    <h3 class="font-bold font-serif" style="color:hsl(220,54%,15%);">Catatan Admin</h3>
                    <p class="text-xs mt-0.5" style="color:hsl(220,15%,55%);" x-text="'Dokumen: ' + catatanDocName"></p>
                </div>
                <button @click="catatanOpen = false"
                    class="w-8 h-8 rounded-lg flex items-center justify-center transition"
                    style="color:hsl(220,15%,55%);background:transparent;"
                    onmouseover="this.style.background='hsl(220,20%,92%)'"
                    onmouseout="this.style.background='transparent'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <line x1="18" y1="6" x2="6" y2="18" />
                        <line x1="6" y1="6" x2="18" y2="18" />
                    </svg>
                </button>
            </div>

            <!-- Body -->
            <div class="p-6">
                <div class="rounded-xl p-4" style="background:hsl(0,72%,51%,.06);border:1px solid hsl(0,72%,51%,.2);">
                    <p class="text-sm" style="color:hsl(220,15%,40%);" x-text="catatanText"></p>
                </div>
            </div>

            <!-- Footer -->
            <div class="px-6 pb-5">
                <button @click="catatanOpen = false"
                    class="w-full py-2.5 text-sm font-semibold text-white rounded-xl transition"
                    style="background:hsl(220,54%,20%);"
                    onmouseover="this.style.background='hsl(220,54%,28%)'"
                    onmouseout="this.style.background='hsl(220,54%,20%)'">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════════════════
         MODAL UPLOAD ULANG DOKUMEN
    ════════════════════════════════════════════════════════════════════════ -->
    <div x-show="uploadOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-40 flex items-end sm:items-center justify-center sm:p-4"
        style="background:rgba(0,0,0,.4);"
        @click.self="!uploading && closeUpload()"
        x-cloak>

        <div x-show="uploadOpen"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="w-full sm:max-w-md bg-white rounded-t-2xl sm:rounded-2xl shadow-xl max-h-[90vh] overflow-y-auto z-50"
            style="border:1px solid hsl(220,20%,88%);">

            <!-- Header Modal Upload -->
            <div class="px-6 py-4 border-b flex items-center justify-between"
                style="border-color:hsl(220,20%,92%);background:hsl(220,20%,98%);">
                <div class="flex items-center gap-2.5">
                    <svg class="w-5 h-5 flex-shrink-0" style="color:hsl(220,54%,20%);" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <polyline points="16 16 12 12 8 16" />
                        <line x1="12" y1="12" x2="12" y2="21" />
                        <path d="M20.39 18.39A5 5 0 0018 9h-1.26A8 8 0 103 16.3" />
                    </svg>
                    <div>
                        <h3 class="font-bold" style="color:hsl(220,54%,15%);">Upload Ulang Dokumen</h3>
                        <p class="text-xs mt-0.5" style="color:hsl(220,15%,55%);">
                            Unggah ulang <strong x-text="uploadDocLabel"></strong> sesuai catatan admin.
                        </p>
                    </div>
                </div>
                <button @click="!uploading && closeUpload()"
                    :disabled="uploading"
                    class="w-8 h-8 rounded-lg flex items-center justify-center transition"
                    style="color:hsl(220,15%,55%);background:transparent;"
                    onmouseover="this.style.background='hsl(220,20%,92%)'"
                    onmouseout="this.style.background='transparent'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <line x1="18" y1="6" x2="6" y2="18" />
                        <line x1="6" y1="6" x2="18" y2="18" />
                    </svg>
                </button>
            </div>

            <!-- Body Modal Upload -->
            <div class="p-6 space-y-4">

                <!-- Catatan Admin (jika ada) -->
                <div x-show="uploadCatatan"
                    class="rounded-xl p-4 flex items-start gap-2.5"
                    style="background:hsl(0,72%,51%,.06);border:1px solid hsl(0,72%,51%,.2);">
                    <svg class="w-4 h-4 flex-shrink-0 mt-0.5" style="color:hsl(0,55%,45%);" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10" />
                        <line x1="12" y1="8" x2="12" y2="12" />
                        <line x1="12" y1="16" x2="12.01" y2="16" />
                    </svg>
                    <div>
                        <p class="text-xs font-semibold mb-1" style="color:hsl(0,55%,40%);">Catatan Admin:</p>
                        <p class="text-sm" style="color:hsl(220,15%,40%);" x-text="uploadCatatan"></p>
                    </div>
                </div>

                <!-- Drop Zone — tampil saat belum pilih file & belum upload -->
                <div x-show="!selectedFile && !uploading"
                    @dragover.prevent="dragover = true"
                    @dragleave.prevent="dragover = false"
                    @drop.prevent="handleDrop($event)"
                    @click="$refs.fileInput.click()"
                    :style="dragover
                        ? 'border-color:hsl(220,54%,50%);background:hsl(220,54%,50%,.05);cursor:pointer;'
                        : 'border-color:hsl(220,20%,82%);background:transparent;cursor:pointer;'"
                    class="rounded-xl border-2 border-dashed p-8 flex flex-col items-center justify-center gap-3 transition-all"
                    style="border-color:hsl(220,20%,82%);">

                    <!-- Ikon dokumen -->
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center"
                        style="background:hsl(220,20%,96%);">
                        <svg class="w-6 h-6" style="color:hsl(220,15%,55%);" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" />
                            <polyline points="14 2 14 8 20 8" />
                            <line x1="12" y1="13" x2="12" y2="18" />
                            <line x1="9.5" y1="15.5" x2="14.5" y2="15.5" />
                        </svg>
                    </div>

                    <div class="text-center">
                        <p class="text-sm font-medium" style="color:hsl(220,54%,15%);">Klik atau seret file ke sini</p>
                        <p class="text-xs mt-1" style="color:hsl(220,15%,55%);">PDF, JPG, atau PNG (maks. 2MB)</p>
                    </div>

                    <input x-ref="fileInput"
                        type="file"
                        class="hidden"
                        accept=".pdf,.jpg,.jpeg,.png"
                        @change="handleFileSelect($event)" />
                </div>

                <!-- File Card — tampil setelah file dipilih -->
                <div x-show="selectedFile"
                    class="rounded-xl border p-4"
                    style="border-color:hsl(220,20%,88%);background:hsl(220,20%,98%);">

                    <div class="flex items-center gap-3">
                        <!-- Ikon: centang jika sudah selesai, dokumen jika belum -->
                        <div class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0 transition"
                            :style="uploadDone
                                ? 'background:hsl(142,71%,45%,.12);'
                                : 'background:hsl(220,54%,20%,.08);'">
                            <template x-if="uploadDone">
                                <svg class="w-5 h-5" style="color:hsl(142,60%,35%);" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path d="M22 11.08V12a10 10 0 11-5.93-9.14" />
                                    <polyline points="22 4 12 14.01 9 11.01" />
                                </svg>
                            </template>
                            <template x-if="!uploadDone">
                                <svg class="w-5 h-5" style="color:hsl(220,54%,20%);" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" />
                                    <polyline points="14 2 14 8 20 8" />
                                </svg>
                            </template>
                        </div>

                        <!-- Nama & ukuran file -->
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium truncate" style="color:hsl(220,54%,15%);" x-text="selectedFileName"></p>
                            <p class="text-xs mt-0.5" style="color:hsl(220,15%,55%);" x-text="selectedFileSize"></p>
                        </div>

                        <!-- Tombol hapus file (hanya saat belum upload / belum selesai) -->
                        <button x-show="!uploading && !uploadDone"
                            @click="clearFile()"
                            class="w-7 h-7 rounded-lg flex items-center justify-center flex-shrink-0 transition"
                            style="color:hsl(220,15%,55%);background:transparent;"
                            onmouseover="this.style.background='hsl(220,20%,92%)'"
                            onmouseout="this.style.background='transparent'">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <line x1="18" y1="6" x2="6" y2="18" />
                                <line x1="6" y1="6" x2="18" y2="18" />
                            </svg>
                        </button>
                    </div>

                    <!-- Progress Bar — tampil saat sedang atau sudah upload -->
                    <div x-show="uploading || uploadDone" class="mt-3">
                        <div class="flex justify-between items-center mb-1.5">
                            <span class="text-xs" style="color:hsl(220,15%,55%);"
                                x-text="uploadDone ? 'Upload selesai' : 'Mengunggah...'"></span>
                            <span class="text-xs font-semibold"
                                :style="uploadDone ? 'color:hsl(142,60%,35%);' : 'color:hsl(220,54%,20%);'"
                                x-text="uploadProgress + '%'"></span>
                        </div>
                        <div class="w-full rounded-full overflow-hidden" style="background:hsl(220,20%,88%);height:6px;">
                            <div class="h-full rounded-full transition-all duration-300"
                                :style="'width:' + uploadProgress + '%;background:' + (uploadDone ? 'hsl(142,71%,45%)' : 'linear-gradient(90deg,hsl(220,54%,30%),hsl(43,92%,55%))') + ';'">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pesan Error -->
                <div x-show="uploadError"
                    class="rounded-xl p-3 flex items-start gap-2"
                    style="background:hsl(0,72%,51%,.08);border:1px solid hsl(0,72%,51%,.25);">
                    <svg class="w-4 h-4 flex-shrink-0 mt-0.5" style="color:hsl(0,55%,45%);" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10" />
                        <line x1="12" y1="8" x2="12" y2="12" />
                        <line x1="12" y1="16" x2="12.01" y2="16" />
                    </svg>
                    <p class="text-xs" style="color:hsl(0,55%,40%);" x-text="uploadError"></p>
                </div>

            </div><!-- /body -->

            <!-- Footer Modal Upload -->
            <div class="px-6 pb-6 flex gap-3">
                <button @click="!uploading && closeUpload()"
                    :disabled="uploading"
                    class="flex-1 py-2.5 text-sm font-semibold rounded-xl border transition"
                    style="border-color:hsl(220,20%,82%);color:hsl(220,54%,20%);background:white;"
                    onmouseover="this.style.background='hsl(220,20%,96%)'"
                    onmouseout="this.style.background='white'">
                    Batal
                </button>

                <button @click="doUpload()"
                    :disabled="!selectedFile || uploading || uploadDone"
                    class="flex-1 py-2.5 text-sm font-semibold text-white rounded-xl transition flex items-center justify-center gap-2"
                    :style="(!selectedFile || uploading || uploadDone)
                        ? 'background:hsl(220,20%,80%);cursor:not-allowed;'
                        : 'background:hsl(220,54%,20%);cursor:pointer;'"
                    onmouseover="if(!this.disabled) this.style.background='hsl(220,54%,28%)'"
                    onmouseout="if(!this.disabled) this.style.background='hsl(220,54%,20%)'">
                    <!-- Spinner saat uploading -->
                    <template x-if="uploading">
                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                    </template>
                    <span x-text="uploading ? 'Mengunggah...' : 'Upload Sekarang'"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════════════════
         TOAST NOTIFIKASI SUKSES
    ════════════════════════════════════════════════════════════════════════ -->
    <div x-show="toastVisible"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-4"
        class="fixed bottom-4 right-4 z-50 w-[min(20rem,calc(100vw-2rem))] rounded-2xl shadow-xl overflow-hidden"
        style="border:1px solid hsl(142,71%,45%,.3);background:white;"
        x-cloak>

        <div class="flex items-start gap-3 p-4 pr-10 relative">
            <!-- Ikon centang -->
            <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                style="background:hsl(142,71%,45%,.12);">
                <svg class="w-5 h-5" style="color:hsl(142,60%,35%);" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path d="M22 11.08V12a10 10 0 11-5.93-9.14" />
                    <polyline points="22 4 12 14.01 9 11.01" />
                </svg>
            </div>

            <!-- Teks -->
            <div class="flex-1 min-w-0">
                <p class="font-semibold text-sm" style="color:hsl(220,54%,15%);">Upload berhasil</p>
                <p class="text-xs mt-0.5 leading-relaxed" style="color:hsl(220,15%,50%);"
                    x-text="toastDocName + ' berhasil diunggah ulang dan menunggu verifikasi admin.'"></p>
            </div>

            <!-- Tombol tutup -->
            <button @click="toastVisible = false"
                class="absolute top-3 right-3 w-6 h-6 rounded-lg flex items-center justify-center transition"
                style="color:hsl(220,15%,55%);background:transparent;"
                onmouseover="this.style.background='hsl(220,20%,92%)'"
                onmouseout="this.style.background='transparent'">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <line x1="18" y1="6" x2="6" y2="18" />
                    <line x1="6" y1="6" x2="18" y2="18" />
                </svg>
            </button>
        </div>

        <!-- Progress bar auto-close (shrink dari kanan ke kiri dalam 4.5 detik) -->
        <div class="h-1" style="background:hsl(220,20%,92%);">
            <div x-ref="toastBar"
                class="h-full"
                style="background:hsl(142,71%,45%);width:100%;transition:width 4.5s linear;">
            </div>
        </div>
    </div>

</div><!-- /x-data="statusModals()" -->

<!-- ══════════════════════════════════════════════════════════════════════════
     ALPINE.JS — statusModals()
     Menggabungkan: catatanModal + uploadUlangModal + toast notification
════════════════════════════════════════════════════════════════════════════ -->
<script>
    function statusModals() {
        return {

            /* ═══════════════════════════════════════
               MODAL CATATAN ADMIN
            ═══════════════════════════════════════ */
            catatanOpen: false,
            catatanDocId: null,
            catatanDocName: '',
            catatanText: '',

            openNote(id, name, note) {
                this.catatanDocId = id;
                this.catatanDocName = name;
                this.catatanText = note;
                this.catatanOpen = true;
            },

            /* ═══════════════════════════════════════
               MODAL UPLOAD ULANG
            ═══════════════════════════════════════ */
            uploadOpen: false,
            uploadJenis: '',
            uploadDocLabel: '',
            uploadCatatan: '',
            selectedFile: null,
            selectedFileName: '',
            selectedFileSize: '',
            dragover: false,
            uploading: false,
            uploadDone: false,
            uploadProgress: 0,
            uploadError: '',

            openUpload(jenis, label, catatan) {
                this.uploadJenis = jenis;
                this.uploadDocLabel = label;
                this.uploadCatatan = catatan;
                /* reset state file & progress */
                this.selectedFile = null;
                this.selectedFileName = '';
                this.selectedFileSize = '';
                this.uploading = false;
                this.uploadDone = false;
                this.uploadProgress = 0;
                this.uploadError = '';
                this.uploadOpen = true;
            },

            closeUpload() {
                if (this.uploading) return; // cegah tutup saat sedang upload
                this.uploadOpen = false;
            },

            clearFile() {
                this.selectedFile = null;
                this.selectedFileName = '';
                this.selectedFileSize = '';
                this.uploadError = '';
                this.uploadProgress = 0;
                this.uploadDone = false;
                /* reset input file agar bisa pilih file yang sama lagi */
                if (this.$refs.fileInput) this.$refs.fileInput.value = '';
            },

            handleDrop(e) {
                this.dragover = false;
                const f = e.dataTransfer.files[0];
                if (f) this.setFile(f);
            },

            handleFileSelect(e) {
                const f = e.target.files[0];
                if (f) this.setFile(f);
            },

            setFile(f) {
                this.uploadError = '';
                /* Validasi tipe */
                const allowed = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
                if (!allowed.includes(f.type)) {
                    this.uploadError = 'Format file tidak diizinkan. Gunakan PDF, JPG, atau PNG.';
                    return;
                }
                /* Validasi ukuran (maks 2 MB) */
                if (f.size > 2 * 1024 * 1024) {
                    this.uploadError = 'Ukuran file melebihi batas maksimal 2MB.';
                    return;
                }
                this.selectedFile = f;
                this.selectedFileName = f.name;
                this.selectedFileSize = this.formatSize(f.size);
                this.uploadDone = false;
                this.uploadProgress = 0;
            },

            formatSize(bytes) {
                if (bytes < 1024) return bytes + ' B';
                if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
                return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
            },

            doUpload() {
                if (!this.selectedFile || this.uploading || this.uploadDone) return;

                this.uploading = true;
                this.uploadError = '';
                this.uploadProgress = 0;

                /* ── Ambil CSRF token dari meta tag yang di-render CI4 di layout ── */
                const csrfMeta = document.querySelector('meta[name="csrf-token"]');
                const csrfToken = csrfMeta ? csrfMeta.content : '';

                const formData = new FormData();
                formData.append('file', this.selectedFile);
                formData.append('jenis_dokumen', this.uploadJenis);
                /* CI4 CSRF filter membaca token dari POST body (field 'csrf_token')
                   ATAU dari header 'X-CSRF-TOKEN' — kita kirim keduanya agar
                   pasti terdeteksi di semua konfigurasi CI4. */
                formData.append('csrf_token', csrfToken);

                const self = this;
                const xhr = new XMLHttpRequest();

                /* Pantau progress upload nyata dari XHR */
                xhr.upload.onprogress = function(e) {
                    if (e.lengthComputable) {
                        /* Stop di 95% — sisanya untuk server processing */
                        self.uploadProgress = Math.min(Math.round((e.loaded / e.total) * 95), 95);
                    }
                };

                xhr.onload = function() {
                    /* ── Refresh CSRF token dari response header (jika $regenerate=true) ── */
                    const newCsrf = xhr.getResponseHeader('X-CSRF-TOKEN');
                    if (newCsrf && csrfMeta) csrfMeta.content = newCsrf;

                    let resp = {};
                    try {
                        resp = JSON.parse(xhr.responseText);
                    } catch (err) {
                        /* ignore */
                    }

                    if (xhr.status === 200 && resp.success) {
                        /* Selesaikan progress ke 100% */
                        self.uploadProgress = 100;
                        self.uploadDone = true;
                        self.uploading = false;

                        /* Tutup modal setelah 800ms, lalu tampilkan toast */
                        setTimeout(function() {
                            self.uploadOpen = false;
                            self.toastDocName = self.uploadDocLabel;
                            self.toastVisible = true;

                            /* Mulai shrink progress bar toast */
                            self.$nextTick(function() {
                                const bar = self.$refs.toastBar;
                                if (bar) {
                                    bar.style.transition = 'none';
                                    bar.style.width = '100%';
                                    /* Force reflow agar browser tahu start value-nya */
                                    void bar.offsetWidth;
                                    bar.style.transition = 'width 4.5s linear';
                                    bar.style.width = '0%';
                                }
                            });

                            /* Auto-dismiss toast setelah 4.8 detik */
                            setTimeout(function() {
                                self.toastVisible = false;
                            }, 4800);
                        }, 800);

                    } else {
                        self.uploading = false;
                        self.uploadProgress = 0;
                        self.uploadDone = false;
                        self.uploadError = resp.message || 'Gagal mengupload file. Silakan coba lagi.';
                    }
                };

                xhr.onerror = function() {
                    self.uploading = false;
                    self.uploadProgress = 0;
                    self.uploadDone = false;
                    self.uploadError = 'Terjadi kesalahan jaringan. Periksa koneksi Anda dan coba lagi.';
                };

                xhr.open('POST', '<?= base_url('dashboard/formulir/upload-ulang-dokumen') ?>');
                /* Kirim CSRF juga via header — CI4 menerima dari salah satu atau keduanya */
                xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                xhr.send(formData);
            },

            /* ═══════════════════════════════════════
               TOAST NOTIFICATION
            ═══════════════════════════════════════ */
            toastVisible: false,
            toastDocName: '',
        };
    }
</script>