<?php

namespace App\Modules\Notifikasi\Models;

use CodeIgniter\Model;

class NotifikasiModel extends Model
{
    protected $table         = 'notifikasis';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'user_id','type','title','message','data','icon','color','action_url','is_read','read_at','created_at',
    ];

    public function getUnread(int $userId, int $limit = 10): array
    {
        return $this->where('user_id', $userId)
            ->where('is_read', 0)
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    public function getAll(int $userId, int $limit = 50): array
    {
        return $this->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    public function countUnread(int $userId): int
    {
        return $this->where('user_id', $userId)
            ->where('is_read', 0)
            ->countAllResults();
    }

    public function markAllRead(int $userId): void
    {
        $this->set(['is_read' => 1, 'read_at' => date('Y-m-d H:i:s')])
            ->where('user_id', $userId)
            ->where('is_read', 0)
            ->update();
    }

    public function markRead(int $id, int $userId): void
    {
        $this->set(['is_read' => 1, 'read_at' => date('Y-m-d H:i:s')])
            ->where('id', $id)
            ->where('user_id', $userId)
            ->update();
    }
    public function getLatest($userId, $limit = 5)
{
    return $this->where('user_id', $userId)
                ->orderBy('created_at', 'DESC')
                ->limit($limit)
                ->findAll();
}
}
