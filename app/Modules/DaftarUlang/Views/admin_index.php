<?php

/**
 * View: App\Modules\DaftarUlang\Views\admin_index.php
 *
 *  1. Stats cards: Total, Menunggu (warning), Tervalidasi (success), Ditolak (destructive)
 *  2. Filter + Search bar
 *  3. Tabel: No.Pendaftaran, Nama, Jurusan, Nominal, Tgl Upload, Status, Kelas, Aksi
 *  4. Modal Detail: 2-kolom info + preview bukti (dengan zoom + download)
 *  5. Modal Validasi: Penempatan Kelas (dropdown pilihan 1 & 2) + Catatan Admin
 *     — NIS DIHAPUS dari flow ini; NIS di-generate otomatis saat konversi ke Buku Induk
 *  6. Modal Tolak: alasan penolakan
 *
 * Variabel dari DaftarUlangAdminController::index():
 *   $daftars        — array   (dengan relasi termasuk pilihan 1 & 2 jurusan)
 *   $status         — string  (filter aktif)
 *   $search         — string  (query pencarian)
 *   $stats          — array   [total, pending, dikonfirmasi, ditolak]
 *   $kelasByJurusan — array   kelas aktif dikelompokkan per jurusan_id
 */

$stats = $stats ?? ['total' => 0, 'pending' => 0, 'dikonfirmasi' => 0, 'ditolak' => 0];
$kelasByJurusan = $kelasByJurusan ?? [];

$badgeCfg = [
    'pending'      => ['bg' => 'hsl(38,92%,50%,.12)',  'text' => 'hsl(38,60%,32%)',  'label' => 'Menunggu Verifikasi'],
    'dikonfirmasi' => ['bg' => 'hsl(142,71%,45%,.12)', 'text' => 'hsl(142,55%,28%)', 'label' => 'Tervalidasi'],
    'ditolak'      => ['bg' => 'hsl(0,72%,51%,.1)',    'text' => 'hsl(0,55%,40%)',   'label' => 'Ditolak'],
];
?>

