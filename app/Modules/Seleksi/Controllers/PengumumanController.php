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

        $pendaftaran  = $this->pendaftaranModel->getByUserId($userId);

        return $this->render('App\Modules\Seleksi\Views\pengumuman', [
            'title'        => 'Pengumuman Hasil Seleksi',
            'pendaftaran'  => $pendaftaran
                ? $this->pendaftaranModel->getWithRelations($pendaftaran->id)
                : null,
            'periodeAktif' => $periodeAktif,
            'isPublished'  => $isPublished,
        ]);
    }

    // =========================================================
    // CARI — endpoint pencarian hasil seleksi
    // Menerima POST dari fetch() JavaScript (AJAX) maupun form biasa.
    //
    // BUG SEBELUMNYA:
    //   isAJAX() mengecek header 'X-Requested-With: XMLHttpRequest'
    //   tapi fetch() browser TIDAK mengirim header itu secara default
    //   → isAJAX() selalu false → selalu return 400 → hasil tidak pernah muncul
    //
    // FIX: Hapus cek isAJAX(). Endpoint ini memang hanya dipakai via JS,
    //      tapi tidak ada kerugian jika diakses langsung (tetap return JSON).
    // =========================================================
    public function cari()
    {
        // Cek apakah pengumuman sudah dipublikasikan
        if (! $this->periodeModel->isPengumumanPublished()) {
            return $this->jsonError('Pengumuman belum dipublikasikan.');
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

        $db  = db_connect();
        $sql = "
            SELECT
                p.id,
                p.no_pendaftaran,
                p.status,
                d.nama_lengkap    AS nama,
                jd.nama           AS jurusan_diterima,
                jd.kode           AS jurusan_diterima_kode,
                j1.nama           AS jurusan_pilihan1,
                j1.kode           AS jurusan_pilihan1_kode
            FROM pendaftaran p
            LEFT JOIN data_diri_siswas d  ON d.pendaftaran_id  = p.id
            LEFT JOIN jurusan jd          ON jd.id             = p.jurusan_diterima_id
            LEFT JOIN jurusan j1          ON j1.id             = p.jurusan_pilihan1_id
            WHERE p.status      IN ('lulus', 'tidak_lulus')
              AND p.deleted_at  IS NULL
              AND (
                    p.no_pendaftaran LIKE ?
                 OR d.nama_lengkap   LIKE ?
              )
            LIMIT 1
        ";

        $like   = '%' . $db->escapeLikeString($q) . '%';
        $result = $db->query($sql, [$like, $like])->getRowObject();

        if (! $result) {
            return $this->jsonError('Data tidak ditemukan. Pastikan nomor pendaftaran atau nama yang dimasukkan sudah benar.');
        }

        $jurusanTampil = $result->jurusan_diterima ?? $result->jurusan_pilihan1 ?? '—';
        $jurusanKode   = $result->jurusan_diterima_kode ?? $result->jurusan_pilihan1_kode ?? '';

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