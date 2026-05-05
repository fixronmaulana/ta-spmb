<?php
$dokumenMap  = [];
foreach ($dokumens as $dok) {
    $dokumenMap[$dok->jenis_dokumen] = $dok;
}
$jenisSemua   = $jenisDokumenSemua ?? [];
$wajibList    = $jenisDokumenWajib ?? [];
$totalWajib   = count($wajibList);
$totalUpload  = count(array_filter($dokumenMap, fn($d) => in_array($d->jenis_dokumen, $wajibList)));
$pct          = $totalWajib > 0 ? round($totalUpload / $totalWajib * 100) : 0;
$semuaLengkap = ($totalUpload >= $totalWajib);
?>

<?php
$flashError   = session()->getFlashdata('error');
$flashSuccess = session()->getFlashdata('success');
?>

<?php if ($flashError): ?>
    <div class="max-w-3xl mx-auto mb-4 flex items-start gap-3 px-4 py-3 rounded-xl text-sm font-medium"
        style="background:hsl(0,72%,51%,0.08);border:1px solid hsl(0,72%,51%,0.25);color:hsl(0,55%,38%);">
        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="10" />
            <line x1="12" y1="8" x2="12" y2="12" />
            <line x1="12" y1="16" x2="12.01" y2="16" />
        </svg>
        <span><?= esc($flashError) ?></span>
    </div>
<?php endif; ?>

