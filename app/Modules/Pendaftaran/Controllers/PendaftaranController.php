<?php

namespace App\Modules\Pendaftaran\Controllers;

use App\Controllers\BaseController;
use App\Libraries\FileUploader;
use App\Modules\MasterData\Models\JurusanModel;
use App\Modules\Pendaftaran\Models\DataDiriSiswaModel;
use App\Modules\Pendaftaran\Models\DokumenModel;
use App\Modules\Pendaftaran\Models\PendaftaranModel;
use App\Modules\Pendaftaran\Services\PendaftaranService;
use App\Modules\Pendaftaran\Validation\PendaftaranValidation;

class PendaftaranController extends BaseController
{
    protected PendaftaranService $service;
    protected PendaftaranModel   $pendaftaranModel;
    protected DataDiriSiswaModel $dataDiriModel;
    protected DokumenModel       $dokumenModel;

    public function __construct()
    {
        $this->service          = new PendaftaranService();
        $this->pendaftaranModel = new PendaftaranModel();
        $this->dataDiriModel    = new DataDiriSiswaModel();
        $this->dokumenModel     = new DokumenModel();
    }

    // =========================================================
    // INDEX — Redirect ke step terakhir
    // =========================================================
    public function index()
    {
        $pendaftaran = $this->service->getOrCreate($this->userId());

        if (! in_array($pendaftaran->status, ['draft', 'revisi'])) {
            return redirect()->to(base_url('dashboard/status'));
        }

        $step = max(1, min(4, $pendaftaran->step_terakhir ?? 1));
        return redirect()->to(base_url("dashboard/formulir/step/{$step}"));
    }

    // =========================================================
    // STEP VIEW
    // =========================================================
    public function step(int $stepNum)
    {
        if ($stepNum < 1 || $stepNum > 4) {
            return redirect()->to(base_url('dashboard/formulir'));
        }

        $pendaftaran = $this->service->getOrCreate($this->userId());

        if (! in_array($pendaftaran->status, ['draft', 'revisi'])) {
            return redirect()->to(base_url('dashboard/status'))
                ->with('info', 'Formulir sudah disubmit dan tidak dapat diedit.');
        }

        $dataDiri  = $this->dataDiriModel->getByPendaftaranId($pendaftaran->id);
        $dokumens  = $this->dokumenModel->getByPendaftaranId($pendaftaran->id);
        $jurusans  = (new JurusanModel())->getAllActive();
        $draftData = $this->pendaftaranModel->getDraft($pendaftaran->id, $stepNum);

        // ── Cek status verifikasi WA untuk step 1 ──────────────────
        $waVerifiedStatus = false;
        $waVerifiedPhone  = '';
        if ($stepNum === 1) {
            $waSession = $this->session->get('wa_verified_' . $this->userId());
            if (
                $waSession &&
                ! empty($waSession['verified']) &&
                (time() - ($waSession['time'] ?? 0)) < 86400
            ) {
                $waVerifiedStatus = true;
                $waVerifiedPhone  = $waSession['phone'] ?? '';
            }
        }

        $data = [
            'title'             => "Formulir Pendaftaran — Step {$stepNum}",
            'pendaftaran'       => $pendaftaran,
            'dataDiri'          => $dataDiri,
            'dokumens'          => $dokumens,
            'jurusans'          => $jurusans,
            'draftData'         => $draftData,
            'currentStep'       => $stepNum,
            'totalSteps'        => 4,
            'steps'             => $this->getStepMeta(),
            'jenisDokumenWajib' => jenis_dokumen_wajib(),
            'jenisDokumenSemua' => $this->getJenisDokumenSemua(),
            'waVerifiedStatus'  => $waVerifiedStatus,
            'waVerifiedPhone'   => $waVerifiedPhone,
        ];

        return $this->render("pendaftaran/formulir/step{$stepNum}", $data);
    }

