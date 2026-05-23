<!-- 
    File: app/Modules/Dashboard/Views/admin_tu.php
    Updated: Tambah filter periode + tampilkan data terbaru sesuai periode
-->

<div class="space-y-6">

    <!-- ── Page Header + Filter Periode ────────────────────────── -->
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold font-serif">Dashboard Admin TU</h1>
            <p class="text-sm text-gray-500">
                <?php if ($periodeTerpilih): ?>
                    Data periode: <span class="font-medium text-gray-700"><?= esc($periodeTerpilih->nama) ?></span>
                    (<?= date('d M Y', strtotime($periodeTerpilih->tanggal_mulai)) ?>
                    – <?= date('d M Y', strtotime($periodeTerpilih->tanggal_selesai)) ?>)
                    <?php if ($periodeTerpilih->is_active): ?>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700 ml-1">Aktif</span>
                    <?php endif; ?>
                <?php else: ?>
                    Semua periode
                <?php endif; ?>
            </p>
        </div>

        <!-- Dropdown Filter Periode -->
        <form method="get" action="" class="flex items-center gap-2 flex-shrink-0">
            <label for="periode_id" class="text-sm font-medium text-gray-700 whitespace-nowrap">Filter Periode:</label>
            <select
                id="periode_id"
                name="periode_id"
                onchange="this.form.submit()"
                class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm text-gray-700 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="0" <?= $periodeIdFilter === 0 ? 'selected' : '' ?>>Semua Periode</option>
                <?php foreach ($allPeriode as $p): ?>
                    <option value="<?= $p->id ?>" <?= $periodeIdFilter === (int)$p->id ? 'selected' : '' ?>>
                        <?= esc($p->nama) ?><?= $p->is_active ? ' ★' : '' ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <!-- ── Stats Cards ──────────────────────────────────────────── -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

        <!-- Total Pendaftar -->
        <div class="bg-white rounded-2xl border border-gray-200 border-l-4 border-l-blue-700 p-4">
            <div class="flex items-center gap-4">
                <div class="h-12 w-12 rounded-lg bg-blue-50 flex items-center justify-center flex-shrink-0">
                    <svg class="h-6 w-6 text-blue-700" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900"><?= number_format($stats['total'] ?? 0) ?></p>
                    <p class="text-sm text-gray-500">Total Pendaftar</p>
                </div>
            </div>
        </div>

        <!-- Menunggu Verifikasi -->
        <div class="bg-white rounded-2xl border border-gray-200 border-l-4 border-l-yellow-500 p-4">
            <div class="flex items-center gap-4">
                <div class="h-12 w-12 rounded-lg bg-yellow-50 flex items-center justify-center flex-shrink-0">
                    <svg class="h-6 w-6 text-yellow-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900"><?= number_format($stats['submitted'] ?? 0) ?></p>
                    <p class="text-sm text-gray-500">Menunggu Verifikasi</p>
                </div>
            </div>
        </div>

        <!-- Terverifikasi -->
        <div class="bg-white rounded-2xl border border-gray-200 border-l-4 border-l-green-600 p-4">
            <div class="flex items-center gap-4">
                <div class="h-12 w-12 rounded-lg bg-green-50 flex items-center justify-center flex-shrink-0">
                    <svg class="h-6 w-6 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <?php
                    $terverifikasi = ($stats['verifikasi'] ?? 0)
                        + ($stats['seleksi'] ?? 0)
                        + ($stats['lulus'] ?? 0)
                        + ($stats['daftar_ulang'] ?? 0)
                        + ($stats['siswa_aktif'] ?? 0);
                    ?>
                    <p class="text-2xl font-bold text-gray-900"><?= number_format($terverifikasi) ?></p>
                    <p class="text-sm text-gray-500">Terverifikasi</p>
                </div>
            </div>
        </div>

        <!-- Ditolak/Perlu Perbaikan -->
        <div class="bg-white rounded-2xl border border-gray-200 border-l-4 border-l-red-500 p-4">
            <div class="flex items-center gap-4">
                <div class="h-12 w-12 rounded-lg bg-red-50 flex items-center justify-center flex-shrink-0">
                    <svg class="h-6 w-6 text-red-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900"><?= number_format($stats['tidak_lulus'] ?? 0) ?></p>
                    <p class="text-sm text-gray-500">Ditolak/Perlu Perbaikan</p>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Charts Row ───────────────────────────────────────────── -->
    <div class="grid lg:grid-cols-2 gap-6">

        <!-- Bar Chart — Jumlah Pendaftar per Jurusan -->
        <div class="bg-white rounded-2xl border border-gray-200 p-5">
            <div class="flex items-center gap-2 mb-5">
                <svg class="h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                </svg>
                <h3 class="font-semibold text-gray-900">Jumlah Pendaftar per Jurusan</h3>
            </div>
            <div style="height:min(300px,55vw);position:relative;">
                <canvas id="barChartJurusan"></canvas>
            </div>
        </div>

        <!-- Pie/Donut Chart — Distribusi Pendaftar -->
        <div class="bg-white rounded-2xl border border-gray-200 p-5">
            <h3 class="font-semibold text-gray-900 mb-5">Distribusi Pendaftar</h3>
            <div style="height:min(300px,55vw);position:relative;">
                <canvas id="pieChartDistribusi"></canvas>
            </div>
        </div>
    </div>

    <!-- ── Pendaftar Terbaru Table ──────────────────────────────── -->
    <div class="bg-white rounded-2xl border border-gray-200">
        <div class="px-5 py-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
                <h3 class="font-semibold text-gray-900">Pendaftar Terbaru</h3>
                <p class="text-xs text-gray-400 mt-0.5">
                    Menampilkan 10 pendaftar terbaru
                    <?= $periodeTerpilih ? 'pada periode ' . esc($periodeTerpilih->nama) : 'dari semua periode' ?>
                </p>
            </div>
            <a href="<?= base_url('admin/verifikasi') ?>"
                class="inline-flex items-center border border-gray-300 rounded-lg px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors self-start sm:self-auto">
                Lihat Semua
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[600px]">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50/50">
                        <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">No. Pendaftaran</th>
                        <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Nama Calon Siswa</th>
                        <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Jurusan</th>
                        <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Tgl Daftar</th>
                        <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pendaftaranTerbaru as $row): ?>
                        <tr class="border-b border-gray-50 last:border-0 hover:bg-gray-50 transition-colors">
                            <td class="py-3 px-4 font-mono text-sm text-gray-600">
                                <?= esc($row->no_pendaftaran ?? '-') ?>
                            </td>
                            <td class="py-3 px-4">
                                <p class="font-medium text-gray-900 text-sm"><?= esc($row->nama_calon) ?></p>
                                <p class="text-xs text-gray-400"><?= esc($row->email_calon ?? '') ?></p>
                            </td>
                            <td class="py-3 px-4 text-sm text-gray-700">
                                <?= esc($row->jurusan_pilihan1_kode ?? '-') ?>
                            </td>
                            <td class="py-3 px-4 text-sm text-gray-500">
                                <?= $row->created_at ? date('d M Y', strtotime($row->created_at)) : '-' ?>
                            </td>
                            <td class="py-3 px-4">
                                <?= status_label($row->status) ?>
                            </td>
                            <td class="py-3 px-4">
                                <a href="<?= base_url('admin/verifikasi/' . $row->id) ?>"
                                    class="inline-flex items-center border border-gray-300 rounded-lg px-3 py-1 text-xs font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                                    <?= $row->status === 'submitted' ? 'Verifikasi' : 'Lihat' ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($pendaftaranTerbaru)): ?>
                        <tr>
                            <td colspan="6" class="py-12 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <svg class="h-10 w-10 text-gray-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                    </svg>
                                    <p class="text-sm text-gray-400">Belum ada pendaftaran pada periode ini</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ── Quick Actions ───────────────────────────────────────── -->
    <div class="grid sm:grid-cols-3 gap-4">

        <!-- Verifikasi Dokumen -->
        <a href="<?= base_url('admin/verifikasi') ?>"
            class="bg-white border border-gray-200 rounded-2xl py-6 px-4 flex flex-col items-center gap-2 hover:bg-gray-50 transition-colors text-center">
            <svg class="h-8 w-8 text-blue-700" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
            </svg>
            <span class="font-medium text-gray-800 text-sm">Verifikasi Dokumen</span>
            <?php if ($needVerifikasi > 0): ?>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">
                    <?= $needVerifikasi ?> menunggu
                </span>
            <?php endif; ?>
        </a>

        <!-- Tetapkan Kelulusan -->
        <a href="<?= base_url('admin/seleksi') ?>"
            class="bg-white border border-gray-200 rounded-2xl py-6 px-4 flex flex-col items-center gap-2 hover:bg-gray-50 transition-colors text-center">
            <svg class="h-8 w-8 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            <span class="font-medium text-gray-800 text-sm">Tetapkan Kelulusan</span>
        </a>

        <!-- Kelola Data Master -->
        <a href="<?= base_url('admin/master-data') ?>"
            class="bg-white border border-gray-200 rounded-2xl py-6 px-4 flex flex-col items-center gap-2 hover:bg-gray-50 transition-colors text-center">
            <svg class="h-8 w-8 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582 4-8 4m16 0c0 2.21-3.582 4-8 4" />
            </svg>
            <span class="font-medium text-gray-800 text-sm">Kelola Data Master</span>
        </a>
    </div>

