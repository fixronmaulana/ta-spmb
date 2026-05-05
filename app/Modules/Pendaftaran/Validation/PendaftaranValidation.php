<?php

namespace App\Modules\Pendaftaran\Validation;

class PendaftaranValidation
{
    public static function getRulesForStep(int $step): array
    {
        return match ($step) {
            1 => self::step1Rules(),
            2 => self::step2Rules(),
            3 => self::step3Rules(),
            4 => [],
            default => [],
        };
    }

    public static function step1Rules(): array
    {
        return [
            'nama_lengkap' => [
                'rules'  => 'required|min_length[3]|max_length[255]',
                'errors' => ['required' => 'Nama lengkap wajib diisi.'],
            ],
            'jenis_kelamin' => [
                'rules'  => 'required|in_list[L,P]',
                'errors' => ['required' => 'Jenis kelamin wajib dipilih.'],
            ],
            'tempat_lahir' => [
                'rules'  => 'required|max_length[100]',
                'errors' => ['required' => 'Tempat lahir wajib diisi.'],
            ],
            'tanggal_lahir' => [
                'rules'  => 'required|valid_date[Y-m-d]',
                'errors' => ['required' => 'Tanggal lahir wajib diisi.'],
            ],
            'agama' => [
                'rules'  => 'required|max_length[30]',
                'errors' => ['required' => 'Agama wajib diisi.'],
            ],
            'alamat' => [
                'rules'  => 'required|min_length[10]',
                'errors' => ['required' => 'Alamat wajib diisi.'],
            ],
            'no_hp' => [
                'rules'  => 'required|min_length[10]|max_length[15]|regex_match[/^0[0-9]{9,14}$/]',
                'errors' => [
                    'required'    => 'Nomor HP / WA wajib diisi.',
                    'min_length'  => 'Nomor HP terlalu pendek. Minimal 10 digit.',
                    'max_length'  => 'Nomor HP terlalu panjang. Maksimal 15 digit.',
                    'regex_match' => 'Format nomor HP tidak valid. Contoh: 08123456789',
                ],
            ],
            'wa_verified' => [
                'rules'  => 'permit_empty|in_list[0,1]',
                'errors' => [],
            ],
        ];
    }

    public static function step2Rules(): array
    {
        return [
            'jurusan_pilihan1_id' => [
                'rules'  => 'required|integer|is_not_unique[jurusan.id]',
                'errors' => ['required' => 'Pilihan jurusan pertama wajib dipilih.'],
            ],
        ];
    }

    public static function step3Rules(): array
    {
        // Regex format nomor HP Indonesia:
        // - Diawali 08
        // - Diikuti 8–13 digit (total panjang 10–15 karakter)
        // - Contoh valid: 081234567890, 082345678901
        $phoneRegex = '/^08[0-9]{8,13}$/';

        $phoneErrors = [
            'required'    => 'Nomor HP wajib diisi.',
            'min_length'  => 'Nomor HP terlalu pendek. Minimal 10 digit (contoh: 081234567890).',
            'max_length'  => 'Nomor HP terlalu panjang. Maksimal 15 digit.',
            'regex_match' => 'Format nomor HP tidak valid. Harus diawali 08 dan terdiri dari 10–15 digit. Contoh: 081234567890.',
        ];

        return [
            'nama_ayah' => [
                'rules'  => 'required|max_length[255]',
                'errors' => ['required' => 'Nama ayah wajib diisi.'],
            ],
            'nama_ibu' => [
                'rules'  => 'required|max_length[255]',
                'errors' => ['required' => 'Nama ibu wajib diisi.'],
            ],

            // ── No. HP Ayah (wajib) ────────────────────────────────────
            'no_hp_ortu' => [
                'rules'  => "required|min_length[10]|max_length[15]|regex_match[{$phoneRegex}]",
                'errors' => array_merge($phoneErrors, [
                    'required' => 'Nomor HP Ayah wajib diisi.',
                ]),
            ],

            // ── No. HP Ibu (opsional, tapi jika diisi harus format yang benar) ──
            'no_hp_ibu' => [
                'rules'  => "permit_empty|min_length[10]|max_length[15]|regex_match[{$phoneRegex}]",
                'errors' => array_merge($phoneErrors, [
                    'min_length'  => 'Nomor HP Ibu terlalu pendek. Minimal 10 digit (contoh: 081234567890).',
                    'regex_match' => 'Format nomor HP Ibu tidak valid. Harus diawali 08 dan terdiri dari 10–15 digit. Contoh: 081234567890.',
                ]),
            ],

            // ── No. HP Wali (opsional, tapi jika diisi harus format yang benar) ──
            'no_hp_wali' => [
                'rules'  => "permit_empty|min_length[10]|max_length[15]|regex_match[{$phoneRegex}]",
                'errors' => array_merge($phoneErrors, [
                    'min_length'  => 'Nomor HP Wali terlalu pendek. Minimal 10 digit (contoh: 081234567890).',
                    'regex_match' => 'Format nomor HP Wali tidak valid. Harus diawali 08 dan terdiri dari 10–15 digit. Contoh: 081234567890.',
                ]),
            ],
        ];
    }
}
