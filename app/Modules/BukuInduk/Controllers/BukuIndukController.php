<?php

namespace App\Modules\BukuInduk\Controllers;

use App\Controllers\BaseController;
use App\Modules\BukuInduk\Models\BukuIndukModel;
use App\Modules\BukuInduk\Models\BukuIndukEditLogModel;
use App\Modules\BukuInduk\Services\BukuIndukService;
use App\Modules\Pendaftaran\Models\PendaftaranModel;
use App\Modules\MasterData\Models\JurusanModel;
use App\Modules\MasterData\Models\KelasModel; 
use App\Modules\BukuInduk\Libraries\ExcelExporter;
use Dompdf\Dompdf;
use Dompdf\Options;

class BukuIndukController extends BaseController
{
    protected BukuIndukModel        $model;
    protected BukuIndukEditLogModel $logModel;
    protected BukuIndukService      $service;

    public function __construct()
    {
        $this->model    = new BukuIndukModel();
        $this->logModel = new BukuIndukEditLogModel();
        $this->service  = new BukuIndukService();
    }

    // =========================================================
    // INDEX — Split-screen
    // =========================================================
    public function index()
    {
        $filters = [
            'jurusan_id'   => $this->request->getGet('jurusan_id')   ?? '',
            'status_siswa' => $this->request->getGet('status_siswa') ?? 'aktif',
            'search'       => $this->request->getGet('search')       ?? '',
        ];

        $siswas   = $this->model->getAllWithRelations($filters);
        $jurusans = (new JurusanModel())->getAllActive();

        $selectedId = (int) ($this->request->getGet('id') ?? ($siswas[0]->id ?? 0));
        $selected   = $selectedId ? $this->model->getWithRelations($selectedId) : null;

        $kelasList = (new KelasModel())->getWithJurusan();

        $editLogs = [];
        if ($selected) {
            $rawLogs  = $this->logModel->getLogsForSiswa($selected->id);
            foreach ($rawLogs as $log) {
                $editLogs[] = [
                    'section'    => $log->section,
                    'fieldLabel' => $log->field_label,
                    'oldValue'   => $log->old_value ?? '',
                    'newValue'   => $log->new_value ?? '',
                    'editedAt'   => date('d/m/Y, H.i.s', strtotime($log->edited_at)),
                    'editedBy'   => $log->editor_name ?? 'Admin TU',
                ];
            }
        }

        return $this->render('App\\Modules\\BukuInduk\\Views\\index', [
            'title'     => 'Buku Induk Siswa',
            'siswas'    => $siswas,
            'jurusans'  => $jurusans,
            'filters'   => $filters,
            'total'     => count($siswas),
            'selected'  => $selected,
            'kelasList' => $kelasList,
            'activeTab' => $this->request->getGet('tab') ?? 'pribadi',
            'editLogs'  => $editLogs,
        ]);
    }

    // =========================================================
    // DETAIL (standalone)
    // =========================================================
    public function detail(int $id)
    {
        $siswa = $this->model->getWithRelations($id);

        if (! $siswa) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Data tidak ditemukan.');
        }

