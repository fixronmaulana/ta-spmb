<?php
$d   = $dataDiri;
$err = session()->getFlashdata('errors') ?? [];

$inpN = 'w-full px-4 py-2.5 border border-gray-300 rounded-xl text-base sm:text-sm transition focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 bg-white';
$inpE = 'w-full px-4 py-2.5 border border-red-400 rounded-xl text-sm transition focus:outline-none focus:ring-2 focus:ring-red-400/15 bg-red-50';
$sel  = 'w-full px-4 py-2.5 border border-gray-300 rounded-xl text-base sm:text-sm focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 bg-white transition';

function errF2(string $field, array $errors): string
{
    if (!isset($errors[$field])) return '';
    return '<p class="mt-1.5 flex items-center gap-1.5 text-xs" style="color:hsl(0,55%,45%);">
        <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/></svg>
        ' . esc($errors[$field]) . '</p>';
}
?>

<div class="max-w-3xl mx-auto space-y-5" x-data="formStep3()" x-init="init()">

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
                style="background:hsl(43,70%,47%,0.1);">
                <svg class="w-5 h-5" style="color:hsl(43,60%,38%);" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" />
                    <circle cx="9" cy="7" r="4" />
                    <path d="M23 21v-2a4 4 0 00-3-3.87" />
                    <path d="M16 3.13a4 4 0 010 7.75" />
                </svg>
            </div>
            <div>
                <h2 class="text-base font-bold font-serif" style="color:hsl(220,54%,15%);">C. IDENTITAS ORANG TUA / WALI</h2>
                <p class="text-xs mt-0.5" style="color:hsl(220,15%,50%);">Isi data orang tua atau wali yang sah</p>
            </div>
        </div>

        <form id="step3-form" action="<?= base_url('dashboard/formulir/step/3') ?>" method="POST"
            class="px-6 py-6 space-y-6" @change="scheduleSave()">
            <?= csrf_field() ?>

            <!-- 1. Nama Orang Tua -->
            <div>
                <div class="flex items-center gap-3 mb-4">
                    <span class="text-sm font-bold" style="color:hsl(220,54%,15%);">1. Nama Orang Tua</span>
                    <div class="flex-1 h-px" style="background:hsl(220,20%,90%);"></div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-semibold mb-1.5" style="color:hsl(220,54%,15%);">
                            Ayah <span style="color:hsl(0,72%,51%);">*</span>
                        </label>
                        <input type="text" name="nama_ayah" value="<?= esc(old('nama_ayah', $d->nama_ayah ?? '')) ?>"
                            placeholder="Nama lengkap ayah kandung"
                            class="<?= isset($err['nama_ayah']) ? $inpE : $inpN ?>" required>
                        <?= errF2('nama_ayah', $err) ?>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1.5" style="color:hsl(220,54%,15%);">
                            Ibu <span style="color:hsl(0,72%,51%);">*</span>
                        </label>
                        <input type="text" name="nama_ibu" value="<?= esc(old('nama_ibu', $d->nama_ibu ?? '')) ?>"
                            placeholder="Nama lengkap ibu kandung"
                            class="<?= isset($err['nama_ibu']) ? $inpE : $inpN ?>" required>
                        <?= errF2('nama_ibu', $err) ?>
                    </div>
                </div>
            </div>

            <!-- 2. Pendidikan & Pekerjaan -->
            <div>
                <div class="flex items-center gap-3 mb-4">
                    <span class="text-sm font-bold" style="color:hsl(220,54%,15%);">2. Pendidikan &amp; Pekerjaan</span>
                    <div class="flex-1 h-px" style="background:hsl(220,20%,90%);"></div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1.5" style="color:hsl(220,54%,15%);">Pendidikan Ayah</label>
                        <select name="pendidikan_ayah" class="<?= $sel ?>">
                            <option value="">-- Pilih --</option>
                            <?php foreach (['SD/MI', 'SMP/MTs', 'SMA/SMK/MA', 'D1/D2/D3', 'S1', 'S2', 'S3', 'Tidak Sekolah'] as $p): ?>
                                <option value="<?= $p ?>" <?= old('pendidikan_ayah', $d->pendidikan_ayah ?? '') === $p ? 'selected' : '' ?>><?= $p ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1.5" style="color:hsl(220,54%,15%);">Pendidikan Ibu</label>
                        <select name="pendidikan_ibu" class="<?= $sel ?>">
                            <option value="">-- Pilih --</option>
                            <?php foreach (['SD/MI', 'SMP/MTs', 'SMA/SMK/MA', 'D1/D2/D3', 'S1', 'S2', 'S3', 'Tidak Sekolah'] as $p): ?>
                                <option value="<?= $p ?>" <?= old('pendidikan_ibu', $d->pendidikan_ibu ?? '') === $p ? 'selected' : '' ?>><?= $p ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1.5" style="color:hsl(220,54%,15%);">Pekerjaan Ayah</label>
                        <input type="text" name="pekerjaan_ayah" value="<?= esc(old('pekerjaan_ayah', $d->pekerjaan_ayah ?? '')) ?>"
                            placeholder="PNS, Wiraswasta, Petani, dll" class="<?= $inpN ?>">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1.5" style="color:hsl(220,54%,15%);">Pekerjaan Ibu</label>
                        <input type="text" name="pekerjaan_ibu" value="<?= esc(old('pekerjaan_ibu', $d->pekerjaan_ibu ?? '')) ?>"
                            placeholder="Ibu Rumah Tangga, PNS, dll" class="<?= $inpN ?>">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1.5" style="color:hsl(220,54%,15%);">Penghasilan Ayah / Bulan</label>
                        <select name="penghasilan_ayah" class="<?= $sel ?>">
                            <option value="">-- Pilih --</option>
                            <?php foreach (['< Rp 1.000.000', 'Rp 1.000.000 - 3.000.000', 'Rp 3.000.000 - 5.000.000', 'Rp 5.000.000 - 10.000.000', '> Rp 10.000.000', 'Tidak Berpenghasilan'] as $pg): ?>
                                <option value="<?= $pg ?>" <?= old('penghasilan_ayah', $d->penghasilan_ayah ?? '') === $pg ? 'selected' : '' ?>><?= $pg ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1.5" style="color:hsl(220,54%,15%);">Penghasilan Ibu / Bulan</label>
                        <select name="penghasilan_ibu" class="<?= $sel ?>">
                            <option value="">-- Pilih --</option>
                            <?php foreach (['< Rp 1.000.000', 'Rp 1.000.000 - 3.000.000', 'Rp 3.000.000 - 5.000.000', 'Rp 5.000.000 - 10.000.000', '> Rp 10.000.000', 'Tidak Berpenghasilan'] as $pg): ?>
                                <option value="<?= $pg ?>" <?= old('penghasilan_ibu', $d->penghasilan_ibu ?? '') === $pg ? 'selected' : '' ?>><?= $pg ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- 3. No. Telp. Wali Murid -->
            <div>
                <div class="flex items-center gap-3 mb-4">
                    <span class="text-sm font-bold" style="color:hsl(220,54%,15%);">3. No. Telp. Wali Murid</span>
                    <div class="flex-1 h-px" style="background:hsl(220,20%,90%);"></div>
                </div>
                <!-- Petunjuk format -->
                <div class="mb-4 flex items-start gap-2 rounded-xl px-4 py-3 text-xs"
                    style="background:hsl(220,60%,97%);border:1px solid hsl(220,40%,88%);color:hsl(220,40%,40%);">
                    <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10" />
                        <line x1="12" y1="16" x2="12" y2="12" />
                        <line x1="12" y1="8" x2="12.01" y2="8" />
                    </svg>
                    <span>
                        Format nomor HP Indonesia: diawali <strong>08</strong>, terdiri dari <strong>10–15 digit</strong>.
                        Contoh: <strong>081234567890</strong>, <strong>082345678901</strong>.
                        Jangan gunakan tanda hubung, spasi, atau kode negara (+62).
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <!-- No HP Ayah -->
                    <div>
                        <label class="block text-sm font-semibold mb-1.5" style="color:hsl(220,54%,15%);">
                            Ayah <span style="color:hsl(0,72%,51%);">*</span>
                        </label>
                        <input
                            type="tel"
                            name="no_hp_ortu"
                            id="no_hp_ortu"
                            value="<?= esc(old('no_hp_ortu', $d->no_hp_ortu ?? '')) ?>"
                            placeholder="08xxxxxxxxxx"
                            maxlength="15"
                            inputmode="numeric"
                            x-on:blur="validatePhone($event.target, 'err_no_hp_ortu', true)"
                            x-on:input="clearPhoneError('err_no_hp_ortu')"
                            class="<?= isset($err['no_hp_ortu']) ? $inpE : $inpN ?>"
                            :class="phoneErrors.no_hp_ortu ? '<?= $inpE ?>' : '<?= $inpN ?>'">
                        <!-- Error dari server (flash) -->
                        <?= errF2('no_hp_ortu', $err) ?>
                        <!-- Error dari validasi frontend realtime -->
                        <p x-show="phoneErrors.no_hp_ortu" x-text="phoneErrors.no_hp_ortu"
                            class="mt-1.5 flex items-center gap-1.5 text-xs" style="color:hsl(0,55%,45%);"></p>
                    </div>

                    <!-- No HP Ibu -->
                    <div>
                        <label class="block text-sm font-medium mb-1.5" style="color:hsl(220,54%,15%);">
                            Ibu
                            <span class="font-normal text-xs ml-1" style="color:hsl(220,15%,55%);">(opsional)</span>
                        </label>
                        <input
                            type="tel"
                            name="no_hp_ibu"
                            id="no_hp_ibu"
                            value="<?= esc(old('no_hp_ibu', $d->no_hp_ibu ?? '')) ?>"
                            placeholder="08xxxxxxxxxx"
                            maxlength="15"
                            inputmode="numeric"
                            x-on:blur="validatePhone($event.target, 'err_no_hp_ibu', false)"
                            x-on:input="clearPhoneError('err_no_hp_ibu')"
                            class="<?= isset($err['no_hp_ibu']) ? $inpE : $inpN ?>"
                            :class="phoneErrors.no_hp_ibu ? '<?= $inpE ?>' : '<?= $inpN ?>'">
                        <?= errF2('no_hp_ibu', $err) ?>
                        <p x-show="phoneErrors.no_hp_ibu" x-text="phoneErrors.no_hp_ibu"
                            class="mt-1.5 flex items-center gap-1.5 text-xs" style="color:hsl(0,55%,45%);"></p>
                    </div>
                </div>
            </div>

            <!-- Data Wali (collapsible) -->
            <div x-data="{ open: <?= !empty($d->nama_wali) ? 'true' : 'false' ?> }">
                <button type="button" @click="open = !open"
                    class="flex items-center gap-2 text-sm font-medium transition-colors"
                    style="color:hsl(220,54%,20%);">
                    <svg class="w-4 h-4 transition-transform duration-200" :class="open ? 'rotate-90' : ''"
                        fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <polyline points="9 18 15 12 9 6" />
                    </svg>
                    Data Wali
                    <span class="font-normal" style="color:hsl(220,15%,55%);">(opsional, jika berbeda dengan orang tua)</span>
                </button>
                <div x-show="open" x-transition class="grid grid-cols-1 sm:grid-cols-2 gap-5 mt-4">
                    <div>
                        <label class="block text-sm font-medium mb-1.5" style="color:hsl(220,54%,15%);">Nama Wali</label>
                        <input type="text" name="nama_wali" value="<?= esc(old('nama_wali', $d->nama_wali ?? '')) ?>" class="<?= $inpN ?>">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1.5" style="color:hsl(220,54%,15%);">No. HP Wali</label>
                        <input
                            type="tel"
                            name="no_hp_wali"
                            id="no_hp_wali"
                            value="<?= esc(old('no_hp_wali', $d->no_hp_wali ?? '')) ?>"
                            placeholder="08xxxxxxxxxx"
                            maxlength="15"
                            inputmode="numeric"
                            x-on:blur="validatePhone($event.target, 'err_no_hp_wali', false)"
                            x-on:input="clearPhoneError('err_no_hp_wali')"
                            class="<?= isset($err['no_hp_wali']) ? $inpE : $inpN ?>"
                            :class="phoneErrors.no_hp_wali ? '<?= $inpE ?>' : '<?= $inpN ?>'">
                        <?= errF2('no_hp_wali', $err) ?>
                        <p x-show="phoneErrors.no_hp_wali" x-text="phoneErrors.no_hp_wali"
                            class="mt-1.5 flex items-center gap-1.5 text-xs" style="color:hsl(0,55%,45%);"></p>
                    </div>
                </div>
            </div>

        </form>
    </div><!-- /card -->

    <!-- NAVIGATION -->
    <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-3">
        <a href="<?= base_url('dashboard/formulir/step/2') ?>"
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

        <button type="button" @click="submitForm()"
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
</div>

