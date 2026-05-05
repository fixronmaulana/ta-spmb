<?php

namespace App\Modules\Verifikasi\Controllers;

use App\Controllers\BaseController;
use App\Modules\Verifikasi\Services\VerifikasiService;
use App\Modules\Verifikasi\Models\VerifikasiModel;
use App\Modules\Pendaftaran\Models\PendaftaranModel;
use App\Modules\Pendaftaran\Models\DataDiriSiswaModel;
use App\Modules\Pendaftaran\Models\DokumenModel;
use App\Modules\MasterData\Models\JurusanModel;
use App\Modules\Notifikasi\Services\NotifikasiService;
use App\Libraries\FileUploader;

class VerifikasiController extends BaseController
{
    protected VerifikasiService  $service;
    protected PendaftaranModel   $pendaftaranModel;
    protected DataDiriSiswaModel $dataDiriModel;
    protected DokumenModel       $dokumenModel;
    protected VerifikasiModel    $verifikasiModel;
    protected JurusanModel       $jurusanModel;
    protected NotifikasiService  $notifService;

    public function __construct()
    {
        $this->service          = new VerifikasiService();
        $this->pendaftaranModel = new PendaftaranModel();
        $this->dataDiriModel    = new DataDiriSiswaModel();
        $this->dokumenModel     = new DokumenModel();
        $this->verifikasiModel  = new VerifikasiModel();
        $this->jurusanModel     = new JurusanModel();
        $this->notifService     = new NotifikasiService();
    }