<div class="max-w-3xl mx-auto space-y-5" x-data="uploadDokumen()" x-init="init()">

    <!-- HEADER -->
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold font-serif" style="color:hsl(220,54%,15%);">Formulir Pendaftaran</h1>
            <p class="text-sm mt-0.5" style="color:hsl(220,15%,50%);">SPMB SMK Al-Munawwir IIBS <?= esc($pendaftaran->periode_nama ?? '2026/2027') ?></p>
        </div>

        <!-- Indikator draft real-time -->
        <div class="flex items-center text-sm flex-shrink-0" style="min-height:24px;">
            <span x-show="saveStatus === 'saving'" x-transition
                class="flex items-center gap-1.5" style="color:hsl(220,15%,50%);">
                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.4 0 0 5.4 0 12h4z" />
                </svg>
                Menyimpan…
            </span>
            <span x-show="saveStatus === 'saved'" x-transition
                class="flex items-center gap-1.5" style="color:hsl(220,15%,50%);">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10" />
                    <polyline points="12 6 12 12 16 14" />
                </svg>
                Tersimpan: <span class="font-medium ml-0.5" style="color:hsl(220,54%,20%);" x-text="lastActivity"></span>
            </span>
            <span x-show="saveStatus === 'error'" x-transition
                class="flex items-center gap-1.5" style="color:hsl(0,60%,50%);">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10" />
                    <line x1="12" y1="8" x2="12" y2="12" />
                    <line x1="12" y1="16" x2="12.01" y2="16" />
                </svg>
                Gagal menyimpan — coba lagi
            </span>
        </div>
    </div>

    <!-- STEP PROGRESS -->
    <?= view('App\Views\Layouts\Components\step_progress', ['currentStep' => $currentStep, 'steps' => $steps]) ?>

    <!-- CARD -->
    <div class="bg-white rounded-2xl border overflow-hidden"
        style="border-color:hsl(220,20%,88%);box-shadow:0 4px 6px -1px hsl(220 54% 20%/0.07),0 2px 4px -2px hsl(220 54% 20%/0.05);">

        <div class="px-6 py-5 border-b flex items-center gap-3"
            style="border-color:hsl(220,20%,92%);background:hsl(220,20%,98%);">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0"
                style="background:hsl(38,92%,50%,0.1);">
                <svg class="w-5 h-5" style="color:hsl(38,68%,38%);" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <polyline points="16 16 12 12 8 16" />
                    <line x1="12" y1="12" x2="12" y2="21" />
                    <path d="M20.39 18.39A5 5 0 0018 9h-1.26A8 8 0 103 16.3" />
                </svg>
            </div>
            <div>
                <h2 class="text-base font-bold font-serif" style="color:hsl(220,54%,15%);">Upload Dokumen Persyaratan</h2>
                <p class="text-xs mt-0.5" style="color:hsl(220,15%,50%);">Format: PDF, JPG, atau PNG — Maks. 2MB per file</p>
            </div>
        </div>

        <div class="px-6 py-6 space-y-5">

            <div class="flex items-start gap-3 p-4 rounded-xl"
                style="background:hsl(38,92%,50%,0.07);border:1px solid hsl(38,92%,50%,0.18);">
                <svg class="w-5 h-5 flex-shrink-0 mt-0.5" style="color:hsl(38,68%,38%);" fill="none"
                    stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                    <line x1="12" y1="9" x2="12" y2="13" />
                    <line x1="12" y1="17" x2="12.01" y2="17" />
                </svg>
                <div>
                    <p class="text-sm font-semibold mb-0.5" style="color:hsl(38,58%,30%);">Dokumen wajib diupload:</p>
                    <p class="text-xs" style="color:hsl(38,50%,40%);">
                        Ijazah / SKHU, Akta Kelahiran, Kartu Keluarga, KTP Orang Tua, Rapor (Sem 1–5), Pas Foto 3×4
                    </p>
                </div>
            </div>

            <!-- Progress Bar — dikendalikan sepenuhnya oleh Alpine via x-bind -->
            <div class="p-4 rounded-xl" style="background:hsl(220,20%,97%);border:1px solid hsl(220,20%,91%);">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-sm font-semibold" style="color:hsl(220,54%,15%);">Progres Upload Dokumen</span>
                    <!-- FIX: Gunakan x-text Alpine agar reaktif, bukan innerHTML statis -->
                    <span class="text-sm font-bold" id="upload-count"
                        style="color:hsl(220,54%,20%);"
                        x-text="`${uploadedWajib}/${totalWajib} dokumen wajib`">
                        <?= $totalUpload ?>/<?= $totalWajib ?> dokumen wajib
                    </span>
                </div>
                <div class="w-full rounded-full h-2" style="background:hsl(220,20%,88%);">
                    <!--
                        FIX: Gunakan :style Alpine binding agar progress bar reaktif terhadap
                        perubahan uploadedWajib tanpa manipulasi DOM manual via getElementById.
                        Sebelumnya _updateProgressBar() mengubah style lewat JS DOM langsung —
                        cara itu tetap berfungsi, tapi rentan konflik saat Alpine re-render.
                        Dengan :style binding, Alpine yang mengurus update-nya secara otomatis.
                    -->
                    <div class="h-2 rounded-full transition-all duration-500" id="upload-bar"
                        :style="`width:${Math.min(totalWajib > 0 ? Math.round(uploadedWajib / totalWajib * 100) : 0, 100)}%;background:${uploadedWajib >= totalWajib ? 'hsl(142,71%,45%)' : 'hsl(220,54%,20%)'}`"
                        style="width:<?= min($pct, 100) ?>%;background:<?= $pct >= 100 ? 'hsl(142,71%,45%)' : 'hsl(220,54%,20%)' ?>;"></div>
                </div>
            </div>

            <!-- Daftar Dokumen -->
            <div class="space-y-3" id="dokumen-list">
                <?php foreach ($jenisSemua as $jenis => $label):
                    $dok    = $dokumenMap[$jenis] ?? null;
                    $wajib  = in_array($jenis, $wajibList);
                    $status = $dok ? $dok->status_verifikasi : null;

                    $borderStyle = match ($status) {
                        'approved' => 'border-color:hsl(142,71%,45%);background:hsl(142,71%,45%,0.04);',
                        'rejected' => 'border-color:hsl(0,72%,51%);background:hsl(0,72%,51%,0.04);',
                        'pending'  => 'border-color:hsl(43,70%,47%);background:hsl(43,70%,47%,0.04);',
                        default    => $dok ? 'border-color:hsl(220,20%,82%);' : 'border-color:hsl(220,20%,88%);border-style:dashed;',
                    };
                    $iconBg = match ($status) {
                        'approved' => 'background:hsl(142,71%,45%,0.1);',
                        'rejected' => 'background:hsl(0,72%,51%,0.1);',
                        'pending'  => 'background:hsl(43,70%,47%,0.1);',
                        default    => 'background:hsl(220,20%,93%);',
                    };
                ?>
                    <div class="flex items-center gap-4 p-4 rounded-xl border-2 transition-all"
                        id="card-<?= $jenis ?>"
                        data-wajib="<?= $wajib ? 'true' : 'false' ?>"
                        data-uploaded="<?= $dok ? 'true' : 'false' ?>"
                        style="<?= $borderStyle ?>">

                        <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 card-icon"
                            style="<?= $iconBg ?>">
                            <?php if ($status === 'approved'): ?>
                                <svg class="w-5 h-5" style="color:hsl(142,60%,35%);" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <polyline points="20 6 9 17 4 12" />
                                </svg>
                            <?php elseif ($status === 'rejected'): ?>
                                <svg class="w-5 h-5" style="color:hsl(0,55%,45%);" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <line x1="18" y1="6" x2="6" y2="18" />
                                    <line x1="6" y1="6" x2="18" y2="18" />
                                </svg>
                            <?php elseif ($status === 'pending'): ?>
                                <svg class="w-5 h-5" style="color:hsl(43,60%,35%);" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="10" />
                                    <polyline points="12 6 12 12 16 14" />
                                </svg>
                            <?php else: ?>
                                <svg class="w-5 h-5" style="color:hsl(220,15%,55%);" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" />
                                    <polyline points="14 2 14 8 20 8" />
                                </svg>
                            <?php endif; ?>
                        </div>

                        <div class="flex-1 min-w-0 card-info">
                            <div class="flex items-center gap-2 mb-0.5 flex-wrap">
                                <span class="text-sm font-semibold" style="color:hsl(220,54%,15%);"><?= esc($label) ?></span>
                                <?php if ($wajib): ?>
                                    <span class="text-xs px-1.5 py-0.5 rounded font-bold"
                                        style="background:hsl(0,72%,51%,0.09);color:hsl(0,55%,43%);">Wajib</span>
                                <?php endif; ?>
                                <?php if ($status): ?>
                                    <span class="text-xs px-1.5 py-0.5 rounded font-medium card-status-badge"
                                        style="<?= match ($status) {
                                                    'approved' => 'background:hsl(142,71%,45%,0.1);color:hsl(142,60%,33%);',
                                                    'rejected' => 'background:hsl(0,72%,51%,0.1);color:hsl(0,55%,43%);',
                                                    'pending'  => 'background:hsl(43,70%,47%,0.1);color:hsl(43,58%,33%);',
                                                    default    => '',
                                                } ?>">
                                        <?= match ($status) {
                                            'approved' => '✓ Disetujui',
                                            'rejected' => '✗ Ditolak',
                                            'pending'  => '⏳ Menunggu',
                                            default    => ''
                                        } ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <?php if ($dok): ?>
                                <p class="text-xs truncate card-file-info" style="color:hsl(220,15%,55%);">
                                    <?= esc($dok->nama_file_asli) ?> &bull; <?= human_filesize($dok->ukuran_file) ?>
                                </p>
                                <?php if ($dok->catatan_verifikasi): ?>
                                    <p class="text-xs mt-0.5 font-medium" style="color:hsl(0,55%,43%);">
                                        <?= esc($dok->catatan_verifikasi) ?>
                                    </p>
                                <?php endif; ?>
                            <?php else: ?>
                                <p class="text-xs card-file-info" style="color:hsl(220,15%,62%);">Belum diupload</p>
                            <?php endif; ?>
                        </div>

                        <div class="flex items-center gap-2 flex-shrink-0 card-actions">
                            <?php if ($dok): ?>
                                <a href="<?= base_url('dashboard/dokumen/lihat/' . $dok->id) ?>" target="_blank" rel="noopener"
                                    class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium border transition"
                                    style="border-color:hsl(220,20%,82%);color:hsl(220,54%,20%);background:white;"
                                    onmouseover="this.style.background='hsl(220,20%,96%)'"
                                    onmouseout="this.style.background='white'">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                        <circle cx="12" cy="12" r="3" />
                                    </svg>
                                    Lihat
                                </a>
                            <?php endif; ?>

                            <?php if ($dok && $status !== 'approved'): ?>
                                <label class="cursor-pointer inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-white transition"
                                    style="background:hsl(220,54%,20%);"
                                    onmouseover="this.style.background='hsl(220,54%,28%)'"
                                    onmouseout="this.style.background='hsl(220,54%,20%)'">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <polyline points="1 4 1 10 7 10" />
                                        <path d="M3.51 15a9 9 0 102.13-9.36L1 10" />
                                    </svg>
                                    Ganti
                                    <input type="file" class="hidden" :disabled="uploading"
                                        data-jenis="<?= $jenis ?>" accept=".pdf,.jpg,.jpeg,.png"
                                        @change="handleUpload($event)">
                                </label>
                                <button type="button"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg transition"
                                    style="background:hsl(0,72%,51%,0.08);color:hsl(0,55%,43%);"
                                    onmouseover="this.style.background='hsl(0,72%,51%,0.15)'"
                                    onmouseout="this.style.background='hsl(0,72%,51%,0.08)'"
                                    @click="hapusDokumen(<?= $dok->id ?>, '<?= $jenis ?>')">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <polyline points="3 6 5 6 21 6" />
                                        <path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6" />
                                        <path d="M10 11v6M14 11v6" />
                                        <path d="M9 6V4h6v2" />
                                    </svg>
                                </button>
                            <?php elseif (!$dok): ?>
                                <label class="cursor-pointer inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-white transition"
                                    style="background:hsl(220,54%,20%);"
                                    onmouseover="this.style.background='hsl(220,54%,28%)'"
                                    onmouseout="this.style.background='hsl(220,54%,20%)'">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <polyline points="16 16 12 12 8 16" />
                                        <line x1="12" y1="12" x2="12" y2="21" />
                                    </svg>
                                    Upload
                                    <input type="file" class="hidden" :disabled="uploading"
                                        data-jenis="<?= $jenis ?>" accept=".pdf,.jpg,.jpeg,.png"
                                        @change="handleUpload($event)">
                                </label>
                            <?php else: ?>
                                <span class="inline-flex items-center gap-1 text-xs px-3 py-1.5 rounded-lg"
                                    style="background:hsl(142,71%,45%,0.09);color:hsl(142,60%,33%);">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                                        <path d="M7 11V7a5 5 0 0110 0v4" />
                                    </svg>
                                    Terkunci
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Indikator upload berjalan -->
            <div x-show="uploading" x-transition
                class="flex items-center gap-3 p-4 rounded-xl"
                style="background:hsl(220,54%,20%,0.05);border:1px solid hsl(220,54%,20%,0.12);">
                <div class="w-5 h-5 rounded-full border-2 flex-shrink-0 animate-spin"
                    style="border-color:hsl(220,54%,20%,0.2);border-top-color:hsl(220,54%,20%);"></div>
                <span class="text-sm" style="color:hsl(220,54%,20%);">Mengupload dokumen...</span>
            </div>

            <!-- Pesan hasil upload (toast inline) -->
            <div x-show="uploadMsg" x-transition x-text="uploadMsg"
                class="p-3 rounded-xl text-sm"
                :class="uploadSuccess ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-red-50 text-red-700 border border-red-200'">
            </div>

            <!-- Banner semua dokumen lengkap — reaktif via Alpine getter -->
            <div x-show="allComplete" x-transition
                class="flex items-start gap-3 p-4 rounded-xl"
                style="background:hsl(142,71%,45%,0.06);border:1px solid hsl(142,71%,45%,0.2);">
                <svg class="w-5 h-5 flex-shrink-0 mt-0.5" style="color:hsl(142,55%,33%);" fill="none"
                    stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M22 11.08V12a10 10 0 11-5.93-9.14" />
                    <polyline points="22 4 12 14.01 9 11.01" />
                </svg>
                <div>
                    <p class="text-sm font-bold" style="color:hsl(142,55%,28%);">Semua dokumen wajib sudah diupload!</p>
                    <p class="text-xs mt-0.5" style="color:hsl(142,45%,38%);">Klik "Kirim Formulir" untuk mengirim pendaftaran Anda.</p>
                </div>
            </div>

        </div><!-- /px-6 py-6 -->
    </div><!-- /card -->

    <!-- NAVIGATION -->
    <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-3">

        <a href="<?= base_url('dashboard/formulir/step/3') ?>"
            class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl text-sm font-medium border transition w-full sm:w-auto"
            style="border-color:hsl(220,20%,82%);color:hsl(220,54%,20%);background:white;"
            onmouseover="this.style.background='hsl(220,20%,96%)'"
            onmouseout="this.style.background='white'">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <polyline points="15 18 9 12 15 6" />
            </svg>
            Sebelumnya
        </a>

        <!-- Simpan Draft -->
        <button type="button" @click="saveDraft()"
            :disabled="saving"
            class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl text-sm font-medium border transition w-full sm:w-auto"
            :style="saving
                ? 'border-color:hsl(220,20%,88%);color:hsl(220,15%,60%);background:hsl(220,20%,96%);cursor:not-allowed;'
                : 'border-color:hsl(220,20%,82%);color:hsl(220,54%,20%);background:white;'"
            onmouseover="if(!this.disabled) this.style.background='hsl(220,20%,96%)'"
            onmouseout="if(!this.disabled) this.style.background='white'">
            <svg x-show="saving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.4 0 0 5.4 0 12h4z" />
            </svg>
            <svg x-show="!saving" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z" />
                <polyline points="17 21 17 13 7 13 7 21" />
                <polyline points="7 3 7 8 15 8" />
            </svg>
            <span x-text="saving ? 'Menyimpan…' : (saveStatus === 'saved' ? 'Draft Tersimpan ✓' : 'Simpan Draft')"></span>
        </button>

        <!-- Tombol Kirim Formulir -->
        <button type="button" @click="konfirmasiKirim()" :disabled="uploading || submitting"
            class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl text-sm font-semibold text-white transition w-full sm:w-auto"
            :style="(uploading || submitting) ? 'background:hsl(220,54%,60%);cursor:not-allowed;' : 'background:hsl(142,55%,35%);'"
            onmouseover="if(!this.disabled) this.style.background='hsl(142,55%,28%)'"
            onmouseout="if(!this.disabled) this.style.background='hsl(142,55%,35%)'">
            <svg x-show="submitting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.4 0 0 5.4 0 12h4z" />
            </svg>
            <svg x-show="!submitting" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <line x1="22" y1="2" x2="11" y2="13" />
                <polygon points="22 2 15 22 11 13 2 9 22 2" />
            </svg>
            <span x-text="submitting ? 'Mengirim...' : (uploading ? 'Menunggu upload...' : 'Kirim Formulir')"></span>
        </button>

    </div>

    <!-- Dialog Konfirmasi Kirim -->
    <div x-show="showConfirm" x-transition
        class="fixed inset-0 z-50 flex items-end sm:items-center justify-center sm:p-4"
        style="background:rgba(0,0,0,0.5);">
        <div class="bg-white rounded-t-2xl sm:rounded-2xl shadow-xl sm:max-w-md w-full p-6 space-y-4 max-h-[90vh] overflow-y-auto"
            style="border:1px solid hsl(220,20%,88%);">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                    style="background:hsl(142,71%,45%,0.1);">
                    <svg class="w-5 h-5" style="color:hsl(142,55%,35%);" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <line x1="22" y1="2" x2="11" y2="13" />
                        <polygon points="22 2 15 22 11 13 2 9 22 2" />
                    </svg>
                </div>
                <h3 class="text-base font-bold font-serif" style="color:hsl(220,54%,15%);">Kirim Formulir Pendaftaran?</h3>
            </div>
            <p class="text-sm" style="color:hsl(220,15%,40%);">
                Setelah dikirim, data tidak dapat diubah kecuali admin meminta revisi.
                Pastikan semua dokumen sudah benar sebelum mengirim.
            </p>
            <div class="flex items-center gap-3 pt-2">
                <button type="button" @click="showConfirm = false"
                    class="flex-1 px-4 py-2.5 rounded-xl text-sm font-medium border transition"
                    style="border-color:hsl(220,20%,82%);color:hsl(220,54%,20%);background:white;"
                    onmouseover="this.style.background='hsl(220,20%,96%)'"
                    onmouseout="this.style.background='white'">
                    Cek Kembali
                </button>
                <button type="button" @click="kirimFormulirFinal()"
                    :disabled="submitting"
                    class="flex-1 px-4 py-2.5 rounded-xl text-sm font-semibold text-white transition inline-flex items-center justify-center gap-2"
                    :style="submitting ? 'background:hsl(142,55%,55%);cursor:not-allowed;' : 'background:hsl(142,55%,35%);'"
                    onmouseover="if(!this.disabled) this.style.background='hsl(142,55%,28%)'"
                    onmouseout="if(!this.disabled) this.style.background='hsl(142,55%,35%)'">
                    <svg x-show="submitting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.4 0 0 5.4 0 12h4z" />
                    </svg>
                    <svg x-show="!submitting" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <line x1="22" y1="2" x2="11" y2="13" />
                        <polygon points="22 2 15 22 11 13 2 9 22 2" />
                    </svg>
                    <span x-text="submitting ? 'Mengirim…' : 'Ya, Kirim Formulir'"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- Loading overlay fullscreen saat submit -->
    <div x-show="submitting && !showConfirm" x-transition
        class="fixed inset-0 z-50 flex flex-col items-center justify-center gap-4"
        style="background:rgba(255,255,255,0.85);backdrop-filter:blur(4px);">
        <div class="w-12 h-12 rounded-full border-4 animate-spin"
            style="border-color:hsl(220,54%,20%,0.15);border-top-color:hsl(220,54%,20%);"></div>
        <p class="text-sm font-semibold" style="color:hsl(220,54%,20%);">Mengirim formulir pendaftaran…</p>
        <p class="text-xs" style="color:hsl(220,15%,50%);">Mohon tunggu, jangan tutup halaman ini.</p>
    </div>

    <!-- Toast Error (muncul di pojok kanan bawah) -->
    <div x-show="submitError" x-transition
        class="fixed bottom-6 right-6 z-50 max-w-sm w-full flex items-start gap-3 px-4 py-3 rounded-xl shadow-lg"
        style="background:hsl(0,72%,97%);border:1.5px solid hsl(0,72%,70%);color:hsl(0,55%,38%);">
        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="10" />
            <line x1="12" y1="8" x2="12" y2="12" />
            <line x1="12" y1="16" x2="12.01" y2="16" />
        </svg>
        <div class="flex-1">
            <p class="text-sm font-semibold">Gagal Mengirim Formulir</p>
            <p class="text-xs mt-0.5" x-text="submitErrorMsg"></p>
        </div>
        <button type="button" @click="submitError = false" class="flex-shrink-0 opacity-60 hover:opacity-100">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <line x1="18" y1="6" x2="6" y2="18" />
                <line x1="6" y1="6" x2="18" y2="18" />
            </svg>
        </button>
    </div>

