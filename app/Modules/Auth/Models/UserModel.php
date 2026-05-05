<?php

namespace App\Modules\Auth\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false; // Nonaktifkan dulu sampai kolom deleted_at ditambahkan
    protected $protectFields    = true;

    // ============================================================
    // PERBAIKAN: Sesuaikan dengan struktur database sebenarnya
    // ============================================================
    protected $allowedFields = [
        'role_id',           // BUKAN 'role' tapi 'role_id' (foreign key ke tabel roles)
        'username',          // Ada di database
        'nama_lengkap',      // BUKAN 'name' tapi 'nama_lengkap'
        'email',
        'password',
        'no_telp',           // BUKAN 'phone' tapi 'no_telp'
        'is_active',
        'email_verified_at',
        'remember_token',
        'last_login_at',
        'last_login_ip',     // Ini mungkin tidak ada di database, perlu ditambahkan
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    protected $validationRules    = [];
    protected $validationMessages = [];
    protected $skipValidation     = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $beforeInsert = ['hashPassword'];
    protected $beforeUpdate = ['hashPassword'];

    /**
     * Hash password sebelum disimpan
     */
    protected function hashPassword(array $data): array
    {
        if (isset($data['data']['password']) && ! empty($data['data']['password'])) {
            if (! $this->isHashed($data['data']['password'])) {
                $data['data']['password'] = password_hash($data['data']['password'], PASSWORD_BCRYPT, ['cost' => 12]);
            }
        }
        return $data;
    }

    /**
     * Cek apakah password sudah di-hash (bcrypt dimulai dengan $2y$)
     */
    private function isHashed(string $password): bool
    {
        return strlen($password) === 60 && strpos($password, '$2y$') === 0;
    }

    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?object
    {
        return $this->where('email', $email)->first();
    }

    /**
     * Find by remember token
     */
    public function findByRememberToken(string $token): ?object
    {
        return $this->where('remember_token', $token)->where('is_active', 1)->first();
    }

    /**
     * Update last login info
     */
    public function updateLastLogin(int $id, string $ip): bool
    {
        return $this->update($id, [
            'last_login_at' => date('Y-m-d H:i:s'),
            // 'last_login_ip' => $ip, // Commented karena kolom mungkin belum ada
        ]);
    }

    /**
     * Set remember me token
     */
    public function setRememberToken(int $id, string $token): bool
    {
        return $this->update($id, ['remember_token' => $token]);
    }

    /**
     * Clear remember token
     */
    public function clearRememberToken(int $id): bool
    {
        return $this->update($id, ['remember_token' => null]);
    }
}
