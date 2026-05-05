<?php

namespace App\Modules\Notifikasi\Services;

use App\Modules\Notifikasi\Models\NotifikasiModel;

class NotifikasiService
{
    protected NotifikasiModel $model;

    public function __construct()
    {
        $this->model = new NotifikasiModel();
    }

    /**
     * Kirim notifikasi ke user tertentu
     */
    public function send(int $userId, string $type, string $title, string $message, array $data = []): int
    {
        return $this->model->insert([
            'user_id'    => $userId,
            'type'       => $type,
            'title'      => $title,
            'message'    => $message,
            'data'       => json_encode($data),
            'action_url' => $data['url'] ?? null,
            'is_read'    => 0,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Kirim ke semua admin TU
     *
     * FIX: Kolom di tabel users adalah 'role_id' (FK ke tabel roles),
     *      bukan 'role'. Query harus JOIN ke tabel roles untuk filter by nama_role.
     */
    public function notifikasiKeAdmin(string $type, string $title, string $message, array $data = []): void
    {
        $db     = db_connect();
        $admins = $db->table('users u')
            ->select('u.id')
            ->join('roles r', 'r.id = u.role_id')
            ->where('r.nama_role', 'admin_tu')
            ->where('u.is_active', 1)
            ->get()
            ->getResultObject();

        foreach ($admins as $admin) {
            $this->send((int) $admin->id, $type, $title, $message, $data);
        }
    }

    /**
     * Kirim ke kepala sekolah
     *
     * FIX: Sama seperti notifikasiKeAdmin — gunakan JOIN ke roles
     */
    public function notifikasiKeKepsek(string $type, string $title, string $message, array $data = []): void
    {
        $db      = db_connect();
        $kepseks = $db->table('users u')
            ->select('u.id')
            ->join('roles r', 'r.id = u.role_id')
            ->where('r.nama_role', 'kepala_sekolah')
            ->where('u.is_active', 1)
            ->get()
            ->getResultObject();

        foreach ($kepseks as $kepsek) {
            $this->send((int) $kepsek->id, $type, $title, $message, $data);
        }
    }

    public function countUnread(int $userId): int
    {
        return $this->model->countUnread($userId);
    }

    public function getAll(int $userId): array
    {
        return $this->model->getAll($userId);
    }

    public function getUnread(int $userId, int $limit = 10): array
    {
        return $this->model->getUnread($userId, $limit);
    }

    public function markAllRead(int $userId): void
    {
        $this->model->markAllRead($userId);
    }

    public function markRead(int $id, int $userId): void
    {
        $this->model->markRead($id, $userId);
    }
}