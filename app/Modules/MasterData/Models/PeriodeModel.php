<?php

namespace App\Modules\MasterData\Models;

use CodeIgniter\Model;

class PeriodeModel extends Model
{
    protected $table         = 'periode';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $allowedFields = [
        'nama',
        'tahun_ajaran',
        'tanggal_mulai',
        'tanggal_selesai',
        'tanggal_pengumuman',
        'tanggal_daftar_ulang_mulai',
        'tanggal_daftar_ulang_selesai',
        'is_active',
        'is_published',
        'deskripsi',
        'wa_grup_link',   // link join grup WA pendaftar
        'wa_cp_no',       // nomor WA contact person panitia
    ];
    protected $useTimestamps = true;

    public function getPeriodeAktif(): ?object
    {
        return $this->where('is_active', 1)->first();
    }

    // =========================================================
    // VALIDASI PERIODE PENDAFTARAN — untuk memblokir
    // akses formulir saat periode belum/tidak aktif.
    // =========================================================

    /**
     * Tentukan status pendaftaran berdasarkan periode aktif (is_active=1)
     * DAN rentang tanggal_mulai - tanggal_selesai.
     *
     * Periode bisa saja di-set is_active=1 oleh admin tapi tanggal hari ini
     * sudah lewat tanggal_selesai (lupa dimatikan), atau belum masuk
     * tanggal_mulai (periode disiapkan lebih awal) — keduanya tetap harus
     * dianggap TUTUP untuk pengisian formulir.
     *
     * @param object|null $periode Opsional, lempar periode yang sudah di-fetch
     *                              agar tidak query ulang (misal dari controller lain).
     *
     * @return array{
     *     buka: bool,
     *     status: string,   // 'open' | 'soon' | 'closed'
     *     message: string,
     *     sisa?: int,
     *     periode: object|null
     * }
     */
    public function getStatusPendaftaran(?object $periode = null): array
    {
        $periode = $periode ?? $this->getPeriodeAktif();

        if (! $periode) {
            return [
                'buka'    => false,
                'status'  => 'closed',
                'message' => 'Pendaftaran belum dibuka. Belum ada periode SPMB yang diaktifkan oleh panitia.',
                'periode' => null,
            ];
        }

        $today   = date('Y-m-d');
        $mulai   = $periode->tanggal_mulai;
        $selesai = $periode->tanggal_selesai;

        if ($today < $mulai) {
            return [
                'buka'    => false,
                'status'  => 'soon',
                'message' => "Pendaftaran belum dibuka. PPDB {$periode->nama} akan dibuka pada " . format_tanggal($mulai) . '.',
                'periode' => $periode,
            ];
        }

        if ($today > $selesai) {
            return [
                'buka'    => false,
                'status'  => 'closed',
                'message' => "Periode pendaftaran {$periode->nama} telah berakhir pada " . format_tanggal($selesai) . '.',
                'periode' => $periode,
            ];
        }

        $sisa = (int) ((strtotime($selesai) - strtotime($today)) / 86400);

        return [
            'buka'    => true,
            'status'  => 'open',
            'message' => 'Periode pendaftaran sedang berjalan. Berakhir ' . format_tanggal($selesai) . '.',
            'sisa'    => $sisa,
            'periode' => $periode,
        ];
    }

    /**
     * Shortcut boolean: apakah pendaftaran sedang dibuka saat ini?
     */
    public function isPendaftaranDibuka(): bool
    {
        return $this->getStatusPendaftaran()['buka'];
    }

    /**
     * Aktifkan periode ini, nonaktifkan yang lain.
     */
    public function setAktif(int $id): bool
    {
        $db = db_connect();
        $db->table('periode')->update(['is_active' => 0]);
        return $this->update($id, ['is_active' => 1]);
    }

    /**
     * Cek apakah periode ini SAH untuk dipublikasikan pengumuman kelulusannya.
     *
     * Guard ini sengaja diletakkan di model (bukan hanya di controller) agar
     * tidak bisa dilewati siapa pun yang memanggil publish() langsung —
     * baik dari controller lain, command, maupun kode baru di masa depan.
     *
     * @return array{ok: bool, reason: ?string, sisa?: int}
     */
    public function canPublish(int $id): array
    {
        $periode = $this->find($id);

        if (! $periode) {
            return ['ok' => false, 'reason' => 'Periode tidak ditemukan.'];
        }

        if ((bool) $periode->is_published) {
            return ['ok' => false, 'reason' => 'Pengumuman periode ini sudah pernah dipublikasikan sebelumnya.'];
        }

        $sisaBelumProses = (int) db_connect()->table('pendaftaran')
            ->where('periode_id', $id)
            ->where('status', 'seleksi')
            ->where('deleted_at IS NULL')
            ->countAllResults();

        if ($sisaBelumProses > 0) {
            return [
                'ok'     => false,
                'reason' => "Pengumuman belum bisa dipublikasikan. Masih ada {$sisaBelumProses} peserta dengan status "
                    . "'Menunggu Seleksi' yang belum ditetapkan. Selesaikan penetapan kelulusan semua peserta terlebih dahulu.",
                'sisa'   => $sisaBelumProses,
            ];
        }

        return ['ok' => true, 'reason' => null];
    }

    /**
     * Publish pengumuman kelulusan.
     *
     * Menjalankan canPublish() lebih dulu — jika tidak lolos, melempar
     * RuntimeException berisi pesan yang sudah siap ditampilkan ke admin.
     * Ini mencegah is_published ke-set 1 secara prematur dari jalur mana pun,
     * termasuk jika ada kode baru di masa depan yang lupa memvalidasi sendiri.
     *
     * @throws \RuntimeException jika periode belum memenuhi syarat publish.
     */
    public function publish(int $id): bool
    {
        $check = $this->canPublish($id);

        if (! $check['ok']) {
            throw new \RuntimeException($check['reason']);
        }

        return $this->update($id, ['is_published' => 1]);
    }

    public function isPengumumanPublished(): bool
    {
        $p = $this->getPeriodeAktif();
        return $p && (bool) $p->is_published;
    }

    // =========================================================
    // HELPER WA — ambil dari periode aktif
    // =========================================================

    /**
     * Link grup WA dari periode aktif.
     * Fallback ke env WA_GRUP_LINK jika kolom belum diisi.
     */
    public function getWaGrupLink(): string
    {
        $p = $this->getPeriodeAktif();
        if ($p && ! empty($p->wa_grup_link)) {
            return $p->wa_grup_link;
        }
        return env('WA_GRUP_LINK', '#');
    }

    /**
     * Nomor WA CP panitia (format display: 0812-xxxx-xxxx)
     * dari periode aktif. Fallback ke env WA_KONTAK_NO.
     */
    public function getWaCpNo(): string
    {
        $p = $this->getPeriodeAktif();
        if ($p && ! empty($p->wa_cp_no)) {
            return $p->wa_cp_no;
        }
        return env('WA_KONTAK_NO', '0812-xxxx-xxxx');
    }

    /**
     * Link wa.me dari nomor WA CP.
     * Konversi 08xxx ke 628xxx otomatis.
     */
    public function getWaCpLink(): string
    {
        $no  = $this->getWaCpNo();
        $raw = preg_replace('/[^0-9]/', '', $no);
        if (str_starts_with($raw, '0')) {
            $raw = '62' . substr($raw, 1);
        }
        return "https://wa.me/{$raw}";
    }
}
