<?php

namespace App\Modules\MasterData\Controllers;

use App\Controllers\BaseController;
use App\Modules\MasterData\Models\JurusanModel;
use App\Modules\MasterData\Models\KelasModel;
use App\Modules\MasterData\Models\PeriodeModel;
use App\Modules\MasterData\Models\JenisDokumenModel;

class MasterDataController extends BaseController
{
    protected JurusanModel      $jurusanModel;
    protected KelasModel        $kelasModel;
    protected PeriodeModel      $periodeModel;
    protected JenisDokumenModel $jenisDokumenModel;

    public function __construct()
    {
        $this->jurusanModel      = new JurusanModel();
        $this->kelasModel        = new KelasModel();
        $this->periodeModel      = new PeriodeModel();
        $this->jenisDokumenModel = new JenisDokumenModel();
    }

    // =========================================================
    // INDEX
    // =========================================================
    public function index()
    {
        return $this->render('App\Modules\MasterData\Views\index', [
            'title'        => 'Master Data',
            'jurusans'     => $this->jurusanModel->orderBy('urutan')->findAll(),
            'kelas'        => $this->kelasModel->getWithJurusan(),
            'periodes'     => $this->periodeModel->orderBy('created_at', 'DESC')->findAll(),
            'jenisDokumens' => $this->jenisDokumenModel->getAllForAdmin(),
        ]);
    }

    // =========================================================
    // JURUSAN CRUD
    // =========================================================
    public function simpanJurusan()
    {
        $id   = $this->request->getPost('id');
        $tab  = $this->request->getPost('tab') ?? 'jurusan';
        $data = [
            'kode'      => strtoupper(trim($this->request->getPost('kode'))),
            'kode_nis'  => $this->request->getPost('kode_nis'),
            'nama'      => $this->request->getPost('nama'),
            'kuota'     => (int) $this->request->getPost('kuota'),
            'urutan'    => (int) $this->request->getPost('urutan'),
            'is_active' => $this->request->getPost('is_active') ? 1 : 0,
            'deskripsi' => $this->request->getPost('deskripsi') ?? '',
        ];

        if ($id) {
            $this->jurusanModel->update($id, $data);
            $msg = 'Jurusan berhasil diperbarui.';
        } else {
            $this->jurusanModel->insert($data);
            $msg = 'Jurusan berhasil ditambahkan.';
        }

        return redirect()->to(base_url('admin/master-data'))
            ->with('success', $msg)
            ->with('active_tab', $tab);
    }

    public function hapusJurusan(int $id)
    {
        $this->jurusanModel->update($id, ['is_active' => 0]);
        return redirect()->to(base_url('admin/master-data'))
            ->with('success', 'Jurusan berhasil dinonaktifkan.')
            ->with('active_tab', 'jurusan');
    }

    // =========================================================
    // KELAS CRUD
    // =========================================================
    public function simpanKelas()
    {
        $id   = $this->request->getPost('id');
        $tab  = $this->request->getPost('tab') ?? 'kelas';
        $data = [
            'jurusan_id' => $this->request->getPost('jurusan_id'),
            'nama'       => $this->request->getPost('nama'),
            'tingkat'    => $this->request->getPost('tingkat'),
            'kapasitas'  => (int) $this->request->getPost('kapasitas'),
            'wali_kelas' => $this->request->getPost('wali_kelas') ?? '',
            'is_active'  => 1,
        ];

        if ($id) {
            $this->kelasModel->update($id, $data);
            $msg = 'Kelas berhasil diperbarui.';
        } else {
            $this->kelasModel->insert($data);
            $msg = 'Kelas berhasil ditambahkan.';
        }

        return redirect()->to(base_url('admin/master-data'))
            ->with('success', $msg)
            ->with('active_tab', $tab);
    }

    public function hapusKelas(int $id)
    {
        $this->kelasModel->update($id, ['is_active' => 0]);
        return redirect()->to(base_url('admin/master-data'))
            ->with('success', 'Kelas berhasil dihapus.')
            ->with('active_tab', 'kelas');
    }

