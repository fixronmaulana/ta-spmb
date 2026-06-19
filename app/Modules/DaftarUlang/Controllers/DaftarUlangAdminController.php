<?php

namespace App\Modules\DaftarUlang\Controllers;

use App\Controllers\BaseController;
use App\Modules\DaftarUlang\Models\DaftarUlangModel;
use App\Modules\Pendaftaran\Models\PendaftaranModel;
use App\Modules\MasterData\Models\KelasModel;
use App\Modules\Notifikasi\Services\NotifikasiService;
use App\Libraries\FileUploader;

class DaftarUlangAdminController extends BaseController
{
    protected DaftarUlangModel  $model;
    protected PendaftaranModel  $pendaftaranModel;
    protected KelasModel        $kelasModel;
    protected NotifikasiService $notifService;

    public function __construct()
    {
        $this->model            = new DaftarUlangModel();
        $this->pendaftaranModel = new PendaftaranModel();
        $this->kelasModel       = new KelasModel();
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

        // FIX #2: Kelas dikelompokkan per jurusan_id untuk dropdown admin.
        // Diambil semua jurusan agar fleksibel, tapi saat render modal
        // JS hanya menampilkan kelas dari jurusan_diterima_id (bukan pilihan1/2).
        $kelasList      = $this->kelasModel->getWithJurusan();
        $kelasByJurusan = [];
        foreach ($kelasList as $k) {
            $kelasByJurusan[$k->jurusan_id][] = [
                'id'   => $k->id,
                'nama' => $k->nama,
            ];
        }

        return $this->render('App\Modules\DaftarUlang\Views\admin_index', [
            'title'          => 'Verifikasi Daftar Ulang',
            'daftars'        => $daftars,
            'status'         => $status,
            'search'         => $search,
            'stats'          => $stats,
            'kelasByJurusan' => $kelasByJurusan,
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
    // FIX #2: Validasi kelas_id wajib dari jurusan_diterima_id
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

        // Ambil data pendaftaran untuk validasi jurusan diterima
        $pendaftaran = $this->pendaftaranModel->find($daftarUlang->pendaftaran_id);
        if (! $pendaftaran) {
            return redirect()->back()->with('error', 'Data pendaftaran tidak ditemukan.');
        }

        $kelasId   = $this->request->getPost('kelas_id') ?: null;
        $namaKelas = trim($this->request->getPost('nama_kelas') ?? '');

        // FIX #2: Validasi kelas_id harus dari jurusan_diterima_id
        if ($kelasId !== null) {
            $kelas = $this->kelasModel->find($kelasId);

            if (! $kelas) {
                return redirect()->back()
                    ->with('error', 'Kelas yang dipilih tidak ditemukan di database.');
            }

            if ((int) $kelas->jurusan_id !== (int) $pendaftaran->jurusan_diterima_id) {
                // Ambil nama jurusan diterima untuk pesan error yang informatif
                $jurusanDiterima = (new \App\Modules\MasterData\Models\JurusanModel())->find($pendaftaran->jurusan_diterima_id);
                $namaJurusanDiterima = $jurusanDiterima->nama ?? 'jurusan yang diterima';

                return redirect()->back()
                    ->with('error', "Kelas '{$kelas->nama}' tidak termasuk dalam jurusan '{$namaJurusanDiterima}'. " .
                        "Admin hanya boleh menetapkan kelas dari jurusan yang diterima siswa.");
            }

            // Ambil nama_kelas dari DB jika tidak dikirim dari form
            if (empty($namaKelas)) {
                $namaKelas = $kelas->nama;
            }
        }

        // Update status → dikonfirmasi
        $this->model->update($id, [
            'status'            => DaftarUlangModel::STATUS_DIKONFIRMASI,
            'nama_kelas'        => $namaKelas ?: null,
            'kelas_id'          => $kelasId ?: null,
            'catatan_admin'     => $this->request->getPost('catatan_admin') ?? '',
            'dikonfirmasi_oleh' => $this->userId(),
            'dikonfirmasi_pada' => date('Y-m-d H:i:s'),
        ]);

        // Status pendaftaran tetap 'daftar_ulang' hingga konversi ke Buku Induk
        if ($pendaftaran->status === 'lulus') {
            $this->pendaftaranModel->updateStatus($daftarUlang->pendaftaran_id, 'daftar_ulang');
        }

        $pesanNotif = 'Pembayaran daftar ulang Anda telah dikonfirmasi!'
            . ($namaKelas ? " Penempatan kelas: {$namaKelas}." : '')
            . ' Data Anda sedang diproses ke Buku Induk oleh Admin TU. NIS akan diberikan setelah proses selesai.';

        $this->notifService->send(
            $daftarUlang->user_id,
            'daftar_ulang_dikonfirmasi',
            'Pembayaran Dikonfirmasi — Proses Buku Induk Sedang Berjalan',
            $pesanNotif,
            ['url' => base_url('dashboard/daftar-ulang/status')]
        );

        $msgKelas = $namaKelas ? " Penempatan kelas: {$namaKelas}." : '';
        return redirect()->to(base_url('admin/daftar-ulang'))
            ->with('success', "Daftar ulang berhasil dikonfirmasi.{$msgKelas} NIS akan digenerate otomatis saat konversi ke Buku Induk.");
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

        if ($daftarUlang->status !== DaftarUlangModel::STATUS_PENDING) {
            return redirect()->back()->with('error', 'Pengajuan ini sudah diproses sebelumnya.');
        }

        $catatan = trim($this->request->getPost('catatan_admin') ?? '');

        if (empty($catatan)) {
            return redirect()->back()->with('error', 'Alasan penolakan wajib diisi.');
        }

        $this->model->update($id, [
            'status'        => DaftarUlangModel::STATUS_DITOLAK,
            'catatan_admin' => $catatan,
        ]);

        // Pastikan status pendaftaran = 'daftar_ulang' agar siswa bisa submit baru
        $pendaftaran = $this->pendaftaranModel->find($daftarUlang->pendaftaran_id);
        if ($pendaftaran && $pendaftaran->status === 'lulus') {
            $this->pendaftaranModel->updateStatus($daftarUlang->pendaftaran_id, 'daftar_ulang');
        }

        $this->notifService->send(
            $daftarUlang->user_id,
            'daftar_ulang_ditolak',
            'Bukti Pembayaran Ditolak — Mohon Upload Ulang',
            'Bukti pembayaran daftar ulang Anda ditolak: ' . $catatan . '. Silakan login dan ajukan daftar ulang baru.',
            ['url' => base_url('dashboard/daftar-ulang')]
        );

        return redirect()->to(base_url('admin/daftar-ulang'))
            ->with('success', 'Pengajuan ditolak. Notifikasi terkirim ke calon siswa.');
    }
}
