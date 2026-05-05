<?php

namespace App\Modules\BukuInduk\Libraries;

/**
 * NIS Generator
 *
 * Format NIS: YYYYKKNNN
 *   YYYY = 4 digit tahun masuk
 *   KK   = 2 digit kode NIS jurusan
 *   NNN  = 3 digit nomor urut (001-999)
 *
 * Contoh: 2024010001 = Tahun 2024, Jurusan ke-01, Siswa ke-001
 */
class NISGenerator
{
    protected \CodeIgniter\Database\ConnectionInterface $db;

    public function __construct()
    {
        $this->db = db_connect();
    }

    /**
     * Generate NIS unik untuk jurusan dan tahun tertentu
     * Menggunakan DB lock untuk mencegah race condition
     *
     * @throws \OverflowException jika nomor urut melebihi 999
     */
    public function generate(string $kodeNis, int $tahunMasuk): string
    {
        $this->db->transStart();

        try {
            // Lock tabel buku_induks untuk row ini
            $lastNIS = $this->db->query("
                SELECT nis FROM buku_induks
                WHERE nis LIKE ?
                ORDER BY nis DESC
                LIMIT 1
                FOR UPDATE
            ", [$tahunMasuk . $kodeNis . '%'])->getRow();

            $urut = 1;
            if ($lastNIS) {
                $lastUrut = (int) substr($lastNIS->nis, -3);
                $urut     = $lastUrut + 1;
            }

            if ($urut > 999) {
                throw new \OverflowException(
                    "Nomor urut NIS sudah mencapai batas maksimum (999) untuk jurusan {$kodeNis} tahun {$tahunMasuk}."
                );
            }

            $nis = $tahunMasuk . $kodeNis . str_pad($urut, 3, '0', STR_PAD_LEFT);

            // Pastikan benar-benar unik (double check)
            $existing = $this->db->table('buku_induks')->where('nis', $nis)->countAllResults();
            if ($existing > 0) {
                throw new \RuntimeException("NIS {$nis} sudah digunakan. Coba lagi.");
            }

            $this->db->transComplete();

            return $nis;
        } catch (\Exception $e) {
            $this->db->transRollback();
            throw $e;
        }
    }

    /**
     * Preview NIS berikutnya tanpa commit
     */
    public function preview(string $kodeNis, int $tahunMasuk): string
    {
        $lastNIS = $this->db->table('buku_induks')
            ->like('nis', $tahunMasuk . $kodeNis, 'after')
            ->orderBy('nis', 'DESC')
            ->limit(1)
            ->get()->getRow();

        $urut = 1;
        if ($lastNIS) {
            $urut = (int) substr($lastNIS->nis, -3) + 1;
        }

        return $tahunMasuk . $kodeNis . str_pad($urut, 3, '0', STR_PAD_LEFT);
    }
}
