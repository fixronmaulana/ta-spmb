<?php

namespace App\Modules\DaftarUlang\Controllers;

use App\Controllers\BaseController;
use App\Modules\DaftarUlang\Models\DaftarUlangModel;
use App\Modules\Pendaftaran\Models\PendaftaranModel;
use App\Modules\MasterData\Models\KelasModel;
use App\Modules\Notifikasi\Services\NotifikasiService;
use App\Libraries\FileUploader;

class DaftarUlangController extends BaseController
{
    protected DaftarUlangModel $model;
    protected PendaftaranModel $pendaftaranModel;

    public function __construct()
    {
        $this->model            = new DaftarUlangModel();
        $this->pendaftaranModel = new PendaftaranModel();
    }

    public function form()
    {
        $userId      = $this->userId();
        $pendaftaran = $this->pendaftaranModel->getByUserId($userId);

        if (! $pendaftaran || ! in_array($pendaftaran->status, ['lulus', 'daftar_ulang'])) {
            return redirect()->to(base_url('dashboard/status'))
                ->with('error', 'Anda tidak memenuhi syarat untuk daftar ulang saat ini.');
        }

        $existing = $this->model->getByPendaftaranId($pendaftaran->id);
        if ($existing && $existing->status === DaftarUlangModel::STATUS_DIKONFIRMASI) {
            return redirect()->to(base_url('dashboard/daftar-ulang/status'))
                ->with('info', 'Daftar ulang Anda sudah dikonfirmasi oleh admin.');
        }

        $pendaftaran = $this->pendaftaranModel->getWithRelations($pendaftaran->id);
        $kelasModel  = new KelasModel();
        $kelasList   = $kelasModel->getKelasAktif((int) $pendaftaran->jurusan_diterima_id);

        return $this->render('App\Modules\DaftarUlang\Views\form', [
            'title'       => 'Daftar Ulang',
            'pendaftaran' => $pendaftaran,
            'existing'    => $existing,
            'kelasList'   => $kelasList,
        ]);
    }

