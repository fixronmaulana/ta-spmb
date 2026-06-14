<?php

/** @var object $pendaftaran */
/** @var object|null $dataDiri */
/** @var array $steps */
/** @var int $currentStep */

$d   = $dataDiri ?? new stdClass();
$err = session()->getFlashdata('errors') ?? [];

$flashError   = session()->getFlashdata('error');
$flashSuccess = session()->getFlashdata('success');

$inpN = 'w-full px-4 py-2.5 border border-gray-300 rounded-xl text-base sm:text-sm transition focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 bg-white';
$inpE = 'w-full px-4 py-2.5 border border-red-400 rounded-xl text-sm transition focus:outline-none focus:ring-2 focus:ring-red-400/15 bg-red-50';
$inpS = 'w-full px-4 py-2.5 border border-emerald-500 rounded-xl text-sm transition focus:outline-none focus:ring-2 focus:ring-emerald-500/15 bg-white';
$sel  = 'w-full px-4 py-2.5 border border-gray-300 rounded-xl text-base sm:text-sm focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 bg-white transition';

function errField(string $field, array $errors): string
{
    if (!isset($errors[$field])) return '';
    return '<p class="mt-1.5 flex items-center gap-1.5 text-xs" style="color:hsl(0,55%,45%);">
        <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        ' . esc($errors[$field]) . '</p>';
}
?>

<?php if ($flashError): ?>
    <div class="max-w-3xl mx-auto mb-3 flex items-start gap-3 px-4 py-3 rounded-xl text-sm"
        style="background:hsl(0,72%,51%,0.08);border:1px solid hsl(0,72%,51%,0.25);color:hsl(0,55%,38%);">
        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="10" />
            <line x1="12" y1="8" x2="12" y2="12" />
            <line x1="12" y1="16" x2="12.01" y2="16" />
        </svg>
        <span><?= esc($flashError) ?></span>
    </div>
<?php endif; ?>

