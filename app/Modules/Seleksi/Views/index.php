<!-- 
    File: app/Modules/Seleksi/Views/index.php
    Disesuaikan dengan mockup React PenetapanKelulusanPage
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

    <!-- ── Quota Cards ──────────────────────────────────────────── -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
        <?php foreach ($byJurusan as $jid => $data):
            $j        = $data['jurusan'];
            $kuota    = (int) $j->kuota;
            $terseleksi = $data['count_lulus'];
            $sisa     = $kuota - $terseleksi;
            $isFull   = $sisa <= 0;
        ?>
            <div class="bg-white rounded-2xl border <?= $isFull ? 'border-red-400' : 'border-gray-200' ?> p-4 text-center">
                <svg class="h-8 w-8 mx-auto mb-2 <?= $isFull ? 'text-red-500' : 'text-blue-700' ?>"
                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.906 59.906 0 0112 3.493a59.903 59.903 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5" />
                </svg>
                <h3 class="font-bold text-gray-900"><?= esc($j->kode) ?></h3>
                <div class="text-xs text-gray-500 mt-1 space-y-0.5">
                    <p>Kuota: <?= $kuota ?></p>
                    <p>Pendaftar: <?= count($data['peserta']) ?></p>
                    <p>Terseleksi: <?= $terseleksi ?></p>
                </div>
                <span class="inline-flex items-center mt-2 px-2.5 py-0.5 rounded-full text-xs font-semibold
                         <?= $isFull ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-800' ?>">
                    <?= $isFull ? 'PENUH' : 'Sisa: ' . $sisa ?>
                </span>
            </div>
        <?php endforeach; ?>
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
            <!-- Badge jumlah dipilih -->
            <span x-show="selectedCount > 0"
                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-700"
                x-text="selectedCount + ' dipilih'"></span>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full min-w-[700px]">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="py-3 px-4 text-left w-10">
                            <input type="checkbox" x-model="selectAll" @change="toggleAll()"
                                class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                        </th>
                        <th class="py-3 px-4 text-left text-sm font-medium text-gray-500">No. Pendaftaran</th>
                        <th class="py-3 px-4 text-left text-sm font-medium text-gray-500">Nama</th>
                        <th class="py-3 px-4 text-left text-sm font-medium text-gray-500">Jurusan</th>
                        <th class="py-3 px-4 text-left text-sm font-medium text-gray-500">Status</th>
                        <th class="py-3 px-4 text-left text-sm font-medium text-gray-500">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($peserta as $p): ?>
                        <tr class="border-b border-gray-50 last:border-0 hover:bg-gray-50 transition-colors"
                            x-show="matchesFilter('<?= esc($p->nama_lengkap ?? '') ?>', '<?= esc($p->no_pendaftaran ?? '') ?>', '<?= esc($p->jurusan_pilihan1_kode ?? '') ?>')"
                            data-id="<?= $p->id ?>">

                            <!-- Checkbox -->
                            <td class="py-3 px-4">
                                <input type="checkbox"
                                    x-model="selected"
                                    value="<?= $p->id ?>"
                                    class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer"
                                    @change="onCheckChange()">
                            </td>

                            <!-- No Pendaftaran -->
                            <td class="py-3 px-4 font-mono text-sm text-gray-600">
                                <?= esc($p->no_pendaftaran ?? '-') ?>
                            </td>

                            <!-- Nama -->
                            <td class="py-3 px-4 font-medium text-gray-900">
                                <?= esc($p->nama_lengkap ?? '-') ?>
                            </td>

                            <!-- Jurusan badge -->
                            <td class="py-3 px-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full border border-gray-300 text-xs font-medium text-gray-700">
                                    <?= esc($p->jurusan_pilihan1_kode ?? '-') ?>
                                </span>
                            </td>

                            <!-- Status -->
                            <td class="py-3 px-4">
                                <?php if ($p->status === 'lulus'): ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                        ✓ Lulus
                                    </span>
                                <?php elseif ($p->status === 'tidak_lulus'): ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700">
                                        ✗ Tidak Lulus
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                        Terverifikasi
                                    </span>
                                <?php endif; ?>
                            </td>

                            <!-- Aksi individual -->
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-1">
                                    <button type="button"
                                        @click="individualAction(<?= $p->id ?>, 'lulus', '<?= esc($p->nama_lengkap ?? '') ?>')"
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium text-green-700 hover:bg-green-50 transition-colors">
                                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Lulus
                                    </button>
                                    <button type="button"
                                        @click="individualAction(<?= $p->id ?>, 'tidak_lulus', '<?= esc($p->nama_lengkap ?? '') ?>')"
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium text-red-600 hover:bg-red-50 transition-colors">
                                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Tolak
                                    </button>
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

        <!-- ── Bulk Actions Footer ──────────────────────────────── -->
        <div class="px-5 py-4 border-t border-gray-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <input type="checkbox" id="select-all-bottom" x-model="selectAll" @change="toggleAll()"
                    class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                <label for="select-all-bottom" class="text-sm text-gray-700 cursor-pointer">Pilih Semua</label>
            </div>
            <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                <button type="button"
                    @click="bulkAction('lulus')"
                    :disabled="selectedCount === 0"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold
                               bg-green-700 text-white hover:bg-green-800 transition-colors
                               disabled:opacity-50 disabled:cursor-not-allowed w-full sm:w-auto">
                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Luluskan (<span x-text="selectedCount"></span>)
                </button>
                <button type="button"
                    @click="bulkAction('tidak_lulus')"
                    :disabled="selectedCount === 0"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold
                               bg-red-600 text-white hover:bg-red-700 transition-colors
                               disabled:opacity-50 disabled:cursor-not-allowed w-full sm:w-auto">
                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Tolak (<span x-text="selectedCount"></span>)
                </button>
            </div>
        </div>
    </div>

    <!-- ── Confirmation Dialog ──────────────────────────────────── -->
    <div x-show="showDialog"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 flex items-end sm:items-center justify-center sm:p-4"
        style="background:rgba(0,0,0,.45);"
        @click.self="showDialog = false">
        <div class="bg-white rounded-t-2xl sm:rounded-2xl shadow-xl w-full sm:max-w-md p-6 max-h-[90vh] overflow-y-auto"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100">

            <h3 class="text-base font-bold text-gray-900 mb-1"
                x-text="pendingAction === 'lulus' ? 'Luluskan Pendaftar?' : 'Tolak Pendaftar?'"></h3>
            <p class="text-sm text-gray-500 mb-5">
                Anda akan
                <span x-text="pendingAction === 'lulus' ? 'meluluskan' : 'menolak'"></span>
                <strong x-text="dialogCount + ' calon siswa'"></strong>.
                Notifikasi akan dikirim otomatis. Lanjutkan?
            </p>
            <div class="flex gap-3 justify-end">
                <button type="button" @click="showDialog = false"
                    class="px-4 py-2 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                    Batal
                </button>
                <button type="button" @click="confirmAction()"
                    :class="pendingAction === 'lulus'
                            ? 'bg-green-700 hover:bg-green-800 text-white'
                            : 'bg-red-600 hover:bg-red-700 text-white'"
                    class="px-4 py-2 rounded-xl text-sm font-semibold transition">
                    Ya,
                    <span x-text="pendingAction === 'lulus' ? 'Luluskan' : 'Tolak'"></span>
                </button>
            </div>
        </div>
    </div>

