<?php

namespace App\Modules\Auth\Validation;

class AuthValidation
{
    public static function loginRules(): array
    {
        return [
            'email' => [
                'rules'  => 'required|valid_email|max_length[255]',
                'errors' => [
                    'required'    => 'Email wajib diisi.',
                    'valid_email' => 'Format email tidak valid.',
                ],
            ],
            'password' => [
                'rules'  => 'required|min_length[6]',
                'errors' => [
                    'required'   => 'Password wajib diisi.',
                    'min_length' => 'Password minimal 6 karakter.',
                ],
            ],
        ];
    }

    public static function registerRules(): array
    {
        return [
            'name' => [
                'rules'  => 'required|min_length[3]|max_length[255]|regex_match[/^[a-zA-Z\s.\']+$/]',
                'errors' => [
                    'required'    => 'Nama lengkap wajib diisi.',
                    'min_length'  => 'Nama minimal 3 karakter.',
                    'regex_match' => 'Nama hanya boleh berisi huruf, spasi, titik, dan apostrof.',
                ],
            ],
            'email' => [
                'rules'  => 'required|valid_email|max_length[255]|is_unique[users.email]',
                'errors' => [
                    'required'    => 'Email wajib diisi.',
                    'valid_email' => 'Format email tidak valid.',
                    'is_unique'   => 'Email sudah terdaftar. Gunakan email lain atau login.',
                ],
            ],
            'password' => [
                'rules'  => 'required|min_length[8]|max_length[72]|regex_match[/^(?=.*[A-Z])(?=.*[0-9]).+$/]',
                'errors' => [
                    'required'    => 'Password wajib diisi.',
                    'min_length'  => 'Password minimal 8 karakter.',
                    'regex_match' => 'Password harus mengandung minimal 1 huruf besar dan 1 angka.',
                ],
            ],
            'password_confirm' => [
                'rules'  => 'required|matches[password]',
                'errors' => [
                    'required' => 'Konfirmasi password wajib diisi.',
                    'matches'  => 'Konfirmasi password tidak sesuai.',
                ],
            ],
            'agree' => [
                'rules'  => 'required',
                'errors' => [
                    'required' => 'Anda harus menyetujui syarat dan ketentuan.',
                ],
            ],
        ];
    }
}
