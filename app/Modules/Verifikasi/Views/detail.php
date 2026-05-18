<?php
/*
 * File: app/Modules/Verifikasi/Views/detail.php
 *
 * PERUBAHAN:
 *  - Tombol approve (✓ Setujui) dan reject (✗ Tolak) di baris dokumen
 *    kini menampilkan SVG ikon centang/silang + label teks, dengan
 *    warna hijau/merah yang jelas — tidak lagi polosan.
 *  - Semua confirm() browser (native popup jelek) diganti dengan modal
 *    konfirmasi Alpine.js yang lebih rapih dan konsisten dengan desain.
 *  - Status badge dokumen (Menunggu / Valid / Ditolak) ditingkatkan
 *    dengan ikon SVG di dalamnya agar lebih mudah dibaca sekilas.
 *  - Statistik dokumen (N disetujui / N ditolak / N menunggu) diperkuat
 *    dengan ikon kecil di sebelah angka.
 *  - Variabel $canApproveSemua, $dokumenStats, $prevId, $nextId, $logs
 *    diasumsikan sudah dikirim dari controller (tidak berubah).
 */
$p = $pendaftaran;
$d = $dataDiri;   // bisa null jika siswa belum isi step 1
?>

<?php
$totalDok    = $dokumenStats['total'];
$approvedDok = $dokumenStats['approved'];
$rejectedDok = $dokumenStats['rejected'];
$pendingDok  = $dokumenStats['pending'];
?>