    // =========================================================
    // PERIODE CRUD
    // =========================================================
    public function simpanPeriode()
    {
        $id       = $this->request->getPost('id');
        $tab      = $this->request->getPost('tab') ?? 'periode';
        $setAktif = (bool) $this->request->getPost('set_aktif');

        $data = [
            'nama'                         => $this->request->getPost('nama'),
            'tahun_ajaran'                 => $this->request->getPost('tahun_ajaran'),
            'tanggal_mulai'                => $this->request->getPost('tanggal_mulai') ?: null,
            'tanggal_selesai'              => $this->request->getPost('tanggal_selesai') ?: null,
            'tanggal_pengumuman'           => $this->request->getPost('tanggal_pengumuman') ?: null,
            'tanggal_daftar_ulang_mulai'   => $this->request->getPost('tanggal_daftar_ulang_mulai') ?: null,
            'tanggal_daftar_ulang_selesai' => $this->request->getPost('tanggal_daftar_ulang_selesai') ?: null,
            'deskripsi'                    => $this->request->getPost('deskripsi') ?? '',
            // ── WhatsApp per-periode ────────────────────────────
            'wa_grup_link'                 => trim($this->request->getPost('wa_grup_link') ?? '') ?: null,
            'wa_cp_no'                     => trim($this->request->getPost('wa_cp_no') ?? '') ?: null,
        ];

        if ($id) {
            $this->periodeModel->update($id, $data);
            $insertedId = $id;
            $msg = 'Periode berhasil diperbarui.';
        } else {
            $insertedId = $this->periodeModel->insert($data);
            $msg = 'Periode berhasil ditambahkan.';
        }

        if ($setAktif && $insertedId) {
            $this->periodeModel->setAktif((int) $insertedId);
            $msg .= ' Periode telah diaktifkan.';
        }

        return redirect()->to(base_url('admin/master-data'))
            ->with('success', $msg)
            ->with('active_tab', $tab);
    }

    public function setAktifPeriode(int $id)
    {
        $this->periodeModel->setAktif($id);
        return redirect()->to(base_url('admin/master-data'))
            ->with('success', 'Periode berhasil diaktifkan.')
            ->with('active_tab', 'periode');
    }

    public function publishPeriode(int $id)
    {
        $this->periodeModel->publish($id);
        return redirect()->to(base_url('admin/master-data'))
            ->with('success', 'Pengumuman periode berhasil dipublikasikan.')
            ->with('active_tab', 'periode');
    }

    public function hapusPeriode(int $id)
    {
        $this->periodeModel->update($id, ['is_active' => 0]);
        return redirect()->to(base_url('admin/master-data'))
            ->with('success', 'Periode berhasil dihapus.')
            ->with('active_tab', 'periode');
    }

    // =========================================================
    // JENIS DOKUMEN CRUD  ← BARU
    // =========================================================

    public function simpanJenisDokumen()
    {
        $id    = $this->request->getPost('id') ?: null;
        $tab   = 'dokumen';
        $kode  = trim(strtolower($this->request->getPost('kode') ?? ''));
        $kode  = preg_replace('/[^a-z0-9_]/', '_', $kode); // sanitasi: hanya huruf kecil, angka, underscore

        // Validasi: kode wajib diisi
        if (empty($kode)) {
            return redirect()->to(base_url('admin/master-data'))
                ->with('error', 'Kode dokumen wajib diisi.')
                ->with('active_tab', $tab);
        }

        // Validasi: kode unik
        if ($this->jenisDokumenModel->isKodeTaken($kode, $id ? (int) $id : null)) {
            return redirect()->to(base_url('admin/master-data'))
                ->with('error', "Kode dokumen '{$kode}' sudah digunakan. Pilih kode lain.")
                ->with('active_tab', $tab);
        }

        $namaDokumen = trim($this->request->getPost('nama_dokumen') ?? '');
        if (empty($namaDokumen)) {
            return redirect()->to(base_url('admin/master-data'))
                ->with('error', 'Nama dokumen wajib diisi.')
                ->with('active_tab', $tab);
        }

        // ── Tentukan nilai urutan ──────────────────────────────────────────
        $urutanInput = $this->request->getPost('urutan');
        $maxUrutan   = $this->jenisDokumenModel->getMaxUrutan();

        if ($urutanInput === '' || $urutanInput === null) {
            // Kosong → taruh di akhir (tidak ada konflik)
            $targetUrutan = $maxUrutan + 1;
        } else {
            $targetUrutan = max(1, (int) $urutanInput);
            // Batasi agar tidak melebihi total baris + 1
            $targetUrutan = min($targetUrutan, $maxUrutan + 1);
        }

        // ── Jika urutan yang diminta sudah ditempati baris lain → geser semua ke bawah ──
        if ($this->jenisDokumenModel->isUrutanTaken($targetUrutan, $id ? (int) $id : null)) {
            $this->jenisDokumenModel->shiftUrutanUp($targetUrutan, $id ? (int) $id : null);
        }

        $data = [
            'kode'         => $kode,
            'nama_dokumen' => $namaDokumen,
            'keterangan'   => trim($this->request->getPost('keterangan') ?? '') ?: null,
            'is_wajib'     => $this->request->getPost('is_wajib')  ? 1 : 0,
            'is_active'    => $this->request->getPost('is_active') ? 1 : 0,
            'urutan'       => $targetUrutan,
        ];

        if ($id) {
            $this->jenisDokumenModel->update((int) $id, $data);
            $msg = "Jenis dokumen '{$data['nama_dokumen']}' berhasil diperbarui.";
        } else {
            $this->jenisDokumenModel->insert($data);
            $msg = "Jenis dokumen '{$data['nama_dokumen']}' berhasil ditambahkan.";
        }

        // ── Normalisasi: pastikan urutan selalu 1,2,3,… tanpa gap/duplikat ──
        $this->jenisDokumenModel->normalizeUrutan();

        return redirect()->to(base_url('admin/master-data'))
            ->with('success', $msg)
            ->with('active_tab', $tab);
    }

