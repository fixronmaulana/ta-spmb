<?php

namespace App\Modules\Auth\Services;

use App\Modules\Auth\Models\UserModel;
use App\Modules\Auth\Models\EmailOtpModel;
use CodeIgniter\Cookie\Cookie;

class AuthService
{
    protected UserModel     $userModel;
    protected EmailOtpModel $otpModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->otpModel  = new EmailOtpModel();
    }

    // =========================================================
    // REGISTER
    // =========================================================

    /**
     * Register user baru — simpan dengan is_active=0, kirim OTP verifikasi
     */
    public function register(array $data): array
    {
        // Cek email sudah terdaftar
        if ($this->userModel->findByEmail($data['email'])) {
            return ['success' => false, 'message' => 'Email sudah terdaftar. Silakan gunakan email lain.'];
        }

        // Generate username dari email (bagian sebelum @)
        $username = explode('@', $data['email'])[0];

        // Cek username sudah ada, jika ya tambahkan angka random
        $usernameExists = $this->userModel->where('username', $username)->first();
        if ($usernameExists) {
            $username = $username . rand(100, 999);
        }

        // Cari role_id untuk calon_siswa
        $db   = \Config\Database::connect();
        $role = $db->table('roles')->where('nama_role', 'calon_siswa')->get()->getRow();

        if (! $role) {
            return ['success' => false, 'message' => 'Role calon_siswa tidak ditemukan. Hubungi administrator.'];
        }

        // Simpan user — is_active=0, email_verified_at=NULL sampai OTP diverifikasi
        $userId = $this->userModel->insert([
            'role_id'           => $role->id,
            'username'          => $username,
            'nama_lengkap'      => trim($data['name']),
            'email'             => strtolower(trim($data['email'])),
            'password'          => $data['password'],
            'no_telp'           => $data['phone'] ?? null,
            'is_active'         => 0,              // Belum aktif
            'email_verified_at' => null,           // Belum terverifikasi
        ]);

        if (! $userId) {
            return ['success' => false, 'message' => 'Gagal membuat akun. Silakan coba lagi.'];
        }

        // Generate & kirim OTP
        $otp = $this->otpModel->createOtp($userId);
        $user = $this->userModel->find($userId);

        $emailSent = $this->sendOtpEmail($user, $otp);

        if (! $emailSent) {
            // Tetap lanjut walau email gagal, tapi beri info
            return [
                'success'        => true,
                'user_id'        => $userId,
                'email'          => $user->email,
                'email_sent'     => false,
                'message'        => 'Akun dibuat, tapi gagal kirim email OTP. Gunakan tombol kirim ulang.',
            ];
        }

        return [
            'success'        => true,
            'user_id'        => $userId,
            'email'          => $user->email,
            'email_sent'     => true,
            'message'        => 'Akun berhasil dibuat! Kode verifikasi telah dikirim ke ' . $user->email,
        ];
    }

    // =========================================================
    // VERIFIKASI OTP
    // =========================================================

    /**
     * Verifikasi OTP yang dimasukkan calon siswa
     */
    public function verifyEmailOtp(int $userId, string $otp): array
    {
        $user = $this->userModel->find($userId);

        if (! $user) {
            return ['success' => false, 'message' => 'Akun tidak ditemukan.'];
        }

        if ($user->email_verified_at) {
            // Sudah terverifikasi sebelumnya, langsung login
            $this->activateAndLogin($user);
            return ['success' => true, 'message' => 'Email sudah terverifikasi. Anda telah login.'];
        }

        $valid = $this->otpModel->verifyOtp($userId, trim($otp));

        if (! $valid) {
            return ['success' => false, 'message' => 'Kode OTP salah, sudah digunakan, atau sudah kedaluwarsa.'];
        }

        // Aktifkan akun
        $this->userModel->update($userId, [
            'is_active'         => 1,
            'email_verified_at' => date('Y-m-d H:i:s'),
        ]);

        $user = $this->userModel->find($userId);

        // Auto login setelah verifikasi berhasil
        $this->setSession($user);

        return ['success' => true, 'message' => 'Email berhasil diverifikasi! Selamat datang.'];
    }

    /**
     * Kirim ulang OTP (dengan pengecekan cooldown)
     */
    public function resendOtp(int $userId): array
    {
        $user = $this->userModel->find($userId);

        if (! $user) {
            return ['success' => false, 'message' => 'Akun tidak ditemukan.'];
        }

        if ($user->email_verified_at) {
            return ['success' => false, 'message' => 'Email sudah terverifikasi.'];
        }

        // Cek cooldown dan batas kirim
        $canResend = $this->otpModel->canResend($userId);
        if (! $canResend['allowed']) {
            return ['success' => false, 'message' => $canResend['message']];
        }

        $otp       = $this->otpModel->createOtp($userId);
        $emailSent = $this->sendOtpEmail($user, $otp);

        if (! $emailSent) {
            return ['success' => false, 'message' => 'Gagal mengirim email. Periksa koneksi atau coba beberapa saat lagi.'];
        }

        return ['success' => true, 'message' => 'Kode OTP baru telah dikirim ke ' . $user->email];
    }

    // =========================================================
    // LOGIN
    // =========================================================

    public function login(string $email, string $password, bool $remember = false): array
    {
        $user = $this->userModel->findByEmail(strtolower(trim($email)));

        if (! $user) {
            return ['success' => false, 'message' => 'Email atau password salah.'];
        }

        if (! password_verify($password, $user->password)) {
            return ['success' => false, 'message' => 'Email atau password salah.'];
        }

        // Cek apakah email sudah diverifikasi
        if (! $user->email_verified_at) {
            // Kirim OTP baru agar user bisa langsung verifikasi
            $otp = $this->otpModel->createOtp($user->id);
            $this->sendOtpEmail($user, $otp);

            return [
                'success'        => false,
                'need_verify'    => true,
                'user_id'        => $user->id,
                'email'          => $user->email,
                'message'        => 'Email belum diverifikasi. Kode OTP baru telah dikirim ke ' . $user->email,
            ];
        }

        if (! $user->is_active) {
            return ['success' => false, 'message' => 'Akun Anda tidak aktif. Hubungi administrator.'];
        }

        // Update last login
        $this->userModel->updateLastLogin($user->id, service('request')->getIPAddress());

        // Set session
        $this->setSession($user);

        // Remember me
        if ($remember) {
            $token = bin2hex(random_bytes(32));
            $this->userModel->setRememberToken($user->id, $token);

            $cookie = new Cookie('remember_token', $token, [
                'expires'  => time() + (30 * 24 * 3600),
                'path'     => '/',
                'httponly' => true,
                'secure'   => false,
                'samesite' => 'Lax',
            ]);
            service('response')->setCookie($cookie);
        }

        return ['success' => true, 'user' => $user, 'message' => 'Login berhasil!'];
    }

    // =========================================================
    // LOGOUT
    // =========================================================

    public function logout(): void
    {
        $userId = session()->get('user_id');

        if ($userId) {
            $this->userModel->clearRememberToken($userId);
        }

        helper('cookie');
        delete_cookie('remember_token');
        session()->destroy();
    }

    // =========================================================
    // REMEMBER TOKEN
    // =========================================================

    public function loginViaRememberToken(string $token): bool
    {
        $user = $this->userModel->findByRememberToken($token);

        if (! $user) {
            return false;
        }

        $this->userModel->updateLastLogin($user->id, service('request')->getIPAddress());
        $this->setSession($user);

        return true;
    }

    // =========================================================
    // FORGOT / RESET PASSWORD
    // =========================================================

    public function forgotPassword(string $email): array
    {
        $user = $this->userModel->findByEmail($email);

        if (! $user) {
            return ['success' => true, 'message' => 'Jika email terdaftar, link reset akan dikirim.'];
        }

        $token     = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

        db_connect()->table('password_resets')->where('email', $email)->delete();
        db_connect()->table('password_resets')->insert([
            'email'      => $email,
            'token'      => $token,
            'expires_at' => $expiresAt,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        // TODO: kirim email reset password
        // $this->sendPasswordResetEmail($user, $token);

        return ['success' => true, 'message' => 'Link reset password telah dikirim ke email Anda.', 'token' => $token];
    }

    public function resetPassword(string $token, string $password): array
    {
        $reset = db_connect()->table('password_resets')
            ->where('token', $token)
            ->where('expires_at >', date('Y-m-d H:i:s'))
            ->get()->getRow();

        if (! $reset) {
            return ['success' => false, 'message' => 'Token tidak valid atau sudah kedaluwarsa.'];
        }

        $user = $this->userModel->findByEmail($reset->email);

        if (! $user) {
            return ['success' => false, 'message' => 'Akun tidak ditemukan.'];
        }

        $this->userModel->update($user->id, ['password' => $password]);
        db_connect()->table('password_resets')->where('token', $token)->delete();

        return ['success' => true, 'message' => 'Password berhasil direset. Silakan login.'];
    }

    // =========================================================
    // PRIVATE HELPERS
    // =========================================================

    /**
     * Kirim email berisi kode OTP via template HTML
     */
    private function sendOtpEmail(object $user, string $otp): bool
    {
        try {
            // Buat instance Email FRESH dengan config eksplisit
            // JANGAN gunakan Services::email() karena bisa pakai cache/default yg salah
            $emailConfig = new \Config\Email();

            $email = new \CodeIgniter\Email\Email($emailConfig);

            $email->setFrom(
                $emailConfig->fromEmail,
                $emailConfig->fromName
            );
            $email->setTo($user->email);
            $email->setSubject('Kode Verifikasi Email — SPMB SMK Al-Munawwir IIBS');
            $email->setMailType('html');
            $email->setMessage($this->buildOtpEmailTemplate($user->nama_lengkap, $otp));

            $sent = $email->send(false);

            if (! $sent) {
                log_message('error', '[AuthService::sendOtpEmail] Debug: ' . $email->printDebugger(['headers', 'subject', 'body']));
            }

            return $sent;

        } catch (\Throwable $e) {
            log_message('error', '[AuthService::sendOtpEmail] ' . $e->getMessage());
            return false;
        }
}

    /**
     * Template HTML email OTP
     */
    private function buildOtpEmailTemplate(string $nama, string $otp): string
    {
        $digits = str_split($otp);

        $digitBoxes = '';
        foreach ($digits as $d) {
            $digitBoxes .= "
            <td style=\"width:44px;height:52px;text-align:center;vertical-align:middle;
                        background:#f0f4ff;border:2px solid #4f7ef8;border-radius:8px;
                        font-size:28px;font-weight:700;color:#1a3a8f;letter-spacing:0;\">
                {$d}
            </td>
            <td style=\"width:8px;\"></td>";
        }

        return <<<HTML
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Kode Verifikasi Email</title>
</head>
<body style="margin:0;padding:0;background:#f5f7fa;font-family:Arial,Helvetica,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f5f7fa;padding:32px 16px;">
  <tr><td align="center">
    <table width="560" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,0.07);">

      <!-- Header -->
      <tr>
        <td style="background:#1a3a8f;padding:28px 40px;text-align:center;">
          <p style="margin:0;color:#ffffff;font-size:20px;font-weight:700;letter-spacing:0.5px;">
            SPMB SMK Al-Munawwir IIBS
          </p>
          <p style="margin:6px 0 0;color:#a8c0f0;font-size:13px;">
            Sistem Penerimaan Murid Baru
          </p>
        </td>
      </tr>

      <!-- Body -->
      <tr>
        <td style="padding:36px 40px 28px;">
          <p style="margin:0 0 8px;font-size:15px;color:#374151;">Assalamu'alaikum,</p>
          <p style="margin:0 0 20px;font-size:15px;color:#374151;">
            Halo <strong>{$nama}</strong>, gunakan kode OTP berikut untuk memverifikasi email Anda:
          </p>

          <!-- OTP Boxes -->
          <table cellpadding="0" cellspacing="0" style="margin:0 auto 24px;">
            <tr>
              {$digitBoxes}
            </tr>
          </table>

          <p style="margin:0 0 16px;font-size:13px;color:#6b7280;text-align:center;">
            Kode berlaku selama <strong>10 menit</strong> dan hanya dapat digunakan <strong>sekali</strong>.
          </p>

          <table width="100%" cellpadding="0" cellspacing="0"
                 style="background:#fef9ec;border:1px solid #fcd34d;border-radius:8px;margin-bottom:20px;">
            <tr>
              <td style="padding:12px 16px;font-size:13px;color:#92400e;">
                &#9888; Jangan bagikan kode ini kepada siapa pun, termasuk pihak sekolah.
                Kami tidak pernah meminta kode OTP Anda.
              </td>
            </tr>
          </table>

          <p style="margin:0;font-size:13px;color:#9ca3af;">
            Jika Anda tidak merasa mendaftar, abaikan email ini.
          </p>
        </td>
      </tr>

      <!-- Footer -->
      <tr>
        <td style="background:#f9fafb;padding:20px 40px;border-top:1px solid #e5e7eb;text-align:center;">
          <p style="margin:0;font-size:12px;color:#9ca3af;">
            &copy; {$this->year()} SPMB SMK Al-Munawwir IIBS &mdash; Email otomatis, tidak perlu dibalas.
          </p>
        </td>
      </tr>

    </table>
  </td></tr>
</table>
</body>
</html>
HTML;
    }

    private function year(): string
    {
        return date('Y');
    }

    /**
     * Aktifkan user & set session (dipanggil jika sudah verified tapi belum login)
     */
    private function activateAndLogin(object $user): void
    {
        if (! $user->is_active) {
            $this->userModel->update($user->id, ['is_active' => 1]);
            $user = $this->userModel->find($user->id);
        }
        $this->setSession($user);
    }

    /**
     * Set session data setelah login / verifikasi berhasil
     */
    private function setSession(object $user): void
    {
        $db   = \Config\Database::connect();
        $role = $db->table('roles')->where('id', $user->role_id)->get()->getRow();

        session()->set([
            'user_id'     => $user->id,
            'user_name'   => $user->nama_lengkap,
            'user_email'  => $user->email,
            'user_role'   => $role ? $role->nama_role : 'calon_siswa',
            'user_active' => $user->is_active,
            'logged_in'   => true,
        ]);

        session()->regenerate();
    }
}