<div class="space-y-4" x-data="verifikasiDetail()">

    <!-- ══ SEARCH + FILTER BAR ══════════════════════════════════════════ -->
    <div class="bg-white rounded-2xl border border-gray-200 p-4">
        <form method="get" action="<?= base_url('admin/verifikasi') ?>" class="flex flex-col sm:flex-row gap-3">

            <!-- Search -->
            <div class="relative flex-1">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400"
                    fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8" />
                    <path d="M21 21l-4.35-4.35" />
                </svg>
                <input type="text" name="search"
                    value="<?= esc(request()->getGet('search') ?? '') ?>"
                    placeholder="Cari nama atau nomor pendaftaran..."
                    class="w-full pl-9 pr-4 py-2.5 border border-gray-300 rounded-xl text-sm
                           focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>

            <!-- Filter Status -->
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400 pointer-events-none"
                    fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3" />
                </svg>
                <select name="status"
                    class="pl-9 pr-8 py-2.5 border border-gray-300 rounded-xl text-sm
                           focus:outline-none focus:ring-2 focus:ring-blue-500
                           w-full sm:w-52 appearance-none bg-white"
                    onchange="this.form.submit()">
                    <option value="all" <?= (request()->getGet('status') ?? 'submitted') === 'all'        ? 'selected' : '' ?>>Semua Status</option>
                    <option value="submitted" <?= (request()->getGet('status') ?? 'submitted') === 'submitted'  ? 'selected' : '' ?>>Menunggu Verifikasi</option>
                    <option value="verifikasi" <?= (request()->getGet('status') ?? '') === 'verifikasi' ? 'selected' : '' ?>>Dalam Verifikasi</option>
                    <option value="seleksi" <?= (request()->getGet('status') ?? '') === 'seleksi'    ? 'selected' : '' ?>>Lolos ke Seleksi</option>
                </select>
                <svg class="absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400 pointer-events-none"
                    fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <polyline points="6 9 12 15 18 9" />
                </svg>
            </div>

            <!-- Filter Jurusan -->
            <div class="relative">
                <select name="jurusan"
                    class="pl-4 pr-8 py-2.5 border border-gray-300 rounded-xl text-sm
                           focus:outline-none focus:ring-2 focus:ring-blue-500
                           w-full sm:w-44 appearance-none bg-white"
                    onchange="this.form.submit()">
                    <option value="">Semua Jurusan</option>
                    <?php
                    $jurusanList = (new \App\Modules\MasterData\Models\JurusanModel())->orderBy('kode')->findAll();
                    foreach ($jurusanList as $jItem):
                    ?>
                        <option value="<?= esc($jItem->kode) ?>"
                            <?= (request()->getGet('jurusan') ?? '') === $jItem->kode ? 'selected' : '' ?>>
                            <?= esc($jItem->kode) ?> — <?= esc($jItem->nama) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <svg class="absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400 pointer-events-none"
                    fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <polyline points="6 9 12 15 18 9" />
                </svg>
            </div>

            <button type="submit"
                class="px-5 py-2.5 rounded-xl text-sm font-semibold text-white transition"
                style="background:hsl(220,54%,20%);"
                onmouseover="this.style.background='hsl(220,54%,28%)'"
                onmouseout="this.style.background='hsl(220,54%,20%)'">
                Cari
            </button>
        </form>
    </div>

    <!-- ══ BREADCRUMB + STATUS ════════════════════════════════════════════ -->
    <div class="flex items-center gap-3 flex-wrap">
        <a href="<?= base_url('admin/verifikasi') ?>"
            class="text-sm font-medium flex items-center gap-1.5 px-3 py-1.5 rounded-lg transition"
            style="color:hsl(220,54%,20%);background:transparent;"
            onmouseover="this.style.background='hsl(220,20%,92%)'"
            onmouseout="this.style.background='transparent'">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <polyline points="19 12 5 12" />
                <polyline points="12 19 5 12 12 5" />
            </svg>
            Kembali ke Daftar
        </a>
        <span style="color:hsl(220,20%,80%);">/</span>
        <span class="text-sm" style="color:hsl(220,15%,55%);">Verifikasi #<?= esc($p->no_pendaftaran ?? $p->id) ?></span>
        <div class="ml-auto">
            <?= status_label($p->status) ?>
        </div>
    </div>

    <!-- ══ SPLIT SCREEN ═══════════════════════════════════════════════════ -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 items-start">

        <!-- ── LEFT: Data + Dokumen ─────────────────────────────────── -->
        <div class="space-y-4">

            <!-- Data Diri -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-900 flex items-center gap-2">
                        <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" />
                            <circle cx="12" cy="7" r="4" />
                        </svg>
                        Data Calon Siswa
                    </h3>
                    <span class="text-xs text-gray-400 font-mono"><?= esc($p->no_pendaftaran ?? '-') ?></span>
                </div>
                <div class="px-5 py-4 grid grid-cols-1 xs:grid-cols-2 gap-3 text-sm">
                    <?php
                    $items = [
                        ['Nama Lengkap',    $d->nama_lengkap  ?? '-'],
                        ['Jenis Kelamin',   isset($d->jenis_kelamin) ? ($d->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan') : '-'],
                        ['Tempat Lahir',    $d->tempat_lahir  ?? '-'],
                        ['Tanggal Lahir',   format_tanggal($d->tanggal_lahir ?? null)],
                        ['Agama',           $d->agama         ?? '-'],
                        ['NISN',            $d->nisn          ?? '-'],
                        ['NIK',             $d->nik           ?? '-'],
                        ['No. HP',          $d->no_hp         ?? '-'],
                        ['Alamat',          $d->alamat        ?? '-'],
                        ['Asal Sekolah',    $d->asal_sekolah  ?? '-'],
                        ['Tahun Lulus',     $d->tahun_lulus   ?? '-'],
                    ];
                    ?>
                    <?php foreach ($items as [$label, $val]): ?>
                        <div>
                            <p class="text-xs text-gray-400"><?= $label ?></p>
                            <p class="font-medium text-gray-800 text-xs mt-0.5 break-words" title="<?= esc($val) ?>">
                                <?= esc($val) ?>
                            </p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Pilihan Jurusan -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 px-5 py-4">
                <h4 class="text-xs font-semibold text-gray-500 uppercase mb-3 flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M22 10v6M2 10l10-5 10 5-10 5z" />
                        <path d="M6 12v5c3 3 9 3 12 0v-5" />
                    </svg>
                    Pilihan Jurusan
                </h4>
                <div class="space-y-2">
                    <div class="flex items-center gap-2">
                        <span class="w-6 h-6 bg-blue-100 text-blue-700 rounded-lg text-xs flex items-center justify-center font-bold flex-shrink-0">1</span>
                        <span class="text-sm font-medium text-gray-800">
                            <?= esc($p->jurusan_pilihan1_nama ?? '-') ?>
                            <?php if ($p->jurusan_pilihan1_kode ?? null): ?>
                                <span class="text-gray-400">(<?= esc($p->jurusan_pilihan1_kode) ?>)</span>
                            <?php endif; ?>
                        </span>
                    </div>
                    <?php if ($p->jurusan_pilihan2_nama ?? null): ?>
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 bg-gray-100 text-gray-600 rounded-lg text-xs flex items-center justify-center font-bold flex-shrink-0">2</span>
                            <span class="text-sm text-gray-600"><?= esc($p->jurusan_pilihan2_nama) ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ══════════════════════════════════════════════════════════
                 DOKUMEN LIST — Ini bagian utama yang diperbaiki
            ══════════════════════════════════════════════════════════ -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200">

                <!-- Header Dokumen + Statistik -->
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-900 flex items-center gap-2">
                        <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" />
                            <polyline points="14 2 14 8 20 8" />
                        </svg>
                        Dokumen
                    </h3>
                    <!-- ✅ Statistik dengan ikon -->
                    <div class="flex items-center gap-3 text-xs">
                        <span class="inline-flex items-center gap-1 font-semibold" style="color:hsl(142,60%,28%);">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path d="M22 11.08V12a10 10 0 11-5.93-9.14" />
                                <polyline points="22 4 12 14.01 9 11.01" />
                            </svg>
                            <?= $dokumenStats['approved'] ?> disetujui
                        </span>
                        <span class="inline-flex items-center gap-1 font-semibold" style="color:hsl(0,55%,40%);">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="10" />
                                <line x1="15" y1="9" x2="9" y2="15" />
                                <line x1="9" y1="9" x2="15" y2="15" />
                            </svg>
                            <?= $dokumenStats['rejected'] ?> ditolak
                        </span>
                        <span class="inline-flex items-center gap-1" style="color:hsl(220,15%,55%);">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="10" />
                                <polyline points="12 6 12 12 16 14" />
                            </svg>
                            <?= $dokumenStats['pending'] ?> menunggu
                        </span>
                    </div>
                </div>

                <!-- Toolbar Bulk Action -->
                <?php if (in_array($p->status, ['submitted', 'verifikasi']) && $totalDok > 0): ?>
                    <div class="px-5 py-2.5 flex items-center justify-between gap-3 flex-wrap"
                        style="background:#f8fafc;border-top:1px solid #f1f5f9;border-bottom:1px solid #f1f5f9;">
                        <div class="flex items-center gap-2.5">
                            <input type="checkbox" id="checkAll"
                                class="w-4 h-4 rounded border-gray-300 cursor-pointer"
                                style="accent-color:hsl(220,54%,20%);"
                                @change="toggleSelectAll($event.target.checked)">
                            <label for="checkAll" class="text-xs font-semibold text-gray-600 cursor-pointer select-none">
                                Pilih Semua
                            </label>
                            <span x-show="selectedDocs.length > 0"
                                class="text-xs text-gray-400"
                                x-text="'(' + selectedDocs.length + ' dipilih)'"></span>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button"
                                x-show="selectedDocs.length > 0"
                                @click="openBulkApprove()"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-white transition"
                                style="background:hsl(142,55%,38%);"
                                onmouseover="this.style.background='hsl(142,55%,30%)'"
                                onmouseout="this.style.background='hsl(142,55%,38%)'">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path d="M20 6L9 17l-5-5" />
                                </svg>
                                Setujui Dipilih
                            </button>
                            <button type="button"
                                x-show="selectedDocs.length > 0"
                                @click="openBulkReject()"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-white transition"
                                style="background:hsl(0,60%,48%);"
                                onmouseover="this.style.background='hsl(0,60%,40%)'"
                                onmouseout="this.style.background='hsl(0,60%,48%)'">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <line x1="18" y1="6" x2="6" y2="18" />
                                    <line x1="6" y1="6" x2="18" y2="18" />
                                </svg>
                                Tolak Dipilih
                            </button>
                            <span x-show="selectedDocs.length === 0"
                                class="text-xs italic" style="color:hsl(220,15%,65%);">
                                Centang dokumen untuk aksi massal
                            </span>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Daftar Dokumen -->
                <div class="divide-y divide-gray-50">
                    <?php foreach ($dokumens as $dok): ?>
                        <?php
                        $sv       = $dok->status_verifikasi ?? 'pending';
                        $isApproved = $sv === 'approved';
                        $isRejected = $sv === 'rejected';
                        $isPending  = $sv === 'pending';
                        ?>
                        <div class="px-5 py-3.5 flex items-center gap-3 dok-row"
                            data-dok-id="<?= $dok->id ?>"
                            :class="selectedDocs.includes(<?= $dok->id ?>) ? 'bg-blue-50 ring-1 ring-inset ring-blue-200' : ''"
                            style="<?= $isRejected ? 'background:hsl(0,72%,51%,.03);' : ($isApproved ? 'background:hsl(142,71%,45%,.02);' : '') ?>">

                            <!-- Checkbox Pilih -->
                            <?php if (in_array($p->status, ['submitted', 'verifikasi'])): ?>
                                <div class="flex-shrink-0">
                                    <input type="checkbox"
                                        :checked="selectedDocs.includes(<?= $dok->id ?>)"
                                        @change="toggleDoc(<?= $dok->id ?>, $event.target.checked)"
                                        class="w-4 h-4 rounded border-gray-300 cursor-pointer"
                                        style="accent-color:hsl(220,54%,20%);">
                                </div>
                            <?php else: ?>
                                <div class="w-4 flex-shrink-0"></div>
                            <?php endif; ?>

                            <!-- Tombol Preview -->
                            <button type="button"
                                @click="viewDokumen(<?= $dok->id ?>, '<?= esc($dok->tipe_mime, 'js') ?>', '<?= esc($dok->nama_file_asli, 'js') ?>')"
                                class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 transition"
                                style="background:hsl(220,20%,96%);"
                                onmouseover="this.style.background='hsl(220,54%,20%,.08)'"
                                onmouseout="this.style.background='hsl(220,20%,96%)'"
                                title="Klik untuk preview dokumen">
                                <?php if (strpos($dok->tipe_mime, 'pdf') !== false): ?>
                                    <svg class="w-5 h-5" style="color:hsl(0,72%,51%);" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" />
                                        <polyline points="14 2 14 8 20 8" />
                                        <line x1="9" y1="13" x2="15" y2="13" />
                                        <line x1="9" y1="17" x2="12" y2="17" />
                                    </svg>
                                <?php else: ?>
                                    <svg class="w-5 h-5" style="color:hsl(199,89%,45%);" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
                                        <circle cx="8.5" cy="8.5" r="1.5" />
                                        <polyline points="21 15 16 10 5 21" />
                                    </svg>
                                <?php endif; ?>
                            </button>

                            <!-- Info Dokumen -->
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-semibold" style="color:hsl(220,54%,15%);">
                                    <?= esc(jenis_dokumen_label($dok->jenis_dokumen)) ?>
                                </p>
                                <p class="text-xs truncate mt-0.5" style="color:hsl(220,15%,55%);">
                                    <?= esc($dok->nama_file_asli) ?> &bull; <?= human_filesize($dok->ukuran_file ?? 0) ?>
                                </p>
                                <?php if ($dok->catatan_verifikasi): ?>
                                    <p class="text-xs mt-1 flex items-center gap-1" style="color:hsl(0,55%,40%);">
                                        <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <circle cx="12" cy="12" r="10" />
                                            <line x1="12" y1="8" x2="12" y2="12" />
                                            <line x1="12" y1="16" x2="12.01" y2="16" />
                                        </svg>
                                        <?= esc($dok->catatan_verifikasi) ?>
                                    </p>
                                <?php endif; ?>
                            </div>

                            <!-- ══════════════════════════════════════════════
                                 STATUS BADGE + TOMBOL AKSI
                                 Ini bagian utama yang diperbaiki
                            ══════════════════════════════════════════════ -->
                            <div class="flex items-center gap-2 flex-shrink-0">

                                <!-- Badge Status Dokumen (dengan ikon) -->
                                <?php if ($isApproved): ?>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold"
                                        style="background:hsl(142,71%,45%,.12);color:hsl(142,60%,28%);border:1px solid hsl(142,71%,45%,.3);">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <path d="M22 11.08V12a10 10 0 11-5.93-9.14" />
                                            <polyline points="22 4 12 14.01 9 11.01" />
                                        </svg>
                                        Valid
                                    </span>
                                <?php elseif ($isRejected): ?>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold"
                                        style="background:hsl(0,72%,51%,.1);color:hsl(0,55%,40%);border:1px solid hsl(0,72%,51%,.3);">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <circle cx="12" cy="12" r="10" />
                                            <line x1="15" y1="9" x2="9" y2="15" />
                                            <line x1="9" y1="9" x2="15" y2="15" />
                                        </svg>
                                        Ditolak
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium"
                                        style="background:hsl(38,92%,50%,.12);color:hsl(38,60%,30%);border:1px solid hsl(38,92%,50%,.3);">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <circle cx="12" cy="12" r="10" />
                                            <polyline points="12 6 12 12 16 14" />
                                        </svg>
                                        Menunggu
                                    </span>
                                <?php endif; ?>

                                <!-- ✅ TOMBOL SETUJUI — tampil jika belum approved -->
                                <?php if (in_array($p->status, ['submitted', 'verifikasi']) && ! $isApproved): ?>
                                    <button type="button"
                                        @click="openApproveModal(<?= $dok->id ?>, <?= $p->id ?>, '<?= esc(jenis_dokumen_label($dok->jenis_dokumen), 'js') ?>')"
                                        class="btn-approve"
                                        title="Setujui dokumen ini">
                                        <!-- Ikon centang -->
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <path d="M20 6L9 17l-5-5" />
                                        </svg>
                                        Setujui
                                    </button>
                                <?php endif; ?>

                                <!-- ✅ TOMBOL TOLAK — tampil jika belum rejected -->
                                <?php if (in_array($p->status, ['submitted', 'verifikasi']) && ! $isRejected): ?>
                                    <button type="button"
                                        @click="openReject(<?= $dok->id ?>, <?= $p->id ?>, '<?= esc(jenis_dokumen_label($dok->jenis_dokumen), 'js') ?>')"
                                        class="btn-reject"
                                        title="Tolak dokumen ini">
                                        <!-- Ikon silang -->
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <line x1="18" y1="6" x2="6" y2="18" />
                                            <line x1="6" y1="6" x2="18" y2="18" />
                                        </svg>
                                        Tolak
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <?php if (empty($dokumens)): ?>
                        <div class="px-5 py-8 text-center">
                            <svg class="w-10 h-10 mx-auto mb-2" style="color:hsl(220,20%,80%);" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
                                <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" />
                                <polyline points="14 2 14 8 20 8" />
                            </svg>
                            <p class="text-xs" style="color:hsl(220,15%,55%);">Belum ada dokumen yang diupload oleh calon siswa.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Action Area -->
                <?php if (in_array($p->status, ['submitted', 'verifikasi'])): ?>
                    <div class="px-5 py-4 border-t border-gray-100 space-y-3">

                        <!-- Catatan Admin -->
                        <div class="space-y-2">
                            <h4 class="text-xs font-semibold text-gray-500 uppercase flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z" />
                                </svg>
                                Catatan untuk Calon Siswa
                            </h4>
                            <textarea x-model="catatanAdmin" rows="3"
                                placeholder="Tulis catatan jika ada dokumen yang perlu diperbaiki..."
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 resize-none"></textarea>
                            <button type="button"
                                @click="kirimCatatan(<?= $p->id ?>)"
                                class="w-full py-2.5 text-white text-sm font-semibold rounded-xl transition flex items-center justify-center gap-2"
                                style="background:hsl(220,54%,20%);"
                                onmouseover="this.style.background='hsl(220,54%,28%)'"
                                onmouseout="this.style.background='hsl(220,54%,20%)'">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <line x1="22" y1="2" x2="11" y2="13" />
                                    <polygon points="22 2 15 22 11 13 2 9 22 2" />
                                </svg>
                                Simpan & Kirim Notifikasi
                            </button>
                        </div>

                        <hr class="border-gray-100">

                        <!-- Approve Semua -->
                        <?php if ($canApproveSemua): ?>
                            <button type="button"
                                @click="openApproveSemua(<?= $p->id ?>)"
                                class="w-full py-2.5 text-white text-sm font-semibold rounded-xl transition flex items-center justify-center gap-2"
                                style="background:hsl(142,60%,35%);"
                                onmouseover="this.style.background='hsl(142,60%,28%)'"
                                onmouseout="this.style.background='hsl(142,60%,35%)'">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path d="M22 11.08V12a10 10 0 11-5.93-9.14" />
                                    <polyline points="22 4 12 14.01 9 11.01" />
                                    <polyline points="16 4 12 8.01 10 6.01" />
                                </svg>
                                Setujui Semua &amp; Masukkan ke Seleksi
                            </button>
                        <?php else: ?>
                            <div class="text-xs text-gray-400 text-center py-2.5 rounded-xl flex items-center justify-center gap-1.5"
                                style="background:hsl(220,20%,96%);border:1px dashed hsl(220,20%,85%);">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="10" />
                                    <line x1="12" y1="8" x2="12" y2="12" />
                                    <line x1="12" y1="16" x2="12.01" y2="16" />
                                </svg>
                                Setujui atau tolak semua dokumen terlebih dahulu sebelum bisa memproses ke seleksi.
                            </div>
                        <?php endif; ?>

                        <!-- Kembalikan untuk Revisi -->
                        <button type="button"
                            @click="openTolak()"
                            class="w-full py-2.5 text-sm font-medium rounded-xl transition flex items-center justify-center gap-2 border"
                            style="background:hsl(0,72%,51%,.06);color:hsl(0,55%,40%);border-color:hsl(0,72%,51%,.25);"
                            onmouseover="this.style.background='hsl(0,72%,51%,.14)'"
                            onmouseout="this.style.background='hsl(0,72%,51%,.06)'">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <polyline points="1 4 1 10 7 10" />
                                <path d="M3.51 15a9 9 0 102.13-9.36L1 10" />
                            </svg>
                            Kembalikan untuk Revisi
                        </button>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Riwayat Aktivitas -->
            <?php if (! empty($logs)): ?>
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 px-5 py-4">
                    <h4 class="text-xs font-semibold text-gray-500 uppercase mb-3 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10" />
                            <polyline points="12 6 12 12 16 14" />
                        </svg>
                        Riwayat Aktivitas
                    </h4>
                    <div class="space-y-2">
                        <?php foreach ($logs as $log): ?>
                            <div class="flex items-start gap-2 text-xs">
                                <div class="w-1.5 h-1.5 rounded-full mt-1.5 flex-shrink-0" style="background:hsl(220,54%,40%);"></div>
                                <div>
                                    <span class="font-medium text-gray-700"><?= esc($log->admin_name) ?></span>
                                    <span class="text-gray-400"> — </span>
                                    <span class="font-medium text-gray-700"><?= esc(str_replace('_', ' ', $log->aksi)) ?></span>
                                    <?php if ($log->keterangan): ?>
                                        <p class="text-gray-400 italic mt-0.5">"<?= esc($log->keterangan) ?>"</p>
                                    <?php endif; ?>
                                    <p class="mt-0.5" style="color:hsl(220,20%,70%);"><?= date('d/m/Y H:i', strtotime($log->created_at)) ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- ── RIGHT: Viewer Dokumen ─────────────────────────────────── -->
        <div class="sticky top-20 max-h-screen overflow-y-auto">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden" style="min-height:min(600px,60vh);">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-gray-900 flex items-center gap-2">
                        <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                            <circle cx="12" cy="12" r="3" />
                        </svg>
                        <span x-text="viewerTitle || 'Viewer Dokumen'"></span>
                    </h3>
                    <div class="flex items-center gap-2">
                        <!-- Zoom controls -->
                        <template x-if="viewerSrc && viewerType === 'image'">
                            <div class="flex items-center gap-1">
                                <button @click="zoom = Math.max(50, zoom - 25)"
                                    class="w-7 h-7 border border-gray-300 rounded-lg text-xs text-gray-600 hover:bg-gray-50 flex items-center justify-center">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <circle cx="11" cy="11" r="8" />
                                        <line x1="21" y1="21" x2="16.65" y2="16.65" />
                                        <line x1="8" y1="11" x2="14" y2="11" />
                                    </svg>
                                </button>
                                <span class="text-xs text-gray-500 w-10 text-center" x-text="zoom + '%'"></span>
                                <button @click="zoom = Math.min(200, zoom + 25)"
                                    class="w-7 h-7 border border-gray-300 rounded-lg text-xs text-gray-600 hover:bg-gray-50 flex items-center justify-center">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <circle cx="11" cy="11" r="8" />
                                        <line x1="21" y1="21" x2="16.65" y2="16.65" />
                                        <line x1="11" y1="8" x2="11" y2="14" />
                                        <line x1="8" y1="11" x2="14" y2="11" />
                                    </svg>
                                </button>
                            </div>
                        </template>
                        <button x-show="viewerSrc" @click="clearViewer()"
                            class="text-xs text-gray-400 hover:text-gray-600 px-2 py-1 rounded-lg hover:bg-gray-100 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <line x1="18" y1="6" x2="6" y2="18" />
                                <line x1="6" y1="6" x2="18" y2="18" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Empty State -->
                <div x-show="!viewerSrc" class="flex flex-col items-center justify-center h-96" style="color:hsl(220,20%,78%);">
                    <svg class="w-16 h-16 mb-3" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
                        <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" />
                        <polyline points="14 2 14 8 20 8" />
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" opacity=".4" />
                    </svg>
                    <p class="text-sm">Klik ikon dokumen untuk preview</p>
                </div>

                <!-- PDF Viewer -->
                <div x-show="viewerType === 'pdf'" style="height:700px;">
                    <iframe :src="viewerSrc" class="w-full h-full border-0" x-show="viewerSrc"></iframe>
                </div>

                <!-- Image Viewer -->
                <div x-show="viewerType === 'image'" class="p-4 flex items-center justify-center overflow-auto" style="min-height:400px;">
                    <img :src="viewerSrc"
                        :style="'transform:scale(' + (zoom/100) + ');transform-origin:top center;transition:transform .2s;max-width:100%;'"
                        class="object-contain rounded-xl shadow-sm" x-show="viewerSrc">
                </div>
            </div>

            <!-- Checklist Verifikasi -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 px-5 py-4 mt-4">
                <h4 class="text-xs font-semibold text-gray-500 uppercase mb-3 flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <polyline points="9 11 12 14 22 4" />
                        <path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11" />
                    </svg>
                    Checklist Verifikasi
                </h4>
                <div class="space-y-2">
                    <?php
                    $checkItems = [
                        'Nama di dokumen sesuai data formulir',
                        'NIK / NISN terlihat jelas dan valid',
                        'Dokumen asli (bukan hasil rekayasa)',
                        'Foto / scan jelas dan tidak blur',
                    ];
                    foreach ($checkItems as $ci): ?>
                        <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer select-none group">
                            <input type="checkbox"
                                class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                            <span class="group-hover:text-gray-900 transition"><?= esc($ci) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════════════
         MODAL: KONFIRMASI APPROVE DOKUMEN (pengganti confirm() browser)
    ════════════════════════════════════════════════════════════════════ -->
    <div x-show="approveModal"
        class="fixed inset-0 z-50 flex items-end sm:items-center justify-center sm:p-4"
        style="background:rgba(17,24,39,.5);"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click.self="approveModal = false"
        x-cloak>

        <div x-show="approveModal"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="bg-white rounded-t-2xl sm:rounded-2xl shadow-xl w-full sm:max-w-sm max-h-[90vh] overflow-y-auto">

            <!-- Header -->
            <div class="px-6 pt-6 pb-4">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center mb-4"
                    style="background:hsl(142,71%,45%,.12);">
                    <svg class="w-6 h-6" style="color:hsl(142,60%,35%);" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path d="M22 11.08V12a10 10 0 11-5.93-9.14" />
                        <polyline points="22 4 12 14.01 9 11.01" />
                    </svg>
                </div>
                <h3 class="text-base font-bold" style="color:hsl(220,54%,15%);">Setujui Dokumen?</h3>
                <p class="text-sm mt-1" style="color:hsl(220,15%,50%);">
                    Anda akan menyetujui dokumen
                    <strong x-text="approveDocName" style="color:hsl(220,54%,15%);"></strong>.
                    Tindakan ini dapat diubah kembali jika diperlukan.
                </p>
            </div>

            <!-- Footer Tombol -->
            <div class="px-6 pb-6 flex gap-3">
                <button @click="approveModal = false"
                    class="flex-1 py-2.5 text-sm font-semibold rounded-xl border transition"
                    style="border-color:hsl(220,20%,82%);color:hsl(220,54%,20%);background:white;"
                    onmouseover="this.style.background='hsl(220,20%,96%)'"
                    onmouseout="this.style.background='white'">
                    Batal
                </button>
                <button @click="submitApprove()"
                    :disabled="approveLoading"
                    class="flex-1 py-2.5 text-sm font-semibold text-white rounded-xl transition flex items-center justify-center gap-2"
                    style="background:hsl(142,60%,35%);"
                    onmouseover="this.style.background='hsl(142,60%,28%)'"
                    onmouseout="this.style.background='hsl(142,60%,35%)'">
                    <template x-if="approveLoading">
                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z" />
                        </svg>
                    </template>
                    <svg x-show="!approveLoading" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path d="M20 6L9 17l-5-5" />
                    </svg>
                    <span x-text="approveLoading ? 'Memproses...' : 'Ya, Setujui'"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════════════
         MODAL: KONFIRMASI APPROVE SEMUA
    ════════════════════════════════════════════════════════════════════ -->
    <div x-show="approveSemua_Modal"
        class="fixed inset-0 z-50 flex items-end sm:items-center justify-center sm:p-4"
        style="background:rgba(17,24,39,.5);"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        @click.self="approveSemua_Modal = false"
        x-cloak>

        <div x-show="approveSemua_Modal"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
            class="bg-white rounded-t-2xl sm:rounded-2xl shadow-xl w-full sm:max-w-sm max-h-[90vh] overflow-y-auto">

            <div class="px-6 pt-6 pb-4">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center mb-4"
                    style="background:hsl(142,71%,45%,.12);">
                    <svg class="w-6 h-6" style="color:hsl(142,60%,35%);" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M22 11.08V12a10 10 0 11-5.93-9.14" />
                        <polyline points="22 4 12 14.01 9 11.01" />
                        <polyline points="16 4 12 8.01 10 6.01" />
                    </svg>
                </div>
                <h3 class="text-base font-bold" style="color:hsl(220,54%,15%);">Setujui Semua &amp; Masuk Seleksi?</h3>
                <p class="text-sm mt-1" style="color:hsl(220,15%,50%);">
                    Semua dokumen akan disetujui dan pendaftar ini akan dimasukkan ke tahap seleksi. Tindakan ini <strong>tidak dapat dibatalkan.</strong>
                </p>
            </div>

            <div class="px-6 pb-6 flex gap-3">
                <button @click="approveSemua_Modal = false"
                    class="flex-1 py-2.5 text-sm font-semibold rounded-xl border transition"
                    style="border-color:hsl(220,20%,82%);color:hsl(220,54%,20%);background:white;"
                    onmouseover="this.style.background='hsl(220,20%,96%)'" onmouseout="this.style.background='white'">
                    Batal
                </button>
                <button @click="submitApproveSemua()"
                    :disabled="approveSemua_Loading"
                    class="flex-1 py-2.5 text-sm font-semibold text-white rounded-xl transition flex items-center justify-center gap-2"
                    style="background:hsl(142,60%,35%);"
                    onmouseover="this.style.background='hsl(142,60%,28%)'" onmouseout="this.style.background='hsl(142,60%,35%)'">
                    <template x-if="approveSemua_Loading">
                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z" />
                        </svg>
                    </template>
                    <span x-text="approveSemua_Loading ? 'Memproses...' : 'Ya, Setujui Semua'"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════════════
         MODAL: TOLAK / REJECT DOKUMEN
    ════════════════════════════════════════════════════════════════════ -->
    <div x-show="rejectModal"
        class="fixed inset-0 z-50 flex items-end sm:items-center justify-center sm:p-4"
        style="background:rgba(17,24,39,.5);"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        @click.self="rejectModal = false"
        x-cloak>

        <div x-show="rejectModal"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
            class="bg-white rounded-t-2xl sm:rounded-2xl shadow-xl w-full sm:max-w-sm max-h-[90vh] overflow-y-auto">

            <div class="px-6 pt-6 pb-4">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center mb-4"
                    style="background:hsl(0,72%,51%,.1);">
                    <svg class="w-6 h-6" style="color:hsl(0,55%,40%);" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10" />
                        <line x1="15" y1="9" x2="9" y2="15" />
                        <line x1="9" y1="9" x2="15" y2="15" />
                    </svg>
                </div>
                <h3 class="text-base font-bold" style="color:hsl(220,54%,15%);">Tolak Dokumen</h3>
                <p class="text-sm mt-1 mb-4" style="color:hsl(220,15%,50%);">
                    Tolak dokumen <strong x-text="rejectDocName" style="color:hsl(220,54%,15%);"></strong>.
                    Catatan ini akan dikirimkan ke calon siswa.
                </p>
                <textarea x-model="rejectCatatan" rows="3"
                    placeholder="Alasan penolakan (wajib diisi)..."
                    class="w-full px-4 py-3 border rounded-xl text-sm focus:outline-none resize-none transition"
                    style="border-color:hsl(220,20%,82%);font-family:inherit;"
                    :style="rejectCatatan.trim() === '' && rejectTried ? 'border-color:hsl(0,72%,51%);box-shadow:0 0 0 2px hsl(0,72%,51%,.15);' : ''">
                </textarea>
                <p x-show="rejectCatatan.trim() === '' && rejectTried"
                    class="text-xs mt-1" style="color:hsl(0,55%,40%);">
                    Alasan penolakan wajib diisi.
                </p>
            </div>

            <div class="px-6 pb-6 flex gap-3">
                <button @click="rejectModal = false; rejectTried = false;"
                    class="flex-1 py-2.5 text-sm font-semibold rounded-xl border transition"
                    style="border-color:hsl(220,20%,82%);color:hsl(220,54%,20%);background:white;"
                    onmouseover="this.style.background='hsl(220,20%,96%)'" onmouseout="this.style.background='white'">
                    Batal
                </button>
                <button @click="submitReject()"
                    :disabled="rejectLoading"
                    class="flex-1 py-2.5 text-sm font-semibold text-white rounded-xl transition flex items-center justify-center gap-2"
                    style="background:hsl(0,55%,40%);"
                    onmouseover="this.style.background='hsl(0,55%,32%)'" onmouseout="this.style.background='hsl(0,55%,40%)'">
                    <template x-if="rejectLoading">
                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z" />
                        </svg>
                    </template>
                    <svg x-show="!rejectLoading" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <line x1="18" y1="6" x2="6" y2="18" />
                        <line x1="6" y1="6" x2="18" y2="18" />
                    </svg>
                    <span x-text="rejectLoading ? 'Memproses...' : 'Tolak Dokumen'"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════════════
         MODAL: KEMBALIKAN UNTUK REVISI (tolak pendaftaran)
    ════════════════════════════════════════════════════════════════════ -->
    <div x-show="tolakModal"
        class="fixed inset-0 z-50 flex items-end sm:items-center justify-center sm:p-4"
        style="background:rgba(17,24,39,.5);"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        @click.self="tolakModal = false"
        x-cloak>

        <div x-show="tolakModal"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
            class="bg-white rounded-t-2xl sm:rounded-2xl shadow-xl w-full sm:max-w-sm max-h-[90vh] overflow-y-auto">

            <div class="px-6 pt-6 pb-4">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center mb-4"
                    style="background:hsl(38,92%,50%,.12);">
                    <svg class="w-6 h-6" style="color:hsl(38,60%,35%);" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <polyline points="1 4 1 10 7 10" />
                        <path d="M3.51 15a9 9 0 102.13-9.36L1 10" />
                    </svg>
                </div>
                <h3 class="text-base font-bold" style="color:hsl(220,54%,15%);">Kembalikan untuk Revisi?</h3>
                <p class="text-sm mt-1 mb-4" style="color:hsl(220,15%,50%);">
                    Pendaftar akan diminta memperbaiki formulir atau dokumen. Tulis alasan dengan jelas.
                </p>
                <textarea x-model="tolakAlasan" rows="3"
                    placeholder="Alasan (wajib diisi)..."
                    class="w-full px-4 py-3 border rounded-xl text-sm focus:outline-none resize-none"
                    style="border-color:hsl(220,20%,82%);font-family:inherit;"></textarea>
            </div>

            <form :action="`<?= base_url('admin/verifikasi/') ?>${pendaftaranId}/tolak`"
                method="POST" id="formTolak">
                <?= csrf_field() ?>
                <input type="hidden" name="alasan" :value="tolakAlasan">
                <div class="px-6 pb-6 flex gap-3">
                    <button type="button" @click="tolakModal = false"
                        class="flex-1 py-2.5 text-sm font-semibold rounded-xl border transition"
                        style="border-color:hsl(220,20%,82%);color:hsl(220,54%,20%);background:white;"
                        onmouseover="this.style.background='hsl(220,20%,96%)'" onmouseout="this.style.background='white'">
                        Batal
                    </button>
                    <button type="button" @click="submitTolak()"
                        class="flex-1 py-2.5 text-sm font-semibold text-white rounded-xl transition flex items-center justify-center gap-2"
                        style="background:hsl(38,70%,40%);"
                        onmouseover="this.style.background='hsl(38,70%,32%)'" onmouseout="this.style.background='hsl(38,70%,40%)'">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <polyline points="1 4 1 10 7 10" />
                            <path d="M3.51 15a9 9 0 102.13-9.36L1 10" />
                        </svg>
                        Kirim Revisi
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════════════
         TOAST NOTIFIKASI
    ════════════════════════════════════════════════════════════════════ -->
    <div x-show="toastVisible"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-4"
        class="fixed bottom-4 right-4 z-50 w-[min(20rem,calc(100vw-2rem))] rounded-2xl shadow-xl overflow-hidden"
        :style="toastSuccess ? 'border:1px solid hsl(142,71%,45%,.3);background:white;' : 'border:1px solid hsl(0,72%,51%,.3);background:white;'"
        x-cloak>
        <div class="flex items-start gap-3 p-4 pr-10 relative">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0"
                :style="toastSuccess ? 'background:hsl(142,71%,45%,.12);' : 'background:hsl(0,72%,51%,.1);'">
                <template x-if="toastSuccess">
                    <svg class="w-5 h-5" style="color:hsl(142,60%,35%);" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path d="M22 11.08V12a10 10 0 11-5.93-9.14" />
                        <polyline points="22 4 12 14.01 9 11.01" />
                    </svg>
                </template>
                <template x-if="!toastSuccess">
                    <svg class="w-5 h-5" style="color:hsl(0,55%,40%);" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10" />
                        <line x1="12" y1="8" x2="12" y2="12" />
                        <line x1="12" y1="16" x2="12.01" y2="16" />
                    </svg>
                </template>
            </div>
            <div>
                <p class="font-semibold text-sm" style="color:hsl(220,54%,15%);" x-text="toastSuccess ? 'Berhasil' : 'Gagal'"></p>
                <p class="text-xs mt-0.5" style="color:hsl(220,15%,50%);" x-text="toastMsg"></p>
            </div>
            <button @click="toastVisible = false"
                class="absolute top-3 right-3 w-6 h-6 rounded-lg flex items-center justify-center"
                style="color:hsl(220,15%,55%);">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <line x1="18" y1="6" x2="6" y2="18" />
                    <line x1="6" y1="6" x2="18" y2="18" />
                </svg>
            </button>
        </div>
        <!-- Progress bar auto-dismiss -->
        <div class="h-1" :style="toastSuccess ? 'background:hsl(220,20%,92%);' : 'background:hsl(220,20%,92%);'">
            <div x-ref="toastBar" class="h-full"
                :style="(toastSuccess ? 'background:hsl(142,71%,45%);' : 'background:hsl(0,55%,40%);') + 'width:100%;transition:width 3.5s linear;'">
            </div>
        </div>
    </div>


    <!-- ══════════════════════════════════════════════════════════════════
         MODAL: BULK APPROVE
    ════════════════════════════════════════════════════════════════════ -->
    <div x-show="bulkApproveModal"
        class="fixed inset-0 z-50 flex items-end sm:items-center justify-center sm:p-4"
        style="background:rgba(17,24,39,.5);"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        @click.self="bulkApproveModal = false"
        x-cloak>
        <div x-show="bulkApproveModal"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
            class="bg-white rounded-t-2xl sm:rounded-2xl shadow-xl w-full sm:max-w-sm max-h-[90vh] overflow-y-auto">
            <div class="px-6 pt-6 pb-4">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center mb-4"
                    style="background:hsl(142,71%,45%,.12);">
                    <svg class="w-6 h-6" style="color:hsl(142,60%,35%);" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path d="M22 11.08V12a10 10 0 11-5.93-9.14" />
                        <polyline points="22 4 12 14.01 9 11.01" />
                    </svg>
                </div>
                <h3 class="text-base font-bold" style="color:hsl(220,54%,15%);">Setujui Dokumen Dipilih?</h3>
                <p class="text-sm mt-1" style="color:hsl(220,15%,50%);">
                    Anda akan menyetujui <strong x-text="selectedDocs.length + ' dokumen'" style="color:hsl(220,54%,15%);"></strong> sekaligus.
                    Tindakan ini dapat diubah kembali jika diperlukan.
                </p>
            </div>
            <div class="px-6 pb-6 flex gap-3">
                <button @click="bulkApproveModal = false"
                    class="flex-1 py-2.5 text-sm font-semibold rounded-xl border transition"
                    style="border-color:hsl(220,20%,82%);color:hsl(220,54%,20%);background:white;"
                    onmouseover="this.style.background='hsl(220,20%,96%)'" onmouseout="this.style.background='white'">
                    Batal
                </button>
                <button @click="submitBulkApprove()" :disabled="bulkLoading"
                    class="flex-1 py-2.5 text-sm font-semibold text-white rounded-xl transition flex items-center justify-center gap-2"
                    style="background:hsl(142,60%,35%);"
                    onmouseover="this.style.background='hsl(142,60%,28%)'" onmouseout="this.style.background='hsl(142,60%,35%)'">
                    <template x-if="bulkLoading">
                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z" />
                        </svg>
                    </template>
                    <svg x-show="!bulkLoading" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path d="M20 6L9 17l-5-5" />
                    </svg>
                    <span x-text="bulkLoading ? 'Memproses...' : 'Ya, Setujui Semua'"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════════════
         MODAL: BULK REJECT
    ════════════════════════════════════════════════════════════════════ -->
    <div x-show="bulkRejectModal"
        class="fixed inset-0 z-50 flex items-end sm:items-center justify-center sm:p-4"
        style="background:rgba(17,24,39,.5);"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        @click.self="bulkRejectModal = false; bulkRejectTried = false;"
        x-cloak>
        <div x-show="bulkRejectModal"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
            class="bg-white rounded-t-2xl sm:rounded-2xl shadow-xl w-full sm:max-w-sm max-h-[90vh] overflow-y-auto">
            <div class="px-6 pt-6 pb-4">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center mb-4"
                    style="background:hsl(0,72%,51%,.1);">
                    <svg class="w-6 h-6" style="color:hsl(0,55%,40%);" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10" />
                        <line x1="15" y1="9" x2="9" y2="15" />
                        <line x1="9" y1="9" x2="15" y2="15" />
                    </svg>
                </div>
                <h3 class="text-base font-bold" style="color:hsl(220,54%,15%);">Tolak Dokumen Dipilih?</h3>
                <p class="text-sm mt-1 mb-4" style="color:hsl(220,15%,50%);">
                    Anda akan menolak <strong x-text="selectedDocs.length + ' dokumen'" style="color:hsl(220,54%,15%);"></strong>.
                    Tulis alasan penolakan yang akan dikirim ke calon siswa.
                </p>
                <textarea x-model="bulkRejectCatatan" rows="3"
                    placeholder="Alasan penolakan (wajib diisi)..."
                    class="w-full px-4 py-3 border rounded-xl text-sm focus:outline-none resize-none transition"
                    style="border-color:hsl(220,20%,82%);font-family:inherit;"
                    :style="bulkRejectCatatan.trim() === '' && bulkRejectTried ? 'border-color:hsl(0,72%,51%);box-shadow:0 0 0 2px hsl(0,72%,51%,.15);' : ''">
                </textarea>
                <p x-show="bulkRejectCatatan.trim() === '' && bulkRejectTried"
                    class="text-xs mt-1" style="color:hsl(0,55%,40%);">
                    Alasan penolakan wajib diisi.
                </p>
            </div>
            <div class="px-6 pb-6 flex gap-3">
                <button @click="bulkRejectModal = false; bulkRejectTried = false;"
                    class="flex-1 py-2.5 text-sm font-semibold rounded-xl border transition"
                    style="border-color:hsl(220,20%,82%);color:hsl(220,54%,20%);background:white;"
                    onmouseover="this.style.background='hsl(220,20%,96%)'" onmouseout="this.style.background='white'">
                    Batal
                </button>
                <button @click="submitBulkReject()" :disabled="bulkLoading"
                    class="flex-1 py-2.5 text-sm font-semibold text-white rounded-xl transition flex items-center justify-center gap-2"
                    style="background:hsl(0,55%,40%);"
                    onmouseover="this.style.background='hsl(0,55%,32%)'" onmouseout="this.style.background='hsl(0,55%,40%)'">
                    <template x-if="bulkLoading">
                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z" />
                        </svg>
                    </template>
                    <svg x-show="!bulkLoading" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <line x1="18" y1="6" x2="6" y2="18" />
                        <line x1="6" y1="6" x2="18" y2="18" />
                    </svg>
                    <span x-text="bulkLoading ? 'Memproses...' : 'Tolak Dokumen Dipilih'"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- ══ NAVIGASI PENDAFTAR (BAWAH) ════════════════════════════════════ -->
    <div class="flex flex-wrap items-center justify-between gap-3 mt-2">

        <!-- Pendaftar Sebelumnya -->
        <?php if ($prevId): ?>
            <a href="<?= base_url('admin/verifikasi/' . $prevId) ?>"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-medium border transition"
                style="border-color:hsl(220,20%,82%);color:hsl(220,54%,20%);background:white;"
                onmouseover="this.style.background='hsl(220,20%,96%)'"
                onmouseout="this.style.background='white'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <polyline points="15 18 9 12 15 6" />
                </svg>
                Pendaftar Sebelumnya
            </a>
        <?php else: ?>
            <span class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-medium border cursor-not-allowed"
                style="border-color:hsl(220,20%,90%);color:hsl(220,20%,70%);background:hsl(220,20%,97%);">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <polyline points="15 18 9 12 15 6" />
                </svg>
                Pendaftar Sebelumnya
            </span>
        <?php endif; ?>

        <!-- Posisi saat ini -->
        <span class="text-sm" style="color:hsl(220,15%,55%);">
            <?php
            // Hitung posisi: ambil urutan dari semua pendaftaran (selain draft), urutkan submitted_at DESC
            $db = db_connect();
            $allIds = $db->table('pendaftaran')
                ->select('id')
                ->whereNotIn('status', ['draft'])
                ->where('deleted_at IS NULL')
                ->orderBy('submitted_at', 'DESC')
                ->get()->getResultArray();
            $allIds   = array_column($allIds, 'id');
            $position = array_search((string)$p->id, array_map('strval', $allIds));
            $posLabel = $position !== false ? ($position + 1) . ' dari ' . count($allIds) . ' pendaftar' : '';
            ?>
            <?= esc($posLabel) ?>
        </span>

        <!-- Pendaftar Selanjutnya -->
        <?php if ($nextId): ?>
            <a href="<?= base_url('admin/verifikasi/' . $nextId) ?>"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-white rounded-xl transition"
                style="background:hsl(220,54%,20%);"
                onmouseover="this.style.background='hsl(220,54%,28%)'"
                onmouseout="this.style.background='hsl(220,54%,20%)'">
                Pendaftar Selanjutnya
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <polyline points="9 18 15 12 9 6" />
                </svg>
            </a>
        <?php else: ?>
            <span class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-medium border cursor-not-allowed"
                style="border-color:hsl(220,20%,90%);color:hsl(220,20%,70%);background:hsl(220,20%,97%);">
                Pendaftar Selanjutnya
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <polyline points="9 18 15 12 9 6" />
                </svg>
            </span>
        <?php endif; ?>

    </div>

</div><!-- /x-data="verifikasiDetail()" -->

<script>
    function verifikasiDetail() {
        return {
            /* ── Viewer ─────────────────────────────────────────────── */
            viewerSrc: '',
            viewerType: '',
            viewerTitle: '',
            zoom: 100,

            /* ── Modal Approve Dokumen ───────────────────────────────── */
            approveModal: false,
            approveDokumenId: null,
            approvePendId: null,
            approveDocName: '',
            approveLoading: false,

            /* ── Modal Approve Semua ─────────────────────────────────── */
            approveSemua_Modal: false,
            approveSemua_PendId: null,
            approveSemua_Loading: false,

            /* ── Modal Reject Dokumen ────────────────────────────────── */
            rejectModal: false,
            rejectDokumenId: null,
            rejectPendId: null,
            rejectDocName: '',
            rejectCatatan: '',
            rejectTried: false,
            rejectLoading: false,

            /* ── Modal Tolak Pendaftaran ─────────────────────────────── */
            tolakModal: false,
            tolakAlasan: '',

            /* ── Catatan Admin ───────────────────────────────────────── */
            catatanAdmin: '<?= esc($pendaftaran->catatan_admin ?? '', 'js') ?>',

            /* ── ID pendaftaran aktif ─────────────────────────────────── */
            pendaftaranId: <?= (int)$p->id ?>,

            /* ── Toast ───────────────────────────────────────────────── */
            toastVisible: false,
            toastMsg: '',
            toastSuccess: true,

            /* ── Bulk Selection ─────────────────────────────────────── */
            selectedDocs: [],
            bulkApproveModal: false,
            bulkRejectModal: false,
            bulkRejectCatatan: '',
            bulkRejectTried: false,
            bulkLoading: false,

            /* ═══════════════════════════════════════════════════════════
               VIEWER
            ═══════════════════════════════════════════════════════════ */
            viewDokumen(id, mime, nama) {
                this.viewerTitle = nama;
                this.viewerSrc = `<?= base_url('admin/dokumen/') ?>${id}`;
                this.viewerType = mime.includes('pdf') ? 'pdf' : 'image';
                this.zoom = 100;
            },
            clearViewer() {
                this.viewerSrc = '';
                this.viewerType = '';
                this.viewerTitle = '';
            },

            /* ═══════════════════════════════════════════════════════════
               SELECTION — pilih per dokumen / pilih semua
            ═══════════════════════════════════════════════════════════ */
            toggleDoc(id, checked) {
                if (checked) {
                    if (!this.selectedDocs.includes(id)) this.selectedDocs.push(id);
                } else {
                    this.selectedDocs = this.selectedDocs.filter(d => d !== id);
                }
            },
            toggleSelectAll(checked) {
                const ids = [...document.querySelectorAll('.dok-row[data-dok-id]')]
                    .map(el => parseInt(el.dataset.dokId))
                    .filter(id => !isNaN(id));
                this.selectedDocs = checked ? ids : [];
            },

            /* ═══════════════════════════════════════════════════════════
               BULK APPROVE
            ═══════════════════════════════════════════════════════════ */
            openBulkApprove() {
                this.bulkLoading = false;
                this.bulkApproveModal = true;
            },
            async submitBulkApprove() {
                if (this.bulkLoading) return;
                this.bulkLoading = true;
                let anyFail = false;
                for (const id of this.selectedDocs) {
                    const res = await this._post(
                        `<?= base_url('admin/verifikasi/') ?>${this.pendaftaranId}/approve-dokumen`, {
                            dokumen_id: id
                        }
                    );
                    const data = await res.json();
                    if (!data.success) anyFail = true;
                }
                this.bulkApproveModal = false;
                this.bulkLoading = false;
                this.selectedDocs = [];
                this._showToast(
                    anyFail ? 'Beberapa dokumen gagal disetujui.' : 'Dokumen terpilih berhasil disetujui!',
                    !anyFail
                );
                setTimeout(() => location.reload(), 1200);
            },

            /* ═══════════════════════════════════════════════════════════
               BULK REJECT
            ═══════════════════════════════════════════════════════════ */
            openBulkReject() {
                this.bulkLoading = false;
                this.bulkRejectCatatan = '';
                this.bulkRejectTried = false;
                this.bulkRejectModal = true;
            },
            async submitBulkReject() {
                this.bulkRejectTried = true;
                if (!this.bulkRejectCatatan.trim()) return;
                if (this.bulkLoading) return;
                this.bulkLoading = true;
                let anyFail = false;
                for (const id of this.selectedDocs) {
                    const res = await this._post(
                        `<?= base_url('admin/verifikasi/') ?>${this.pendaftaranId}/reject-dokumen`, {
                            dokumen_id: id,
                            catatan: this.bulkRejectCatatan
                        }
                    );
                    const data = await res.json();
                    if (!data.success) anyFail = true;
                }
                this.bulkRejectModal = false;
                this.bulkLoading = false;
                this.selectedDocs = [];
                this._showToast(
                    anyFail ? 'Beberapa dokumen gagal ditolak.' : 'Dokumen terpilih berhasil ditolak.',
                    !anyFail
                );
                setTimeout(() => location.reload(), 1200);
            },

            /* ═══════════════════════════════════════════════════════════
               APPROVE DOKUMEN — buka modal konfirmasi dulu
            ═══════════════════════════════════════════════════════════ */
            openApproveModal(dokumenId, pendaftaranId, docName) {
                this.approveDokumenId = dokumenId;
                this.approvePendId = pendaftaranId;
                this.approveDocName = docName;
                this.approveLoading = false;
                this.approveModal = true;
            },
            async submitApprove() {
                if (this.approveLoading) return;
                this.approveLoading = true;
                const res = await this._post(
                    `<?= base_url('admin/verifikasi/') ?>${this.approvePendId}/approve-dokumen`, {
                        dokumen_id: this.approveDokumenId
                    }
                );
                const data = await res.json();
                this.approveModal = false;
                this.approveLoading = false;
                this._showToast(data.message, data.success);
                if (data.success) setTimeout(() => location.reload(), 1200);
            },

            /* ═══════════════════════════════════════════════════════════
               APPROVE SEMUA — buka modal konfirmasi dulu
            ═══════════════════════════════════════════════════════════ */
            openApproveSemua(pendaftaranId) {
                this.approveSemua_PendId = pendaftaranId;
                this.approveSemua_Loading = false;
                this.approveSemua_Modal = true;
            },
            async submitApproveSemua() {
                if (this.approveSemua_Loading) return;
                this.approveSemua_Loading = true;
                const res = await this._post(
                    `<?= base_url('admin/verifikasi/') ?>${this.approveSemua_PendId}/approve-semua`, {}
                );
                const data = await res.json();
                this.approveSemua_Modal = false;
                this.approveSemua_Loading = false;
                this._showToast(data.message, data.success);
                if (data.success) setTimeout(() => location.reload(), 1200);
            },

            /* ═══════════════════════════════════════════════════════════
               REJECT DOKUMEN
            ═══════════════════════════════════════════════════════════ */
            openReject(dokumenId, pendaftaranId, docName) {
                this.rejectDokumenId = dokumenId;
                this.rejectPendId = pendaftaranId;
                this.rejectDocName = docName;
                this.rejectCatatan = '';
                this.rejectTried = false;
                this.rejectLoading = false;
                this.rejectModal = true;
            },
            async submitReject() {
                this.rejectTried = true;
                if (!this.rejectCatatan.trim()) return;
                if (this.rejectLoading) return;
                this.rejectLoading = true;
                const res = await this._post(
                    `<?= base_url('admin/verifikasi/') ?>${this.rejectPendId}/reject-dokumen`, {
                        dokumen_id: this.rejectDokumenId,
                        catatan: this.rejectCatatan
                    }
                );
                const data = await res.json();
                this.rejectModal = false;
                this.rejectLoading = false;
                this._showToast(data.message, data.success);
                if (data.success) setTimeout(() => location.reload(), 1200);
            },

            /* ═══════════════════════════════════════════════════════════
               TOLAK / REVISI PENDAFTARAN
            ═══════════════════════════════════════════════════════════ */
            openTolak() {
                this.tolakAlasan = '';
                this.tolakModal = true;
            },
            submitTolak() {
                if (!this.tolakAlasan.trim()) {
                    this._showToast('Alasan revisi wajib diisi.', false);
                    return;
                }
                document.getElementById('formTolak').submit();
            },

            /* ═══════════════════════════════════════════════════════════
               KIRIM CATATAN ADMIN
            ═══════════════════════════════════════════════════════════ */
            async kirimCatatan(pendaftaranId) {
                if (!this.catatanAdmin.trim()) {
                    this._showToast('Catatan tidak boleh kosong!', false);
                    return;
                }
                const res = await this._post(
                    `<?= base_url('admin/verifikasi/') ?>${pendaftaranId}/catatan`, {
                        catatan: this.catatanAdmin
                    }
                );
                const data = await res.json();
                this._showToast(data.message, data.success);
            },

            /* ═══════════════════════════════════════════════════════════
               HELPER: POST dengan CSRF token
            ═══════════════════════════════════════════════════════════ */
            async _post(url, fields) {
                const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
                const form = new FormData();
                form.append('<?= csrf_token() ?>', csrf);
                Object.entries(fields).forEach(([k, v]) => form.append(k, v));
                return fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrf,
                    },
                    body: form,
                });
            },

            /* ═══════════════════════════════════════════════════════════
               HELPER: Toast notification (menggantikan alert())
            ═══════════════════════════════════════════════════════════ */
            _showToast(msg, success = true) {
                this.toastMsg = msg;
                this.toastSuccess = success;
                this.toastVisible = true;
                this.$nextTick(() => {
                    const bar = this.$refs.toastBar;
                    if (bar) {
                        bar.style.transition = 'none';
                        bar.style.width = '100%';
                        void bar.offsetWidth;
                        bar.style.transition = 'width 3.5s linear';
                        bar.style.width = '0%';
                    }
                });
                setTimeout(() => {
                    this.toastVisible = false;
                }, 3800);
            },
        };
    }
</script>