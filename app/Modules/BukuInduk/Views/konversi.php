<!--
    File : app/Modules/BukuInduk/Views/konversi.php
-->
<div class="space-y-6" x-data="konversiPage()">

    <!-- ── Page Header ──────────────────────────────────────────── -->
    <div>
        <h1 class="text-2xl font-bold font-serif">Konversi ke Buku Induk</h1>
        <p class="text-sm text-gray-500">Pindahkan data pendaftar yang diterima &amp; daftar ulang ke Buku Induk Siswa</p>
    </div>

    <!-- Flash messages -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="flex items-start gap-3 px-4 py-3 bg-green-50 border border-green-200 rounded-xl text-sm text-green-800">
            <svg class="h-5 w-5 flex-shrink-0 text-green-600 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span><?= session()->getFlashdata('success') ?></span>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="flex items-start gap-3 px-4 py-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-800">
            <svg class="h-5 w-5 flex-shrink-0 text-red-500 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126z" />
            </svg>
            <span><?= session()->getFlashdata('error') ?></span>
        </div>
    <?php endif; ?>

    <!-- ── Info Alert ────────────────────────────────────────────── -->
    <div class="flex items-start gap-4 px-5 py-4 bg-blue-50 border border-blue-200 rounded-2xl">
        <svg class="h-5 w-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
        </svg>
        <div class="space-y-1 text-sm">
            <p class="font-semibold text-blue-900">Informasi Penting</p>
            <p class="text-blue-800">Fitur ini akan memindahkan data pendaftar yang <strong>DITERIMA &amp; sudah DIKONFIRMASI admin di menu Daftar Ulang</strong> ke tabel Buku Induk Siswa.</p>
            <p class="text-blue-800">Data akan otomatis ter-generate dengan NIS (Nomor Induk Siswa) yang unik.</p>
            <p class="font-semibold text-amber-700">⚠️ Proses ini TIDAK BISA DIUNDO. Pastikan data sudah benar sebelum melanjutkan.</p>
        </div>
    </div>

    <!-- ── Filter Card ───────────────────────────────────────────── -->
    <div class="bg-white rounded-2xl border border-gray-200 p-4">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">

            <!-- Jurusan filter -->
            <div class="relative">
                <select x-model="majorFilter"
                    class="pl-4 pr-8 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 w-52 appearance-none bg-white">
                    <option value="">Semua Jurusan</option>
                    <?php foreach ($jurusans ?? [] as $j): ?>
                        <option value="<?= esc($j->kode) ?>"><?= esc($j->kode) ?> — <?= esc($j->nama) ?></option>
                    <?php endforeach; ?>
                </select>
                <svg class="absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                </svg>
            </div>

            <!-- Count badge — PERBAIKAN: hitung dari du_status === 'dikonfirmasi', bukan dari pendaftaran.status -->
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm font-semibold bg-green-100 text-green-800">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <?= count(array_filter($siapKonversi, fn($s) => is_null($s->buku_induk_id) && $s->du_status === 'dikonfirmasi')) ?> siswa siap dikonversi
            </span>
        </div>

        <?php
        // FIX: $siapKonversi sekarang TIDAK mengandung row status 'ditolak' sama sekali
        // (sudah difilter di query backend dengan subquery JOIN 'dikonfirmasi' only).
        // countDitolak selalu 0. Kita hitung pending dan belum DU saja.
        $countPending = count(array_filter($siapKonversi, fn($s) => is_null($s->buku_induk_id) && $s->du_status === 'pending'));
        $countBelumDU = count(array_filter($siapKonversi, fn($s) => is_null($s->buku_induk_id) && empty($s->du_status)));
        ?>
        <?php if ($countPending > 0 || $countBelumDU > 0): ?>
            <div class="mt-3 pt-3 border-t border-gray-100 text-xs text-gray-500 flex flex-wrap gap-x-4 gap-y-1">
                <?php if ($countPending > 0): ?>
                    <span>⏳ <strong class="text-amber-700"><?= $countPending ?></strong> siswa menunggu verifikasi di
                        <a href="<?= base_url('admin/daftar-ulang') ?>" class="underline hover:text-amber-800">menu Daftar Ulang</a>
                    </span>
                <?php endif; ?>
                <?php if ($countBelumDU > 0): ?>
                    <span>📋 <strong class="text-gray-600"><?= $countBelumDU ?></strong> siswa belum mengajukan daftar ulang</span>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- ── Preview Table Card ────────────────────────────────────── -->
    <div class="bg-white rounded-2xl border border-gray-200">

        <!-- Card Header -->
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                </svg>
                <h3 class="font-semibold text-gray-900">Preview Data yang Akan Dikonversi</h3>
            </div>
            <span x-show="selectedCount > 0"
                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-700"
                x-text="selectedCount + ' dipilih'"></span>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full min-w-[600px]">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="py-3 px-4 text-left w-10">
                            <input type="checkbox"
                                x-model="selectAll" @change="toggleAll()"
                                class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                        </th>
                        <th class="py-3 px-4 text-left text-sm font-medium text-gray-500">No</th>
                        <th class="py-3 px-4 text-left text-sm font-medium text-gray-500">No. Pendaftaran</th>
                        <th class="py-3 px-4 text-left text-sm font-medium text-gray-500">Nama</th>
                        <th class="py-3 px-4 text-left text-sm font-medium text-gray-500">Jurusan</th>
                        <th class="py-3 px-4 text-left text-sm font-medium text-gray-500">Status Daftar Ulang</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($siapKonversi as $idx => $s):
                        $sudahKonversi  = !is_null($s->buku_induk_id);
                        $belumKonversi  = is_null($s->buku_induk_id);

                        // PERBAIKAN: status valid HARUS dari daftar_ulangs.status === 'dikonfirmasi',
                        // bukan dari pendaftaran.status. pendaftaran.status hanya menandakan siswa
                        // SUDAH MENGUPLOAD bukti (belum tentu sudah diverifikasi admin TU).
                        $duStatus       = $s->du_status; // null | pending | dikonfirmasi | ditolak
                        $isValid        = $duStatus === 'dikonfirmasi' && $belumKonversi;
                    ?>
                        <tr class="border-b border-gray-50 last:border-0 transition-colors"
                            :class="selectedIds.includes('<?= $s->id ?>') ? 'bg-blue-50/50' : 'hover:bg-gray-50'"
                            x-show="!majorFilter || '<?= esc($s->jurusan_kode) ?>' === majorFilter"
                            data-id="<?= $s->id ?>"
                            data-valid="<?= $isValid ? '1' : '0' ?>">

                            <!-- Checkbox -->
                            <td class="py-3 px-4">
                                <input type="checkbox"
                                    value="<?= $s->id ?>"
                                    x-model="selectedIds"
                                    <?= !$isValid ? 'disabled' : '' ?>
                                    @change="onCheckChange()"
                                    class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed">
                            </td>

                            <!-- No -->
                            <td class="py-3 px-4 text-gray-500"><?= $idx + 1 ?></td>

                            <!-- No Pendaftaran -->
                            <td class="py-3 px-4 font-mono text-sm text-gray-600">
                                <?= esc($s->no_pendaftaran ?? '-') ?>
                            </td>

                            <!-- Nama -->
                            <td class="py-3 px-4 font-medium text-gray-900">
                                <?= esc($s->nama_lengkap ?? '-') ?>
                            </td>

                            <!-- Jurusan badge -->
                            <td class="py-3 px-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full border border-gray-300 text-xs font-medium text-gray-700">
                                    <?= esc($s->jurusan_kode ?? '-') ?>
                                </span>
                            </td>

                            <!-- Status Daftar Ulang — dibaca dari daftar_ulangs.status (du_status) -->
                            <td class="py-3 px-4">
                                <?php if ($sudahKonversi): ?>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-600">
                                        NIS: <?= esc($s->nis ?? '-') ?>
                                    </span>
                                <?php elseif ($duStatus === 'dikonfirmasi'): ?>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                        ✅ Valid — Siap Dikonversi
                                    </span>
                                <?php elseif ($duStatus === 'pending'): ?>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                                        ⏳ Menunggu Verifikasi Admin
                                    </span>
                                <?php elseif ($duStatus === 'ditolak'): ?>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700">
                                        ✗ Bukti Ditolak
                                    </span>
                                <?php else: ?>
                                    <!-- du_status null = siswa belum upload bukti pembayaran sama sekali -->
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-600">
                                        Belum Daftar Ulang
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (empty($siapKonversi)): ?>
                        <tr>
                            <td colspan="6" class="py-16 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-200 mb-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-8.69-6.44l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z" />
                                </svg>
                                <p class="text-sm text-gray-400">Belum ada siswa yang siap dikonversi</p>
                                <p class="text-xs text-gray-300 mt-1">Siswa perlu menyelesaikan daftar ulang terlebih dahulu</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- ── Summary + Action ─────────────────────────────── -->
        <div class="px-5 py-4 border-t border-gray-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
            <p class="text-sm text-gray-500">
                <strong x-text="selectedCount"></strong> siswa dipilih untuk dikonversi ke Buku Induk
            </p>
            <button type="button"
                @click="selectedCount > 0 && (showDialog = true)"
                :disabled="selectedCount === 0"
                class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-700 text-white rounded-xl text-sm font-semibold
                           hover:bg-blue-800 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2 3h6a4 4 0 014 4v14a3 3 0 00-3-3H2z" />
                    <path d="M22 3h-6a4 4 0 00-4 4v14a3 3 0 013-3h7z" />
                </svg>
                Generate NIS &amp; Konversi ke Buku Induk
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                </svg>
            </button>
        </div>
    </div>

    <!-- ── Confirmation Dialog ──────────────────────────────────── -->
    <div x-show="showDialog"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-end sm:items-center justify-center sm:p-4"
        style="background:rgba(0,0,0,.5);"
        @click.self="!isConverting && (showDialog = false)">
        <div class="bg-white rounded-t-2xl sm:rounded-2xl shadow-xl w-full sm:max-w-md max-h-[90vh] overflow-y-auto"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">

            <!-- Dialog Header -->
            <div class="px-6 pt-5 pb-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900 flex items-center gap-2">
                    <svg class="h-5 w-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                    Konfirmasi Konversi
                </h3>
            </div>

            <!-- Dialog Body -->
            <div class="px-6 py-4 space-y-3 text-sm text-gray-600">
                <p>Anda akan mengkonversi <strong x-text="selectedCount + ' siswa'"></strong> ke Buku Induk.</p>
                <ul class="list-disc list-inside space-y-1 text-gray-700">
                    <li>NIS akan di-generate otomatis (format: TAHUN + urutan jurusan)</li>
                    <li>Data akan dipindahkan ke tabel Buku Induk Siswa</li>
                    <li>Status pendaftar akan diubah menjadi <strong>Siswa Aktif</strong></li>
                </ul>
                <p class="font-semibold text-amber-700">⚠️ Proses ini TIDAK BISA DIUNDO. Lanjutkan?</p>
            </div>

            <!-- Dialog Footer -->
            <div class="px-6 pb-5 flex gap-3 justify-end">
                <button type="button"
                    @click="showDialog = false"
                    :disabled="isConverting"
                    class="px-4 py-2 border border-gray-300 text-gray-700 rounded-xl text-sm font-medium hover:bg-gray-50 transition disabled:opacity-50">
                    Batal
                </button>
                <!-- Submit form -->
                <button type="button"
                    @click="submitKonversi()"
                    :disabled="isConverting"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-green-700 text-white rounded-xl text-sm font-semibold hover:bg-green-800 transition disabled:opacity-70">
                    <svg x-show="isConverting" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <span x-text="isConverting ? 'Mengkonversi...' : 'Ya, Konversi'"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- Hidden form for bulk konversi -->
    <form id="form-konversi-bulk"
        action="<?= base_url('admin/buku-induk/konversi-bulk-selected') ?>"
        method="POST" class="hidden">
        <?= csrf_field() ?>
        <div id="konversi-hidden-inputs"></div>
    </form>