<div class="space-y-6" x-data="daftarUlangAdmin()" x-init="init()">

    <!-- ══ PAGE HEADER ══════════════════════════════════════════════════ -->
    <div>
        <h1 class="text-2xl font-bold font-serif" style="color:hsl(220,54%,15%);">Verifikasi Daftar Ulang</h1>
        <p class="text-sm mt-0.5" style="color:hsl(220,15%,55%);">
            Verifikasi bukti pembayaran daftar ulang dan tentukan penempatan kelas calon siswa
        </p>
    </div>

    <!-- Flash messages -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm"
            style="background:hsl(142,71%,45%,.08);border:1px solid hsl(142,71%,45%,.3);color:hsl(142,55%,28%);">
            <svg class="h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <?= esc(session()->getFlashdata('success')) ?>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm"
            style="background:hsl(0,72%,51%,.08);border:1px solid hsl(0,72%,51%,.3);color:hsl(0,55%,38%);">
            <svg class="h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126z" />
            </svg>
            <?= esc(session()->getFlashdata('error')) ?>
        </div>
    <?php endif; ?>

    <!-- ══ STATS CARDS (sesuai mockup: 2×2 grid mobile, 4-col desktop) ══ -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

        <!-- Total -->
        <div class="bg-white rounded-2xl p-4" style="border:1px solid hsl(220,20%,88%);box-shadow:0 1px 3px hsl(220 54% 20%/0.06);">
            <p class="text-sm" style="color:hsl(220,15%,55%);">Total</p>
            <p class="text-2xl font-bold mt-1" style="color:hsl(220,54%,15%);"><?= $stats['total'] ?></p>
        </div>

        <!-- Menunggu (warning amber) -->
        <div class="bg-white rounded-2xl p-4" style="border:1px solid hsl(220,20%,88%);box-shadow:0 1px 3px hsl(220 54% 20%/0.06);">
            <p class="text-sm" style="color:hsl(220,15%,55%);">Menunggu</p>
            <p class="text-2xl font-bold mt-1" style="color:hsl(38,60%,32%);"><?= $stats['pending'] ?></p>
        </div>

        <!-- Tervalidasi (success green) -->
        <div class="bg-white rounded-2xl p-4" style="border:1px solid hsl(220,20%,88%);box-shadow:0 1px 3px hsl(220 54% 20%/0.06);">
            <p class="text-sm" style="color:hsl(220,15%,55%);">Tervalidasi</p>
            <p class="text-2xl font-bold mt-1" style="color:hsl(142,55%,28%);"><?= $stats['dikonfirmasi'] ?></p>
        </div>

        <!-- Ditolak (destructive red) -->
        <div class="bg-white rounded-2xl p-4" style="border:1px solid hsl(220,20%,88%);box-shadow:0 1px 3px hsl(220 54% 20%/0.06);">
            <p class="text-sm" style="color:hsl(220,15%,55%);">Ditolak</p>
            <p class="text-2xl font-bold mt-1" style="color:hsl(0,55%,40%);"><?= $stats['ditolak'] ?></p>
        </div>
    </div>

    <!-- ══ SEARCH + FILTER ══════════════════════════════════════════════ -->
    <div class="bg-white rounded-2xl p-4" style="border:1px solid hsl(220,20%,88%);">
        <form method="get" class="flex flex-col sm:flex-row gap-3">

            <!-- Search -->
            <div class="relative flex-1">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 pointer-events-none"
                    style="color:hsl(220,15%,60%);"
                    fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <circle cx="11" cy="11" r="8" />
                    <path d="M21 21l-4.35-4.35" />
                </svg>
                <input type="text" name="search"
                    value="<?= esc($search) ?>"
                    placeholder="Cari nama atau nomor pendaftaran..."
                    class="w-full pl-9 pr-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:border-transparent"
                    style="--tw-ring-color:hsl(220,54%,20%,.15);">
            </div>

            <!-- Filter Status -->
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 pointer-events-none"
                    style="color:hsl(220,15%,60%);"
                    fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3" />
                </svg>
                <select name="status"
                    class="pl-9 pr-8 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none w-full sm:w-56 appearance-none bg-white"
                    onchange="this.form.submit()">
                    <option value="" <?= $status === ''             ? 'selected' : '' ?>>Semua Status</option>
                    <option value="pending" <?= $status === 'pending'      ? 'selected' : '' ?>>Menunggu Verifikasi</option>
                    <option value="dikonfirmasi" <?= $status === 'dikonfirmasi' ? 'selected' : '' ?>>Tervalidasi</option>
                    <option value="ditolak" <?= $status === 'ditolak'      ? 'selected' : '' ?>>Ditolak</option>
                </select>
                <svg class="absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 pointer-events-none"
                    style="color:hsl(220,15%,60%);"
                    fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <polyline points="6 9 12 15 18 9" />
                </svg>
            </div>

            <button type="submit"
                class="px-5 py-2.5 text-sm font-semibold text-white rounded-xl transition"
                style="background:hsl(220,54%,20%);"
                onmouseover="this.style.background='hsl(220,54%,28%)'"
                onmouseout="this.style.background='hsl(220,54%,20%)'">
                Cari
            </button>
        </form>
    </div>

    <!-- ══ TABLE CARD ═══════════════════════════════════════════════════ -->
    <div class="bg-white rounded-2xl overflow-hidden"
        style="border:1px solid hsl(220,20%,88%);box-shadow:0 4px 6px -1px hsl(220 54% 20%/0.07);">

        <!-- Card Header -->
        <div class="px-5 py-4 border-b flex items-center gap-3" style="border-color:hsl(220,20%,92%);background:hsl(220,20%,98%);">
            <svg class="h-5 w-5" style="color:hsl(220,54%,20%);" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <rect x="2" y="5" width="20" height="14" rx="2" />
                <line x1="2" y1="10" x2="22" y2="10" />
            </svg>
            <h3 class="font-semibold" style="color:hsl(220,54%,15%);">Daftar Bukti Pembayaran</h3>
            <span class="ml-auto text-sm" style="color:hsl(220,15%,55%);"><?= count($daftars) ?> data</span>
        </div>

        <?php if (empty($daftars)): ?>
            <div class="py-16 text-center">
                <div class="w-14 h-14 rounded-full flex items-center justify-center mx-auto mb-4"
                    style="background:hsl(220,20%,95%);">
                    <svg class="w-7 h-7" style="color:hsl(220,20%,72%);" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" />
                        <polyline points="14 2 14 8 20 8" />
                    </svg>
                </div>
                <p class="text-sm font-medium" style="color:hsl(220,54%,15%);">Tidak ada data</p>
                <p class="text-xs mt-1" style="color:hsl(220,15%,55%);">
                    <?= $search ? 'Tidak ditemukan untuk pencarian "' . esc($search) . '"' : 'Belum ada pengajuan daftar ulang' ?>
                </p>
            </div>

        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[650px] text-sm">
                    <thead style="background:hsl(220,20%,97%);border-bottom:1px solid hsl(220,20%,92%);">
                        <tr>
                            <th class="text-left px-5 py-3.5 text-xs font-semibold uppercase tracking-wide" style="color:hsl(220,15%,50%);">No. Pendaftaran</th>
                            <th class="text-left px-4 py-3.5 text-xs font-semibold uppercase tracking-wide" style="color:hsl(220,15%,50%);">Nama</th>
                            <th class="text-left px-4 py-3.5 text-xs font-semibold uppercase tracking-wide" style="color:hsl(220,15%,50%);">Jurusan</th>
                            <th class="text-left px-4 py-3.5 text-xs font-semibold uppercase tracking-wide" style="color:hsl(220,15%,50%);">Nominal</th>
                            <th class="text-left px-4 py-3.5 text-xs font-semibold uppercase tracking-wide" style="color:hsl(220,15%,50%);">Tgl Upload</th>
                            <th class="text-left px-4 py-3.5 text-xs font-semibold uppercase tracking-wide" style="color:hsl(220,15%,50%);">Status</th>
                            <th class="text-left px-4 py-3.5 text-xs font-semibold uppercase tracking-wide" style="color:hsl(220,15%,50%);">Kelas</th>
                            <th class="text-right px-5 py-3.5 text-xs font-semibold uppercase tracking-wide" style="color:hsl(220,15%,50%);">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($daftars as $d):
                            $bc = $badgeCfg[$d->status] ?? ['bg' => 'hsl(220,20%,92%)', 'text' => 'hsl(220,15%,45%)', 'label' => ucfirst($d->status)];
                        ?>
                            <tr style="border-bottom:1px solid hsl(220,20%,95%);"
                                onmouseover="this.style.background='hsl(220,20%,98.5%)'"
                                onmouseout="this.style.background='transparent'">

                                <td class="px-5 py-3.5">
                                    <span class="text-xs font-mono" style="color:hsl(220,15%,50%);"><?= esc($d->no_pendaftaran) ?></span>
                                </td>

                                <td class="px-4 py-3.5">
                                    <p class="font-semibold text-sm" style="color:hsl(220,54%,15%);">
                                        <?= esc($d->nama_tampil ?? $d->nama_calon ?? '-') ?>
                                    </p>
                                    <p class="text-xs mt-0.5" style="color:hsl(220,15%,55%);"><?= esc($d->email_calon ?? '') ?></p>
                                </td>

                                <td class="px-4 py-3.5">
                                    <?php if ($d->jurusan_kode ?? null): ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold"
                                            style="background:hsl(220,54%,20%,.08);color:hsl(220,54%,20%);border:1px solid hsl(220,54%,20%,.2);">
                                            <?= esc($d->jurusan_kode) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-xs" style="color:hsl(220,15%,55%);">-</span>
                                    <?php endif; ?>
                                </td>

                                <td class="px-4 py-3.5">
                                    <span class="text-sm font-semibold" style="color:hsl(220,54%,15%);">
                                        Rp <?= number_format((int)$d->nominal_pembayaran, 0, ',', '.') ?>
                                    </span>
                                </td>

                                <td class="px-4 py-3.5">
                                    <span class="text-xs" style="color:hsl(220,15%,55%);">
                                        <?= date('d/m/Y H:i', strtotime($d->created_at)) ?>
                                    </span>
                                </td>

                                <td class="px-4 py-3.5">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold"
                                        style="background:<?= $bc['bg'] ?>;color:<?= $bc['text'] ?>;">
                                        <?= $bc['label'] ?>
                                    </span>
                                </td>

                                <td class="px-4 py-3.5">
                                    <?php if ($d->nama_kelas ?? null): ?>
                                        <span class="text-xs font-semibold" style="color:hsl(142,55%,28%);"><?= esc($d->nama_kelas) ?></span>
                                    <?php else: ?>
                                        <span class="text-xs" style="color:hsl(220,15%,65%);">—</span>
                                    <?php endif; ?>
                                </td>

                                <td class="px-5 py-3.5 text-right">
                                    <div class="flex items-center justify-end gap-1.5">

                                        <!-- Tombol Lihat Detail (Eye) -->
                                        <button type="button"
                                            @click="openDetail(<?= htmlspecialchars(json_encode($d)) ?>)"
                                            class="p-1.5 rounded-lg transition"
                                            style="color:hsl(199,60%,35%);background:hsl(199,89%,48%,.08);"
                                            onmouseover="this.style.background='hsl(199,89%,48%,.18)'"
                                            onmouseout="this.style.background='hsl(199,89%,48%,.08)'"
                                            title="Lihat Detail">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                                <circle cx="12" cy="12" r="3" />
                                            </svg>
                                        </button>

                                        <?php if ($d->status === 'pending'): ?>
                                            <!-- Validasi (CheckCircle2) -->
                                            <button type="button"
                                                @click="openApprove(<?= htmlspecialchars(json_encode($d)) ?>)"
                                                class="p-1.5 rounded-lg transition"
                                                style="color:hsl(142,55%,28%);background:hsl(142,71%,45%,.08);"
                                                onmouseover="this.style.background='hsl(142,71%,45%,.18)'"
                                                onmouseout="this.style.background='hsl(142,71%,45%,.08)'"
                                                title="Validasi Pembayaran">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                                    <path d="M22 11.08V12a10 10 0 11-5.93-9.14" />
                                                    <polyline points="22 4 12 14.01 9 11.01" />
                                                </svg>
                                            </button>

                                            <!-- Tolak (XCircle) -->
                                            <button type="button"
                                                @click="openReject(<?= htmlspecialchars(json_encode($d)) ?>)"
                                                class="p-1.5 rounded-lg transition"
                                                style="color:hsl(0,55%,40%);background:hsl(0,72%,51%,.08);"
                                                onmouseover="this.style.background='hsl(0,72%,51%,.18)'"
                                                onmouseout="this.style.background='hsl(0,72%,51%,.08)'"
                                                title="Tolak Bukti">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                                    <circle cx="12" cy="12" r="10" />
                                                    <line x1="15" y1="9" x2="9" y2="15" />
                                                    <line x1="9" y1="9" x2="15" y2="15" />
                                                </svg>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>


    <!-- ══════════════════════════════════════════════════════════════════
         MODAL: DETAIL BUKTI PEMBAYARAN
         Sesuai mockup: max-w-4xl, 2-kolom info + preview
    ══════════════════════════════════════════════════════════════════ -->
    <div x-show="detailOpen"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-end sm:items-center justify-center sm:p-4"
        style="background:rgba(17,24,39,.5);"
        @click.self="detailOpen = false"
        x-cloak>

        <div x-show="detailOpen"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
            class="bg-white rounded-t-2xl sm:rounded-2xl shadow-2xl w-full overflow-hidden"
            style="max-width:min(820px,100%);max-height:90vh;overflow-y:auto;">

            <!-- Header modal -->
            <div class="px-6 py-4 border-b flex items-center gap-3" style="border-color:hsl(220,20%,92%);background:hsl(220,20%,98%);">
                <svg class="h-5 w-5" style="color:hsl(220,54%,20%);" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" />
                    <polyline points="14 2 14 8 20 8" />
                </svg>
                <div class="flex-1">
                    <h3 class="font-semibold" style="color:hsl(220,54%,15%);">Detail Bukti Pembayaran</h3>
                    <p class="text-xs mt-0.5" style="color:hsl(220,15%,55%);">
                        <span x-text="selected?.no_pendaftaran"></span>
                        &bull;
                        <span x-text="selected?.nama_tampil ?? selected?.nama_calon"></span>
                    </p>
                </div>
                <button @click="detailOpen = false" class="p-1.5 rounded-lg hover:bg-gray-100 transition" style="color:hsl(220,15%,55%);">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <line x1="18" y1="6" x2="6" y2="18" />
                        <line x1="6" y1="6" x2="18" y2="18" />
                    </svg>
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-6">

                <!-- ── Kiri: Info ───────────────────────────────────── -->
                <div class="space-y-5">

                    <!-- DATA SISWA -->
                    <div>
                        <h4 class="text-xs font-semibold uppercase tracking-wide mb-2 flex items-center gap-1.5" style="color:hsl(220,15%,50%);">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" />
                                <circle cx="12" cy="7" r="4" />
                            </svg>
                            Data Siswa
                        </h4>
                        <div class="rounded-xl p-4 space-y-2.5" style="background:hsl(220,20%,97%);border:1px solid hsl(220,20%,92%);">
                            <div class="grid grid-cols-2 gap-1 text-sm">
                                <span style="color:hsl(220,15%,55%);">Nama</span>
                                <span class="font-semibold" style="color:hsl(220,54%,15%);" x-text="selected?.nama_tampil ?? selected?.nama_calon ?? '-'"></span>
                                <span style="color:hsl(220,15%,55%);">No. Pendaftaran</span>
                                <span class="font-mono text-xs" style="color:hsl(220,54%,15%);" x-text="selected?.no_pendaftaran ?? '-'"></span>
                                <span style="color:hsl(220,15%,55%);">Jurusan Diterima</span>
                                <span class="font-medium" style="color:hsl(220,54%,15%);" x-text="(selected?.jurusan_kode ? '['+selected.jurusan_kode+'] ' : '') + (selected?.jurusan_nama ?? '-')"></span>
                                <span style="color:hsl(220,15%,55%);">Email</span>
                                <span class="text-xs" style="color:hsl(220,54%,15%);" x-text="selected?.email_calon ?? '-'"></span>
                            </div>
                        </div>
                    </div>

                    <!-- INFO PEMBAYARAN -->
                    <div>
                        <h4 class="text-xs font-semibold uppercase tracking-wide mb-2 flex items-center gap-1.5" style="color:hsl(220,15%,50%);">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <rect x="2" y="5" width="20" height="14" rx="2" />
                                <line x1="2" y1="10" x2="22" y2="10" />
                            </svg>
                            Info Pembayaran
                        </h4>
                        <div class="rounded-xl p-4 space-y-2.5" style="background:hsl(220,20%,97%);border:1px solid hsl(220,20%,92%);">
                            <div class="grid grid-cols-2 gap-1 text-sm">
                                <span style="color:hsl(220,15%,55%);">Nominal</span>
                                <span class="font-bold" style="color:hsl(220,54%,15%);">
                                    Rp <span x-text="selected?.nominal_pembayaran ? parseInt(selected.nominal_pembayaran).toLocaleString('id-ID') : '0'"></span>
                                </span>
                                <span style="color:hsl(220,15%,55%);">Tgl Upload</span>
                                <span class="text-xs" style="color:hsl(220,54%,15%);" x-text="selected?.created_at ?? '-'"></span>
                                <span style="color:hsl(220,15%,55%);">File</span>
                                <span class="text-xs font-mono" style="color:hsl(220,54%,15%);" x-text="selected?.nama_file_bukti ?? 'file.jpg'"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Catatan siswa -->
                    <template x-if="selected?.catatan_siswa">
                        <div class="flex items-start gap-3 p-4 rounded-xl"
                            style="background:hsl(199,89%,48%,.06);border:1px solid hsl(199,89%,48%,.25);">
                            <svg class="w-4 h-4 flex-shrink-0 mt-0.5" style="color:hsl(199,60%,38%);" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <circle cx="12" cy="12" r="10" />
                                <line x1="12" y1="8" x2="12" y2="12" />
                                <line x1="12" y1="16" x2="12.01" y2="16" />
                            </svg>
                            <div>
                                <p class="text-xs font-semibold mb-0.5" style="color:hsl(199,60%,32%);">Catatan Siswa</p>
                                <p class="text-sm" style="color:hsl(199,60%,28%);" x-text="selected?.catatan_siswa"></p>
                            </div>
                        </div>
                    </template>

                    <!-- Banner dikonfirmasi -->
                    <template x-if="selected?.status === 'dikonfirmasi'">
                        <div class="p-4 rounded-xl" style="background:hsl(142,71%,45%,.06);border:1px solid hsl(142,71%,45%,.3);">
                            <p class="text-xs font-semibold mb-2 flex items-center gap-1.5" style="color:hsl(142,55%,28%);">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                    <path d="M22 11.08V12a10 10 0 11-5.93-9.14" />
                                    <polyline points="22 4 12 14.01 9 11.01" />
                                </svg>
                                Pembayaran Terverifikasi
                            </p>
                            <div class="grid grid-cols-2 gap-2 text-sm">
                                <template x-if="selected?.nama_kelas">
                                    <span style="color:hsl(142,55%,40%);">Kelas</span>
                                </template>
                                <template x-if="selected?.nama_kelas">
                                    <span class="font-semibold" style="color:hsl(142,55%,28%);" x-text="selected?.nama_kelas"></span>
                                </template>
                                <template x-if="!selected?.nama_kelas">
                                    <span class="col-span-2 text-xs italic" style="color:hsl(142,55%,45%);">Kelas belum ditetapkan. NIS akan digenerate otomatis saat konversi ke Buku Induk.</span>
                                </template>
                            </div>
                        </div>
                    </template>

                    <!-- Banner ditolak -->
                    <template x-if="selected?.status === 'ditolak' && selected?.catatan_admin">
                        <div class="p-4 rounded-xl" style="background:hsl(0,72%,51%,.06);border:1px solid hsl(0,72%,51%,.3);">
                            <p class="text-xs font-semibold mb-1 flex items-center gap-1.5" style="color:hsl(0,55%,40%);">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <circle cx="12" cy="12" r="10" />
                                    <line x1="15" y1="9" x2="9" y2="15" />
                                    <line x1="9" y1="9" x2="15" y2="15" />
                                </svg>
                                Alasan Penolakan
                            </p>
                            <p class="text-sm" style="color:hsl(0,55%,38%);" x-text="selected?.catatan_admin"></p>
                        </div>
                    </template>

                    <!-- Tombol aksi jika pending -->
                    <template x-if="selected?.status === 'pending'">
                        <div class="flex gap-2 pt-1">
                            <button @click="detailOpen = false; openApprove(selected)"
                                class="flex-1 py-2.5 text-sm font-semibold text-white rounded-xl transition flex items-center justify-center gap-2"
                                style="background:hsl(142,60%,35%);"
                                onmouseover="this.style.background='hsl(142,55%,28%)'"
                                onmouseout="this.style.background='hsl(142,60%,35%)'">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                    <path d="M22 11.08V12a10 10 0 11-5.93-9.14" />
                                    <polyline points="22 4 12 14.01 9 11.01" />
                                </svg>
                                Validasi
                            </button>
                            <button @click="detailOpen = false; openReject(selected)"
                                class="flex-1 py-2.5 text-sm font-semibold text-white rounded-xl transition flex items-center justify-center gap-2"
                                style="background:hsl(0,65%,45%);"
                                onmouseover="this.style.background='hsl(0,55%,38%)'"
                                onmouseout="this.style.background='hsl(0,65%,45%)'">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                    <circle cx="12" cy="12" r="10" />
                                    <line x1="15" y1="9" x2="9" y2="15" />
                                    <line x1="9" y1="9" x2="15" y2="15" />
                                </svg>
                                Tolak
                            </button>
                        </div>
                    </template>
                </div>

                <!-- ── Kanan: Preview Bukti ─────────────────────────── -->
                <div class="space-y-3">
                    <h4 class="text-xs font-semibold uppercase tracking-wide flex items-center gap-1.5" style="color:hsl(220,15%,50%);">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                            <circle cx="12" cy="12" r="3" />
                        </svg>
                        Preview Bukti
                    </h4>

                    <!-- Viewer container — height 400px sesuai mockup -->
                    <div class="relative rounded-xl overflow-hidden flex items-center justify-center"
                        style="height:400px;background:hsl(220,20%,95%);border:1px solid hsl(220,20%,88%);">

                        <!-- PDF viewer -->
                        <template x-if="selected?.bukti_pembayaran_path && isPdf(selected?.nama_file_bukti)">
                            <iframe :src="'<?= base_url('admin/daftar-ulang/') ?>' + selected?.id + '/bukti'"
                                class="w-full h-full border-0" title="Preview PDF"></iframe>
                        </template>

                        <!-- Image viewer dengan zoom -->
                        <template x-if="selected?.bukti_pembayaran_path && !isPdf(selected?.nama_file_bukti)">
                            <img :src="'<?= base_url('admin/daftar-ulang/') ?>' + selected?.id + '/bukti'"
                                alt="Bukti Pembayaran"
                                :style="'transform:scale(' + (zoom/100) + ');transform-origin:center;transition:transform .2s;max-width:100%;max-height:100%;'"
                                class="object-contain">
                        </template>

                        <!-- No file state -->
                        <template x-if="!selected?.bukti_pembayaran_path">
                            <div class="text-center p-8">
                                <svg class="w-12 h-12 mx-auto mb-3" style="color:hsl(220,20%,75%);" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                                    <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" />
                                    <polyline points="14 2 14 8 20 8" />
                                </svg>
                                <p class="text-sm" style="color:hsl(220,15%,55%);">Belum ada file bukti</p>
                            </div>
                        </template>

                        <!-- Zoom controls + Download (sesuai mockup) — hanya untuk image -->
                        <template x-if="selected?.bukti_pembayaran_path && !isPdf(selected?.nama_file_bukti)">
                            <div class="absolute bottom-2 right-2 flex items-center gap-1 rounded-xl p-1"
                                style="background:white;border:1px solid hsl(220,20%,88%);box-shadow:0 2px 8px hsl(220 54% 20%/0.12);">
                                <!-- Zoom Out -->
                                <button @click="zoom = Math.max(50, zoom - 25)"
                                    class="w-7 h-7 flex items-center justify-center rounded-lg hover:bg-gray-100 transition"
                                    style="color:hsl(220,54%,15%);" title="Zoom out">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <circle cx="11" cy="11" r="8" />
                                        <line x1="21" y1="21" x2="16.65" y2="16.65" />
                                        <line x1="8" y1="11" x2="14" y2="11" />
                                    </svg>
                                </button>
                                <span class="text-xs w-10 text-center font-medium" style="color:hsl(220,54%,15%);" x-text="zoom + '%'"></span>
                                <!-- Zoom In -->
                                <button @click="zoom = Math.min(200, zoom + 25)"
                                    class="w-7 h-7 flex items-center justify-center rounded-lg hover:bg-gray-100 transition"
                                    style="color:hsl(220,54%,15%);" title="Zoom in">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <circle cx="11" cy="11" r="8" />
                                        <line x1="21" y1="21" x2="16.65" y2="16.65" />
                                        <line x1="11" y1="8" x2="11" y2="14" />
                                        <line x1="8" y1="11" x2="14" y2="11" />
                                    </svg>
                                </button>
                                <!-- Download button (sesuai mockup React Download icon) -->
                                <a :href="'<?= base_url('admin/daftar-ulang/') ?>' + selected?.id + '/bukti'"
                                    :download="selected?.nama_file_bukti ?? 'bukti-pembayaran'"
                                    class="w-7 h-7 flex items-center justify-center rounded-lg hover:bg-gray-100 transition"
                                    style="color:hsl(220,54%,15%);" title="Download">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4" />
                                        <polyline points="7 10 12 15 17 10" />
                                        <line x1="12" y1="15" x2="12" y2="3" />
                                    </svg>
                                </a>
                            </div>
                        </template>

                        <!-- PDF: Download button di pojok -->
                        <template x-if="selected?.bukti_pembayaran_path && isPdf(selected?.nama_file_bukti)">
                            <div class="absolute bottom-2 right-2">
                                <a :href="'<?= base_url('admin/daftar-ulang/') ?>' + selected?.id + '/bukti'"
                                    target="_blank"
                                    class="flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium transition"
                                    style="background:white;border:1px solid hsl(220,20%,88%);color:hsl(220,54%,15%);box-shadow:0 2px 4px hsl(220 54% 20%/0.1);"
                                    onmouseover="this.style.background='hsl(220,20%,95%)'"
                                    onmouseout="this.style.background='white'"
                                    title="Buka PDF">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6" />
                                        <polyline points="15 3 21 3 21 9" />
                                        <line x1="10" y1="14" x2="21" y2="3" />
                                    </svg>
                                    Buka Tab Baru
                                </a>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- ══════════════════════════════════════════════════════════════════
         MODAL: VALIDASI BUKTI PEMBAYARAN
         — NIS dihapus dari flow ini (digenerate otomatis saat konversi ke Buku Induk)
         — Penempatan Kelas: dropdown dari kelas pilihan 1 & 2 calon siswa
    ══════════════════════════════════════════════════════════════════ -->
    <div x-show="approveOpen"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-end sm:items-center justify-center sm:p-4"
        style="background:rgba(17,24,39,.5);"
        @click.self="approveOpen = false"
        x-cloak>

        <div x-show="approveOpen"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
            class="bg-white rounded-t-2xl sm:rounded-2xl shadow-2xl w-full sm:max-w-md max-h-[90vh] overflow-y-auto">

            <div class="px-6 pt-6 pb-4">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center mb-4"
                    style="background:hsl(142,71%,45%,.12);">
                    <svg class="w-6 h-6" style="color:hsl(142,60%,35%);" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path d="M22 11.08V12a10 10 0 11-5.93-9.14" />
                        <polyline points="22 4 12 14.01 9 11.01" />
                    </svg>
                </div>
                <h3 class="text-base font-bold" style="color:hsl(220,54%,15%);">Validasi Bukti Pembayaran</h3>
                <p class="text-sm mt-1" style="color:hsl(220,15%,50%);">
                    Konfirmasi pembayaran untuk
                    <strong x-text="selected?.nama_tampil ?? selected?.nama_calon" style="color:hsl(220,54%,15%);"></strong>.
                    NIS akan digenerate otomatis saat Admin TU mengkonversi ke Buku Induk.
                </p>
            </div>

            <form :action="'<?= base_url('admin/daftar-ulang/') ?>' + selected?.id + '/konfirmasi'"
                method="POST" class="px-6 pb-6 space-y-4">
                <?= csrf_field() ?>

                <!-- Penempatan Kelas (opsional) — dropdown dari pilihan 1 & 2 -->
                <div class="space-y-1.5">
                    <label class="block text-sm font-medium" style="color:hsl(220,54%,15%);">
                        Penempatan Kelas
                        <span class="text-xs font-normal ml-1" style="color:hsl(220,15%,55%);">(opsional)</span>
                    </label>

                    <!-- Dropdown diisi secara dinamis via Alpine berdasarkan pilihan jurusan calon siswa -->
                    <select name="kelas_id" x-model="approveKelasId"
                        @change="approveKelasNama = $event.target.options[$event.target.selectedIndex].text === '— Tidak ada kelas tersedia —' || $event.target.value === '' ? '' : $event.target.options[$event.target.selectedIndex].text"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:border-transparent"
                        style="--tw-ring-color:hsl(220,54%,20%,.15);">
                        <option value="">— Belum ditetapkan —</option>
                        <template x-for="opt in approveKelasOptions" :key="opt.id">
                            <option :value="opt.id" x-text="opt.label"></option>
                        </template>
                    </select>
                    <!-- Hidden field nama_kelas untuk dikirim ke controller -->
                    <input type="hidden" name="nama_kelas" :value="approveKelasNama">

                    <!-- FIX #2: Info jurusan DITERIMA (bukan pilihan1/2) -->
                    <p class="text-xs" style="color:hsl(220,15%,55%);">
                        Kelas ditampilkan berdasarkan <strong>jurusan yang diterima</strong>:
                        <span class="font-semibold" style="color:hsl(220,54%,20%);"
                            x-text="(selected?.jurusan_kode ? '[' + selected.jurusan_kode + '] ' : '') + (selected?.jurusan_nama ?? '-')">
                        </span>
                    </p>

                    <!-- Warning jika tidak ada kelas tersedia -->
                    <template x-if="approveKelasOptions.length === 0">
                        <p class="text-xs px-3 py-2 rounded-lg" style="background:hsl(38,92%,50%,.1);color:hsl(38,60%,32%);border:1px solid hsl(38,92%,50%,.25);">
                            Belum ada kelas aktif untuk jurusan pilihan calon siswa ini. Kelas dapat ditetapkan nanti saat konversi ke Buku Induk.
                        </p>
                    </template>
                </div>

                <!-- Catatan Admin -->
                <div class="space-y-1.5">
                    <label class="block text-sm font-medium" style="color:hsl(220,54%,15%);">
                        Catatan untuk Siswa
                        <span class="text-xs font-normal ml-1" style="color:hsl(220,15%,55%);">(opsional)</span>
                    </label>
                    <textarea name="catatan_admin" rows="2"
                        placeholder="Misal: Selamat! Pembayaran terverifikasi. Harap hadir pada hari orientasi..."
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm resize-none focus:outline-none focus:ring-2 focus:border-transparent"
                        style="--tw-ring-color:hsl(220,54%,20%,.15);"></textarea>
                </div>

                <!-- Info notif -->
                <div class="flex items-start gap-2 p-3 rounded-xl text-xs"
                    style="background:hsl(199,89%,48%,.06);border:1px solid hsl(199,89%,48%,.25);color:hsl(199,60%,35%);">
                    <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <circle cx="12" cy="12" r="10" />
                        <line x1="12" y1="8" x2="12" y2="12" />
                        <line x1="12" y1="16" x2="12.01" y2="16" />
                    </svg>
                    <span>Notifikasi konfirmasi dan informasi kelas (jika dipilih) akan dikirim otomatis ke siswa. NIS akan diberikan setelah konversi ke Buku Induk.</span>
                </div>

                <div class="flex gap-3 pt-1">
                    <button type="button" @click="approveOpen = false"
                        class="flex-1 py-2.5 text-sm font-semibold rounded-xl border transition"
                        style="border-color:hsl(220,20%,82%);color:hsl(220,54%,20%);background:white;"
                        onmouseover="this.style.background='hsl(220,20%,96%)'"
                        onmouseout="this.style.background='white'">
                        Batal
                    </button>
                    <button type="submit"
                        class="flex-1 py-2.5 text-sm font-semibold text-white rounded-xl transition flex items-center justify-center gap-2"
                        style="background:hsl(142,60%,35%);"
                        onmouseover="this.style.background='hsl(142,55%,28%)'"
                        onmouseout="this.style.background='hsl(142,60%,35%)'">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path d="M22 11.08V12a10 10 0 11-5.93-9.14" />
                            <polyline points="22 4 12 14.01 9 11.01" />
                        </svg>
                        Validasi &amp; Kirim Notifikasi
                    </button>
                </div>
            </form>
        </div>
    </div>


    <!-- ══════════════════════════════════════════════════════════════════
         MODAL: TOLAK BUKTI PEMBAYARAN
    ══════════════════════════════════════════════════════════════════ -->
    <div x-show="rejectOpen"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-end sm:items-center justify-center sm:p-4"
        style="background:rgba(17,24,39,.5);"
        @click.self="rejectOpen = false"
        x-cloak>

        <div x-show="rejectOpen"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
            class="bg-white rounded-t-2xl sm:rounded-2xl shadow-2xl w-full sm:max-w-md max-h-[90vh] overflow-y-auto">

            <div class="px-6 pt-6 pb-4">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center mb-4"
                    style="background:hsl(0,72%,51%,.1);">
                    <svg class="w-6 h-6" style="color:hsl(0,55%,40%);" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <circle cx="12" cy="12" r="10" />
                        <line x1="15" y1="9" x2="9" y2="15" />
                        <line x1="9" y1="9" x2="15" y2="15" />
                    </svg>
                </div>
                <h3 class="text-base font-bold" style="color:hsl(220,54%,15%);">Tolak Bukti Pembayaran</h3>
                <p class="text-sm mt-1" style="color:hsl(220,15%,50%);">
                    Berikan alasan penolakan untuk
                    <strong x-text="selected?.nama_tampil ?? selected?.nama_calon" style="color:hsl(220,54%,15%);"></strong>.
                    Siswa akan diminta upload ulang bukti pembayaran.
                </p>
            </div>

            <form :action="'<?= base_url('admin/daftar-ulang/') ?>' + selected?.id + '/tolak'"
                method="POST" class="px-6 pb-6 space-y-4">
                <?= csrf_field() ?>

                <div class="space-y-1.5">
                    <label class="block text-sm font-medium" style="color:hsl(220,54%,15%);">
                        Alasan Penolakan <span style="color:hsl(0,72%,51%);">*</span>
                    </label>
                    <textarea name="catatan_admin" rows="4" required
                        placeholder="Contoh: Bukti transfer tidak jelas, nominal tidak sesuai, foto terpotong, dll..."
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl text-sm resize-none focus:outline-none focus:ring-2 focus:border-transparent"
                        style="--tw-ring-color:hsl(0,72%,51%,.15);"></textarea>
                    <p class="text-xs" style="color:hsl(220,15%,55%);">Pesan ini akan dikirimkan langsung ke siswa.</p>
                </div>

                <div class="flex gap-3 pt-1">
                    <button type="button" @click="rejectOpen = false"
                        class="flex-1 py-2.5 text-sm font-semibold rounded-xl border transition"
                        style="border-color:hsl(220,20%,82%);color:hsl(220,54%,20%);background:white;"
                        onmouseover="this.style.background='hsl(220,20%,96%)'"
                        onmouseout="this.style.background='white'">
                        Batal
                    </button>
                    <button type="submit"
                        class="flex-1 py-2.5 text-sm font-semibold text-white rounded-xl transition flex items-center justify-center gap-2"
                        style="background:hsl(0,65%,45%);"
                        onmouseover="this.style.background='hsl(0,55%,38%)'"
                        onmouseout="this.style.background='hsl(0,65%,45%)'">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <line x1="18" y1="6" x2="6" y2="18" />
                            <line x1="6" y1="6" x2="18" y2="18" />
                        </svg>
                        Tolak &amp; Kirim Notifikasi
                    </button>
                </div>
            </form>
        </div>
    </div>