<script>
    // ── Validasi format HP Indonesia ─────────────────────────────────────────
    // Format valid: diawali 08, panjang total 10–15 digit, hanya angka.
    const HP_REGEX = /^08[0-9]{8,13}$/;

    function validatePhoneValue(val) {
        if (!val || val.trim() === '') return null; // kosong = boleh (kecuali required)
        const clean = val.trim().replace(/[\s\-]/g, '');
        if (!/^[0-9]+$/.test(clean)) {
            return 'Nomor HP hanya boleh berisi angka, tanpa spasi atau tanda hubung.';
        }
        if (!clean.startsWith('08')) {
            return 'Nomor HP harus diawali dengan 08. Contoh: 081234567890.';
        }
        if (clean.length < 10) {
            return `Nomor HP terlalu pendek (${clean.length} digit). Minimal 10 digit, contoh: 081234567890.`;
        }
        if (clean.length > 15) {
            return `Nomor HP terlalu panjang (${clean.length} digit). Maksimal 15 digit.`;
        }
        if (!HP_REGEX.test(clean)) {
            return 'Format nomor HP tidak valid. Contoh: 081234567890 atau 082345678901.';
        }
        return null; // valid
    }

    // ── formStep3: autosave + validasi HP ────────────────────────────────────
    function formStep3() {
        return {
            saving: false,
            saveStatus: '', // '' | 'saving' | 'saved' | 'error'
            lastSaved: '',
            timer: null,
            phoneErrors: {
                no_hp_ortu: '',
                no_hp_ibu: '',
                no_hp_wali: '',
            },

            init() {
                <?php if (!empty($pendaftaran->updated_at)): ?>
                    const pad = n => String(n).padStart(2, '0'),
                        now = new Date();
                    this.lastSaved = `${pad(now.getHours())}.${pad(now.getMinutes())}.${pad(now.getSeconds())}`;
                    this.saveStatus = 'saved';
                <?php endif; ?>
            },

            // ── Validasi nomor HP dari event blur ───────────────────────────
            validatePhone(input, errKey, isRequired) {
                const val = input.value.trim();
                let msg = null;

                if (isRequired && (!val || val === '')) {
                    msg = 'Nomor HP Ayah wajib diisi.';
                } else {
                    msg = validatePhoneValue(val);
                }

                this.phoneErrors[errKey.replace('err_', '')] = msg || '';

                // Update styling input secara langsung
                const inpN = 'w-full px-4 py-2.5 border border-gray-300 rounded-xl text-base sm:text-sm transition focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 bg-white';
                const inpE = 'w-full px-4 py-2.5 border border-red-400 rounded-xl text-sm transition focus:outline-none focus:ring-2 focus:ring-red-400/15 bg-red-50';
                input.className = msg ? inpE : inpN;
            },

            clearPhoneError(errKey) {
                this.phoneErrors[errKey.replace('err_', '')] = '';
            },

            // ── Validasi semua phone field sebelum submit ────────────────────
            validateAllPhones() {
                let hasError = false;

                const fields = [{
                        id: 'no_hp_ortu',
                        key: 'no_hp_ortu',
                        required: true
                    },
                    {
                        id: 'no_hp_ibu',
                        key: 'no_hp_ibu',
                        required: false
                    },
                    {
                        id: 'no_hp_wali',
                        key: 'no_hp_wali',
                        required: false
                    },
                ];

                for (const f of fields) {
                    const input = document.getElementById(f.id);
                    if (!input) continue;
                    const val = input.value.trim();
                    let msg = null;

                    if (f.required && (!val || val === '')) {
                        msg = 'Nomor HP Ayah wajib diisi.';
                    } else if (val !== '') {
                        msg = validatePhoneValue(val);
                    }

                    this.phoneErrors[f.key] = msg || '';

                    const inpN = 'w-full px-4 py-2.5 border border-gray-300 rounded-xl text-base sm:text-sm transition focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 bg-white';
                    const inpE = 'w-full px-4 py-2.5 border border-red-400 rounded-xl text-sm transition focus:outline-none focus:ring-2 focus:ring-red-400/15 bg-red-50';
                    input.className = msg ? inpE : inpN;

                    if (msg) {
                        hasError = true;
                        if (!document.getElementById(f.id + '_focused')) {
                            input.scrollIntoView({
                                behavior: 'smooth',
                                block: 'center'
                            });
                            input.focus();
                            input.setAttribute('id', f.id + '_focused'); // trick supaya hanya scroll ke yg pertama
                        }
                    }
                }

                // Bersihkan trick id
                ['no_hp_ortu_focused', 'no_hp_ibu_focused', 'no_hp_wali_focused'].forEach(id => {
                    const el = document.getElementById(id);
                    if (el) el.removeAttribute('id');
                });

                return !hasError;
            },

            // ── Submit form (dengan validasi HP dulu) ───────────────────────
            submitForm() {
                if (!this.validateAllPhones()) {
                    // Scroll ke error pertama
                    const firstErr = document.querySelector('.border-red-400');
                    if (firstErr) firstErr.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                    return; // Hentikan — jangan lanjut ke step berikutnya
                }
                document.getElementById('step3-form').submit();
            },

            scheduleSave() {
                clearTimeout(this.timer);
                this.saveStatus = 'saving';
                this.timer = setTimeout(() => this._doSave(), 3000);
            },

            async saveDraft() {
                if (this.saving) return;
                clearTimeout(this.timer);
                await this._doSave();
            },

            async _doSave() {
                if (this.saving) return;
                this.saving = true;
                this.saveStatus = 'saving';
                const data = new FormData(document.getElementById('step3-form'));
                data.append('step', '3');
                try {
                    const res = await fetch('<?= base_url('dashboard/formulir/autosave') ?>', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: data
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