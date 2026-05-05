<!--
    File : app/Modules/MasterData/Views/index.php
    UPDATED: Tambah tab "Jenis Dokumen" — admin bisa kelola jenis
             dokumen yang wajib/tidak wajib diupload calon siswa.
-->

<?php
$activeTab = session()->getFlashdata('active_tab') ?? 'jurusan';
?>

<div class="space-y-6" x-data="{ tab: '<?= $activeTab ?>' }">

    <!-- ── Page Header ──────────────────────────────────────────── -->
    <div>
        <h1 class="text-2xl font-bold font-serif">Data Master</h1>
        <p class="text-sm text-gray-500">Kelola jurusan, kelas, periode pendaftaran, dan jenis dokumen</p>
    </div>

    <!-- Flash messages -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="flex items-center gap-3 px-4 py-3 bg-green-50 border border-green-200 rounded-xl text-sm text-green-800">
            <svg class="h-4 w-4 flex-shrink-0 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <?= esc(session()->getFlashdata('success')) ?>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="flex items-center gap-3 px-4 py-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-800">
            <svg class="h-4 w-4 flex-shrink-0 text-red-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126z" />
            </svg>
            <?= esc(session()->getFlashdata('error')) ?>
        </div>
    <?php endif; ?>

    <!-- ══════════════════════════════════════════════════════════
         TABS — 4 kolom (tambah tab Jenis Dokumen)
    ══════════════════════════════════════════════════════════ -->
    <div class="bg-gray-100 rounded-xl p-1 grid grid-cols-2 sm:grid-cols-4 gap-1">

        <button type="button" @click="tab = 'jurusan'"
            :class="tab === 'jurusan' ? 'bg-white shadow-sm text-gray-900 font-semibold' : 'text-gray-500 hover:text-gray-700'"
            class="flex items-center justify-center gap-2 py-2.5 rounded-lg text-sm transition-all">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.906 59.906 0 0112 3.493a59.903 59.903 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5" />
            </svg>
            Jurusan
        </button>

        <button type="button" @click="tab = 'kelas'"
            :class="tab === 'kelas' ? 'bg-white shadow-sm text-gray-900 font-semibold' : 'text-gray-500 hover:text-gray-700'"
            class="flex items-center justify-center gap-2 py-2.5 rounded-lg text-sm transition-all">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
            </svg>
            Kelas
        </button>

        <button type="button" @click="tab = 'periode'"
            :class="tab === 'periode' ? 'bg-white shadow-sm text-gray-900 font-semibold' : 'text-gray-500 hover:text-gray-700'"
            class="flex items-center justify-center gap-2 py-2.5 rounded-lg text-sm transition-all">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
            </svg>
            Periode
        </button>

        <!-- TAB BARU: Jenis Dokumen -->
        <button type="button" @click="tab = 'dokumen'"
            :class="tab === 'dokumen' ? 'bg-white shadow-sm text-gray-900 font-semibold' : 'text-gray-500 hover:text-gray-700'"
            class="flex items-center justify-center gap-2 py-2.5 rounded-lg text-sm transition-all">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
            </svg>
            Jenis Dokumen
        </button>
    </div>


    <!-- ══════════════════════════════════════════════════════════
         TAB CONTENT: JURUSAN
    ══════════════════════════════════════════════════════════ -->
    <div x-show="tab === 'jurusan'"
        x-data="{ open: false, item: {}, mode: 'add' }"
        x-cloak>

        <div class="bg-white rounded-2xl border border-gray-200">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-semibold text-gray-900">Daftar Jurusan</h3>
                <button type="button"
                    @click="item = {}; mode = 'add'; open = true"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-700 text-white rounded-xl text-sm font-medium hover:bg-blue-800 transition-colors">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Tambah Jurusan
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[500px]">
                    <thead>
                        <tr class="border-b border-gray-100">
                            <th class="py-3 px-4 text-left text-sm font-medium text-gray-500">No</th>
                            <th class="py-3 px-4 text-left text-sm font-medium text-gray-500">Kode</th>
                            <th class="py-3 px-4 text-left text-sm font-medium text-gray-500">Nama Jurusan</th>
                            <th class="py-3 px-4 text-left text-sm font-medium text-gray-500">Kuota</th>
                            <th class="py-3 px-4 text-left text-sm font-medium text-gray-500">Status</th>
                            <th class="py-3 px-4 text-left text-sm font-medium text-gray-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($jurusans as $i => $j): ?>
                            <tr class="border-b border-gray-50 last:border-0 hover:bg-gray-50 transition-colors">
                                <td class="py-3 px-4 text-gray-500"><?= $i + 1 ?></td>
                                <td class="py-3 px-4 font-mono font-medium text-gray-900"><?= esc($j->kode) ?></td>
                                <td class="py-3 px-4 text-gray-900"><?= esc($j->nama) ?></td>
                                <td class="py-3 px-4 text-gray-700"><?= $j->kuota ?></td>
                                <td class="py-3 px-4">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold
                                    <?= $j->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-500' ?>">
                                        <?= $j->is_active ? '🟢 Aktif' : '🔴 Nonaktif' ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-0.5">
                                        <button type="button"
                                            @click="item = <?= htmlspecialchars(json_encode($j)) ?>; mode = 'edit'; open = true"
                                            class="p-1.5 rounded-lg text-gray-500 hover:bg-gray-100 transition-colors" title="Edit">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                            </svg>
                                        </button>
                                        <a href="<?= base_url('admin/master-data/jurusan/' . $j->id . '/hapus') ?>"
                                            onclick="return confirm('Nonaktifkan jurusan \'<?= esc($j->nama, 'js') ?>\'?')"
                                            class="p-1.5 rounded-lg text-red-400 hover:bg-red-50 transition-colors" title="Nonaktifkan">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                            </svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($jurusans)): ?>
                            <tr>
                                <td colspan="6" class="py-10 text-center text-sm text-gray-400">Belum ada data jurusan</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal Jurusan -->
        <div x-show="open"
            x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 flex items-end sm:items-center justify-center sm:p-4"
            style="background:rgba(0,0,0,.5);" @click.self="open = false">
            <div class="bg-white rounded-t-2xl sm:rounded-2xl shadow-xl w-full sm:max-w-md max-h-[90vh] overflow-y-auto"
                x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
                <div class="px-6 pt-5 pb-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-900" x-text="mode === 'add' ? 'Tambah Jurusan' : 'Edit Jurusan'"></h3>
                </div>
                <form action="<?= base_url('admin/master-data/jurusan/simpan') ?>" method="POST" class="px-6 pt-4 pb-6 space-y-4">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" :value="item.id ?? ''">
                    <input type="hidden" name="tab" value="jurusan">
                    <div class="space-y-1.5">
                        <label class="block text-sm font-medium text-gray-700">Kode Jurusan</label>
                        <input type="text" name="kode" :value="item.kode ?? ''" required maxlength="10" placeholder="Contoh: TKJ"
                            class="w-full px-3 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-sm font-medium text-gray-700">Kode NIS <span class="text-gray-400 font-normal text-xs">(2 digit)</span></label>
                        <input type="text" name="kode_nis" :value="item.kode_nis ?? ''" required maxlength="2" placeholder="Contoh: 01"
                            class="w-full px-3 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-sm font-medium text-gray-700">Nama Jurusan</label>
                        <input type="text" name="nama" :value="item.nama ?? ''" required placeholder="Contoh: Teknik Komputer dan Jaringan"
                            class="w-full px-3 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1.5">
                            <label class="block text-sm font-medium text-gray-700">Kuota</label>
                            <input type="number" name="kuota" :value="item.kuota ?? 36" required min="1" placeholder="40"
                                class="w-full px-3 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div class="space-y-1.5">
                            <label class="block text-sm font-medium text-gray-700">Urutan</label>
                            <input type="number" name="urutan" :value="item.urutan ?? 1" min="1"
                                class="w-full px-3 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    <label class="flex items-center gap-3 cursor-pointer select-none">
                        <div class="relative">
                            <input type="checkbox" name="is_active" value="1" :checked="item.is_active != 0 || mode === 'add'" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-300 rounded-full peer-checked:bg-blue-600 transition-colors after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-5"></div>
                        </div>
                        <span class="text-sm font-medium text-gray-700">Aktif</span>
                    </label>
                    <div class="flex gap-3 pt-1">
                        <button type="button" @click="open = false" class="flex-1 py-2.5 border border-gray-300 text-gray-700 rounded-xl text-sm font-medium hover:bg-gray-50 transition">Batal</button>
                        <button type="submit" class="flex-1 py-2.5 bg-blue-700 text-white rounded-xl text-sm font-semibold hover:bg-blue-800 transition">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div><!-- /jurusan tab -->


    <!-- ══════════════════════════════════════════════════════════
         TAB CONTENT: KELAS
    ══════════════════════════════════════════════════════════ -->
    <div x-show="tab === 'kelas'"
        x-data="{ open: false, item: {}, mode: 'add' }"
        x-cloak>

        <div class="bg-white rounded-2xl border border-gray-200">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-semibold text-gray-900">Daftar Kelas</h3>
                <button type="button"
                    @click="item = {}; mode = 'add'; open = true"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-700 text-white rounded-xl text-sm font-medium hover:bg-blue-800 transition-colors">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Tambah Kelas
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[500px]">
                    <thead>
                        <tr class="border-b border-gray-100">
                            <th class="py-3 px-4 text-left text-sm font-medium text-gray-500">Nama Kelas</th>
                            <th class="py-3 px-4 text-left text-sm font-medium text-gray-500">Jurusan</th>
                            <th class="py-3 px-4 text-left text-sm font-medium text-gray-500">Tahun Ajaran</th>
                            <th class="py-3 px-4 text-left text-sm font-medium text-gray-500">Wali Kelas</th>
                            <th class="py-3 px-4 text-left text-sm font-medium text-gray-500">Kapasitas</th>
                            <th class="py-3 px-4 text-left text-sm font-medium text-gray-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($kelas as $k): ?>
                            <tr class="border-b border-gray-50 last:border-0 hover:bg-gray-50 transition-colors">
                                <td class="py-3 px-4 font-medium text-gray-900"><?= esc($k->nama) ?></td>
                                <td class="py-3 px-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full border border-gray-300 text-xs font-medium text-gray-700"><?= esc($k->kode_jurusan ?? '-') ?></span>
                                </td>
                                <td class="py-3 px-4 text-sm text-gray-600"><?= esc($k->tahun_ajaran ?? '-') ?></td>
                                <td class="py-3 px-4 text-sm text-gray-600"><?= esc($k->wali_kelas ?: '-') ?></td>
                                <td class="py-3 px-4 text-sm text-gray-700"><?= ($k->siswa_count ?? 0) ?>/<?= $k->kapasitas ?></td>
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-0.5">
                                        <button type="button"
                                            @click="item = <?= htmlspecialchars(json_encode($k)) ?>; mode = 'edit'; open = true"
                                            class="p-1.5 rounded-lg text-gray-500 hover:bg-gray-100 transition-colors">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                            </svg>
                                        </button>
                                        <a href="<?= base_url('admin/master-data/kelas/' . $k->id . '/hapus') ?>"
                                            onclick="return confirm('Hapus kelas \'<?= esc($k->nama, 'js') ?>\'?')"
                                            class="p-1.5 rounded-lg text-red-400 hover:bg-red-50 transition-colors">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                            </svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($kelas)): ?>
                            <tr>
                                <td colspan="6" class="py-10 text-center text-sm text-gray-400">Belum ada data kelas</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal Kelas -->
        <div x-show="open"
            x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 flex items-end sm:items-center justify-center sm:p-4"
            style="background:rgba(0,0,0,.5);" @click.self="open = false">
            <div class="bg-white rounded-t-2xl sm:rounded-2xl shadow-xl w-full sm:max-w-md max-h-[90vh] overflow-y-auto"
                x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
                <div class="px-6 pt-5 pb-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-900" x-text="mode === 'add' ? 'Tambah Kelas' : 'Edit Kelas'"></h3>
                </div>
                <form action="<?= base_url('admin/master-data/kelas/simpan') ?>" method="POST" class="px-6 pt-4 pb-6 space-y-4">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" :value="item.id ?? ''">
                    <input type="hidden" name="tab" value="kelas">
                    <div class="space-y-1.5">
                        <label class="block text-sm font-medium text-gray-700">Nama Kelas</label>
                        <input type="text" name="nama" :value="item.nama ?? ''" required placeholder="Contoh: X TKJ 1"
                            class="w-full px-3 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1.5">
                            <label class="block text-sm font-medium text-gray-700">Tingkat</label>
                            <select name="tingkat" required class="w-full px-3 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                                <option value="X" :selected="item.tingkat === 'X' || !item.id">Kelas X</option>
                                <option value="XI" :selected="item.tingkat === 'XI'">Kelas XI</option>
                                <option value="XII" :selected="item.tingkat === 'XII'">Kelas XII</option>
                            </select>
                        </div>
                        <div class="space-y-1.5">
                            <label class="block text-sm font-medium text-gray-700">Jurusan</label>
                            <select name="jurusan_id" required class="w-full px-3 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                                <?php foreach ($jurusans as $j): ?>
                                    <option value="<?= $j->id ?>" :selected="item.jurusan_id == <?= $j->id ?>"><?= esc($j->kode) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-sm font-medium text-gray-700">Wali Kelas</label>
                        <input type="text" name="wali_kelas" :value="item.wali_kelas ?? ''" placeholder="Nama wali kelas"
                            class="w-full px-3 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-sm font-medium text-gray-700">Kapasitas</label>
                        <input type="number" name="kapasitas" :value="item.kapasitas ?? 36" required min="1" placeholder="36"
                            class="w-full px-3 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="flex gap-3 pt-1">
                        <button type="button" @click="open = false" class="flex-1 py-2.5 border border-gray-300 text-gray-700 rounded-xl text-sm font-medium hover:bg-gray-50 transition">Batal</button>
                        <button type="submit" class="flex-1 py-2.5 bg-blue-700 text-white rounded-xl text-sm font-semibold hover:bg-blue-800 transition">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div><!-- /kelas tab -->


    <!-- ══════════════════════════════════════════════════════════
         TAB CONTENT: PERIODE
    ══════════════════════════════════════════════════════════ -->
    <div x-show="tab === 'periode'"
        x-data="{ open: false, item: {}, mode: 'add' }"
        x-cloak>

        <div class="bg-white rounded-2xl border border-gray-200">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-semibold text-gray-900">Periode Pendaftaran</h3>
                <button type="button"
                    @click="item = {}; mode = 'add'; open = true"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-700 text-white rounded-xl text-sm font-medium hover:bg-blue-800 transition-colors">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Tambah Periode
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[500px]">
                    <thead>
                        <tr class="border-b border-gray-100">
                            <th class="py-3 px-4 text-left text-sm font-medium text-gray-500">Nama</th>
                            <th class="py-3 px-4 text-left text-sm font-medium text-gray-500">Tahun Ajaran</th>
                            <th class="py-3 px-4 text-left text-sm font-medium text-gray-500">Tanggal Mulai</th>
                            <th class="py-3 px-4 text-left text-sm font-medium text-gray-500">Tanggal Akhir</th>
                            <th class="py-3 px-4 text-left text-sm font-medium text-gray-500">Status</th>
                            <th class="py-3 px-4 text-left text-sm font-medium text-gray-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($periodes as $p): ?>
                            <tr class="border-b border-gray-50 last:border-0 hover:bg-gray-50 transition-colors">
                                <td class="py-3 px-4 font-medium text-gray-900"><?= esc($p->nama) ?></td>
                                <td class="py-3 px-4 text-sm text-gray-600"><?= esc($p->tahun_ajaran) ?></td>
                                <td class="py-3 px-4 text-sm text-gray-600"><?= $p->tanggal_mulai ? date('d/m/Y', strtotime($p->tanggal_mulai)) : '-' ?></td>
                                <td class="py-3 px-4 text-sm text-gray-600"><?= $p->tanggal_selesai ? date('d/m/Y', strtotime($p->tanggal_selesai)) : '-' ?></td>
                                <td class="py-3 px-4">
                                    <div class="flex flex-wrap gap-1">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold <?= $p->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-500' ?>">
                                            <?= $p->is_active ? '🟢 Aktif' : '⏸️ Nonaktif' ?>
                                        </span>
                                        <?php if ($p->is_published): ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">📢 Published</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-0.5 flex-wrap">
                                        <button type="button"
                                            @click="item = <?= htmlspecialchars(json_encode($p)) ?>; mode = 'edit'; open = true"
                                            class="p-1.5 rounded-lg text-gray-500 hover:bg-gray-100 transition-colors">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                            </svg>
                                        </button>
                                        <a href="<?= base_url('admin/master-data/periode/' . $p->id . '/hapus') ?>"
                                            onclick="return confirm('Hapus periode \'<?= esc($p->nama, 'js') ?>\'?')"
                                            class="p-1.5 rounded-lg text-red-400 hover:bg-red-50 transition-colors">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                            </svg>
                                        </a>
                                        <?php if (!$p->is_active): ?>
                                            <a href="<?= base_url('admin/master-data/periode/' . $p->id . '/aktif') ?>"
                                                onclick="return confirm('Aktifkan periode ini?')"
                                                class="px-2 py-1 rounded-lg text-xs font-medium text-blue-600 hover:bg-blue-50 transition-colors">Set Aktif</a>
                                        <?php endif; ?>
                                        <?php if ($p->is_active && !$p->is_published): ?>
                                            <a href="<?= base_url('admin/master-data/periode/' . $p->id . '/publish') ?>"
                                                onclick="return confirm('Publish pengumuman?')"
                                                class="px-2 py-1 rounded-lg text-xs font-medium text-green-600 hover:bg-green-50 transition-colors">Publish</a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($periodes)): ?>
                            <tr>
                                <td colspan="6" class="py-10 text-center text-sm text-gray-400">Belum ada periode</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal Periode -->
        <div x-show="open"
            x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 flex items-end sm:items-center justify-center sm:p-4"
            style="background:rgba(0,0,0,.5);" @click.self="open = false">
            <div class="bg-white rounded-t-2xl sm:rounded-2xl shadow-xl w-full sm:max-w-lg max-h-[90vh] flex flex-col overflow-y-auto"
                x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
                <div class="px-6 pt-5 pb-4 border-b border-gray-100 flex-shrink-0">
                    <h3 class="font-semibold text-gray-900" x-text="mode === 'add' ? 'Tambah Periode' : 'Edit Periode'"></h3>
                </div>
                <form action="<?= base_url('admin/master-data/periode/simpan') ?>" method="POST" class="px-6 pt-4 pb-6 space-y-4 overflow-y-auto">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" :value="item.id ?? ''">
                    <input type="hidden" name="tab" value="periode">
                    <div class="space-y-1.5">
                        <label class="block text-sm font-medium text-gray-700">Nama Periode</label>
                        <input type="text" name="nama" :value="item.nama ?? ''" required placeholder="Contoh: Gelombang 1"
                            class="w-full px-3 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-sm font-medium text-gray-700">Tahun Ajaran</label>
                        <input type="text" name="tahun_ajaran" :value="item.tahun_ajaran ?? ''" required placeholder="2026/2027"
                            class="w-full px-3 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1.5">
                            <label class="block text-sm font-medium text-gray-700">Tanggal Mulai</label>
                            <input type="date" name="tanggal_mulai" :value="item.tanggal_mulai ?? ''" required
                                class="w-full px-3 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div class="space-y-1.5">
                            <label class="block text-sm font-medium text-gray-700">Tanggal Akhir</label>
                            <input type="date" name="tanggal_selesai" :value="item.tanggal_selesai ?? ''" required
                                class="w-full px-3 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-sm font-medium text-gray-700">Tanggal Pengumuman <span class="text-gray-400 font-normal text-xs">(opsional)</span></label>
                        <input type="date" name="tanggal_pengumuman" :value="item.tanggal_pengumuman ?? ''"
                            class="w-full px-3 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1.5">
                            <label class="block text-sm font-medium text-gray-700">Daftar Ulang Mulai</label>
                            <input type="date" name="tanggal_daftar_ulang_mulai" :value="item.tanggal_daftar_ulang_mulai ?? ''"
                                class="w-full px-3 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div class="space-y-1.5">
                            <label class="block text-sm font-medium text-gray-700">Daftar Ulang Selesai</label>
                            <input type="date" name="tanggal_daftar_ulang_selesai" :value="item.tanggal_daftar_ulang_selesai ?? ''"
                                class="w-full px-3 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    <label class="flex items-center gap-3 cursor-pointer select-none">
                        <div class="relative">
                            <input type="checkbox" name="set_aktif" value="1" :checked="item.is_active == 1" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-300 rounded-full peer-checked:bg-blue-600 transition-colors after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-5"></div>
                        </div>
                        <span class="text-sm font-medium text-gray-700">Aktifkan periode ini</span>
                    </label>
                    <div class="flex gap-3 pt-1">
                        <button type="button" @click="open = false" class="flex-1 py-2.5 border border-gray-300 text-gray-700 rounded-xl text-sm font-medium hover:bg-gray-50 transition">Batal</button>
                        <button type="submit" class="flex-1 py-2.5 bg-blue-700 text-white rounded-xl text-sm font-semibold hover:bg-blue-800 transition">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div><!-- /periode tab -->


    <!-- ══════════════════════════════════════════════════════════
         TAB CONTENT: JENIS DOKUMEN  ← BARU
    ══════════════════════════════════════════════════════════ -->
    <div x-show="tab === 'dokumen'"
        x-data="{ open: false, item: {}, mode: 'add' }"
        x-cloak>

        <div class="bg-white rounded-2xl border border-gray-200">

            <!-- Header -->
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h3 class="font-semibold text-gray-900">Jenis Dokumen</h3>
                    <p class="text-xs text-gray-400 mt-0.5">
                        Dokumen yang harus diupload oleh calon siswa saat pendaftaran.
                        Centang <strong>Wajib</strong> agar dokumen tidak bisa dilewati.
                    </p>
                </div>
                <button type="button"
                    @click="item = {}; mode = 'add'; open = true"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-700 text-white rounded-xl text-sm font-medium hover:bg-blue-800 transition-colors flex-shrink-0">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Tambah Jenis
                </button>
            </div>

            <!-- Legenda -->
            <div class="px-5 py-3 bg-blue-50 border-b border-blue-100 flex items-start gap-2 text-xs text-blue-700">
                <svg class="h-4 w-4 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                </svg>
                <span>
                    Kolom <strong>Kode</strong> digunakan secara internal (huruf kecil, tanpa spasi, contoh: <code class="bg-blue-100 px-1 rounded">akta_lahir</code>).
                    Kolom <strong>Nama</strong> yang tampil ke calon siswa.
                    Toggle <strong>Wajib</strong> menentukan apakah dokumen harus diupload sebelum submit formulir.
                </span>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full min-w-[500px]">
                    <thead>
                        <tr class="border-b border-gray-100">
                            <th class="py-3 px-4 text-left text-sm font-medium text-gray-500 w-8">No</th>
                            <th class="py-3 px-4 text-left text-sm font-medium text-gray-500">Kode</th>
                            <th class="py-3 px-4 text-left text-sm font-medium text-gray-500">Nama Dokumen</th>
                            <th class="py-3 px-4 text-left text-sm font-medium text-gray-500">Keterangan</th>
                            <th class="py-3 px-4 text-center text-sm font-medium text-gray-500">Wajib</th>
                            <th class="py-3 px-4 text-center text-sm font-medium text-gray-500">Status</th>
                            <th class="py-3 px-4 text-left text-sm font-medium text-gray-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($jenisDokumens as $i => $dok): ?>
                            <tr class="border-b border-gray-50 last:border-0 hover:bg-gray-50 transition-colors <?= !$dok->is_active ? 'opacity-50' : '' ?>">
                                <td class="py-3 px-4 text-gray-400 text-sm"><?= $dok->urutan ?></td>
                                <td class="py-3 px-4">
                                    <code class="text-xs bg-gray-100 text-gray-700 px-2 py-0.5 rounded-lg font-mono"><?= esc($dok->kode) ?></code>
                                </td>
                                <td class="py-3 px-4 font-medium text-gray-900 text-sm"><?= esc($dok->nama_dokumen) ?></td>
                                <td class="py-3 px-4 text-gray-500 text-xs max-w-xs truncate" title="<?= esc($dok->keterangan ?? '') ?>">
                                    <?= esc($dok->keterangan ?: '-') ?>
                                </td>

                                <!-- Toggle Wajib via AJAX -->
                                <td class="py-3 px-4 text-center">
                                    <button type="button"
                                        onclick="toggleWajib(<?= $dok->id ?>, this)"
                                        class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors focus:outline-none <?= $dok->is_wajib ? 'bg-red-500' : 'bg-gray-300' ?>"
                                        title="<?= $dok->is_wajib ? 'Wajib — klik untuk jadikan opsional' : 'Opsional — klik untuk jadikan wajib' ?>">
                                        <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow-sm transition-transform <?= $dok->is_wajib ? 'translate-x-4' : 'translate-x-0.5' ?>"></span>
                                    </button>
                                    <p class="text-xs mt-0.5 <?= $dok->is_wajib ? 'text-red-600 font-semibold' : 'text-gray-400' ?>">
                                        <?= $dok->is_wajib ? 'Wajib' : 'Opsional' ?>
                                    </p>
                                </td>

                                <!-- Status Aktif/Nonaktif -->
                                <td class="py-3 px-4 text-center">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold
                                        <?= $dok->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-500' ?>">
                                        <?= $dok->is_active ? '🟢 Aktif' : '⏸️ Nonaktif' ?>
                                    </span>
                                </td>

                                <!-- Aksi -->
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-0.5">
                                        <!-- Edit -->
                                        <button type="button"
                                            @click="item = <?= htmlspecialchars(json_encode($dok)) ?>; mode = 'edit'; open = true"
                                            class="p-1.5 rounded-lg text-gray-500 hover:bg-gray-100 transition-colors" title="Edit">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                            </svg>
                                        </button>
                                        <!-- Toggle Aktif/Nonaktif -->
                                        <a href="<?= base_url('admin/master-data/dokumen/' . $dok->id . '/toggle') ?>"
                                            onclick="return confirm('<?= $dok->is_active ? 'Nonaktifkan' : 'Aktifkan' ?> jenis dokumen \'<?= esc($dok->nama_dokumen, 'js') ?>\'?')"
                                            class="p-1.5 rounded-lg transition-colors <?= $dok->is_active ? 'text-yellow-500 hover:bg-yellow-50' : 'text-green-500 hover:bg-green-50' ?>"
                                            title="<?= $dok->is_active ? 'Nonaktifkan' : 'Aktifkan' ?>">
                                            <?php if ($dok->is_active): ?>
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                                </svg>
                                            <?php else: ?>
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            <?php endif; ?>
                                        </a>
                                        <!-- Hapus Permanen (hanya jika belum dipakai) -->
                                        <a href="<?= base_url('admin/master-data/dokumen/' . $dok->id . '/hapus') ?>"
                                            onclick="return confirm('Hapus PERMANEN jenis dokumen \'<?= esc($dok->nama_dokumen, 'js') ?>\'?\n\nTidak dapat dilakukan jika sudah dipakai pendaftar.\nSebaiknya nonaktifkan saja.')"
                                            class="p-1.5 rounded-lg text-red-400 hover:bg-red-50 transition-colors" title="Hapus permanen">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                            </svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($jenisDokumens)): ?>
                            <tr>
                                <td colspan="7" class="py-12 text-center text-sm text-gray-400">
                                    <svg class="h-10 w-10 mx-auto mb-2 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                    </svg>
                                    Belum ada jenis dokumen. Klik "Tambah Jenis" untuk memulai.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ── Modal Tambah / Edit Jenis Dokumen ─────────────────────── -->
        <div x-show="open"
            x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 flex items-end sm:items-center justify-center sm:p-4"
            style="background:rgba(0,0,0,.5);" @click.self="open = false">
            <div class="bg-white rounded-t-2xl sm:rounded-2xl shadow-xl w-full sm:max-w-md max-h-[90vh] overflow-y-auto"
                x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
                <div class="px-6 pt-5 pb-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="font-semibold text-gray-900" x-text="mode === 'add' ? 'Tambah Jenis Dokumen' : 'Edit Jenis Dokumen'"></h3>
                    <button @click="open = false" class="text-gray-400 hover:text-gray-600 transition">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <form action="<?= base_url('admin/master-data/dokumen/simpan') ?>" method="POST" class="px-6 pt-4 pb-6 space-y-4">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" :value="item.id ?? ''">

                    <!-- Kode -->
                    <div class="space-y-1.5">
                        <label class="block text-sm font-medium text-gray-700">
                            Kode <span class="text-red-500">*</span>
                            <span class="text-gray-400 font-normal text-xs ml-1">(huruf kecil, angka, underscore — tidak bisa diubah setelah dipakai)</span>
                        </label>
                        <input type="text" name="kode" :value="item.kode ?? ''" required
                            placeholder="Contoh: akta_lahir"
                            pattern="[a-z0-9_]+"
                            title="Hanya huruf kecil, angka, dan underscore"
                            :readonly="mode === 'edit' && item.id"
                            :class="mode === 'edit' && item.id ? 'bg-gray-50 text-gray-500 cursor-not-allowed' : 'bg-white'"
                            class="w-full px-3 py-2.5 border border-gray-300 rounded-xl text-sm font-mono focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <p x-show="mode === 'edit' && item.id" class="text-xs text-amber-600 flex items-center gap-1">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126z" />
                            </svg>
                            Kode tidak dapat diubah untuk menghindari kerusakan data yang sudah ada.
                        </p>
                    </div>

                    <!-- Nama Dokumen -->
                    <div class="space-y-1.5">
                        <label class="block text-sm font-medium text-gray-700">Nama Dokumen <span class="text-red-500">*</span></label>
                        <input type="text" name="nama_dokumen" :value="item.nama_dokumen ?? ''" required
                            placeholder="Contoh: Akta Kelahiran"
                            class="w-full px-3 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <!-- Keterangan -->
                    <div class="space-y-1.5">
                        <label class="block text-sm font-medium text-gray-700">Keterangan <span class="text-gray-400 font-normal text-xs">(opsional)</span></label>
                        <textarea name="keterangan" rows="2"
                            :value="item.keterangan ?? ''"
                            placeholder="Instruksi singkat untuk calon siswa, mis: Scan atau foto jelas, ukuran maks 2MB"
                            class="w-full px-3 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
                            x-text="item.keterangan ?? ''"></textarea>
                    </div>

                    <!-- Urutan -->
                    <div class="space-y-1.5">
                        <label class="block text-sm font-medium text-gray-700">Urutan Tampil</label>
                        <input type="number" name="urutan" :value="item.urutan ?? ''" min="1"
                            placeholder="Kosongkan untuk otomatis"
                            class="w-full px-3 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <!-- Switch Wajib + Switch Aktif (2 kolom) -->
                    <div class="grid grid-cols-2 gap-4">
                        <label class="flex items-center gap-3 cursor-pointer select-none">
                            <div class="relative">
                                <input type="checkbox" name="is_wajib" value="1" :checked="item.is_wajib == 1" class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-300 rounded-full peer-checked:bg-red-500 transition-colors after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-5"></div>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-700 block">Wajib</span>
                                <span class="text-xs text-gray-400">Harus diupload</span>
                            </div>
                        </label>

                        <label class="flex items-center gap-3 cursor-pointer select-none">
                            <div class="relative">
                                <input type="checkbox" name="is_active" value="1" :checked="item.is_active != 0 || mode === 'add'" class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-300 rounded-full peer-checked:bg-blue-600 transition-colors after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-5"></div>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-700 block">Aktif</span>
                                <span class="text-xs text-gray-400">Tampil di form</span>
                            </div>
                        </label>
                    </div>

                    <div class="flex gap-3 pt-1">
                        <button type="button" @click="open = false"
                            class="flex-1 py-2.5 border border-gray-300 text-gray-700 rounded-xl text-sm font-medium hover:bg-gray-50 transition">
                            Batal
                        </button>
                        <button type="submit"
                            class="flex-1 py-2.5 bg-blue-700 text-white rounded-xl text-sm font-semibold hover:bg-blue-800 transition">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div><!-- /dokumen tab -->

