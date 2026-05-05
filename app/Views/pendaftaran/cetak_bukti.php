<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Bukti Pendaftaran <?= esc($pendaftaran->no_pendaftaran) ?></title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', sans-serif;
            font-size: 12px;
            color: #1a202c;
            margin: 0;
            padding: 20px;
            background: white;
        }

        .header {
            border-bottom: 3px solid #1d4ed8;
            padding-bottom: 12px;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .logo-circle {
            width: 60px;
            height: 60px;
            background: #1d4ed8;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 22px;
            font-weight: bold;
            flex-shrink: 0;
        }

        h1 {
            font-size: 16px;
            font-weight: 700;
            color: #1d4ed8;
            margin: 0;
        }

        h2 {
            font-size: 11px;
            font-weight: 500;
            color: #4b5563;
            margin: 2px 0 0;
        }

        .badge-no {
            background: #1d4ed8;
            color: white;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 18px;
            font-weight: 800;
            display: inline-block;
            letter-spacing: 1px;
            margin: 12px 0;
        }

        table.info {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }

        table.info td {
            padding: 5px 8px;
            font-size: 11px;
        }

        table.info tr td:first-child {
            color: #6b7280;
            width: 35%;
        }

        table.info tr td:last-child {
            font-weight: 600;
            color: #111827;
        }

        table.info tr:nth-child(even) td {
            background: #f9fafb;
        }

        .section {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 10px 12px;
            margin-bottom: 12px;
        }

        .section-title {
            font-size: 11px;
            font-weight: 700;
            color: #374151;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 4px;
        }

        .status-badge {
            display: inline-block;
            padding: 3px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            background: #dbeafe;
            color: #1d4ed8;
        }

        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 60px;
            font-weight: 900;
            color: rgba(29, 78, 216, 0.04);
            pointer-events: none;
            white-space: nowrap;
        }

        .footer {
            text-align: center;
            color: #9ca3af;
            font-size: 10px;
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px dashed #e5e7eb;
        }

        @media print {
            body {
                padding: 0;
            }

            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body>

    <div class="watermark">SMK AL-MUNAWWIR IIBS</div>

    <div class="header">
        <div class="logo-circle">M</div>
        <div>
            <h1>SMK Al-Munawwir IIBS</h1>
            <h2>Bukti Pendaftaran PPDB — Sistem Penerimaan Murid Baru Digital</h2>
        </div>
    </div>

    <div style="text-align: center; margin-bottom: 16px;">
        <div class="badge-no"><?= esc($pendaftaran->no_pendaftaran) ?></div>
        <div style="margin-top: 4px;">
            <span class="status-badge"><?= ucwords(str_replace('_', ' ', $pendaftaran->status)) ?></span>
        </div>
        <p style="color: #6b7280; font-size: 10px; margin: 6px 0 0;">Dokumen ini dicetak pada <?= date('d/m/Y H:i') ?></p>
    </div>

    <div class="section">
        <div class="section-title">Data Pribadi Calon Siswa</div>
        <table class="info">
            <tr>
                <td>Nama Lengkap</td>
                <td><?= esc($dataDiri->nama_lengkap ?? '-') ?></td>
            </tr>
            <tr>
                <td>NISN</td>
                <td><?= esc($dataDiri->nisn ?? '-') ?></td>
            </tr>
            <tr>
                <td>NIK</td>
                <td><?= esc($dataDiri->nik ?? '-') ?></td>
            </tr>
            <tr>
                <td>Jenis Kelamin</td>
                <td><?= $dataDiri->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' ?></td>
            </tr>
            <tr>
                <td>Tempat, Tgl Lahir</td>
                <td><?= esc($dataDiri->tempat_lahir ?? '-') ?>, <?= format_tanggal($dataDiri->tanggal_lahir ?? null) ?></td>
            </tr>
            <tr>
                <td>Agama</td>
                <td><?= esc($dataDiri->agama ?? '-') ?></td>
            </tr>
            <tr>
                <td>Alamat</td>
                <td><?= esc($dataDiri->alamat ?? '-') ?></td>
            </tr>
            <tr>
                <td>No. HP</td>
                <td><?= esc($dataDiri->no_hp ?? '-') ?></td>
            </tr>
            <tr>
                <td>Asal Sekolah</td>
                <td><?= esc($dataDiri->asal_sekolah ?? '-') ?></td>
            </tr>
            <tr>
                <td>Tahun Lulus</td>
                <td><?= esc($dataDiri->tahun_lulus ?? '-') ?></td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Pilihan Program Keahlian</div>
        <table class="info">
            <tr>
                <td>Pilihan Pertama</td>
                <td><?= esc($pendaftaran->jurusan_pilihan1_nama ?? '-') ?></td>
            </tr>
            <tr>
                <td>Pilihan Kedua</td>
                <td><?= esc($pendaftaran->jurusan_pilihan2_nama ?? '-') ?></td>
            </tr>
            <?php if ($pendaftaran->jurusan_diterima_nama): ?>
                <tr>
                    <td>Jurusan Diterima</td>
                    <td style="color: #065f46;"><?= esc($pendaftaran->jurusan_diterima_nama) ?></td>
                </tr>
            <?php endif; ?>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Orang Tua / Wali</div>
        <table class="info">
            <tr>
                <td>Nama Ayah</td>
                <td><?= esc($dataDiri->nama_ayah ?? '-') ?></td>
            </tr>
            <tr>
                <td>Nama Ibu</td>
                <td><?= esc($dataDiri->nama_ibu ?? '-') ?></td>
            </tr>
            <tr>
                <td>No. HP Ortu</td>
                <td><?= esc($dataDiri->no_hp_ortu ?? '-') ?></td>
            </tr>
        </table>
    </div>

    <div class="footer">
        Simpan dokumen ini sebagai bukti pendaftaran Anda &mdash; SMK Al-Munawwir IIBS &mdash; <?= date('Y') ?>
    </div>

    <div class="no-print" style="text-align: center; margin-top: 20px;">
        <button onclick="window.print()" style="padding: 10px 24px; background: #1d4ed8; color: white; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer;">
            Cetak / Simpan PDF
        </button>
        <a href="<?= base_url('dashboard/status') ?>" style="margin-left: 10px; padding: 10px 20px; background: #f3f4f6; color: #374151; border-radius: 8px; font-size: 14px; text-decoration: none;">
            Kembali
        </a>
    </div>
</body>

</html>