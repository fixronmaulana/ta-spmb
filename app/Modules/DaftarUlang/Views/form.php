<?php

/**
 * View: App\Modules\DaftarUlang\Views\form.php
 *
 * PERBAIKAN (sesuai mockup React DaftarUlangPage.tsx):
 *  1. Tambah Alert "Selamat! Anda DITERIMA!" dengan nama jurusan & deadline
 *  2. Layout 2-kolom: kiri = Info Pembayaran (nominal, deadline, rekening),
 *     kanan = Upload Form — persis seperti mockup
 *  3. Timeline 4-step di bawah:
 *     Diterima → Upload Bukti → Verifikasi → Siswa Aktif
 *  4. FIX: 'menunggu' → 'pending' agar sesuai ENUM DB
 */

// FIX: 'menunggu' diganti 'pending' agar sesuai ENUM DB
$sudahKirim  = $existing && in_array($existing->status, ['pending', 'dikonfirmasi']);
$jurusanNama = $pendaftaran->jurusan_diterima_nama
    ?? $pendaftaran->jurusan_pilihan1_nama
    ?? '-';
$jurusanKode = $pendaftaran->jurusan_diterima_kode
    ?? $pendaftaran->jurusan_pilihan1_kode
    ?? '';

// Ambil data konfigurasi SPMB (nominal biaya, deadline)
$nominalDaftarUlang = 2500000; // default — idealnya dari master data / config
$deadlineDaftarUlang = null;
try {
    $masterModel = new \App\Modules\MasterData\Models\PeriodeModel();
    $periodeAktif = $masterModel->getPeriodeAktif();
    if ($periodeAktif) {
        $nominalDaftarUlang  = (int)($periodeAktif->biaya_daftar_ulang ?? 2500000);
        $deadlineDaftarUlang = $periodeAktif->batas_daftar_ulang ?? null;
    }
} catch (\Throwable $e) {
    // Gunakan default
}

$deadlineLabel = $deadlineDaftarUlang
    ? date('d F Y', strtotime($deadlineDaftarUlang))
    : '—';

// Status daftar ulang saat ini
$duStatus = $existing->status ?? null; // null | pending | dikonfirmasi | ditolak
?>

