<?php

namespace App\Modules\Pendaftaran\Controllers;

use App\Controllers\BaseController;
use App\Modules\Pendaftaran\Models\PendaftaranModel;
use App\Modules\Pendaftaran\Models\DokumenModel;
use App\Libraries\FileUploader;

class DokumenController extends BaseController
{
    protected PendaftaranModel $pendaftaranModel;
    protected DokumenModel     $dokumenModel;
    protected FileUploader     $uploader;

    public function __construct()
    {
        $this->pendaftaranModel = new PendaftaranModel();
        $this->dokumenModel     = new DokumenModel();
        $this->uploader         = new FileUploader();
    }

    /**
     * Upload dokumen (AJAX fetch dari step4.php)
     *
     * Hanya untuk status 'draft' dan 'revisi' — pengisian formulir awal.
     *
     * BUG 1 FIX: $this->request->isAJAX() mendeteksi header
     * "X-Requested-With: XMLHttpRequest", tapi fetch() API modern
     * (yang dipakai Alpine.js di step4.php) TIDAK mengirim header tersebut
     * secara default. Akibatnya isAJAX() selalu false → controller
     * melakukan redirect()->back() alih-alih return JSON → browser
     * menerima response HTML redirect → JSON.parse gagal → catch block
     * menampilkan "Gagal mengupload file. Coba lagi."
     *
     * Solusi: ganti deteksi dengan memeriksa header Accept atau
     * Content-Type request, atau cukup selalu return JSON karena
     * endpoint ini memang hanya dipanggil via fetch (tidak pernah
     * diakses langsung via form submit biasa).
     */
    public function upload()
    {
        $pendaftaran = $this->pendaftaranModel->getByUserId($this->userId());

        if (! $pendaftaran || ! in_array($pendaftaran->status, ['draft', 'revisi'])) {
            return $this->jsonError('Pendaftaran tidak dapat diedit.');
        }

        return $this->prosesUpload($pendaftaran, false);
    }

    /**
     * ══════════════════════════════════════════════════════════════════
     * Upload Ulang Dokumen (AJAX dari halaman status.php)
     * ══════════════════════════════════════════════════════════════════
     *
     * Berbeda dari upload() biasa, method ini dikhususkan untuk:
     * - Status pendaftaran: 'verifikasi', 'submitted', 'revisi'
     *   (dokumen sudah pernah disubmit, tapi diminta perbaikan oleh admin)
     * - Me-reset status_verifikasi dokumen menjadi 'pending' (menunggu
     *   verifikasi ulang oleh admin)
     * - Menghapus catatan_verifikasi lama agar bersih
     *
     * Endpoint: POST dashboard/formulir/upload-ulang-dokumen
     * Dipanggil dari: doUpload() di statusModals() Alpine.js (status.php)
     */
    public function uploadUlang()
    {
        $pendaftaran = $this->pendaftaranModel->getByUserId($this->userId());

        // Status yang diizinkan untuk upload ulang setelah submit
        $statusDiizinkan = ['verifikasi', 'submitted', 'revisi'];

        if (! $pendaftaran) {
            return $this->jsonError('Data pendaftaran tidak ditemukan.');
        }

        if (! in_array($pendaftaran->status, $statusDiizinkan)) {
            return $this->jsonError(
                'Upload ulang hanya dapat dilakukan saat pendaftaran dalam status verifikasi atau revisi. '
                    . 'Status saat ini: ' . $pendaftaran->status
            );
        }

        return $this->prosesUpload($pendaftaran, true);
    }

