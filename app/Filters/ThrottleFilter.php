<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Cache\CacheInterface;

class ThrottleFilter implements FilterInterface
{
    protected int $maxAttempts = 5;
    protected int $decayMinutes = 15;

    public function before(RequestInterface $request, $arguments = null)
    {
        if (! $request->is('post')) {
            return;
        }

        $ip  = $request->getIPAddress();
        $uri = $request->getPath();
        $key = 'throttle_' . md5($ip . $uri);

        $cache    = \Config\Services::cache();
        $attempts = $cache->get($key) ?? 0;

        if ($attempts >= $this->maxAttempts) {
            if ($request->isAJAX()) {
                return service('response')
                    ->setStatusCode(429)
                    ->setJSON([
                        'success' => false,
                        'message' => 'Terlalu banyak percobaan. Coba lagi dalam ' . $this->decayMinutes . ' menit.',
                    ]);
            }

            return redirect()->back()
                ->with('error', 'Terlalu banyak percobaan. Coba lagi dalam ' . $this->decayMinutes . ' menit.');
        }

        // Increment attempts
        $cache->save($key, $attempts + 1, $this->decayMinutes * 60);
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
