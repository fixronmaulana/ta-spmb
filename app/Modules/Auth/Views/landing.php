<?= $this->extend('App\Views\Layouts\public') ?>

<?= $this->section('content') ?>

<?= $this->endSection() ?><?= $this->extend('App\Views\Layouts\public') ?>

<?= $this->section('content') ?>

<!-- ============================================================
     HERO SECTION
     ============================================================ -->
<section class="relative overflow-hidden" style="background: hsl(220,54%,20%);">
    <!-- Background Pattern -->
    <div class="absolute inset-0 opacity-10 hero-pattern"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 md:py-28 relative">
        <div class="max-w-3xl mx-auto text-center">
            <p class="font-semibold mb-4 animate-fade-in" style="color: hsl(43,70%,57%);">
                SMK Al-Munawwir IIBS
            </p>
            <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold font-serif mb-6 animate-slide-up"
                style="color: hsl(45,70%,95%);">
                PENERIMAAN MURID BARU
                <span class="block mt-2" style="color: hsl(43,70%,57%);">
                    <?= esc($periode->tahun_ajaran ?? '2026/2027') ?>
                </span>
            </h1>
            <p class="text-lg md:text-xl mb-8 animate-fade-in" style="color: rgba(255,255,255,0.8); animation-delay:0.2s;">
                International Islamic Boarding School — Banyuwangi
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center animate-slide-up" style="animation-delay:0.3s;">
                <?php if (session()->get('logged_in')): ?>
                    <a href="<?= base_url('dashboard') ?>"
                        class="inline-flex items-center justify-center gap-2 px-8 py-4 font-bold rounded-xl transition-all shadow-lg text-base"
                        style="background: hsl(43,70%,47%); color: hsl(220,54%,15%);"
                        onmouseover="this.style.background='hsl(43,80%,55%)'"
                        onmouseout="this.style.background='hsl(43,70%,47%)'">
                        Ke Dashboard
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                        </svg>
                    </a>
                <?php else: ?>
                    <a href="<?= base_url('auth/register') ?>"
                        class="inline-flex items-center justify-center gap-2 px-8 py-4 font-bold rounded-xl transition-all shadow-lg text-base"
                        style="background: hsl(43,70%,47%); color: hsl(220,54%,15%);"
                        onmouseover="this.style.background='hsl(43,80%,55%)'"
                        onmouseout="this.style.background='hsl(43,70%,47%)'">
                        Daftar Sekarang
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                        </svg>
                    </a>
                <?php endif; ?>
                <a href="<?= base_url('panduan') ?>"
                    class="inline-flex items-center justify-center gap-2 px-8 py-4 font-semibold rounded-xl transition-all text-base border"
                    style="border-color: rgba(255,255,255,0.3); color: hsl(45,70%,95%);"
                    onmouseover="this.style.background='rgba(255,255,255,0.1)'"
                    onmouseout="this.style.background='transparent'">
                    Panduan SPMB
                </a>
            </div>
        </div>
    </div>

    <!-- Wave decoration -->
    <div class="absolute bottom-0 left-0 right-0">
        <svg viewBox="0 0 1440 120" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-auto block">
            <path d="M0 120L60 110C120 100 240 80 360 70C480 60 600 60 720 65C840 70 960 80 1080 85C1200 90 1320 90 1380 90L1440 90V120H1380C1320 120 1200 120 1080 120C960 120 840 120 720 120C600 120 480 120 360 120C240 120 120 120 60 120H0Z" fill="hsl(45,30%,98%)" />
        </svg>
    </div>
</section>

<!-- ============================================================
     INFO CARDS SECTION
     ============================================================ -->
