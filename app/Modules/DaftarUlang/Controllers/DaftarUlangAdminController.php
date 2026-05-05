<?php

namespace App\Modules\DaftarUlang\Controllers;

use App\Controllers\BaseController;
use App\Modules\DaftarUlang\Models\DaftarUlangModel;
use App\Modules\Pendaftaran\Models\PendaftaranModel;
use App\Modules\Notifikasi\Services\NotifikasiService;
use App\Libraries\FileUploader;

class DaftarUlangAdminController extends BaseController
{
    protected DaftarUlangModel  $model;
    protected PendaftaranModel  $pendaftaranModel;
    protected NotifikasiService $notifService;

    public function __construct()
    {
        $this->model            = new DaftarUlangModel();
        $this->pendaftaranModel = new PendaftaranModel();
        $this->notifService     = new NotifikasiService();
    }

    // =========================================================
    // INDEX
    // =========================================================
    public function index()
    {
        $status  = $this->request->getGet('status') ?? '';
        $search  = trim($this->request->getGet('search') ?? '');
        $daftars = $this->model->getAllWithRelations($status, $search);
        $stats   = $this->model->getStatsByStatus();

        return $this->render('App\Modules\DaftarUlang\Views\admin_index', [
            'title'   => 'Verifikasi Daftar Ulang',
            'daftars' => $daftars,
            'status'  => $status,
            'search'  => $search,
            'stats'   => $stats,
        ]);
    }

