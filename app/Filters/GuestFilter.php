<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * GuestFilter — Redirect authenticated users away from guest-only pages
 * (login, register)
 */
class GuestFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (session()->get('user_id')) {

            $role = session()->get('user_role');

            return match ($role) {
                'admin_tu'       => redirect()->to(base_url('admin')),
                'kepala_sekolah' => redirect()->to(base_url('kepala-sekolah')),
                'calon_siswa'    => redirect()->to(base_url('dashboard')),
                default          => redirect()->to(base_url('auth/logout')),
            };
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
