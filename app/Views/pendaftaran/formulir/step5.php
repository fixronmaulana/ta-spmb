<?php
$d = $dataDiri;
$p = $pendaftaran;

function rVal($v): string
{
    return $v ? esc($v) : '<span style="color:hsl(220,15%,62%);">—</span>';
}
?>

<div class="max-w-3xl mx-auto space-y-5" x-data="{ agreed: false, submitting: false }">

    <!-- ── HEADER ─────────────────────────────────────── -->
    <div class="flex items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold font-serif" style="color:hsl(220,54%,15%);">Formulir Pendaftaran</h1>
            <p class="text-sm mt-0.5" style="color:hsl(220,15%,50%);">SPMB SMK Al-Munawwir IIBS <?= esc($pendaftaran->periode_nama ?? '2026/2027') ?></p>
        </div>
    </div>

    <!-- ── STEP PROGRESS ──────────────────────────────── -->
    <?= view('App\Views\Layouts\Components\step_progress', ['currentStep' => $currentStep, 'steps' => $steps]) ?>

    <!-- ── Peringatan dokumen kurang ────────────────── -->
    <?php if (!empty($missingDocs)): ?>
        <div class="flex items-start gap-3 p-4 rounded-xl"
            style="background:hsl(0,72%,51%,0.07);border:1px solid hsl(0,72%,51%,0.18);">
            <svg class="w-5 h-5 flex-shrink-0 mt-0.5" style="color:hsl(0,55%,43%);" fill="none"
                stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                <line x1="12" y1="9" x2="12" y2="13" />
                <line x1="12" y1="17" x2="12.01" y2="17" />
            </svg>
            <div class="flex-1">
                <p class="text-sm font-semibold mb-1" style="color:hsl(0,55%,38%);">Dokumen belum diupload:</p>
                <ul class="space-y-0.5">
                    <?php foreach ($missingDocs as $m): ?>
                        <li class="flex items-center gap-1.5 text-xs" style="color:hsl(0,50%,43%);">
                            <svg class="w-3 h-3 flex-shrink-0" fill="currentColor" viewBox="0 0 8 8">
                                <circle cx="4" cy="4" r="3" />
                            </svg>
                            <?= esc(jenis_dokumen_label($m)) ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <a href="<?= base_url('dashboard/formulir/step/4') ?>"
                    class="inline-flex items-center gap-1 mt-2 text-xs font-semibold underline"
                    style="color:hsl(0,55%,38%);">
                    Upload Dokumen Sekarang →
                </a>
            </div>
        </div>
    <?php endif; ?>

    <!-- ── CARD REVIEW ────────────────────────────────── -->
    <div class="bg-white rounded-2xl border overflow-hidden"
        style="border-color:hsl(220,20%,88%);box-shadow:0 4px 6px -1px hsl(220 54% 20%/0.07),0 2px 4px -2px hsl(220 54% 20%/0.05);">

        <!-- Card Header -->
        <div class="px-6 py-5 border-b flex items-center gap-3"
            style="border-color:hsl(220,20%,92%);background:hsl(220,20%,98%);">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0"
                style="background:hsl(142,71%,45%,0.1);">
                <svg class="w-5 h-5" style="color:hsl(142,55%,33%);" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" />
                    <polyline points="14 2 14 8 20 8" />
                    <polyline points="9 15 12 18 15 15" />
                    <line x1="12" y1="12" x2="12" y2="18" />
                </svg>
            </div>
            <div>
                <h2 class="text-base font-bold font-serif" style="color:hsl(220,54%,15%);">Review &amp; Submit Formulir</h2>
                <p class="text-xs mt-0.5" style="color:hsl(220,15%,50%);">Periksa kembali semua data sebelum mengirim</p>
            </div>
        </div>

        <div class="px-6 py-6 space-y-6">

            <!-- A. Identitas Siswa -->
            <div>
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-bold flex items-center gap-2" style="color:hsl(220,54%,15%);">
                        <span class="w-5 h-5 rounded text-xs flex items-center justify-center font-bold text-white"
                            style="background:hsl(220,54%,20%);">A</span>
                        Identitas Peserta Didik
                    </h3>
                    <a href="<?= base_url('dashboard/formulir/step/1') ?>"
                        class="text-xs font-medium flex items-center gap-1 transition-opacity hover:opacity-70"
                        style="color:hsl(220,54%,28%);">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7" />
                            <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                        </svg>
                        Edit
                    </a>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                    <?php
                    $jk = ($d->jenis_kelamin ?? '') === 'L' ? 'Laki-laki' : (($d->jenis_kelamin ?? '') === 'P' ? 'Perempuan' : null);
                    $tgl = isset($d->tanggal_lahir) ? date('d/m/Y', strtotime($d->tanggal_lahir)) : '-';
                    $items = [
                        ['Nama Lengkap',     $d->nama_lengkap ?? null],
                        ['Jenis Kelamin',    $jk],
                        ['Tempat, Tgl Lahir', ($d->tempat_lahir ?? '-') . ', ' . $tgl],
                        ['Agama',            $d->agama ?? null],
                        ['NIK',              $d->nik ?? null],
                        ['NISN',             $d->nisn ?? null],
                        ['No. HP',           $d->no_hp ?? null],
                        ['Sekolah Asal',     $d->asal_sekolah ?? null],
                    ];
                    foreach ($items as [$label, $val]): ?>
                        <div class="rounded-xl p-3" style="background:hsl(220,20%,97%);border:1px solid hsl(220,20%,91%);">
                            <p class="text-xs mb-0.5" style="color:hsl(220,15%,55%);"><?= $label ?></p>
                            <p class="text-sm font-semibold truncate" style="color:hsl(220,54%,15%);"><?= rVal($val) ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="h-px" style="background:hsl(220,20%,92%);"></div>

            <!-- C. Orang Tua -->
            <div>
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-bold flex items-center gap-2" style="color:hsl(220,54%,15%);">
                        <span class="w-5 h-5 rounded text-xs flex items-center justify-center font-bold text-white"
                            style="background:hsl(43,70%,47%);">C</span>
                        Identitas Orang Tua
                    </h3>
                    <a href="<?= base_url('dashboard/formulir/step/2') ?>"
                        class="text-xs font-medium flex items-center gap-1 transition-opacity hover:opacity-70"
                        style="color:hsl(220,54%,28%);">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7" />
                            <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                        </svg>
                        Edit
                    </a>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                    <?php $items = [
                        ['Nama Ayah',      $d->nama_ayah ?? null],
                        ['Pekerjaan Ayah', $d->pekerjaan_ayah ?? null],
                        ['Nama Ibu',       $d->nama_ibu ?? null],
                        ['Pekerjaan Ibu',  $d->pekerjaan_ibu ?? null],
                        ['No. HP Wali',    $d->no_hp_ortu ?? $d->no_hp_ayah ?? null],
                    ];
                    foreach ($items as [$label, $val]): ?>
                        <div class="rounded-xl p-3" style="background:hsl(220,20%,97%);border:1px solid hsl(220,20%,91%);">
                            <p class="text-xs mb-0.5" style="color:hsl(220,15%,55%);"><?= $label ?></p>
                            <p class="text-sm font-semibold truncate" style="color:hsl(220,54%,15%);"><?= rVal($val) ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="h-px" style="background:hsl(220,20%,92%);"></div>

            <!-- B. Jurusan -->
            <div>
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-bold flex items-center gap-2" style="color:hsl(220,54%,15%);">
                        <span class="w-5 h-5 rounded text-xs flex items-center justify-center font-bold text-white"
                            style="background:hsl(160,55%,35%);">B</span>
                        Jurusan yang Dipilih
                    </h3>
                    <a href="<?= base_url('dashboard/formulir/step/3') ?>"
                        class="text-xs font-medium flex items-center gap-1 transition-opacity hover:opacity-70"
                        style="color:hsl(220,54%,28%);">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7" />
                            <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                        </svg>
                        Edit
                    </a>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                    <div class="rounded-xl p-4"
                        style="background:hsl(220,54%,20%,0.04);border:2px solid hsl(220,54%,20%,0.14);">
                        <p class="text-xs font-medium mb-1" style="color:hsl(220,54%,33%);">Pilihan Pertama</p>
                        <p class="text-sm font-bold" style="color:hsl(220,54%,15%);"><?= esc($p->jurusan_pilihan1_nama ?? '—') ?></p>
                    </div>
                    <div class="rounded-xl p-4" style="background:hsl(220,20%,97%);border:1px solid hsl(220,20%,91%);">
                        <p class="text-xs font-medium mb-1" style="color:hsl(220,15%,55%);">Pilihan Kedua</p>
                        <p class="text-sm font-semibold" style="color:hsl(220,54%,15%);"><?= esc($p->jurusan_pilihan2_nama ?? '—') ?></p>
                    </div>
                </div>
            </div>

            <div class="h-px" style="background:hsl(220,20%,92%);"></div>

            <!-- Dokumen -->
            <div>
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-bold" style="color:hsl(220,54%,15%);">Dokumen Terupload</h3>
                    <a href="<?= base_url('dashboard/formulir/step/4') ?>"
                        class="text-xs font-medium flex items-center gap-1 transition-opacity hover:opacity-70"
                        style="color:hsl(220,54%,28%);">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7" />
                            <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                        </svg>
                        Edit
                    </a>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    <?php foreach ($dokumens as $dok): ?>
                        <div class="flex items-center gap-3 p-3 rounded-xl"
                            style="background:hsl(142,71%,45%,0.04);border:1px solid hsl(142,71%,45%,0.18);">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0"
                                style="background:hsl(142,71%,45%,0.1);">
                                <svg class="w-4 h-4" style="color:hsl(142,55%,33%);" fill="none" stroke="currentColor"
                                    stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" />
                                    <polyline points="14 2 14 8 20 8" />
                                </svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-xs font-semibold truncate" style="color:hsl(220,54%,15%);"><?= esc(jenis_dokumen_label($dok->jenis_dokumen)) ?></p>
                                <p class="text-xs truncate" style="color:hsl(220,15%,55%);"><?= esc($dok->nama_file_asli) ?></p>
                            </div>
                            <svg class="w-4 h-4 flex-shrink-0" style="color:hsl(142,55%,38%);" fill="none"
                                stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <polyline points="20 6 9 17 4 12" />
                            </svg>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Banner siap submit -->
            <?php if ($canSubmit ?? false): ?>
                <div class="flex items-start gap-3 p-4 rounded-xl"
                    style="background:hsl(142,71%,45%,0.06);border:1px solid hsl(142,71%,45%,0.18);">
                    <svg class="w-5 h-5 flex-shrink-0 mt-0.5" style="color:hsl(142,55%,33%);" fill="none"
                        stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M22 11.08V12a10 10 0 11-5.93-9.14" />
                        <polyline points="22 4 12 14.01 9 11.01" />
                    </svg>
                    <div>
                        <p class="text-sm font-bold" style="color:hsl(142,55%,28%);">Semua data lengkap — siap untuk dikirim!</p>
                        <p class="text-xs mt-0.5" style="color:hsl(142,45%,35%);">Setelah dikirim, data tidak dapat diubah kecuali admin meminta revisi.</p>
                    </div>
                </div>

                <!-- Checkbox persetujuan -->
                <label class="flex items-start gap-3 cursor-pointer">
                    <div class="relative mt-0.5 flex-shrink-0">
                        <input type="checkbox" x-model="agreed" class="sr-only">
                        <div class="w-5 h-5 rounded border-2 flex items-center justify-center transition-all"
                            :style="agreed
                             ? 'background:hsl(220,54%,20%);border-color:hsl(220,54%,20%);'
                             : 'border-color:hsl(220,20%,68%);background:white;'"
                            @click="agreed = !agreed">
                            <svg class="w-3 h-3 text-white transition-opacity"
                                :class="agreed ? 'opacity-100' : 'opacity-0'"
                                fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                <polyline points="20 6 9 17 4 12" />
                            </svg>
                        </div>
                    </div>
                    <p class="text-sm leading-relaxed" style="color:hsl(220,54%,15%);">
                        Saya menyatakan bahwa semua data dan dokumen yang saya isi adalah
                        <strong>benar dan sah</strong>. Saya bersedia menerima konsekuensi
                        jika terbukti ada data yang tidak valid.
                    </p>
                </label>
            <?php endif; ?>

        </div>
    </div><!-- /card -->

    <!-- ══════════════════════════════════════════════════
         NAVIGATION — Di luar card
         ══════════════════════════════════════════════════ -->
    <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-3">

        <a href="<?= base_url('dashboard/formulir/step/4') ?>"
            class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl text-sm font-medium border transition w-full sm:w-auto"
            style="border-color:hsl(220,20%,82%);color:hsl(220,54%,20%);background:white;"
            onmouseover="this.style.background='hsl(220,20%,96%)'"
            onmouseout="this.style.background='white'">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <polyline points="15 18 9 12 15 6" />
            </svg>
            Sebelumnya
        </a>

        <?php if ($canSubmit ?? false): ?>

            <!-- Slot tengah kosong untuk menjaga posisi tombol kanan tetap di kanan -->
            <div></div>

            <!-- Tombol Submit -->
            <form action="<?= base_url('dashboard/formulir/submit') ?>" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="confirm_submit" value="1">
                <button type="submit"
                    :disabled="!agreed || submitting"
                    @click="if (agreed && !submitting) submitting = true"
                    class="inline-flex items-center gap-2 px-8 py-2.5 rounded-xl text-sm font-bold text-white
                           transition shadow-sm cursor-pointer"
                    :style="(agreed && !submitting)
                        ? 'background:hsl(142,55%,35%);'
                        : 'background:hsl(220,15%,72%);cursor:not-allowed;opacity:0.65;'">
                    <svg x-show="!submitting" class="w-4 h-4" fill="none" stroke="currentColor"
                        stroke-width="2" viewBox="0 0 24 24">
                        <line x1="22" y1="2" x2="11" y2="13" />
                        <polygon points="22 2 15 22 11 13 2 9 22 2" />
                    </svg>
                    <svg x-show="submitting" class="w-4 h-4 animate-spin" fill="none" stroke="currentColor"
                        stroke-width="2" viewBox="0 0 24 24">
                        <path d="M21 12a9 9 0 11-6.219-8.56" />
                    </svg>
                    <span x-text="submitting ? 'Mengirim...' : 'Kirim Formulir Pendaftaran'"></span>
                </button>
            </form>

        <?php else: ?>
            <!-- Jika tidak bisa submit, slot tengah + kanan kosong -->
            <div></div>
            <div></div>
        <?php endif; ?>

    </div><!-- /nav -->
</div><!-- /wrapper -->