<?= $this->extend('App\Views\Layouts\public') ?>

<?= $this->section('content') ?>

<!-- Hero -->
<section class="py-16 md:py-20" style="background: hsl(220,54%,20%); color: hsl(45,70%,95%);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <p class="font-semibold mb-2" style="color: hsl(43,70%,57%);">Hubungi Kami</p>
        <h1 class="text-3xl md:text-5xl font-bold font-serif mb-4">Kontak</h1>
        <p class="max-w-2xl mx-auto" style="color: rgba(255,255,255,0.8);">
            Punya pertanyaan tentang SPMB? Jangan ragu untuk menghubungi kami
        </p>
    </div>
</section>

<!-- Content -->
<section class="py-14 md:py-20" style="background: hsl(45,30%,98%);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-2 gap-8 max-w-5xl mx-auto">

            <!-- Contact Info -->
            <div class="space-y-6">
                <h2 class="text-2xl font-bold font-serif mb-4">Informasi Kontak</h2>
                <div class="space-y-4">
                    <?php
                    $contacts = [
                        [
                            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>',
                            'title' => 'Alamat',
                            'detail' => 'Jl. Kedungliwung No.35, Kemiri, Singojuruh, Kabupaten Banyuwangi, Jawa Timur 68465'
                        ],
                        [
                            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>',
                            'title' => 'Telepon',
                            'detail' => '(0333) xxx-xxxx'
                        ],
                        [
                            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>',
                            'title' => 'Email',
                            'detail' => 'spmb@smk-almunawwir.sch.id'
                        ],
                        [
                            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>',
                            'title' => 'Jam Operasional',
                            'detail' => 'Senin - Sabtu: 08.00 - 15.00 WIB'
                        ],
                    ];
                    foreach ($contacts as $info):
                    ?>
                        <div class="flex items-start gap-4">
                            <div class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0" style="background: hsl(220,54%,20%,0.1);">
                                <svg class="w-5 h-5" style="color: hsl(220,54%,20%);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <?= $info['icon'] ?>
                                </svg>
                            </div>
                            <div>
                                <p class="font-semibold text-sm"><?= $info['title'] ?></p>
                                <p class="text-sm" style="color: hsl(220,15%,45%);"><?= $info['detail'] ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Social -->
                <div class="pt-4">
                    <h3 class="font-semibold text-sm mb-3">Media Sosial</h3>
                    <div class="flex gap-3">
                        <a href="#" class="w-10 h-10 rounded-full flex items-center justify-center transition-colors"
                            style="background: hsl(220,20%,92%);"
                            onmouseover="this.style.background='hsl(220,54%,20%)'; this.style.color='white';"
                            onmouseout="this.style.background='hsl(220,20%,92%)'; this.style.color='inherit';">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z" />
                            </svg>
                        </a>
                        <a href="#" class="w-10 h-10 rounded-full flex items-center justify-center transition-colors"
                            style="background: hsl(220,20%,92%);"
                            onmouseover="this.style.background='hsl(220,54%,20%)'; this.style.color='white';"
                            onmouseout="this.style.background='hsl(220,20%,92%)'; this.style.color='inherit';">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <rect x="2" y="2" width="20" height="20" rx="5" ry="5" stroke-width="2" />
                                <path d="M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37z" stroke-width="2" />
                                <line x1="17.5" y1="6.5" x2="17.51" y2="6.5" stroke-width="2" />
                            </svg>
                        </a>
                        <a href="#" class="w-10 h-10 rounded-full flex items-center justify-center transition-colors"
                            style="background: hsl(220,20%,92%);"
                            onmouseover="this.style.background='hsl(220,54%,20%)'; this.style.color='white';"
                            onmouseout="this.style.background='hsl(220,20%,92%)'; this.style.color='inherit';">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M22.54 6.42a2.78 2.78 0 00-1.95-1.96C18.88 4 12 4 12 4s-6.88 0-8.59.46a2.78 2.78 0 00-1.95 1.96A29 29 0 001 12a29 29 0 00.46 5.58A2.78 2.78 0 003.41 19.6C5.12 20 12 20 12 20s6.88 0 8.59-.46a2.78 2.78 0 001.95-1.95A29 29 0 0023 12a29 29 0 00-.46-5.58z" />
                                <polygon fill="white" points="9.75 15.02 15.5 12 9.75 8.98 9.75 15.02" />
                            </svg>
                        </a>
                        <a href="#" class="w-10 h-10 rounded-full flex items-center justify-center transition-colors"
                            style="background: hsl(220,20%,92%);"
                            onmouseover="this.style.background='hsl(142,71%,45%)'; this.style.color='white';"
                            onmouseout="this.style.background='hsl(220,20%,92%)'; this.style.color='inherit';">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z" />
                                <path d="M11.527 2.014C5.945 2.014 1.457 6.502 1.457 12.084c0 1.868.49 3.622 1.345 5.14L1 22l4.93-1.757a10.017 10.017 0 004.597 1.115c5.582 0 10.07-4.488 10.07-10.07 0-5.582-4.488-10.27-10.07-10.274z" />
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- Map -->
                <div class="rounded-xl overflow-hidden border h-48" style="border-color: hsl(220,20%,88%);">
                    <iframe
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3295.2419846812086!2d114.2046592741292!3d-8.29071138337786!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2dd15300689df32d%3A0xf4d28a0ff76d227c!2sSMK%20AL%20MUNAWWIR%20IIBS!5e1!3m2!1sid!2sid!4v1772965150579!5m2!1sid!2sid"
                        class="w-full h-full"
                        style="border:0;"
                        allowfullscreen
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                        title="Lokasi SMK Al-Munawwir IIBS">
                    </iframe>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="card-elevated p-6 lg:p-8">
                <h2 class="text-2xl font-bold font-serif mb-6">Kirim Pesan</h2>

                <?php if (session()->getFlashdata('kontak_success')): ?>
                    <div class="flex items-center gap-3 p-4 rounded-lg text-sm mb-4" style="background: hsl(142,71%,45%,0.1); border: 1px solid hsl(142,71%,45%,0.3); color: hsl(142,71%,30%);">
                        <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        Pesan Anda telah terkirim. Kami akan segera membalas!
                    </div>
                <?php endif; ?>

                <form action="<?= base_url('kontak/kirim') ?>" method="POST" class="space-y-4">
                    <?= csrf_field() ?>
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="block text-sm font-medium">Nama Lengkap</label>
                            <input type="text" name="name" placeholder="Nama Anda" required class="form-input">
                        </div>
                        <div class="space-y-1.5">
                            <label class="block text-sm font-medium">Email</label>
                            <input type="email" name="email" placeholder="email@contoh.com" required class="form-input">
                        </div>
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-sm font-medium">Subjek</label>
                        <input type="text" name="subject" placeholder="Perihal pesan Anda" required class="form-input">
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-sm font-medium">Pesan</label>
                        <textarea name="message" placeholder="Tulis pesan Anda di sini..." rows="5" required class="form-input" style="resize: vertical;"></textarea>
                    </div>
                    <button type="submit"
                        class="w-full flex items-center justify-center gap-2 py-3 px-6 font-semibold text-white rounded-xl transition-all"
                        style="background: hsl(220,54%,20%);"
                        onmouseover="this.style.background='hsl(220,54%,30%)'"
                        onmouseout="this.style.background='hsl(220,54%,20%)'">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <line x1="22" y1="2" x2="11" y2="13" stroke-width="2" />
                            <polygon points="22 2 15 22 11 13 2 9 22 2" stroke-width="2" />
                        </svg>
                        Kirim Pesan
                    </button>
                </form>
            </div>

        </div>
    </div>
</section>

<?= $this->endSection() ?>