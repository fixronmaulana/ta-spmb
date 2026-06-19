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
    protected KelasModel       $kelasModel;

    public function __construct()
    {
        $this->model            = new DaftarUlangModel();
        $this->pendaftaranModel = new PendaftaranModel();
        $this->kelasModel       = new KelasModel();
    }

    // =========================================================
    // FORM
    // =========================================================
    public function form()
    {
        $userId      = $this->userId();
        $pendaftaran = $this->pendaftaranModel->getByUserId($userId);

        if (! $pendaftaran || ! in_array($pendaftaran->status, ['lulus', 'daftar_ulang'])) {
            return redirect()->to(base_url('dashboard/status'))
                ->with('error', 'Anda tidak memenuhi syarat untuk daftar ulang saat ini.');
        }

        // FIX #3: Cek apakah sudah ada yang DIKONFIRMASI — jika ya, tidak perlu form lagi
        $dikonfirmasi = $this->model->getDikonfirmasiByPendaftaranId($pendaftaran->id);
        if ($dikonfirmasi) {
            return redirect()->to(base_url('dashboard/daftar-ulang/status'))
                ->with('info', 'Daftar ulang Anda sudah dikonfirmasi oleh admin.');
        }

        // FIX #3: Cek apakah ada PENDING — jika ya, tampilkan info tapi form ditutup
        $pending = $this->model->getPendingByPendaftaranId($pendaftaran->id);

        $pendaftaran = $this->pendaftaranModel->getWithRelations($pendaftaran->id);

        // FIX #1: Kelas yang ditampilkan HANYA dari jurusan_diterima_id
        $kelasList = $this->kelasModel->getKelasAktif((int) $pendaftaran->jurusan_diterima_id);

        return $this->render('App\Modules\DaftarUlang\Views\form', [
            'title'       => 'Daftar Ulang',
            'pendaftaran' => $pendaftaran,
            'pending'     => $pending,    // FIX #3: info pengajuan yg sedang diproses
            'kelasList'   => $kelasList,
        ]);
    }

    // =========================================================
    // SUBMIT
    // =========================================================
    /**
     * FIX #3: Selalu INSERT row baru — tidak pernah UPDATE existing.
     * FIX #1: Validasi kelas_id harus berasal dari jurusan_diterima_id.
     */
    public function submit()
    {
        $isAjax = $this->request->isAJAX();

        $userId      = $this->userId();
        $pendaftaran = $this->pendaftaranModel->getByUserId($userId);

        if (! $pendaftaran || ! in_array($pendaftaran->status, ['lulus', 'daftar_ulang'])) {
            return $this->failResponse($isAjax, 403, 'Tidak dapat melakukan daftar ulang.');
        }

        // FIX #3: Sudah dikonfirmasi → tolak submit
        $dikonfirmasi = $this->model->getDikonfirmasiByPendaftaranId($pendaftaran->id);
        if ($dikonfirmasi) {
            return $this->failResponse(
                $isAjax,
                422,
                'Daftar ulang Anda sudah dikonfirmasi, tidak bisa diajukan lagi.',
                base_url('dashboard/daftar-ulang/status')
            );
        }

        // FIX #3: Ada PENDING → tolak submit (cegah double submit)
        $pending = $this->model->getPendingByPendaftaranId($pendaftaran->id);
        if ($pending) {
            return $this->failResponse(
                $isAjax,
                422,
                'Anda sudah memiliki pengajuan daftar ulang yang sedang menunggu konfirmasi admin. Harap tunggu hasilnya.',
                base_url('dashboard/daftar-ulang/status')
            );
        }

        // ── Validasi nominal ──────────────────────────────────────────────
        $nominalBersih = preg_replace('/[^0-9]/', '', $this->request->getPost('nominal_pembayaran') ?? '0');
        if (empty($nominalBersih) || (int) $nominalBersih <= 0) {
            return $this->failResponse($isAjax, 422, 'Nominal pembayaran wajib diisi dan harus lebih dari 0.');
        }

        // ── FIX #1: Validasi kelas_id harus dari jurusan_diterima_id ─────
        $kelasId     = $this->request->getPost('kelas_id') ?: null;
        $pendaftaran = $this->pendaftaranModel->getWithRelations($pendaftaran->id);

        if ($kelasId !== null) {
            $kelas = $this->kelasModel->find($kelasId);
            if (! $kelas) {
                return $this->failResponse($isAjax, 422, 'Kelas yang dipilih tidak valid.');
            }
            if ((int) $kelas->jurusan_id !== (int) $pendaftaran->jurusan_diterima_id) {
                return $this->failResponse(
                    $isAjax,
                    422,
                    'Kelas yang dipilih tidak sesuai dengan jurusan yang Anda terima (' .
                        ($pendaftaran->jurusan_diterima_nama ?? 'jurusan diterima') . ').'
                );
            }
        }

        // ── Validasi file — wajib diupload setiap submit baru ────────────
        if (! $this->validate(['bukti_pembayaran' => 'uploaded[bukti_pembayaran]|max_size[bukti_pembayaran,2048]'])) {
            $errMsg = implode(' ', $this->validator->getErrors());
            return $this->failResponse($isAjax, 422, $errMsg ?: 'File bukti pembayaran tidak valid.');
        }

        // ── Upload file ───────────────────────────────────────────────────
        $file     = $this->request->getFile('bukti_pembayaran');
        $uploader = new FileUploader();

        if (! $file || ! $file->isValid() || $file->hasMoved()) {
            return $this->failResponse($isAjax, 422, 'Bukti pembayaran wajib diupload.');
        }

        $upload = $uploader->upload($file, 'bukti');
        if (! $upload['success']) {
            return $this->failResponse($isAjax, 422, $upload['message']);
        }

        // ── FIX #3: Selalu INSERT row baru ───────────────────────────────
        $this->model->insert([
            'pendaftaran_id'        => $pendaftaran->id,
            'user_id'               => $userId,
            'kelas_id'              => $kelasId,
            'bukti_pembayaran_path' => $upload['path'],
            'nama_file_bukti'       => $upload['original_name'],
            'nominal_pembayaran'    => (int) $nominalBersih,
            'catatan_siswa'         => $this->request->getPost('catatan_siswa') ?? '',
            'status'                => DaftarUlangModel::STATUS_PENDING,
        ]);

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

    // =========================================================
    // STATUS — tampilkan pengajuan terbaru + riwayat semua
    // =========================================================
    public function status()
    {
        $userId      = $this->userId();
        $pendaftaran = $this->pendaftaranModel->getByUserId($userId);

        // FIX #3: tampilkan pengajuan terbaru dan semua riwayat
        $daftarUlang = null;
        $riwayat     = [];
        if ($pendaftaran) {
            $daftarUlang = $this->model->getByPendaftaranId($pendaftaran->id); // terbaru
            $riwayat     = $this->model->getHistoryByPendaftaranId($pendaftaran->id);
        }

        return $this->render('App\Modules\DaftarUlang\Views\status', [
            'title'       => 'Status Daftar Ulang',
            'daftarUlang' => $daftarUlang,
            'riwayat'     => $riwayat,
            'pendaftaran' => $pendaftaran,
        ]);
    }

    // =========================================================
    // STREAM BUKTI
    // =========================================================
    public function streamBukti(int $id)
    {
        $userId      = $this->userId();
        $pendaftaran = $this->pendaftaranModel->getByUserId($userId);

        if (! $pendaftaran) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $daftarUlang = $this->model->find($id);

        if (! $daftarUlang || (int) $daftarUlang->pendaftaran_id !== (int) $pendaftaran->id) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        if (! $daftarUlang->bukti_pembayaran_path) {
            return redirect()->back()->with('error', 'File bukti tidak ditemukan.');
        }

        $uploader = new FileUploader();
        $namaFile = basename($daftarUlang->bukti_pembayaran_path);

        try {
            $uploader->stream($namaFile, 'bukti');
        } catch (\RuntimeException $e) {
            return redirect()->back()->with('error', 'File tidak dapat dibuka.');
        }
    }

    // =========================================================
    // HELPER
    // =========================================================
    private function failResponse(bool $isAjax, int $code, string $message, string $redirect = ''): \CodeIgniter\HTTP\ResponseInterface|false
    {
        if ($isAjax) {
            $payload = ['success' => false, 'message' => $message];
            if ($redirect) {
                $payload['redirect'] = $redirect;
            }
            return $this->response->setStatusCode($code)->setJSON($payload);
        }

        if ($redirect) {
            return redirect()->to($redirect)->with('error', $message);
        }

        return redirect()->back()->withInput()->with('error', $message);
    }
}
