<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Exceptions\PageNotFoundException;
use Psr\Log\LoggerInterface;

abstract class BaseController extends Controller
{
    protected $session;

    public function initController(
        RequestInterface $request,
        ResponseInterface $response,
        LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);

        // Explicit property (PHP 8.2 safe)
        $this->session = service('session');
    }

    /**
     * Render view dengan master layout
     */
    protected function render(
        string $view,
        array $data = [],
        string $layout = 'App\Views\Layouts\app'
    ): string {
        $data['currentUser'] = [
            'id'    => $this->session->get('user_id'),
            'name'  => $this->session->get('user_name'),
            'email' => $this->session->get('user_email'),
            'role'  => $this->session->get('user_role'),
        ];

        $data['pageTitle'] = $data['title'] ?? 'SPMB';

        $data['content'] = view($view, $data);

        return view($layout, $data);
    }

    /**
     * JSON Success
     */
    protected function jsonSuccess(
        string $message = 'OK',
        array $data = [],
        int $code = 200
    ): ResponseInterface {
        return $this->response
            ->setStatusCode($code)
            ->setJSON([
                'success' => true,
                'message' => $message,
                'data'    => $data,
            ]);
    }

    /**
     * JSON Error
     */
    protected function jsonError(
        string $message = 'Error',
        array $errors = [],
        int $code = 422
    ): ResponseInterface {
        return $this->response
            ->setStatusCode($code)
            ->setJSON([
                'success' => false,
                'message' => $message,
                'errors'  => $errors,
            ]);
    }

    /**
     * Current user ID
     */
    protected function userId(): int
    {
        return (int) $this->session->get('user_id');
    }

    protected function userRole(): string
    {
        return $this->session->get('user_role') ?? '';
    }

    /**
     * Authorize role
     */
    protected function authorize(string ...$roles): void
    {
        if (! in_array($this->userRole(), $roles, true)) {
            throw new PageNotFoundException('Akses ditolak.');
        }
    }
}