</div>

<!-- Hidden form for bulk submit -->
<form id="form-bulk-seleksi" action="<?= base_url('admin/seleksi/tetapkan') ?>" method="POST" class="hidden">
    <?= csrf_field() ?>
    <div id="bulk-hidden-inputs"></div>
</form>

<!-- Hidden form for individual action -->
<form id="form-individual-seleksi" action="<?= base_url('admin/seleksi/tetapkan') ?>" method="POST" class="hidden">
    <?= csrf_field() ?>
    <div id="individual-hidden-inputs"></div>
</form>

<script>
    function seleksiPage() {
        return {
            search: '',
            majorFilter: '',
            selected: [], // array of pendaftaran IDs (string from x-model)
            selectAll: false,
            showDialog: false,
            pendingAction: 'lulus', // 'lulus' | 'tidak_lulus'
            pendingIds: [],
            dialogCount: 0,

            get selectedCount() {
                return this.selected.length;
            },

            matchesFilter(nama, noPendaftaran, kode) {
                const q = this.search.toLowerCase();
                const matchSearch = !q || nama.toLowerCase().includes(q) || noPendaftaran.toLowerCase().includes(q);
                const matchMajor = !this.majorFilter || kode === this.majorFilter;
                return matchSearch && matchMajor;
            },

            toggleAll() {
                if (this.selectAll) {
                    // Pilih semua yang visible
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
                // Sync selectAll state
                const visibleRows = document.querySelectorAll('tbody tr[data-id]');
                const visibleIds = Array.from(visibleRows)
                    .filter(r => r.style.display !== 'none')
                    .map(r => r.dataset.id);
                this.selectAll = visibleIds.length > 0 && visibleIds.every(id => this.selected.includes(id));
            },

            bulkAction(action) {
                if (this.selected.length === 0) {
                    alert('Pilih minimal 1 pendaftar.');
                    return;
                }
                this.pendingAction = action;
                this.pendingIds = [...this.selected];
                this.dialogCount = this.selected.length;
                this.showDialog = true;
            },

            individualAction(id, action, nama) {
                this.pendingAction = action;
                this.pendingIds = [String(id)];
                this.dialogCount = 1;
                this.showDialog = true;
            },

            confirmAction() {
                this.showDialog = false;

                const form = document.getElementById('form-bulk-seleksi');
                const hiddenDiv = document.getElementById('bulk-hidden-inputs');
                hiddenDiv.innerHTML = '';

                this.pendingIds.forEach(id => {
                    const inp = document.createElement('input');
                    inp.type = 'hidden';
                    inp.name = this.pendingAction === 'lulus' ? 'lulus_ids[]' : 'tidak_lulus_ids[]';
                    inp.value = id;
                    hiddenDiv.appendChild(inp);
                });

                form.submit();
            },
        };
    }
</script>