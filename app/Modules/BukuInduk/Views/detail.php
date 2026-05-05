<div class="max-w-2xl mx-auto space-y-5">
    <div class="flex items-center gap-3">
        <a href="<?= base_url('admin/buku-induk') ?>" class="text-sm text-blue-600 hover:underline flex items-center gap-1">
            <i class="fas fa-arrow-left text-xs"></i> Buku Induk
        </a>
        <span class="text-gray-300">/</span>
        <span class="text-sm text-gray-600"><?= esc($siswa->nis) ?></span>
    </div>

    <!-- NIS Card -->
    <div class="bg-gradient-to-r from-emerald-700 to-teal-700 rounded-2xl p-6 text-white">
        <p class="text-emerald-100 text-xs mb-1">Nomor Induk Siswa</p>
        <h2 class="text-3xl font-bold font-mono tracking-widest"><?= esc($siswa->nis) ?></h2>
        <p class="text-emerald-100 text-sm mt-1"><?= esc($siswa->nama_lengkap) ?></p>
        <div class="flex flex-wrap items-center gap-2 mt-3">
            <span class="text-xs bg-white/20 px-2.5 py-1 rounded-full"><?= esc($siswa->jurusan_nama) ?></span>
            <span class="text-xs bg-white/20 px-2.5 py-1 rounded-full"><?= esc($siswa->kelas_nama ?? 'Kelas belum ditentukan') ?></span>
            <span class="text-xs bg-white/20 px-2.5 py-1 rounded-full">TA <?= $siswa->tahun_masuk ?></span>
        </div>
    </div>

    <!-- Detail -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200">
        <div class="px-5 py-4 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-900">Data Siswa Lengkap</h3>
        </div>
        <div class="px-5 py-4 grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
            <?php $rows = [
                ['NISN', $siswa->nisn ?? '-'],
                ['Jenis Kelamin', $siswa->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan'],
                ['Tempat Lahir', $siswa->tempat_lahir ?? '-'],
                ['Tanggal Lahir', format_tanggal($siswa->tanggal_lahir ?? null)],
                ['Agama', $siswa->agama ?? '-'],
                ['Alamat', $siswa->alamat ?? '-'],
                ['No. HP', $siswa->no_hp ?? '-'],
                ['Nama Ayah', $siswa->nama_ayah ?? '-'],
                ['Nama Ibu', $siswa->nama_ibu ?? '-'],
                ['No. HP Ortu', $siswa->no_hp_ortu ?? '-'],
                ['Jurusan', $siswa->jurusan_nama ?? '-'],
                ['Kelas', $siswa->kelas_nama ?? '-'],
                ['Status Siswa', ucfirst($siswa->status_siswa ?? '-')],
                ['Dikonversi oleh', $siswa->admin_name ?? '-'],
                ['Tanggal Konversi', date('d/m/Y H:i', strtotime($siswa->converted_at ?? 'now'))],
            ]; ?>
            <?php foreach ($rows as [$label, $val]): ?>
            <div>
                <p class="text-xs text-gray-400"><?= $label ?></p>
                <p class="font-medium text-gray-800 mt-0.5"><?= esc($val) ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="flex flex-col sm:flex-row gap-3">
        <a href="<?= base_url('admin/buku-induk/' . $siswa->id . '/cetak-kartu') ?>"
           target="_blank"
           class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-emerald-700 text-white text-sm font-medium rounded-xl hover:bg-emerald-800 transition w-full sm:w-auto">
            <i class="fas fa-id-card"></i> Cetak Kartu Siswa
        </a>
        <a href="<?= base_url('admin/buku-induk') ?>"
           class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-gray-100 text-gray-700 text-sm font-medium rounded-xl hover:bg-gray-200 transition w-full sm:w-auto">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
</div>
