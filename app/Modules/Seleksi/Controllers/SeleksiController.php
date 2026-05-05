<?php

namespace App\Modules\Seleksi\Controllers;

use App\Controllers\BaseController;
use App\Modules\Seleksi\Models\SeleksiModel;
use App\Modules\Pendaftaran\Models\PendaftaranModel;
use App\Modules\MasterData\Models\JurusanModel;
use App\Modules\MasterData\Models\PeriodeModel;
use App\Modules\Notifikasi\Services\NotifikasiService;

class SeleksiController extends BaseController
{
    protected SeleksiModel      $seleksiModel;
    protected PendaftaranModel  $pendaftaranModel;
    protected JurusanModel      $jurusanModel;
    protected PeriodeModel      $periodeModel;
    protected NotifikasiService $notifService;

    public function __construct()
    {
        $this->seleksiModel     = new SeleksiModel();
        $this->pendaftaranModel = new PendaftaranModel();
        $this->jurusanModel     = new JurusanModel();
        $this->periodeModel     = new PeriodeModel();
        $this->notifService     = new NotifikasiService();
    }

    // =========================================================
    // INDEX — Daftar peserta seleksi (Penetapan Kelulusan)
    // =========================================================
    public function index()
    {
        $peserta  = $this->seleksiModel->getForSeleksiByJurusan();
        $jurusans = $this->jurusanModel->getAllActive();

        // Kelompokkan per jurusan + hitung count_lulus untuk quota cards
        $byJurusan = [];
        foreach ($jurusans as $j) {
            $byJurusan[$j->id] = [
                'jurusan'     => $j,
                'peserta'     => [],
                'kuota'       => (int) $j->kuota,
                'count_lulus' => 0,
            ];
        }

        foreach ($peserta as $p) {
            $jid = $p->jurusan_pilihan1_id;
            if (isset($byJurusan[$jid])) {
                $byJurusan[$jid]['peserta'][] = $p;
                if ($p->status === 'lulus') {
                    $byJurusan[$jid]['count_lulus']++;
                }
            }
        }

        return $this->render('App\Modules\Seleksi\Views\index', [
            'title'     => 'Penetapan Kelulusan',
            'peserta'   => $peserta,
            'jurusans'  => $jurusans,
            'byJurusan' => $byJurusan,
        ]);
    }

    // =========================================================
    // TETAPKAN LULUS / TIDAK LULUS (POST)
    // Langsung final — tidak perlu persetujuan siapa pun
    // =========================================================
    public function tetapkan()
    {
        $lulusIds        = $this->request->getPost('lulus_ids')       ?? [];
        $tidakLulusIds   = $this->request->getPost('tidak_lulus_ids') ?? [];
        $jurusanDiterima = $this->request->getPost('jurusan_diterima') ?? [];

        if (empty($lulusIds) && empty($tidakLulusIds)) {
            return redirect()->back()->with('error', 'Tidak ada data yang dipilih.');
        }

        // Build jurusan diterima map: [pendaftaran_id => jurusan_id]
        $jurusanMap = [];
        foreach ($jurusanDiterima as $pendId => $jurusanId) {
            if ($jurusanId) {
                $jurusanMap[(int) $pendId] = (int) $jurusanId;
            }
        }

        $result = $this->seleksiModel->tetapkanLulus(
            array_map('intval', $lulusIds),
            array_map('intval', $tidakLulusIds),
            $jurusanMap
        );

        if (! $result) {
            return redirect()->back()->with('error', 'Gagal menyimpan hasil seleksi. Coba lagi.');
        }

        // Notifikasi ke calon siswa yang LULUS — langsung final dari panitia
        foreach ($lulusIds as $pendId) {
            $pend = $this->pendaftaranModel->find((int) $pendId);
            if ($pend) {
                $this->notifService->send(
                    $pend->user_id,
                    'hasil_seleksi_lulus',
                    'Selamat! Anda Dinyatakan Lulus Seleksi',
                    'Panitia PPDB SMK Al-Munawwir telah menetapkan bahwa Anda LULUS seleksi penerimaan peserta didik baru. Silakan pantau pengumuman resmi untuk informasi daftar ulang.',
                    ['url' => base_url('dashboard/status')]
                );
            }
        }

        // Notifikasi ke calon siswa yang TIDAK LULUS
        foreach ($tidakLulusIds as $pendId) {
            $pend = $this->pendaftaranModel->find((int) $pendId);
            if ($pend) {
                $this->notifService->send(
                    $pend->user_id,
                    'hasil_seleksi_tidak_lulus',
                    'Hasil Seleksi PPDB SMK Al-Munawwir',
                    'Mohon maaf, berdasarkan hasil seleksi yang telah dilakukan oleh panitia PPDB SMK Al-Munawwir, Anda belum dapat diterima pada periode ini. Terima kasih telah mendaftar.',
                    ['url' => base_url('dashboard/status')]
                );
            }
        }

        $total = count($lulusIds) + count($tidakLulusIds);
        return redirect()->to(base_url('admin/seleksi'))
            ->with('success', "Hasil seleksi {$total} peserta berhasil ditetapkan oleh panitia.");
    }

    // =========================================================
    // PUBLISH Pengumuman Resmi (Admin TU)
    // =========================================================
    public function publish()
    {
        $periodeId = $this->request->getPost('periode_id');
        $periode   = $this->periodeModel->find($periodeId);

        if (! $periode) {
            return redirect()->back()->with('error', 'Periode tidak ditemukan.');
        }

        $this->periodeModel->publish($periodeId);

        // Notif massal ke semua calon siswa yang sudah diseleksi
        $pendaftarans = $this->pendaftaranModel
            ->whereIn('status', ['lulus', 'tidak_lulus'])
            ->findAll();

        foreach ($pendaftarans as $p) {
            if ($p->status === 'lulus') {
                $msg = '🎉 Selamat! Anda resmi DITERIMA di SMK Al-Munawwir. Segera lakukan daftar ulang sesuai jadwal yang telah ditentukan oleh pihak sekolah.';
            } else {
                $msg = 'Pengumuman resmi PPDB SMK Al-Munawwir telah diterbitkan. Mohon maaf, Anda belum dapat diterima pada periode ini. Terima kasih atas kepercayaan Anda kepada SMK Al-Munawwir.';
            }

            $this->notifService->send(
                $p->user_id,
                'pengumuman_kelulusan',
                'Pengumuman Resmi PPDB SMK Al-Munawwir',
                $msg,
                ['url' => base_url('dashboard/pengumuman')]
            );
        }

        return redirect()->to(base_url('admin/seleksi'))
            ->with('success', 'Pengumuman resmi PPDB berhasil dipublikasikan oleh panitia!');
    }
}
