<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Modules\Auth\Models\UserModel;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Cek apakah user sudah login
        if (! session()->get('user_id')) {
            session()->set('redirect_url', current_url());

            if ($request->isAJAX()) {
                return service('response')
                    ->setStatusCode(401)
                    ->setJSON(['success' => false, 'message' => 'Session expired. Please login again.', 'redirect' => base_url('auth/login')]);
            }

            return redirect()->to(base_url('auth/login'))
                ->with('error', 'Silakan login terlebih dahulu.');
        }

        // Cek apakah akun aktif
        if (! session()->get('user_active')) {
            session()->destroy();
            return redirect()->to(base_url('auth/login'))
                ->with('error', 'Akun Anda tidak aktif. Hubungi administrator.');
        }

        // ---------------------------------------------------------------
        // SINGLE SESSION VALIDATION
        // Bandingkan session_token di session PHP dengan yang ada di DB.
        // Jika berbeda, berarti ada login baru di browser/perangkat lain
        // yang telah menimpa token ini — sesi ini harus diakhiri.
        // ---------------------------------------------------------------
        $userId       = (int) session()->get('user_id');
        $sessionToken = session()->get('session_token');

        if ($sessionToken) {
            $userModel = new UserModel();
            $isValid   = $userModel->isSessionTokenValid($userId, $sessionToken);

            if (! $isValid) {
                // Hancurkan sesi lokal ini — sesi sudah diambil alih
                session()->destroy();

                if ($request->isAJAX()) {
                    return service('response')
                        ->setStatusCode(401)
                        ->setJSON([
                            'success'  => false,
                            'message'  => 'Sesi Anda telah berakhir karena akun ini login di perangkat lain.',
                            'redirect' => base_url('auth/login'),
                        ]);
                }

                return redirect()->to(base_url('auth/login'))
                    ->with('error', 'Sesi Anda telah berakhir karena akun ini digunakan untuk login di perangkat/browser lain.');
            }
        }
        // ---------------------------------------------------------------

        // Role check — filter dipanggil sebagai 'auth:calon_siswa' atau 'auth:admin_tu,kepala_sekolah'
        if ($arguments && ! empty($arguments)) {
            $userRole     = session()->get('user_role');
            $allowedRoles = $arguments;

            if (! in_array($userRole, $allowedRoles)) {
                if ($request->isAJAX()) {
                    return service('response')
                        ->setStatusCode(403)
                        ->setJSON(['success' => false, 'message' => 'Akses ditolak. Anda tidak memiliki izin.']);
                }

                return redirect()->to($this->getHomeByRole($userRole))
                    ->with('error', 'Anda tidak memiliki akses ke halaman tersebut.');
            }
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Nothing needed here
    }

    private function getHomeByRole(string $role): string
    {
        return match ($role) {
            'admin_tu'       => base_url('admin'),
            'kepala_sekolah' => base_url('kepala-sekolah'),
            'calon_siswa'    => base_url('dashboard'),
            default          => base_url('auth/login'),
        };
    }
}