    // =========================================================
    // DETAIL (AJAX)
    // =========================================================
    public function detail(int $id)
    {
        $daftarUlang = $this->model->getWithRelations($id);

        if (! $daftarUlang) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(404)
                    ->setJSON(['success' => false, 'message' => 'Data tidak ditemukan.']);
            }
            return redirect()->back()->with('error', 'Data tidak ditemukan.');
        }

        return $this->response->setJSON(['success' => true, 'data' => $daftarUlang]);
    }

    // =========================================================
    // STREAM BUKTI PEMBAYARAN
    // =========================================================
    public function streamBukti(int $id)
    {
        $daftarUlang = $this->model->find($id);

        if (! $daftarUlang || ! $daftarUlang->bukti_pembayaran_path) {
            return redirect()->back()->with('error', 'File bukti tidak ditemukan.');
        }

        $uploader = new FileUploader();
        $namaFile = basename($daftarUlang->bukti_pembayaran_path);

        try {
            $uploader->stream($namaFile, 'bukti');
        } catch (\RuntimeException $e) {
            return redirect()->back()->with('error', 'File tidak dapat dibuka: ' . $e->getMessage());
        }
    }

    // =========================================================
    // KONFIRMASI
    // FIX: Tambahkan validasi uniqueness NIS sebelum menyimpan.
    //      NIS yang sama tidak boleh diberikan ke dua siswa berbeda.
    //      Tanpa validasi ini, dua siswa dengan NIS sama akan menyebabkan
    //      Duplicate entry error saat konversi ke Buku Induk.
    // =========================================================
    public function konfirmasi(int $id)
    {
        $daftarUlang = $this->model->find($id);

        if (! $daftarUlang) {
            return redirect()->back()->with('error', 'Data tidak ditemukan.');
        }

        if ($daftarUlang->status !== DaftarUlangModel::STATUS_PENDING) {
            return redirect()->back()->with('error', 'Pengajuan ini sudah diproses sebelumnya.');
        }

        $nis       = trim($this->request->getPost('nis') ?? '');
        $namaKelas = trim($this->request->getPost('nama_kelas') ?? '');
        $kelasId   = $this->request->getPost('kelas_id') ?: $daftarUlang->kelas_id;

        if (empty($nis)) {
            return redirect()->back()->with('error', 'NIS wajib diisi sebelum mengkonfirmasi.');
        }

        // ── FIX: Validasi uniqueness NIS ────────────────────────────────────
        // Cek apakah NIS sudah dipakai di buku_induks (siswa yang sudah dikonversi)
        $db = db_connect();

        $nisInBukuInduk = $db->table('buku_induks')
            ->where('nis', $nis)
            ->countAllResults();

        if ($nisInBukuInduk > 0) {
            return redirect()->back()
                ->withInput()
                ->with('error', "NIS '{$nis}' sudah digunakan oleh siswa lain di Buku Induk. Gunakan NIS yang berbeda.");
        }

        // Cek apakah NIS sudah dipakai di daftar_ulangs lain (dikonfirmasi, bukan record ini sendiri)
        $nisInDaftarUlang = $db->table('daftar_ulangs')
            ->where('nis', $nis)
            ->where('status', DaftarUlangModel::STATUS_DIKONFIRMASI)
            ->where('id !=', $id)
            ->countAllResults();

        if ($nisInDaftarUlang > 0) {
            return redirect()->back()
                ->withInput()
                ->with('error', "NIS '{$nis}' sudah ditetapkan untuk calon siswa lain yang sedang diproses. Gunakan NIS yang berbeda.");
        }
        // ────────────────────────────────────────────────────────────────────

        // Update status daftar_ulangs → dikonfirmasi
        $this->model->update($id, [
            'status'            => DaftarUlangModel::STATUS_DIKONFIRMASI,
            'nis'               => $nis,
            'nama_kelas'        => $namaKelas ?: null,
            'kelas_id'          => $kelasId ?: null,
            'catatan_admin'     => $this->request->getPost('catatan_admin') ?? '',
            'dikonfirmasi_oleh' => $this->userId(),
            'dikonfirmasi_pada' => date('Y-m-d H:i:s'),
        ]);

        // Status pendaftaran tetap 'daftar_ulang' hingga admin konversi ke Buku Induk
        $pendaftaran = $this->pendaftaranModel->find($daftarUlang->pendaftaran_id);
        if ($pendaftaran && $pendaftaran->status === 'lulus') {
            $this->pendaftaranModel->updateStatus($daftarUlang->pendaftaran_id, 'daftar_ulang');
        }

        // Notif ke siswa
        $pesanNotif = "Pembayaran daftar ulang Anda telah dikonfirmasi! "
            . "NIS sementara: {$nis}"
            . ($namaKelas ? ", Kelas: {$namaKelas}" : "")
            . ". Data Anda sedang diproses ke Buku Induk oleh Admin TU.";

        $this->notifService->send(
            $daftarUlang->user_id,
            'daftar_ulang_dikonfirmasi',
            'Pembayaran Dikonfirmasi — Proses Buku Induk Sedang Berjalan',
            $pesanNotif,
            ['url' => base_url('dashboard/daftar-ulang/status')]
        );

        return redirect()->to(base_url('admin/daftar-ulang'))
            ->with('success', "Daftar ulang berhasil dikonfirmasi. NIS {$nis} telah ditetapkan. Silakan konversi ke Buku Induk.");
    }

    // =========================================================
    // TOLAK
    // =========================================================
    public function tolak(int $id)
    {
        $daftarUlang = $this->model->find($id);

        if (! $daftarUlang) {
            return redirect()->back()->with('error', 'Data tidak ditemukan.');
        }

        $catatan = trim($this->request->getPost('catatan_admin') ?? '');

        if (empty($catatan)) {
            return redirect()->back()->with('error', 'Alasan penolakan wajib diisi.');
        }

        $this->model->update($id, [
            'status'        => DaftarUlangModel::STATUS_DITOLAK,
            'catatan_admin' => $catatan,
        ]);

        // Pastikan status pendaftaran = 'daftar_ulang' agar siswa bisa upload ulang
        $pendaftaran = $this->pendaftaranModel->find($daftarUlang->pendaftaran_id);
        if ($pendaftaran && $pendaftaran->status === 'lulus') {
            $this->pendaftaranModel->updateStatus($daftarUlang->pendaftaran_id, 'daftar_ulang');
        }

        $this->notifService->send(
            $daftarUlang->user_id,
            'daftar_ulang_ditolak',
            'Bukti Pembayaran Ditolak — Mohon Upload Ulang',
            'Bukti pembayaran daftar ulang Anda ditolak: ' . $catatan . '. Silakan login dan upload ulang bukti pembayaran.',
            ['url' => base_url('dashboard/daftar-ulang')]
        );

        return redirect()->to(base_url('admin/daftar-ulang'))
            ->with('success', 'Pengajuan ditolak. Notifikasi terkirim ke calon siswa.');
    }
}