    // =========================================================
    // SAVE STEP (POST)
    // =========================================================
    public function saveStep(int $stepNum)
    {
        // Deteksi AJAX: fetch() modern mengirim header X-Requested-With,
        // tapi form submit biasa tidak. Kita cek keduanya.
        $isAjax = $this->request->isAJAX()
            || $this->request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest'
            || str_contains($this->request->getHeaderLine('Accept'), 'application/json');

        $pendaftaran = $this->pendaftaranModel->getByUserId($this->userId());

        if (! $pendaftaran || ! in_array($pendaftaran->status, ['draft', 'revisi'])) {
            if ($isAjax) {
                return $this->response->setStatusCode(422)->setJSON([
                    'success' => false,
                    'message' => 'Formulir tidak dapat diedit.',
                ]);
            }
            return redirect()->to(base_url('dashboard/status'))
                ->with('error', 'Formulir tidak dapat diedit.');
        }

        // ── Step 1: set wa_verified dari session jika belum ada di POST ──
        // Tidak memblokir — hanya memperkaya data. Validasi wa_verified
        // di PendaftaranValidation bersifat permit_empty sehingga '0'
        // tetap lolos validasi.
        if ($stepNum === 1) {
            $noHp       = $this->request->getPost('no_hp');
            $normalized = $this->normalizePhone($noHp);
            $waSession  = $this->session->get('wa_verified_' . $this->userId());

            $waVerified = (
                $waSession &&
                ! empty($waSession['verified']) &&
                isset($waSession['phone']) &&
                $normalized !== null &&
                $waSession['phone'] === $normalized &&
                (time() - ($waSession['time'] ?? 0)) < 86400
            ) ? '1' : '0';

            // Inject ke $_POST agar dapat dibaca oleh $this->validate()
            // Prioritaskan nilai dari form jika sudah ada
            if (empty($_POST['wa_verified'])) {
                $_POST['wa_verified'] = $waVerified;
            }
        }

        $rules = PendaftaranValidation::getRulesForStep($stepNum);

        if (! empty($rules) && ! $this->validate($rules)) {
            $errors = $this->validator->getErrors();
            if ($isAjax) {
                return $this->response->setStatusCode(422)->setJSON([
                    'success' => false,
                    'message' => implode(' ', $errors),
                    'errors'  => $errors,
                ]);
            }
            return redirect()->back()
                ->withInput()
                ->with('errors', $errors)
                ->with('error', implode(' ', $errors));
        }

        $postData = $this->request->getPost();

        if ($stepNum === 1) {
            $result = $this->service->saveStep1($pendaftaran->id, $postData);
        } elseif ($stepNum === 2) {
            $result = $this->service->saveStep2($pendaftaran->id, $postData);
        } elseif ($stepNum === 3) {
            $result = $this->service->saveStep3($pendaftaran->id, $postData);
        } elseif ($stepNum === 4) {
            $result = $this->service->submitDariStep4($pendaftaran->id, $this->userId());
        } else {
            $result = ['success' => false, 'message' => 'Step tidak valid.'];
        }

        if (! $result['success']) {
            if ($isAjax) {
                return $this->response->setStatusCode(422)->setJSON([
                    'success' => false,
                    'message' => $result['message'],
                ]);
            }
            return redirect()->to(base_url("dashboard/formulir/step/{$stepNum}"))
                ->with('error', $result['message']);
        }

        // ── Step 1–3: lanjut ke step berikutnya ──────────────────────
        if ($stepNum < 4) {
            $nextStep   = $stepNum + 1;
            $redirectTo = base_url("dashboard/formulir/step/{$nextStep}");

            if ($isAjax) {
                return $this->response->setStatusCode(200)->setJSON([
                    'success'  => true,
                    'message'  => "Langkah {$stepNum} berhasil disimpan.",
                    'redirect' => $redirectTo,
                ]);
            }
            return redirect()->to($redirectTo)
                ->with('success', "Langkah {$stepNum} berhasil disimpan.");
        }

        // ── Step 4 (Submit): redirect ke halaman status ───────────────
        $redirectTo = base_url('dashboard/status');

        if ($isAjax) {
            return $this->response->setStatusCode(200)->setJSON([
                'success'        => true,
                'message'        => $result['message'],
                'no_pendaftaran' => $result['no_pendaftaran'] ?? null,
                'redirect'       => $redirectTo,
            ]);
        }

        return redirect()->to($redirectTo)
            ->with('success', $result['message']);
    }

