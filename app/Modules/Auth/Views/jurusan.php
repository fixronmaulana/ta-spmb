<?= $this->extend('App\Views\Layouts\public') ?>

<?= $this->section('content') ?>

<!-- Hero -->
<section class="py-16 md:py-20" style="background: hsl(220,54%,20%); color: hsl(45,70%,95%);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <p class="font-semibold mb-2" style="color: hsl(43,70%,57%);">Program Keahlian</p>
        <h1 class="text-3xl md:text-5xl font-bold font-serif mb-4">Jurusan Tersedia</h1>
        <p class="max-w-2xl mx-auto" style="color: rgba(255,255,255,0.8);">
            Pilih jurusan yang sesuai dengan minat dan bakatmu untuk masa depan yang cerah
        </p>
    </div>
</section>

<!-- Jurusan List -->
<section class="py-14 md:py-20" style="background: hsl(45,30%,98%);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="space-y-8 max-w-4xl mx-auto">
            <?php
            $majorDefs = [
                [
                    'code' => 'TKJ',
                    'name' => 'Teknik Komputer dan Jaringan',
                    'color' => 'hsl(199,89%,48%)',
                    'bg' => 'hsl(199,89%,48%,0.1)',
                    'border' => 'hsl(199,89%,48%,0.2)',
                    'quota' => 72,
                    'desc' => 'Mempelajari perakitan komputer, instalasi jaringan, administrasi server, dan keamanan jaringan. Lulusan siap bekerja sebagai Network Engineer, IT Support, atau System Administrator.',
                    'skills'  => ['Jaringan Komputer', 'Linux Server', 'Cisco Networking', 'Cloud Computing', 'Cyber Security'],
                    'careers' => ['Network Engineer', 'IT Support', 'System Administrator', 'Cloud Engineer'],
                    'icon' => '<rect x="2" y="3" width="20" height="14" rx="2" ry="2" stroke-width="2"/><line x1="8" y1="21" x2="16" y2="21" stroke-width="2"/><line x1="12" y1="17" x2="12" y2="21" stroke-width="2"/>',
                ],
                [
                    'code' => 'AK',
                    'name' => 'Akuntansi dan Keuangan Lembaga',
                    'color' => 'hsl(43,70%,47%)',
                    'bg' => 'hsl(43,70%,47%,0.1)',
                    'border' => 'hsl(43,70%,47%,0.2)',
                    'quota' => 72,
                    'desc' => 'Mempelajari pembukuan, laporan keuangan, perpajakan, dan manajemen keuangan lembaga. Lulusan siap bekerja di sektor perbankan, akuntan, atau berwirausaha.',
                    'skills'  => ['Akuntansi Dasar', 'Perpajakan', 'Spreadsheet', 'Aplikasi Akuntansi', 'Keuangan Lembaga'],
                    'careers' => ['Staff Accounting', 'Teller Bank', 'Tax Consultant', 'Auditor'],
                    'icon' => '<line x1="18" y1="20" x2="18" y2="10" stroke-width="2"/><line x1="12" y1="20" x2="12" y2="4" stroke-width="2"/><line x1="6" y1="20" x2="6" y2="14" stroke-width="2"/>',
                ],
                [
                    'code' => 'DKV',
                    'name' => 'Desain Komunikasi Visual',
                    'color' => 'hsl(160,60%,40%)',
                    'bg' => 'hsl(160,60%,40%,0.1)',
                    'border' => 'hsl(160,60%,40%,0.2)',
                    'quota' => 36,
                    'desc' => 'Mempelajari desain grafis, fotografi, videografi, animasi, dan multimedia. Lulusan siap berkarir di industri kreatif sebagai desainer, content creator, atau animator.',
                    'skills'  => ['Adobe Creative Suite', 'UI/UX Design', 'Fotografi', 'Videografi', 'Motion Graphics'],
                    'careers' => ['Graphic Designer', 'UI/UX Designer', 'Content Creator', 'Animator'],
                    'icon' => '<circle cx="12" cy="12" r="3" stroke-width="2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/>',
                ],
                [
                    'code' => 'ATU',
                    'name' => 'Agribisnis Ternak Unggas',
                    'color' => 'hsl(38,92%,50%)',
                    'bg' => 'hsl(38,92%,50%,0.1)',
                    'border' => 'hsl(38,92%,50%,0.2)',
                    'quota' => 36,
                    'desc' => 'Mempelajari budidaya ternak unggas, manajemen peternakan, pengolahan hasil ternak, dan pemasaran produk peternakan. Lulusan siap menjadi peternak mandiri atau bekerja di industri peternakan.',
                    'skills'  => ['Budidaya Unggas', 'Manajemen Peternakan', 'Pakan Ternak', 'Kesehatan Hewan', 'Pemasaran Produk'],
                    'careers' => ['Peternak Mandiri', 'Supervisor Farm', 'Quality Control', 'Agripreneur'],
                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3C7 3 4 7 4 10c0 2 1 3.5 2.5 4.5L6 21h12l-.5-6.5C19 13.5 20 12 20 10c0-3-3-7-8-7z"/>',
                ],
                [
                    'code' => 'DPIB',
                    'name' => 'Desain Pemodelan dan Informasi Bangunan',
                    'color' => 'hsl(220,54%,30%)',
                    'bg' => 'hsl(220,54%,20%,0.1)',
                    'border' => 'hsl(220,54%,20%,0.2)',
                    'quota' => 36,
                    'desc' => 'Mempelajari gambar teknik, desain arsitektur, pemodelan 3D, dan Building Information Modeling (BIM). Lulusan siap bekerja sebagai drafter, estimator, atau teknisi bangunan.',
                    'skills'  => ['AutoCAD', 'SketchUp', 'Revit BIM', 'Gambar Teknik', 'RAB Bangunan'],
                    'careers' => ['Drafter', 'Estimator', 'Teknisi Bangunan', 'BIM Modeler'],
                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>',
                ],
            ];

            // Merge dengan data DB jika ada
            $displayMajors = $majorDefs;
            if (!empty($jurusans)) {
                foreach ($jurusans as $j) {
                    foreach ($displayMajors as &$def) {
                        if ($def['code'] === ($j->kode_jurusan ?? $j->kode ?? '')) {
                            $def['name']  = $j->nama_jurusan ?? $j->nama ?? $def['name'];
                            $def['quota'] = $j->kuota ?? $def['quota'];
                        }
                    }
                }
            }

            foreach ($displayMajors as $major):
            ?>
                <div class="card-elevated overflow-hidden" style="border-color: <?= $major['border'] ?>;">
                    <div class="p-6">
                        <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                            <div class="w-14 h-14 rounded-xl flex items-center justify-center flex-shrink-0"
                                style="background: <?= $major['bg'] ?>; color: <?= $major['color'] ?>;">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <?= $major['icon'] ?>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <span class="badge badge-secondary mb-1"><?= esc($major['code']) ?></span>
                                <h3 class="text-lg md:text-xl font-bold font-serif"><?= esc($major['name']) ?></h3>
                            </div>
                            <div class="text-right hidden sm:block">
                                <p class="text-xs" style="color: hsl(220,15%,45%);">Kuota</p>
                                <p class="text-2xl font-bold" style="color: hsl(220,54%,20%);"><?= $major['quota'] ?></p>
                                <p class="text-xs" style="color: hsl(220,15%,45%);">siswa</p>
                            </div>
                        </div>

                        <p class="text-sm leading-relaxed mt-4" style="color: hsl(220,15%,45%);"><?= esc($major['desc']) ?></p>

                        <div class="grid sm:grid-cols-2 gap-4 mt-4">
                            <div>
                                <div class="flex items-center gap-2 mb-2">
                                    <svg class="w-4 h-4" style="color: hsl(220,54%,20%);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                    </svg>
                                    <p class="text-sm font-semibold">Kompetensi</p>
                                </div>
                                <div class="flex flex-wrap gap-1.5">
                                    <?php foreach ($major['skills'] as $skill): ?>
                                        <span class="badge badge-outline"><?= esc($skill) ?></span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div>
                                <div class="flex items-center gap-2 mb-2">
                                    <svg class="w-4 h-4" style="color: hsl(43,70%,47%);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                    <p class="text-sm font-semibold">Prospek Karir</p>
                                </div>
                                <div class="flex flex-wrap gap-1.5">
                                    <?php foreach ($major['careers'] as $career): ?>
                                        <span class="badge badge-outline" style="background: hsl(43,70%,47%,0.05);"><?= esc($career) ?></span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <div class="sm:hidden flex items-center gap-2 text-sm mt-4">
                            <svg class="w-4 h-4" style="color: hsl(220,15%,45%);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span style="color: hsl(220,15%,45%);">Kuota:</span>
                            <span class="font-bold" style="color: hsl(220,54%,20%);"><?= $major['quota'] ?> siswa</span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- CTA -->
        <div class="text-center mt-12">
            <a href="<?= base_url('auth/register') ?>"
                class="inline-flex items-center gap-2 px-8 py-4 font-bold rounded-xl text-white transition-all"
                style="background: hsl(220,54%,20%);"
                onmouseover="this.style.background='hsl(220,54%,30%)'"
                onmouseout="this.style.background='hsl(220,54%,20%)'">
                Daftar Sekarang
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                </svg>
            </a>
        </div>
    </div>
</section>

<?= $this->endSection() ?>