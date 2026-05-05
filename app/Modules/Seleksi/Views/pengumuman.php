<?php

/**
 * View: App\Modules\Seleksi\Views\pengumuman.php
 *
 * Variabel dari PengumumanController::index():
 *   $pendaftaran  — object|null  (data pendaftaran user yg login, sudah getWithRelations)
 *   $periodeAktif — object|null  (periode aktif dari PeriodeModel)
 *   $isPublished  — bool         (apakah pengumuman sudah dipublikasikan)
 */

$p = $pendaftaran;

// Tanggal pengumuman & daftar ulang dari periodeAktif
$tglPengumuman    = $periodeAktif->tanggal_pengumuman ?? null;
$tglDuMulai       = $periodeAktif->tanggal_daftar_ulang_mulai ?? null;
$tglDuSelesai     = $periodeAktif->tanggal_daftar_ulang_selesai ?? null;
$tahunAjaran      = $periodeAktif->tahun_ajaran ?? '2026/2027';

// Apakah user yg login sudah diterima?
$userDiterima = $p && $p->status === 'lulus';
?>

<div class="space-y-6 max-w-4xl mx-auto">

    <!-- ══ PAGE HEADER ══════════════════════════════════════════════════ -->
    <div class="text-center space-y-1">
        <h1 class="text-3xl font-bold font-serif" style="color:hsl(220,54%,15%);">
            Pengumuman Hasil Seleksi
        </h1>
        <p class="text-sm" style="color:hsl(220,15%,50%);">
            SPMB SMK Al-Munawwir IIBS Tahun Ajaran <?= esc($tahunAjaran) ?>
        </p>
    </div>

    <!-- ══ BANNER TANGGAL PENGUMUMAN ════════════════════════════════════ -->
    <div class="max-w-2xl mx-auto flex items-start gap-3 p-4 rounded-2xl"
        style="background:hsl(199,89%,48%,.07);border:1px solid hsl(199,89%,48%,.22);">
        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" style="color:hsl(199,89%,48%);" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
            <line x1="16" y1="2" x2="16" y2="6" />
            <line x1="8" y1="2" x2="8" y2="6" />
            <line x1="3" y1="10" x2="21" y2="10" />
        </svg>
        <div class="text-sm" style="color:hsl(220,54%,15%);">
            <p class="font-semibold mb-1">Tanggal Pengumuman</p>
            <p>
                Hasil seleksi diumumkan pada
                <strong><?= $tglPengumuman ? format_tanggal($tglPengumuman) : 'Akan segera diumumkan' ?></strong>
            </p>
            <?php if ($tglDuMulai && $tglDuSelesai): ?>
                <p class="mt-1 text-xs" style="color:hsl(220,15%,50%);">
                    Periode daftar ulang:
                    <strong><?= format_tanggal($tglDuMulai) ?> — <?= format_tanggal($tglDuSelesai) ?></strong>
                </p>
            <?php endif; ?>
        </div>
    </div>

    <!-- ══ SEARCH CARD ══════════════════════════════════════════════════ -->
    <div class="max-w-2xl mx-auto bg-white rounded-2xl overflow-hidden"
        style="border:1px solid hsl(220,20%,88%);box-shadow:0 4px 6px -1px hsl(220 54% 20%/0.07),0 2px 4px -2px hsl(220 54% 20%/0.05);"
        x-data="cekHasil()">

        <!-- Card Header -->
        <div class="px-6 py-4 border-b flex items-center gap-2"
            style="border-color:hsl(220,20%,92%);background:hsl(220,20%,98%);">
            <svg class="w-5 h-5" style="color:hsl(220,54%,20%);" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="11" cy="11" r="8" />
                <line x1="21" y1="21" x2="16.65" y2="16.65" />
            </svg>
            <h2 class="font-semibold" style="color:hsl(220,54%,15%);">Cek Hasil Seleksi</h2>
        </div>

        <div class="px-6 py-6 space-y-4">
            <!-- Input + Tombol Cari -->
            <div>
                <label class="block text-sm font-medium mb-2" style="color:hsl(220,54%,15%);">
                    Nomor Pendaftaran atau Nama
                </label>
                <div class="flex gap-2">
                    <input type="text"
                        x-model="query"
                        @keydown.enter="cari()"
                        placeholder="Contoh: SPMB-2026-001234 atau Nama Lengkap"
                        class="flex-1 px-4 py-2.5 border rounded-xl text-sm transition focus:outline-none"
                        style="border-color:hsl(220,20%,85%);color:hsl(220,54%,15%);"
                        :style="query ? '' : ''"
                        @focus="this.style.borderColor='hsl(220,54%,20%)';this.style.boxShadow='0 0 0 3px hsl(220 54% 20%/.1)'"
                        @blur="this.style.borderColor='hsl(220,20%,85%)';this.style.boxShadow='none'">

                    <button @click="cari()"
                        :disabled="isSearching || !query.trim()"
                        class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white rounded-xl transition flex-shrink-0"
                        :class="(isSearching || !query.trim()) ? 'cursor-not-allowed opacity-60' : 'cursor-pointer'"
                        style="background:hsl(220,54%,20%);"
                        @mouseenter="if(!isSearching && query.trim()) this.style.background='hsl(220,54%,28%)'"
                        @mouseleave="this.style.background='hsl(220,54%,20%)'">
                        <!-- Spinner -->
                        <svg x-show="isSearching" class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10" stroke-opacity="0.25" />
                            <path d="M12 2a10 10 0 0110 10" stroke-opacity="0.9" />
                        </svg>
                        <!-- Search Icon -->
                        <svg x-show="!isSearching" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <circle cx="11" cy="11" r="8" />
                            <line x1="21" y1="21" x2="16.65" y2="16.65" />
                        </svg>
                        <span x-text="isSearching ? 'Mencari...' : 'Cari'"></span>
                    </button>
                </div>
            </div>

            <!-- ── HASIL PENCARIAN ────────────────────────────────────── -->
            <div x-show="hasSearched" x-transition class="mt-2">

                <!-- DITERIMA -->
                <template x-if="result && result.status === 'lulus'">
                    <div class="p-6 rounded-2xl"
                        style="background:hsl(142,71%,45%,.05);border:2px solid hsl(142,71%,45%);">

                        <!-- Header Selamat -->
                        <div class="flex items-center gap-4 mb-5">
                            <div class="w-16 h-16 rounded-full flex items-center justify-center flex-shrink-0"
                                style="background:hsl(142,71%,45%,.2);">
                                <span class="text-3xl">🎉</span>
                            </div>
                            <div>
                                <h3 class="text-2xl font-bold" style="color:hsl(142,60%,30%);">SELAMAT!</h3>
                                <p class="text-sm" style="color:hsl(142,60%,38%);">Anda dinyatakan <strong>DITERIMA</strong></p>
                            </div>
                        </div>

                        <!-- Grid data -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5">
                            <div>
                                <p class="text-xs mb-0.5" style="color:hsl(220,15%,55%);">Nama</p>
                                <p class="text-sm font-semibold" style="color:hsl(220,54%,15%);" x-text="result.nama"></p>
                            </div>
                            <div>
                                <p class="text-xs mb-0.5" style="color:hsl(220,15%,55%);">No. Pendaftaran</p>
                                <p class="text-sm font-mono font-semibold" style="color:hsl(220,54%,15%);" x-text="result.no_pendaftaran"></p>
                            </div>
                            <div class="col-span-2">
                                <p class="text-xs mb-1" style="color:hsl(220,15%,55%);">Diterima di Jurusan</p>
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5" style="color:hsl(220,54%,20%);" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M22 10v6M2 10l10-5 10 5-10 5z" />
                                        <path d="M6 12v5c3 3 9 3 12 0v-5" />
                                    </svg>
                                    <span class="text-base font-bold" style="color:hsl(220,54%,15%);" x-text="result.jurusan_diterima"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Warning daftar ulang -->
                        <?php if ($tglDuSelesai): ?>
                            <div class="flex items-start gap-3 p-3 rounded-xl mb-5"
                                style="background:hsl(38,92%,50%,.1);border:1px solid hsl(38,92%,50%,.3);">
                                <svg class="w-4 h-4 flex-shrink-0 mt-0.5" style="color:hsl(38,60%,35%);" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="10" />
                                    <line x1="12" y1="8" x2="12" y2="12" />
                                    <line x1="12" y1="16" x2="12.01" y2="16" />
                                </svg>
                                <p class="text-xs" style="color:hsl(38,60%,35%);">
                                    <strong>Penting!</strong> Segera lakukan daftar ulang sebelum
                                    <strong><?= format_tanggal($tglDuSelesai) ?></strong> untuk mengamankan kursi Anda.
                                </p>
                            </div>
                        <?php endif; ?>

                        <!-- Tombol Daftar Ulang -->
                        <a href="<?= base_url('dashboard/daftar-ulang') ?>"
                            class="flex items-center justify-center gap-2 w-full py-3 text-sm font-semibold text-white rounded-xl transition"
                            style="background:hsl(220,54%,20%);"
                            onmouseover="this.style.background='hsl(220,54%,28%)'"
                            onmouseout="this.style.background='hsl(220,54%,20%)'">
                            Lanjut ke Daftar Ulang
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <line x1="5" y1="12" x2="19" y2="12" />
                                <polyline points="12 5 19 12 12 19" />
                            </svg>
                        </a>
                    </div>
                </template>

                <!-- TIDAK DITERIMA -->
                <template x-if="result && result.status === 'tidak_lulus'">
                    <div class="p-6 rounded-2xl"
                        style="background:hsl(0,72%,51%,.05);border:2px solid hsl(0,72%,51%);">

                        <div class="flex items-center gap-4 mb-5">
                            <div class="w-16 h-16 rounded-full flex items-center justify-center flex-shrink-0"
                                style="background:hsl(0,72%,51%,.15);">
                                <svg class="w-8 h-8" style="color:hsl(0,72%,51%);" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="10" />
                                    <line x1="15" y1="9" x2="9" y2="15" />
                                    <line x1="9" y1="9" x2="15" y2="15" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-2xl font-bold" style="color:hsl(0,55%,40%);">Mohon Maaf</h3>
                                <p class="text-sm" style="color:hsl(0,55%,48%);">Anda belum diterima pada gelombang ini</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                            <div>
                                <p class="text-xs mb-0.5" style="color:hsl(220,15%,55%);">Nama</p>
                                <p class="text-sm font-semibold" style="color:hsl(220,54%,15%);" x-text="result.nama"></p>
                            </div>
                            <div>
                                <p class="text-xs mb-0.5" style="color:hsl(220,15%,55%);">No. Pendaftaran</p>
                                <p class="text-sm font-mono font-semibold" style="color:hsl(220,54%,15%);" x-text="result.no_pendaftaran"></p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 p-3 rounded-xl"
                            style="background:hsl(199,89%,48%,.08);border:1px solid hsl(199,89%,48%,.25);">
                            <svg class="w-4 h-4 flex-shrink-0 mt-0.5" style="color:hsl(199,89%,48%);" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="10" />
                                <line x1="12" y1="8" x2="12" y2="12" />
                                <line x1="12" y1="16" x2="12.01" y2="16" />
                            </svg>
                            <p class="text-xs" style="color:hsl(199,60%,35%);">
                                Jangan berkecil hati! Anda masih bisa mendaftar ulang di
                                <strong>Gelombang 2</strong> yang akan segera dibuka.
                                Hubungi panitia SPMB untuk informasi lebih lanjut.
                            </p>
                        </div>
                    </div>
                </template>

                <!-- DATA TIDAK DITEMUKAN / ERROR -->
                <template x-if="!result">
                    <div class="flex items-start gap-3 p-4 rounded-xl"
                        style="background:hsl(0,72%,51%,.06);border:1px solid hsl(0,72%,51%,.25);">
                        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" style="color:hsl(0,55%,45%);" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10" />
                            <line x1="12" y1="8" x2="12" y2="12" />
                            <line x1="12" y1="16" x2="12.01" y2="16" />
                        </svg>
                        <div>
                            <p class="text-sm font-semibold mb-0.5" style="color:hsl(0,55%,40%);">Data Tidak Ditemukan</p>
                            <p class="text-xs" style="color:hsl(220,15%,50%);"
                                x-text="errorMsg || 'Nomor pendaftaran atau nama yang Anda masukkan tidak ditemukan. Pastikan data yang dimasukkan benar.'">
                            </p>
                        </div>
                    </div>
                </template>

            </div><!-- /hasil pencarian -->
        </div>
    </div>

    <!-- ══ QUICK ACCESS — User yang sudah login & diterima ══════════════ -->
    <?php if ($userDiterima): ?>
        <div class="max-w-2xl mx-auto bg-white rounded-2xl p-6"
            style="border:2px solid hsl(142,71%,45%,.4);box-shadow:0 4px 6px -1px hsl(142 71% 45%/0.1);">
            <div class="flex items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <svg class="w-8 h-8 flex-shrink-0" style="color:hsl(142,71%,45%);" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M22 11.08V12a10 10 0 11-5.93-9.14" />
                        <polyline points="22 4 12 14.01 9 11.01" />
                    </svg>
                    <div>
                        <p class="font-semibold" style="color:hsl(220,54%,15%);">
                            Selamat, <?= esc($p->nama_calon ?? session()->get('user_name')) ?>!
                        </p>
                        <p class="text-sm" style="color:hsl(142,60%,35%);">
                            Status Anda: <strong>DITERIMA</strong>
                            <?php if ($p->jurusan_diterima_nama): ?>
                                — <?= esc($p->jurusan_diterima_nama) ?>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
                <a href="<?= base_url('dashboard/daftar-ulang') ?>"
                    class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white rounded-xl transition flex-shrink-0"
                    style="background:hsl(220,54%,20%);"
                    onmouseover="this.style.background='hsl(220,54%,28%)'"
                    onmouseout="this.style.background='hsl(220,54%,20%)'">
                    Daftar Ulang
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <line x1="5" y1="12" x2="19" y2="12" />
                        <polyline points="12 5 19 12 12 19" />
                    </svg>
                </a>
            </div>
        </div>
    <?php endif; ?>

    <!-- ══ INFO CARDS 2-KOLOM ════════════════════════════════════════════ -->
    <div class="grid md:grid-cols-2 gap-4 max-w-4xl mx-auto">

        <!-- Jika Diterima -->
        <div class="bg-white rounded-2xl p-6"
            style="border:1px solid hsl(220,20%,88%);box-shadow:0 4px 6px -1px hsl(220 54% 20%/0.07),0 2px 4px -2px hsl(220 54% 20%/0.05);">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0"
                    style="background:hsl(142,71%,45%,.1);">
                    <svg class="w-6 h-6" style="color:hsl(142,71%,45%);" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M22 11.08V12a10 10 0 11-5.93-9.14" />
                        <polyline points="22 4 12 14.01 9 11.01" />
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold mb-2" style="color:hsl(220,54%,15%);">Jika DITERIMA</h3>
                    <ul class="text-sm space-y-1.5" style="color:hsl(220,15%,50%);">
                        <li class="flex items-start gap-2">
                            <span class="mt-1.5 w-1.5 h-1.5 rounded-full flex-shrink-0" style="background:hsl(142,71%,45%);"></span>
                            Lakukan daftar ulang dalam waktu yang ditentukan
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="mt-1.5 w-1.5 h-1.5 rounded-full flex-shrink-0" style="background:hsl(142,71%,45%);"></span>
                            Bayar biaya daftar ulang sesuai ketentuan
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="mt-1.5 w-1.5 h-1.5 rounded-full flex-shrink-0" style="background:hsl(142,71%,45%);"></span>
                            Upload bukti pembayaran di sistem ini
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="mt-1.5 w-1.5 h-1.5 rounded-full flex-shrink-0" style="background:hsl(142,71%,45%);"></span>
                            Tunggu verifikasi dari Admin
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Dokumen yang Diperlukan -->
        <div class="bg-white rounded-2xl p-6"
            style="border:1px solid hsl(220,20%,88%);box-shadow:0 4px 6px -1px hsl(220 54% 20%/0.07),0 2px 4px -2px hsl(220 54% 20%/0.05);">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0"
                    style="background:hsl(199,89%,48%,.1);">
                    <svg class="w-6 h-6" style="color:hsl(199,89%,48%);" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" />
                        <polyline points="14 2 14 8 20 8" />
                    </svg>
                </div>
                <div>
                    <h3 class="font-semibold mb-2" style="color:hsl(220,54%,15%);">Dokumen yang Diperlukan</h3>
                    <ul class="text-sm space-y-1.5" style="color:hsl(220,15%,50%);">
                        <li class="flex items-start gap-2">
                            <span class="mt-1.5 w-1.5 h-1.5 rounded-full flex-shrink-0" style="background:hsl(199,89%,48%);"></span>
                            Bukti pembayaran daftar ulang
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="mt-1.5 w-1.5 h-1.5 rounded-full flex-shrink-0" style="background:hsl(199,89%,48%);"></span>
                            Kartu Keluarga asli (dibawa saat registrasi)
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="mt-1.5 w-1.5 h-1.5 rounded-full flex-shrink-0" style="background:hsl(199,89%,48%);"></span>
                            Ijazah / SKL asli
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="mt-1.5 w-1.5 h-1.5 rounded-full flex-shrink-0" style="background:hsl(199,89%,48%);"></span>
                            Pas foto 3×4 (4 lembar)
                        </li>
                    </ul>
                </div>
            </div>
        </div>

    </div><!-- /info cards -->

    <!-- ══ BELUM DIPUBLIKASIKAN — overlay di atas card pencarian jika !isPublished ══ -->
    <?php if (! $isPublished): ?>
        <div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center sm:p-4" style="background:rgba(0,0,0,.4);" id="belum-published-overlay">
            <div class="bg-white rounded-t-2xl sm:rounded-2xl p-8 sm:p-10 text-center sm:max-w-sm w-full shadow-2xl max-h-[90vh] overflow-y-auto"
                style="border:1px solid hsl(220,20%,88%);">
                <div class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4"
                    style="background:hsl(38,92%,50%,.12);">
                    <svg class="w-8 h-8" style="color:hsl(38,92%,50%);" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10" />
                        <polyline points="12 6 12 12 16 14" />
                    </svg>
                </div>
                <h2 class="text-lg font-bold font-serif mb-2" style="color:hsl(220,54%,15%);">
                    Pengumuman Belum Tersedia
                </h2>
                <p class="text-sm mb-3" style="color:hsl(220,15%,50%);">
                    Hasil seleksi sedang dalam proses. Pengumuman akan segera dipublikasikan oleh panitia.
                </p>
                <?php if ($tglPengumuman): ?>
                    <p class="text-sm font-semibold" style="color:hsl(199,89%,40%);">
                        Estimasi: <?= format_tanggal($tglPengumuman) ?>
                    </p>
                <?php endif; ?>
                <a href="<?= base_url('dashboard') ?>"
                    class="mt-6 inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white rounded-xl transition"
                    style="background:hsl(220,54%,20%);"
                    onmouseover="this.style.background='hsl(220,54%,28%)'"
                    onmouseout="this.style.background='hsl(220,54%,20%)'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <polyline points="19 12 5 12" />
                        <polyline points="12 19 5 12 12 5" />
                    </svg>
                    Kembali ke Dashboard
                </a>
            </div>
        </div>
    <?php endif; ?>

