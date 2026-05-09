<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bukti Pendaftaran <?= esc($pendaftaran->no_pendaftaran) ?> — SMK Al-Munawwir IIBS</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', 'Plus Jakarta Sans', sans-serif;
            font-size: 13px;
            color: #1a1f36;
            background: #f1f5f9;
            padding: 24px 16px 40px;
        }

        /* ── ACTION BAR ── */
        .action-bar {
            max-width: 860px;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 9px 18px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            text-decoration: none;
            transition: background 0.15s, box-shadow 0.15s;
            line-height: 1;
        }

        .btn-ghost {
            background: white;
            color: #374151;
            border: 1px solid #e5e7eb;
            box-shadow: 0 1px 2px rgba(0,0,0,.05);
        }

        .btn-ghost:hover { background: #f9fafb; }

        .btn-outline {
            background: white;
            color: #374151;
            border: 1px solid #d1d5db;
            box-shadow: 0 1px 2px rgba(0,0,0,.05);
        }

        .btn-outline:hover { background: #f3f4f6; }

        .btn-primary {
            background: hsl(220,54%,20%);
            color: white;
            box-shadow: 0 1px 3px rgba(0,0,0,.15);
        }

        .btn-primary:hover { background: hsl(220,54%,28%); }

        .btn-group { display: flex; gap: 10px; flex-wrap: wrap; }

        /* ── PAPER CARD ── */
        .paper {
            max-width: 860px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,.10), 0 1px 3px rgba(0,0,0,.06);
            overflow: hidden;
            border: 1px solid #e5e7eb;
        }

        /* ── HEADER BAND ── */
        .paper-header {
            background: hsl(220,54%,20%);
            padding: 22px 28px;
            display: flex;
            align-items: center;
            gap: 18px;
        }

        .paper-header img {
            width: 64px;
            height: 64px;
            object-fit: contain;
            background: white;
            border-radius: 10px;
            padding: 4px;
            flex-shrink: 0;
        }

        .paper-header-text h1 {
            font-size: 17px;
            font-weight: 800;
            color: hsl(45,70%,95%);
            letter-spacing: .3px;
        }

        .paper-header-text p {
            font-size: 12px;
            color: rgba(255,255,255,.75);
            margin-top: 3px;
        }

        .paper-header-text p.addr {
            font-size: 11px;
            color: rgba(255,255,255,.55);
            margin-top: 2px;
        }

        /* ── TITLE ROW ── */
        .paper-title {
            text-align: center;
            padding: 22px 28px 18px;
            border-bottom: 1px solid #e5e7eb;
        }

        .paper-title h2 {
            font-size: 20px;
            font-weight: 800;
            color: #111827;
            letter-spacing: .5px;
        }

        .paper-title p {
            font-size: 12px;
            color: #6b7280;
            margin-top: 4px;
        }

        /* ── BODY ── */
        .paper-body {
            padding: 24px 28px;
        }

        /* Status badge */
        .status-wrap {
            display: flex;
            justify-content: center;
            margin-bottom: 18px;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 8px 20px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: .4px;
        }

        .status-badge.verified {
            background: rgba(16,185,129,.10);
            color: #065f46;
            border: 1px solid rgba(16,185,129,.25);
        }

        .status-badge.pending {
            background: rgba(245,158,11,.10);
            color: #92400e;
            border: 1px solid rgba(245,158,11,.25);
        }

        .status-badge svg {
            width: 16px;
            height: 16px;
            flex-shrink: 0;
        }

        /* No pendaftaran box */
        .no-pendaftaran-box {
            text-align: center;
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 14px 20px;
            margin-bottom: 22px;
        }

        .no-pendaftaran-box .label {
            font-size: 11px;
            color: #9ca3af;
            margin-bottom: 4px;
        }

        .no-pendaftaran-box .value {
            font-size: 22px;
            font-weight: 800;
            font-family: 'Courier New', monospace;
            color: #111827;
            letter-spacing: 2px;
        }

        /* Two-column grid */
        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
            margin-bottom: 18px;
        }

        @media (max-width: 600px) {
            .grid-2 { grid-template-columns: 1fr; }
        }

        /* Section card */
        .section {
            margin-bottom: 18px;
        }

        .section-title {
            display: flex;
            align-items: center;
            gap: 7px;
            font-size: 12px;
            font-weight: 700;
            color: #374151;
            padding-bottom: 8px;
            border-bottom: 1px solid #e5e7eb;
            margin-bottom: 12px;
        }

        .section-title svg {
            width: 15px;
            height: 15px;
            color: hsl(220,54%,30%);
            flex-shrink: 0;
        }

        /* Data rows */
        .data-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
            padding: 5px 0;
            font-size: 12px;
            border-bottom: 1px solid #f3f4f6;
        }

        .data-row:last-child { border-bottom: none; }

        .data-row .key {
            color: #6b7280;
            flex-shrink: 0;
            min-width: 120px;
        }

        .data-row .val {
            font-weight: 600;
            color: #111827;
            text-align: right;
        }

        .val.accepted { color: #065f46; }

        /* Timeline box */
        .timeline-box {
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 14px 18px;
            margin-bottom: 18px;
        }

        .timeline-box .section-title { margin-bottom: 10px; }

        .timeline-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        @media (max-width: 500px) {
            .timeline-grid { grid-template-columns: 1fr; }
        }

        .timeline-item .t-label {
            font-size: 11px;
            color: #9ca3af;
        }

        .timeline-item .t-val {
            font-size: 12px;
            font-weight: 600;
            color: #111827;
            margin-top: 2px;
        }

        /* QR + Signature row */
        .bottom-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
            padding-top: 18px;
            border-top: 1px solid #e5e7eb;
        }

        @media (max-width: 500px) {
            .bottom-row { grid-template-columns: 1fr; }
        }

        .qr-wrap {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
        }

        .qr-box {
            width: 110px;
            height: 110px;
            border: 2px dashed #d1d5db;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f9fafb;
        }

        .qr-box svg {
            width: 64px;
            height: 64px;
            color: #9ca3af;
        }

        .qr-label {
            font-size: 10px;
            color: #9ca3af;
            text-align: center;
            line-height: 1.5;
        }

        .qr-label .mono {
            font-family: 'Courier New', monospace;
            font-weight: 600;
        }

        /* Signature */
        .signature-wrap {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-end;
            text-align: center;
        }

        .signature-label {
            font-size: 11px;
            color: #9ca3af;
            margin-bottom: 10px;
        }

        .signature-name {
            font-size: 18px;
            font-style: italic;
            color: hsl(220,54%,20%);
            font-family: Georgia, serif;
            border-bottom: 2px solid hsl(220,54%,20%);
            padding-bottom: 2px;
            min-width: 140px;
            text-align: center;
        }

        .signature-title {
            font-size: 12px;
            font-weight: 600;
            color: #111827;
            margin-top: 6px;
        }

        .signature-sub {
            font-size: 10px;
            color: #9ca3af;
            margin-top: 2px;
        }

        /* Paper footer */
        .paper-footer {
            text-align: center;
            font-size: 10px;
            color: #9ca3af;
            padding: 16px 28px 20px;
            border-top: 1px solid #e5e7eb;
            line-height: 1.7;
        }

        /* Info tip */
        .info-tip {
            max-width: 860px;
            margin: 16px auto 0;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 10px;
            padding: 12px 18px;
            font-size: 12px;
            color: #1e40af;
        }

        /* ══ PRINT STYLES ══ */
        @media print {
            body {
                background: white;
                padding: 0;
            }

            .action-bar,
            .info-tip { display: none !important; }

            .paper {
                border: none;
                box-shadow: none;
                border-radius: 0;
                max-width: 100%;
            }

            .paper-header {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .status-badge {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>

<body>

    <?php
    // ── Helpers ──────────────────────────────────────────────
    $status        = $pendaftaran->status ?? 'draft';
    $statusLabel   = [
        'draft'       => 'Draft',
        'submitted'   => 'Diajukan',
        'verified'    => 'Terverifikasi',
        'accepted'    => 'Diterima',
        'rejected'    => 'Ditolak',
        'daftar_ulang'=> 'Daftar Ulang',
        'siswa_aktif' => 'Siswa Aktif',
    ][$status] ?? ucwords(str_replace('_', ' ', $status));

    $isVerified = in_array($status, ['verified', 'accepted', 'daftar_ulang', 'siswa_aktif']);

    $verifiedBy   = $pendaftaran->verifikasi_oleh_nama ?? 'Admin TU';
    $submittedAt  = $pendaftaran->submitted_at ?? $pendaftaran->created_at ?? null;
    $verifiedAt   = $pendaftaran->verified_at  ?? null;
    ?>

    <!-- ══ ACTION BAR ══ -->
    <div class="action-bar no-print">
        <a href="<?= base_url('dashboard/status') ?>" class="btn btn-ghost">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="15" height="15">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Status
        </a>
        <div class="btn-group">
            <button onclick="window.print()" class="btn btn-outline">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="15" height="15">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2v-5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Unduh PDF
            </button>
            <button onclick="window.print()" class="btn btn-primary">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="15" height="15">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Cetak
            </button>
        </div>
    </div>

    <!-- ══ PAPER ══ -->
    <div class="paper">

        <!-- Header Band -->
        <div class="paper-header">
            <img src="<?= base_url('assets/logo/logo-smk.png') ?>" alt="Logo SMK Al-Munawwir IIBS">
            <div class="paper-header-text">
                <h1>SMK AL-MUNAWWIR IIBS</h1>
                <p>International Islamic Boarding School</p>
                <p class="addr">Jl. Kedungliwung No.35, Kemiri, Singojuruh, Banyuwangi, Jawa Timur</p>
            </div>
        </div>

        <!-- Title Row -->
        <div class="paper-title">
            <h2>BUKTI PENDAFTARAN</h2>
            <p>Sistem Penerimaan Murid Baru (SPMB) Tahun Ajaran <?= esc($periode->tahun_ajaran ?? '2026/2027') ?></p>
        </div>

        <!-- Body -->
        <div class="paper-body">

            <!-- Status Badge -->
            <div class="status-wrap">
                <span class="status-badge <?= $isVerified ? 'verified' : 'pending' ?>">
                    <?php if ($isVerified): ?>
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    <?php else: ?>
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    <?php endif; ?>
                    STATUS: <?= strtoupper($statusLabel) ?>
                </span>
            </div>

            <!-- No Pendaftaran -->
            <div class="no-pendaftaran-box">
                <div class="label">Nomor Pendaftaran</div>
                <div class="value"><?= esc($pendaftaran->no_pendaftaran) ?></div>
            </div>

            <!-- Data Pribadi + Kontak -->
            <div class="grid-2">

                <!-- Data Pribadi -->
                <div class="section">
                    <div class="section-title">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Data Pribadi
                    </div>
                    <div class="data-row">
                        <span class="key">Nama Lengkap</span>
                        <span class="val"><?= esc($dataDiri->nama_lengkap ?? '-') ?></span>
                    </div>
                    <div class="data-row">
                        <span class="key">NIK</span>
                        <span class="val"><?= esc($dataDiri->nik ?? '-') ?></span>
                    </div>
                    <div class="data-row">
                        <span class="key">NISN</span>
                        <span class="val"><?= esc($dataDiri->nisn ?? '-') ?></span>
                    </div>
                    <div class="data-row">
                        <span class="key">Tempat, Tgl Lahir</span>
                        <span class="val"><?= esc($dataDiri->tempat_lahir ?? '-') ?>, <?= format_tanggal($dataDiri->tanggal_lahir ?? null) ?></span>
                    </div>
                    <div class="data-row">
                        <span class="key">Jenis Kelamin</span>
                        <span class="val"><?= ($dataDiri->jenis_kelamin ?? '') === 'L' ? 'Laki-laki' : 'Perempuan' ?></span>
                    </div>
                    <div class="data-row">
                        <span class="key">Agama</span>
                        <span class="val"><?= esc($dataDiri->agama ?? '-') ?></span>
                    </div>
                </div>

                <!-- Kontak & Alamat -->
                <div class="section">
                    <div class="section-title">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        Kontak &amp; Alamat
                    </div>
                    <div class="data-row">
                        <span class="key">Alamat</span>
                        <span class="val"><?= esc($dataDiri->alamat ?? '-') ?></span>
                    </div>
                    <div class="data-row">
                        <span class="key">No. HP</span>
                        <span class="val"><?= esc($dataDiri->no_hp ?? '-') ?></span>
                    </div>
                    <div class="data-row">
                        <span class="key">Email</span>
                        <span class="val"><?= esc($user->email ?? '-') ?></span>
                    </div>
                    <div class="data-row">
                        <span class="key">No. HP Ortu</span>
                        <span class="val"><?= esc($dataDiri->no_hp_ortu ?? '-') ?></span>
                    </div>
                    <div class="data-row">
                        <span class="key">Nama Ayah</span>
                        <span class="val"><?= esc($dataDiri->nama_ayah ?? '-') ?></span>
                    </div>
                    <div class="data-row">
                        <span class="key">Nama Ibu</span>
                        <span class="val"><?= esc($dataDiri->nama_ibu ?? '-') ?></span>
                    </div>
                </div>
            </div>

            <!-- Asal Sekolah + Pilihan Jurusan -->
            <div class="grid-2">

                <!-- Asal Sekolah -->
                <div class="section">
                    <div class="section-title">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/>
                        </svg>
                        Asal Sekolah
                    </div>
                    <div class="data-row">
                        <span class="key">Nama Sekolah</span>
                        <span class="val"><?= esc($dataDiri->asal_sekolah ?? '-') ?></span>
                    </div>
                    <div class="data-row">
                        <span class="key">Tahun Lulus</span>
                        <span class="val"><?= esc($dataDiri->tahun_lulus ?? '-') ?></span>
                    </div>
                </div>

                <!-- Pilihan Jurusan -->
                <div class="section">
                    <div class="section-title">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                        </svg>
                        Pilihan Jurusan
                    </div>
                    <div class="data-row">
                        <span class="key">Pilihan 1</span>
                        <span class="val"><?= esc($pendaftaran->jurusan_pilihan1_nama ?? '-') ?></span>
                    </div>
                    <div class="data-row">
                        <span class="key">Pilihan 2</span>
                        <span class="val"><?= esc($pendaftaran->jurusan_pilihan2_nama ?? '-') ?></span>
                    </div>
                    <?php if (!empty($pendaftaran->jurusan_diterima_nama)): ?>
                    <div class="data-row">
                        <span class="key">Jurusan Diterima</span>
                        <span class="val accepted"><?= esc($pendaftaran->jurusan_diterima_nama) ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Riwayat Proses -->
            <div class="timeline-box">
                <div class="section-title">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="15" height="15">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Riwayat Proses
                </div>
                <div class="timeline-grid">
                    <div class="timeline-item">
                        <div class="t-label">Tanggal Submit:</div>
                        <div class="t-val">
                            <?= $submittedAt ? date('d F Y, H:i', strtotime($submittedAt)) . ' WIB' : '-' ?>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="t-label">Tanggal Verifikasi:</div>
                        <div class="t-val">
                            <?= $verifiedAt ? date('d F Y, H:i', strtotime($verifiedAt)) . ' WIB' : '-' ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- QR + Tanda Tangan -->
            <div class="bottom-row">

                <!-- QR Code -->
                <div class="qr-wrap">
                    <div class="qr-box">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4H4v8h8V4zM20 4h-8v8h8V4zM12 12H4v8h8v-8zM20 16v4M16 20h4M16 12v4M20 12h-4"/>
                        </svg>
                    </div>
                    <div class="qr-label">
                        Scan untuk verifikasi<br>
                        <span class="mono"><?= esc($pendaftaran->no_pendaftaran) ?></span>
                    </div>
                </div>

                <!-- Tanda Tangan Digital -->
                <div class="signature-wrap">
                    <div class="signature-label">Diverifikasi oleh:</div>
                    <div class="signature-name"><?= esc($verifiedBy) ?></div>
                    <div class="signature-title"><?= esc($pendaftaran->verifikasi_oleh_jabatan ?? 'Admin TU') ?> - <?= esc($verifiedBy) ?></div>
                    <div class="signature-sub">Tanda Tangan Digital</div>
                </div>
            </div>

        </div><!-- /paper-body -->

        <!-- Paper Footer -->
        <div class="paper-footer">
            <p>Dokumen ini dicetak secara otomatis oleh sistem SPMB SMK Al-Munawwir IIBS.</p>
            <p>Berlaku sebagai bukti pendaftaran yang sah.</p>
            <p style="margin-top:6px;">
                Dicetak pada:
                <?php
                    $hari = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
                    $bulan = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
                    echo $hari[date('w')] . ', ' . date('j') . ' ' . $bulan[(int)date('n')] . ' ' . date('Y') . ' pukul ' . date('H:i') . ' WIB';
                ?>
            </p>
        </div>

    </div><!-- /paper -->

    <!-- Info Tip -->
    <div class="info-tip no-print">
        <strong>Tips:</strong> Untuk hasil cetak terbaik, gunakan kertas A4 dan aktifkan opsi <em>"Print backgrounds"</em> pada pengaturan printer Anda.
    </div>

</body>

</html>