<section class="py-16 md:py-20" style="background: hsl(45,30%,98%);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid md:grid-cols-3 gap-6 lg:gap-8">

            <!-- Jadwal Pendaftaran -->
            <div class="card-elevated p-6 lg:p-8">
                <div class="w-14 h-14 rounded-xl flex items-center justify-center mb-5" style="background: hsl(43,70%,47%,0.1);">
                    <svg class="w-7 h-7" style="color: hsl(43,70%,47%);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2" stroke-width="2" />
                        <line x1="16" y1="2" x2="16" y2="6" stroke-width="2" />
                        <line x1="8" y1="2" x2="8" y2="6" stroke-width="2" />
                        <line x1="3" y1="10" x2="21" y2="10" stroke-width="2" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold font-serif mb-4">Jadwal Pendaftaran</h3>
                <ul class="space-y-3">
                    <?php
                    $waves = [
                        ['wave' => 'Gelombang 1', 'period' => 'Januari - Maret 2026'],
                        ['wave' => 'Gelombang 2', 'period' => 'April - Mei 2026'],
                        ['wave' => 'Gelombang 3', 'period' => 'Juni - Juli 2026'],
                    ];
                    foreach ($waves as $item):
                    ?>
                        <li class="flex justify-between items-center py-2 border-b last:border-0" style="border-color: hsl(220,20%,88%);">
                            <span class="font-medium text-sm"><?= $item['wave'] ?></span>
                            <span class="text-sm" style="color: hsl(220,15%,45%);"><?= $item['period'] ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Persyaratan -->
            <div class="card-elevated p-6 lg:p-8">
                <div class="w-14 h-14 rounded-xl flex items-center justify-center mb-5" style="background: hsl(199,89%,48%,0.1);">
                    <svg class="w-7 h-7" style="color: hsl(199,89%,48%);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold font-serif mb-4">Persyaratan</h3>
                <ul class="space-y-2.5">
                    <?php
                    $requirements = ['Kartu Keluarga (KK)', 'Ijazah/SKHUN SMP', 'Akta Kelahiran', 'Pas Foto 3×4 (warna)', 'NISN'];
                    foreach ($requirements as $req):
                    ?>
                        <li class="flex items-center gap-3 text-sm">
                            <svg class="w-4 h-4 flex-shrink-0" style="color: hsl(142,71%,45%);" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            <span><?= $req ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Jurusan -->
            <div class="card-elevated p-6 lg:p-8">
                <div class="w-14 h-14 rounded-xl flex items-center justify-center mb-5" style="background: hsl(160,60%,40%,0.1);">
                    <svg class="w-7 h-7" style="color: hsl(160,60%,40%);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="M22 10v6M2 10l10-5 10 5-10 5z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M6 12v5c3 3 9 3 12 0v-5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold font-serif mb-4">Jurusan Tersedia</h3>
                <ul class="space-y-2">
                    <?php
                    $majorList = [
                        [
                            'code' => 'TKJ',
                            'name' => 'Teknik Komputer dan Jaringan',
                            'color' => 'hsl(199,89%,48%)',
                            'bg'    => 'hsl(199,89%,48%,0.1)',
                            'svg'   => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>',
                        ],
                        [
                            'code' => 'AK',
                            'name' => 'Akuntansi dan Keuangan Lembaga',
                            'color' => 'hsl(142,71%,40%)',
                            'bg'    => 'hsl(142,71%,40%,0.1)',
                            'svg'   => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>',
                        ],
                        [
                            'code' => 'DKV',
                            'name' => 'Desain Komunikasi Visual',
                            'color' => 'hsl(280,65%,55%)',
                            'bg'    => 'hsl(280,65%,55%,0.1)',
                            'svg'   => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>',
                        ],
                        [
                            'code' => 'ATU',
                            'name' => 'Agribisnis Ternak Unggas',
                            'color' => 'hsl(25,80%,50%)',
                            'bg'    => 'hsl(25,80%,50%,0.1)',
                            'svg'   => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
                        ],
                        [
                            'code' => 'DPIB',
                            'name' => 'Desain Pemodelan dan Informasi Bangunan',
                            'color' => 'hsl(43,70%,47%)',
                            'bg'    => 'hsl(43,70%,47%,0.1)',
                            'svg'   => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>',
                        ],
                    ];
                    // Gunakan data dari DB jika tersedia, fallback ke static
                    // Untuk data dari DB, gunakan icon default (komputer/sekolah)
                    $defaultSvg   = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>';
                    $defaultColor = 'hsl(220,54%,35%)';
                    $defaultBg    = 'hsl(220,54%,35%,0.1)';

                    $displayMajors = !empty($jurusans) ? array_map(fn($j) => [
                        'code'  => $j->kode_jurusan ?? $j->kode ?? '',
                        'name'  => $j->nama_jurusan ?? $j->nama ?? '',
                        'svg'   => $defaultSvg,
                        'color' => $defaultColor,
                        'bg'    => $defaultBg,
                    ], $jurusans) : $majorList;

                    foreach ($displayMajors as $major):
                    ?>
                        <li class="flex items-center gap-3 text-sm py-1.5">
                            <span class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0"
                                  style="background: <?= $major['bg'] ?>;">
                                <svg class="w-4 h-4" style="color: <?= $major['color'] ?>;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <?= $major['svg'] ?>
                                </svg>
                            </span>
                            <div>
                                <span class="font-semibold" style="color: hsl(220,54%,20%);"><?= esc($major['code']) ?></span>
                                <span class="ml-2 hidden sm:inline" style="color: hsl(220,15%,45%);">- <?= esc($major['name']) ?></span>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

        </div>
    </div>