</div><!-- /wrapper -->

<script>
    function cekHasil() {
        return {
            query: '',
            isSearching: false,
            hasSearched: false,
            result: null,
            errorMsg: '',

            async cari() {
                if (this.isSearching || !this.query.trim()) return;

                this.isSearching = true;
                this.hasSearched = false;
                this.result = null;
                this.errorMsg = '';

                try {
                    const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

                    // FIX: Tambah header 'X-Requested-With' agar CI4 isAJAX() = true.
                    // fetch() browser tidak mengirim header ini secara default,
                    // sehingga controller sebelumnya selalu return 400 → tidak ada hasil.
                    const res = await fetch('<?= base_url('dashboard/pengumuman/cari') ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify({
                            q: this.query.trim()
                        }),
                    });

                    // Parse JSON dari semua status code (200, 400, 422, dsb)
                    let data = null;
                    try {
                        data = await res.json();
                    } catch (_) {}

                    if (data && data.success) {
                        this.result = data.data;
                        this.errorMsg = '';
                    } else {
                        this.result = null;
                        this.errorMsg = data?.message || 'Data tidak ditemukan.';
                    }

                } catch (e) {
                    console.error('cekHasil error:', e);
                    this.result = null;
                    this.errorMsg = 'Gagal menghubungi server. Periksa koneksi internet Anda.';
                } finally {
                    this.hasSearched = true;
                    this.isSearching = false;
                }
            }
        }
    }
</script>