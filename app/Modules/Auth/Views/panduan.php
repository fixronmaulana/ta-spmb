<?= $this->extend('App\Views\Layouts\public') ?>

<?= $this->section('content') ?>

<!-- Hero -->
<section class="py-16 md:py-20" style="background: hsl(220,54%,20%); color: hsl(45,70%,95%);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <p class="font-semibold mb-2" style="color: hsl(43,70%,57%);">Panduan Lengkap</p>
        <h1 class="text-3xl md:text-5xl font-bold font-serif mb-4">Panduan SPMB</h1>
        <p class="max-w-2xl mx-auto" style="color: rgba(255,255,255,0.8);">
            Informasi lengkap tentang alur pendaftaran, persyaratan, dan FAQ
        </p>
    </div>
</section>

<!-- Alur Pendaftaran -->
<section class="py-14 md:py-20" style="background: hsl(45,30%,98%);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-10">
            <h2 class="text-2xl md:text-3xl font-bold font-serif mb-3">Alur Pendaftaran</h2>
            <p style="color: hsl(220,15%,45%);">Ikuti 6 langkah berikut untuk menyelesaikan pendaftaran</p>
        </div>

        <div class="max-w-3xl mx-auto space-y-4">
            <?php
            $steps = [
                [
                    'step' => 1,
                    'title' => 'Registrasi Akun',
                    'desc' => 'Buat akun di portal SPMB dengan mengisi data diri berupa nama lengkap, email, dan password.',
                    'tips' => 'Gunakan email aktif yang bisa diakses untuk verifikasi dan notifikasi.',
                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>',
                ],
                [
                    'step' => 2,
                    'title' => 'Isi Formulir Pendaftaran',
                    'desc' => 'Lengkapi formulir dengan data pribadi, data orang tua/wali, riwayat pendidikan, dan pilihan jurusan.',
                    'tips' => 'Siapkan KK dan ijazah SMP sebagai acuan pengisian data.',
                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>',
                ],
                [
                    'step' => 3,
                    'title' => 'Upload Dokumen',
                    'desc' => 'Unggah dokumen persyaratan dalam format PDF/JPG (maks. 2MB per file).',
                    'tips' => 'Pastikan hasil scan jelas dan terbaca. Gunakan scanner atau aplikasi CamScanner.',
                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>',
                ],
                [
                    'step' => 4,
                    'title' => 'Verifikasi oleh Admin',
                    'desc' => 'Tim admin akan memverifikasi kelengkapan dan keabsahan dokumen yang diupload.',
                    'tips' => 'Pantau status verifikasi di dashboard. Perbaiki dokumen jika diminta revisi.',
                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>',
                ],
                [
                    'step' => 5,
                    'title' => 'Pengumuman Hasil',
                    'desc' => 'Hasil seleksi akan diumumkan melalui dashboard dan notifikasi email.',
                    'tips' => 'Cek dashboard secara berkala pada tanggal pengumuman yang telah ditentukan.',
                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>',
                ],
                [
                    'step' => 6,
                    'title' => 'Daftar Ulang',
                    'desc' => 'Calon siswa yang diterima melakukan daftar ulang dengan membawa berkas asli ke sekolah.',
                    'tips' => 'Bawa semua berkas asli dan fotokopi. Datang sesuai jadwal yang ditentukan.',
                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
                ],
            ];
            foreach ($steps as $item):
            ?>
                <div class="card-elevated overflow-hidden">
                    <div class="p-5 sm:p-6">
                        <div class="flex gap-4">
                            <div class="w-12 h-12 rounded-full flex items-center justify-center flex-shrink-0"
                                style="background: hsl(220,54%,20%); color: hsl(45,70%,95%);">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <?= $item['icon'] ?>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="text-xs font-bold mb-1" style="color: hsl(220,54%,20%);">Langkah <?= $item['step'] ?></p>
                                <h3 class="font-bold font-serif text-lg mb-1"><?= $item['title'] ?></h3>
                                <p class="text-sm mb-2" style="color: hsl(220,15%,45%);"><?= $item['desc'] ?></p>
                                <div class="flex items-start gap-2 rounded-lg p-3" style="background: hsl(43,70%,47%,0.05); border: 1px solid hsl(43,70%,47%,0.1);">
                                    <svg class="w-4 h-4 flex-shrink-0 mt-0.5" style="color: hsl(43,70%,47%);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <circle cx="12" cy="12" r="10" stroke-width="2" />
                                        <line x1="12" y1="8" x2="12" y2="12" stroke-width="2" />
                                        <line x1="12" y1="16" x2="12.01" y2="16" stroke-width="2" />
                                    </svg>
                                    <p class="text-xs" style="color: hsl(220,15%,45%);"><span class="font-semibold" style="color: hsl(43,70%,47%);">Tips:</span> <?= $item['tips'] ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Persyaratan Dokumen -->