    // =========================================================
    // INDEX
    // FIX: pakai db_connect() langsung agar tidak ada state
    //      WHERE carryover dari Model query builder.
    //      COALESCE(dds.nama_lengkap, u.nama_lengkap) sebagai
    //      nama_tampil — aman meski data_diri belum diisi siswa.
    // =========================================================
    public function index()
    {
        $status        = $this->request->getGet('status')  ?? 'submitted';
        $search        = $this->request->getGet('search')  ?? '';
        $jurusanFilter = $this->request->getGet('jurusan') ?? '';
        $perPage       = 20;
        $db            = db_connect();

        $builder = $db->table('pendaftaran')
            ->select('
                pendaftaran.id,
                pendaftaran.no_pendaftaran,
                pendaftaran.status,
                pendaftaran.submitted_at,
                u.nama_lengkap  AS nama_calon,
                u.email         AS email_calon,
                COALESCE(dds.nama_lengkap, u.nama_lengkap) AS nama_tampil,
                dds.asal_sekolah,
                j1.nama         AS jurusan_pilihan1_nama,
                j1.kode         AS jurusan_pilihan1_kode
            ')
            ->join('users u',              'u.id = pendaftaran.user_id')
            ->join('data_diri_siswas dds', 'dds.pendaftaran_id = pendaftaran.id', 'left')
            ->join('jurusan j1',           'j1.id = pendaftaran.jurusan_pilihan1_id', 'left')
            ->where('pendaftaran.deleted_at IS NULL', null, false);

        if ($status === 'all') {
            $builder->whereNotIn('pendaftaran.status', ['draft']);
        } else {
            $builder->where('pendaftaran.status', $status);
        }

        if ($search) {
            $builder->groupStart()
                ->like('u.nama_lengkap',              $search)
                ->orLike('dds.nama_lengkap',          $search)
                ->orLike('pendaftaran.no_pendaftaran', $search)
                ->orLike('u.email',                   $search)
                ->groupEnd();
        }

        if ($jurusanFilter) {
            $builder->where('j1.kode', $jurusanFilter);
        }

        $builder->orderBy('pendaftaran.submitted_at', 'DESC');

        // Hitung total dulu (false = jangan reset query)
        $total       = $builder->countAllResults(false);
        $currentPage = max(1, (int)($this->request->getGet('page') ?? 1));
        $offset      = ($currentPage - 1) * $perPage;
        $totalPages  = $total > 0 ? (int)ceil($total / $perPage) : 1;

        $pendaftarans = $builder->limit($perPage, $offset)->get()->getResultObject();

        // Badge count per status
        $badges = [];
        foreach (['submitted', 'verifikasi', 'seleksi', 'all'] as $s) {
            $bq = $db->table('pendaftaran')->where('deleted_at IS NULL', null, false);
            if ($s === 'all') {
                $bq->whereNotIn('status', ['draft']);
            } else {
                $bq->where('status', $s);
            }
            $badges[$s] = (int)$bq->countAllResults();
        }

        $jurusans = $this->jurusanModel->orderBy('kode')->findAll();

        return $this->render('App\Modules\Verifikasi\Views\index', [
            'title'         => 'Verifikasi Dokumen',
            'pendaftarans'  => $pendaftarans,
            'total'         => $total,
            'currentPage'   => $currentPage,
            'totalPages'    => $totalPages,
            'perPage'       => $perPage,
            'statusFilter'  => $status,
            'search'        => $search,
            'jurusanFilter' => $jurusanFilter,
            'jurusans'      => $jurusans,
            'badges'        => $badges,
        ]);
    }

    // =========================================================
    // DETAIL
    // FIX: tambah prevId/nextId navigasi antar pendaftar
    // =========================================================
    public function detail(int $id)
    {
        $pendaftaran = $this->pendaftaranModel->getWithRelations($id);

        if (! $pendaftaran) {
            return redirect()->to(base_url('admin/verifikasi'))
                ->with('error', 'Pendaftaran tidak ditemukan.');
        }

        $dataDiri = $this->dataDiriModel->getByPendaftaranId($id);
        $dokumens = $this->dokumenModel->getByPendaftaranId($id);
        $logs     = $this->verifikasiModel->getByPendaftaran($id);
        $prevNext = $this->pendaftaranModel->getPrevNextId($id, 'all');

        $dokumenStats = [
            'total'    => count($dokumens),
            'approved' => count(array_filter($dokumens, fn($d) => $d->status_verifikasi === 'approved')),
            'rejected' => count(array_filter($dokumens, fn($d) => $d->status_verifikasi === 'rejected')),
            'pending'  => count(array_filter($dokumens, fn($d) => $d->status_verifikasi === 'pending')),
        ];

        $canApproveSemua = $dokumenStats['total'] > 0
            && $dokumenStats['pending']  === 0
            && $dokumenStats['rejected'] === 0;

        return $this->render('App\Modules\Verifikasi\Views\detail', [
            'title'           => 'Detail Verifikasi — ' . ($pendaftaran->no_pendaftaran ?? '#' . $id),
            'pendaftaran'     => $pendaftaran,
            'dataDiri'        => $dataDiri,
            'dokumens'        => $dokumens,
            'logs'            => $logs,
            'dokumenStats'    => $dokumenStats,
            'canApproveSemua' => $canApproveSemua,
            'prevId'          => $prevNext['prev'],
            'nextId'          => $prevNext['next'],
        ]);
    }

    // =========================================================
    // APPROVE DOKUMEN (AJAX)
    // =========================================================
    public function approveDokumen(int $pendaftaranId)
    {
        $dokumenId = (int)$this->request->getPost('dokumen_id');
        $result    = $this->service->approveDokumen($pendaftaranId, $dokumenId, $this->userId());

        if ($this->request->isAJAX()) {
            return $result['success']
                ? $this->jsonSuccess($result['message'])
                : $this->jsonError($result['message']);
        }
        return redirect()->back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    // =========================================================
    // REJECT DOKUMEN (AJAX)
    // =========================================================
    public function rejectDokumen(int $pendaftaranId)
    {
        $dokumenId = (int)$this->request->getPost('dokumen_id');
        $catatan   = trim($this->request->getPost('catatan') ?? '');

        if (empty($catatan)) {
            return $this->jsonError('Catatan penolakan wajib diisi.');
        }

        $result = $this->service->rejectDokumen($pendaftaranId, $dokumenId, $this->userId(), $catatan);

        if ($this->request->isAJAX()) {
            return $result['success']
                ? $this->jsonSuccess($result['message'])
                : $this->jsonError($result['message']);
        }
        return redirect()->back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    // =========================================================
    // APPROVE SEMUA + MASUK SELEKSI
    // =========================================================
    public function approveSemua(int $pendaftaranId)
    {
        $result = $this->service->approveSemua($pendaftaranId, $this->userId());

        if ($this->request->isAJAX()) {
            return $result['success']
                ? $this->jsonSuccess($result['message'])
                : $this->jsonError($result['message']);
        }
        return redirect()->to(base_url("admin/verifikasi/{$pendaftaranId}"))
            ->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    // =========================================================
    // KIRIM CATATAN ADMIN (AJAX) — method baru
    // =========================================================
    public function kirimCatatan(int $pendaftaranId)
    {
        $catatan = trim($this->request->getPost('catatan') ?? '');

        if (empty($catatan)) {
            return $this->jsonError('Catatan tidak boleh kosong.');
        }

        $pendaftaran = $this->pendaftaranModel->find($pendaftaranId);
        if (! $pendaftaran) {
            return $this->jsonError('Pendaftaran tidak ditemukan.');
        }

        // Simpan catatan ke kolom catatan_admin
        $this->pendaftaranModel->updateStatus($pendaftaranId, $pendaftaran->status, [
            'catatan_admin' => $catatan,
        ]);

        // Log
        $this->verifikasiModel->log($pendaftaranId, $this->userId(), 'kirim_catatan', [
            'keterangan' => $catatan,
        ]);

        // Notifikasi ke calon siswa
        $this->notifService->send(
            $pendaftaran->user_id,
            'catatan_admin',
            'Catatan dari Admin',
            $catatan,
            ['url' => base_url('dashboard/status')]
        );

        return $this->jsonSuccess('Catatan berhasil dikirim ke calon siswa.');
    }

    // =========================================================
    // TOLAK PENDAFTARAN — minta revisi
    // =========================================================
    public function tolakPendaftaran(int $pendaftaranId)
    {
        $alasan = trim($this->request->getPost('alasan') ?? '');

        if (empty($alasan)) {
            return redirect()->back()->with('error', 'Alasan penolakan wajib diisi.');
        }

        $result = $this->service->tolakPendaftaran($pendaftaranId, $this->userId(), $alasan);

        return redirect()->to(base_url("admin/verifikasi/{$pendaftaranId}"))
            ->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    // =========================================================
    // STREAM DOKUMEN (secure file access)
    // =========================================================
    public function streamDokumen(int $dokumenId)
    {
        $dokumen = $this->dokumenModel->find($dokumenId);

        if (! $dokumen) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Dokumen tidak ditemukan.');
        }

        $this->authorize('admin_tu', 'kepala_sekolah');

        $uploader = new FileUploader();

        try {
            $uploader->stream($dokumen->nama_file_simpan, 'dokumen', $dokumen->tipe_mime);
        } catch (\RuntimeException $e) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException($e->getMessage());
        }
    }
}