</div>

<!-- ── Script toggle wajib via AJAX ─────────────────────────────────── -->
<script>
    async function toggleWajib(id, btn) {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
        const form = new FormData();
        form.append('<?= csrf_token() ?>', csrf);

        try {
            const res = await fetch(`<?= base_url('admin/master-data/dokumen/') ?>${id}/toggle-wajib`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: form,
            });
            const data = await res.json();
            if (!data.success) {
                alert(data.message);
                return;
            }

            // Update UI tanpa reload
            const isWajib = data.is_wajib == 1;
            const track = btn;
            const thumb = btn.querySelector('span');
            const label = btn.nextElementSibling;

            track.classList.toggle('bg-red-500', isWajib);
            track.classList.toggle('bg-gray-300', !isWajib);
            thumb.classList.toggle('translate-x-4', isWajib);
            thumb.classList.toggle('translate-x-0.5', !isWajib);
            label.textContent = isWajib ? 'Wajib' : 'Opsional';
            label.className = `text-xs mt-0.5 ${isWajib ? 'text-red-600 font-semibold' : 'text-gray-400'}`;
            track.title = isWajib ? 'Wajib — klik untuk jadikan opsional' : 'Opsional — klik untuk jadikan wajib';
        } catch (e) {
            alert('Gagal mengubah status. Coba lagi.');
        }
    }
</script>