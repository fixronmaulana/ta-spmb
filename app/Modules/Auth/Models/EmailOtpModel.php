<?php

namespace App\Modules\Auth\Models;

use CodeIgniter\Model;

class EmailOtpModel extends Model
{
    protected $table            = 'email_otps';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $protectFields    = true;

    protected $allowedFields = [
        'user_id',
        'otp_code',
        'expires_at',
        'is_used',
    ];

    protected $useTimestamps = false;

    /**
     * Buat OTP baru untuk user, hapus OTP lama terlebih dahulu
     */
    public function createOtp(int $userId): string
    {
        // Hapus OTP lama milik user ini
        $this->where('user_id', $userId)->delete();

        // Generate 6 digit OTP
        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $this->insert([
            'user_id'    => $userId,
            'otp_code'   => $otp,
            'expires_at' => date('Y-m-d H:i:s', strtotime('+10 minutes')),
            'is_used'    => 0,
        ]);

        return $otp;
    }

    /**
     * Verifikasi OTP: cek kode, belum expired, belum dipakai
     */
    public function verifyOtp(int $userId, string $code): bool
    {
        $record = $this
            ->where('user_id', $userId)
            ->where('otp_code', $code)
            ->where('is_used', 0)
            ->where('expires_at >', date('Y-m-d H:i:s'))
            ->first();

        if (! $record) {
            return false;
        }

        // Tandai sudah dipakai
        $this->update($record->id, ['is_used' => 1]);

        return true;
    }

    /**
     * Cek apakah user boleh minta OTP baru (cooldown 60 detik, max 3x per jam)
     */
    public function canResend(int $userId): array
    {
        $oneHourAgo  = date('Y-m-d H:i:s', strtotime('-1 hour'));
        $oneMinAgo   = date('Y-m-d H:i:s', strtotime('-60 seconds'));

        // Hitung kirim dalam 1 jam terakhir
        $countLastHour = $this->where('user_id', $userId)
            ->where('created_at >', $oneHourAgo)
            ->countAllResults();

        if ($countLastHour >= 3) {
            return ['allowed' => false, 'message' => 'Anda sudah meminta OTP terlalu banyak. Coba lagi 1 jam kemudian.'];
        }

        // Cek cooldown 60 detik dari OTP terakhir
        $lastOtp = $this->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->first();

        if ($lastOtp && strtotime($lastOtp->created_at) > strtotime($oneMinAgo)) {
            $sisaDetik = 60 - (time() - strtotime($lastOtp->created_at));
            return ['allowed' => false, 'message' => "Tunggu {$sisaDetik} detik sebelum meminta OTP baru."];
        }

        return ['allowed' => true, 'message' => ''];
    }
}