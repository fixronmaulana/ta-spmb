<!-- 
    File: app/Modules/Seleksi/Views/index.php

    PERBAIKAN:
    1. Tombol "Lulus" per-baris membuka modal jurusan diterima
       → admin pilih apakah diterima di pilihan 1 atau pilihan 2
    2. Bulk "Tidak Lulus" tetap ada (tidak perlu pilih jurusan)
    3. Bulk "Luluskan" dihapus dari footer — luluskan harus per siswa
       agar jurusan_diterima_id bisa dipilih dengan benar
    4. Kartu kuota dihitung dari jurusan_diterima_id (bukan pilihan1)
    5. Form hidden menyertakan jurusan_diterima[id] saat submit lulus
-->

<div class="space-y-6" x-data="seleksiPage()">

    <!-- ── Page Header ──────────────────────────────────────────── -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold font-serif">Penetapan Kelulusan</h1>
            <p class="text-sm text-gray-500">Seleksi dan penetapan kelulusan calon siswa</p>
        </div>
        <a href="<?= base_url('admin/laporan/ekspor-excel') ?>"
            class="inline-flex items-center gap-2 border border-gray-300 bg-white rounded-xl px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
            </svg>
            Export Data
        </a>
    </div>

    <!-- Flash messages -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="flex items-start gap-3 px-4 py-3 rounded-xl text-sm"
            style="background:hsl(142,71%,45%,.1);border:1px solid hsl(142,71%,45%,.3);color:hsl(142,55%,28%);">
            <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path d="M22 11.08V12a10 10 0 11-5.93-9.14" />
                <polyline points="22 4 12 14.01 9 11.01" />
            </svg>
            <?= session()->getFlashdata('success') ?>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="flex items-start gap-3 px-4 py-3 rounded-xl text-sm"
            style="background:hsl(0,72%,51%,.08);border:1px solid hsl(0,72%,51%,.25);color:hsl(0,55%,40%);">
            <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <circle cx="12" cy="12" r="10" />
                <line x1="12" y1="8" x2="12" y2="12" />
                <line x1="12" y1="16" x2="12.01" y2="16" />
            </svg>
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>

    <!-- ── Quota Cards ──────────────────────────────────────────── -->
    <!-- count_lulus dihitung dari jurusan_diterima_id → akurat -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
        <?php foreach ($byJurusan as $jid => $data):
            $j          = $data['jurusan'];
            $kuota      = (int) $j->kuota;
            $countLulus = $data['count_lulus'];   // dihitung dari jurusan_diterima_id
            $sisa       = max(0, $kuota - $countLulus);
            $isFull     = $sisa <= 0;
        ?>
            <div class="bg-white rounded-2xl border <?= $isFull ? 'border-red-400' : 'border-gray-200' ?> p-4 text-center">
                <svg class="h-8 w-8 mx-auto mb-2 <?= $isFull ? 'text-red-500' : 'text-blue-700' ?>"
                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.906 59.906 0 0112 3.493a59.903 59.903 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5" />
                </svg>
                <h3 class="font-bold text-gray-900"><?= esc($j->kode) ?></h3>
                <p class="text-xs font-medium text-gray-500 mt-0.5"><?= esc($j->nama) ?></p>
                <div class="text-xs text-gray-500 mt-1 space-y-0.5">
                    <p>Kuota: <span class="font-semibold text-gray-700"><?= $kuota ?></span></p>
                    <p>Pendaftar: <span class="font-semibold text-gray-700"><?= count($data['peserta']) ?></span></p>
                    <p>Diterima: <span class="font-semibold <?= $countLulus > 0 ? 'text-green-700' : 'text-gray-700' ?>"><?= $countLulus ?></span></p>
                </div>
                <span class="inline-flex items-center mt-2 px-2.5 py-0.5 rounded-full text-xs font-semibold
                         <?= $isFull ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-800' ?>">
                    <?= $isFull ? 'PENUH' : 'Sisa: ' . $sisa ?>
                </span>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- ── Info cara penggunaan ──────────────────────────────────── -->
    <div class="flex items-start gap-3 px-4 py-3 rounded-xl text-sm bg-blue-50 border border-blue-200 text-blue-800">
        <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <circle cx="12" cy="12" r="10" />
            <line x1="12" y1="8" x2="12" y2="12" />
            <line x1="12" y1="16" x2="12.01" y2="16" />
        </svg>
        <span>
            <strong>Cara penggunaan:</strong>
            Klik tombol <span class="font-semibold text-green-700">Lulus</span> per-baris untuk memilih jurusan yang diterima (pilihan 1 atau 2).
            Untuk menolak banyak siswa sekaligus, centang kotak lalu klik <span class="font-semibold text-red-600">Tolak Terpilih</span>.
            <br>
            <span class="text-blue-700">🔒 Siswa yang sudah berstatus <strong>Daftar Ulang</strong> atau <strong>Siswa Aktif</strong> terkunci dan tidak bisa diubah lagi dari halaman ini.</span>
        </span>
    </div>

    <!-- ── Filters Card ─────────────────────────────────────────── -->
    <div class="bg-white rounded-2xl border border-gray-200 p-4">
        <div class="flex flex-col sm:flex-row gap-3">
            <!-- Search -->
            <div class="relative flex-1">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z" />
                </svg>
                <input type="text" x-model="search"
                    placeholder="Cari nama atau nomor pendaftaran..."
                    class="w-full pl-9 pr-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Jurusan filter -->
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400 pointer-events-none" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z" />
                </svg>
                <select x-model="majorFilter"
                    class="pl-9 pr-8 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 w-full sm:w-52 appearance-none bg-white">
                    <option value="">Semua Jurusan</option>
                    <?php foreach ($jurusans as $j): ?>
                        <option value="<?= esc($j->kode) ?>"><?= esc($j->kode) ?> — <?= esc($j->nama) ?></option>
                    <?php endforeach; ?>
                </select>
                <svg class="absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400 pointer-events-none" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                </svg>
            </div>

            <!-- Status filter -->
            <div class="relative">
                <select x-model="statusFilter"
                    class="pl-4 pr-8 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 w-full sm:w-44 appearance-none bg-white">
                    <option value="">Semua Status</option>
                    <option value="seleksi">Belum Diproses</option>
                    <option value="lulus">Lulus</option>
                    <option value="tidak_lulus">Tidak Lulus</option>
                </select>
                <svg class="absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400 pointer-events-none" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                </svg>
            </div>
        </div>
    </div>

    <!-- ── Applicants Table Card ─────────────────────────────────── -->
    <div class="bg-white rounded-2xl border border-gray-200">

        <!-- Card Header -->
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                </svg>
                <h3 class="font-semibold text-gray-900">Daftar Pendaftar Terverifikasi</h3>
            </div>
            <span x-show="selectedCount > 0"
                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-700"
                x-text="selectedCount + ' dipilih'"></span>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full min-w-[800px]">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="py-3 px-4 text-left w-10">
                            <input type="checkbox" x-model="selectAll" @change="toggleAll()"
                                class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                        </th>
                        <th class="py-3 px-4 text-left text-sm font-medium text-gray-500">No. Pendaftaran</th>
                        <th class="py-3 px-4 text-left text-sm font-medium text-gray-500">Nama</th>
                        <th class="py-3 px-4 text-left text-sm font-medium text-gray-500">Pilihan Jurusan</th>
                        <th class="py-3 px-4 text-left text-sm font-medium text-gray-500">Jurusan Diterima</th>
                        <th class="py-3 px-4 text-left text-sm font-medium text-gray-500">Status</th>
                        <th class="py-3 px-4 text-left text-sm font-medium text-gray-500">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($peserta as $p): ?>
                        <tr class="border-b border-gray-50 last:border-0 hover:bg-gray-50 transition-colors"
                            x-show="matchesFilter(
                                '<?= esc(addslashes($p->nama_lengkap ?? '')) ?>',
                                '<?= esc($p->no_pendaftaran ?? '') ?>',
                                '<?= esc($p->jurusan_pilihan1_kode ?? '') ?>',
                                '<?= esc($p->status ?? '') ?>'
                            )"
                            data-id="<?= $p->id ?>">

                            <!-- Checkbox (hanya untuk bulk Tidak Lulus — siswa yang sudah lanjut ke daftar ulang/siswa aktif tidak bisa ditolak lagi) -->
                            <td class="py-3 px-4">
                                <?php if (! in_array($p->status, ['lulus', 'daftar_ulang', 'siswa_aktif'])): ?>
                                    <input type="checkbox"
                                        x-model="selected"
                                        value="<?= $p->id ?>"
                                        class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer"
                                        @change="onCheckChange()">
                                <?php else: ?>
                                    <!-- Sudah lulus / lanjut tahap berikutnya, tidak bisa diubah lagi via bulk -->
                                    <span class="w-4 h-4 block"></span>
                                <?php endif; ?>
                            </td>

                            <!-- No Pendaftaran -->
                            <td class="py-3 px-4 font-mono text-sm text-gray-600">
                                <?= esc($p->no_pendaftaran ?? '-') ?>
                            </td>

                            <!-- Nama -->
                            <td class="py-3 px-4 font-medium text-gray-900">
                                <?= esc($p->nama_lengkap ?? '-') ?>
                                <?php if ($p->nisn ?? null): ?>
                                    <p class="text-xs text-gray-400 font-normal">NISN: <?= esc($p->nisn) ?></p>
                                <?php endif; ?>
                            </td>

                            <!-- Pilihan Jurusan (1 & 2) -->
                            <td class="py-3 px-4">
                                <div class="space-y-1">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full border border-blue-200 bg-blue-50 text-xs font-medium text-blue-700">
                                        <span class="text-blue-400 font-normal">1.</span>
                                        <?= esc($p->jurusan_pilihan1_kode ?? '-') ?>
                                    </span>
                                    <?php if ($p->jurusan_pilihan2_kode ?? null): ?>
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full border border-gray-200 text-xs font-medium text-gray-600">
                                            <span class="text-gray-400 font-normal">2.</span>
                                            <?= esc($p->jurusan_pilihan2_kode) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </td>

                            <!-- Jurusan Diterima -->
                            <td class="py-3 px-4">
                                <?php if ($p->jurusan_diterima_kode ?? null): ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-green-100 border border-green-200 text-xs font-semibold text-green-800">
                                        <?= esc($p->jurusan_diterima_kode) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-xs text-gray-400">—</span>
                                <?php endif; ?>
                            </td>

                            <!-- Status -->
                            <td class="py-3 px-4">
                                <?php if ($p->status === 'siswa_aktif'): ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">
                                        🎓 Siswa Aktif
                                    </span>
                                <?php elseif ($p->status === 'daftar_ulang'): ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                        ✓ Lulus — Proses Daftar Ulang
                                    </span>
                                <?php elseif ($p->status === 'lulus'): ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                        ✓ Lulus
                                    </span>
                                <?php elseif ($p->status === 'tidak_lulus'): ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700">
                                        ✗ Tidak Lulus
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                                        Menunggu
                                    </span>
                                <?php endif; ?>
                            </td>

                            <!-- Aksi individual -->
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-1 flex-wrap">
                                    <?php if ($p->status === 'siswa_aktif'): ?>
                                        <!-- Sudah jadi siswa aktif (sudah dikonversi ke Buku Induk) — tidak ada aksi lagi di sini -->
                                        <span class="text-xs text-gray-400 italic">🔒 Sudah di Buku Induk</span>

                                    <?php elseif ($p->status === 'daftar_ulang'): ?>
                                        <!-- Siswa sudah lulus & sedang proses daftar ulang — jurusan TIDAK BOLEH diubah lagi
                                             dari sini karena bisa mengganggu proses verifikasi pembayaran/konversi yang sedang berjalan.
                                             Perubahan jurusan/kelas dilakukan di menu Daftar Ulang atau Konversi Buku Induk. -->
                                        <a href="<?= base_url('admin/daftar-ulang') ?>"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium text-blue-700 border border-blue-200 hover:bg-blue-50 transition-colors">
                                            <svg class="h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                                            </svg>
                                            🔒 Lihat di Daftar Ulang
                                        </a>

                                    <?php else: ?>
                                        <?php if ($p->status !== 'lulus'): ?>
                                            <!-- Tombol Lulus → buka modal pilih jurusan diterima -->
                                            <button type="button"
                                                @click="openLulusModal(
                                                    <?= $p->id ?>,
                                                    '<?= esc(addslashes($p->nama_lengkap ?? '')) ?>',
                                                    <?= (int) ($p->jurusan_pilihan1_id ?? 0) ?>,
                                                    '<?= esc($p->jurusan_pilihan1_kode ?? '') ?>',
                                                    '<?= esc(addslashes($p->jurusan_pilihan1_nama ?? '')) ?>',
                                                    <?= (int) ($p->jurusan_pilihan2_id ?? 0) ?>,
                                                    '<?= esc($p->jurusan_pilihan2_kode ?? '') ?>',
                                                    '<?= esc(addslashes($p->jurusan_pilihan2_nama ?? '')) ?>'
                                                )"
                                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium text-white bg-green-700 hover:bg-green-800 transition-colors">
                                                <svg class="h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                Lulus
                                            </button>
                                        <?php else: ?>
                                            <!-- Status 'lulus' tapi belum upload bukti daftar ulang — masih boleh ubah jurusan -->
                                            <button type="button"
                                                @click="openLulusModal(
                                                    <?= $p->id ?>,
                                                    '<?= esc(addslashes($p->nama_lengkap ?? '')) ?>',
                                                    <?= (int) ($p->jurusan_pilihan1_id ?? 0) ?>,
                                                    '<?= esc($p->jurusan_pilihan1_kode ?? '') ?>',
                                                    '<?= esc(addslashes($p->jurusan_pilihan1_nama ?? '')) ?>',
                                                    <?= (int) ($p->jurusan_pilihan2_id ?? 0) ?>,
                                                    '<?= esc($p->jurusan_pilihan2_kode ?? '') ?>',
                                                    '<?= esc(addslashes($p->jurusan_pilihan2_nama ?? '')) ?>'
                                                )"
                                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium text-green-700 border border-green-300 hover:bg-green-50 transition-colors">
                                                <svg class="h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" />
                                                </svg>
                                                Ubah
                                            </button>
                                        <?php endif; ?>

                                        <?php if ($p->status !== 'tidak_lulus'): ?>
                                            <!-- Tombol Tolak individual — hanya tampil untuk status seleksi/lulus (belum lanjut ke daftar ulang) -->
                                            <button type="button"
                                                @click="confirmTolak(<?= $p->id ?>, '<?= esc(addslashes($p->nama_lengkap ?? '')) ?>')"
                                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium text-red-600 hover:bg-red-50 border border-transparent hover:border-red-200 transition-colors">
                                                <svg class="h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                Tolak
                                            </button>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <!-- Empty state -->
                    <?php if (empty($peserta)): ?>
                        <tr>
                            <td colspan="7" class="py-16 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-200 mb-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 13.5h3.86a2.25 2.25 0 012.012 1.244l.256.512a2.25 2.25 0 002.013 1.244h3.218a2.25 2.25 0 002.013-1.244l.256-.512a2.25 2.25 0 012.013-1.244h3.859m-19.5.338V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 00-2.15-1.588H6.911a2.25 2.25 0 00-2.15 1.588L2.35 13.177a2.25 2.25 0 00-.1.661z" />
                                </svg>
                                <p class="text-sm text-gray-400">Belum ada peserta yang masuk tahap seleksi</p>
                                <p class="text-xs text-gray-300 mt-1">Peserta masuk setelah dokumennya diverifikasi</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- ── Bulk Actions Footer (hanya Tolak) ─────────────────── -->
        <div class="px-5 py-4 border-t border-gray-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <input type="checkbox" id="select-all-bottom" x-model="selectAll" @change="toggleAll()"
                    class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                <label for="select-all-bottom" class="text-sm text-gray-700 cursor-pointer">
                    Pilih Semua (untuk bulk tolak)
                </label>
            </div>
            <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                <button type="button"
                    @click="bulkTolak()"
                    :disabled="selectedCount === 0"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold
                               bg-red-600 text-white hover:bg-red-700 transition-colors
                               disabled:opacity-50 disabled:cursor-not-allowed w-full sm:w-auto">
                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Tolak Terpilih (<span x-text="selectedCount"></span>)
                </button>
            </div>
        </div>
    </div>



    <!-- ══════════════════════════════════════════════════════════════
     MODAL: PILIH JURUSAN DITERIMA (saat Luluskan per-siswa)
