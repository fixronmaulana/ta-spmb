<?php

namespace App\Modules\Verifikasi\Services;

use App\Modules\Pendaftaran\Models\PendaftaranModel;
use App\Modules\Pendaftaran\Models\DokumenModel;
use App\Modules\Verifikasi\Models\VerifikasiModel;
use App\Modules\Notifikasi\Services\NotifikasiService;

class VerifikasiService
{
    protected PendaftaranModel  $pendaftaranModel;
    protected DokumenModel      $dokumenModel;
    protected VerifikasiModel   $verifikasiModel;
    protected NotifikasiService $notifService;

    public function __construct()
    {
        $this->pendaftaranModel = new PendaftaranModel();
        $this->dokumenModel     = new DokumenModel();
        $this->verifikasiModel  = new VerifikasiModel();
        $this->notifService     = new NotifikasiService();
    }

    /**
     * Approve satu dokumen
     */
    public function approveDokumen(int $pendaftaranId, int $dokumenId, int $adminId): array
    {
        $dokumen = $this->dokumenModel->find($dokumenId);
        if (! $dokumen || $dokumen->pendaftaran_id != $pendaftaranId) {
            return ['success' => false, 'message' => 'Dokumen tidak ditemukan.'];
        }

        $this->dokumenModel->approve($dokumenId, $adminId);

        // Update status pendaftaran ke 'verifikasi' jika masih submitted
        $pendaftaran = $this->pendaftaranModel->find($pendaftaranId);
        if ($pendaftaran->status === 'submitted') {
            $this->pendaftaranModel->updateStatus($pendaftaranId, 'verifikasi', ['verified_by' => $adminId]);
        }

        $this->verifikasiModel->log($pendaftaranId, $adminId, 'approve_dokumen', [
            'target_type' => 'dokumen', 'target_id' => $dokumenId,
        ]);

        return ['success' => true, 'message' => 'Dokumen disetujui.'];
    }

    /**
     * Reject satu dokumen
     */
    public function rejectDokumen(int $pendaftaranId, int $dokumenId, int $adminId, string $catatan): array
    {
        $dokumen = $this->dokumenModel->find($dokumenId);
        if (! $dokumen || $dokumen->pendaftaran_id != $pendaftaranId) {
            return ['success' => false, 'message' => 'Dokumen tidak ditemukan.'];
        }

        $this->dokumenModel->reject($dokumenId, $adminId, $catatan);

        $this->verifikasiModel->log($pendaftaranId, $adminId, 'reject_dokumen', [
            'target_type' => 'dokumen', 'target_id' => $dokumenId,
            'keterangan'  => $catatan,
        ]);

        return ['success' => true, 'message' => 'Dokumen ditolak.'];
    }

    /**
     * Approve semua dokumen sekaligus, lalu masukkan ke seleksi
     */
    public function approveSemua(int $pendaftaranId, int $adminId): array
    {
        $pendaftaran = $this->pendaftaranModel->find($pendaftaranId);
        if (! $pendaftaran) {
            return ['success' => false, 'message' => 'Pendaftaran tidak ditemukan.'];
        }

        $this->dokumenModel->approveAll($pendaftaranId, $adminId);

        $this->pendaftaranModel->updateStatus($pendaftaranId, 'seleksi', [
            'verified_at' => date('Y-m-d H:i:s'),
            'verified_by' => $adminId,
        ]);

        $this->verifikasiModel->log($pendaftaranId, $adminId, 'approve_semua_masuk_seleksi');

        // Notif ke calon siswa
        $this->notifService->send(
            $pendaftaran->user_id,
            'dokumen_verified',
            'Dokumen Anda Disetujui',
            'Semua dokumen telah diverifikasi. Pendaftaran Anda masuk ke tahap seleksi.',
            ['url' => base_url('dashboard/status')]
        );

        return ['success' => true, 'message' => 'Semua dokumen disetujui. Pendaftaran masuk ke tahap seleksi.'];
    }

    /**
     * Tolak keseluruhan pendaftaran (minta revisi)
     */
    public function tolakPendaftaran(int $pendaftaranId, int $adminId, string $alasan): array
    {
        $pendaftaran = $this->pendaftaranModel->find($pendaftaranId);
        if (! $pendaftaran) {
            return ['success' => false, 'message' => 'Pendaftaran tidak ditemukan.'];
        }

        $this->pendaftaranModel->updateStatus($pendaftaranId, 'revisi', [
            'alasan_penolakan' => $alasan,
            'catatan_admin'    => $alasan,
        ]);

        $this->verifikasiModel->log($pendaftaranId, $adminId, 'tolak_pendaftaran_revisi', [
            'keterangan' => $alasan,
        ]);

        $this->notifService->send(
            $pendaftaran->user_id,
            'revisi_diperlukan',
            'Formulir Anda Perlu Direvisi',
            "Admin meminta revisi: {$alasan}. Silakan perbaiki dan kirim ulang.",
            ['url' => base_url('dashboard/formulir')]
        );

        return ['success' => true, 'message' => 'Pendaftaran dikembalikan untuk revisi.'];
    }
}
