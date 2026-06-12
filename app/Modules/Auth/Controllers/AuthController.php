<?php

namespace App\Modules\Auth\Controllers;

use App\Controllers\BaseController;
use App\Modules\Auth\Services\AuthService;
use App\Modules\Auth\Validation\AuthValidation;

class AuthController extends BaseController
{
    protected AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    // =========================================================
    // PUBLIC PAGES
    // =========================================================

    public function landing()
    {
        if (session()->get('logged_in')) {
            return $this->redirectByRole();
        }

        $data = [
            'title'    => 'SPMB SMK Al-Munawwir IIBS',
            'jurusans' => (new \App\Modules\MasterData\Models\JurusanModel())->findAll(),
            'periode'  => (new \App\Modules\MasterData\Models\PeriodeModel())->getPeriodeAktif(),
        ];

        return view('App\Modules\Auth\Views\landing', $data);
    }

    public function profilSekolah()
    {
        return view('App\Modules\Auth\Views\profil_sekolah', ['title' => 'Profil Sekolah']);
    }

    public function jurusan()
    {
        $data = [
            'title'    => 'Program Keahlian',
            'jurusans' => (new \App\Modules\MasterData\Models\JurusanModel())->where('is_active', 1)->findAll(),
        ];
        return view('App\Modules\Auth\Views\jurusan', $data);
    }

    public function panduan()
    {
        return view('App\Modules\Auth\Views\panduan', ['title' => 'Panduan SPMB']);
    }

    public function kontak()
    {
        return view('App\Modules\Auth\Views\kontak', ['title' => 'Kontak']);
    }

    // =========================================================
    // LOGIN
    // =========================================================

    public function login()
    {
        if (session()->get('logged_in')) {
            return $this->redirectByRole();
        }

        return view('App\Modules\Auth\Views\login', [
            'title' => 'Login — SPMB',
        ]);
    }

    public function doLogin()
    {
        $rules = AuthValidation::loginRules();

        if (! $this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $result = $this->authService->login(
            $this->request->getPost('email'),
            $this->request->getPost('password'),
            (bool) $this->request->getPost('remember')
        );

        // Email belum diverifikasi — arahkan ke halaman OTP
        if (! $result['success'] && ! empty($result['need_verify'])) {
            session()->set('otp_user_id', $result['user_id']);
            session()->set('otp_email',   $result['email']);
            return redirect()->to(base_url('auth/verify-otp'))
                ->with('info', $result['message']);
        }

        // Akun sedang aktif di perangkat/browser lain — tawarkan force login
        if (! $result['success'] && ! empty($result['already_active'])) {
            // Simpan user_id sementara untuk digunakan oleh doForceLogin
            session()->set('force_login_user_id', $result['user_id']);

            return redirect()->back()
                ->withInput()
                ->with('already_active', $result['message']);
        }

        if (! $result['success']) {
            return redirect()->back()
                ->withInput()
                ->with('error', $result['message']);
        }

        $redirectUrl = session()->get('redirect_url');
        session()->remove('redirect_url');

        if ($redirectUrl && strpos($redirectUrl, base_url()) === 0) {
            return redirect()->to($redirectUrl)->with('success', $result['message']);
        }

        return $this->redirectByRole()->with('success', 'Selamat datang, ' . session()->get('user_name') . '!');
    }

    /**
     * Paksa login: akhiri sesi lama dan buat sesi baru di browser ini.
     * Hanya bisa dipanggil setelah doLogin mengembalikan already_active.
     */
    public function doForceLogin()
    {
        $userId = session()->get('force_login_user_id');

        if (! $userId) {
            return redirect()->to(base_url('auth/login'))
                ->with('error', 'Sesi tidak valid. Silakan login kembali.');
        }

        // Hapus flag sementara
        session()->remove('force_login_user_id');

        $result = $this->authService->forceLogin((int) $userId);

        if (! $result['success']) {
            return redirect()->to(base_url('auth/login'))
                ->with('error', $result['message']);
        }

        $redirectUrl = session()->get('redirect_url');
        session()->remove('redirect_url');

        if ($redirectUrl && strpos($redirectUrl, base_url()) === 0) {
            return redirect()->to($redirectUrl)->with('success', $result['message']);
        }

        return $this->redirectByRole()->with('success', 'Selamat datang, ' . session()->get('user_name') . '!');
    }

    // =========================================================
    // REGISTER
    // =========================================================

    public function register()
    {
        if (session()->get('logged_in')) {
            return $this->redirectByRole();
        }

        return view('App\Modules\Auth\Views\register', [
            'title' => 'Daftar Akun — SPMB',
        ]);
    }

    public function doRegister()
    {
        $rules = AuthValidation::registerRules();

        if (! $this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $result = $this->authService->register([
            'name'     => $this->request->getPost('name'),
            'email'    => $this->request->getPost('email'),
            'password' => $this->request->getPost('password'),
        ]);

        if (! $result['success']) {
            return redirect()->back()
                ->withInput()
                ->with('error', $result['message']);
        }

        session()->set('otp_user_id', $result['user_id']);
        session()->set('otp_email',   $result['email']);

        return redirect()->to(base_url('auth/verify-otp'))
            ->with('success', $result['message']);
    }

    // =========================================================
    // VERIFIKASI OTP
    // =========================================================

    public function verifyOtp()
    {
        if (session()->get('logged_in')) {
            return $this->redirectByRole();
        }

        $userId = session()->get('otp_user_id');
        if (! $userId) {
            return redirect()->to(base_url('auth/login'))
                ->with('error', 'Sesi tidak valid. Silakan login kembali.');
        }

        return view('App\Modules\Auth\Views\verify_otp', [
            'title' => 'Verifikasi Email — SPMB',
            'email' => session()->get('otp_email'),
        ]);
    }

    public function doVerifyOtp()
    {
        $userId = session()->get('otp_user_id');

        if (! $userId) {
            return redirect()->to(base_url('auth/login'))
                ->with('error', 'Sesi tidak valid. Silakan login kembali.');
        }

        if (! $this->validate(['otp' => 'required|min_length[6]|max_length[6]|numeric'])) {
            return redirect()->back()->with('error', 'Kode OTP harus berupa 6 angka.');
        }

        $otp    = $this->request->getPost('otp');
        $result = $this->authService->verifyEmailOtp((int) $userId, $otp);

        if (! $result['success']) {
            return redirect()->back()->with('error', $result['message']);
        }

        session()->remove('otp_user_id');
        session()->remove('otp_email');

        return $this->redirectByRole()->with('success', $result['message']);
    }

    public function resendOtp()
    {
        $userId = session()->get('otp_user_id');

        if (! $userId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Sesi tidak valid. Silakan ulangi pendaftaran.',
            ]);
        }

        $result = $this->authService->resendOtp((int) $userId);

        return $this->response->setJSON($result);
    }

