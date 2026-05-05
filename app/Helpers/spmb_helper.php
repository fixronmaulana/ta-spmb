<?php

if (! function_exists('status_label')) {
    /**
     * Mengembalikan label HTML untuk status pendaftaran
     */
    function status_label(string $status): string
    {
        $map = [
            'draft'        => ['label' => 'Draft',             'class' => 'bg-gray-100 text-gray-700'],
            'submitted'    => ['label' => 'Menunggu Verifikasi', 'class' => 'bg-blue-100 text-blue-800'],
            'verifikasi'   => ['label' => 'Dalam Verifikasi',  'class' => 'bg-yellow-100 text-yellow-800'],
            'revisi'       => ['label' => 'Perlu Revisi',      'class' => 'bg-orange-100 text-orange-800'],
            'seleksi'      => ['label' => 'Dalam Seleksi',     'class' => 'bg-purple-100 text-purple-800'],
            'lulus'        => ['label' => 'Diterima / Lulus',  'class' => 'bg-green-100 text-green-800'],
            'tidak_lulus'  => ['label' => 'Tidak Diterima',    'class' => 'bg-red-100 text-red-800'],
            'daftar_ulang' => ['label' => 'Daftar Ulang',      'class' => 'bg-teal-100 text-teal-800'],
            'siswa_aktif'  => ['label' => 'Siswa Aktif',       'class' => 'bg-emerald-100 text-emerald-800'],
        ];

        $s = $map[$status] ?? ['label' => ucfirst($status), 'class' => 'bg-gray-100 text-gray-600'];
        return sprintf(
            '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium %s">%s</span>',
            $s['class'],
            $s['label']
        );
    }
}

if (! function_exists('dokumen_status_label')) {
    function dokumen_status_label(string $status): string
    {
        $map = [
            'pending'  => ['label' => 'Menunggu',  'class' => 'bg-gray-100 text-gray-700'],
            'approved' => ['label' => 'Disetujui', 'class' => 'bg-green-100 text-green-800'],
            'rejected' => ['label' => 'Ditolak',   'class' => 'bg-red-100 text-red-800'],
        ];
        $s = $map[$status] ?? ['label' => $status, 'class' => 'bg-gray-100 text-gray-700'];
        return sprintf(
            '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium %s">%s</span>',
            $s['class'],
            $s['label']
        );
    }
}

// ══════════════════════════════════════════════════════════════════════════
// HELPER: Cache in-memory untuk data jenis_dokumen
// ══════════════════════════════════════════════════════════════════════════

if (! function_exists('_get_jenis_dokumen_cache')) {
    /**
     * Ambil data jenis_dokumen dari DB dan cache dalam memori proses ini.
     * Hanya query 1x per request, tidak bergantung pada file config.
     *
     * @return object[]  Array row object dari tabel jenis_dokumen
     */
    function _get_jenis_dokumen_cache(): array
    {
        static $cache = null;

        if ($cache === null) {
            try {
                $cache = db_connect()
                    ->table('jenis_dokumen')
                    ->where('is_active', 1)
                    ->orderBy('urutan', 'ASC')
                    ->orderBy('nama_dokumen', 'ASC')
                    ->get()
                    ->getResultObject();
            } catch (\Throwable $e) {
                // Fallback jika tabel belum ada (misalnya saat migration pertama)
                log_message('warning', 'spmb_helper: gagal query jenis_dokumen — ' . $e->getMessage());
                $cache = [];
            }
        }

        return $cache;
    }
}

if (! function_exists('jenis_dokumen_label')) {
    /**
     * Ambil label/nama dokumen berdasarkan kode.
     * Data bersumber dari tabel jenis_dokumen (DB), bukan hardcode.
     * Jika kode tidak ditemukan di DB, fallback ke format otomatis.
     */
    function jenis_dokumen_label(string $kode): string
    {
        foreach (_get_jenis_dokumen_cache() as $row) {
            if ($row->kode === $kode) {
                return $row->nama_dokumen;
            }
        }
        // Fallback — format dari kode: "akta_lahir" → "Akta Lahir"
        return ucwords(str_replace('_', ' ', $kode));
    }
}