══════════════════════════════════════════════════════════════ -->
    <div x-show="lulusModalOpen"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-end sm:items-center justify-center sm:p-4"
        style="background:rgba(0,0,0,.45);"
        @click.self="lulusModalOpen = false"
        x-cloak>

        <div class="bg-white rounded-t-2xl sm:rounded-2xl shadow-xl w-full sm:max-w-md max-h-[90vh] overflow-y-auto"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">

            <div class="px-6 pt-6 pb-4">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center mb-4 bg-green-100">
                    <svg class="w-6 h-6 text-green-700" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-base font-bold text-gray-900">Tetapkan Lulus</h3>
                <p class="text-sm mt-1 text-gray-500">
                    Pilih jurusan yang diterima untuk
                    <strong class="text-gray-900" x-text="lulusNama"></strong>.
                </p>
            </div>

            <!-- Form submit -->
            <form id="form-lulus-modal" action="<?= base_url('admin/seleksi/tetapkan') ?>" method="POST" class="px-6 pb-6 space-y-4">
                <?= csrf_field() ?>
                <input type="hidden" name="lulus_ids[]" :value="lulusId">
                <input type="hidden" name="jurusan_diterima_lulus" :value="lulusId">

                <!-- Pilih jurusan diterima -->
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700">
                        Jurusan yang Diterima <span class="text-red-500">*</span>
                    </label>

                    <!-- Pilihan 1 -->
                    <template x-if="lulusJ1Id > 0">
                        <label class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition"
                            :class="jurusanDiterima == lulusJ1Id ? 'border-green-500 bg-green-50' : 'border-gray-200 hover:border-gray-300'">
                            <input type="radio" name="jurusan_diterima_selected" :value="lulusJ1Id" x-model.number="jurusanDiterima"
                                class="h-4 w-4 text-green-600">
                            <div>
                                <p class="text-sm font-semibold text-gray-900" x-text="'[' + lulusJ1Kode + '] ' + lulusJ1Nama"></p>
                                <p class="text-xs text-green-600 font-medium">Pilihan 1 (Utama)</p>
                            </div>
                        </label>
                    </template>

                    <!-- Pilihan 2 -->
                    <template x-if="lulusJ2Id > 0">
                        <label class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition"
                            :class="jurusanDiterima == lulusJ2Id ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300'">
                            <input type="radio" name="jurusan_diterima_selected" :value="lulusJ2Id" x-model.number="jurusanDiterima"
                                class="h-4 w-4 text-blue-600">
                            <div>
                                <p class="text-sm font-semibold text-gray-900" x-text="'[' + lulusJ2Kode + '] ' + lulusJ2Nama"></p>
                                <p class="text-xs text-blue-600 font-medium">Pilihan 2</p>
                            </div>
                        </label>
                    </template>

                    <!-- Warning jika tidak ada pilihan -->
                    <template x-if="lulusJ1Id === 0 && lulusJ2Id === 0">
                        <p class="text-sm text-amber-700 bg-amber-50 border border-amber-200 rounded-xl p-3">
                            Calon siswa ini belum memiliki data pilihan jurusan. Cek data pendaftaran terlebih dahulu.
                        </p>
                    </template>

                    <!-- Hidden field yang benar-benar dikirim ke controller -->
                    <input type="hidden" name="jurusan_diterima_input" :value="jurusanDiterima" id="jurusan-diterima-hidden">
                </div>

                <div class="flex gap-3 pt-1">
                    <button type="button" @click="lulusModalOpen = false"
                        class="flex-1 py-2.5 text-sm font-semibold rounded-xl border border-gray-300 text-gray-700 hover:bg-gray-50 transition">
                        Batal
                    </button>
                    <button type="button"
                        @click="submitLulus()"
                        :disabled="jurusanDiterima === 0"
                        class="flex-1 py-2.5 text-sm font-semibold text-white rounded-xl bg-green-700 hover:bg-green-800 transition disabled:opacity-50 disabled:cursor-not-allowed">
                        Tetapkan Lulus
                    </button>
                </div>
            </form>
        </div>
    </div>


    <!-- ══════════════════════════════════════════════════════════════
     MODAL: KONFIRMASI TOLAK (individual & bulk)
