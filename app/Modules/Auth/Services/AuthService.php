<?php

namespace App\Modules\Auth\Services;

use App\Modules\Auth\Models\UserModel;
use App\Modules\Auth\Models\EmailOtpModel;
use CodeIgniter\Cookie\Cookie;

class AuthService
{
  protected UserModel     $userModel;
  protected EmailOtpModel $otpModel;

  // Token berlaku 20 menit
  private const RESET_TOKEN_TTL_MINUTES = 20;

  public function __construct()
  {
    $this->userModel = new UserModel();
    $this->otpModel  = new EmailOtpModel();
  }

  // =========================================================
  // REGISTER
  // =========================================================

  public function register(array $data): array
  {
    if ($this->userModel->findByEmail($data['email'])) {
      return ['success' => false, 'message' => 'Email sudah terdaftar. Silakan gunakan email lain.'];
    }

    $username = explode('@', $data['email'])[0];
    if ($this->userModel->where('username', $username)->first()) {
      $username = $username . rand(100, 999);
    }

    $db   = \Config\Database::connect();
    $role = $db->table('roles')->where('nama_role', 'calon_siswa')->get()->getRow();

    if (! $role) {
      return ['success' => false, 'message' => 'Role calon_siswa tidak ditemukan. Hubungi administrator.'];
    }

    $userId = $this->userModel->insert([
      'role_id'           => $role->id,
      'username'          => $username,
      'nama_lengkap'      => trim($data['name']),
      'email'             => strtolower(trim($data['email'])),
      'password'          => $data['password'],
      'no_telp'           => $data['phone'] ?? null,
      'is_active'         => 0,
      'email_verified_at' => null,
    ]);

    if (! $userId) {
      return ['success' => false, 'message' => 'Gagal membuat akun. Silakan coba lagi.'];
    }

    $otp  = $this->otpModel->createOtp($userId);
    $user = $this->userModel->find($userId);

    $emailSent = $this->sendOtpEmail($user, $otp);

    if (! $emailSent) {
      return [
        'success'    => true,
        'user_id'    => $userId,
        'email'      => $user->email,
        'email_sent' => false,
        'message'    => 'Akun dibuat, tapi gagal kirim email OTP. Gunakan tombol kirim ulang.',
      ];
    }

    return [
      'success'    => true,
      'user_id'    => $userId,
      'email'      => $user->email,
      'email_sent' => true,
      'message'    => 'Akun berhasil dibuat! Kode verifikasi telah dikirim ke ' . $user->email,
    ];
  }

  // =========================================================
  // VERIFIKASI OTP
  // =========================================================

  public function verifyEmailOtp(int $userId, string $otp): array
  {
    $user = $this->userModel->find($userId);

    if (! $user) {
      return ['success' => false, 'message' => 'Akun tidak ditemukan.'];
    }

    if ($user->email_verified_at) {
      $this->activateAndLogin($user);
      return ['success' => true, 'message' => 'Email sudah terverifikasi. Anda telah login.'];
    }

    $valid = $this->otpModel->verifyOtp($userId, trim($otp));

    if (! $valid) {
      return ['success' => false, 'message' => 'Kode OTP salah, sudah digunakan, atau sudah kedaluwarsa.'];
    }

    $this->userModel->update($userId, [
      'is_active'         => 1,
      'email_verified_at' => date('Y-m-d H:i:s'),
    ]);

    $user = $this->userModel->find($userId);
    $this->setSession($user);

    return ['success' => true, 'message' => 'Email berhasil diverifikasi! Selamat datang.'];
  }