<section class="py-14 md:py-20" style="background: hsl(220,20%,95%);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-10">
            <svg class="w-10 h-10 mx-auto mb-4" style="color: hsl(199,89%,48%);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <h2 class="text-2xl md:text-3xl font-bold font-serif mb-3">Persyaratan Dokumen</h2>
        </div>
        <div class="max-w-2xl mx-auto overflow-x-auto">
            <table class="w-full min-w-[400px] text-sm bg-white rounded-xl overflow-hidden shadow-sm border" style="border-color: hsl(220,20%,88%);">
                <thead>
                    <tr style="border-bottom: 1px solid hsl(220,20%,88%); background: hsl(220,20%,97%);">
                        <th class="text-left py-3 px-4 font-semibold">Dokumen</th>
                        <th class="text-left py-3 px-4 font-semibold">Format</th>
                        <th class="text-left py-3 px-4 font-semibold">Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $requirements = [
                        ['doc' => 'Kartu Keluarga (KK)',    'format' => 'PDF/JPG', 'note' => 'Scan berwarna'],
                        ['doc' => 'Ijazah / SKHUN SMP',     'format' => 'PDF/JPG', 'note' => 'Atau surat keterangan lulus'],
                        ['doc' => 'Akta Kelahiran',          'format' => 'PDF/JPG', 'note' => 'Scan berwarna'],
                        ['doc' => 'Pas Foto 3×4',            'format' => 'JPG/PNG', 'note' => 'Latar merah, formal'],
                        ['doc' => 'NISN',                    'format' => '-',       'note' => 'Nomor 10 digit'],
                        ['doc' => 'Rapor SMP (opsional)',    'format' => 'PDF',     'note' => 'Semester 1-5'],
                    ];
                    foreach ($requirements as $r):
                    ?>
                        <tr style="border-bottom: 1px solid hsl(220,20%,92%);">
                            <td class="py-3 px-4 font-medium"><?= $r['doc'] ?></td>
                            <td class="py-3 px-4" style="color: hsl(220,15%,45%);"><?= $r['format'] ?></td>
                            <td class="py-3 px-4" style="color: hsl(220,15%,45%);"><?= $r['note'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- Jadwal -->
<section class="py-14 md:py-20" style="background: hsl(45,30%,98%);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <svg class="w-10 h-10 mx-auto mb-4" style="color: hsl(43,70%,47%);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <rect x="3" y="4" width="18" height="18" rx="2" ry="2" stroke-width="2" />
            <line x1="16" y1="2" x2="16" y2="6" stroke-width="2" />
            <line x1="8" y1="2" x2="8" y2="6" stroke-width="2" />
            <line x1="3" y1="10" x2="21" y2="10" stroke-width="2" />
        </svg>
        <h2 class="text-2xl md:text-3xl font-bold font-serif mb-6">Jadwal Pendaftaran</h2>
        <div class="grid sm:grid-cols-3 gap-4 max-w-2xl mx-auto">
            <?php
            $waves = [
                ['wave' => 'Gelombang 1', 'period' => 'Januari - Maret 2026'],
                ['wave' => 'Gelombang 2', 'period' => 'April - Mei 2026'],
                ['wave' => 'Gelombang 3', 'period' => 'Juni - Juli 2026'],
            ];
            foreach ($waves as $w):
            ?>
                <div class="card-elevated p-5 text-center">
                    <p class="font-bold font-serif" style="color: hsl(220,54%,20%);"><?= $w['wave'] ?></p>
                    <p class="text-sm mt-1" style="color: hsl(220,15%,45%);"><?= $w['period'] ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- FAQ -->
<section class="py-14 md:py-20" style="background: hsl(220,20%,95%);" x-data="{ open: null }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-10">
            <svg class="w-10 h-10 mx-auto mb-4" style="color: hsl(220,54%,20%);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <h2 class="text-2xl md:text-3xl font-bold font-serif mb-3">Pertanyaan Umum (FAQ)</h2>
        </div>
        <div class="max-w-2xl mx-auto space-y-2">
            <?php
            $faqs = [
                [
                    'q' => 'Apakah pendaftaran online gratis?',
                    'a' => 'Ya, pendaftaran online melalui portal SPMB tidak dikenakan biaya apapun.'
                ],
                [
                    'q' => 'Bisa memilih lebih dari satu jurusan?',
                    'a' => 'Anda dapat memilih 1 jurusan utama dan 1 jurusan alternatif pada formulir pendaftaran.'
                ],
                [
                    'q' => 'Bagaimana jika dokumen ditolak saat verifikasi?',
                    'a' => 'Anda akan mendapat notifikasi beserta alasan penolakan. Upload ulang dokumen yang diperbaiki melalui dashboard.'
                ],
                [
                    'q' => 'Kapan pengumuman hasil seleksi?',
                    'a' => 'Pengumuman mengikuti jadwal per gelombang. Cek halaman utama untuk jadwal lengkap.'
                ],
                [
                    'q' => 'Apakah tersedia asrama/boarding?',
                    'a' => 'Ya, SMK Al-Munawwir IIBS menyediakan fasilitas asrama (boarding school) bagi seluruh siswa.'
                ],
                [
                    'q' => 'Bagaimana cara menghubungi panitia?',
                    'a' => 'Hubungi melalui halaman Kontak atau email ke spmb@smk-almunawwir.sch.id.'
                ],
            ];
            foreach ($faqs as $i => $faq):
            ?>
                <div class="bg-white rounded-lg border px-4" style="border-color: hsl(220,20%,88%);">
                    <button class="w-full flex justify-between items-center py-4 text-left text-sm font-medium gap-4"
                        @click="open === <?= $i ?> ? open = null : open = <?= $i ?>">
                        <span><?= esc($faq['q']) ?></span>
                        <svg class="w-4 h-4 flex-shrink-0 transition-transform" :class="open === <?= $i ?> ? 'rotate-180' : ''"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open === <?= $i ?>" x-cloak x-collapse class="pb-4 text-sm" style="color: hsl(220,15%,45%);">
                        <?= esc($faq['a']) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="py-14 md:py-20" style="background: hsl(45,30%,98%);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
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
</section>

<?= $this->endSection() ?>