</div><!-- /x-data -->

<script>
    // Data kelas aktif per jurusan_id, di-inject dari PHP
    const kelasByJurusan = <?= json_encode($kelasByJurusan ?? [], JSON_UNESCAPED_UNICODE) ?>;

    function daftarUlangAdmin() {
        return {
            // State modal
            detailOpen: false,
            approveOpen: false,
            rejectOpen: false,

            // Data item yang sedang dipilih
            selected: null,

            // Form approve — NIS dihapus, kelas pakai dropdown
            approveKelasId: '',
            approveKelasNama: '',
            approveKelasOptions: [], // [{id, label}] — diisi dari pilihan 1 & 2 calon siswa

            // Zoom viewer
            zoom: 100,

            init() {
                // No special init needed
            },

            openDetail(item) {
                this.selected = item;
                this.zoom = 100;
                this.detailOpen = true;
            },

            openApprove(item) {
                this.selected = item;
                this.approveKelasId = '';
                this.approveKelasNama = '';
                this.approveKelasOptions = this.buildKelasOptions(item);
                this.approveOpen = true;
            },

            openReject(item) {
                this.selected = item;
                this.rejectOpen = true;
            },

            /**
             * FIX #2: Bangun daftar opsi kelas HANYA dari jurusan_diterima_id.
             * Sebelumnya salah menggunakan pilihan1 + pilihan2. Admin hanya boleh
             * menetapkan kelas dari jurusan yang sudah ditetapkan sebagai jurusan diterima.
             */
            buildKelasOptions(item) {
                const options = [];

                // Gunakan jurusan_diterima_id — bukan pilihan1 / pilihan2
                const jurusanId = item.jurusan_diterima_id;
                const jurusanKode = item.jurusan_kode;

                if (!jurusanId) return options;

                const kelas = kelasByJurusan[jurusanId] ?? [];
                kelas.forEach(k => {
                    const prefix = jurusanKode ? `[${jurusanKode}] ` : '';
                    options.push({
                        id: k.id,
                        label: `${prefix}${k.nama}`
                    });
                });

                return options;
            },

            isPdf(namaFile) {
                if (!namaFile) return false;
                return namaFile.toLowerCase().endsWith('.pdf');
            },
        };
    }
</script>