        return $this->render('App\Modules\BukuInduk\Views\detail', [
            'title' => 'Detail Siswa — ' . $siswa->nama_lengkap,
            'siswa' => $siswa,
        ]);
    }

    // =========================================================
    // UPDATE DATA PRIBADI
    // =========================================================
    public function updatePribadi(int $id)
    {
        $siswa = $this->model->find($id);
        if (! $siswa) {
            return $this->request->isAJAX()
                ? $this->jsonError('Data tidak ditemukan.', [], 404)
                : redirect()->back()->with('error', 'Data tidak ditemukan.');
        }

        $newData = [
            'nisn'            => $this->request->getPost('nisn'),
            'nik'             => $this->request->getPost('nik'),
            'nama_lengkap'    => $this->request->getPost('nama_lengkap'),
            'nama_panggilan'  => $this->request->getPost('nama_panggilan'),
            'tempat_lahir'    => $this->request->getPost('tempat_lahir'),
            'tanggal_lahir'   => $this->request->getPost('tanggal_lahir'),
            'jenis_kelamin'   => $this->request->getPost('jenis_kelamin'),
            'agama'           => $this->request->getPost('agama'),
            'kewarganegaraan' => $this->request->getPost('kewarganegaraan'),
            'asal_sekolah'    => $this->request->getPost('asal_sekolah'),
            'tahun_lulus_smp' => $this->request->getPost('tahun_lulus_smp'),
            'alamat'          => $this->request->getPost('alamat'),
            'no_hp'           => $this->request->getPost('no_hp'),
            'email_siswa'     => $this->request->getPost('email_siswa'),
            'nama_ayah'       => $this->request->getPost('nama_ayah'),
            'pekerjaan_ayah'  => $this->request->getPost('pekerjaan_ayah'),
            'no_hp_ayah'      => $this->request->getPost('no_hp_ayah'),
            'nama_ibu'        => $this->request->getPost('nama_ibu'),
            'pekerjaan_ibu'   => $this->request->getPost('pekerjaan_ibu'),
            'no_hp_ibu'       => $this->request->getPost('no_hp_ibu'),
        ];

        $oldData = [
            'nisn'            => $siswa->nisn            ?? '',
            'nik'             => $siswa->nik             ?? '',
            'nama_lengkap'    => $siswa->nama_lengkap    ?? '',
            'nama_panggilan'  => $siswa->nama_panggilan  ?? '',
            'tempat_lahir'    => $siswa->tempat_lahir    ?? '',
            'tanggal_lahir'   => $siswa->tanggal_lahir   ?? '',
            'jenis_kelamin'   => $siswa->jenis_kelamin   ?? '',
            'agama'           => $siswa->agama           ?? '',
            'kewarganegaraan' => $siswa->kewarganegaraan ?? '',
            'asal_sekolah'    => $siswa->asal_sekolah    ?? '',
            'tahun_lulus_smp' => $siswa->tahun_lulus_smp ?? '',
            'alamat'          => $siswa->alamat          ?? '',
            'no_hp'           => $siswa->no_hp           ?? '',
            'email_siswa'     => $siswa->email_siswa     ?? '',
            'nama_ayah'       => $siswa->nama_ayah       ?? '',
            'pekerjaan_ayah'  => $siswa->pekerjaan_ayah  ?? '',
            'no_hp_ayah'      => $siswa->no_hp_ayah      ?? '',
            'nama_ibu'        => $siswa->nama_ibu        ?? '',
            'pekerjaan_ibu'   => $siswa->pekerjaan_ibu   ?? '',
            'no_hp_ibu'       => $siswa->no_hp_ibu       ?? '',
        ];

        $ok = $this->model->updatePribadi($id, $newData, $oldData, $this->userId());

        if ($this->request->isAJAX()) {
            if (! $ok) return $this->jsonError('Gagal menyimpan data pribadi.');
            $rawLogs = $this->logModel->getLogsForSiswa($id);
            $logs    = $this->formatLogs($rawLogs);
            return $this->jsonSuccess('Data pribadi berhasil disimpan.', ['logs' => $logs]);
        }

        return redirect()->to(base_url('admin/buku-induk?id=' . $id . '&tab=pribadi'))
            ->with($ok ? 'success' : 'error', $ok ? 'Data pribadi berhasil disimpan.' : 'Gagal menyimpan.');
    }

    // =========================================================
    // UPDATE KESEHATAN
    // =========================================================
    public function updateKesehatan(int $id)
    {
        $siswa = $this->model->find($id);
        if (! $siswa) {
            return $this->request->isAJAX()
                ? $this->jsonError('Data tidak ditemukan.', [], 404)
                : redirect()->back()->with('error', 'Data tidak ditemukan.');
        }

        $newData = [
            'golongan_darah'    => $this->request->getPost('golongan_darah'),
            'tinggi_badan'      => $this->request->getPost('tinggi_badan') ? (int) $this->request->getPost('tinggi_badan') : null,
            'berat_badan'       => $this->request->getPost('berat_badan')  ? (int) $this->request->getPost('berat_badan')  : null,
            'riwayat_penyakit'  => $this->request->getPost('riwayat_penyakit'),
            'catatan_kesehatan' => $this->request->getPost('catatan_kesehatan'),
        ];

        $oldData = [
            'golongan_darah'    => $siswa->golongan_darah    ?? '',
            'tinggi_badan'      => (string) ($siswa->tinggi_badan ?? ''),
            'berat_badan'       => (string) ($siswa->berat_badan  ?? ''),
            'riwayat_penyakit'  => $siswa->riwayat_penyakit  ?? '',
            'catatan_kesehatan' => $siswa->catatan_kesehatan ?? '',
        ];

        $ok = $this->model->updateKesehatan($id, $newData, $oldData, $this->userId());

        if ($this->request->isAJAX()) {
            if (! $ok) return $this->jsonError('Gagal menyimpan data kesehatan.');
            $rawLogs = $this->logModel->getLogsForSiswa($id);
            $logs    = $this->formatLogs($rawLogs);
            return $this->jsonSuccess('Data kesehatan berhasil disimpan.', ['logs' => $logs]);
        }

        return redirect()->to(base_url('admin/buku-induk?id=' . $id . '&tab=kesehatan'))
            ->with($ok ? 'success' : 'error', $ok ? 'Data kesehatan berhasil disimpan.' : 'Gagal menyimpan.');
    }

    // =========================================================
    // UPDATE KELAS
    // =========================================================
    public function updateKelas(int $id)
    {
        $siswa = $this->model->find($id);
        if (! $siswa) {
            return $this->request->isAJAX()
                ? $this->jsonError('Data tidak ditemukan.', [], 404)
                : redirect()->back()->with('error', 'Data tidak ditemukan.');
        }

        $newData = [
            'kelas_id'    => $this->request->getPost('kelas_id')    ? (int) $this->request->getPost('kelas_id')  : null,
            'tahun_masuk' => $this->request->getPost('tahun_masuk') ?: null,
        ];

        $oldData = [
            'kelas_id'    => (string) ($siswa->kelas_id    ?? ''),
            'tahun_masuk' => (string) ($siswa->tahun_masuk ?? ''),
        ];

        $ok = $this->model->updateKelas($id, $newData, $oldData, $this->userId());

        if ($this->request->isAJAX()) {
            if (! $ok) return $this->jsonError('Gagal menyimpan penempatan kelas.');
            $rawLogs = $this->logModel->getLogsForSiswa($id);
            $logs    = $this->formatLogs($rawLogs);
            return $this->jsonSuccess('Penempatan kelas berhasil disimpan.', ['logs' => $logs]);
        }

        return redirect()->to(base_url('admin/buku-induk?id=' . $id . '&tab=kelas'))
            ->with($ok ? 'success' : 'error', $ok ? 'Penempatan kelas berhasil disimpan.' : 'Gagal menyimpan.');
    }

    // =========================================================
    // KONVERSI PAGE
    // =========================================================
    public function konversiPage()
    {
        $pendaftaranM = new PendaftaranModel();
        $siapKonversi = $pendaftaranM
            ->select('pendaftaran.id, pendaftaran.no_pendaftaran, pendaftaran.status,
                      pendaftaran.jurusan_diterima_id,
                      dds.nama_lengkap, dds.nisn,
                      j.nama as jurusan_nama, j.kode as jurusan_kode, j.kode_nis,
                      bi.id as buku_induk_id, bi.nis,
                      du.status as du_status, du.nis as du_nis, du.nama_kelas as du_kelas')
            ->join('data_diri_siswas dds', 'dds.pendaftaran_id = pendaftaran.id', 'left')
            ->join('jurusan j',            'j.id = pendaftaran.jurusan_diterima_id', 'left')
            ->join('buku_induks bi',       'bi.pendaftaran_id = pendaftaran.id', 'left')
            ->join('daftar_ulangs du',     'du.pendaftaran_id = pendaftaran.id', 'left')
            ->whereIn('pendaftaran.status', ['daftar_ulang', 'siswa_aktif'])
            ->orderBy('j.kode', 'ASC')
            ->orderBy('dds.nama_lengkap', 'ASC')
            ->findAll();

        $jurusans  = (new JurusanModel())->getAllActive();
        $kelasList = (new KelasModel())->getWithJurusan();

        return $this->render('App\Modules\BukuInduk\Views\konversi', [
            'title'        => 'Konversi ke Buku Induk',
            'siapKonversi' => $siapKonversi,
            'jurusans'     => $jurusans,
            'kelasList'    => $kelasList,
        ]);
    }

    // =========================================================
    // KONVERSI SINGLE
    // =========================================================
    public function konversi()
    {
        $pendaftaranId = (int) $this->request->getPost('pendaftaran_id');
        $kelasId       = (int) ($this->request->getPost('kelas_id') ?: 0) ?: null;

        if (! $pendaftaranId) {
            return redirect()->back()->with('error', 'Pilih pendaftaran terlebih dahulu.');
        }

        $result = $this->service->konversi($pendaftaranId, $this->userId(), $kelasId);

        // FIX: Gunakan flash 'error' jika konversi gagal, bukan 'success'
        $flashType = $result['success'] ? 'success' : 'error';
        return redirect()->to(base_url('admin/buku-induk'))->with($flashType, $result['message']);
    }

    // =========================================================
    // KONVERSI BULK SELECTED
    // FIX: Gunakan flash 'warning' jika ada campuran sukses/gagal,
    //      dan 'error' jika semua gagal. Sebelumnya selalu pakai 'success'
    //      sehingga notifikasi hijau muncul meski konversi gagal semua.
    // =========================================================
    public function konversiBulkSelected()
    {
        $pendaftaranIds = $this->request->getPost('pendaftaran_ids') ?? [];

        if (empty($pendaftaranIds)) {
            return redirect()->back()->with('error', 'Pilih minimal 1 siswa untuk dikonversi.');
        }

        $berhasil = 0;
        $gagal    = 0;
        $errors   = [];

        foreach ($pendaftaranIds as $pid) {
            $result = $this->service->konversi((int) $pid, $this->userId());
            if ($result['success']) {
                $berhasil++;
            } else {
                $gagal++;
                $errors[] = $result['message'];
            }
        }

        // FIX: Tentukan jenis flash sesuai hasil nyata
        if ($berhasil > 0 && $gagal === 0) {
            // Semua berhasil → hijau (success)
            $flashType = 'success';
            $msg = "Berhasil mengkonversi {$berhasil} siswa ke Buku Induk!";
        } elseif ($berhasil > 0 && $gagal > 0) {
            // Sebagian berhasil → kuning (warning)
            $flashType = 'warning';
            $msg = "{$berhasil} siswa berhasil dikonversi, {$gagal} gagal: "
                . implode(' | ', array_slice($errors, 0, 3));
        } else {
            // Semua gagal → merah (error)
            $flashType = 'error';
            $msg = "Konversi gagal untuk semua {$gagal} siswa: "
                . implode(' | ', array_slice($errors, 0, 3));
        }

        return redirect()->to(base_url('admin/buku-induk/konversi'))->with($flashType, $msg);
    }

    // =========================================================
    // KONVERSI BULK ALL
    // FIX: Sama seperti di atas — flash type sesuai hasil
    // =========================================================
    public function konversiBulk()
    {
        $result = $this->service->konversiBulk($this->userId());

        $berhasil = $result['sukses'] ?? 0;
        $gagal    = $result['gagal']  ?? 0;

        if ($berhasil > 0 && $gagal === 0) {
            $flashType = 'success';
            $msg = $result['message'];
        } elseif ($berhasil > 0 && $gagal > 0) {
            $flashType = 'warning';
            $msg = $result['message'];
            if (! empty($result['errors'])) {
                $msg .= ' Gagal: ' . implode('; ', array_slice($result['errors'], 0, 3));
            }
        } else {
            $flashType = 'error';
            $msg = $result['message'];
            if (! empty($result['errors'])) {
                $msg .= ' Detail: ' . implode('; ', array_slice($result['errors'], 0, 3));
            }
        }

        return redirect()->to(base_url('admin/buku-induk'))->with($flashType, $msg);
    }

    // =========================================================
    // EXPORT EXCEL — BULK (semua / filter / selected)
    // GET  admin/buku-induk/export-excel
    // POST admin/buku-induk/export-excel-selected
    // =========================================================

    /**
     * Export bulk: ambil filter dari query-string (sama persis
     * dengan filter di halaman index agar hasilnya konsisten).
     *
     * ?jurusan_id=&status_siswa=aktif&search=
     */
    public function exportExcel()
    {
        $filters = [
            'jurusan_id'   => $this->request->getGet('jurusan_id')   ?? '',
            'status_siswa' => $this->request->getGet('status_siswa') ?? '',
            'search'       => $this->request->getGet('search')       ?? '',
        ];

        // Untuk info header di Excel kita butuh nama jurusan juga
        if (! empty($filters['jurusan_id'])) {
            $j = (new \App\Modules\MasterData\Models\JurusanModel())->find((int) $filters['jurusan_id']);
            $filters['jurusan_nama'] = $j ? $j->nama : '';
        }

        $siswas = $this->model->getAllForExport($filters);

        (new ExcelExporter())->exportBulk($siswas, $filters);
        // exportBulk() memanggil exit — tidak ada kode setelah ini
    }

    /**
     * Export selected: terima array id[] dari POST (checkbox di halaman index).
     */
    public function exportExcelSelected()
    {
        $ids = $this->request->getPost('ids') ?? [];

        if (empty($ids)) {
            return redirect()->to(base_url('admin/buku-induk'))
                ->with('error', 'Pilih minimal 1 siswa untuk diekspor.');
        }

        $filters = ['ids' => $ids];
        $siswas  = $this->model->getAllForExport($filters);

        (new ExcelExporter())->exportBulk($siswas, ['jurusan_nama' => 'Terpilih (' . count($siswas) . ')']);
    }

    // =========================================================
    // EXPORT EXCEL — SINGLE (1 siswa)
    // GET  admin/buku-induk/{id}/export-excel
    // =========================================================

    public function exportExcelSingle(int $id)
    {
        $siswa = $this->model->getWithRelations($id);

        if (! $siswa) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Data siswa tidak ditemukan.');
        }

        (new ExcelExporter())->exportSingle($siswa);
        // exportSingle() memanggil exit — tidak ada kode setelah ini
    }

    // =========================================================
    // CETAK PDF — BUKU INDUK LENGKAP
    // GET  admin/buku-induk/{id}/cetak
    // =========================================================

    public function cetak(int $id)
    {
        $siswa = $this->model->getWithRelations($id);

        if (! $siswa) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Data siswa tidak ditemukan.');
        }

        $html = view('App\Modules\BukuInduk\Views\cetak', [
            'siswa'    => $siswa,
            'tglCetak' => date('d/m/Y H:i'),
        ]);

        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'buku_induk_' . preg_replace('/[^a-zA-Z0-9]/', '_', $siswa->nis) . '.pdf';
        $dompdf->stream($filename, ['Attachment' => false]); // false = buka di browser (bukan download)
    }

    // =========================================================
    // CETAK KARTU SISWA
    // GET  admin/buku-induk/{id}/cetak-kartu
    // =========================================================

    public function cetakKartu(int $id)
    {
        $siswa = $this->model->getWithRelations($id);

        if (! $siswa) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Data siswa tidak ditemukan.');
        }

        $html = view('App\Modules\BukuInduk\Views\cetak', [
            'siswa'    => $siswa,
            'tglCetak' => date('d/m/Y H:i'),
            'mode'     => 'kartu',
        ]);

        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper([0, 0, 241.89, 153.07], 'landscape'); // ukuran ID card 85.6mm x 53.98mm
        $dompdf->render();

        $filename = 'kartu_siswa_' . preg_replace('/[^a-zA-Z0-9]/', '_', $siswa->nis) . '.pdf';
        $dompdf->stream($filename, ['Attachment' => false]);
    }

    // =========================================================
    // PRIVATE HELPER
    // =========================================================
    private function formatLogs(array $rawLogs): array
    {
        return array_map(fn($log) => [
            'section'    => $log->section,
            'fieldLabel' => $log->field_label,
            'oldValue'   => $log->old_value ?? '',
            'newValue'   => $log->new_value ?? '',
            'editedAt'   => date('d/m/Y, H.i.s', strtotime($log->edited_at)),
            'editedBy'   => $log->editor_name ?? 'Admin TU',
        ], $rawLogs);
    }
}