  public function resendOtp(int $userId): array
  {
    $user = $this->userModel->find($userId);

    if (! $user) {
      return ['success' => false, 'message' => 'Akun tidak ditemukan.'];
    }

    if ($user->email_verified_at) {
      return ['success' => false, 'message' => 'Email sudah terverifikasi.'];
    }

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

    if (! $user->email_verified_at) {
      $otp = $this->otpModel->createOtp($user->id);
      $this->sendOtpEmail($user, $otp);

      return [
        'success'     => false,
        'need_verify' => true,
        'user_id'     => $user->id,
        'email'       => $user->email,
        'message'     => 'Email belum diverifikasi. Kode OTP baru telah dikirim ke ' . $user->email,
      ];
    }

    if (! $user->is_active) {
      return ['success' => false, 'message' => 'Akun Anda tidak aktif. Hubungi administrator.'];
    }

    // ------------------------------------------------------------------
    // SINGLE SESSION CHECK
    // Jika user sudah punya session_token aktif di DB, berarti ada sesi
    // lain yang sedang berjalan. Login ditolak kecuali sesi lama di-force
    // logout terlebih dahulu.
    // ------------------------------------------------------------------
    if (! empty($user->session_token)) {
      return [
        'success'        => false,
        'already_active' => true,
        'message'        => 'Akun ini sedang digunakan di perangkat/browser lain. '
          . 'Silakan logout dari sesi sebelumnya terlebih dahulu, '
          . 'atau klik "Paksa Login" untuk mengakhiri sesi lama dan login di sini.',
        'user_id'        => $user->id,
      ];
    }
    // ------------------------------------------------------------------

    $this->userModel->updateLastLogin($user->id, service('request')->getIPAddress());
    $this->setSession($user);

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

  /**
   * Login paksa: akhiri sesi lama, buat sesi baru di browser ini.
   * Dipanggil ketika user mengkonfirmasi "Paksa Login" setelah muncul
   * pesan already_active.
   */
  public function forceLogin(int $userId): array
  {
    $user = $this->userModel->find($userId);

    if (! $user) {
      return ['success' => false, 'message' => 'Akun tidak ditemukan.'];
    }

    if (! $user->is_active) {
      return ['success' => false, 'message' => 'Akun tidak aktif.'];
    }

    // Hapus session token lama agar sesi di browser sebelumnya tidak valid
    $this->userModel->clearSessionToken($userId);

    $this->userModel->updateLastLogin($user->id, service('request')->getIPAddress());
    $this->setSession($user);

    return ['success' => true, 'user' => $user, 'message' => 'Login berhasil. Sesi sebelumnya telah diakhiri.'];
  }

  // =========================================================
  // LOGOUT
  // =========================================================

  public function logout(): void
  {
    $userId = session()->get('user_id');

    if ($userId) {
      // Hapus session token dari DB agar sesi ini benar-benar berakhir
      $this->userModel->clearSessionToken($userId);
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

    // Cek apakah sudah ada sesi aktif lain
    if (! empty($user->session_token)) {
      // Jika remember token cocok tapi ada sesi lain, tolak auto-login
      log_message('info', '[AuthService::loginViaRememberToken] Sesi aktif lain ditemukan untuk user ' . $user->id . ', remember token diabaikan.');
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
    $genericMessage = 'Jika email terdaftar, link reset password akan dikirim dalam beberapa saat.';

    $user = $this->userModel->findByEmail(strtolower(trim($email)));

    if (! $user) {
      return ['success' => true, 'message' => $genericMessage];
    }

    $db = db_connect();

    $db->table('password_resets')->where('email', $user->email)->delete();

    $token     = bin2hex(random_bytes(32));
    $expiresAt = date('Y-m-d H:i:s', strtotime('+' . self::RESET_TOKEN_TTL_MINUTES . ' minutes'));

    $db->table('password_resets')->insert([
      'email'      => $user->email,
      'token'      => $token,
      'expires_at' => $expiresAt,
      'created_at' => date('Y-m-d H:i:s'),
    ]);

    $sent = $this->sendPasswordResetEmail($user, $token);

    if (! $sent) {
      log_message('error', '[AuthService::forgotPassword] Gagal kirim email reset ke ' . $user->email);
    }

    return ['success' => true, 'message' => 'Link reset password telah dikirim ke email Anda.'];
  }

  public function resetPassword(string $token, string $password): array
  {
    $db = db_connect();

    $reset = $db->table('password_resets')
      ->where('token', $token)
      ->where('expires_at >', date('Y-m-d H:i:s'))
      ->get()->getRow();

    if (! $reset) {
      return ['success' => false, 'message' => 'Link reset tidak valid atau sudah kedaluwarsa. Silakan minta ulang.'];
    }

    $user = $this->userModel->findByEmail($reset->email);

    if (! $user) {
      return ['success' => false, 'message' => 'Akun tidak ditemukan.'];
    }

    $this->userModel->update($user->id, ['password' => $password]);

    $db->table('password_resets')->where('token', $token)->delete();

    return ['success' => true, 'message' => 'Password berhasil diperbarui. Silakan login dengan password baru Anda.'];
  }

  // =========================================================
  // PRIVATE: EMAIL SENDERS
  // =========================================================

  private function sendOtpEmail(object $user, string $otp): bool
  {
    try {
      $emailConfig = new \Config\Email();
      $email       = new \CodeIgniter\Email\Email($emailConfig);

      $email->setFrom($emailConfig->fromEmail, $emailConfig->fromName);
      $email->setTo($user->email);
      $email->setSubject('Kode Verifikasi Email — SPMB SMK Al-Munawwir IIBS');
      $email->setMailType('html');
      $email->setMessage($this->buildOtpEmailTemplate($user->nama_lengkap, $otp));

      $sent = $email->send(false);

      if (! $sent) {
        log_message('error', '[AuthService::sendOtpEmail] ' . $email->printDebugger(['headers', 'subject', 'body']));
      }

      return $sent;
    } catch (\Throwable $e) {
      log_message('error', '[AuthService::sendOtpEmail] ' . $e->getMessage());
      return false;
    }
  }

  private function sendPasswordResetEmail(object $user, string $token): bool
  {
    try {
      $emailConfig = new \Config\Email();
      $email       = new \CodeIgniter\Email\Email($emailConfig);

      $resetUrl = base_url('auth/reset-password/' . $token);

      $email->setFrom($emailConfig->fromEmail, $emailConfig->fromName);
      $email->setTo($user->email);
      $email->setSubject('Reset Password — SPMB SMK Al-Munawwir IIBS');
      $email->setMailType('html');
      $email->setMessage($this->buildResetPasswordEmailTemplate(
        $user->nama_lengkap,
        $resetUrl,
        self::RESET_TOKEN_TTL_MINUTES
      ));

      $sent = $email->send(false);

      if (! $sent) {
        log_message('error', '[AuthService::sendPasswordResetEmail] ' . $email->printDebugger(['headers', 'subject', 'body']));
      }

      return $sent;
    } catch (\Throwable $e) {
      log_message('error', '[AuthService::sendPasswordResetEmail] ' . $e->getMessage());
      return false;
    }
  }

  // =========================================================
  // PRIVATE: EMAIL TEMPLATES
  // =========================================================

  private function buildOtpEmailTemplate(string $nama, string $otp): string
  {
    $digits     = str_split($otp);
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

    $year = $this->year();

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
      <tr>
        <td style="background:#1a3a8f;padding:28px 40px;text-align:center;">
          <p style="margin:0;color:#ffffff;font-size:20px;font-weight:700;letter-spacing:0.5px;">SPMB SMK Al-Munawwir IIBS</p>
          <p style="margin:6px 0 0;color:#a8c0f0;font-size:13px;">Sistem Penerimaan Murid Baru</p>
        </td>
      </tr>
      <tr>
        <td style="padding:36px 40px 28px;">
          <p style="margin:0 0 8px;font-size:15px;color:#374151;">Assalamu'alaikum,</p>
          <p style="margin:0 0 20px;font-size:15px;color:#374151;">
            Halo <strong>{$nama}</strong>, gunakan kode OTP berikut untuk memverifikasi email Anda:
          </p>
          <table cellpadding="0" cellspacing="0" style="margin:0 auto 24px;">
            <tr>{$digitBoxes}</tr>
          </table>
          <p style="margin:0 0 16px;font-size:13px;color:#6b7280;text-align:center;">
            Kode berlaku selama <strong>10 menit</strong> dan hanya dapat digunakan <strong>sekali</strong>.
          </p>
          <table width="100%" cellpadding="0" cellspacing="0"
                 style="background:#fef9ec;border:1px solid #fcd34d;border-radius:8px;margin-bottom:20px;">
            <tr>
              <td style="padding:12px 16px;font-size:13px;color:#92400e;">
                &#9888; Jangan bagikan kode ini kepada siapa pun. Kami tidak pernah meminta kode OTP Anda.
              </td>
            </tr>
          </table>
          <p style="margin:0;font-size:13px;color:#9ca3af;">Jika Anda tidak merasa mendaftar, abaikan email ini.</p>
        </td>
      </tr>
      <tr>
        <td style="background:#f9fafb;padding:20px 40px;border-top:1px solid #e5e7eb;text-align:center;">
          <p style="margin:0;font-size:12px;color:#9ca3af;">
            &copy; {$year} SPMB SMK Al-Munawwir IIBS &mdash; Email otomatis, tidak perlu dibalas.
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

  private function buildResetPasswordEmailTemplate(string $nama, string $resetUrl, int $ttlMinutes): string
  {
    $year       = $this->year();
    $expiryText = $ttlMinutes . ' menit';

    return <<<HTML
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Reset Password</title>
</head>
<body style="margin:0;padding:0;background:#f5f7fa;font-family:Arial,Helvetica,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f5f7fa;padding:32px 16px;">
  <tr><td align="center">
    <table width="560" cellpadding="0" cellspacing="0"
           style="background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,0.07);">
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
      <tr>
        <td style="padding:36px 40px 28px;">
          <table cellpadding="0" cellspacing="0" style="margin:0 auto 24px;">
            <tr>
              <td style="width:64px;height:64px;background:#eff6ff;border-radius:50%;text-align:center;vertical-align:middle;">
                <span style="font-size:30px;">&#128274;</span>
              </td>
            </tr>
          </table>
          <h2 style="margin:0 0 8px;font-size:20px;font-weight:700;color:#111827;text-align:center;">
            Permintaan Reset Password
          </h2>
          <p style="margin:0 0 8px;font-size:15px;color:#374151;">Assalamu'alaikum,</p>
          <p style="margin:0 0 24px;font-size:15px;color:#374151;">
            Halo <strong>{$nama}</strong>, kami menerima permintaan untuk mereset password akun SPMB Anda.
            Klik tombol di bawah ini untuk membuat password baru:
          </p>
          <table cellpadding="0" cellspacing="0" style="margin:0 auto 28px;">
            <tr>
              <td style="background:#1a3a8f;border-radius:8px;text-align:center;">
                <a href="{$resetUrl}"
                   style="display:inline-block;padding:14px 36px;color:#ffffff;text-decoration:none;
                          font-size:15px;font-weight:700;letter-spacing:0.3px;">
                  Reset Password Saya
                </a>
              </td>
            </tr>
          </table>
          <p style="margin:0 0 8px;font-size:13px;color:#6b7280;text-align:center;">
            Atau salin tautan berikut ke browser Anda:
          </p>
          <p style="margin:0 0 24px;font-size:12px;color:#4f7ef8;text-align:center;word-break:break-all;">
            <a href="{$resetUrl}" style="color:#4f7ef8;">{$resetUrl}</a>
          </p>
          <table width="100%" cellpadding="0" cellspacing="0"
                 style="background:#fef9ec;border:1px solid #fcd34d;border-radius:8px;margin-bottom:20px;">
            <tr>
              <td style="padding:12px 16px;font-size:13px;color:#92400e;">
                &#9888; Link ini hanya berlaku selama <strong>{$expiryText}</strong> dan hanya dapat digunakan
                <strong>sekali</strong>. Setelah itu, Anda perlu mengajukan permintaan baru.
              </td>
            </tr>
          </table>
          <table width="100%" cellpadding="0" cellspacing="0"
                 style="background:#fef2f2;border:1px solid #fca5a5;border-radius:8px;margin-bottom:20px;">
            <tr>
              <td style="padding:12px 16px;font-size:13px;color:#991b1b;">
                &#128683; Jika Anda tidak meminta reset password, abaikan email ini.
                Password Anda <strong>tidak akan berubah</strong> selama link tidak diklik.
              </td>
            </tr>
          </table>
          <p style="margin:0;font-size:13px;color:#9ca3af;">
            Dikirim karena ada permintaan reset password untuk akun yang terkait dengan email ini.
          </p>
        </td>
      </tr>
      <tr>
        <td style="background:#f9fafb;padding:20px 40px;border-top:1px solid #e5e7eb;text-align:center;">
          <p style="margin:0;font-size:12px;color:#9ca3af;">
            &copy; {$year} SPMB SMK Al-Munawwir IIBS &mdash; Email otomatis, tidak perlu dibalas.
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

  // =========================================================
  // PRIVATE: SESSION HELPERS
  // =========================================================

  private function activateAndLogin(object $user): void
  {
    if (! $user->is_active) {
      $this->userModel->update($user->id, ['is_active' => 1]);
      $user = $this->userModel->find($user->id);
    }
    $this->setSession($user);
  }

  /**
   * Buat session PHP dan simpan session_token unik ke DB.
   * Session token inilah yang divalidasi di setiap request untuk
   * memastikan tidak ada sesi paralel.
   */
  private function setSession(object $user): void
  {
    $db   = \Config\Database::connect();
    $role = $db->table('roles')->where('id', $user->role_id)->get()->getRow();

    // Buat token unik untuk sesi ini dan simpan ke DB
    $sessionToken = $this->userModel->createSessionToken($user->id);

    session()->set([
      'user_id'       => $user->id,
      'user_name'     => $user->nama_lengkap,
      'user_email'    => $user->email,
      'user_role'     => $role ? $role->nama_role : 'calon_siswa',
      'user_active'   => $user->is_active,
      'logged_in'     => true,
      'session_token' => $sessionToken, // Disimpan di session PHP untuk divalidasi tiap request
    ]);

    session()->regenerate();
  }
}
