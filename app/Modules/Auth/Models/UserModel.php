<?php

namespace App\Modules\Auth\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'role_id',
        'username',
        'nama_lengkap',
        'email',
        'password',
        'no_telp',
        'is_active',
        'email_verified_at',
        'remember_token',
        'session_token',     // Token unik per sesi aktif (single session enforcement)
        'last_login_at',
        'last_login_ip',
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

    // =========================================================
    // SESSION TOKEN — Single Active Session Management
    // =========================================================

    /**
     * Buat dan simpan session token baru untuk user.
     * Token lama otomatis ditimpa sehingga sesi sebelumnya menjadi tidak valid.
     *
     * @return string Token yang baru dibuat
     */
    public function createSessionToken(int $id): string
    {
        $token = bin2hex(random_bytes(32)); // 64 karakter hex
        $this->update($id, ['session_token' => $token]);
        return $token;
    }

    /**
     * Hapus session token (saat logout).
     */
    public function clearSessionToken(int $id): bool
    {
        return $this->update($id, ['session_token' => null]);
    }

    /**
     * Validasi apakah session token di session PHP cocok dengan yang ada di DB.
     *
     * @param int    $userId        ID user dari session
     * @param string $sessionToken  Token dari session PHP
     * @return bool  true = valid, false = sudah diambil alih / tidak valid
     */
    public function isSessionTokenValid(int $userId, string $sessionToken): bool
    {
        $user = $this->select('session_token')->find($userId);

        if (! $user) {
            return false;
        }

        return $user->session_token === $sessionToken;
    }
}
