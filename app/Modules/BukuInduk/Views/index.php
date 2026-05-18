<?php

/**
 * File : app/Modules/BukuInduk/Views/index.php
 * Sesuai mockup React BukuIndukPage — split-screen hero + 4 tab detail
 * Self-contained (no partials), Alpine.js + Tailwind
 */

$activeTab  = $activeTab  ?? 'pribadi';
$selectedId = $selected->id ?? 0;

/* ── helpers ── */
function bi_init(string $n): string
{
    $p = array_filter(explode(' ', trim($n)));
    return strtoupper(implode('', array_map(fn($x) => mb_substr($x, 0, 1), array_slice($p, 0, 2))));
}
function bi_bmi(float $b): array
{
    if ($b < 18.5) return ['Kurus',   'hsl(199,60%,40%)'];
    if ($b < 25)   return ['Normal',  'hsl(142,55%,32%)'];
    if ($b < 30)   return ['Gemuk',   'hsl(38,60%,38%)'];
    return               ['Obesitas', 'hsl(0,55%,40%)'];
}
?>
<div class="space-y-6" x-data="bukuIndukPage()" x-cloak>

    <!-- ══ PAGE HEADER ══════════════════════════════════════════ -->
    <div class="flex flex-col gap-3">

        <!-- Baris 1: Judul + Search -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold font-serif tracking-tight" style="color:hsl(220,54%,15%)">Buku Induk Siswa</h1>
                <p class="text-sm mt-1" style="color:hsl(220,15%,50%)">Kelola seluruh catatan permanen siswa aktif sekolah</p>
            </div>
            <form method="get" class="relative w-full sm:w-[340px]">
                <?php if ($selectedId): ?><input type="hidden" name="id" value="<?= $selectedId ?>"><?php endif; ?>
                <?php if (!empty($filters['jurusan_id'])): ?><input type="hidden" name="jurusan_id" value="<?= esc($filters['jurusan_id']) ?>"><?php endif; ?>
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 pointer-events-none" style="color:hsl(220,15%,55%)" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z" />
                </svg>
                <input type="text" name="search" value="<?= esc($filters['search'] ?? '') ?>"
                    placeholder="Cari NIS, nama, atau kelas..."
                    class="w-full pl-9 pr-4 h-11 border rounded-xl text-sm focus:outline-none focus:ring-2"
                    style="border-color:hsl(220,20%,85%);color:hsl(220,54%,15%)">
            </form>
        </div>

        <!-- Baris 2: Toolbar Export -->
        <?php
        $exportQs = http_build_query(array_filter([
            'jurusan_id'   => $filters['jurusan_id']   ?? '',
            'status_siswa' => $filters['status_siswa'] ?? '',
            'search'       => $filters['search']       ?? '',
        ]));
        $exportAllUrl = base_url('admin/buku-induk/export-excel') . ($exportQs ? '?' . $exportQs : '');
        ?>
        <div class="flex flex-wrap items-center gap-2">

            <!-- Export semua (sesuai filter aktif) -->
            <a href="<?= $exportAllUrl ?>"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold border transition"
                style="background:hsl(142,55%,95%);border-color:hsl(142,55%,70%);color:hsl(142,55%,28%);"
                onmouseover="this.style.background='hsl(142,55%,88%)'"
                onmouseout="this.style.background='hsl(142,55%,95%)'">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
                Export Semua ke Excel
                <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold" style="background:hsl(142,55%,38%);color:white"><?= number_format($total) ?></span>
            </a>

            <!-- Export selected -->
            <form id="formExportSelected"
                method="post"
                action="<?= base_url('admin/buku-induk/export-excel-selected') ?>"
                x-data>
                <?= csrf_field() ?>
                <template x-for="id in $store.bukuIndukChecked.ids" :key="id">
                    <input type="hidden" name="ids[]" :value="id">
                </template>
                <button type="button"
                    @click="
                            if ($store.bukuIndukChecked.ids.length === 0) {
                                alert('Centang minimal 1 siswa dari daftar terlebih dahulu.');
                            } else {
                                $el.closest('form').submit();
                            }
                        "
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold border transition"
                    style="background:hsl(38,80%,95%);border-color:hsl(38,70%,65%);color:hsl(38,60%,28%);"
                    onmouseover="this.style.background='hsl(38,80%,88%)'"
                    onmouseout="this.style.background='hsl(38,80%,95%)'">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                    </svg>
                    Export Terpilih
                    <span x-text="$store.bukuIndukChecked.ids.length > 0 ? '(' + $store.bukuIndukChecked.ids.length + ')' : ''"
                        class="px-1.5 py-0.5 rounded-full text-[10px] font-bold"
                        style="background:hsl(38,60%,40%);color:white"
                        x-show="$store.bukuIndukChecked.ids.length > 0"></span>
                </button>
            </form>

            <!-- Centang semua / batal -->
            <button type="button"
                @click="$store.bukuIndukChecked.toggleAll(<?= htmlspecialchars(json_encode(array_column($siswas, 'id'))) ?>)"
                class="inline-flex items-center gap-2 px-3 py-2 rounded-xl text-xs font-medium border transition"
                style="background:white;border-color:hsl(220,20%,82%);color:hsl(220,15%,45%);"
                onmouseover="this.style.background='hsl(220,20%,96%)'"
                onmouseout="this.style.background='white'">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        x-bind:d="$store.bukuIndukChecked.ids.length > 0
                              ? 'M6 18L18 6M6 6l12 12'
                              : 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z'" />
                </svg>
                <span x-text="$store.bukuIndukChecked.ids.length > 0 ? 'Batal Pilih' : 'Pilih Semua'">Pilih Semua</span>
            </button>

            <?php if (!empty($filters['jurusan_id']) || !empty($filters['search'])): ?>
                <span class="text-xs italic" style="color:hsl(220,15%,55%)">
                    Filter aktif —
                    <?php if (!empty($filters['jurusan_id'])): ?>Jurusan ID: <?= esc($filters['jurusan_id']) ?><?php endif; ?>
                    <?php if (!empty($filters['search'])): ?> | Cari: "<?= esc($filters['search']) ?>"<?php endif; ?>
                </span>
            <?php endif; ?>
        </div>
    </div>

    <!-- Alpine store untuk checkbox export selected -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('bukuIndukChecked', {
                ids: [],
                toggle(id) {
                    const idx = this.ids.indexOf(id);
                    idx === -1 ? this.ids.push(id) : this.ids.splice(idx, 1);
                },
                has(id) {
                    return this.ids.includes(id);
                },
                toggleAll(allIds) {
                    this.ids = this.ids.length > 0 ? [] : [...allIds];
                }
            });
        });
    </script>


    <!-- ══ FLASH MESSAGE ══ -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="flex items-center gap-3 px-4 py-3 rounded-xl border text-sm font-medium"
            style="background:hsl(142,71%,45%,0.08);border-color:hsl(142,71%,45%,0.3);color:hsl(142,55%,28%)">
            <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <?= session()->getFlashdata('success') ?>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="flex items-center gap-3 px-4 py-3 rounded-xl border text-sm font-medium"
            style="background:hsl(0,72%,51%,0.08);border-color:hsl(0,72%,51%,0.3);color:hsl(0,55%,38%)">
            <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126z" />
            </svg>
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>

    <!-- ══ MAIN GRID ══════════════════════════════════════════ -->
    <div class="grid lg:grid-cols-12 gap-6">

        <!-- ─── LEFT: Daftar Siswa ─────────────────────────── -->
        <div class="lg:col-span-4 xl:col-span-3 bg-white rounded-2xl border overflow-hidden flex flex-col" style="border-color:hsl(220,20%,88%)">

            <!-- header -->
            <div class="px-5 py-4 border-b flex items-center justify-between"
                style="background:linear-gradient(135deg,hsl(220,54%,97%),hsl(43,70%,97%));border-color:hsl(220,20%,88%)">
                <h3 class="font-semibold text-sm flex items-center gap-2" style="color:hsl(220,54%,15%)">
                    <svg class="h-4 w-4" style="color:hsl(220,54%,30%)" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                    </svg>
                    Daftar Siswa
                </h3>
                <span class="text-xs font-mono px-2 py-0.5 border rounded-full" style="border-color:hsl(220,20%,82%);color:hsl(220,15%,50%)"><?= number_format($total) ?></span>
            </div>

            <!-- filter jurusan -->
            <div class="px-4 py-3 border-b" style="border-color:hsl(220,20%,94%)">
                <form method="get">
                    <select name="jurusan_id" onchange="this.form.submit()"
                        class="w-full px-3 py-2 border rounded-xl text-xs focus:outline-none bg-white"
                        style="border-color:hsl(220,20%,85%);color:hsl(220,54%,15%)">
                        <option value="">Semua Jurusan</option>
                        <?php foreach ($jurusans as $j): ?>
                            <option value="<?= $j->id ?>" <?= ($filters['jurusan_id'] ?? '') == $j->id ? 'selected' : '' ?>>
                                <?= esc($j->kode) ?> — <?= esc($j->nama) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>

            <!-- list -->
            <div class="flex-1 divide-y overflow-y-auto" style="max-height:60vh;divide-color:hsl(220,20%,94%)">
                <?php foreach ($siswas as $s):
                    $active = ($selectedId === $s->id);
                    $init   = bi_init($s->nama_lengkap);
                    $href   = base_url('admin/buku-induk?id=' . $s->id
                        . (!empty($filters['search'])     ? '&search=' . urlencode($filters['search'])       : '')
                        . (!empty($filters['jurusan_id']) ? '&jurusan_id=' . $filters['jurusan_id']         : ''));
                ?>
                    <!-- Item siswa: wrapper div supaya checkbox + link bisa sejajar -->
                    <div class="relative flex items-stretch border-l-4 transition-colors group"
                        style="<?= $active ? 'border-left-color:hsl(43,70%,47%);background:hsl(220,54%,97%)' : 'border-left-color:transparent' ?>"
                        x-data>

                        <!-- Checkbox export -->
                        <label class="flex items-center pl-3 pr-1 cursor-pointer shrink-0"
                            @click.stop>
                            <input type="checkbox"
                                :checked="$store.bukuIndukChecked.has(<?= $s->id ?>)"
                                @change="$store.bukuIndukChecked.toggle(<?= $s->id ?>)"
                                class="w-4 h-4 rounded cursor-pointer"
                                style="accent-color:hsl(220,54%,30%)">
                        </label>

                        <!-- Link ke detail -->
                        <a href="<?= $href ?>"
                            class="flex items-center gap-3 px-3 py-3.5 flex-1 group transition-colors"
                            onmouseover="if(!this.closest('[style*=background]').style.background)this.style.background='hsl(220,20%,97%)'"
                            onmouseout="this.style.background=''">
                            <div class="h-10 w-10 rounded-full flex items-center justify-center font-bold text-sm shrink-0"
                                style="<?= $active ? 'background:linear-gradient(135deg,hsl(220,54%,20%),hsl(220,54%,35%));color:hsl(45,70%,92%)' : 'background:hsl(220,20%,92%);color:hsl(220,54%,25%)' ?>">
                                <?= $init ?>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="font-semibold text-sm truncate" style="color:hsl(220,54%,15%)"><?= esc($s->nama_lengkap) ?></p>
                                <p class="text-xs font-mono" style="color:hsl(220,15%,50%)">NIS: <?= esc($s->nis) ?></p>
                                <div class="flex gap-1.5 mt-1.5 flex-wrap">
                                    <span class="px-1.5 py-0.5 border rounded-full text-[10px] font-medium" style="border-color:hsl(220,20%,82%);color:hsl(220,15%,40%)"><?= esc($s->jurusan_kode) ?></span>
                                    <span class="px-1.5 py-0.5 rounded-full text-[10px] font-medium" style="background:hsl(142,71%,45%,0.1);color:hsl(142,60%,28%)"><?= esc($s->kelas_nama ?? '-') ?></span>
                                </div>
                            </div>
                        </a>

                        <!-- Tombol export single (muncul saat hover) -->
                        <a href="<?= base_url('admin/buku-induk/' . $s->id . '/export-excel') ?>"
                            title="Export Excel siswa ini"
                            class="flex items-center px-2 opacity-0 group-hover:opacity-100 transition-opacity shrink-0"
                            style="color:hsl(142,55%,35%)"
                            onmouseover="this.style.color='hsl(142,55%,25%)'"
                            onmouseout="this.style.color='hsl(142,55%,35%)'">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                            </svg>
                        </a>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($siswas)): ?>
                    <div class="py-14 text-center" style="color:hsl(220,15%,60%)">
                        <svg class="h-10 w-10 mx-auto mb-2 opacity-30" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path d="M21 21l-4.35-4.35M17 11A6 6 0 105 11a6 6 0 0012 0z" />
                        </svg>
                        <p class="text-sm">Tidak ada siswa ditemukan</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- ─── RIGHT: Detail ──────────────────────────────── -->
        <?php if ($selected):
            $init   = bi_init($selected->nama_lengkap);
            $tb     = (float)($selected->tinggi_badan ?? 0);
            $bb     = (float)($selected->berat_badan  ?? 0);
            $bmiVal = ($tb > 0 && $bb > 0) ? round($bb / (($tb / 100) ** 2), 1) : null;
            $bmiCat = $bmiVal ? bi_bmi($bmiVal) : null;
        ?>
            <div class="lg:col-span-8 xl:col-span-9 space-y-6">

                <!-- ── HERO CARD ─────────────────────────────────── -->
                <div class="rounded-2xl overflow-hidden shadow-lg" style="border:1px solid hsl(220,20%,88%)">

                    <!-- banner -->
                    <div class="relative p-6 text-white" style="background:linear-gradient(135deg,hsl(220,54%,20%) 0%,hsl(220,54%,28%) 60%,hsl(220,54%,22%) 100%)">
                        <div class="absolute inset-0 opacity-10 pointer-events-none" style="background:radial-gradient(circle at 20% 50%,hsl(43,70%,60%),transparent 55%)"></div>
                        <div class="relative flex flex-col md:flex-row gap-5 items-start md:items-center">
                            <!-- avatar -->
                            <div class="h-24 w-24 rounded-2xl flex items-center justify-center text-3xl font-bold font-serif shadow-xl shrink-0"
                                style="background:linear-gradient(135deg,hsl(43,70%,47%),hsl(43,80%,58%));color:hsl(220,54%,10%)">
                                <?= $init ?>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-wrap items-center gap-2 mb-2">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold" style="background:hsl(142,71%,45%);color:white">
                                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                            <path d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Siswa Aktif
                                    </span>
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold border" style="border-color:rgba(255,255,255,.3);background:rgba(255,255,255,.1)"><?= esc($selected->jurusan_kode) ?></span>
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold border" style="border-color:rgba(255,255,255,.3);background:rgba(255,255,255,.1)"><?= esc($selected->kelas_nama ?? '-') ?></span>
                                </div>
                                <h2 class="text-2xl md:text-3xl font-bold font-serif leading-tight"><?= esc($selected->nama_lengkap) ?></h2>
                                <div class="flex flex-wrap gap-x-5 gap-y-1 mt-2 text-sm" style="color:rgba(255,255,255,.75)">
                                    <span>NIS: <span class="font-mono font-semibold text-white"><?= esc($selected->nis) ?></span></span>
                                    <span>NISN: <span class="font-mono font-semibold text-white"><?= esc($selected->nisn ?? '-') ?></span></span>
                                    <span>TA <?= esc($selected->tahun_masuk ?? date('Y')) ?>/<?= ($selected->tahun_masuk ?? (int)date('Y')) + 1 ?></span>
                                </div>
                            </div>
                            <!-- actions -->
                            <div class="flex flex-col gap-2 w-full md:w-auto shrink-0">
                                <button type="button" @click="showLogDialog = true"
                                    class="inline-flex items-center justify-center gap-2 px-3 py-2 rounded-xl text-sm font-semibold"
                                    style="background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.25);color:white">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Riwayat Edit
                                    <span x-show="editLogs.length>0" x-text="editLogs.length"
                                        class="px-1.5 py-0.5 rounded-full text-[10px] font-bold" style="background:hsl(0,72%,51%)"></span>
                                </button>
                                <a href="<?= base_url('admin/buku-induk/' . $selected->id . '/cetak') ?>" target="_blank"
                                    class="inline-flex items-center justify-center gap-2 px-3 py-2 rounded-xl text-sm font-semibold"
                                    style="background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.25);color:white">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.056 48.056 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659" />
                                    </svg>
                                    Cetak Buku Induk
                                </a>

                                <!-- Export Excel siswa ini -->
                                <a href="<?= base_url('admin/buku-induk/' . $selected->id . '/export-excel') ?>"
                                    class="inline-flex items-center justify-center gap-2 px-3 py-2 rounded-xl text-sm font-semibold"
                                    style="background:rgba(34,197,94,.18);border:1px solid rgba(34,197,94,.35);color:white"
                                    title="Unduh data siswa ini ke Excel">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                    </svg>
                                    Export Excel
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- quick stats strip -->
                    <div class="grid grid-cols-2 md:grid-cols-4 bg-white" style="border-top:1px solid hsl(220,20%,88%)">
                        <div class="p-4 text-center">
                            <p class="text-[10px] uppercase tracking-wider font-semibold mb-1" style="color:hsl(220,15%,55%)">Tempat, Tgl Lahir</p>
                            <p class="font-bold text-sm" style="color:hsl(220,54%,15%)"><?= esc($selected->tempat_lahir ?? '-') ?></p>
                            <p class="text-xs mt-0.5" style="color:hsl(220,15%,55%)"><?= $selected->tanggal_lahir ? date('d/m/Y', strtotime($selected->tanggal_lahir)) : '-' ?></p>
                        </div>
                        <div class="p-4 text-center">
                            <p class="text-[10px] uppercase tracking-wider font-semibold mb-1" style="color:hsl(220,15%,55%)">Wali Kelas</p>
                            <p class="font-bold text-sm truncate" style="color:hsl(220,54%,15%)"><?= esc($selected->wali_kelas ?? '-') ?></p>
                        </div>
                        <div class="p-4 text-center">
                            <p class="text-[10px] uppercase tracking-wider font-semibold mb-1" style="color:hsl(220,15%,55%)">Gol. Darah</p>
                            <p class="font-bold text-2xl font-serif" style="color:hsl(0,72%,51%)"><?= esc($selected->golongan_darah ?? '-') ?></p>
                        </div>
                        <div class="p-4 text-center">
                            <p class="text-[10px] uppercase tracking-wider font-semibold mb-1" style="color:hsl(220,15%,55%)">Kontak</p>
                            <p class="font-bold text-sm font-mono" style="color:hsl(220,54%,15%)"><?= esc($selected->no_hp ?? '-') ?></p>
                        </div>
                    </div>
                </div><!-- /hero -->

                <!-- ── TABS CARD ──────────────────────────────────── -->
                <div class="bg-white rounded-2xl border overflow-hidden" style="border-color:hsl(220,20%,88%)"
                    x-data="{ tab: '<?= $activeTab ?>' }">

                    <!-- tab bar -->
                    <div class="flex border-b overflow-x-auto" style="background:hsl(220,20%,97%);border-color:hsl(220,20%,88%)">
                        <?php
                        $TABS = [
                            ['pribadi',   'Data Pribadi',      'M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z'],
                            ['kontak',    'Alamat & Keluarga', 'M15 10.5a3 3 0 11-6 0 3 3 0 016 0zM19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z'],
                            ['kesehatan', 'Data Kesehatan',    'M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z'],
                            ['kelas',     'Penempatan Kelas',  'M4.26 10.147a60.438 60.438 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.906 59.906 0 0112 3.493a59.903 59.903 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0112 13.489a50.702 50.702 0 017.74-3.342'],
                        ];
                        foreach ($TABS as [$tv, $tl, $tp]):
                        ?>
                            <button type="button" @click="tab='<?= $tv ?>'"
                                :class="tab==='<?= $tv ?>' ? 'border-b-2 font-semibold' : 'border-b-2 border-transparent'"
                                class="flex items-center gap-2 px-5 py-3.5 text-sm shrink-0 transition-colors"
                                :style="tab==='<?= $tv ?>'
                ? 'border-bottom-color:hsl(43,70%,47%);color:hsl(220,54%,20%);background:white'
                : 'color:hsl(220,15%,55%)'">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="<?= $tp ?>" />
                                </svg>
                                <span class="hidden sm:inline"><?= $tl ?></span>
                            </button>
                        <?php endforeach; ?>
                    </div>

                    <!-- ══════════════════════ TAB: DATA PRIBADI ══════════════════════ -->
                    <div x-show="tab==='pribadi'" x-transition class="p-6">

                        <!-- edit banner -->
                        <div x-show="!editingPribadi">
                            <div class="flex items-center justify-between gap-4 flex-wrap px-4 py-3 rounded-xl border"
                                style="background:hsl(142,71%,45%,0.06);border-color:hsl(142,71%,45%,0.25)">
                                <div class="flex items-center gap-2 text-sm" style="color:hsl(142,55%,30%)">
                                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Identitas resmi siswa berdasarkan formulir pendaftaran.
                                </div>
                                <button type="button" @click="editingPribadi=true"
                                    class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm font-semibold text-white"
                                    style="background:hsl(220,54%,20%)"
                                    onmouseover="this.style.background='hsl(220,54%,28%)'" onmouseout="this.style.background='hsl(220,54%,20%)'">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path d="m16.862 4.487 1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                                    </svg>
                                    Edit Data
                                </button>
                            </div>
                        </div>
                        <div x-show="editingPribadi">
                            <div class="flex items-center justify-between gap-4 flex-wrap px-4 py-3 rounded-xl border"
                                style="background:hsl(38,92%,50%,0.06);border-color:hsl(38,92%,50%,0.35)">
                                <div class="flex items-center gap-2 text-sm font-medium" style="color:hsl(38,60%,30%)">
                                    <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126z" />
                                    </svg>
                                    <strong>Mode Edit Aktif.</strong>&nbsp;Semua perubahan tercatat di Riwayat Edit.
                                </div>
                                <div class="flex gap-2">
                                    <button type="button" @click="confirmCancel('pribadi')"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-semibold border"
                                        style="border-color:hsl(220,20%,82%);color:hsl(220,54%,20%)"
                                        onmouseover="this.style.background='hsl(220,20%,95%)'" onmouseout="this.style.background=''">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path d="M6 18L18 6M6 6l12 12" />
                                        </svg>Batal
                                    </button>
                                    <button type="button" @click="confirmSave('pribadi')"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-semibold text-white"
                                        style="background:hsl(220,54%,20%)"
                                        onmouseover="this.style.background='hsl(220,54%,28%)'" onmouseout="this.style.background='hsl(220,54%,20%)'">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                                        </svg>Simpan
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- VIEW pribadi -->
                        <div x-show="!editingPribadi" class="mt-6 grid md:grid-cols-2 gap-x-8 gap-y-6">
                            <!-- identitas -->
                            <div>
                                <div class="flex items-center gap-3 mb-4">
                                    <div class="h-9 w-9 rounded-lg flex items-center justify-center" style="background:hsl(220,54%,20%,0.08)">
                                        <svg class="h-5 w-5" style="color:hsl(220,54%,25%)" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="font-bold font-serif text-base" style="color:hsl(220,54%,15%)">Identitas Resmi</h3>
                                        <div class="h-0.5 w-12 rounded-full mt-0.5" style="background:hsl(43,70%,47%)"></div>
                                    </div>
                                </div>
                                <?php
                                $identRows = [
                                    ['NIS',           esc($selected->nis ?? '-')],
                                    ['NISN',          esc($selected->nisn ?? '-')],
                                    ['NIK',           esc($selected->nik ?? '-')],
                                    ['Nama Lengkap',  esc($selected->nama_lengkap ?? '-')],
                                    ['Nama Panggilan', esc($selected->nama_panggilan ?? '-')],
                                    ['Tempat Lahir',  esc($selected->tempat_lahir ?? '-')],
                                    ['Tanggal Lahir', $selected->tanggal_lahir ? date('d/m/Y', strtotime($selected->tanggal_lahir)) : '-'],
                                    ['Jenis Kelamin', ($selected->jenis_kelamin ?? '') === 'L' ? 'Laki-laki' : 'Perempuan'],
                                    ['Agama',         esc($selected->agama ?? '-')],
                                    ['Kewarganegaraan', esc($selected->kewarganegaraan ?? 'Indonesia')],
                                ];
                                foreach ($identRows as [$lbl, $val]):
                                ?>
                                    <div class="py-2.5 border-b" style="border-color:hsl(220,20%,93%)">
                                        <p class="text-[10px] font-semibold uppercase tracking-wider" style="color:hsl(220,15%,55%)"><?= $lbl ?></p>
                                        <p class="text-sm font-semibold mt-0.5 break-words" style="color:hsl(220,54%,15%)"><?= $val ?></p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <!-- riwayat pendidikan -->
                            <div>
                                <div class="flex items-center gap-3 mb-4">
                                    <div class="h-9 w-9 rounded-lg flex items-center justify-center" style="background:hsl(199,89%,48%,0.1)">
                                        <svg class="h-5 w-5" style="color:hsl(199,60%,38%)" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path d="M4.26 10.147a60.438 60.438 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.906 59.906 0 0112 3.493a59.903 59.903 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0112 13.489a50.702 50.702 0 017.74-3.342" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="font-bold font-serif text-base" style="color:hsl(220,54%,15%)">Riwayat Pendidikan</h3>
                                        <div class="h-0.5 w-12 rounded-full mt-0.5" style="background:hsl(43,70%,47%)"></div>
                                    </div>
                                </div>
                                <?php foreach (
                                    [
                                        ['Sekolah Asal', esc($selected->asal_sekolah ?? '-')],
                                        ['Tahun Lulus',  esc($selected->tahun_lulus_smp ?? '-')],
                                    ] as [$lbl, $val]
                                ): ?>
                                    <div class="py-2.5 border-b" style="border-color:hsl(220,20%,93%)">
                                        <p class="text-[10px] font-semibold uppercase tracking-wider" style="color:hsl(220,15%,55%)"><?= $lbl ?></p>
                                        <p class="text-sm font-semibold mt-0.5" style="color:hsl(220,54%,15%)"><?= $val ?></p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- EDIT pribadi -->
                        <div x-show="editingPribadi" class="mt-6">
                            <form id="formPribadi" action="<?= base_url('admin/buku-induk/' . $selected->id . '/pribadi') ?>" method="POST">
                                <?= csrf_field() ?>
                                <div class="grid md:grid-cols-2 gap-6">
                                    <div class="space-y-3">
                                        <h4 class="font-bold text-sm font-serif" style="color:hsl(220,54%,15%)">Identitas Resmi</h4>

                                        <div class="space-y-1.5">
                                            <label class="block text-[10px] font-semibold uppercase tracking-wider" style="color:hsl(220,15%,55%)">NIS (terkunci)</label>
                                            <input type="text" value="<?= esc($selected->nis) ?>" disabled
                                                class="w-full px-3 py-2.5 border rounded-xl text-sm" style="background:hsl(220,20%,95%);border-color:hsl(220,20%,85%);color:hsl(220,15%,55%)">
                                        </div>
                                        <?php foreach (
                                            [
                                                ['nisn',          'NISN',          'text', esc($selected->nisn ?? '')],
                                                ['nik',           'NIK',           'text', esc($selected->nik ?? '')],
                                                ['nama_lengkap',  'Nama Lengkap',  'text', esc($selected->nama_lengkap ?? '')],
                                                ['nama_panggilan', 'Nama Panggilan', 'text', esc($selected->nama_panggilan ?? '')],
                                            ] as [$n, $l, $t, $v]
                                        ): ?>
                                            <div class="space-y-1.5">
                                                <label class="block text-[10px] font-semibold uppercase tracking-wider" style="color:hsl(220,15%,55%)"><?= $l ?></label>
                                                <input type="<?= $t ?>" name="<?= $n ?>" value="<?= $v ?>"
                                                    class="w-full px-3 py-2.5 border rounded-xl text-sm focus:outline-none focus:ring-2" style="border-color:hsl(220,20%,85%)">
                                            </div>
                                        <?php endforeach; ?>

                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                            <div class="space-y-1.5">
                                                <label class="block text-[10px] font-semibold uppercase tracking-wider" style="color:hsl(220,15%,55%)">Tempat Lahir</label>
                                                <input type="text" name="tempat_lahir" value="<?= esc($selected->tempat_lahir ?? '') ?>" data-old-value="<?= esc($selected->tempat_lahir ?? '') ?>"
                                                    class="w-full px-3 py-2.5 border rounded-xl text-sm focus:outline-none" style="border-color:hsl(220,20%,85%)">
                                            </div>
                                            <div class="space-y-1.5">
                                                <label class="block text-[10px] font-semibold uppercase tracking-wider" style="color:hsl(220,15%,55%)">Tanggal Lahir</label>
                                                <input type="date" name="tanggal_lahir" value="<?= esc($selected->tanggal_lahir ?? '') ?>" data-old-value="<?= esc($selected->tanggal_lahir ?? '') ?>"
                                                    class="w-full px-3 py-2.5 border rounded-xl text-sm focus:outline-none" style="border-color:hsl(220,20%,85%)">
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                            <div class="space-y-1.5">
                                                <label class="block text-[10px] font-semibold uppercase tracking-wider" style="color:hsl(220,15%,55%)">Jenis Kelamin</label>
                                                <select name="jenis_kelamin" class="w-full px-3 py-2.5 border rounded-xl text-sm focus:outline-none bg-white" style="border-color:hsl(220,20%,85%)">
                                                    <option value="L" <?= ($selected->jenis_kelamin ?? '') === 'L' ? 'selected' : '' ?>>Laki-laki</option>
                                                    <option value="P" <?= ($selected->jenis_kelamin ?? '') === 'P' ? 'selected' : '' ?>>Perempuan</option>
                                                </select>
                                            </div>
                                            <div class="space-y-1.5">
                                                <label class="block text-[10px] font-semibold uppercase tracking-wider" style="color:hsl(220,15%,55%)">Agama</label>
                                                <select name="agama" class="w-full px-3 py-2.5 border rounded-xl text-sm focus:outline-none bg-white" style="border-color:hsl(220,20%,85%)">
                                                    <?php foreach (['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'] as $ag): ?>
                                                        <option value="<?= $ag ?>" <?= ($selected->agama ?? '') === $ag ? 'selected' : '' ?>><?= $ag ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="block text-[10px] font-semibold uppercase tracking-wider" style="color:hsl(220,15%,55%)">Kewarganegaraan</label>
                                            <input type="text" name="kewarganegaraan" value="<?= esc($selected->kewarganegaraan ?? 'Indonesia') ?>" data-old-value="<?= esc($selected->kewarganegaraan ?? 'Indonesia') ?>"
                                                class="w-full px-3 py-2.5 border rounded-xl text-sm focus:outline-none" style="border-color:hsl(220,20%,85%)">
                                        </div>
                                    </div>
                                    <div class="space-y-3">
                                        <h4 class="font-bold text-sm font-serif" style="color:hsl(220,54%,15%)">Riwayat Pendidikan</h4>
                                        <div class="space-y-1.5">
                                            <label class="block text-[10px] font-semibold uppercase tracking-wider" style="color:hsl(220,15%,55%)">Sekolah Asal</label>
                                            <input type="text" name="asal_sekolah" value="<?= esc($selected->asal_sekolah ?? '') ?>" data-old-value="<?= esc($selected->asal_sekolah ?? '') ?>"
                                                class="w-full px-3 py-2.5 border rounded-xl text-sm focus:outline-none" style="border-color:hsl(220,20%,85%)">
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="block text-[10px] font-semibold uppercase tracking-wider" style="color:hsl(220,15%,55%)">Tahun Lulus SMP</label>
                                            <input type="text" name="tahun_lulus_smp" value="<?= esc($selected->tahun_lulus_smp ?? '') ?>" data-old-value="<?= esc($selected->tahun_lulus_smp ?? '') ?>"
                                                class="w-full px-3 py-2.5 border rounded-xl text-sm focus:outline-none" style="border-color:hsl(220,20%,85%)">
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div><!-- /tab pribadi -->

                    <!-- ══════════════════════ TAB: ALAMAT & KELUARGA ══════════════════════ -->
                    <div x-show="tab==='kontak'" x-transition class="p-6">

                        <!-- banner (shares editingPribadi) -->
                        <div x-show="!editingPribadi">
                            <div class="flex items-center justify-between gap-4 flex-wrap px-4 py-3 rounded-xl border"
                                style="background:hsl(142,71%,45%,0.06);border-color:hsl(142,71%,45%,0.25)">
                                <span class="text-sm" style="color:hsl(142,55%,30%)">Alamat tempat tinggal, kontak siswa, dan data orang tua/wali.</span>
                                <button type="button" @click="editingPribadi=true"
                                    class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm font-semibold text-white"
                                    style="background:hsl(220,54%,20%)"
                                    onmouseover="this.style.background='hsl(220,54%,28%)'" onmouseout="this.style.background='hsl(220,54%,20%)'">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path d="m16.862 4.487 1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                                    </svg>
                                    Edit Data
                                </button>
                            </div>
                        </div>
                        <div x-show="editingPribadi">
                            <div class="flex items-center justify-between gap-4 flex-wrap px-4 py-3 rounded-xl border"
                                style="background:hsl(38,92%,50%,0.06);border-color:hsl(38,92%,50%,0.35)">
                                <span class="text-sm font-medium" style="color:hsl(38,60%,30%)"><strong>Mode Edit Aktif.</strong>&nbsp;Perubahan dicatat di Riwayat Edit.</span>
                                <div class="flex gap-2">
                                    <button type="button" @click="confirmCancel('pribadi')"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-semibold border"
                                        style="border-color:hsl(220,20%,82%);color:hsl(220,54%,20%)"
                                        onmouseover="this.style.background='hsl(220,20%,95%)'" onmouseout="this.style.background=''">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path d="M6 18L18 6M6 6l12 12" />
                                        </svg>Batal
                                    </button>
                                    <button type="button" @click="confirmSave('pribadi')"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-semibold text-white"
                                        style="background:hsl(220,54%,20%)"
                                        onmouseover="this.style.background='hsl(220,54%,28%)'" onmouseout="this.style.background='hsl(220,54%,20%)'">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                                        </svg>Simpan
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- VIEW kontak -->
                        <div x-show="!editingPribadi" class="mt-6 grid md:grid-cols-2 gap-x-8 gap-y-6">
                            <div>
                                <div class="flex items-center gap-3 mb-4">
                                    <div class="h-9 w-9 rounded-lg flex items-center justify-center" style="background:hsl(38,92%,50%,0.1)">
                                        <svg class="h-5 w-5" style="color:hsl(38,60%,40%)" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0zM19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="font-bold font-serif text-base" style="color:hsl(220,54%,15%)">Alamat &amp; Kontak</h3>
                                        <div class="h-0.5 w-12 rounded-full mt-0.5" style="background:hsl(43,70%,47%)"></div>
                                    </div>
                                </div>
                                <?php foreach (
                                    [
                                        ['Alamat Lengkap', esc($selected->alamat ?? '-')],
                                        ['No. Telepon',    esc($selected->no_hp ?? '-')],
                                        ['Email',          esc($selected->email_siswa ?? '-')],
                                    ] as [$lbl, $val]
                                ): ?>
                                    <div class="py-2.5 border-b" style="border-color:hsl(220,20%,93%)">
                                        <p class="text-[10px] font-semibold uppercase tracking-wider" style="color:hsl(220,15%,55%)"><?= $lbl ?></p>
                                        <p class="text-sm font-semibold mt-0.5 break-words" style="color:hsl(220,54%,15%)"><?= $val ?></p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div>
                                <div class="flex items-center gap-3 mb-4">
                                    <div class="h-9 w-9 rounded-lg flex items-center justify-center" style="background:hsl(142,71%,45%,0.1)">
                                        <svg class="h-5 w-5" style="color:hsl(142,55%,32%)" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="font-bold font-serif text-base" style="color:hsl(220,54%,15%)">Data Orang Tua / Wali</h3>
                                        <div class="h-0.5 w-12 rounded-full mt-0.5" style="background:hsl(43,70%,47%)"></div>
                                    </div>
                                </div>
                                <!-- Ayah -->
                                <div class="rounded-xl border p-3 mb-2" style="background:hsl(220,20%,97%);border-color:hsl(220,20%,90%)">
                                    <p class="text-[10px] font-bold uppercase tracking-wider mb-2" style="color:hsl(220,54%,25%)">Ayah</p>
                                    <?php foreach (
                                        [
                                            ['Nama',      esc($selected->nama_ayah ?? '-')],
                                            ['Pekerjaan', esc($selected->pekerjaan_ayah ?? '-')],
                                            ['No. HP',    esc($selected->no_hp_ayah ?? $selected->no_hp_ortu ?? '-')],
                                        ] as [$lbl, $val]
                                    ): ?>
                                        <div class="py-1.5 border-b last:border-0" style="border-color:hsl(220,20%,91%)">
                                            <p class="text-[10px]" style="color:hsl(220,15%,55%)"><?= $lbl ?></p>
                                            <p class="text-sm font-semibold" style="color:hsl(220,54%,15%)"><?= $val ?></p>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <!-- Ibu -->
                                <div class="rounded-xl border p-3" style="background:hsl(220,20%,97%);border-color:hsl(220,20%,90%)">
                                    <p class="text-[10px] font-bold uppercase tracking-wider mb-2" style="color:hsl(220,54%,25%)">Ibu</p>
                                    <?php foreach (
                                        [
                                            ['Nama',      esc($selected->nama_ibu ?? '-')],
                                            ['Pekerjaan', esc($selected->pekerjaan_ibu ?? '-')],
                                            ['No. HP',    esc($selected->no_hp_ibu ?? $selected->no_hp_ortu ?? '-')],
                                        ] as [$lbl, $val]
                                    ): ?>
                                        <div class="py-1.5 border-b last:border-0" style="border-color:hsl(220,20%,91%)">
                                            <p class="text-[10px]" style="color:hsl(220,15%,55%)"><?= $lbl ?></p>
                                            <p class="text-sm font-semibold" style="color:hsl(220,54%,15%)"><?= $val ?></p>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <!-- EDIT kontak (lanjutan form #formPribadi) -->
                        <div x-show="editingPribadi" class="mt-6 grid md:grid-cols-2 gap-6">
                            <div class="space-y-3">
                                <h4 class="font-bold text-sm font-serif" style="color:hsl(220,54%,15%)">Alamat &amp; Kontak</h4>
                                <div class="space-y-1.5">
                                    <label class="block text-[10px] font-semibold uppercase tracking-wider" style="color:hsl(220,15%,55%)">Alamat Lengkap</label>
                                    <textarea name="alamat" rows="3" form="formPribadi"
                                        class="w-full px-3 py-2.5 border rounded-xl text-sm focus:outline-none resize-none" style="border-color:hsl(220,20%,85%)"><?= esc($selected->alamat ?? '') ?></textarea>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div class="space-y-1.5">
                                        <label class="block text-[10px] font-semibold uppercase tracking-wider" style="color:hsl(220,15%,55%)">No. Telepon</label>
                                        <input type="text" name="no_hp" value="<?= esc($selected->no_hp ?? '') ?>" form="formPribadi" data-old-value="<?= esc($selected->no_hp ?? '') ?>"
                                            class="w-full px-3 py-2.5 border rounded-xl text-sm focus:outline-none" style="border-color:hsl(220,20%,85%)">
                                    </div>
                                    <div class="space-y-1.5">
                                        <label class="block text-[10px] font-semibold uppercase tracking-wider" style="color:hsl(220,15%,55%)">Email</label>
                                        <input type="email" name="email_siswa" value="<?= esc($selected->email_siswa ?? '') ?>" form="formPribadi" data-old-value="<?= esc($selected->email_siswa ?? '') ?>"
                                            class="w-full px-3 py-2.5 border rounded-xl text-sm focus:outline-none" style="border-color:hsl(220,20%,85%)">
                                    </div>
                                </div>
                            </div>
                            <div class="space-y-3">
                                <h4 class="font-bold text-sm font-serif" style="color:hsl(220,54%,15%)">Data Orang Tua / Wali</h4>
                                <div class="rounded-xl border p-3 space-y-3" style="border-color:hsl(220,20%,88%)">
                                    <p class="text-[10px] font-bold uppercase" style="color:hsl(220,54%,25%)">Ayah</p>
                                    <div class="space-y-1.5">
                                        <label class="block text-[10px] font-semibold uppercase tracking-wider" style="color:hsl(220,15%,55%)">Nama Ayah</label>
                                        <input type="text" name="nama_ayah" value="<?= esc($selected->nama_ayah ?? '') ?>" form="formPribadi" data-old-value="<?= esc($selected->nama_ayah ?? '') ?>"
                                            class="w-full px-3 py-2.5 border rounded-xl text-sm focus:outline-none" style="border-color:hsl(220,20%,85%)">
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <div class="space-y-1.5">
                                            <label class="block text-[10px] font-semibold uppercase tracking-wider" style="color:hsl(220,15%,55%)">Pekerjaan</label>
                                            <input type="text" name="pekerjaan_ayah" value="<?= esc($selected->pekerjaan_ayah ?? '') ?>" form="formPribadi" data-old-value="<?= esc($selected->pekerjaan_ayah ?? '') ?>"
                                                class="w-full px-3 py-2.5 border rounded-xl text-sm focus:outline-none" style="border-color:hsl(220,20%,85%)">
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="block text-[10px] font-semibold uppercase tracking-wider" style="color:hsl(220,15%,55%)">No. HP</label>
                                            <input type="text" name="no_hp_ayah" value="<?= esc($selected->no_hp_ayah ?? '') ?>" form="formPribadi" data-old-value="<?= esc($selected->no_hp_ayah ?? '') ?>"
                                                class="w-full px-3 py-2.5 border rounded-xl text-sm focus:outline-none" style="border-color:hsl(220,20%,85%)">
                                        </div>
                                    </div>
                                </div>
                                <div class="rounded-xl border p-3 space-y-3" style="border-color:hsl(220,20%,88%)">
                                    <p class="text-[10px] font-bold uppercase" style="color:hsl(220,54%,25%)">Ibu</p>
                                    <div class="space-y-1.5">
                                        <label class="block text-[10px] font-semibold uppercase tracking-wider" style="color:hsl(220,15%,55%)">Nama Ibu</label>
                                        <input type="text" name="nama_ibu" value="<?= esc($selected->nama_ibu ?? '') ?>" form="formPribadi" data-old-value="<?= esc($selected->nama_ibu ?? '') ?>"
                                            class="w-full px-3 py-2.5 border rounded-xl text-sm focus:outline-none" style="border-color:hsl(220,20%,85%)">
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <div class="space-y-1.5">
                                            <label class="block text-[10px] font-semibold uppercase tracking-wider" style="color:hsl(220,15%,55%)">Pekerjaan</label>
                                            <input type="text" name="pekerjaan_ibu" value="<?= esc($selected->pekerjaan_ibu ?? '') ?>" form="formPribadi" data-old-value="<?= esc($selected->pekerjaan_ibu ?? '') ?>"
                                                class="w-full px-3 py-2.5 border rounded-xl text-sm focus:outline-none" style="border-color:hsl(220,20%,85%)">
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="block text-[10px] font-semibold uppercase tracking-wider" style="color:hsl(220,15%,55%)">No. HP</label>
                                            <input type="text" name="no_hp_ibu" value="<?= esc($selected->no_hp_ibu ?? '') ?>" form="formPribadi" data-old-value="<?= esc($selected->no_hp_ibu ?? '') ?>"
                                                class="w-full px-3 py-2.5 border rounded-xl text-sm focus:outline-none" style="border-color:hsl(220,20%,85%)">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div><!-- /tab kontak -->

                    <!-- ══════════════════════ TAB: DATA KESEHATAN ══════════════════════ -->
                    <div x-show="tab==='kesehatan'" x-transition class="p-6">

                        <!-- banner -->
                        <div x-show="!editingHealth">
                            <div class="flex items-center justify-between gap-4 flex-wrap px-4 py-3 rounded-xl border"
                                style="background:hsl(142,71%,45%,0.06);border-color:hsl(142,71%,45%,0.25)">
                                <span class="text-sm" style="color:hsl(142,55%,30%)">Catatan medis siswa untuk keperluan UKS dan kesiapsiagaan darurat.</span>
                                <button type="button" @click="editingHealth=true"
                                    class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm font-semibold text-white"
                                    style="background:hsl(220,54%,20%)"
                                    onmouseover="this.style.background='hsl(220,54%,28%)'" onmouseout="this.style.background='hsl(220,54%,20%)'">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path d="m16.862 4.487 1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                                    </svg>
                                    Edit Data
                                </button>
                            </div>
                        </div>
                        <div x-show="editingHealth">
                            <div class="flex items-center justify-between gap-4 flex-wrap px-4 py-3 rounded-xl border"
                                style="background:hsl(38,92%,50%,0.06);border-color:hsl(38,92%,50%,0.35)">
                                <span class="text-sm font-medium" style="color:hsl(38,60%,30%)"><strong>Mode Edit Aktif.</strong>&nbsp;Perubahan dicatat di Riwayat Edit.</span>
                                <div class="flex gap-2">
                                    <button type="button" @click="confirmCancel('kesehatan')"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-semibold border"
                                        style="border-color:hsl(220,20%,82%);color:hsl(220,54%,20%)"
                                        onmouseover="this.style.background='hsl(220,20%,95%)'" onmouseout="this.style.background=''">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path d="M6 18L18 6M6 6l12 12" />
                                        </svg>Batal
                                    </button>
                                    <button type="button" @click="confirmSave('kesehatan')"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-semibold text-white"
                                        style="background:hsl(220,54%,20%)"
                                        onmouseover="this.style.background='hsl(220,54%,28%)'" onmouseout="this.style.background='hsl(220,54%,20%)'">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                                        </svg>Simpan
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- stat cards -->
                        <div class="mt-6 grid grid-cols-2 sm:grid-cols-3 gap-4 mb-6">
                            <?php
                            $hStatCards = [
                                ['Golongan Darah', esc($selected->golongan_darah ?? '-'), 'hsl(0,72%,51%)',   'hsl(0,72%,51%,0.1)',   'M19.5 13.5 12 21m0 0-7.5-7.5M12 21V3'],
                                ['Tinggi Badan',   ($selected->tinggi_badan ? $selected->tinggi_badan . ' cm' : '-'), 'hsl(199,60%,38%)', 'hsl(199,89%,48%,0.1)', 'M3 7.5L7.5 3m0 0L12 7.5M7.5 3v13.5m13.5 0L16.5 21m0 0L12 16.5m4.5 4.5V7.5'],
                                ['Berat Badan',    ($selected->berat_badan  ? $selected->berat_badan . ' kg'  : '-'), 'hsl(38,60%,40%)', 'hsl(38,92%,50%,0.1)',  'M12 3v17.25m0 0c-1.472 0-2.882.265-4.185.75M12 20.25c1.472 0 2.882.265 4.185.75M18.75 4.97A48.416 48.416 0 0012 4.5c-2.291 0-4.545.16-6.75.47m13.5 0c1.01.143 2.01.317 3 .52m-3-.52 2.62 10.726c.122.499-.106 1.028-.589 1.202a5.988 5.988 0 01-2.031.352 5.988 5.988 0 01-2.031-.352c-.483-.174-.711-.703-.59-1.202L18.75 4.971zm-16.5.52c.99-.203 1.99-.377 3-.52m0 0 2.62 10.726c.122.499-.106 1.028-.589 1.202a5.989 5.989 0 01-2.031.352 5.989 5.989 0 01-2.031-.352c-.483-.174-.711-.703-.59-1.202L5.25 4.971z'],
                            ];
                            foreach ($hStatCards as [$lbl, $val, $col, $bg, $path]):
                            ?>
                                <div class="rounded-xl border p-4 hover:shadow-md transition-shadow" style="border-color:hsl(220,20%,88%)">
                                    <div class="flex items-center gap-3">
                                        <div class="h-10 w-10 rounded-lg flex items-center justify-center shrink-0" style="background:<?= $bg ?>">
                                            <svg class="h-5 w-5" style="color:<?= $col ?>" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="<?= $path ?>" />
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-[10px] uppercase tracking-wider font-semibold" style="color:hsl(220,15%,55%)"><?= $lbl ?></p>
                                            <p class="font-bold text-lg mt-0.5" style="color:hsl(220,54%,15%)"><?= $val ?></p>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <?php if ($bmiVal): ?>
                            <div class="flex items-center gap-3 px-4 py-3 rounded-xl border mb-6 text-sm"
                                style="background:hsl(199,89%,48%,0.05);border-color:hsl(199,89%,48%,0.25)">
                                <svg class="h-4 w-4 shrink-0" style="color:hsl(199,60%,38%)" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z" />
                                </svg>
                                <span style="color:hsl(199,60%,30%)">
                                    <strong>Indeks Massa Tubuh (BMI):</strong> <?= $bmiVal ?> kg/m²
                                    <span class="ml-1 font-semibold" style="color:<?= $bmiCat[1] ?>">(<?= $bmiCat[0] ?>)</span>
                                    — perhitungan otomatis dari TB &amp; BB.
                                </span>
                            </div>
                        <?php endif; ?>

                        <div class="border-t mb-5" style="border-color:hsl(220,20%,91%)"></div>

                        <!-- VIEW kesehatan -->
                        <div x-show="!editingHealth" class="grid md:grid-cols-2 gap-6">
                            <div>
                                <div class="flex items-center gap-3 mb-3">
                                    <div class="h-9 w-9 rounded-lg flex items-center justify-center" style="background:hsl(0,72%,51%,0.1)">
                                        <svg class="h-5 w-5" style="color:hsl(0,55%,40%)" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />
                                        </svg>
                                    </div>
                                    <h3 class="font-bold font-serif text-sm" style="color:hsl(220,54%,15%)">Riwayat Medis</h3>
                                </div>
                                <div class="rounded-xl border p-4 min-h-[100px]" style="background:hsl(220,20%,97%);border-color:hsl(220,20%,90%)">
                                    <p class="text-sm whitespace-pre-wrap" style="color:hsl(220,54%,20%)"><?= esc($selected->riwayat_penyakit ?? 'Belum ada catatan riwayat penyakit.') ?></p>
                                </div>
                            </div>
                            <div>
                                <div class="flex items-center gap-3 mb-3">
                                    <div class="h-9 w-9 rounded-lg flex items-center justify-center" style="background:hsl(199,89%,48%,0.1)">
                                        <svg class="h-5 w-5" style="color:hsl(199,60%,38%)" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
                                        </svg>
                                    </div>
                                    <h3 class="font-bold font-serif text-sm" style="color:hsl(220,54%,15%)">Catatan Tambahan</h3>
                                </div>
                                <div class="rounded-xl border p-4 min-h-[100px]" style="background:hsl(220,20%,97%);border-color:hsl(220,20%,90%)">
                                    <p class="text-sm whitespace-pre-wrap" style="color:hsl(220,54%,20%)"><?= esc($selected->catatan_kesehatan ?? 'Tidak ada catatan tambahan.') ?></p>
                                </div>
                            </div>
                        </div>

                        <!-- EDIT kesehatan -->
                        <div x-show="editingHealth">
                            <form id="formKesehatan" action="<?= base_url('admin/buku-induk/' . $selected->id . '/kesehatan') ?>" method="POST">
                                <?= csrf_field() ?>
                                <div class="space-y-4 max-w-2xl">
                                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                                        <div class="space-y-1.5">
                                            <label class="block text-[10px] font-semibold uppercase tracking-wider" style="color:hsl(220,15%,55%)">Golongan Darah</label>
                                            <select name="golongan_darah" class="w-full px-3 py-2.5 border rounded-xl text-sm focus:outline-none bg-white" style="border-color:hsl(220,20%,85%)" data-old-value="<?= esc($selected->golongan_darah ?? '') ?>">
                                                <option value="">Pilih...</option>
                                                <?php foreach (['A', 'B', 'AB', 'O'] as $gd): ?>
                                                    <option value="<?= $gd ?>" <?= ($selected->golongan_darah ?? '') === $gd ? 'selected' : '' ?>><?= $gd ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="block text-[10px] font-semibold uppercase tracking-wider" style="color:hsl(220,15%,55%)">Tinggi Badan (cm)</label>
                                            <input type="number" name="tinggi_badan" value="<?= esc($selected->tinggi_badan ?? '') ?>" min="100" max="250" data-old-value="<?= esc($selected->tinggi_badan ?? '') ?>"
                                                class="w-full px-3 py-2.5 border rounded-xl text-sm focus:outline-none" style="border-color:hsl(220,20%,85%)">
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="block text-[10px] font-semibold uppercase tracking-wider" style="color:hsl(220,15%,55%)">Berat Badan (kg)</label>
                                            <input type="number" name="berat_badan" value="<?= esc($selected->berat_badan ?? '') ?>" min="20" max="200" data-old-value="<?= esc($selected->berat_badan ?? '') ?>"
                                                class="w-full px-3 py-2.5 border rounded-xl text-sm focus:outline-none" style="border-color:hsl(220,20%,85%)">
                                        </div>
                                    </div>
                                    <div class="space-y-1.5">
                                        <label class="block text-[10px] font-semibold uppercase tracking-wider" style="color:hsl(220,15%,55%)">Riwayat Penyakit</label>
                                        <textarea name="riwayat_penyakit" rows="3"
                                            placeholder="Tuliskan riwayat penyakit kronis, alergi, dll. Tulis 'Tidak Ada' jika tidak ada."
                                            class="w-full px-3 py-2.5 border rounded-xl text-sm focus:outline-none resize-none" style="border-color:hsl(220,20%,85%)"><?= esc($selected->riwayat_penyakit ?? '') ?></textarea>
                                    </div>
                                    <div class="space-y-1.5">
                                        <label class="block text-[10px] font-semibold uppercase tracking-wider" style="color:hsl(220,15%,55%)">Keterangan Tambahan</label>
                                        <textarea name="catatan_kesehatan" rows="3"
                                            placeholder="Contoh: memakai kacamata minus, alat bantu dengar, dll."
                                            class="w-full px-3 py-2.5 border rounded-xl text-sm focus:outline-none resize-none" style="border-color:hsl(220,20%,85%)"><?= esc($selected->catatan_kesehatan ?? '') ?></textarea>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div><!-- /tab kesehatan -->

                    <!-- ══════════════════════ TAB: PENEMPATAN KELAS ══════════════════════ -->
                    <div x-show="tab==='kelas'" x-transition class="p-6">

                        <!-- banner -->
                        <div x-show="!editingClass">
                            <div class="flex items-center justify-between gap-4 flex-wrap px-4 py-3 rounded-xl border"
                                style="background:hsl(142,71%,45%,0.06);border-color:hsl(142,71%,45%,0.25)">
                                <span class="text-sm" style="color:hsl(142,55%,30%)">Penempatan kelas, jurusan, dan wali kelas untuk tahun ajaran berjalan.</span>
                                <button type="button" @click="editingClass=true"
                                    class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm font-semibold text-white"
                                    style="background:hsl(220,54%,20%)"
                                    onmouseover="this.style.background='hsl(220,54%,28%)'" onmouseout="this.style.background='hsl(220,54%,20%)'">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path d="m16.862 4.487 1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                                    </svg>
                                    Edit Data
                                </button>
                            </div>
                        </div>
                        <div x-show="editingClass">
                            <div class="flex items-center justify-between gap-4 flex-wrap px-4 py-3 rounded-xl border"
                                style="background:hsl(38,92%,50%,0.06);border-color:hsl(38,92%,50%,0.35)">
                                <span class="text-sm font-medium" style="color:hsl(38,60%,30%)"><strong>Mode Edit Aktif.</strong>&nbsp;Perubahan dicatat di Riwayat Edit.</span>
                                <div class="flex gap-2">
                                    <button type="button" @click="confirmCancel('kelas')"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-semibold border"
                                        style="border-color:hsl(220,20%,82%);color:hsl(220,54%,20%)"
                                        onmouseover="this.style.background='hsl(220,20%,95%)'" onmouseout="this.style.background=''">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path d="M6 18L18 6M6 6l12 12" />
                                        </svg>Batal
                                    </button>
                                    <button type="button" @click="confirmSave('kelas')"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-semibold text-white"
                                        style="background:hsl(220,54%,20%)"
                                        onmouseover="this.style.background='hsl(220,54%,28%)'" onmouseout="this.style.background='hsl(220,54%,20%)'">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                                        </svg>Simpan
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- VIEW kelas -->
                        <div x-show="!editingClass" class="mt-6 space-y-6">
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                <?php
                                $kCards = [
                                    ['Tahun Ajaran', esc(($selected->tahun_masuk ?? '-') . '/' . (($selected->tahun_masuk ?? 0) + 1)), 'hsl(220,54%,25%)', 'hsl(220,54%,20%,0.08)', 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25'],
                                    ['Tingkat',     'Kelas ' . esc($selected->kelas_tingkat ?? 'X'), 'hsl(43,60%,35%)', 'hsl(43,70%,47%,0.1)', 'M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25'],
                                    ['Kelas',       esc($selected->kelas_nama ?? '-'),             'hsl(142,55%,30%)', 'hsl(142,71%,45%,0.1)', 'M4.26 10.147a60.438 60.438 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.906 59.906 0 0112 3.493a59.903 59.903 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0112 13.489a50.702 50.702 0 017.74-3.342'],
                                    ['Wali Kelas',  esc($selected->wali_kelas ?? '-'),             'hsl(199,60%,35%)', 'hsl(199,89%,48%,0.1)', 'M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z'],
                                ];
                                foreach ($kCards as [$lbl, $val, $col, $bg, $path]):
                                ?>
                                    <div class="rounded-xl border p-4 hover:shadow-md transition-shadow" style="border-color:hsl(220,20%,88%)">
                                        <div class="flex items-center gap-3">
                                            <div class="h-10 w-10 rounded-lg flex items-center justify-center shrink-0" style="background:<?= $bg ?>">
                                                <svg class="h-5 w-5" style="color:<?= $col ?>" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="<?= $path ?>" />
                                                </svg>
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p class="text-[10px] uppercase tracking-wider font-semibold" style="color:hsl(220,15%,55%)"><?= $lbl ?></p>
                                                <p class="font-bold text-sm mt-0.5 truncate" style="color:hsl(220,54%,15%)"><?= $val ?></p>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="flex items-center gap-3 px-4 py-3 rounded-xl border text-sm"
                                style="background:hsl(220,54%,20%,0.04);border-color:hsl(220,54%,20%,0.2)">
                                <svg class="h-4 w-4 shrink-0" style="color:hsl(220,54%,30%)" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25" />
                                </svg>
                                <span style="color:hsl(220,40%,30%)">
                                    Tahun masuk: <strong><?= esc($selected->tahun_masuk ?? '-') ?></strong>.
                                    Penempatan ini tercatat permanen di Buku Induk dan menjadi acuan rapor serta ijazah.
                                </span>
                            </div>
                        </div>

                        <!-- EDIT kelas -->
                        <div x-show="editingClass" class="mt-6">
                            <form id="formKelas" action="<?= base_url('admin/buku-induk/' . $selected->id . '/kelas') ?>" method="POST">
                                <?= csrf_field() ?>
                                <div class="grid md:grid-cols-2 gap-5 max-w-2xl">
                                    <div class="space-y-1.5">
                                        <label class="block text-[10px] font-semibold uppercase tracking-wider" style="color:hsl(220,15%,55%)">Tahun Masuk</label>
                                        <select name="tahun_masuk" class="w-full px-3 py-2.5 border rounded-xl text-sm focus:outline-none bg-white" style="border-color:hsl(220,20%,85%)" data-old-value="<?= esc($selected->tahun_masuk ?? '') ?>">
                                            <?php
                                            $tNow = (int)date('Y');
                                            for ($t = $tNow; $t >= $tNow - 3; $t--):
                                            ?>
                                                <option value="<?= $t ?>" <?= ($selected->tahun_masuk ?? '') == $t ? 'selected' : '' ?>><?= $t ?>/<?= $t + 1 ?></option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>
                                    <div class="space-y-1.5">
                                        <label class="block text-[10px] font-semibold uppercase tracking-wider" style="color:hsl(220,15%,55%)">Kelas</label>
                                        <select name="kelas_id" class="w-full px-3 py-2.5 border rounded-xl text-sm focus:outline-none bg-white" style="border-color:hsl(220,20%,85%)" data-old-value="<?= esc($selected->kelas_id ?? '') ?>">
                                            <option value="">-- Pilih Kelas --</option>
                                            <?php foreach ($kelasList as $k): ?>
                                                <option value="<?= $k->id ?>" <?= ($selected->kelas_id ?? '') == $k->id ? 'selected' : '' ?>>
                                                    <?= esc($k->nama) ?><?= $k->wali_kelas ? ' (Wali: ' . esc($k->wali_kelas) . ')' : '' ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div><!-- /tab kelas -->

                </div><!-- /tabs card -->
            </div><!-- /right -->

        <?php else: ?>
            <!-- empty state -->
            <div class="lg:col-span-8 xl:col-span-9 bg-white rounded-2xl border flex flex-col items-center justify-center py-24 text-center" style="border-color:hsl(220,20%,88%)">
                <div class="h-20 w-20 rounded-2xl flex items-center justify-center mb-4" style="background:hsl(220,20%,93%)">
                    <svg class="h-10 w-10" style="color:hsl(220,20%,72%)" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                    </svg>
                </div>
                <p class="font-semibold text-lg font-serif" style="color:hsl(220,20%,55%)">Pilih siswa dari daftar</p>
                <p class="text-sm mt-1" style="color:hsl(220,15%,65%)">Klik nama siswa di panel kiri untuk melihat detail</p>
            </div>
        <?php endif; ?>

    </div><!-- /grid -->

    <!-- ══ MODAL: Konfirmasi Simpan ══ -->
    <div x-show="showSaveConfirm" x-transition.opacity
        class="fixed inset-0 z-50 flex items-end sm:items-center justify-center sm:p-4" style="background:rgba(0,0,0,.5)"
        @click.self="showSaveConfirm=null">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6" @click.stop>
            <div class="flex items-center gap-3 mb-4">
                <div class="h-10 w-10 rounded-xl flex items-center justify-center" style="background:hsl(38,92%,50%,0.12)">
                    <svg class="h-5 w-5" style="color:hsl(38,60%,40%)" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126z" />
                    </svg>
                </div>
                <h3 class="font-bold text-base font-serif" style="color:hsl(220,54%,15%)">Konfirmasi Simpan Perubahan</h3>
            </div>
            <p class="text-sm mb-1" style="color:hsl(220,15%,40%)">Anda akan memperbarui <strong x-text="sectionLabel(showSaveConfirm)"></strong> siswa <strong><?= esc($selected->nama_lengkap ?? '') ?></strong>.</p>
            <p class="text-xs mb-6" style="color:hsl(220,15%,55%)">Setiap perubahan akan tercatat dalam Riwayat Edit dengan timestamp &amp; nama admin.</p>
            <div class="flex flex-col-reverse sm:flex-row gap-3 sm:justify-end">
                <button @click="showSaveConfirm=null" class="px-4 py-2 rounded-xl text-sm font-semibold border" style="border-color:hsl(220,20%,82%);color:hsl(220,54%,20%)" onmouseover="this.style.background='hsl(220,20%,95%)'" onmouseout="this.style.background=''">Batal</button>
                <button @click="doSave()" class="px-4 py-2 rounded-xl text-sm font-semibold text-white" style="background:hsl(220,54%,20%)" onmouseover="this.style.background='hsl(220,54%,28%)'" onmouseout="this.style.background='hsl(220,54%,20%)'">Ya, Simpan</button>
            </div>
        </div>
    </div>

    <!-- ══ MODAL: Konfirmasi Batal ══ -->
    <div x-show="showCancelConfirm" x-transition.opacity
        class="fixed inset-0 z-50 flex items-end sm:items-center justify-center sm:p-4" style="background:rgba(0,0,0,.5)"
        @click.self="showCancelConfirm=null">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6" @click.stop>
            <h3 class="font-bold text-base font-serif mb-2" style="color:hsl(220,54%,15%)">Batalkan Perubahan?</h3>
            <p class="text-sm mb-6" style="color:hsl(220,15%,40%)">Semua perubahan yang belum disimpan akan hilang. Apakah Anda yakin?</p>
            <div class="flex flex-col-reverse sm:flex-row gap-3 sm:justify-end">
                <button @click="showCancelConfirm=null" class="px-4 py-2 rounded-xl text-sm font-semibold border" style="border-color:hsl(220,20%,82%);color:hsl(220,54%,20%)" onmouseover="this.style.background='hsl(220,20%,95%)'" onmouseout="this.style.background=''">Lanjutkan Edit</button>
                <button @click="doCancel()" class="px-4 py-2 rounded-xl text-sm font-semibold text-white" style="background:hsl(0,72%,51%)">Ya, Batalkan</button>
            </div>
        </div>
    </div>

    <!-- ══ MODAL: Riwayat Edit ══ -->
    <div x-show="showLogDialog" x-transition.opacity
        class="fixed inset-0 z-50 flex items-end sm:items-center justify-center sm:p-4" style="background:rgba(0,0,0,.5)"
        @click.self="showLogDialog=false">
        <div class="bg-white rounded-t-2xl sm:rounded-2xl shadow-2xl w-full sm:max-w-2xl max-h-[90vh] flex flex-col" @click.stop>
            <div class="flex items-center justify-between px-6 py-4 border-b shrink-0" style="border-color:hsl(220,20%,91%)">
                <h3 class="font-bold text-base font-serif flex items-center gap-2" style="color:hsl(220,54%,15%)">
                    <svg class="h-5 w-5" style="color:hsl(220,54%,30%)" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Riwayat Edit Buku Induk
                </h3>
                <button @click="showLogDialog=false" class="rounded-lg p-1.5 transition-colors" style="color:hsl(220,15%,50%)"
                    onmouseover="this.style.background='hsl(220,20%,93%)'" onmouseout="this.style.background=''">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <p class="px-6 py-2 text-sm border-b shrink-0" style="border-color:hsl(220,20%,91%);color:hsl(220,15%,50%)">
                Log perubahan data siswa <strong style="color:hsl(220,54%,15%)"><?= esc($selected->nama_lengkap ?? '') ?></strong>
            </p>
            <div class="flex-1 overflow-y-auto px-6 py-4 space-y-2">
                <template x-if="editLogs.length===0">
                    <div class="text-center py-12" style="color:hsl(220,15%,60%)">
                        <svg class="h-12 w-12 mx-auto mb-3 opacity-30" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="font-medium">Belum ada riwayat perubahan</p>
                        <p class="text-xs mt-1">Perubahan akan muncul di sini setelah Anda menyimpan data</p>
                    </div>
                </template>
                <template x-for="(log,i) in editLogs" :key="i">
                    <div class="border rounded-xl p-3 hover:bg-gray-50 transition-colors" style="border-color:hsl(220,20%,90%)">
                        <div class="flex items-center justify-between mb-2 flex-wrap gap-2">
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 border rounded-full text-[10px] font-medium" style="border-color:hsl(220,20%,80%);color:hsl(220,15%,45%)" x-text="log.section"></span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-medium" style="background:hsl(220,20%,93%);color:hsl(220,54%,20%)" x-text="log.fieldLabel"></span>
                            </div>
                            <span class="text-[10px]" style="color:hsl(220,15%,55%)" x-text="log.editedAt+' • '+log.editedBy"></span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                            <div>
                                <p class="text-[10px] uppercase tracking-wider mb-1" style="color:hsl(220,15%,55%)">Sebelum</p>
                                <p class="line-through px-2 py-1 rounded break-words" style="background:hsl(0,72%,51%,0.06);color:hsl(0,55%,40%)" x-text="log.oldValue||'(kosong)'"></p>
                            </div>
                            <div>
                                <p class="text-[10px] uppercase tracking-wider mb-1" style="color:hsl(220,15%,55%)">Sesudah</p>
                                <p class="font-medium px-2 py-1 rounded break-words" style="background:hsl(142,71%,45%,0.06);color:hsl(142,55%,30%)" x-text="log.newValue||'(kosong)'"></p>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
            <div class="px-6 py-4 border-t shrink-0 flex flex-col-reverse sm:flex-row sm:justify-end gap-3" style="border-color:hsl(220,20%,91%)">
                <button @click="showLogDialog=false" class="px-4 py-2 rounded-xl text-sm font-semibold border" style="border-color:hsl(220,20%,82%);color:hsl(220,54%,20%)" onmouseover="this.style.background='hsl(220,20%,95%)'" onmouseout="this.style.background=''">Tutup</button>
            </div>
        </div>
    </div>

    <!-- ══ TOAST ══ -->
    <div x-show="toast.show" x-transition.opacity
        class="fixed bottom-5 right-5 z-50 flex items-center gap-3 px-4 py-3 rounded-xl shadow-xl text-sm font-medium text-white"
        :style="'background:'+(toast.type==='success'?'hsl(142,71%,45%)':toast.type==='warning'?'hsl(38,92%,50%)':'hsl(0,72%,51%)')">
        <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
            <path x-show="toast.type==='success'" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            <path x-show="toast.type!=='success'" d="M12 9v3.75m9-4.93a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span x-text="toast.message"></span>
    </div>

</div><!-- /x-data -->

<script>
    function bukuIndukPage() {
        return {
            editingPribadi: false,
            editingHealth: false,
            editingClass: false,
            showSaveConfirm: null,
            showCancelConfirm: null,
            showLogDialog: false,
            editLogs: <?= json_encode($editLogs ?? []) ?>,
            toast: {
                show: false,
                message: '',
                type: 'success'
            },

            sectionLabel(s) {
                return {
                    pribadi: 'Data Pribadi',
                    kesehatan: 'Data Kesehatan',
                    kelas: 'Penempatan Kelas'
                } [s] ?? s;
            },
            showToast(msg, type = 'success') {
                this.toast = {
                    show: true,
                    message: msg,
                    type
                };
                setTimeout(() => this.toast.show = false, 3500);
            },
            confirmCancel(s) {
                this.showCancelConfirm = s;
            },
            confirmSave(s) {
                this.showSaveConfirm = s;
            },

            doCancel() {
                const s = this.showCancelConfirm;
                if (s === 'pribadi') this.editingPribadi = false;
                if (s === 'kesehatan') this.editingHealth = false;
                if (s === 'kelas') this.editingClass = false;
                this.showCancelConfirm = null;
            },
            doSave() {
                const s = this.showSaveConfirm;
                this.showSaveConfirm = null;
                const map = {
                    pribadi: 'formPribadi',
                    kesehatan: 'formKesehatan',
                    kelas: 'formKelas'
                };
                this.submitForm(map[s], s);
            },

            async submitForm(formId, section) {
                const form = document.getElementById(formId);
                if (!form) {
                    this.showToast('Form tidak ditemukan', 'error');
                    return;
                }

                const fd = new FormData(form);
                const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

                try {
                    const res = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: fd,
                    });
                    const data = await res.json().catch(() => ({
                        success: false,
                        message: 'Server error'
                    }));

                    if (data.success) {
                        // Update editLogs dari server (data ter-akurat)
                        if (data.data && Array.isArray(data.data.logs)) {
                            this.editLogs = data.data.logs;
                        }
                        if (section === 'pribadi') this.editingPribadi = false;
                        if (section === 'kesehatan') this.editingHealth = false;
                        if (section === 'kelas') this.editingClass = false;
                        this.showToast('Berhasil menyimpan ' + this.sectionLabel(section));
                        // Reload halaman agar nilai tampilan terupdate
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        this.showToast(data.message ?? 'Gagal menyimpan, coba lagi.', 'error');
                    }
                } catch (err) {
                    // fallback: submit biasa
                    form.submit();
                }
            },
        };
    }
</script>