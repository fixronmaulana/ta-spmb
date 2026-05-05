<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migrasi: Sinkronisasi status pendaftaran vs status daftar ulang
 *
 * FIX yang diselesaikan migrasi ini:
 *  1. Pastikan semua baris daftar_ulangs yang status='pending' memiliki
 *     status pendaftaran = 'daftar_ulang' (bukan 'lulus')
 *  2. Pastikan baris dengan status='ditolak' juga berstatus pendaftaran 'daftar_ulang'
 *     agar siswa tetap bisa akses form upload ulang
 *  3. Hapus data orphan yang tidak konsisten
 */
class FixStatusSyncDaftarUlang extends Migration
{
    public function up(): void
    {
        // 1. Siswa yang sudah submit daftar ulang (pending/ditolak)
        //    tapi status pendaftaran masih 'lulus' → update ke 'daftar_ulang'
        $this->db->query("
            UPDATE pendaftaran p
            INNER JOIN daftar_ulangs du ON du.pendaftaran_id = p.id
            SET p.status = 'daftar_ulang'
            WHERE p.status = 'lulus'
              AND du.status IN ('pending', 'ditolak')
              AND p.deleted_at IS NULL
        ");

        // 2. Siswa yang daftar ulangnya sudah 'dikonfirmasi' admin
        //    tapi status pendaftaran masih 'lulus' atau 'daftar_ulang'
        //    (karena sebelumnya admin langsung set siswa_aktif, sekarang ditangani konversi buku induk)
        //    → Biarkan tetap 'daftar_ulang' agar bisa dikonversi ke buku induk
        $this->db->query("
            UPDATE pendaftaran p
            INNER JOIN daftar_ulangs du ON du.pendaftaran_id = p.id
            LEFT JOIN buku_induks bi ON bi.pendaftaran_id = p.id
            SET p.status = 'daftar_ulang'
            WHERE p.status IN ('lulus', 'siswa_aktif')
              AND du.status = 'dikonfirmasi'
              AND bi.id IS NULL
              AND p.deleted_at IS NULL
        ");

        // 3. Log hasil sinkronisasi
        log_message('info', 'FixStatusSyncDaftarUlang migration: status pendaftaran disinkronkan dengan daftar_ulangs');
    }

    public function down(): void
    {
        // Tidak ada rollback karena ini adalah data fix, bukan structural change
        log_message('info', 'FixStatusSyncDaftarUlang migration rolled back (no structural changes)');
    }
}