    /**
     * ══════════════════════════════════════════════════════════════════
     * Proses Upload (shared logic antara upload() dan uploadUlang())
     * ══════════════════════════════════════════════════════════════════
     *
     * @param  object $pendaftaran  Row pendaftaran dari DB
     * @param  bool   $isUlang     true = upload ulang (reset ke pending),
     *                             false = upload pertama kali (draft/revisi)
     */
    private function prosesUpload(object $pendaftaran, bool $isUlang): \CodeIgniter\HTTP\ResponseInterface
    {
        $jenisDokumen = $this->request->getPost('jenis_dokumen');

        if (! in_array($jenisDokumen, array_keys($this->getAllowedJenis()))) {
            return $this->jsonError('Jenis dokumen tidak valid: ' . ($jenisDokumen ?? '(kosong)'));
        }

        $file = $this->request->getFile('file');

        if (! $file || ! $file->isValid()) {
            return $this->jsonError('File tidak valid atau tidak ada. Error: ' . ($file ? $file->getErrorString() : 'null'));
        }

        if ($file->hasMoved()) {
            return $this->jsonError('File sudah diproses sebelumnya.');
        }

        $allowed = ['pdf', 'jpg', 'jpeg', 'png'];

        if (! in_array(strtolower($file->getExtension()), $allowed)) {
            return $this->jsonError('Format file tidak diizinkan. Gunakan PDF, JPG, atau PNG.');
        }

        // Upload menggunakan FileUploader library
        $result = $this->uploader->upload($file, 'dokumen');

        if (! $result['success']) {
            return $this->jsonError($result['message']);
        }

        // Hapus dokumen lama dengan jenis yang sama jika ada
        $existing = $this->dokumenModel->getByJenis($pendaftaran->id, $jenisDokumen);
        if ($existing) {
            $this->uploader->delete($existing->nama_file_simpan, 'dokumen');
            $this->dokumenModel->delete($existing->id);
        }

        // Simpan ke database
        // Jika upload ulang: status_verifikasi = 'pending' dan catatan dihapus
        // Jika upload pertama: status_verifikasi = 'pending'
        $dokumenData = [
            'pendaftaran_id'    => $pendaftaran->id,
            'jenis_dokumen'     => $jenisDokumen,
            'nama_file_asli'    => $result['original_name'],
            'nama_file_simpan'  => $result['saved_name'],
            'path_file'         => $result['path'],
            'ukuran_file'       => $result['size'],
            'tipe_mime'         => $result['mime_type'],
            'status_verifikasi' => 'pending',
        ];

        // Jika upload ulang, reset catatan verifikasi lama
        if ($isUlang) {
            $dokumenData['catatan_verifikasi'] = null;
            $dokumenData['diverifikasi_oleh']  = null;
            $dokumenData['diverifikasi_pada']  = null;
        }

        $dokumenId = $this->dokumenModel->insert($dokumenData);

        if (! $dokumenId) {
            return $this->jsonError('Gagal menyimpan data dokumen ke database.');
        }

        /**
         * BUG 2 FIX: jsonSuccess() di BaseController membungkus data
         * dalam key 'data': { success, message, data: { ... } }
         * Tapi di step4.php JS melakukan: data.success, data.label,
         * data.nama_file_asli, data.ukuran — langsung di root level,
         * bukan data.data.label dst.
         *
         * Solusi: kembalikan semua field yang dibutuhkan JS langsung
         * di root JSON menggunakan response()->setJSON() manual,
         * atau flatten strukturnya agar cocok dengan yang dibaca JS.
         *
         * Kita pilih pendekatan flatten agar JS tidak perlu diubah.
         */
        return $this->response->setStatusCode(200)->setJSON([
            'success'        => true,
            'message'        => $isUlang
                ? 'Dokumen berhasil diupload ulang dan menunggu verifikasi admin.'
                : 'Dokumen berhasil diupload.',
            'id'             => $dokumenId,
            'jenis'          => $jenisDokumen,
            'label'          => jenis_dokumen_label($jenisDokumen),
            'nama_file_asli' => $result['original_name'],
            'ukuran'         => human_filesize($result['size']),
            'status'         => 'pending',
            'is_ulang'       => $isUlang,
            // Digunakan JS untuk update progress bar di step4
            'total_wajib'    => count(jenis_dokumen_wajib()), // dinamis dari DB
        ]);
    }

    /**
     * Hapus dokumen
     *
     * BUG 3 FIX: sama seperti upload — hapusDokumen() di step4.php
     * menggunakan fetch dengan method DELETE, bukan XMLHttpRequest,
     * sehingga isAJAX() juga false di sini. Karena endpoint ini
     * memang pure JSON, hapus semua conditional isAJAX() check.
     */
    public function hapus(int $dokumenId)
    {
        $dokumen     = $this->dokumenModel->find($dokumenId);
        $pendaftaran = $this->pendaftaranModel->getByUserId($this->userId());

        if (! $dokumen || ! $pendaftaran || $dokumen->pendaftaran_id !== $pendaftaran->id) {
            return $this->jsonError('Dokumen tidak ditemukan atau tidak memiliki akses.');
        }

        if (! in_array($pendaftaran->status, ['draft', 'revisi'])) {
            return $this->jsonError('Dokumen tidak dapat dihapus setelah formulir disubmit.');
        }

        // Hapus file fisik
        $this->uploader->delete($dokumen->nama_file_simpan, 'dokumen');

        // Hapus dari database
        $this->dokumenModel->delete($dokumenId);

        return $this->jsonSuccess('Dokumen berhasil dihapus.');
    }

    private function getAllowedJenis(): array
    {
        // Dinamis dari DB — admin dapat menambah/nonaktifkan jenis dokumen
        // via halaman Master Data tanpa perlu ubah kode ini.
        return jenis_dokumen_semua();
    }
}