</div>

<script>
    function konversiPage() {
        return {
            majorFilter: '',
            selectedIds: [],
            selectAll: false,
            showDialog: false,
            isConverting: false,

            get selectedCount() {
                return this.selectedIds.length;
            },

            toggleAll() {
                const visibleValid = Array.from(document.querySelectorAll('tbody tr[data-valid="1"]'))
                    .filter(r => {
                        const kode = r.querySelector('td:nth-child(5) span')?.textContent?.trim() ?? '';
                        return !this.majorFilter || kode === this.majorFilter;
                    })
                    .map(r => r.dataset.id);

                this.selectedIds = this.selectAll ? visibleValid : [];
            },

            onCheckChange() {
                const visibleValid = Array.from(document.querySelectorAll('tbody tr[data-valid="1"]'))
                    .filter(r => {
                        const kode = r.querySelector('td:nth-child(5) span')?.textContent?.trim() ?? '';
                        return !this.majorFilter || kode === this.majorFilter;
                    })
                    .map(r => r.dataset.id);

                this.selectAll = visibleValid.length > 0 && visibleValid.every(id => this.selectedIds.includes(id));
            },

            submitKonversi() {
                if (this.selectedIds.length === 0) return;
                this.isConverting = true;

                const form = document.getElementById('form-konversi-bulk');
                const hiddenDiv = document.getElementById('konversi-hidden-inputs');
                hiddenDiv.innerHTML = '';

                this.selectedIds.forEach(id => {
                    const inp = document.createElement('input');
                    inp.type = 'hidden';
                    inp.name = 'pendaftaran_ids[]';
                    inp.value = id;
                    hiddenDiv.appendChild(inp);
                });

                form.submit();
            },
        };
    }
</script>