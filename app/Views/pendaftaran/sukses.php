<?php
/**
 * View: pendaftaran/sukses.php
 *
 * Halaman konfirmasi setelah pendaftar berhasil submit formulir (step 4).
 * Menyediakan satu CTA utama: Gabung Grup WhatsApp.
 * Jika pengguna kembali ke halaman ini (misalnya setelah buka WA), akan
 * otomatis diarahkan ke /dashboard/status via tombol sekunder.
 *
 * Variabel dari PendaftaranController::sukses():
 *   $pendaftaran   — object
 *   $dataDiri      — object|null
 *   $waGrupLink    — string  (link grup WA dari env/config)
 *   $waKontakLink  — string  (link WA personal panitia)
 *   $waKontakNo    — string  (nomor WA panitia, format tampilan)
 */

$noPendaftaran = $pendaftaran->no_pendaftaran ?? '—';
$namaSiswa     = $dataDiri->nama_lengkap ?? ($pendaftaran->nama ?? '—');
$waGrupLink    = $waGrupLink ?? '#';
$waKontakLink  = $waKontakLink ?? '#';
$waKontakNo    = $waKontakNo ?? '0812-xxxx-xxxx';
?>

<div class="max-w-2xl mx-auto" x-data="suksesPage()">

    <!-- ══ KONFETTI ANIMASI (CSS only) ══════════════════════════════════ -->
    <style>
        @keyframes fadeInUp {
            from { opacity:0; transform:translateY(24px); }
            to   { opacity:1; transform:translateY(0); }
        }
        @keyframes popIn {
            0%   { transform:scale(0.7); opacity:0; }
            70%  { transform:scale(1.08); opacity:1; }
            100% { transform:scale(1); }
        }
        @keyframes pulse-ring {
            0%   { transform:scale(1); opacity:.6; }
            100% { transform:scale(1.5); opacity:0; }
        }
        @keyframes wa-bounce {
            0%,100% { transform:translateY(0) scale(1); }
            50%     { transform:translateY(-6px) scale(1.04); }
        }
        .animate-fade-up   { animation:fadeInUp .5s ease both; }
        .animate-pop-in    { animation:popIn .45s cubic-bezier(.34,1.56,.64,1) both; }
        .animate-delay-1   { animation-delay:.1s; }
        .animate-delay-2   { animation-delay:.22s; }
        .animate-delay-3   { animation-delay:.35s; }
        .animate-delay-4   { animation-delay:.48s; }
        .animate-delay-5   { animation-delay:.62s; }
        .pulse-ring {
            position:absolute;
            inset:-10px;
            border-radius:9999px;
            border:2px solid hsl(142,71%,45%,.35);
            animation:pulse-ring 1.8s ease-out infinite;
        }
        .wa-btn:hover { animation:wa-bounce .5s ease infinite; }
    </style>

    <!-- ══ CARD UTAMA ═══════════════════════════════════════════════════ -->
    <div class="bg-white rounded-3xl overflow-hidden animate-fade-up"
         style="border:1px solid hsl(142,71%,45%,.25);
                box-shadow:0 20px 60px -10px hsl(142 71% 45%/0.15),0 4px 6px -1px hsl(220 54% 20%/0.07);">

        <!-- Header hijau gradient -->
        <div class="px-8 py-10 text-center"
             style="background:linear-gradient(135deg,hsl(142,60%,96%) 0%,hsl(162,60%,94%) 100%);">

            <!-- Icon centang dengan animasi -->
            <div class="relative w-24 h-24 mx-auto mb-6 animate-pop-in">
                <div class="pulse-ring"></div>
                <div class="pulse-ring" style="animation-delay:.5s;"></div>
                <div class="w-24 h-24 rounded-full flex items-center justify-center"
                     style="background:linear-gradient(135deg,hsl(142,71%,45%),hsl(162,65%,38%));">
                    <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" stroke-width="2.5"
                         stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <path d="M22 11.08V12a10 10 0 11-5.93-9.14"/>
                        <polyline points="22 4 12 14.01 9 11.01"/>
                    </svg>
                </div>
            </div>

            <h1 class="text-3xl font-bold font-serif mb-2 animate-fade-up animate-delay-1"
                style="color:hsl(142,55%,22%);">
                Formulir Berhasil Dikirim!
            </h1>
            <p class="text-base animate-fade-up animate-delay-2"
               style="color:hsl(142,40%,38%);">
                Selamat, <strong><?= esc($namaSiswa) ?></strong>.<br>
                Pendaftaran Anda sedang dalam proses verifikasi oleh panitia SPMB.
            </p>
        </div>

        <!-- Body: No. Pendaftaran + CTA -->
        <div class="px-8 py-8 space-y-6">

            <!-- Nomor Pendaftaran -->
            <div class="flex items-center gap-4 p-4 rounded-2xl animate-fade-up animate-delay-2"
                 style="background:hsl(220,20%,97%);border:1px solid hsl(220,20%,90%);">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                     style="background:hsl(220,54%,20%,.08);">
                    <svg class="w-5 h-5" style="color:hsl(220,54%,20%);" fill="none" stroke="currentColor"
                         stroke-width="2" viewBox="0 0 24 24">
                        <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                        <line x1="16" y1="13" x2="8" y2="13"/>
                        <line x1="16" y1="17" x2="8" y2="17"/>
                        <polyline points="10 9 9 9 8 9"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-xs font-medium mb-0.5" style="color:hsl(220,15%,55%);">Nomor Pendaftaran</p>
                    <p class="text-lg font-bold font-serif tracking-wide" style="color:hsl(220,54%,15%);">
                        <?= esc($noPendaftaran) ?>
                    </p>
                </div>
                <!-- Copy button -->
                <button type="button"
                        @click="copyNo('<?= esc($noPendaftaran) ?>')"
                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium transition"
                        style="background:hsl(220,54%,20%,.08);color:hsl(220,54%,20%);"
                        onmouseover="this.style.background='hsl(220,54%,20%,.15)'"
                        onmouseout="this.style.background='hsl(220,54%,20%,.08)'">
                    <svg x-show="!copied" class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/>
                        <path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/>
                    </svg>
                    <svg x-show="copied" class="w-3.5 h-3.5" style="color:hsl(142,60%,38%);" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                    <span x-text="copied ? 'Tersalin!' : 'Salin'"></span>
                </button>
            </div>

            <!-- CTA UTAMA: Gabung Grup WA -->
            <div class="animate-fade-up animate-delay-3">
                <p class="text-sm font-semibold text-center mb-3" style="color:hsl(220,54%,25%);">
                    Langkah selanjutnya:
                </p>

                <a href="<?= esc($waGrupLink) ?>"
                   target="_blank"
                   rel="noopener noreferrer"
                   @click="markJoined()"
                   class="wa-btn flex items-center justify-center gap-3 w-full px-6 py-4 rounded-2xl text-white text-base font-bold transition-transform"
                   style="background:linear-gradient(135deg,hsl(142,60%,38%),hsl(142,65%,30%));
                          box-shadow:0 8px 24px -4px hsl(142 65% 38%/0.45);">

                    <!-- WA Icon -->
                    <svg class="w-7 h-7 flex-shrink-0" viewBox="0 0 32 32" fill="currentColor">
                        <path d="M16 0C7.163 0 0 7.163 0 16c0 2.827.737 5.476 2.027 7.775L0 32l8.476-2.004A15.932 15.932 0 0016 32c8.837 0 16-7.163 16-16S24.837 0 16 0zm0 29.333a13.267 13.267 0 01-6.773-1.846l-.486-.288-5.027 1.188 1.21-4.906-.317-.504A13.267 13.267 0 012.667 16C2.667 8.636 8.636 2.667 16 2.667S29.333 8.636 29.333 16 23.364 29.333 16 29.333zm7.27-9.94c-.398-.2-2.353-1.16-2.717-1.293-.365-.133-.63-.2-.896.2-.265.397-1.03 1.293-1.26 1.56-.232.265-.464.298-.863.1-.398-.2-1.683-.62-3.204-1.977-1.185-1.056-1.984-2.36-2.217-2.758-.232-.397-.025-.612.174-.81.179-.177.398-.464.597-.696.2-.232.265-.397.398-.663.133-.265.066-.497-.033-.696-.1-.2-.896-2.16-1.228-2.957-.323-.775-.652-.67-.896-.682-.232-.01-.497-.013-.763-.013a1.464 1.464 0 00-1.06.497c-.364.397-1.393 1.36-1.393 3.317s1.426 3.847 1.625 4.112c.199.265 2.806 4.283 6.797 6.007.95.41 1.692.655 2.27.838.954.304 1.822.26 2.508.158.765-.114 2.353-.963 2.686-1.893.332-.93.332-1.726.232-1.893-.098-.166-.364-.265-.762-.464z"/>
                    </svg>

                    <span>Gabung Grup WhatsApp Pendaftar</span>

                    <svg class="w-5 h-5 flex-shrink-0 opacity-80" fill="none" stroke="currentColor" stroke-width="2.5"
                         viewBox="0 0 24 24">
                        <path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/>
                        <polyline points="15 3 21 3 21 9"/>
                        <line x1="10" y1="14" x2="21" y2="3"/>
                    </svg>
                </a>

                <p class="text-xs text-center mt-2.5" style="color:hsl(220,15%,55%);">
                    Grup WA berisi pengumuman, info seleksi, dan jadwal dari panitia SPMB.
                </p>
            </div>

            <!-- Divider -->
            <div class="flex items-center gap-3 animate-fade-up animate-delay-4">
                <div class="flex-1 h-px" style="background:hsl(220,20%,90%);"></div>
                <span class="text-xs font-medium" style="color:hsl(220,15%,60%);">atau</span>
                <div class="flex-1 h-px" style="background:hsl(220,20%,90%);"></div>
            </div>

            <!-- CTA SEKUNDER: Lihat Status -->
            <div class="animate-fade-up animate-delay-4">
                <a href="<?= base_url('dashboard/status') ?>"
                   class="flex items-center justify-center gap-2 w-full px-6 py-3.5 rounded-2xl text-sm font-semibold border transition"
                   style="border-color:hsl(220,20%,82%);color:hsl(220,54%,20%);background:white;"
                   onmouseover="this.style.background='hsl(220,20%,97%)'"
                   onmouseout="this.style.background='white'">
                    <svg class="w-4.5 h-4.5 w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10"/>
                        <polyline points="12 6 12 12 16 14"/>
                    </svg>
                    Lihat Status Pendaftaran
                </a>
            </div>

            <!-- Kontak Panitia -->
            <div class="flex items-center gap-3 p-4 rounded-2xl animate-fade-up animate-delay-5"
                 style="background:hsl(199,89%,48%,.06);border:1px solid hsl(199,89%,48%,.2);">
                <svg class="w-5 h-5 flex-shrink-0" style="color:hsl(199,60%,42%);" fill="none" stroke="currentColor"
                     stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                <p class="text-sm" style="color:hsl(220,15%,45%);">
                    Ada pertanyaan? Hubungi panitia langsung via WhatsApp:
                    <a href="<?= esc($waKontakLink) ?>" target="_blank" rel="noopener"
                       class="font-semibold" style="color:hsl(142,60%,32%);">
                        <?= esc($waKontakNo) ?>
                    </a>
                </p>
            </div>

        </div>
    </div>

    <!-- ══ INFO LANGKAH SELANJUTNYA ═════════════════════════════════════ -->
    <div class="mt-5 bg-white rounded-2xl p-5 animate-fade-up animate-delay-5"
         style="border:1px solid hsl(220,20%,88%);box-shadow:0 4px 6px -1px hsl(220 54% 20%/0.06);">
        <h3 class="font-semibold text-sm mb-4 flex items-center gap-2" style="color:hsl(220,54%,15%);">
            <svg class="w-4.5 h-4.5 w-5 h-5" style="color:hsl(220,54%,20%);" fill="none" stroke="currentColor"
                 stroke-width="2" viewBox="0 0 24 24">
                <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
            </svg>
            Apa yang terjadi selanjutnya?
        </h3>
        <ol class="space-y-3">
            <?php
            $steps = [
                ['icon' => '1', 'label' => 'Verifikasi Dokumen', 'desc' => 'Tim panitia akan memeriksa kelengkapan dan keabsahan dokumen Anda dalam 3–5 hari kerja.'],
                ['icon' => '2', 'label' => 'Notifikasi Status', 'desc' => 'Anda akan mendapat pemberitahuan di halaman status jika ada pembaruan atau permintaan revisi.'],
                ['icon' => '3', 'label' => 'Proses Seleksi', 'desc' => 'Setelah dokumen terverifikasi, pendaftaran masuk ke tahap seleksi sesuai jadwal SPMB.'],
                ['icon' => '4', 'label' => 'Pengumuman', 'desc' => 'Hasil seleksi akan diumumkan melalui sistem ini dan grup WhatsApp pendaftar.'],
            ];
            foreach ($steps as $s):
            ?>
                <li class="flex items-start gap-3">
                    <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0 mt-0.5"
                          style="background:hsl(220,54%,20%,.1);color:hsl(220,54%,20%);">
                        <?= $s['icon'] ?>
                    </span>
                    <div>
                        <p class="text-sm font-semibold" style="color:hsl(220,54%,15%);"><?= esc($s['label']) ?></p>
                        <p class="text-xs mt-0.5" style="color:hsl(220,15%,50%);"><?= esc($s['desc']) ?></p>
                    </div>
                </li>
            <?php endforeach; ?>
        </ol>
    </div>

</div>

<script>
    function suksesPage() {
        return {
            copied: false,
            joined: false,

            copyNo(text) {
                navigator.clipboard.writeText(text).then(() => {
                    this.copied = true;
                    setTimeout(() => { this.copied = false; }, 2000);
                }).catch(() => {
                    /* fallback: select + execCommand untuk browser lama */
                    const el = document.createElement('textarea');
                    el.value = text;
                    document.body.appendChild(el);
                    el.select();
                    document.execCommand('copy');
                    document.body.removeChild(el);
                    this.copied = true;
                    setTimeout(() => { this.copied = false; }, 2000);
                });
            },

            markJoined() {
                this.joined = true;
            },
        };
    }
</script>