<div class="space-y-6 max-w-4xl mx-auto">

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

    <!-- Flash errors -->
    <?php if (session()->getFlashdata('errors')): ?>
        <div class="px-4 py-3 rounded-xl text-sm"
            style="background:hsl(0,72%,51%,.08);border:1px solid hsl(0,72%,51%,.3);color:hsl(0,55%,38%);">
            <?php foreach (session()->getFlashdata('errors') as $err): ?>
                <p>• <?= esc($err) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="px-4 py-3 rounded-xl text-sm"
            style="background:hsl(0,72%,51%,.08);border:1px solid hsl(0,72%,51%,.3);color:hsl(0,55%,38%);">
            <?= esc(session()->getFlashdata('error')) ?>
        </div>
    <?php endif; ?>

    <!-- ══ PAGE HEADER ════════════════════════════════════════════════════ -->
    <div>
        <h1 class="text-2xl font-bold font-serif" style="color:hsl(220,54%,15%);">Daftar Ulang</h1>
        <p class="text-sm mt-1" style="color:hsl(220,15%,50%);">Konfirmasi kelulusan dan upload bukti pembayaran</p>
    </div>

    <!-- ══ ALERT SELAMAT DITERIMA (sesuai mockup DaftarUlangPage.tsx) ═════ -->
    <div class="flex items-start gap-4 p-5 rounded-2xl"
        style="background:hsl(142,71%,45%,.06);border:1.5px solid hsl(142,71%,45%,.4);">
        <!-- Icon -->
        <div class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0"
            style="background:hsl(142,71%,45%,.18);">
            <svg class="w-6 h-6" style="color:hsl(142,60%,35%);" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M5.8 11.3L2 22l10.7-3.79" />
                <path d="M4 3h.01" />
                <path d="M22 8h.01" />
                <path d="M15 2h.01" />
                <path d="M22 20h.01" />
                <path d="M22 2l-2.24.75a2.9 2.9 0 00-1.96 3.12c.1.86-.57 1.63-1.45 1.63h-.38c-.86 0-1.6.6-1.76 1.44L14 10" />
                <path d="M22 13l-.82-.33c-.86-.34-1.82.2-1.98 1.11c-.11.7-.72 1.22-1.43 1.22H17" />
                <path d="M11 2l.33.82c.34.86-.2 1.82-1.11 1.98C9.52 4.9 9 5.52 9 6.23V7" />
                <path d="M11 13c1.93 1.93 2.83 4.17 2 5-.83.83-3.07-.07-5-2-1.93-1.93-2.83-4.17-2-5 .83-.83 3.07.07 5 2z" />
            </svg>
        </div>
        <div class="flex-1">
            <p class="font-bold text-base" style="color:hsl(142,55%,28%);">
                Selamat! Anda Dinyatakan <span style="text-transform:uppercase;">DITERIMA!</span> 🎉
            </p>
            <p class="text-sm mt-1" style="color:hsl(142,60%,35%);">
                Anda diterima di jurusan
                <strong><?= esc($jurusanNama) ?><?= $jurusanKode ? ' (' . esc($jurusanKode) . ')' : '' ?></strong>
            </p>
            <?php if ($deadlineDaftarUlang): ?>
                <p class="text-sm mt-1" style="color:hsl(142,60%,38%);">
                    Silakan lakukan daftar ulang sebelum
                    <strong style="color:hsl(0,72%,51%);"><?= $deadlineLabel ?></strong>
                </p>
            <?php endif; ?>
        </div>
    </div>

    <!-- ══ BANNER SUDAH DIKIRIM ═══════════════════════════════════════════ -->
    <?php if ($sudahKirim): ?>
        <div class="flex items-start gap-3 p-4 rounded-2xl"
            style="background:hsl(38,92%,50%,.1);border:1px solid hsl(38,92%,50%,.3);">
            <svg class="w-5 h-5 flex-shrink-0 mt-0.5" style="color:hsl(38,60%,38%);" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10" />
                <polyline points="12 6 12 12 16 14" />
            </svg>
            <div>
                <p class="text-sm font-semibold" style="color:hsl(38,60%,32%);">Pengajuan Sedang Diproses</p>
                <p class="text-xs mt-0.5" style="color:hsl(38,60%,38%);">
                    Anda sudah mengirim pengajuan daftar ulang dan sedang menunggu konfirmasi admin.
                    Anda masih bisa memperbarui data jika diperlukan.
                </p>
            </div>
        </div>
    <?php endif; ?>

    <!-- ══ 2-COLUMN LAYOUT: INFO PEMBAYARAN + UPLOAD FORM ════════════════ -->
    <div class="grid lg:grid-cols-2 gap-6">

        <!-- ── KIRI: Informasi Pembayaran ─────────────────────────────── -->
        <div class="bg-white rounded-2xl overflow-hidden"
            style="border:1px solid hsl(220,20%,88%);box-shadow:0 4px 6px -1px hsl(220 54% 20%/0.07);">

            <!-- Card Header -->
            <div class="px-6 py-4 border-b flex items-center gap-2"
                style="border-color:hsl(220,20%,92%);background:hsl(220,20%,98%);">
                <svg class="w-5 h-5" style="color:hsl(220,54%,20%);" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <rect x="1" y="4" width="22" height="16" rx="2" ry="2" />
                    <line x1="1" y1="10" x2="23" y2="10" />
                </svg>
                <h2 class="font-semibold" style="color:hsl(220,54%,15%);">Informasi Pembayaran</h2>
            </div>

            <div class="px-6 py-5 space-y-4">

                <!-- Nominal & Batas Waktu -->
                <div class="p-4 rounded-xl space-y-3" style="background:hsl(220,20%,97%);border:1px solid hsl(220,20%,92%);">
                    <div class="flex flex-wrap justify-between items-center gap-1">
                        <span class="text-sm" style="color:hsl(220,15%,55%);">Biaya Daftar Ulang</span>
                        <span class="font-bold text-lg" style="color:hsl(220,54%,15%);">
                            Rp <?= number_format($nominalDaftarUlang, 0, ',', '.') ?>
                        </span>
                    </div>
                    <?php if ($deadlineDaftarUlang): ?>
                        <div class="flex flex-wrap justify-between items-center pt-2 border-t gap-1" style="border-color:hsl(220,20%,90%);">
                            <span class="text-sm" style="color:hsl(220,15%,55%);">Batas Waktu</span>
                            <span class="font-semibold text-sm" style="color:hsl(0,72%,51%);"><?= $deadlineLabel ?></span>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Info Rekening Bank -->
                <div class="p-4 rounded-xl"
                    style="background:hsl(199,89%,48%,.06);border:1px solid hsl(199,89%,48%,.25);">
                    <p class="text-xs font-semibold mb-2 flex items-center gap-1.5" style="color:hsl(199,60%,32%);">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10" />
                            <line x1="12" y1="8" x2="12" y2="12" />
                            <line x1="12" y1="16" x2="12.01" y2="16" />
                        </svg>
                        Metode Pembayaran
                    </p>
                    <ul class="text-sm space-y-1" style="color:hsl(199,60%,30%);">
                        <li>• Transfer Bank BRI: <strong class="font-mono">0123-4567-8901-2345</strong></li>
                        <li>• a.n. <strong>SMK Al-Munawwir IIBS</strong></li>
                        <li>• Atau bayar langsung di Tata Usaha sekolah</li>
                    </ul>
                </div>

                <!-- Peringatan penting -->
                <div class="p-4 rounded-xl"
                    style="background:hsl(38,92%,50%,.08);border:1px solid hsl(38,92%,50%,.25);">
                    <p class="text-xs font-semibold flex items-center gap-1.5" style="color:hsl(38,60%,32%);">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                            <line x1="12" y1="9" x2="12" y2="13" />
                            <line x1="12" y1="17" x2="12.01" y2="17" />
                        </svg>
                        Penting!
                    </p>
                    <p class="text-xs mt-1" style="color:hsl(38,55%,35%);">
                        Cantumkan <strong>Nama Lengkap</strong> dan <strong>No. Pendaftaran</strong> pada berita transfer.
                    </p>
                </div>

            </div>
        </div>

        <!-- ── KANAN: Upload Bukti Pembayaran ─────────────────────────── -->
        <div class="bg-white rounded-2xl overflow-hidden"
            style="border:1px solid hsl(220,20%,88%);box-shadow:0 4px 6px -1px hsl(220 54% 20%/0.07);">

            <!-- Card Header -->
            <div class="px-6 py-4 border-b flex items-center gap-2"
                style="border-color:hsl(220,20%,92%);background:hsl(220,20%,98%);">
                <svg class="w-5 h-5" style="color:hsl(220,54%,20%);" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4" />
                    <polyline points="17 8 12 3 7 8" />
                    <line x1="12" y1="3" x2="12" y2="15" />
                </svg>
                <h2 class="font-semibold" style="color:hsl(220,54%,15%);">Upload Bukti Pembayaran</h2>
            </div>

            <form action="<?= base_url('dashboard/daftar-ulang') ?>" method="POST" enctype="multipart/form-data"
                class="px-6 py-5 space-y-4" x-data="daftarUlangForm()">
                <?= csrf_field() ?>

                <!-- Error AJAX (Alpine) -->
                <div x-show="errorMsg" x-cloak
                    class="px-4 py-3 rounded-xl text-sm"
                    style="background:hsl(0,72%,51%,.08);border:1px solid hsl(0,72%,51%,.3);color:hsl(0,55%,38%);"
                    x-text="errorMsg"></div>

                <!-- Pilih Kelas (opsional) -->
                <?php if (! empty($kelasList)): ?>
                    <div>
                        <label class="block text-sm font-medium mb-1.5" style="color:hsl(220,54%,15%);">
                            Pilih Kelas
                            <span class="text-xs font-normal ml-1" style="color:hsl(220,15%,55%);">(opsional)</span>
                        </label>
                        <select name="kelas_id"
                            class="w-full px-4 py-2.5 border rounded-xl text-sm transition focus:outline-none"
                            style="border-color:hsl(220,20%,85%);color:hsl(220,54%,15%);">
                            <option value="">-- Pilih kelas --</option>
                            <?php foreach ($kelasList as $kelas): ?>
                                <option value="<?= $kelas->id ?>"
                                    <?= ($existing->kelas_id ?? '') == $kelas->id ? 'selected' : '' ?>>
                                    <?= esc($kelas->nama) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>

                <!-- Upload Area — sesuai mockup -->
                <div class="space-y-1.5">
                    <label class="block text-sm font-medium" style="color:hsl(220,54%,15%);">
                        Bukti Transfer/Pembayaran
                        <span style="color:hsl(0,72%,51%);">*</span>
                    </label>

                    <!-- Drop Zone -->
                    <label for="bukti_pembayaran"
                        class="flex flex-col items-center justify-center gap-2 border-2 border-dashed rounded-xl p-8 cursor-pointer transition"
                        style="border-color:hsl(220,20%,82%);min-height:10rem;"
                        @mouseenter="this.style.borderColor='hsl(220,54%,20%)';this.style.background='hsl(220,54%,20%,.03)'"
                        @mouseleave="this.style.borderColor='hsl(220,20%,82%)';this.style.background='transparent'">

                        <!-- Belum ada file -->
                        <template x-if="!fileName">
                            <div class="text-center">
                                <svg class="w-10 h-10 mx-auto mb-2" style="color:hsl(220,20%,75%);" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4" />
                                    <polyline points="17 8 12 3 7 8" />
                                    <line x1="12" y1="3" x2="12" y2="15" />
                                </svg>
                                <p class="text-sm font-medium" style="color:hsl(220,54%,15%);">
                                    <span class="font-semibold">Klik untuk upload</span> atau drag &amp; drop
                                </p>
                                <p class="text-xs mt-1" style="color:hsl(220,15%,55%);">PDF, JPG, atau PNG (Maks. 2MB)</p>
                            </div>
                        </template>

                        <!-- File sudah dipilih -->
                        <template x-if="fileName">
                            <div class="flex items-center gap-3 w-full">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                                    style="background:hsl(142,71%,45%,.15);">
                                    <svg class="w-5 h-5" style="color:hsl(142,60%,35%);" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" />
                                        <polyline points="14 2 14 8 20 8" />
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium truncate" style="color:hsl(220,54%,15%);" x-text="fileName"></p>
                                    <p class="text-xs mt-0.5" style="color:hsl(142,60%,38%);">File siap diupload ✓</p>
                                </div>
                                <button type="button"
                                    @click.stop="fileName = ''; document.getElementById('bukti_pembayaran').value = ''"
                                    class="flex-shrink-0 w-7 h-7 flex items-center justify-center rounded-lg transition"
                                    style="color:hsl(0,55%,45%);background:hsl(0,72%,51%,.08);"
                                    onmouseover="this.style.background='hsl(0,72%,51%,.18)'"
                                    onmouseout="this.style.background='hsl(0,72%,51%,.08)'">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <line x1="18" y1="6" x2="6" y2="18" />
                                        <line x1="6" y1="6" x2="18" y2="18" />
                                    </svg>
                                </button>
                            </div>
                        </template>

                        <input id="bukti_pembayaran" type="file" name="bukti_pembayaran"
                            accept=".pdf,.jpg,.jpeg,.png" class="hidden"
                            @change="onFileChange($event)">
                    </label>

                    <!-- Info bukti sebelumnya -->
                    <?php if ($existing && $existing->bukti_pembayaran_path): ?>
                        <div class="flex items-center gap-2 text-xs" style="color:hsl(142,60%,38%);">
                            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M22 11.08V12a10 10 0 11-5.93-9.14" />
                                <polyline points="22 4 12 14.01 9 11.01" />
                            </svg>
                            Bukti sebelumnya sudah tersimpan
                            <?php if ($existing->nama_file_bukti): ?>
                                (<?= esc($existing->nama_file_bukti) ?>)
                            <?php endif; ?>
                            — upload baru hanya jika ingin mengganti
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Nominal Pembayaran -->
                <div>
                    <label class="block text-sm font-medium mb-1.5" style="color:hsl(220,54%,15%);">
                        Nominal Pembayaran <span style="color:hsl(0,72%,51%);">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-sm font-medium pointer-events-none"
                            style="color:hsl(220,15%,55%);">Rp</span>
                        <input type="text" name="nominal_pembayaran" required
                            placeholder="0"
                            value="<?= esc(number_format((int)($existing->nominal_pembayaran ?? 0), 0, ',', '.')) ?>"
                            @input="formatNominal($event)"
                            class="w-full pl-10 pr-4 py-2.5 border rounded-xl text-sm transition focus:outline-none"
                            style="border-color:hsl(220,20%,85%);color:hsl(220,54%,15%);"
                            @focus="this.style.borderColor='hsl(220,54%,20%)';this.style.boxShadow='0 0 0 3px hsl(220 54% 20%/.1)'"
                            @blur="this.style.borderColor='hsl(220,20%,85%)';this.style.boxShadow='none'">
                    </div>
                    <p class="text-xs mt-1" style="color:hsl(220,15%,58%);">Masukkan sesuai nominal tagihan yang ditetapkan panitia</p>
                </div>

                <!-- Catatan Siswa -->
                <div>
                    <label class="block text-sm font-medium mb-1.5" style="color:hsl(220,54%,15%);">
                        Catatan (Opsional)
                    </label>
                    <textarea name="catatan_siswa" rows="3"
                        placeholder="Tambahkan catatan jika diperlukan (misal: nama pengirim transfer, metode bayar, dll)..."
                        class="w-full px-4 py-2.5 border rounded-xl text-sm transition focus:outline-none resize-none"
                        style="border-color:hsl(220,20%,85%);color:hsl(220,54%,15%);"
                        @focus="this.style.borderColor='hsl(220,54%,20%)';this.style.boxShadow='0 0 0 3px hsl(220 54% 20%/.1)'"
                        @blur="this.style.borderColor='hsl(220,20%,85%)';this.style.boxShadow='none'"><?= esc($existing->catatan_siswa ?? '') ?></textarea>
                </div>

                <!-- Submit Button — full width, sesuai mockup -->
                <button type="button"
                    :disabled="submitting"
                    @click="handleSubmit($el.closest('form'))"
                    class="w-full py-3 text-sm font-semibold text-white rounded-xl transition flex items-center justify-center gap-2"
                    style="background:hsl(220,54%,20%);"
                    onmouseover="if(!this.disabled) this.style.background='hsl(220,54%,28%)'"
                    onmouseout="this.style.background='hsl(220,54%,20%)'">
                    <!-- Spinner saat submit -->
                    <svg x-show="submitting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.4 0 0 5.4 0 12h4z" />
                    </svg>
                    <!-- Icon Centang saat idle -->
                    <svg x-show="!submitting" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path d="M22 11.08V12a10 10 0 11-5.93-9.14" />
                        <polyline points="22 4 12 14.01 9 11.01" />
                    </svg>
                    <span x-text="submitting ? 'Mengupload...' : 'Kirim Bukti Pembayaran'"></span>
                </button>

                <p class="text-xs text-center" style="color:hsl(220,15%,55%);">
                    Bukti pembayaran akan diverifikasi oleh Admin TU dalam 1×24 jam kerja.
                </p>
            </form>
        </div>
    </div>

    <!-- ══ TIMELINE STATUS DAFTAR ULANG (4-step sesuai mockup) ═══════════ -->
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
        // Tentukan step mana yang aktif berdasarkan status
        // Step: 1=Diterima, 2=Upload Bukti, 3=Verifikasi, 4=Siswa Aktif
        $stepState = 2; // Default: step upload aktif (belum kirim)
        if ($duStatus === 'pending')       $stepState = 3; // sudah upload, menunggu verifikasi
        if ($duStatus === 'dikonfirmasi')  $stepState = 5; // semua selesai
        if ($duStatus === 'ditolak')       $stepState = 2; // kembali ke upload

        $steps = [
            ['num' => 1, 'label' => 'Diterima',    'desc' => 'Status kelulusan Anda telah ditetapkan'],
            ['num' => 2, 'label' => 'Upload Bukti', 'desc' => 'Upload bukti pembayaran daftar ulang'],
            ['num' => 3, 'label' => 'Verifikasi',   'desc' => 'Admin memverifikasi pembayaran Anda'],
            ['num' => 4, 'label' => 'Siswa Aktif',  'desc' => 'Anda resmi menjadi siswa SMK Al-Munawwir'],
        ];
        ?>

        <div class="relative">
            <!-- Garis penghubung -->
            <div class="absolute top-5 left-0 right-0 h-0.5" style="background:hsl(220,20%,88%);margin:0 2.5rem;"></div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-2 relative">
                <?php foreach ($steps as $step):
                    $num = $step['num'];
                    $isDone    = $num < $stepState;
                    $isCurrent = $num === $stepState;
                    $isPending = $num > $stepState;

                    if ($duStatus === 'ditolak' && $num === 2) {
                        // Step upload: ditolak = merah, perlu re-upload
                        $dotStyle  = 'background:hsl(0,72%,51%);border:2px solid hsl(0,72%,51%);';
                        $textColor = 'hsl(0,55%,40%)';
                        $badgeVariant = 'warning';
                        $badgeLabel = 'Ditolak';
                        $badgeBg   = 'hsl(0,72%,51%,.12)';
                        $badgeText = 'hsl(0,55%,40%)';
                        $icon = '<svg class="w-4 h-4 text-white" fill="none" stroke="white" stroke-width="2.5" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>';
                    } elseif ($isDone) {
                        $dotStyle  = 'background:hsl(142,71%,45%);border:2px solid hsl(142,71%,45%);';
                        $textColor = 'hsl(220,54%,15%)';
                        $badgeBg   = 'hsl(142,71%,45%,.12)';
                        $badgeText = 'hsl(142,55%,28%)';
                        $badgeLabel = 'Selesai';
                        $icon = '<svg class="w-4 h-4" fill="none" stroke="white" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>';
                    } elseif ($isCurrent) {
                        if ($duStatus === 'pending' && $num === 3) {
                            $dotStyle  = 'background:hsl(38,92%,50%);border:2px solid hsl(38,92%,50%);';
                            $textColor = 'hsl(220,54%,15%)';
                            $badgeBg   = 'hsl(38,92%,50%,.15)';
                            $badgeText = 'hsl(38,60%,32%)';
                            $badgeLabel = 'Menunggu';
                        } else {
                            $dotStyle  = 'background:hsl(38,92%,50%);border:2px solid hsl(38,92%,50%);';
                            $textColor = 'hsl(220,54%,15%)';
                            $badgeBg   = 'hsl(38,92%,50%,.15)';
                            $badgeText = 'hsl(38,60%,32%)';
                            $badgeLabel = 'Menunggu';
                        }
                        $icon = '<span class="w-2.5 h-2.5 rounded-full inline-block animate-pulse" style="background:white;"></span>';
                    } else {
                        $dotStyle  = 'background:hsl(220,20%,88%);border:2px solid hsl(220,20%,82%);';
                        $textColor = 'hsl(220,15%,65%)';
                        $badgeBg   = 'hsl(220,20%,92%)';
                        $badgeText = 'hsl(220,15%,55%)';
                        $badgeLabel = 'Belum';
                        $icon = '';
                    }
                ?>
                    <div class="flex flex-col items-center gap-2">
                        <!-- Dot -->
                        <div class="w-10 h-10 rounded-full flex items-center justify-center z-10"
                            style="<?= $dotStyle ?>">
                            <?= $icon ?>
                        </div>
                        <!-- Label -->
                        <div class="text-center">
                            <p class="font-medium text-xs" style="color:<?= $textColor ?>;"><?= $step['label'] ?></p>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold mt-1"
                                style="background:<?= $badgeBg ?>;color:<?= $badgeText ?>;">
                                <?= $badgeLabel ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