    /**
     * Handle submit bukti pembayaran.
     * Mendukung AJAX fetch (returns JSON) dan form POST biasa (returns redirect).
     * Menggunakan AJAX agar spinner tidak stuck jika terjadi error server.
     */
    public function submit()
    {
        $isAjax = $this->request->isAJAX();

        $userId      = $this->userId();
        $pendaftaran = $this->pendaftaranModel->getByUserId($userId);

        if (! $pendaftaran || ! in_array($pendaftaran->status, ['lulus', 'daftar_ulang'])) {
            if ($isAjax) {
                return $this->response->setStatusCode(403)->setJSON([
                    'success' => false,
                    'message' => 'Tidak dapat melakukan daftar ulang.',
                ]);
            }
            return redirect()->back()->with('error', 'Tidak dapat melakukan daftar ulang.');
        }

        $existing = $this->model->getByPendaftaranId($pendaftaran->id);

        if ($existing && $existing->status === DaftarUlangModel::STATUS_DIKONFIRMASI) {
            if ($isAjax) {
                return $this->response->setStatusCode(422)->setJSON([
                    'success' => false,
                    'message' => 'Daftar ulang Anda sudah dikonfirmasi, tidak bisa diubah lagi.',
                    'redirect' => base_url('dashboard/daftar-ulang/status'),
                ]);
            }
            return redirect()->to(base_url('dashboard/daftar-ulang/status'))
                ->with('info', 'Daftar ulang Anda sudah dikonfirmasi, tidak bisa diubah lagi.');
        }

        // ── Validasi nominal ──────────────────────────────────────────────
        // Nilai dari form: format ribuan Indonesia mis. "2.500.000"
        // Strip semua non-digit sebelum validasi agar tidak gagal karena titik.
        $nominalBersih = preg_replace('/[^0-9]/', '', $this->request->getPost('nominal_pembayaran') ?? '0');

        if (empty($nominalBersih) || (int) $nominalBersih <= 0) {
            if ($isAjax) {
                return $this->response->setStatusCode(422)->setJSON([
                    'success' => false,
                    'message' => 'Nominal pembayaran wajib diisi dan harus lebih dari 0.',
                ]);
            }
            return redirect()->back()->withInput()
                ->with('error', 'Nominal pembayaran wajib diisi dan harus lebih dari 0.');
        }

        // ── Validasi file ─────────────────────────────────────────────────
        $sudahUpload = $existing && $existing->bukti_pembayaran_path;
        $buktiFiler  = $sudahUpload
            ? 'permit_empty|max_size[bukti_pembayaran,2048]'
            : 'uploaded[bukti_pembayaran]|max_size[bukti_pembayaran,2048]';

        if (! $this->validate(['bukti_pembayaran' => $buktiFiler])) {
            $errMsg = implode(' ', $this->validator->getErrors());
            if ($isAjax) {
                return $this->response->setStatusCode(422)->setJSON([
                    'success' => false,
                    'message' => $errMsg ?: 'File bukti pembayaran tidak valid.',
                ]);
            }
            return redirect()->back()->withInput()->with('error', $errMsg);
        }

        // ── Upload file ───────────────────────────────────────────────────
        $file     = $this->request->getFile('bukti_pembayaran');
        $uploader = new FileUploader();

        $buktiPath     = $existing->bukti_pembayaran_path ?? null;
        $namaFileBukti = $existing->nama_file_bukti ?? null;

        if ($file && $file->isValid() && ! $file->hasMoved()) {
            $upload = $uploader->upload($file, 'bukti');
            if (! $upload['success']) {
                if ($isAjax) {
                    return $this->response->setStatusCode(422)->setJSON([
                        'success' => false,
                        'message' => $upload['message'],
                    ]);
                }
                return redirect()->back()->withInput()->with('error', $upload['message']);
            }
            $buktiPath     = $upload['path'];
            $namaFileBukti = $upload['original_name'];
        }

        if (! $buktiPath) {
            if ($isAjax) {
                return $this->response->setStatusCode(422)->setJSON([
                    'success' => false,
                    'message' => 'Bukti pembayaran wajib diupload.',
                ]);
            }
            return redirect()->back()->withInput()->with('error', 'Bukti pembayaran wajib diupload.');
        }

        // ── Simpan ke DB ──────────────────────────────────────────────────
        $data = [
            'pendaftaran_id'        => $pendaftaran->id,
            'user_id'               => $userId,
            'kelas_id'              => $this->request->getPost('kelas_id') ?: null,
            'bukti_pembayaran_path' => $buktiPath,
            'nama_file_bukti'       => $namaFileBukti,
            'nominal_pembayaran'    => (int) $nominalBersih,
            'catatan_siswa'         => $this->request->getPost('catatan_siswa') ?? '',
            'status'                => DaftarUlangModel::STATUS_PENDING,
        ];

        if ($existing) {
            $this->model->update($existing->id, $data);
        } else {
            $this->model->insert($data);
        }

        // Update status pendaftaran ke 'daftar_ulang' hanya jika masih 'lulus'
        if ($pendaftaran->status === 'lulus') {
            $this->pendaftaranModel->updateStatus($pendaftaran->id, 'daftar_ulang');
        }

        // ── Notifikasi ke admin ───────────────────────────────────────────
        try {
            $notif = new NotifikasiService();
            $notif->notifikasiKeAdmin(
                'daftar_ulang_masuk',
                'Daftar Ulang Baru',
                'Ada pengajuan daftar ulang baru dari pendaftar ' . ($pendaftaran->no_pendaftaran ?? '#' . $pendaftaran->id),
                ['url' => base_url('admin/daftar-ulang')]
            );
        } catch (\Throwable $e) {
            log_message('warning', 'DaftarUlangController::submit - notif admin gagal: ' . $e->getMessage());
        }

        // ── Response ──────────────────────────────────────────────────────
        $redirectUrl = base_url('dashboard/daftar-ulang/status');

        if ($isAjax) {
            return $this->response->setJSON([
                'success'  => true,
                'message'  => 'Bukti pembayaran berhasil diupload! Menunggu verifikasi admin.',
                'redirect' => $redirectUrl,
            ]);
        }

        return redirect()->to($redirectUrl)
            ->with('success', 'Bukti pembayaran berhasil diupload! Menunggu verifikasi admin.');
    }

    public function status()
    {
        $userId      = $this->userId();
        $pendaftaran = $this->pendaftaranModel->getByUserId($userId);
        $daftarUlang = $pendaftaran ? $this->model->getByPendaftaranId($pendaftaran->id) : null;

        return $this->render('App\Modules\DaftarUlang\Views\status', [
            'title'       => 'Status Daftar Ulang',
            'daftarUlang' => $daftarUlang,
            'pendaftaran' => $pendaftaran,
        ]);
    }
}