<div class="max-w-3xl mx-auto space-y-5" x-data="formStep1()" x-init="init()">

    <!-- HEADER -->
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold font-serif" style="color:hsl(220,54%,15%);">Formulir Pendaftaran</h1>
            <p class="text-sm mt-0.5" style="color:hsl(220,15%,50%);">SPMB SMK Al-Munawwir IIBS <?= esc($pendaftaran->periode_nama ?? '2026/2027') ?></p>
        </div>

        <!-- ── Indikator draft real-time (waktu device) ── -->
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

    <!-- CARD FORMULIR -->
    <div class="bg-white rounded-2xl border overflow-hidden"
        style="border-color:hsl(220,20%,88%);box-shadow:0 4px 6px -1px hsl(220 54% 20%/0.07),0 2px 4px -2px hsl(220 54% 20%/0.05);">

        <div class="px-6 py-5 border-b flex items-center gap-3"
            style="border-color:hsl(220,20%,92%);background:hsl(220,20%,98%);">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0"
                style="background:hsl(220,54%,20%,0.08);">
                <svg class="w-5 h-5" style="color:hsl(220,54%,20%);" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" />
                    <circle cx="12" cy="7" r="4" />
                </svg>
            </div>
            <div>
                <h2 class="text-base font-bold font-serif" style="color:hsl(220,54%,15%);">A. IDENTITAS PESERTA DIDIK</h2>
                <p class="text-xs mt-0.5" style="color:hsl(220,15%,50%);">Isi data diri sesuai dokumen resmi (Akta Kelahiran / KK)</p>
            </div>
        </div>

        <form id="step1-form" action="<?= base_url('dashboard/formulir/step/1') ?>" method="POST"
            class="px-6 py-6 space-y-5">
            <?= csrf_field() ?>

            <!-- 1. Nama Lengkap + Panggilan -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-semibold mb-1.5" style="color:hsl(220,54%,15%);">
                        1. Nama Lengkap Siswa <span style="color:hsl(0,72%,51%);">*</span>
                    </label>
                    <input type="text" name="nama_lengkap"
                        value="<?= esc(old('nama_lengkap', $d->nama_lengkap ?? '')) ?>"
                        placeholder="Sesuai dengan ijazah / akta kelahiran"
                        class="<?= isset($err['nama_lengkap']) ? $inpE : $inpN ?>" required>
                    <?= errField('nama_lengkap', $err) ?>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1.5" style="color:hsl(220,54%,15%);">Nama Panggilan</label>
                    <input type="text" name="nama_panggilan"
                        value="<?= esc(old('nama_panggilan', $d->nama_panggilan ?? '')) ?>"
                        placeholder="Nama sehari-hari" class="<?= $inpN ?>">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-2" style="color:hsl(220,54%,15%);">
                        2. Jenis Kelamin <span style="color:hsl(0,72%,51%);">*</span>
                    </label>
                    <div class="flex gap-5 mt-1">
                        <?php foreach (['L' => 'Laki-laki', 'P' => 'Perempuan'] as $val => $label):
                            $checked = old('jenis_kelamin', $d->jenis_kelamin ?? '') === $val; ?>
                            <label class="flex items-center gap-2.5 cursor-pointer">
                                <input type="radio" name="jenis_kelamin" value="<?= $val ?>"
                                    <?= $checked ? 'checked' : '' ?> required class="w-4 h-4 accent-primary cursor-pointer">
                                <span class="text-sm" style="color:hsl(220,54%,15%);"><?= $label ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <?= errField('jenis_kelamin', $err) ?>
                </div>
            </div>

            <!-- 3. Tempat & Tanggal Lahir -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-semibold mb-1.5" style="color:hsl(220,54%,15%);">
                        3. Tempat Lahir <span style="color:hsl(0,72%,51%);">*</span>
                    </label>
                    <input type="text" name="tempat_lahir"
                        value="<?= esc(old('tempat_lahir', $d->tempat_lahir ?? '')) ?>"
                        placeholder="Contoh: Banyuwangi"
                        class="<?= isset($err['tempat_lahir']) ? $inpE : $inpN ?>" required>
                    <?= errField('tempat_lahir', $err) ?>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1.5" style="color:hsl(220,54%,15%);">
                        Tanggal Lahir <span style="color:hsl(0,72%,51%);">*</span>
                    </label>
                    <input type="date" name="tanggal_lahir"
                        value="<?= esc(old('tanggal_lahir', $d->tanggal_lahir ?? '')) ?>"
                        max="<?= date('Y-m-d', strtotime('-10 years')) ?>"
                        class="<?= isset($err['tanggal_lahir']) ? $inpE : $inpN ?>" required>
                    <?= errField('tanggal_lahir', $err) ?>
                </div>
            </div>

            <!-- 4. NIK + NISN -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div x-data="{ val: '<?= esc(old('nik', $d->nik ?? '')) ?>' }">
                    <label class="block text-sm font-semibold mb-1.5" style="color:hsl(220,54%,15%);">
                        4. NIK <span class="font-normal text-xs" style="color:hsl(220,15%,55%);">(Nomor Induk Kependudukan)</span>
                    </label>
                    <div class="relative">
                        <input type="text" name="nik" maxlength="16" inputmode="numeric" x-model="val"
                            placeholder="16 digit NIK"
                            :class="val.length === 16 ? '<?= $inpS ?> pr-10' : '<?= isset($err['nik']) ? $inpE : $inpN ?>'">
                        <span x-show="val.length === 16" class="absolute inset-y-0 right-3 flex items-center pointer-events-none">
                            <svg class="w-4 h-4" style="color:hsl(142,60%,40%);" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path d="M22 11.08V12a10 10 0 11-5.93-9.14" />
                                <polyline points="22 4 12 14.01 9 11.01" />
                            </svg>
                        </span>
                    </div>
                    <p x-show="val.length === 16" class="mt-1 text-xs" style="color:hsl(142,60%,40%);">✓ 16 digit valid</p>
                    <?= errField('nik', $err) ?>
                </div>
                <div x-data="{ val: '<?= esc(old('nisn', $d->nisn ?? '')) ?>' }">
                    <label class="block text-sm font-semibold mb-1.5" style="color:hsl(220,54%,15%);">
                        13. NISN <span style="color:hsl(0,72%,51%);">*</span>
                    </label>
                    <div class="relative">
                        <input type="text" name="nisn" maxlength="10" inputmode="numeric" x-model="val"
                            placeholder="10 digit NISN"
                            :class="val.length === 10 ? '<?= $inpS ?> pr-10' : (val.length > 0 && val.length < 10 ? '<?= $inpE ?>' : '<?= isset($err['nisn']) ? $inpE : $inpN ?>')"
                            required>
                        <span x-show="val.length === 10" class="absolute inset-y-0 right-3 flex items-center pointer-events-none">
                            <svg class="w-4 h-4" style="color:hsl(142,60%,40%);" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path d="M22 11.08V12a10 10 0 11-5.93-9.14" />
                                <polyline points="22 4 12 14.01 9 11.01" />
                            </svg>
                        </span>
                    </div>
                    <p x-show="val.length === 10" class="mt-1 text-xs" style="color:hsl(142,60%,40%);">✓ 10 digit valid</p>
                    <p x-show="val.length > 0 && val.length < 10" class="mt-1 text-xs" style="color:hsl(0,55%,45%);">
                        Kurang <span x-text="10 - val.length"></span> digit lagi
                    </p>
                    <?= errField('nisn', $err) ?>
                </div>
            </div>

            <!-- 5. Status Anak + 6. Agama -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium mb-1.5" style="color:hsl(220,54%,15%);">5. Status Anak dalam Keluarga</label>
                    <select name="status_anak" class="<?= $sel ?>">
                        <option value="">-- Pilih --</option>
                        <?php foreach (['Anak Kandung', 'Anak Angkat', 'Yatim', 'Piatu', 'Yatim Piatu', 'Anak Tiri'] as $s): ?>
                            <option value="<?= $s ?>" <?= old('status_anak', $d->status_anak ?? '') === $s ? 'selected' : '' ?>><?= $s ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1.5" style="color:hsl(220,54%,15%);">
                        6. Agama <span style="color:hsl(0,72%,51%);">*</span>
                    </label>
                    <select name="agama" class="<?= isset($err['agama']) ? str_replace('border-gray-300', 'border-red-400 bg-red-50', $sel) : $sel ?>" required>
                        <option value="">-- Pilih --</option>
                        <?php foreach (['Islam', 'Kristen Protestan', 'Kristen Katolik', 'Hindu', 'Buddha', 'Konghucu'] as $a): ?>
                            <option value="<?= $a ?>" <?= old('agama', $d->agama ?? '') === $a ? 'selected' : '' ?>><?= $a ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?= errField('agama', $err) ?>
                </div>
            </div>

            <!-- 7. Kewarganegaraan -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium mb-1.5" style="color:hsl(220,54%,15%);">7. Kewarganegaraan</label>
                    <select name="kewarganegaraan" class="<?= $sel ?>">
                        <option value="WNI" <?= old('kewarganegaraan', $d->kewarganegaraan ?? 'WNI') === 'WNI' ? 'selected' : '' ?>>WNI (Warga Negara Indonesia)</option>
                        <option value="WNA" <?= old('kewarganegaraan', $d->kewarganegaraan ?? '') === 'WNA' ? 'selected' : '' ?>>WNA (Warga Negara Asing)</option>
                    </select>
                </div>
            </div>

            <!-- 9. Alamat -->
            <div>
                <div class="flex items-center gap-3 mb-4">
                    <span class="text-sm font-bold" style="color:hsl(220,54%,15%);">9. Alamat Tempat Tinggal</span>
                    <div class="flex-1 h-px" style="background:hsl(220,20%,90%);"></div>
                </div>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold mb-1.5" style="color:hsl(220,54%,15%);">
                            Alamat Lengkap <span style="color:hsl(0,72%,51%);">*</span>
                        </label>
                        <textarea name="alamat" rows="2" required
                            placeholder="Jalan, nomor rumah, dsb."
                            class="<?= isset($err['alamat']) ? $inpE : $inpN ?> resize-none"><?= esc(old('alamat', $d->alamat ?? '')) ?></textarea>
                        <?= errField('alamat', $err) ?>
                    </div>
                    <div class="grid grid-cols-3 sm:grid-cols-4 gap-3">
                        <div>
                            <label class="block text-xs font-medium mb-1" style="color:hsl(220,54%,15%);">Dusun</label>
                            <input type="text" name="dusun" value="<?= esc(old('dusun', $d->dusun ?? '')) ?>" placeholder="Nama dusun" class="<?= $inpN ?>">
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1" style="color:hsl(220,54%,15%);">RT</label>
                            <input type="text" name="rt" maxlength="3" value="<?= esc(old('rt', $d->rt ?? '')) ?>" placeholder="001" class="<?= $inpN ?>">
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1" style="color:hsl(220,54%,15%);">RW</label>
                            <input type="text" name="rw" maxlength="3" value="<?= esc(old('rw', $d->rw ?? '')) ?>" placeholder="002" class="<?= $inpN ?>">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium mb-1" style="color:hsl(220,54%,15%);">Desa / Kelurahan</label>
                            <input type="text" name="kelurahan" value="<?= esc(old('kelurahan', $d->kelurahan ?? '')) ?>" placeholder="Nama desa" class="<?= $inpN ?>">
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1" style="color:hsl(220,54%,15%);">Kecamatan</label>
                            <input type="text" name="kecamatan" value="<?= esc(old('kecamatan', $d->kecamatan ?? '')) ?>" placeholder="Nama kecamatan" class="<?= $inpN ?>">
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1" style="color:hsl(220,54%,15%);">Kabupaten / Kota</label>
                            <input type="text" name="kabupaten" value="<?= esc(old('kabupaten', $d->kabupaten ?? '')) ?>" placeholder="Nama kabupaten" class="<?= $inpN ?>">
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1" style="color:hsl(220,54%,15%);">Provinsi</label>
                            <input type="text" name="provinsi" value="<?= esc(old('provinsi', $d->provinsi ?? '')) ?>" placeholder="Nama provinsi" class="<?= $inpN ?>">
                        </div>
                    </div>
                </div>
            </div>

            <!-- 11. Kontak & Sekolah Asal -->
            <div>
                <div class="flex items-center gap-3 mb-4">
                    <span class="text-sm font-bold" style="color:hsl(220,54%,15%);">11. Kontak &amp; Sekolah Asal</span>
                    <div class="flex-1 h-px" style="background:hsl(220,20%,90%);"></div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

                    <!-- No. HP / WA dengan OTP -->
                    <div>
                        <label class="block text-sm font-semibold mb-1.5" style="color:hsl(220,54%,15%);">
                            No. HP / WA Aktif <span style="color:hsl(0,72%,51%);">*</span>
                        </label>
                        <input type="tel" name="no_hp" id="no_hp"
                            value="<?= esc(old('no_hp', $d->no_hp ?? '')) ?>"
                            placeholder="08xxxxxxxxxx"
                            class="<?= isset($err['no_hp']) ? $inpE : $inpN ?>"
                            autocomplete="tel" required>
                        <?= errField('no_hp', $err) ?>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1.5" style="color:hsl(220,54%,15%);">E-Mail Siswa</label>
                        <input type="email" name="email_siswa" value="<?= esc(old('email_siswa', $d->email_siswa ?? '')) ?>"
                            placeholder="contoh@email.com" class="<?= $inpN ?>">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-semibold mb-1.5" style="color:hsl(220,54%,15%);">
                            12. Nama Sekolah Asal <span style="color:hsl(0,72%,51%);">*</span>
                        </label>
                        <input type="text" name="asal_sekolah" value="<?= esc(old('asal_sekolah', $d->asal_sekolah ?? '')) ?>"
                            placeholder="Contoh: SMP Negeri 1 Singojuruh"
                            class="<?= isset($err['asal_sekolah']) ? $inpE : $inpN ?>" required>
                        <?= errField('asal_sekolah', $err) ?>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1.5" style="color:hsl(220,54%,15%);">Alamat Sekolah</label>
                        <input type="text" name="alamat_sekolah" value="<?= esc(old('alamat_sekolah', $d->alamat_sekolah ?? '')) ?>"
                            placeholder="Alamat sekolah asal" class="<?= $inpN ?>">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1.5" style="color:hsl(220,54%,15%);">Tahun Lulus</label>
                        <select name="tahun_lulus" class="<?= $sel ?>">
                            <option value="">-- Pilih Tahun --</option>
                            <?php for ($y = date('Y') + 1; $y >= date('Y') - 5; $y--): ?>
                                <option value="<?= $y ?>" <?= old('tahun_lulus', $d->tahun_lulus ?? '') == $y ? 'selected' : '' ?>><?= $y ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>

                </div>
            </div>

        </form>
    </div><!-- /card -->

    <!-- NAVIGATION -->
    <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-3">

        <a href="<?= base_url('dashboard') ?>"
            class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl text-sm font-medium border transition w-full sm:w-auto"
            style="border-color:hsl(220,20%,82%);color:hsl(220,54%,20%);background:white;"
            onmouseover="this.style.background='hsl(220,20%,96%)'"
            onmouseout="this.style.background='white'">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <polyline points="15 18 9 12 15 6" />
            </svg>
            Ke Dashboard
        </a>

        <!-- Simpan Draft -->
        <button type="button" @click="saveDraft()"
            :disabled="saving"
            class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl text-sm font-medium border transition w-full sm:w-auto"
            :style="saving
                ? 'border-color:hsl(220,20%,88%);color:hsl(220,15%,60%);background:hsl(220,20%,96%);cursor:not-allowed;'
                : 'border-color:hsl(220,20%,82%);color:hsl(220,54%,20%);background:white;cursor:pointer;'"
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

        <button type="submit" form="step1-form"
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
    function waVerify() {
        return {
            phone: '<?= esc(old("no_hp", $d->no_hp ?? "")) ?>',
            otpSent: false,
            otpValue: '',
            maskedPhone: '',
            sending: false,
            verifying: false,
            countdown: 0,
            _cdTimer: null,
            formatError: '',
            otpError: '',
            isVerified: <?= json_encode($waVerifiedStatus ?? false) ?>,

            validateFormat() {
                const p = this.phone.replace(/[\s\-\(\)]/g, '');
                if (!p) {
                    this.formatError = '';
                    return;
                }
                this.formatError = /^(\+62|62|0)8[0-9]{8,12}$/.test(p) ? '' : 'Format tidak valid. Contoh: 08512345678';
            },
            onPhoneInput() {
                if (this.isVerified) {
                    this.isVerified = false;
                    this.otpSent = false;
                    this.otpValue = '';
                    this.otpError = '';
                    clearInterval(this._cdTimer);
                    this.countdown = 0;
                }
                this.validateFormat();
            },
            async sendOtp() {
                this.validateFormat();
                if (this.formatError || this.phone.length < 10 || this.sending) return;
                this.sending = true;
                this.otpError = '';
                try {
                    const res = await fetch('<?= base_url("dashboard/formulir/check-wa") ?>', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: new URLSearchParams({
                            no_hp: this.phone
                        }),
                    });
                    const data = await res.json();
                    if (data.success) {
                        this.otpSent = true;
                        this.maskedPhone = data.masked ?? this.phone;
                        this.startCountdown(60);
                        this.$nextTick(() => document.getElementById('otp_box_0')?.focus());
                    } else {
                        this.formatError = data.message ?? 'Gagal mengirim OTP. Coba lagi.';
                    }
                } catch {
                    this.formatError = 'Koneksi gagal. Coba lagi.';
                } finally {
                    this.sending = false;
                }
            },
            startCountdown(s) {
                this.countdown = s;
                clearInterval(this._cdTimer);
                this._cdTimer = setInterval(() => {
                    if (--this.countdown <= 0) {
                        this.countdown = 0;
                        clearInterval(this._cdTimer);
                    }
                }, 1000);
            },
            onOtpDigit(e, idx) {
                const v = e.target.value.replace(/\D/, '');
                e.target.value = v;
                this.refreshOtpValue();
                this.otpError = '';
                if (v) document.getElementById(`otp_box_${idx + 1}`)?.focus();
            },
            onOtpKeydown(e, idx) {
                if (e.key === 'Backspace' && !e.target.value && idx > 0) document.getElementById(`otp_box_${idx - 1}`)?.focus();
            },
            onOtpPaste(e) {
                const txt = e.clipboardData.getData('text').replace(/\D/g, '').slice(0, 6);
                [...txt].forEach((ch, i) => {
                    const el = document.getElementById(`otp_box_${i}`);
                    if (el) el.value = ch;
                });
                this.refreshOtpValue();
                document.getElementById(`otp_box_${Math.min(txt.length, 5)}`)?.focus();
            },
            refreshOtpValue() {
                this.otpValue = Array.from({
                    length: 6
                }, (_, i) => document.getElementById(`otp_box_${i}`)?.value ?? '').join('');
            },
            async verifyOtp() {
                if (this.otpValue.length < 6 || this.verifying) return;
                this.verifying = true;
                this.otpError = '';
                try {
                    const res = await fetch('<?= base_url("dashboard/formulir/verify-wa-otp") ?>', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: new URLSearchParams({
                            no_hp: this.phone,
                            otp: this.otpValue
                        }),
                    });
                    const data = await res.json();
                    if (data.success) {
                        this.isVerified = true;
                        this.otpSent = false;
                        clearInterval(this._cdTimer);
                    } else {
                        this.otpError = data.message ?? 'OTP tidak valid.';
                        for (let i = 0; i < 6; i++) {
                            const el = document.getElementById(`otp_box_${i}`);
                            if (el) {
                                el.classList.add('animate-pulse');
                                setTimeout(() => el.classList.remove('animate-pulse'), 600);
                            }
                        }
                    }
                } catch {
                    this.otpError = 'Koneksi gagal. Coba lagi.';
                } finally {
                    this.verifying = false;
                }
            },
        };
    }

    // ── formStep1: autosave dengan waktu DEVICE ───────────────────────────
    function formStep1() {
        return {
            saving: false,
            saveStatus: '', // '' | 'saving' | 'saved' | 'error'
            lastSaved: '',
            timer: null,

            init() {
                // Jika ada data tersimpan → tampilkan jam DEVICE (bukan parse server time)
                <?php if (!empty($pendaftaran->updated_at)): ?>
                    const pad = n => String(n).padStart(2, '0'),
                        now = new Date();
                    this.lastSaved = `${pad(now.getHours())}.${pad(now.getMinutes())}.${pad(now.getSeconds())}`;
                    this.saveStatus = 'saved';
                <?php endif; ?>

                // Auto-save setiap kali ada perubahan input (debounce 2 detik)
                this.$nextTick(() => {
                    const form = document.getElementById('step1-form');
                    if (!form) return;
                    form.querySelectorAll('input:not([type=submit]):not([type=hidden]), select, textarea').forEach(el => {
                        el.addEventListener('input', () => this.scheduleSave());
                        el.addEventListener('change', () => this.scheduleSave());
                    });
                });
            },

            scheduleSave() {
                clearTimeout(this.timer);
                this.saveStatus = 'saving';
                this.timer = setTimeout(() => this._doSave(), 2000);
            },

            async saveDraft() {
                // Klik tombol → simpan SEKARANG, tanpa validasi form, tanpa debounce
                if (this.saving) return;
                clearTimeout(this.timer);
                await this._doSave();
            },

            async _doSave() {
                if (this.saving) return;
                this.saving = true;
                this.saveStatus = 'saving';

                const data = new FormData(document.getElementById('step1-form'));
                data.append('step', '1');

                try {
                    const res = await fetch('<?= base_url('dashboard/formulir/autosave') ?>', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: data,
                    });
                    if (res.ok) {
                        // Jam dari device — real-time sesuai timezone lokal pengguna
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