if (! function_exists('jenis_dokumen_wajib')) {
    /**
     * Kembalikan array kode dokumen yang wajib diupload.
     * Data bersumber dari tabel jenis_dokumen (kolom is_wajib = 1).
     * Tidak hardcode lagi — admin bisa ubah via halaman Master Data.
     *
     * @return string[]  ['ijazah', 'akta_lahir', ...]
     */
    function jenis_dokumen_wajib(): array
    {
        $wajib = [];
        foreach (_get_jenis_dokumen_cache() as $row) {
            if ((int) $row->is_wajib === 1) {
                $wajib[] = $row->kode;
            }
        }

        // Fallback hardcode jika tabel kosong (belum di-seed)
        if (empty($wajib)) {
            return ['ijazah', 'akta_lahir', 'kartu_keluarga', 'ktp_ortu', 'foto'];
        }

        return $wajib;
    }
}

if (! function_exists('jenis_dokumen_semua')) {
    /**
     * Kembalikan map [kode => nama_dokumen] semua jenis dokumen aktif.
     * Dipakai oleh controller sebagai pengganti hardcode array di
     * getJenisDokumenSemua() dan getAllowedJenis().
     *
     * @return array<string, string>  ['ijazah' => 'Ijazah SMP/MTs', ...]
     */
    function jenis_dokumen_semua(): array
    {
        $map = [];
        foreach (_get_jenis_dokumen_cache() as $row) {
            $map[$row->kode] = $row->nama_dokumen;
        }

        // Fallback
        if (empty($map)) {
            return [
                'ijazah'         => 'Ijazah SMP/MTs',
                'akta_lahir'     => 'Akta Kelahiran',
                'kartu_keluarga' => 'Kartu Keluarga (KK)',
                'ktp_ortu'       => 'KTP Orang Tua',
                'foto'           => 'Pas Foto Terbaru',
            ];
        }

        return $map;
    }
}

if (! function_exists('format_tanggal')) {
    function format_tanggal(?string $date, string $format = 'd F Y'): string
    {
        if (! $date) return '-';
        $bulan = [
            '01' => 'Januari',
            '02' => 'Februari',
            '03' => 'Maret',
            '04' => 'April',
            '05' => 'Mei',
            '06' => 'Juni',
            '07' => 'Juli',
            '08' => 'Agustus',
            '09' => 'September',
            '10' => 'Oktober',
            '11' => 'November',
            '12' => 'Desember',
        ];
        $d = date('d', strtotime($date));
        $m = date('m', strtotime($date));
        $y = date('Y', strtotime($date));
        return $d . ' ' . ($bulan[$m] ?? $m) . ' ' . $y;
    }
}

if (! function_exists('format_rupiah')) {
    function format_rupiah(?int $nominal): string
    {
        if ($nominal === null) return '-';
        return 'Rp ' . number_format($nominal, 0, ',', '.');
    }
}

if (! function_exists('human_filesize')) {
    function human_filesize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}

if (! function_exists('generate_no_pendaftaran')) {
    function generate_no_pendaftaran(): string
    {
        $year   = date('Y');
        $random = strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
        $noPend = "PPDB-{$year}-{$random}";

        $db = db_connect();
        while ($db->table('pendaftaran')->where('no_pendaftaran', $noPend)->countAllResults() > 0) {
            $random = strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
            $noPend = "PPDB-{$year}-{$random}";
        }

        return $noPend;
    }
}

if (! function_exists('current_user')) {
    function current_user(): array
    {
        return [
            'id'    => session()->get('user_id'),
            'name'  => session()->get('user_name'),
            'email' => session()->get('user_email'),
            'role'  => session()->get('user_role'),
        ];
    }
}
