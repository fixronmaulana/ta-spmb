<?php

namespace App\Validation;

/**
 * Custom Validation Rules
 * FILE: app/Validation/CustomRules.php
 */
class CustomRules
{
    /**
     * Validasi format nomor HP Indonesia.
     *
     * FIXED: $params dan $error dijadikan nullable (?string)
     * karena CI4 memanggil rule tanpa params → null dikirim
     */
    public function valid_indonesian_phone(
        ?string $value,
        ?string $params,           // ← FIX: ?string bukan string
        array $data = [],
        ?string &$error = null
    ): bool {
        if (empty($value)) {
            return false;
        }

        $clean = preg_replace('/[\s\-\(\)]/', '', $value);

        if (str_starts_with($clean, '+62')) {
            $clean = '0' . substr($clean, 3);
        } elseif (str_starts_with($clean, '62') && strlen($clean) > 10) {
            $clean = '0' . substr($clean, 2);
        }

        if (! preg_match('/^08[0-9]{8,13}$/', $clean)) {
            return false;
        }

        $validPrefixes = [
            // Telkomsel
            '0811',
            '0812',
            '0813',
            '0821',
            '0822',
            '0823',
            '0851',
            '0852',
            '0853',
            // Indosat Ooredoo
            '0814',
            '0815',
            '0816',
            '0855',
            '0856',
            '0857',
            '0858',
            // XL Axiata
            '0817',
            '0818',
            '0819',
            '0859',
            '0877',
            '0878',
            // Axis
            '0831',
            '0832',
            '0833',
            '0838',
            // Tri (3)
            '0895',
            '0896',
            '0897',
            '0898',
            '0899',
            // Smartfren
            '0881',
            '0882',
            '0883',
            '0884',
            '0885',
            '0886',
            '0887',
            '0888',
            '0889',
        ];

        if (! in_array(substr($clean, 0, 4), $validPrefixes, true)) {
            $error = 'Nomor HP tidak dikenali sebagai nomor operator Indonesia yang valid.';
            return false;
        }

        return true;
    }
}