</div><!-- /wrapper -->

<script>
    function daftarUlangForm() {
        return {
            nominal: '',
            fileName: '',
            submitting: false,
            errorMsg: '',

            formatNominal(e) {
                let val = e.target.value.replace(/\D/g, '');
                e.target.value = val ? parseInt(val, 10).toLocaleString('id-ID') : '';
                this.nominal = e.target.value;
            },

            onFileChange(e) {
                const file = e.target.files[0];
                if (!file) { this.fileName = ''; return; }
                const allowed = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
                if (!allowed.includes(file.type)) {
                    this.errorMsg = 'Format file tidak didukung. Hanya PDF, JPG, atau PNG.';
                    e.target.value = ''; this.fileName = ''; return;
                }
                if (file.size > 2 * 1024 * 1024) {
                    this.errorMsg = 'Ukuran file terlalu besar. Maksimal 2MB.';
                    e.target.value = ''; this.fileName = ''; return;
                }
                this.errorMsg = '';
                this.fileName = file.name;
            },

            /**
             * Submit via fetch() agar:
             * 1. Spinner bisa di-reset jika terjadi error server (termasuk 500)
             * 2. Redirect dikontrol JS setelah dapat respon JSON sukses
             * 3. Toast notifikasi bisa ditampilkan tanpa full page reload
             */
            async handleSubmit(form) {
                if (this.submitting) return;
                this.errorMsg = '';
                this.submitting = true;

                try {
                    const formData = new FormData(form);

                    const res = await fetch(form.action, {
                        method: 'POST',
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                        body: formData,
                    });

                    // Coba parse JSON
                    let json = null;
                    const ct = res.headers.get('content-type') || '';
                    if (ct.includes('application/json')) {
                        json = await res.json();
                    } else {
                        // Server mengembalikan HTML (error 500 dsb) — ambil teks untuk log
                        const text = await res.text();
                        console.error('Server returned non-JSON:', res.status, text.substring(0, 300));
                        throw new Error('Terjadi kesalahan server (status ' + res.status + '). Coba lagi atau hubungi admin.');
                    }

                    if (json.success) {
                        // Tampilkan toast sukses, lalu redirect
                        this._showToast(json.message || 'Berhasil!', 'success');
                        setTimeout(() => {
                            window.location.href = json.redirect || '<?= base_url('dashboard/daftar-ulang/status') ?>';
                        }, 1200);
                        // Biarkan submitting = true selama redirect berlangsung
                    } else {
                        // Validasi / error dari server
                        this.errorMsg = json.message || 'Terjadi kesalahan. Coba lagi.';
                        this.submitting = false;
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                        if (json.redirect) {
                            setTimeout(() => window.location.href = json.redirect, 1500);
                        }
                    }

                } catch (err) {
                    console.error('Submit error:', err);
                    this.errorMsg = err.message || 'Gagal menghubungi server. Periksa koneksi internet Anda.';
                    this.submitting = false;
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            },

            _showToast(message, type = 'success') {
                const toast = document.createElement('div');
                const bg = type === 'success' ? 'hsl(142,71%,45%)' : 'hsl(0,72%,51%)';
                toast.style.cssText = [
                    'position:fixed', 'bottom:24px', 'right:24px', 'z-index:9999',
                    'display:flex', 'align-items:center', 'gap:10px',
                    'padding:14px 20px', 'border-radius:14px',
                    'background:' + bg,
                    'color:white', 'font-size:14px', 'font-weight:500',
                    'box-shadow:0 8px 24px rgba(0,0,0,.18)',
                    'transform:translateY(80px)', 'opacity:0',
                    'transition:all .35s cubic-bezier(.34,1.56,.64,1)',
                    'max-width:360px',
                ].join(';');
                toast.innerHTML = '<svg width="20" height="20" fill="none" stroke="white" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg><span>' + message + '</span>';
                document.body.appendChild(toast);
                requestAnimationFrame(() => {
                    toast.style.transform = 'translateY(0)';
                    toast.style.opacity   = '1';
                });
                setTimeout(() => {
                    toast.style.transform = 'translateY(80px)';
                    toast.style.opacity   = '0';
                    setTimeout(() => toast.remove(), 400);
                }, 4000);
            },
        };
    }
</script>