    // =========================================================
    // LOGOUT
    // =========================================================

    public function logout()
    {
        $this->authService->logout();
        return redirect()->to(base_url('auth/login'))
            ->with('success', 'Anda telah berhasil logout.');
    }

    // =========================================================
    // FORGOT / RESET PASSWORD
    // =========================================================

    public function forgotPassword()
    {
        if (session()->get('logged_in')) {
            return $this->redirectByRole();
        }

        return view('App\Modules\Auth\Views\forgot_password', ['title' => 'Lupa Password']);
    }

    public function doForgotPassword()
    {
        if (! $this->validate(['email' => 'required|valid_email'])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $result = $this->authService->forgotPassword($this->request->getPost('email'));

        return redirect()->back()->with('success', $result['message']);
    }

    public function resetPassword(string $token)
    {
        $reset = db_connect()->table('password_resets')
            ->where('token', $token)
            ->where('expires_at >', date('Y-m-d H:i:s'))
            ->get()->getRow();

        if (! $reset) {
            return redirect()->to(base_url('auth/login'))
                ->with('error', 'Link reset tidak valid atau sudah kedaluwarsa.');
        }

        return view('App\Modules\Auth\Views\reset_password', [
            'title' => 'Reset Password',
            'token' => $token,
        ]);
    }

    public function doResetPassword()
    {
        $rules = [
            'token'            => 'required',
            'password'         => 'required|min_length[8]|regex_match[/^(?=.*[A-Z])(?=.*[0-9]).+$/]',
            'password_confirm' => 'required|matches[password]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $result = $this->authService->resetPassword(
            $this->request->getPost('token'),
            $this->request->getPost('password')
        );

        if (! $result['success']) {
            return redirect()->back()->with('error', $result['message']);
        }

        return redirect()->to(base_url('auth/login'))->with('success', $result['message']);
    }

    // =========================================================
    // HELPERS
    // =========================================================

    private function redirectByRole(): \CodeIgniter\HTTP\RedirectResponse
    {
        $role = session()->get('user_role');
        return match ($role) {
            'admin_tu'       => redirect()->to(base_url('admin')),
            'kepala_sekolah' => redirect()->to(base_url('kepala-sekolah')),
            default          => redirect()->to(base_url('dashboard')),
        };
    }
}
