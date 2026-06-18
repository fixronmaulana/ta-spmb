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

        // Hitung jumlah lulus per jurusan dari jurusan_diterima_id
        // (bukan dari jurusan_pilihan1_id agar akurat)
        $lulusPerJurusan = $this->seleksiModel->getCountLulusPerJurusan();

        // Kelompokkan peserta per jurusan_pilihan1_id untuk tampilan tabel
        $byJurusan = [];
        foreach ($jurusans as $j) {
            $byJurusan[$j->id] = [
                'jurusan'     => $j,
                'peserta'     => [],
                'kuota'       => (int) $j->kuota,
                'count_lulus' => $lulusPerJurusan[$j->id] ?? 0,
            ];
        }

        foreach ($peserta as $p) {
            $jid = $p->jurusan_pilihan1_id;
            if (isset($byJurusan[$jid])) {
                $byJurusan[$jid]['peserta'][] = $p;
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
    //
    // PERBAIKAN:
    // - Setiap pendaftar yang dilulus kan WAJIB disertai jurusan_diterima
    //   yang dikirim via hidden input: jurusan_diterima[{id}]
    // - Jika jurusan_diterima tidak dipilih untuk siswa lulus,
    //   default ke jurusan_pilihan1_id-nya
    //
    // GUARD KEAMANAN (PENTING):
    // - Siswa yang statusnya sudah 'daftar_ulang' atau 'siswa_aktif' TIDAK
    //   BISA diubah lagi melalui endpoint ini, baik individu maupun bulk.
    // - Validasi dilakukan di 2 lapis: di sini (controller, untuk pesan yang
    //   jelas ke admin) dan di SeleksiModel::tetapkanLulus() (query-level
    //   guard, defense-in-depth jika ada yang mencoba bypass UI).
    // - Notifikasi HANYA dikirim untuk id yang benar-benar berhasil diupdate
    //   (lulus_editable_ids / tidak_lulus_editable_ids dari hasil model),
    //   bukan dari id mentah yang dikirim user, supaya siswa yang sudah
    //   terkunci tidak menerima notifikasi yang menyesatkan.
    // =========================================================
    public function tetapkan()
    {
        $lulusIds        = $this->request->getPost('lulus_ids')       ?? [];
        $tidakLulusIds   = $this->request->getPost('tidak_lulus_ids') ?? [];
        $jurusanDiterima = $this->request->getPost('jurusan_diterima') ?? [];

        if (empty($lulusIds) && empty($tidakLulusIds)) {
            return redirect()->back()->with('error', 'Tidak ada data yang dipilih.');
        }

        $lulusIdsInt      = array_map('intval', $lulusIds);
        $tidakLulusIdsInt = array_map('intval', $tidakLulusIds);

        // Build jurusan_diterima map: [pendaftaran_id => jurusan_id]
        $jurusanMap = [];
        foreach ($jurusanDiterima as $pendId => $jurusanId) {
            if ($jurusanId) {
                $jurusanMap[(int) $pendId] = (int) $jurusanId;
            }
        }

        // Untuk siswa lulus yang tidak ada jurusan_diterima di POST,
        // fallback ke jurusan_pilihan1_id dari DB
        foreach ($lulusIdsInt as $pendId) {
            if (! isset($jurusanMap[$pendId])) {
                $pend = $this->pendaftaranModel->find($pendId);
                if ($pend && $pend->jurusan_pilihan1_id) {
                    $jurusanMap[$pendId] = (int) $pend->jurusan_pilihan1_id;
                }
            }
        }

        $result = $this->seleksiModel->tetapkanLulus($lulusIdsInt, $tidakLulusIdsInt, $jurusanMap);

        if (! $result['success']) {
            return redirect()->back()->with('error', 'Gagal menyimpan hasil seleksi. Coba lagi.');
        }

        // Notifikasi ke calon siswa yang BENAR-BENAR berhasil di-set LULUS
        // (pakai lulus_editable_ids dari model, bukan $lulusIdsInt mentah,
        // supaya siswa yang sudah terkunci tidak ikut dapat notifikasi)
        foreach ($result['lulus_editable_ids'] as $pendId) {
            $pend = $this->pendaftaranModel->find($pendId);
            if ($pend) {
                $jurusanNama = '';
                if (isset($jurusanMap[$pendId])) {
                    $jur = $this->jurusanModel->find($jurusanMap[$pendId]);
                    $jurusanNama = $jur ? " di jurusan {$jur->nama}" : '';
                }

                $this->notifService->send(
                    $pend->user_id,
                    'hasil_seleksi_lulus',
                    'Selamat! Anda Dinyatakan Lulus Seleksi',
                    "Panitia PPDB SMK Al-Munawwir telah menetapkan bahwa Anda LULUS seleksi{$jurusanNama}. Silakan pantau pengumuman resmi untuk informasi daftar ulang.",
                    ['url' => base_url('dashboard/status')]
                );
            }
        }

        // Notifikasi ke calon siswa yang BENAR-BENAR berhasil di-set TIDAK LULUS
        foreach ($result['tidak_lulus_editable_ids'] as $pendId) {
            $pend = $this->pendaftaranModel->find($pendId);
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

        // ── Susun pesan akhir, sertakan info jika ada yang ditolak karena terkunci ──
        $msgParts = [];
        if ($result['lulus_updated'])      $msgParts[] = "{$result['lulus_updated']} peserta dinyatakan lulus";
        if ($result['tidak_lulus_updated']) $msgParts[] = "{$result['tidak_lulus_updated']} peserta tidak lulus";

        $totalLocked = $result['lulus_locked'] + $result['tidak_lulus_locked'];

        if (empty($msgParts) && $totalLocked > 0) {
            // Semua yang dipilih ternyata sudah terkunci — tidak ada yang berubah sama sekali
            return redirect()->to(base_url('admin/seleksi'))
                ->with('error', "{$totalLocked} peserta yang dipilih sudah berstatus Daftar Ulang/Siswa Aktif dan tidak bisa diubah lagi statusnya.");
        }

        $successMsg = implode(', ', $msgParts) . '. Hasil seleksi berhasil disimpan.';

        if ($totalLocked > 0) {
            $successMsg .= " ({$totalLocked} peserta dilewati karena sudah berstatus Daftar Ulang/Siswa Aktif dan terkunci.)";
        }

        return redirect()->to(base_url('admin/seleksi'))->with('success', $successMsg);
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
