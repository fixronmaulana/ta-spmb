<?php
/*
 * File: app/Modules/Verifikasi/Views/index.php
 * FIX: pakai $row->nama_tampil (COALESCE dari controller),
 *      pagination manual (bukan CI pager), semua kolom konsisten.
 */
?>

<div class="space-y-6">

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold font-serif">Verifikasi Dokumen</h1>
            <p class="text-sm text-gray-500">Verifikasi dokumen dan validasi data pendaftar</p>
        </div>
        <div class="text-sm text-gray-500">
            <?= number_format($total ?? 0) ?> pendaftar ditemukan
        </div>
    </div>

    <!-- Flash Messages -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 text-sm flex items-center gap-2">
            <i class="fas fa-check-circle text-green-500"></i>
            <?= esc(session()->getFlashdata('success')) ?>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 text-sm flex items-center gap-2">
            <i class="fas fa-exclamation-circle text-red-500"></i>
            <?= esc(session()->getFlashdata('error')) ?>
        </div>
    <?php endif; ?>

    <!-- Filters -->
    <div class="bg-white rounded-2xl border border-gray-200 p-4">
        <form method="get" class="flex flex-col sm:flex-row gap-3">

            <!-- Search -->
            <div class="relative flex-1">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z" />
                </svg>
                <input type="text" name="search" value="<?= esc($search ?? '') ?>"
                    placeholder="Cari nama atau nomor pendaftaran..."
                    class="w-full pl-9 pr-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>

            <!-- Status -->
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400 pointer-events-none" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z" />
                </svg>
                <select name="status" class="pl-9 pr-8 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 w-full sm:w-52 appearance-none bg-white" onchange="this.form.submit()">
                    <option value="all" <?= ($statusFilter ?? '') === 'all'        ? 'selected' : '' ?>>Semua Status</option>
                    <option value="submitted" <?= ($statusFilter ?? 'submitted') === 'submitted'  ? 'selected' : '' ?>>Menunggu Verifikasi</option>
                    <option value="verifikasi" <?= ($statusFilter ?? '') === 'verifikasi' ? 'selected' : '' ?>>Dalam Verifikasi</option>
                    <option value="seleksi" <?= ($statusFilter ?? '') === 'seleksi'    ? 'selected' : '' ?>>Lolos ke Seleksi</option>
                </select>
                <svg class="absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400 pointer-events-none" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                </svg>
            </div>

            <!-- Jurusan -->
            <div class="relative">
                <select name="jurusan" class="pl-4 pr-8 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 w-full sm:w-44 appearance-none bg-white" onchange="this.form.submit()">
                    <option value="">Semua Jurusan</option>
                    <?php foreach ($jurusans ?? [] as $j): ?>
                        <option value="<?= esc($j->kode) ?>" <?= ($jurusanFilter ?? '') === $j->kode ? 'selected' : '' ?>>
                            <?= esc($j->kode) ?> — <?= esc($j->nama) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <svg class="absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400 pointer-events-none" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                </svg>
            </div>

            <button type="submit" class="px-4 py-2.5 bg-blue-700 text-white rounded-xl text-sm font-medium hover:bg-blue-800 transition sm:w-auto">
                Cari
            </button>
        </form>
    </div>

    <!-- Status Tabs / Badges -->
    <div class="flex items-center gap-2 flex-wrap">
        <?php
        $tabDefs = [
            'submitted'  => 'Menunggu',
            'verifikasi' => 'Dalam Verif.',
            'seleksi'    => 'Lolos Seleksi',
            'all'        => 'Semua',
        ];
        foreach ($tabDefs as $key => $tabLabel):
            $isActive = ($statusFilter ?? 'submitted') === $key;
            $count    = $badges[$key] ?? 0;
            $qs       = http_build_query(array_filter([
                'status'  => $key,
                'search'  => $search   ?? '',
                'jurusan' => $jurusanFilter ?? '',
            ]));
        ?>
            <a href="?<?= $qs ?>"
                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-medium transition-colors <?= $isActive ? 'bg-blue-700 text-white' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50' ?>">
                <?= $tabLabel ?>
                <span class="<?= $isActive ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-700' ?> text-xs px-1.5 py-0.5 rounded-full font-bold min-w-[20px] text-center">
                    <?= $count ?>
                </span>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[700px]">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="text-left py-3 px-5 text-sm font-medium text-gray-500">Pendaftar</th>
                        <th class="text-left py-3 px-4 text-sm font-medium text-gray-500">No. Pendaftaran</th>
                        <th class="text-left py-3 px-4 text-sm font-medium text-gray-500">Jurusan</th>
                        <th class="text-left py-3 px-4 text-sm font-medium text-gray-500">Status</th>
                        <th class="text-left py-3 px-4 text-sm font-medium text-gray-500">Tgl Submit</th>
                        <th class="py-3 px-4"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($pendaftarans)): ?>
                        <tr>
                            <td colspan="6" class="py-16 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-200 mb-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 13.5h3.86a2.25 2.25 0 012.012 1.244l.256.512a2.25 2.25 0 002.013 1.244h3.218a2.25 2.25 0 002.013-1.244l.256-.512a2.25 2.25 0 012.013-1.244h3.859m-19.5.338V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 00-2.15-1.588H6.911a2.25 2.25 0 00-2.15 1.588L2.35 13.177a2.25 2.25 0 00-.1.661z" />
                                </svg>
                                <p class="text-sm text-gray-400">Tidak ada data untuk filter ini</p>
                                <?php if (($statusFilter ?? 'submitted') === 'submitted'): ?>
                                    <p class="text-xs text-gray-300 mt-1">Calon siswa belum ada yang submit formulir</p>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($pendaftarans as $row): ?>
                        <tr class="border-b border-gray-50 last:border-0 hover:bg-gray-50 transition-colors">
                            <td class="py-3 px-5">
                                <p class="font-medium text-gray-900">
                                    <?= esc($row->nama_tampil ?? $row->nama_calon ?? '-') ?>
                                </p>
                                <p class="text-xs text-gray-400"><?= esc($row->email_calon ?? '-') ?></p>
                            </td>
                            <td class="py-3 px-4 font-mono text-sm text-gray-600">
                                <?= esc($row->no_pendaftaran ?? '-') ?>
                            </td>
                            <td class="py-3 px-4">
                                <span class="text-sm font-medium text-gray-700">
                                    <?= esc($row->jurusan_pilihan1_kode ?? '-') ?>
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <?= status_label($row->status) ?>
                            </td>
                            <td class="py-3 px-4 text-xs text-gray-500">
                                <?= $row->submitted_at ? date('d/m/Y H:i', strtotime($row->submitted_at)) : '-' ?>
                            </td>
                            <td class="py-3 px-4">
                                <a href="<?= base_url('admin/verifikasi/' . $row->id) ?>"
                                    class="inline-flex items-center gap-1.5 border border-gray-300 rounded-lg px-3 py-1 text-xs font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                                    <svg class="h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <?= $row->status === 'submitted' ? 'Verifikasi' : 'Lihat' ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination manual -->
        <?php if (($totalPages ?? 1) > 1): ?>
            <div class="px-5 py-4 border-t border-gray-100 flex items-center justify-between">
                <p class="text-xs text-gray-500">
                    Halaman <?= $currentPage ?> dari <?= $totalPages ?> (<?= number_format($total) ?> data)
                </p>
                <div class="flex items-center gap-1">
                    <?php
                    $qsBase = http_build_query(array_filter([
                        'status'  => $statusFilter  ?? '',
                        'search'  => $search        ?? '',
                        'jurusan' => $jurusanFilter  ?? '',
                    ]));
                    ?>
                    <?php if ($currentPage > 1): ?>
                        <a href="?<?= $qsBase ?>&page=<?= $currentPage - 1 ?>"
                            class="px-3 py-1.5 text-xs border border-gray-300 rounded-lg hover:bg-gray-50 text-gray-700">
                            &laquo; Prev
                        </a>
                    <?php endif; ?>

                    <?php for ($p = max(1, $currentPage - 2); $p <= min($totalPages, $currentPage + 2); $p++): ?>
                        <a href="?<?= $qsBase ?>&page=<?= $p ?>"
                            class="px-3 py-1.5 text-xs border rounded-lg <?= $p === $currentPage ? 'bg-blue-700 text-white border-blue-700' : 'border-gray-300 hover:bg-gray-50 text-gray-700' ?>">
                            <?= $p ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($currentPage < $totalPages): ?>
                        <a href="?<?= $qsBase ?>&page=<?= $currentPage + 1 ?>"
                            class="px-3 py-1.5 text-xs border border-gray-300 rounded-lg hover:bg-gray-50 text-gray-700">
                            Next &raquo;
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

</div>