</section>

<!-- ============================================================
     ALUR PENDAFTARAN SECTION
     ============================================================ -->
<section class="py-16 md:py-20" style="background: hsl(220,20%,95%);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold font-serif mb-4">Alur Pendaftaran SPMB</h2>
            <p style="color: hsl(220,15%,45%);" class="max-w-2xl mx-auto">
                Ikuti langkah-langkah berikut untuk menyelesaikan pendaftaran Anda
            </p>
        </div>

        <div class="max-w-4xl mx-auto">
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                <?php
                $steps = [
                    ['step' => 1, 'title' => 'Registrasi Akun',      'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>'],
                    ['step' => 2, 'title' => 'Isi Formulir Online',   'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>'],
                    ['step' => 3, 'title' => 'Upload Dokumen',        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>'],
                    ['step' => 4, 'title' => 'Verifikasi Admin',      'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>'],
                    ['step' => 5, 'title' => 'Pengumuman Hasil',      'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>'],
                    ['step' => 6, 'title' => 'Daftar Ulang',          'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>'],
                ];
                foreach ($steps as $i => $item):
                ?>
                    <div class="relative group">
                        <div class="card-elevated p-4 text-center hover:border-blue-200 transition-colors">
                            <div class="w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform"
                                style="background: hsl(220,54%,20%); color: hsl(45,70%,95%);">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <?= $item['icon'] ?>
                                </svg>
                            </div>
                            <p class="text-xs font-bold mb-1" style="color: hsl(220,54%,20%);">Step <?= $item['step'] ?></p>
                            <p class="text-xs leading-tight" style="color: hsl(220,15%,45%);"><?= $item['title'] ?></p>
                        </div>
                        <?php if ($i < count($steps) - 1): ?>
                            <div class="hidden lg:flex absolute top-1/2 -right-2 transform -translate-y-1/2 z-10">
                                <svg class="w-4 h-4" style="color: hsl(220,15%,65%);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                                </svg>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<!-- ============================================================
     CTA SECTION
     ============================================================ -->
<section class="py-16 md:py-20" style="background: hsl(45,30%,98%);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl mx-auto text-center rounded-2xl p-8 md:p-12"
            style="background: linear-gradient(135deg, hsl(220,54%,20%) 0%, hsl(220,54%,30%) 100%);">
            <svg class="w-12 h-12 mx-auto mb-4" style="color: hsl(43,70%,57%);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            <h2 class="text-2xl md:text-3xl font-bold font-serif mb-4" style="color: hsl(45,70%,95%);">
                Bergabunglah Bersama Kami
            </h2>
            <p class="mb-8" style="color: rgba(255,255,255,0.8);">
                Jadilah bagian dari SMK Al-Munawwir IIBS dan raih masa depan cerahmu!
            </p>
            <?php if (session()->get('logged_in')): ?>
                <a href="<?= base_url('dashboard') ?>"
                    class="inline-flex items-center gap-2 px-8 py-4 font-bold rounded-xl transition-all shadow-lg"
                    style="background: hsl(43,70%,47%); color: hsl(220,54%,15%);"
                    onmouseover="this.style.background='hsl(43,80%,55%)'"
                    onmouseout="this.style.background='hsl(43,70%,47%)'">
                    Lanjutkan Pendaftaran
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                    </svg>
                </a>
            <?php else: ?>
                <a href="<?= base_url('auth/register') ?>"
                    class="inline-flex items-center gap-2 px-8 py-4 font-bold rounded-xl transition-all shadow-lg"
                    style="background: hsl(43,70%,47%); color: hsl(220,54%,15%);"
                    onmouseover="this.style.background='hsl(43,80%,55%)'"
                    onmouseout="this.style.background='hsl(43,70%,47%)'">
                    Mulai Pendaftaran
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                    </svg>
                </a>
            <?php endif; ?>
        </div>
    </div>
</section>

<?= $this->endSection() ?>