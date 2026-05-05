<?= $this->extend('App\Views\Layouts\public') ?>

<?= $this->section('content') ?>

<!-- Hero -->
<section class="py-16 md:py-20" style="background: hsl(220,54%,20%); color: hsl(45,70%,95%);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <p class="font-semibold mb-2" style="color: hsl(43,70%,57%);">Profil Sekolah</p>
        <h1 class="text-3xl md:text-5xl font-bold font-serif mb-4">SMK Al-Munawwir IIBS</h1>
        <p class="max-w-2xl mx-auto" style="color: rgba(255,255,255,0.8);">
            International Islamic Boarding School — Banyuwangi, Jawa Timur
        </p>
    </div>
</section>

<!-- Tentang Kami -->
<section class="py-14 md:py-20" style="background: hsl(45,30%,98%);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl mx-auto text-center mb-12">
            <svg class="w-10 h-10 mx-auto mb-4" style="color: hsl(43,70%,47%);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path d="M22 10v6M2 10l10-5 10 5-10 5z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                <path d="M6 12v5c3 3 9 3 12 0v-5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            <h2 class="text-2xl md:text-3xl font-bold font-serif mb-4">Tentang Kami</h2>
            <p class="leading-relaxed" style="color: hsl(220,15%,45%);">
                SMK Al-Munawwir IIBS adalah lembaga pendidikan kejuruan yang memadukan kurikulum
                nasional dengan pendidikan pesantren modern. Berlokasi di Banyuwangi, Jawa Timur,
                sekolah ini berkomitmen mencetak generasi yang unggul dalam ilmu pengetahuan, teknologi,
                dan akhlakul karimah.
            </p>
        </div>

        <!-- Visi & Misi -->
        <div class="grid md:grid-cols-2 gap-6 max-w-4xl mx-auto">
            <!-- Visi -->
            <div class="card-elevated p-6 lg:p-8" style="border-color: hsl(43,70%,47%,0.2);">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center" style="background: hsl(43,70%,47%,0.1);">
                        <svg class="w-6 h-6" style="color: hsl(43,70%,47%);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold font-serif">Visi</h3>
                </div>
                <ul class="space-y-3">
                    <?php
                    $visi = [
                        'Menjadi lembaga pendidikan kejuruan bertaraf internasional',
                        'Berlandaskan nilai-nilai Islam dan akhlakul karimah',
                        'Menghasilkan lulusan yang kompeten, berdaya saing global',
                    ];
                    foreach ($visi as $i => $v):
                    ?>
                        <li class="flex items-start gap-3 text-sm" style="color: hsl(220,15%,45%);">
                            <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0 mt-0.5"
                                style="background: hsl(43,70%,47%,0.1); color: hsl(43,70%,40%);">
                                <?= $i + 1 ?>
                            </span>
                            <?= $v ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Misi -->
            <div class="card-elevated p-6 lg:p-8" style="border-color: hsl(220,54%,20%,0.2);">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center" style="background: hsl(220,54%,20%,0.1);">
                        <svg class="w-6 h-6" style="color: hsl(220,54%,20%);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold font-serif">Misi</h3>
                </div>
                <ul class="space-y-3">
                    <?php
                    $misi = [
                        'Menyelenggarakan pendidikan kejuruan berkualitas berbasis teknologi',
                        'Menanamkan nilai-nilai keislaman dalam setiap aspek pembelajaran',
                        'Mengembangkan keterampilan dan kompetensi sesuai kebutuhan industri',
                        'Membangun kerja sama dengan dunia usaha dan industri nasional maupun internasional',
                        'Membentuk karakter peserta didik yang berakhlak mulia dan berwawasan global',
                    ];
                    foreach ($misi as $i => $m):
                    ?>
                        <li class="flex items-start gap-3 text-sm" style="color: hsl(220,15%,45%);">
                            <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0 mt-0.5"
                                style="background: hsl(220,54%,20%,0.1); color: hsl(220,54%,30%);">
                                <?= $i + 1 ?>
                            </span>
                            <?= $m ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</section>

<!-- Fasilitas -->
<section class="py-14 md:py-20" style="background: hsl(220,20%,95%);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-10">
            <h2 class="text-2xl md:text-3xl font-bold font-serif mb-3">Fasilitas Sekolah</h2>
            <p style="color: hsl(220,15%,45%);">Sarana dan prasarana yang mendukung proses pembelajaran</p>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 max-w-3xl mx-auto">
            <?php
            $facilities = [
                ['name' => 'Laboratorium Komputer', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>'],
                ['name' => 'Perpustakaan Digital',  'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>'],
                ['name' => 'Masjid & Asrama',       'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>'],
                ['name' => 'Workshop Praktik',      'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>'],
                ['name' => 'Lapangan Olahraga',     'icon' => '<circle cx="12" cy="12" r="10" stroke-width="2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3"/>'],
                ['name' => 'Aula Serbaguna',         'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>'],
            ];
            foreach ($facilities as $f):
            ?>
                <div class="card-elevated p-5 text-center">
                    <svg class="w-8 h-8 mx-auto mb-3" style="color: hsl(220,54%,20%);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <?= $f['icon'] ?>
                    </svg>
                    <p class="text-sm font-medium"><?= $f['name'] ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Prestasi -->
<section class="py-14 md:py-20" style="background: hsl(45,30%,98%);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl mx-auto text-center mb-10">
            <svg class="w-10 h-10 mx-auto mb-4" style="color: hsl(43,70%,47%);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
            </svg>
            <h2 class="text-2xl md:text-3xl font-bold font-serif mb-3">Prestasi & Akreditasi</h2>
        </div>
        <div class="max-w-2xl mx-auto space-y-3">
            <?php
            $achievements = [
                'Juara 1 LKS Tingkat Provinsi - TKJ 2025',
                'Juara 2 Kompetisi Desain Nasional 2025',
                'Akreditasi A dari BAN-SMK',
                'Sertifikasi ISO 9001:2015',
                'Mitra resmi Huawei ICT Academy',
            ];
            foreach ($achievements as $a):
            ?>
                <div class="flex items-center gap-4 card-elevated p-4">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0" style="background: hsl(43,70%,47%,0.1);">
                        <svg class="w-5 h-5" style="color: hsl(43,70%,47%);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                        </svg>
                    </div>
                    <p class="text-sm font-medium"><?= $a ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Lokasi -->
<section class="py-14 md:py-20" style="background: hsl(220,20%,95%);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <svg class="w-10 h-10 mx-auto mb-4" style="color: hsl(220,54%,20%);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg>
        <h2 class="text-2xl md:text-3xl font-bold font-serif mb-3">Lokasi Sekolah</h2>
        <p class="mb-6" style="color: hsl(220,15%,45%);">
            Jl. Kedungliwung No.35, Kemiri, Singojuruh, Kabupaten Banyuwangi, Jawa Timur
        </p>
        <div class="max-w-3xl mx-auto rounded-xl overflow-hidden border h-64 md:h-80 flex items-center justify-center" style="background: hsl(220,20%,92%); border-color: hsl(220,20%,88%);">
            <div class="text-center" style="color: hsl(220,15%,65%);">
                <svg class="w-12 h-12 mx-auto mb-2 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <p class="text-sm">Peta lokasi akan ditampilkan di sini</p>
            </div>
        </div>
    </div>
</section>

<?= $this->endSection() ?>