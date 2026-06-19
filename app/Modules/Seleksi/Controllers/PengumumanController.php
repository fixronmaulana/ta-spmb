<?php

namespace App\Modules\Seleksi\Controllers;

use App\Controllers\BaseController;
use App\Modules\Pendaftaran\Models\PendaftaranModel;
use App\Modules\Pendaftaran\Models\DataDiriSiswaModel;
use App\Modules\MasterData\Models\PeriodeModel;

class PengumumanController extends BaseController
{
    protected PendaftaranModel   $pendaftaranModel;
    protected DataDiriSiswaModel $dataDiriModel;
    protected PeriodeModel       $periodeModel;

    public function __construct()
    {
        $this->pendaftaranModel = new PendaftaranModel();
        $this->dataDiriModel    = new DataDiriSiswaModel();
        $this->periodeModel     = new PeriodeModel();
    }

    // =========================================================
    // INDEX — Halaman pengumuman hasil seleksi
    // =========================================================
    public function index()
    {
        $userId       = $this->userId();
        $periodeAktif = $this->periodeModel->getPeriodeAktif();
        $isPublished  = $this->periodeModel->isPengumumanPublished();

        // Ambil pendaftaran milik user yang login, di periode aktif saja
        // Tidak boleh menampilkan data pendaftaran dari periode lain
        $pendaftaran = null;
        if ($periodeAktif) {
            $pendaftaran = $this->pendaftaranModel
                ->where('user_id', $userId)
                ->where('periode_id', $periodeAktif->id)
                ->first();

            if ($pendaftaran) {
                $pendaftaran = $this->pendaftaranModel->getWithRelations($pendaftaran->id);
            }
        }

        return $this->render('App\Modules\Seleksi\Views\pengumuman', [
            'title'        => 'Pengumuman Hasil Seleksi',
            'pendaftaran'  => $pendaftaran,
            'periodeAktif' => $periodeAktif,
            'isPublished'  => $isPublished,
        ]);
    }

    // =========================================================
    // CARI — endpoint pencarian hasil seleksi (AJAX)
    //
    // KEAMANAN:
    // - Hanya bisa diakses jika pengumuman sudah dipublikasikan (is_published=1)
    // - Query hanya mencari di periode aktif — tidak bisa lihat hasil periode lain
    // - Data yang dikembalikan hanya: no_pendaftaran, nama, status, jurusan_diterima
    //   TIDAK ada: user_id, email, nomor HP, data pribadi lain
    // - Status yang boleh tampil hanya 'lulus' dan 'tidak_lulus' — status
    //   'seleksi' (belum diproses) tidak akan pernah muncul di sini
    // =========================================================
    public function cari()
    {
        // Cek apakah pengumuman sudah dipublikasikan
        if (! $this->periodeModel->isPengumumanPublished()) {
            return $this->jsonError('Pengumuman belum dipublikasikan.');
        }

        $periodeAktif = $this->periodeModel->getPeriodeAktif();
        if (! $periodeAktif) {
            return $this->jsonError('Tidak ada periode SPMB yang aktif.');
        }

        // Baca query dari JSON body (fetch) atau POST form
        $q = '';
        $contentType = $this->request->getHeaderLine('Content-Type');

        if (str_contains($contentType, 'application/json')) {
            $body = $this->request->getJSON(true);
            $q    = trim($body['q'] ?? '');
        } else {
            $q = trim($this->request->getPost('q') ?? '');
        }

        if ($q === '') {
            return $this->jsonError('Masukkan nomor pendaftaran atau nama.');
        }

        // ── Cek apakah yang mencari adalah user yang login ────────────────────
        // Jika user sudah login dan memiliki pendaftaran di periode aktif ini,
        // pastikan query cocok dengan data dirinya sendiri.
        // Jika tidak cocok, tolak — user tidak boleh melihat status orang lain.
        $userId      = $this->userId();
        $pendaftaranSaya = $this->pendaftaranModel
            ->where('user_id', $userId)
            ->where('periode_id', $periodeAktif->id)
            ->first();

        $db   = db_connect();
        $like = '%' . $db->escapeLikeString($q) . '%';

        if ($pendaftaranSaya) {
            // User punya pendaftaran — hanya boleh lihat data dirinya sendiri
            $sql = "
                SELECT
                    p.id,
                    p.no_pendaftaran,
                    p.status,
                    d.nama_lengkap    AS nama,
                    jd.nama           AS jurusan_diterima,
                    jd.kode           AS jurusan_diterima_kode
                FROM pendaftaran p
                LEFT JOIN data_diri_siswas d ON d.pendaftaran_id = p.id
                LEFT JOIN jurusan jd         ON jd.id            = p.jurusan_diterima_id
                WHERE p.id          = ?
                  AND p.periode_id  = ?
                  AND p.status      IN ('lulus', 'tidak_lulus')
                  AND p.deleted_at  IS NULL
                  AND (
                        p.no_pendaftaran LIKE ?
                     OR d.nama_lengkap   LIKE ?
                  )
                LIMIT 1
            ";
            $result = $db->query($sql, [
                $pendaftaranSaya->id,
                $periodeAktif->id,
                $like,
                $like,
            ])->getRowObject();

            if (! $result) {
                return $this->jsonError(
                    'Data tidak ditemukan. Pastikan nomor pendaftaran atau nama yang Anda masukkan sesuai dengan data pendaftaran Anda sendiri.'
                );
            }
        } else {
            // User belum/tidak punya pendaftaran di periode ini — boleh cari bebas
            // (misalnya orang tua yang cek hasil anaknya via halaman publik tanpa login)
            // tapi tetap dibatasi hanya periode aktif
            $sql = "
                SELECT
                    p.id,
                    p.no_pendaftaran,
                    p.status,
                    d.nama_lengkap    AS nama,
                    jd.nama           AS jurusan_diterima,
                    jd.kode           AS jurusan_diterima_kode
                FROM pendaftaran p
                LEFT JOIN data_diri_siswas d ON d.pendaftaran_id = p.id
                LEFT JOIN jurusan jd         ON jd.id            = p.jurusan_diterima_id
                WHERE p.periode_id  = ?
                  AND p.status      IN ('lulus', 'tidak_lulus')
                  AND p.deleted_at  IS NULL
                  AND (
                        p.no_pendaftaran LIKE ?
                     OR d.nama_lengkap   LIKE ?
                  )
                LIMIT 1
            ";
            $result = $db->query($sql, [
                $periodeAktif->id,
                $like,
                $like,
            ])->getRowObject();

            if (! $result) {
                return $this->jsonError('Data tidak ditemukan. Pastikan nomor pendaftaran atau nama yang dimasukkan sudah benar.');
            }
        }

        $jurusanTampil = $result->jurusan_diterima ?? '—';
        $jurusanKode   = $result->jurusan_diterima_kode ?? '';

        return $this->jsonSuccess('Data ditemukan.', [
            'no_pendaftaran'   => $result->no_pendaftaran,
            'nama'             => $result->nama ?? 'Tidak diketahui',
            'status'           => $result->status,
            'jurusan_diterima' => $jurusanKode
                ? "{$jurusanKode} — {$jurusanTampil}"
                : $jurusanTampil,
        ]);
    }
}