    /**
     * Toggle aktif/nonaktif — tidak hard-delete agar histori upload tetap ada.
     */
    public function toggleJenisDokumen(int $id)
    {
        $row = $this->jenisDokumenModel->find($id);
        if (! $row) {
            return redirect()->to(base_url('admin/master-data'))
                ->with('error', 'Jenis dokumen tidak ditemukan.')
                ->with('active_tab', 'dokumen');
        }

        $newStatus = $row->is_active ? 0 : 1;
        $this->jenisDokumenModel->update($id, ['is_active' => $newStatus]);

        $action = $newStatus ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->to(base_url('admin/master-data'))
            ->with('success', "Jenis dokumen '{$row->nama_dokumen}' berhasil {$action}.")
            ->with('active_tab', 'dokumen');
    }

    /**
     * Toggle wajib/tidak wajib via AJAX (dari toggle switch di tabel).
     */
    public function toggleWajibJenisDokumen(int $id)
    {
        $row = $this->jenisDokumenModel->find($id);
        if (! $row) {
            return $this->response->setStatusCode(404)->setJSON(['success' => false, 'message' => 'Tidak ditemukan.']);
        }

        $newWajib = $row->is_wajib ? 0 : 1;
        $this->jenisDokumenModel->update($id, ['is_wajib' => $newWajib]);

        return $this->response->setJSON([
            'success'  => true,
            'is_wajib' => $newWajib,
            'message'  => "Status wajib berhasil diubah.",
        ]);
    }

    /**
     * Hard delete — hanya boleh jika belum pernah dipakai di dokumen_pendaftaran.
     */
    public function hapusJenisDokumen(int $id)
    {
        $row = $this->jenisDokumenModel->find($id);
        if (! $row) {
            return redirect()->to(base_url('admin/master-data'))
                ->with('error', 'Jenis dokumen tidak ditemukan.')
                ->with('active_tab', 'dokumen');
        }

        // Cek apakah sudah dipakai
        $dipakai = db_connect()
            ->table('dokumen_pendaftaran')
            ->where('jenis_dokumen', $row->kode)
            ->countAllResults();

        if ($dipakai > 0) {
            return redirect()->to(base_url('admin/master-data'))
                ->with('error', "Jenis dokumen '{$row->nama_dokumen}' tidak dapat dihapus karena sudah digunakan oleh {$dipakai} pendaftar. Nonaktifkan saja.")
                ->with('active_tab', 'dokumen');
        }

        $this->jenisDokumenModel->delete($id);

        // Normalisasi ulang urutan setelah hapus agar tidak ada gap
        $this->jenisDokumenModel->normalizeUrutan();

        return redirect()->to(base_url('admin/master-data'))
            ->with('success', "Jenis dokumen '{$row->nama_dokumen}' berhasil dihapus permanen.")
            ->with('active_tab', 'dokumen');
    }
}