    // =========================================================
    // AUTO-SAVE (AJAX)
    // =========================================================
    public function autosave()
    {
        if ($this->request->getMethod() !== 'post') {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Invalid request',
            ]);
        }

        $pendaftaran = $this->pendaftaranModel->getByUserId($this->userId());
        if (! $pendaftaran) {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => 'Pendaftaran tidak ditemukan',
            ]);
        }

        $stepNum  = (int) $this->request->getPost('step');
        $postData = $this->request->getPost();
        unset($postData['csrf_token'], $postData['step']);

        $this->pendaftaranModel->saveDraft($pendaftaran->id, $postData, $stepNum);

        return $this->response->setStatusCode(200)->setJSON([
            'success' => true,
            'message' => 'Draft tersimpan otomatis.',
        ]);
    }

    // =========================================================
    // LIHAT DOKUMEN — Stream file ke siswa (owner saja)
    // GET /dashboard/dokumen/lihat/(:num)
    // =========================================================
    public function lihatDokumen(int $dokumenId)
    {
        $pendaftaran = $this->pendaftaranModel->getByUserId($this->userId());

        if (! $pendaftaran) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Pendaftaran tidak ditemukan.');
        }

        $dokumen = $this->dokumenModel->find($dokumenId);

        // Pastikan dokumen milik user ini
        if (! $dokumen || (int) $dokumen->pendaftaran_id !== (int) $pendaftaran->id) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Dokumen tidak ditemukan atau akses ditolak.');
        }

        $uploader = new FileUploader();

        try {
            $uploader->stream($dokumen->nama_file_simpan, 'dokumen', $dokumen->tipe_mime);
        } catch (\RuntimeException $e) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException($e->getMessage());
        }
    }

    // =========================================================
    // CHECK WA — Kirim OTP via Fonnte
    // POST /dashboard/formulir/check-wa
    // =========================================================
    public function checkWa()
    {
        // Rate limiting: maks 3 permintaan per 10 menit per user
        $limitKey = 'wa_otp_limit_' . $this->userId();
        $attempts = cache($limitKey) ?? 0;

        if ($attempts >= 3) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terlalu banyak percobaan. Tunggu 10 menit sebelum mencoba lagi.',
            ]);
        }

        $noHp       = trim($this->request->getPost('no_hp') ?? '');
        $normalized = $this->normalizePhone($noHp);

        if (! $normalized) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Format nomor HP tidak valid. Gunakan format: 08xxxxxxxxxx',
            ]);
        }

        // Generate OTP 6 digit
        $otp    = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiry = time() + 300; // berlaku 5 menit

        $otpKey = 'wa_otp_' . $this->userId() . '_' . md5($normalized);
        cache()->save($otpKey, [
            'otp'   => $otp,
            'exp'   => $expiry,
            'phone' => $normalized,
        ], 300);

        cache()->save($limitKey, $attempts + 1, 600);

        $sent = $this->sendWaOtp($normalized, $otp);

        if (! $sent) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal mengirim OTP ke WhatsApp. Pastikan nomor terdaftar di WhatsApp dan coba lagi.',
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => "Kode OTP dikirim ke WhatsApp {$noHp}. Berlaku 5 menit.",
            'masked'  => $this->maskPhone($noHp),
        ]);
    }

    // =========================================================
    // VERIFY WA OTP
    // POST /dashboard/formulir/verify-wa-otp
    // =========================================================
    public function verifyWaOtp()
    {
        $noHp       = trim($this->request->getPost('no_hp') ?? '');
        $inputOtp   = trim($this->request->getPost('otp') ?? '');
        $normalized = $this->normalizePhone($noHp);

        if (! $normalized) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Nomor HP tidak valid.',
            ]);
        }

        $otpKey = 'wa_otp_' . $this->userId() . '_' . md5($normalized);
        $stored = cache($otpKey);

        if (! $stored) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'OTP tidak ditemukan atau sudah kadaluarsa. Kirim ulang OTP.',
            ]);
        }

        if (time() > $stored['exp']) {
            cache()->delete($otpKey);
            return $this->response->setJSON([
                'success' => false,
                'message' => 'OTP sudah kadaluarsa. Silakan kirim ulang.',
            ]);
        }

        $attemptKey = 'wa_otp_attempt_' . $this->userId();
        $wrongCount = cache($attemptKey) ?? 0;

        if ($wrongCount >= 5) {
            cache()->delete($otpKey);
            cache()->delete($attemptKey);
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terlalu banyak percobaan salah. Silakan minta OTP baru.',
            ]);
        }

        if ($inputOtp !== $stored['otp']) {
            cache()->save($attemptKey, $wrongCount + 1, 300);
            $remaining = 5 - ($wrongCount + 1);
            return $this->response->setJSON([
                'success' => false,
                'message' => "OTP salah. Sisa percobaan: {$remaining}",
            ]);
        }

        // OTP benar: simpan verified ke session
        $this->session->set('wa_verified_' . $this->userId(), [
            'phone'    => $normalized,
            'verified' => true,
            'time'     => time(),
        ]);

        cache()->delete($otpKey);
        cache()->delete($attemptKey);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Nomor WhatsApp berhasil diverifikasi! ✓',
        ]);
    }

    // =========================================================
    // STATUS
    // =========================================================
    public function status()
    {
        $pendaftaran = $this->pendaftaranModel->getByUserId($this->userId());

        if (! $pendaftaran) {
            return redirect()->to(base_url('dashboard/formulir'))
                ->with('info', 'Anda belum mengisi formulir pendaftaran.');
        }

        $pendaftaran = $this->pendaftaranModel->getWithRelations($pendaftaran->id);
        $dataDiri    = $this->dataDiriModel->getByPendaftaranId($pendaftaran->id);
        $dokumens    = $this->dokumenModel->getByPendaftaranId($pendaftaran->id);

        $data = [
            'title'       => 'Status Pendaftaran',
            'pendaftaran' => $pendaftaran,
            'dataDiri'    => $dataDiri,
            'dokumens'    => $dokumens,
            'timeline'    => $this->buildTimeline($pendaftaran),
        ];

        return $this->render('pendaftaran/status', $data);
    }

    // =========================================================
    // CETAK BUKTI
    // =========================================================
    public function cetakBukti()
    {
        $pendaftaran = $this->pendaftaranModel->getByUserId($this->userId());

        if (! $pendaftaran || $pendaftaran->status === 'draft') {
            return redirect()->to(base_url('dashboard/status'))
                ->with('error', 'Bukti pendaftaran belum tersedia.');
        }

        $pendaftaran = $this->pendaftaranModel->getWithRelations($pendaftaran->id);
        $dataDiri    = $this->dataDiriModel->getByPendaftaranId($pendaftaran->id);

        $data = [
            'title'       => 'Cetak Bukti Pendaftaran',
            'pendaftaran' => $pendaftaran,
            'dataDiri'    => $dataDiri,
        ];

        return $this->render('pendaftaran/cetak_bukti', $data);
    }

    // =========================================================
    // PRIVATE HELPERS
    // =========================================================

    private function normalizePhone(?string $phone): ?string
    {
        if (empty($phone)) {
            return null;
        }

        $clean = preg_replace('/[\s\-\(\)\+]/', '', $phone);

        if (str_starts_with($clean, '0')) {
            $clean = '62' . substr($clean, 1);
        } elseif (! str_starts_with($clean, '62')) {
            return null;
        }

        if (! preg_match('/^62[0-9]{8,13}$/', $clean)) {
            return null;
        }

        return $clean;
    }

    private function maskPhone(string $phone): string
    {
        $len = strlen($phone);
        if ($len < 8) {
            return $phone;
        }
        return substr($phone, 0, 4) . str_repeat('*', $len - 8) . substr($phone, -4);
    }

    private function sendWaOtp(string $phone, string $otp): bool
    {
        $token = env('FONNTE_TOKEN', '');

        if (empty($token)) {
            log_message('info', "[WA OTP DEV] Kirim ke {$phone}: kode = {$otp}");
            return true;
        }

        $message = "Halo! Kode verifikasi WhatsApp SPMB Anda:\n\n*{$otp}*\n\nBerlaku 5 menit. Jangan bagikan ke siapapun.";

        try {
            $client = \Config\Services::curlrequest();

            $response = $client->post('https://api.fonnte.com/send', [
                'headers'     => ['Authorization' => $token],
                'form_params' => [
                    'target'  => $phone,
                    'message' => $message,
                ],
                'timeout'     => 10,
                'http_errors' => false,
            ]);

            $body = json_decode((string) $response->getBody(), true);

            if (! isset($body['status']) || $body['status'] !== true) {
                log_message('warning', '[WA OTP] Fonnte response: ' . json_encode($body));
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            log_message('error', '[WA OTP] Exception: ' . $e->getMessage());
            return false;
        }
    }

    private function getStepMeta(): array
    {
        return [
            1 => ['label' => 'Data Pribadi',    'icon' => 'user'],
            2 => ['label' => 'Pilihan Jurusan', 'icon' => 'graduation'],
            3 => ['label' => 'Data Orang Tua',  'icon' => 'users'],
            4 => ['label' => 'Upload Dokumen',  'icon' => 'upload'],
        ];
    }

    private function getJenisDokumenSemua(): array
    {
        // Dinamis dari DB via helper — tidak hardcode lagi.
        // Admin dapat mengelola jenis dokumen di halaman Master Data.
        return jenis_dokumen_semua();
    }

    private function buildTimeline(object $pendaftaran): array
    {
        $statusOrder = [
            'draft'        => 0,
            'revisi'       => 1,
            'submitted'    => 2,
            'verifikasi'   => 3,
            'seleksi'      => 4,
            'lulus'        => 5,
            'tidak_lulus'  => 5,
            'daftar_ulang' => 6,
            'siswa_aktif'  => 7,
        ];

        $currentOrder = $statusOrder[$pendaftaran->status] ?? 0;

        $steps = [
            ['status' => 'draft',        'label' => 'Formulir Dibuat',      'icon' => 'file-alt',       'order' => 0],
            ['status' => 'submitted',    'label' => 'Formulir Dikirim',     'icon' => 'paper-plane',    'order' => 2],
            ['status' => 'verifikasi',   'label' => 'Dalam Verifikasi',     'icon' => 'search',         'order' => 3],
            ['status' => 'seleksi',      'label' => 'Proses Seleksi',       'icon' => 'trophy',         'order' => 4],
            ['status' => 'lulus',        'label' => 'Pengumuman Kelulusan', 'icon' => 'bullhorn',       'order' => 5],
            ['status' => 'daftar_ulang', 'label' => 'Daftar Ulang',         'icon' => 'sync',           'order' => 6],
            ['status' => 'siswa_aktif',  'label' => 'Siswa Aktif',          'icon' => 'graduation-cap', 'order' => 7],
        ];

        $timeline = [];
        foreach ($steps as $step) {
            $order = $step['order'];
            if ($currentOrder >= $order) {
                $step['state'] = 'done';
            } elseif ($currentOrder + 1 === $order) {
                $step['state'] = 'current';
            } else {
                $step['state'] = 'pending';
            }
            $timeline[] = $step;
        }

        return $timeline;
    }
}
