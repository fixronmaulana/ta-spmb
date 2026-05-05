<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Cek apakah user sudah login
        if (! session()->get('user_id')) {
            // Simpan URL yang diminta untuk redirect setelah login
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

        // Role check — filter dipanggil sebagai 'auth:calon_siswa' atau 'auth:admin_tu,kepala_sekolah'
        if ($arguments && ! empty($arguments)) {
            $userRole = session()->get('user_role');
            $allowedRoles = $arguments; // CI4 passes as array

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

    /**
     * Redirect berdasarkan role
     */
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
