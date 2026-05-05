<?php

/** @var object $pendaftaran */
/** @var object|null $dataDiri */
/** @var array $jurusans */
/** @var array $steps */
/** @var int $currentStep */

$d   = $dataDiri;
$p   = $pendaftaran;
$err = session()->getFlashdata('errors') ?? [];
?>

<div class="max-w-3xl mx-auto space-y-5" x-data="formStep2()" x-init="init()">

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
                Tersimpan: <span class="font-medium ml-0.5" style="color:hsl(220,54%,20%);" x-text="lastSaved"></span>
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
                style="background:hsl(160,60%,40%,0.1);">
                <svg class="w-5 h-5" style="color:hsl(160,55%,33%);" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <path d="M22 10v6M2 10l10-5 10 5-10 5z" />
                    <path d="M6 12v5c3 3 9 3 12 0v-5" />
                </svg>
            </div>
            <div>
                <h2 class="text-base font-bold font-serif" style="color:hsl(220,54%,15%);">B. JURUSAN YANG DIPILIH</h2>
                <p class="text-xs mt-0.5" style="color:hsl(220,15%,50%);">Pilih program keahlian yang Anda minati</p>
            </div>
        </div>

        <form id="step2-form" action="<?= base_url('dashboard/formulir/step/2') ?>" method="POST"
            class="px-6 py-6 space-y-6">
            <?= csrf_field() ?>

            <div class="flex items-start gap-3 p-4 rounded-xl"
                style="background:hsl(220,54%,20%,0.05);border:1px solid hsl(220,54%,20%,0.12);">
                <svg class="w-5 h-5 flex-shrink-0 mt-0.5" style="color:hsl(220,54%,20%);"
                    fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10" />
                    <line x1="12" y1="8" x2="12" y2="12" />
                    <line x1="12" y1="16" x2="12.01" y2="16" />
                </svg>
                <p class="text-sm" style="color:hsl(220,54%,20%);">
                    Pilih <strong>2 program keahlian</strong> yang Anda minati. Jika kuota pilihan pertama penuh,
                    Anda akan dipertimbangkan untuk pilihan kedua.
                </p>
            </div>

            <?php if (isset($err['jurusan_pilihan1_id'])): ?>
                <div class="flex items-center gap-2 p-3 rounded-xl"
                    style="background:hsl(0,72%,51%,0.08);border:1px solid hsl(0,72%,51%,0.2);">
                    <svg class="w-4 h-4 flex-shrink-0" style="color:hsl(0,55%,45%);" fill="none"
                        stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10" />
                        <line x1="12" y1="8" x2="12" y2="12" />
                    </svg>
                    <p class="text-sm" style="color:hsl(0,55%,45%);"><?= esc($err['jurusan_pilihan1_id']) ?></p>
                </div>
            <?php endif; ?>

            <!-- Jurusan Cards -->
            <div>
                <p class="text-sm font-semibold mb-3" style="color:hsl(220,54%,15%);">
                    Pilihan Pertama <span style="color:hsl(0,72%,51%);">*</span>
                    <span class="text-xs font-normal ml-1" style="color:hsl(220,15%,55%);">— pilih satu jurusan</span>
                </p>
                <div class="space-y-3" id="jurusan-cards">
                    <?php foreach ($jurusans as $idx => $j):
                        $sel1 = old('jurusan_pilihan1_id', $p->jurusan_pilihan1_id ?? '') == $j->id;
                    ?>
                        <div class="jurusan-card p-4 rounded-xl border-2 cursor-pointer transition-all select-none"
                            data-id="<?= $j->id ?>"
                            onclick="selectJurusan1(<?= $j->id ?>)"
                            style="<?= $sel1 ? 'border-color:hsl(220,54%,20%);background:hsl(220,54%,20%,0.05);' : 'border-color:hsl(220,20%,88%);' ?>">
                            <div class="flex items-center gap-4">
                                <div class="w-5 h-5 rounded flex items-center justify-center border-2 flex-shrink-0 jurusan-check transition-all"
                                    style="<?= $sel1 ? 'background:hsl(220,54%,20%);border-color:hsl(220,54%,20%);' : 'border-color:hsl(220,20%,72%);background:white;' ?>">
                                    <svg class="w-3 h-3 text-white <?= $sel1 ? '' : 'hidden' ?>"
                                        fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                        <polyline points="20 6 9 17 4 12" />
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="text-xs font-bold px-2 py-0.5 rounded"
                                            style="background:hsl(43,70%,47%,0.12);color:hsl(43,58%,33%);">
                                            <?= esc($j->kode) ?>
                                        </span>
                                        <p class="text-sm font-semibold" style="color:hsl(220,54%,15%);">
                                            <?= ($idx + 1) . '. ' . esc($j->nama) ?>
                                        </p>
                                    </div>
                                    <p class="text-xs mt-0.5" style="color:hsl(220,15%,55%);">Kuota: <?= $j->kuota ?> siswa</p>
                                </div>
                                <div class="flex-shrink-0 jurusan-badge <?= $sel1 ? '' : 'hidden' ?>">
                                    <svg class="w-5 h-5" style="color:hsl(220,54%,20%);" fill="none"
                                        stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M22 11.08V12a10 10 0 11-5.93-9.14" />
                                        <polyline points="22 4 12 14.01 9 11.01" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <input type="hidden" name="jurusan_pilihan1_id" id="pilihan1_val"
                    value="<?= old('jurusan_pilihan1_id', $p->jurusan_pilihan1_id ?? '') ?>">
            </div>

            <!-- Pilihan Kedua -->
            <div>
                <div class="flex items-center gap-3 mb-3">
                    <p class="text-sm font-semibold" style="color:hsl(220,54%,15%);">Pilihan Kedua</p>
                    <span class="text-xs" style="color:hsl(220,15%,55%);">(opsional)</span>
                    <div class="flex-1 h-px" style="background:hsl(220,20%,90%);"></div>
                </div>
                <select name="jurusan_pilihan2_id" id="pilihan2"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-base sm:text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10 bg-white transition">
                    <option value="">-- Tidak Ada Pilihan Kedua --</option>
                    <?php foreach ($jurusans as $j): ?>
                        <option value="<?= $j->id ?>"
                            <?= old('jurusan_pilihan2_id', $p->jurusan_pilihan2_id ?? '') == $j->id ? 'selected' : '' ?>>
                            <?= esc($j->nama) ?> (<?= esc($j->kode) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="text-xs mt-1.5" style="color:hsl(220,15%,55%);">Pilihan kedua tidak boleh sama dengan pilihan pertama</p>
            </div>

        </form>
    </div><!-- /card -->

    <!-- NAVIGATION -->
    <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-3">

        <a href="<?= base_url('dashboard/formulir/step/1') ?>"
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

        <button type="button" onclick="submitStep2()"
            class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl text-sm font-semibold text-white transition w-full sm:w-auto"
            style="background:hsl(220,54%,20%);"
            onmouseover="this.style.background='hsl(220,54%,28%)'"
            onmouseout="this.style.background='hsl(220,54%,20%)'">
            Simpan &amp; Lanjutkan
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <polyline points="9 18 15 12 9 6" />
            </svg>
        </button>

    </div>
</div><!-- /wrapper -->

<script>
    function selectJurusan1(id) {
        document.getElementById('pilihan1_val').value = id;
        document.querySelectorAll('.jurusan-card').forEach(card => {
            const ok = parseInt(card.dataset.id) === id;
            card.style.borderColor = ok ? 'hsl(220,54%,20%)' : 'hsl(220,20%,88%)';
            card.style.background = ok ? 'hsl(220,54%,20%,0.05)' : '';
            const chk = card.querySelector('.jurusan-check');
            chk.style.background = ok ? 'hsl(220,54%,20%)' : 'white';
            chk.style.borderColor = ok ? 'hsl(220,54%,20%)' : 'hsl(220,20%,72%)';
            chk.querySelector('svg').classList.toggle('hidden', !ok);
            card.querySelector('.jurusan-badge').classList.toggle('hidden', !ok);
        });
    }

    function submitStep2() {
        const p1 = document.getElementById('pilihan1_val').value;
        const p2 = document.getElementById('pilihan2').value;
        if (!p1) {
            alert('Pilih jurusan pilihan pertama terlebih dahulu!');
            return;
        }
        if (p2 && p1 === p2) {
            alert('Pilihan jurusan 1 dan 2 tidak boleh sama!');
            return;
        }
        document.getElementById('step2-form').submit();
    }

    // ── formStep2: autosave dengan waktu DEVICE ───────────────────────────
    function formStep2() {
        return {
            saving: false,
            saveStatus: '', // '' | 'saving' | 'saved' | 'error'
            lastSaved: '',

            init() {
                // Tampilkan jam DEVICE saat halaman dibuka jika ada data tersimpan
                <?php if (!empty($pendaftaran->updated_at)): ?>
                    const pad = n => String(n).padStart(2, '0'),
                        now = new Date();
                    this.lastSaved = `${pad(now.getHours())}.${pad(now.getMinutes())}.${pad(now.getSeconds())}`;
                    this.saveStatus = 'saved';
                <?php endif; ?>
            },

            async saveDraft() {
                // Klik tombol → simpan SEKARANG, tanpa validasi, tanpa debounce
                if (this.saving) return;
                this.saving = true;
                this.saveStatus = 'saving';
                const data = new FormData(document.getElementById('step2-form'));
                data.append('step', '2');
                try {
                    const res = await fetch('<?= base_url('dashboard/formulir/autosave') ?>', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: data,
                    });
                    if (res.ok) {
                        const now = new Date(),
                            pad = n => String(n).padStart(2, '0');
                        this.lastSaved = `${pad(now.getHours())}.${pad(now.getMinutes())}.${pad(now.getSeconds())}`;
                        this.saveStatus = 'saved';
                    } else {
                        this.saveStatus = 'error';
                    }
                } catch {
                    this.saveStatus = 'error';
                } finally {
                    this.saving = false;
                }
            }
        }
    }
</script>