</div>

<!-- ── Chart.js Scripts ──────────────────────────────────────────── -->
<script>
    (function() {
        const CHART_COLORS = [
            '#1e40af',
            '#d97706',
            '#059669',
            '#7c3aed',
            '#0891b2',
        ];

        const statsByJurusan = <?= json_encode(array_map(function ($row) {
                                    return [
                                        'kode'         => $row->kode ?? '',
                                        'jurusan'      => $row->jurusan ?? '',
                                        'total_daftar' => (int)($row->total_daftar ?? 0),
                                        'kuota'        => (int)($row->kuota ?? 0),
                                    ];
                                }, $statsByJurusan ?? [])) ?>;

        const tooltipDefaults = {
            backgroundColor: '#fff',
            titleColor: '#111827',
            bodyColor: '#6b7280',
            borderColor: '#e5e7eb',
            borderWidth: 1,
            padding: 12,
            cornerRadius: 8,
        };

        // ── Bar Chart ─────────────────────────────────────────────────
        const barCtx = document.getElementById('barChartJurusan');
        if (barCtx) {
            new Chart(barCtx, {
                type: 'bar',
                data: {
                    labels: statsByJurusan.map(j => j.kode),
                    datasets: [{
                        label: 'Pendaftar',
                        data: statsByJurusan.map(j => j.total_daftar),
                        backgroundColor: statsByJurusan.map((_, i) => CHART_COLORS[i % CHART_COLORS.length]),
                        borderRadius: 4,
                        borderSkipped: false,
                    }, {
                        label: 'Kuota',
                        data: statsByJurusan.map(j => j.kuota),
                        backgroundColor: 'rgba(156,163,175,0.25)',
                        borderColor: '#9ca3af',
                        borderWidth: 1,
                        borderRadius: 4,
                        borderSkipped: false,
                        type: 'bar',
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            labels: {
                                boxWidth: 12,
                                font: {
                                    size: 11
                                }
                            }
                        },
                        tooltip: {
                            ...tooltipDefaults,
                            callbacks: {
                                title(items) {
                                    return statsByJurusan[items[0].dataIndex]?.jurusan || items[0].label;
                                },
                                label(item) {
                                    return ` ${item.dataset.label}: ${item.raw}`;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: '#6b7280',
                                font: {
                                    size: 12
                                }
                            }
                        },
                        y: {
                            grid: {
                                color: '#f3f4f6'
                            },
                            ticks: {
                                color: '#6b7280',
                                font: {
                                    size: 12
                                }
                            },
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        // ── Donut/Pie Chart ────────────────────────────────────────────
        const pieCtx = document.getElementById('pieChartDistribusi');
        if (pieCtx && statsByJurusan.length > 0) {
            const total = statsByJurusan.reduce((s, j) => s + j.total_daftar, 0);

            new Chart(pieCtx, {
                type: 'doughnut',
                data: {
                    labels: statsByJurusan.map(j => j.kode),
                    datasets: [{
                        data: statsByJurusan.map(j => j.total_daftar),
                        backgroundColor: CHART_COLORS.slice(0, statsByJurusan.length),
                        borderWidth: 2,
                        borderColor: '#fff',
                        hoverOffset: 6,
                    }]
                },
                plugins: [{
                    id: 'pieLabels',
                    afterDatasetDraw(chart) {
                        const {
                            ctx,
                            data
                        } = chart;
                        const dataset = data.datasets[0];
                        const meta = chart.getDatasetMeta(0);
                        ctx.save();
                        ctx.font = 'bold 11px sans-serif';
                        ctx.fillStyle = '#fff';
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'middle';
                        meta.data.forEach((arc, i) => {
                            const val = dataset.data[i];
                            const pct = total > 0 ? Math.round(val / total * 100) : 0;
                            if (pct < 5) return;
                            const label = `${data.labels[i]} (${pct}%)`;
                            const {
                                x,
                                y
                            } = arc.tooltipPosition();
                            ctx.fillText(label, x, y);
                        });
                        ctx.restore();
                    }
                }],
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '55%',
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom',
                            labels: {
                                boxWidth: 12,
                                font: {
                                    size: 11
                                }
                            }
                        },
                        tooltip: {
                            ...tooltipDefaults,
                            callbacks: {
                                title(items) {
                                    return statsByJurusan[items[0].dataIndex]?.jurusan || items[0].label;
                                },
                                label(item) {
                                    const pct = total > 0 ? Math.round(item.raw / total * 100) : 0;
                                    return ` ${item.raw} pendaftar (${pct}%)`;
                                }
                            }
                        }
                    }
                }
            });
        } else if (pieCtx) {
            // Tampilkan pesan kosong jika tidak ada data
            const ctx2d = pieCtx.getContext('2d');
            ctx2d.fillStyle = '#9ca3af';
            ctx2d.font = '14px sans-serif';
            ctx2d.textAlign = 'center';
            ctx2d.fillText('Belum ada data untuk periode ini', pieCtx.width / 2, pieCtx.height / 2);
        }
    })();
</script>