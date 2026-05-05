<?php

namespace App\Modules\Notifikasi\Controllers;

use App\Controllers\BaseController;
use App\Modules\Notifikasi\Services\NotifikasiService;

class NotifikasiController extends BaseController
{
    protected NotifikasiService $service;

    public function __construct()
    {
        $this->service = new NotifikasiService();
    }

    // Halaman daftar notifikasi
    public function index()
    {
        $userId      = $this->userId();
        $notifikasis = $this->service->getAll($userId);

        // CATATAN: Auto-markAllRead dihapus agar fitur filter
        // "Belum Dibaca / Sudah Dibaca" di halaman ini tetap berfungsi.
        // Notifikasi ditandai dibaca secara individual (onClick) atau
        // melalui tombol "Tandai Semua Dibaca".

        return $this->render('App\Modules\Notifikasi\Views\index', [
            'title'       => 'Notifikasi',
            'notifikasis' => $notifikasis,
        ]);
    }

    // API: count unread (polling untuk badge topbar)
    public function count()
    {
        return $this->jsonSuccess('ok', [
            'count' => $this->service->countUnread($this->userId()),
        ]);
    }

    // API: list unread (dropdown topbar)
    public function list()
    {
        $notifikasis = $this->service->getUnread($this->userId(), 8);

        $items = array_map(fn($n) => [
            'id'         => $n->id,
            'title'      => $n->title,
            'message'    => $n->message,
            'action_url' => $n->action_url,
            'is_read'    => (bool)$n->is_read,
            'time'       => date('d/m H:i', strtotime($n->created_at)),
        ], $notifikasis);

        return $this->jsonSuccess('ok', [
            'items' => $items,
            'count' => $this->service->countUnread($this->userId()),
        ]);
    }

    // Mark satu sebagai dibaca (dipanggil via JS fetch saat notif di-expand)
    public function markRead(int $id)
    {
        $this->service->markRead($id, $this->userId());

        if ($this->request->isAJAX()) {
            return $this->jsonSuccess('Notifikasi ditandai sudah dibaca.');
        }

        return redirect()->back();
    }

    // Mark semua sebagai dibaca (dipanggil via tombol "Tandai Semua Dibaca")
    public function markAllRead()
    {
        $this->service->markAllRead($this->userId());
        return $this->jsonSuccess('Semua notifikasi sudah dibaca.');
    }
}