══════════════════════════════════════════════════════════════ -->
    <div x-show="tolakModalOpen"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-end sm:items-center justify-center sm:p-4"
        style="background:rgba(0,0,0,.45);"
        @click.self="tolakModalOpen = false"
        x-cloak>

        <div class="bg-white rounded-t-2xl sm:rounded-2xl shadow-xl w-full sm:max-w-md p-6 max-h-[90vh] overflow-y-auto"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">

            <h3 class="text-base font-bold text-gray-900 mb-1">Tolak Pendaftar?</h3>
            <p class="text-sm text-gray-500 mb-5">
                Anda akan menolak
                <strong class="text-gray-800" x-text="tolakLabel"></strong>.
                Notifikasi akan dikirim otomatis. Lanjutkan?
            </p>
            <div class="flex gap-3 justify-end">
                <button type="button" @click="tolakModalOpen = false"
                    class="px-4 py-2 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                    Batal
                </button>
                <button type="button" @click="submitTolak()"
                    class="px-4 py-2 rounded-xl text-sm font-semibold text-white bg-red-600 hover:bg-red-700 transition">
                    Ya, Tolak
                </button>
            </div>
        </div>
    </div>


    <!-- Hidden forms -->
    <form id="form-lulus-submit" action="<?= base_url('admin/seleksi/tetapkan') ?>" method="POST" class="hidden">
        <?= csrf_field() ?>
        <div id="lulus-inputs"></div>
    </form>

    <form id="form-tolak-submit" action="<?= base_url('admin/seleksi/tetapkan') ?>" method="POST" class="hidden">
        <?= csrf_field() ?>
        <div id="tolak-inputs"></div>
    </form>


    <script>
        function seleksiPage() {
            return {
                // Filter state
                search: '',
                majorFilter: '',
                statusFilter: '',

                // Checkbox state (untuk bulk tolak)
                selected: [],
                selectAll: false,

                // Modal Lulus (per-siswa)
                lulusModalOpen: false,
                lulusId: 0,
                lulusNama: '',
                lulusJ1Id: 0,
                lulusJ1Kode: '',
                lulusJ1Nama: '',
                lulusJ2Id: 0,
                lulusJ2Kode: '',
                lulusJ2Nama: '',
                jurusanDiterima: 0,

                // Modal Tolak
                tolakModalOpen: false,
                tolakLabel: '',
                tolakIds: [],

                // ──────────────────────────────────────────────────
                get selectedCount() {
                    return this.selected.length;
                },

                matchesFilter(nama, noPendaftaran, kode, status) {
                    const q = this.search.toLowerCase();
                    const matchSearch = !q || nama.toLowerCase().includes(q) || noPendaftaran.toLowerCase().includes(q);
                    const matchMajor = !this.majorFilter || kode === this.majorFilter;
                    const matchStatus = !this.statusFilter || status === this.statusFilter;
                    return matchSearch && matchMajor && matchStatus;
                },

                toggleAll() {
                    if (this.selectAll) {
                        const visibleIds = [];
                        document.querySelectorAll('tbody tr[data-id]').forEach(row => {
                            if (row.style.display !== 'none') {
                                visibleIds.push(row.dataset.id);
                            }
                        });
                        this.selected = visibleIds;
                    } else {
                        this.selected = [];
                    }
                },

                onCheckChange() {
                    const visibleRows = document.querySelectorAll('tbody tr[data-id]');
                    const visibleIds = Array.from(visibleRows)
                        .filter(r => r.style.display !== 'none')
                        .map(r => r.dataset.id);
                    this.selectAll = visibleIds.length > 0 && visibleIds.every(id => this.selected.includes(id));
                },

                // ── LULUS MODAL ───────────────────────────────────
                openLulusModal(id, nama, j1Id, j1Kode, j1Nama, j2Id, j2Kode, j2Nama) {
                    this.lulusId = id;
                    this.lulusNama = nama;
                    this.lulusJ1Id = j1Id;
                    this.lulusJ1Kode = j1Kode;
                    this.lulusJ1Nama = j1Nama;
                    this.lulusJ2Id = j2Id;
                    this.lulusJ2Kode = j2Kode;
                    this.lulusJ2Nama = j2Nama;
                    // Default pilih jurusan 1
                    this.jurusanDiterima = j1Id > 0 ? j1Id : (j2Id > 0 ? j2Id : 0);
                    this.lulusModalOpen = true;
                },

                submitLulus() {
                    if (this.jurusanDiterima === 0) {
                        alert('Pilih jurusan yang diterima terlebih dahulu.');
                        return;
                    }

                    const form = document.getElementById('form-lulus-submit');
                    const inputDiv = document.getElementById('lulus-inputs');
                    inputDiv.innerHTML = '';

                    // lulus_ids[]
                    const inpId = document.createElement('input');
                    inpId.type = 'hidden';
                    inpId.name = 'lulus_ids[]';
                    inpId.value = this.lulusId;
                    inputDiv.appendChild(inpId);

                    // jurusan_diterima[pendaftaran_id] = jurusan_id
                    const inpJur = document.createElement('input');
                    inpJur.type = 'hidden';
                    inpJur.name = `jurusan_diterima[${this.lulusId}]`;
                    inpJur.value = this.jurusanDiterima;
                    inputDiv.appendChild(inpJur);

                    this.lulusModalOpen = false;
                    form.submit();
                },

                // ── TOLAK ─────────────────────────────────────────
                confirmTolak(id, nama) {
                    this.tolakIds = [id];
                    this.tolakLabel = `"${nama}"`;
                    this.tolakModalOpen = true;
                },

                bulkTolak() {
                    if (this.selected.length === 0) {
                        alert('Pilih minimal 1 pendaftar terlebih dahulu.');
                        return;
                    }
                    this.tolakIds = [...this.selected];
                    this.tolakLabel = `${this.selected.length} calon siswa`;
                    this.tolakModalOpen = true;
                },

                submitTolak() {
                    const form = document.getElementById('form-tolak-submit');
                    const inputDiv = document.getElementById('tolak-inputs');
                    inputDiv.innerHTML = '';

                    this.tolakIds.forEach(id => {
                        const inp = document.createElement('input');
                        inp.type = 'hidden';
                        inp.name = 'tidak_lulus_ids[]';
                        inp.value = id;
                        inputDiv.appendChild(inp);
                    });

                    this.tolakModalOpen = false;
                    form.submit();
                },
            };
        }
    </script>

</div><!-- /x-data="seleksiPage()" -->