</div><!-- /wrapper -->

<script>
    function uploadDokumen() {
        return {
            uploading: false,
            uploadMsg: '',
            uploadSuccess: false,
            showConfirm: false,
            saving: false,
            submitting: false,
            submitError: false,
            submitErrorMsg: '',
            saveStatus: '',
            lastActivity: '',

            /*
             * FIX 1 — uploadedWajib & totalWajib sebagai data reaktif Alpine
             * ────────────────────────────────────────────────────────────────
             * Sebelumnya uploadedWajib dihitung via IIFE (immediately invoked
             * function expression) saat objek dibuat — hasilnya adalah angka
             * statis yang tidak reaktif. Nilai hanya dihitung SEKALI saat
             * Alpine memuat komponen, kemudian perubahan harus di-sync manual
             * via _updateProgressBar() yang memanipulasi DOM langsung.
             *
             * Dengan menjadikannya properti Alpine biasa yang diinisialisasi
             * di init(), nilai berubah → Alpine otomatis memperbarui semua
             * binding x-text dan :style yang bergantung padanya — tanpa perlu
             * getElementById atau style manipulation manual sama sekali.
             */
            uploadedWajib: 0,
            totalWajib: <?= $totalWajib ?>,

            /*
             * FIX 2 — allComplete sebagai computed getter Alpine
             * ──────────────────────────────────────────────────
             * Getter ini dievaluasi ulang otomatis setiap kali uploadedWajib
             * berubah, sehingga banner "Semua dokumen wajib sudah diupload!"
             * muncul/hilang secara reaktif tanpa kode tambahan.
             */
            get allComplete() {
                return this.uploadedWajib >= this.totalWajib;
            },

            /*
             * FIX 3 — Hitung uploadedWajib di init() setelah DOM siap
             * ──────────────────────────────────────────────────────────
             * IIFE di deklarasi objek berjalan sebelum Alpine me-mount
             * komponen ke DOM, sehingga querySelectorAll bisa mengembalikan
             * hasil yang belum lengkap pada halaman dengan rendering lambat.
             * init() dijamin berjalan setelah x-init selesai dan DOM siap.
             */
            init() {
                // Hitung jumlah dokumen wajib yang sudah diupload dari atribut data-*
                // yang di-render server-side. Ini adalah single source of truth awal.
                this.uploadedWajib = document.querySelectorAll(
                    '#dokumen-list [data-wajib="true"][data-uploaded="true"]'
                ).length;

                <?php if (!empty($pendaftaran->updated_at)): ?>
                    const pad = n => String(n).padStart(2, '0'),
                        now = new Date();
                    this.lastActivity = `${pad(now.getHours())}.${pad(now.getMinutes())}.${pad(now.getSeconds())}`;
                    this.saveStatus = 'saved';
                <?php endif; ?>

                // Ekspos instance Alpine ke window agar dapat diakses dari
                // event listener yang dipasang secara manual (hapusDokumen, dll.)
                window.__alpineUploader = this;
            },

            _tick() {
                const now = new Date(),
                    p = n => String(n).padStart(2, '0');
                this.lastActivity = `${p(now.getHours())}.${p(now.getMinutes())}.${p(now.getSeconds())}`;
                this.saveStatus = 'saved';
            },

            /*
             * FIX 4 — _updateProgressBar() disederhanakan
             * ─────────────────────────────────────────────
             * Dengan progress bar dan teks count sudah terikat ke Alpine via
             * x-text dan :style, method ini cukup memastikan uploadedWajib
             * terupdate. Alpine otomatis mengurus re-render semua binding.
             * Manipulasi DOM manual (getElementById, style.width, dll.) dihapus
             * karena bisa konflik dengan Alpine dan menyebabkan inkonsistensi.
             */
            _updateProgressBar(delta) {
                // delta: +1 saat upload baru, -1 saat hapus
                // Clamp ke 0 agar tidak negatif, dan ke totalWajib agar tidak overflow
                this.uploadedWajib = Math.max(0, Math.min(this.totalWajib, this.uploadedWajib + delta));
            },

            async saveDraft() {
                if (this.saving) return;
                this.saving = true;
                this.saveStatus = 'saving';
                try {
                    const data = new FormData();
                    data.append('step', '4');
                    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
                    if (csrfMeta) data.append('csrf_token', csrfMeta.content);
                    const res = await fetch('<?= base_url('dashboard/formulir/autosave') ?>', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfMeta?.content ?? ''
                        },
                        body: data,
                    });
                    if (res.ok) {
                        this._tick();
                    } else {
                        this.saveStatus = 'error';
                    }
                } catch {
                    this.saveStatus = 'error';
                } finally {
                    this.saving = false;
                }
            },

            konfirmasiKirim() {
                if (this.uploading || this.submitting) return;
                this.submitError = false;
                this.showConfirm = true;
            },

            async kirimFormulirFinal() {
                if (this.submitting) return;
                this.showConfirm = false;
                this.submitting = true;
                this.submitError = false;

                try {
                    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
                    const csrfToken = csrfMeta?.content ?? '';
                    const form = new FormData();
                    form.append('csrf_token', csrfToken);

                    const res = await fetch('<?= base_url('dashboard/formulir/step/4') ?>', {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                        body: form,
                    });

                    const newCsrf = res.headers.get('X-CSRF-TOKEN');
                    if (newCsrf && csrfMeta) csrfMeta.content = newCsrf;

                    let data;
                    try {
                        data = await res.json();
                    } catch {
                        throw new Error('Respons server tidak valid. Coba lagi.');
                    }

                    if (data.success && data.redirect) {
                        window.location.href = data.redirect;
                        return;
                    } else {
                        this.submitting = false;
                        this.submitError = true;
                        this.submitErrorMsg = data.message ?? 'Terjadi kesalahan. Silakan coba lagi.';
                        setTimeout(() => {
                            this.submitError = false;
                        }, 8000);
                    }
                } catch (err) {
                    this.submitting = false;
                    this.submitError = true;
                    this.submitErrorMsg = err.message ?? 'Gagal terhubung ke server. Periksa koneksi internet Anda.';
                    setTimeout(() => {
                        this.submitError = false;
                    }, 8000);
                }
            },

            async handleUpload(event) {
                if (this.uploading) return;
                const input = event.target;
                const file = input.files[0];
                const jenis = input.dataset.jenis;
                if (!file) return;

                // Validasi sisi client sebelum kirim ke server
                const ext = file.name.split('.').pop().toLowerCase();
                if (!['pdf', 'jpg', 'jpeg', 'png'].includes(ext)) {
                    this.uploadMsg = 'Format file harus PDF, JPG, atau PNG';
                    this.uploadSuccess = false;
                    input.value = '';
                    return;
                }
                if (file.size > 2 * 1024 * 1024) {
                    this.uploadMsg = 'Ukuran file maksimal 2MB';
                    this.uploadSuccess = false;
                    input.value = '';
                    return;
                }

                this.uploading = true;
                this.uploadMsg = '';

                const form = new FormData();
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
                form.append('file', file);
                form.append('jenis_dokumen', jenis);

                try {
                    const res = await fetch('<?= base_url('dashboard/formulir/upload-dokumen') ?>', {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: form,
                    });
                    const data = await res.json();
                    this.uploadSuccess = data.success;
                    this.uploadMsg = data.message;

                    if (data.success) {
                        /*
                         * FIX 5 — Baca data-uploaded SEBELUM _updateCardAfterUpload()
                         * mengubah atribut tersebut.
                         *
                         * Bug lama: wasUp dibaca SETELAH _updateCardAfterUpload() yang
                         * mengubah card.dataset.uploaded = 'true'. Akibatnya wasUp selalu
                         * 'true' dan uploadedWajib tidak pernah bertambah untuk upload
                         * pertama kali (hanya berfungsi untuk "Ganti").
                         *
                         * Fix: simpan nilai wasUp terlebih dahulu, baru panggil
                         * _updateCardAfterUpload() untuk update tampilan card.
                         */
                        const card = document.getElementById(`card-${jenis}`);
                        const wasUploaded = card?.dataset.uploaded === 'true';
                        const isWajib = card?.dataset.wajib === 'true';

                        // Update tampilan card (mengubah data-uploaded ke 'true')
                        this._updateCardAfterUpload(jenis, data);
                        this._tick();

                        /*
                         * FIX 6 — Tambah uploadedWajib hanya jika ini UPLOAD BARU
                         * (bukan mengganti file yang sudah ada)
                         *
                         * Gunakan _updateProgressBar(+1) yang mengubah uploadedWajib
                         * sebagai state Alpine — Alpine otomatis memperbarui:
                         *   • x-text di elemen #upload-count
                         *   • :style di elemen #upload-bar (width & warna)
                         *   • x-show="allComplete" di banner sukses
                         */
                        if (isWajib && !wasUploaded) {
                            this._updateProgressBar(+1);
                        }
                    }
                } catch {
                    this.uploadMsg = 'Gagal mengupload file. Coba lagi.';
                    this.uploadSuccess = false;
                } finally {
                    this.uploading = false;
                    input.value = '';
                }
            },

            _updateCardAfterUpload(jenis, data) {
                const card = document.getElementById(`card-${jenis}`);
                if (!card) return;

                // Update visual card ke state "pending" (menunggu verifikasi)
                card.style.borderStyle = 'solid';
                card.style.borderColor = 'hsl(43,70%,47%)';
                card.style.background = 'hsl(43,70%,47%,0.04)';
                card.dataset.uploaded = 'true'; // penting: diubah SETELAH wasUploaded dicatat di handleUpload

                const iconEl = card.querySelector('.card-icon');
                if (iconEl) {
                    iconEl.style.background = 'hsl(43,70%,47%,0.1)';
                    iconEl.innerHTML = `<svg class="w-5 h-5" style="color:hsl(43,60%,35%);" fill="none"
                        stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>`;
                }

                const fileInfoEl = card.querySelector('.card-file-info');
                if (fileInfoEl) {
                    fileInfoEl.textContent = `${data.nama_file_asli} • ${data.ukuran}`;
                    fileInfoEl.style.color = 'hsl(220,15%,55%)';
                }

                // Tambahkan badge status "⏳ Menunggu" jika belum ada
                const infoRow = card.querySelector('.flex.items-center.gap-2');
                if (infoRow) {
                    const existingBadge = infoRow.querySelector('.card-status-badge');
                    if (existingBadge) {
                        // Update badge yang sudah ada (kasus "Ganti" file)
                        existingBadge.style.cssText = 'background:hsl(43,70%,47%,0.1);color:hsl(43,58%,33%);';
                        existingBadge.textContent = '⏳ Menunggu';
                    } else {
                        // Buat badge baru (kasus upload pertama)
                        const badge = document.createElement('span');
                        badge.className = 'text-xs px-1.5 py-0.5 rounded font-medium card-status-badge';
                        badge.style.cssText = 'background:hsl(43,70%,47%,0.1);color:hsl(43,58%,33%);';
                        badge.textContent = '⏳ Menunggu';
                        infoRow.appendChild(badge);
                    }
                }

                // Render ulang tombol aksi: Lihat + Ganti + Hapus
                const actionsEl = card.querySelector('.card-actions');
                if (actionsEl) {
                    const lihatUrl = `<?= base_url('dashboard/dokumen/lihat/') ?>${data.id}`;
                    actionsEl.innerHTML = `
                    <a href="${lihatUrl}" target="_blank" rel="noopener"
                       class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium border transition"
                       style="border-color:hsl(220,20%,82%);color:hsl(220,54%,20%);background:white;"
                       onmouseover="this.style.background='hsl(220,20%,96%)'"
                       onmouseout="this.style.background='white'">
                       <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                           <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                           <circle cx="12" cy="12" r="3"/>
                       </svg>
                       Lihat
                    </a>
                    <label class="cursor-pointer inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg
                                  text-xs font-semibold text-white transition"
                           style="background:hsl(220,54%,20%);"
                           onmouseover="this.style.background='hsl(220,54%,28%)'"
                           onmouseout="this.style.background='hsl(220,54%,20%)'">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <polyline points="1 4 1 10 7 10"/>
                            <path d="M3.51 15a9 9 0 102.13-9.36L1 10"/>
                        </svg>
                        Ganti
                        <input type="file" class="hidden" data-jenis="${jenis}" accept=".pdf,.jpg,.jpeg,.png">
                    </label>
                    <button type="button"
                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg transition"
                            style="background:hsl(0,72%,51%,0.08);color:hsl(0,55%,43%);"
                            onmouseover="this.style.background='hsl(0,72%,51%,0.15)'"
                            onmouseout="this.style.background='hsl(0,72%,51%,0.08)'"
                            onclick="window.__alpineUploader.hapusDokumen(${data.id}, '${jenis}')">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <polyline points="3 6 5 6 21 6"/>
                            <path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/>
                            <path d="M10 11v6M14 11v6"/>
                            <path d="M9 6V4h6v2"/>
                        </svg>
                    </button>`;

                    // Re-attach event listener Alpine pada input file baru
                    const newInput = actionsEl.querySelector('input[type="file"]');
                    if (newInput) {
                        newInput.addEventListener('change', (e) => this.handleUpload(e));
                    }
                }
            },

            async hapusDokumen(id, jenis) {
                if (!confirm('Hapus dokumen ini?')) return;
                const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
                try {
                    const res = await fetch(`<?= base_url('dashboard/formulir/hapus-dokumen/') ?>${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrf
                        },
                    });
                    const data = await res.json();
                    if (data.success) {
                        const card = document.getElementById(`card-${jenis}`);
                        if (card) {
                            const isWajib = card.dataset.wajib === 'true';

                            // Reset card ke state kosong
                            card.dataset.uploaded = 'false';
                            card.style.borderStyle = 'dashed';
                            card.style.borderColor = 'hsl(220,20%,88%)';
                            card.style.background = '';

                            const iconEl = card.querySelector('.card-icon');
                            if (iconEl) {
                                iconEl.style.background = 'hsl(220,20%,93%)';
                                iconEl.innerHTML = `<svg class="w-5 h-5" style="color:hsl(220,15%,55%);" fill="none"
                                    stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                                    <polyline points="14 2 14 8 20 8"/></svg>`;
                            }

                            const fileInfoEl = card.querySelector('.card-file-info');
                            if (fileInfoEl) {
                                fileInfoEl.textContent = 'Belum diupload';
                                fileInfoEl.style.color = 'hsl(220,15%,62%)';
                            }

                            // Hapus badge status
                            const badge = card.querySelector('.card-status-badge');
                            if (badge) badge.remove();

                            // Reset tombol aksi ke "Upload" saja
                            const actionsEl = card.querySelector('.card-actions');
                            if (actionsEl) {
                                actionsEl.innerHTML = `
                                <label class="cursor-pointer inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg
                                              text-xs font-semibold text-white transition"
                                       style="background:hsl(220,54%,20%);"
                                       onmouseover="this.style.background='hsl(220,54%,28%)'"
                                       onmouseout="this.style.background='hsl(220,54%,20%)'">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <polyline points="16 16 12 12 8 16"/>
                                        <line x1="12" y1="12" x2="12" y2="21"/>
                                    </svg>
                                    Upload
                                    <input type="file" class="hidden" data-jenis="${jenis}" accept=".pdf,.jpg,.jpeg,.png">
                                </label>`;

                                const newInput = actionsEl.querySelector('input[type="file"]');
                                if (newInput) {
                                    newInput.addEventListener('change', (e) => this.handleUpload(e));
                                }
                            }

                            /*
                             * FIX 7 — Kurangi uploadedWajib via _updateProgressBar(-1)
                             * ──────────────────────────────────────────────────────────
                             * Sebelumnya: this.uploadedWajib-- lalu _updateProgressBar()
                             * yang memanipulasi DOM manual.
                             *
                             * Sekarang: cukup panggil _updateProgressBar(-1), Alpine
                             * otomatis memperbarui semua binding yang bergantung pada
                             * uploadedWajib (teks count, lebar bar, warna bar, banner).
                             */
                            if (isWajib) {
                                this._updateProgressBar(-1);
                            }
                        }
                    } else {
                        alert(data.message);
                    }
                } catch {
                    alert('Gagal menghapus dokumen');
                }
            },
        };
    }
</script>