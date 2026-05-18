<div class="max-w-4xl mx-auto space-y-6">
    <div>
        <h1 class="text-xl font-bold text-gray-900">Approval Hasil Seleksi</h1>
        <p class="text-sm text-gray-500">Review dan setujui hasil seleksi sebelum pengumuman dipublikasikan</p>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <?php
        $lulus     = array_values($lulus);
        $tidakLulus = array_values($tidakLulus);
        ?>
        <div class="bg-green-50 border border-green-100 rounded-2xl p-4 text-center">
            <p class="text-3xl font-bold text-green-700"><?= count($lulus) ?></p>
            <p class="text-xs text-green-600 mt-1">Dinyatakan Lulus</p>
        </div>
        <div class="bg-red-50 border border-red-100 rounded-2xl p-4 text-center">
            <p class="text-3xl font-bold text-red-700"><?= count($tidakLulus) ?></p>
            <p class="text-xs text-red-600 mt-1">Tidak Diterima</p>
        </div>
        <div class="bg-blue-50 border border-blue-100 rounded-2xl p-4 text-center">
            <p class="text-3xl font-bold text-blue-700"><?= count($lulus) + count($tidakLulus) ?></p>
            <p class="text-xs text-blue-600 mt-1">Total Peserta Seleksi</p>
        </div>
        <div class="bg-gray-50 border border-gray-200 rounded-2xl p-4 text-center">
            <p class="text-3xl font-bold text-gray-700"><?= $periode ? $periode->tahun_ajaran : '-' ?></p>
            <p class="text-xs text-gray-500 mt-1">Tahun Ajaran</p>
        </div>
    </div>

    <!-- Tabel Peserta Lulus -->
    <div class="bg-white rounded-2xl shadow-sm border border-green-200">
        <div class="px-5 py-4 border-b border-green-100 bg-green-50 rounded-t-2xl">
            <h3 class="text-sm font-semibold text-green-800">Peserta Dinyatakan LULUS (<?= count($lulus) ?>)</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[500px] text-sm">
                <thead class="border-b border-gray-100">
                    <tr>
                        <th class="text-left px-5 py-2.5 text-xs font-semibold text-gray-500 uppercase">Nama</th>
                        <th class="text-left px-3 py-2.5 text-xs font-semibold text-gray-500 uppercase">Asal Sekolah</th>
                        <th class="text-left px-3 py-2.5 text-xs font-semibold text-gray-500 uppercase">Jurusan Diterima</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <?php foreach ($lulus as $p): ?>
                        <tr>
                            <td class="px-5 py-2.5 font-medium text-gray-900"><?= esc($p->nama_lengkap ?? '-') ?></td>
                            <td class="px-3 py-2.5 text-xs text-gray-500"><?= esc($p->asal_sekolah ?? '-') ?></td>
                            <td class="px-3 py-2.5 text-sm text-blue-700 font-medium"><?= esc($p->jurusan_diterima_nama ?? $p->jurusan_pilihan1_nama ?? '-') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Tabel Tidak Lulus (collapsed) -->
    <div x-data="{ showTidakLulus: false }" class="bg-white rounded-2xl shadow-sm border border-gray-200">
        <button type="button" @click="showTidakLulus = !showTidakLulus"
            class="w-full px-5 py-4 flex items-center justify-between text-left border-b border-gray-100 rounded-t-2xl hover:bg-gray-50 transition">
            <h3 class="text-sm font-semibold text-gray-700">Peserta Tidak Diterima (<?= count($tidakLulus) ?>)</h3>
            <i class="fas fa-chevron-down text-gray-400 transition" :class="showTidakLulus ? 'rotate-180' : ''"></i>
        </button>
        <div x-show="showTidakLulus" class="overflow-x-auto">
            <table class="w-full min-w-[500px] text-sm">
                <thead class="border-b border-gray-100 bg-gray-50">
                    <tr>
                        <th class="text-left px-5 py-2.5 text-xs font-semibold text-gray-500">Nama</th>
                        <th class="text-left px-3 py-2.5 text-xs font-semibold text-gray-500">Pilihan 1</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <?php foreach ($tidakLulus as $p): ?>
                        <tr>
                            <td class="px-5 py-2.5 text-gray-700"><?= esc($p->nama_lengkap ?? '-') ?></td>
                            <td class="px-3 py-2.5 text-xs text-gray-500"><?= esc($p->jurusan_pilihan1_nama ?? '-') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Action Buttons -->
    <?php if ($periode && !$periode->is_published): ?>
        <div class="bg-white rounded-2xl border border-gray-200 p-6">
            <h3 class="text-sm font-semibold text-gray-900 mb-4">Keputusan Kepala Sekolah</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <form action="<?= base_url('kepala-sekolah/seleksi/approve') ?>" method="POST">
                    <?= csrf_field() ?>
                    <input type="hidden" name="periode_id" value="<?= $periode->id ?>">
                    <div class="p-4 bg-green-50 border border-green-200 rounded-xl mb-3">
                        <p class="text-sm text-green-800 font-medium"><i class="fas fa-check-circle mr-1"></i> Setujui & Publikasikan</p>
                        <p class="text-xs text-green-600 mt-1">Pengumuman akan langsung dipublikasikan dan notifikasi dikirim ke semua peserta.</p>
                    </div>
                    <button type="submit"
                        onclick="return confirm('Setujui dan publikasikan hasil seleksi? Tindakan ini tidak dapat dibatalkan.')"
                        class="w-full py-3 bg-green-700 text-white text-sm font-bold rounded-xl hover:bg-green-800 transition">
                        <i class="fas fa-check-double mr-2"></i> Setujui & Publikasikan Pengumuman
                    </button>
                </form>

                <form action="<?= base_url('kepala-sekolah/seleksi/revisi') ?>" method="POST">
                    <?= csrf_field() ?>
                    <div class="p-4 bg-orange-50 border border-orange-200 rounded-xl mb-3">
                        <p class="text-sm text-orange-800 font-medium"><i class="fas fa-undo-alt mr-1"></i> Kembalikan untuk Revisi</p>
                        <p class="text-xs text-orange-600 mt-1">Minta admin TU untuk memperbaiki data seleksi.</p>
                    </div>
                    <textarea name="catatan" rows="2" required placeholder="Catatan revisi untuk admin..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-orange-400 resize-none mb-2"></textarea>
                    <button type="submit"
                        class="w-full py-2.5 bg-orange-50 text-orange-700 text-sm font-medium rounded-xl border border-orange-200 hover:bg-orange-100 transition">
                        <i class="fas fa-undo-alt mr-2"></i> Kirim Revisi
                    </button>
                </form>
            </div>
        </div>
    <?php else: ?>
        <div class="bg-green-50 border border-green-200 rounded-2xl p-5 text-center">
            <i class="fas fa-check-circle text-green-600 text-3xl mb-2"></i>
            <p class="text-sm font-semibold text-green-800">Pengumuman sudah dipublikasikan.</p>
        </div>
    <?php endif; ?>
</div>