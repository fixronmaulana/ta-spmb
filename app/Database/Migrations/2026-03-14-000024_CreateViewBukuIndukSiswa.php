<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Membuat VIEW v_buku_induk_siswa — menggabungkan seluruh data
 * siswa dari semua tabel pendukung dalam satu query siap pakai
 * untuk cetak buku induk dan laporan.
 */
class CreateViewBukuIndukSiswa extends Migration
{
    public function up(): void
    {
        $this->db->query('DROP VIEW IF EXISTS v_buku_induk_siswa');

        $this->db->query("
            CREATE VIEW v_buku_induk_siswa AS
            SELECT
                cs.id                   AS siswa_id,
                cs.no_pendaftaran,
                cs.nama_lengkap,
                cs.nama_panggilan,
                cs.nik,
                cs.nisn,
                cs.jenis_kelamin,
                cs.tempat_lahir,
                cs.tanggal_lahir,
                cs.agama,
                cs.kewarganegaraan,
                cs.status_dalam_keluarga,
                cs.jumlah_saudara,
                cs.anak_ke,
                cs.bahasa_sehari_hari,
                cs.email,
                cs.no_telp,
                cs.foto_profil,

                als.alamat_lengkap,
                als.dusun,
                als.rt,
                als.rw,
                wkel.nama               AS kelurahan,
                wkec.nama               AS kecamatan,
                wkab.nama               AS kabupaten,
                wprov.nama              AS provinsi,
                als.kode_pos,

                kes.golongan_darah,
                kes.tinggi_badan,
                kes.berat_badan,

                mb.kesenian,
                mb.olahraga,

                ask.nama_sekolah_asal,
                ask.npsn,

                ot.nama_ayah,
                ot.nama_ibu,

                -- 🔥 FIXED AREA
                p.periode_id,
                p.no_pendaftaran        AS no_daftar,
                p.status                AS status_pendaftaran,
                p.submitted_at,

                j.nama AS nama_jurusan

            FROM calon_siswa cs
            LEFT JOIN alamat_siswa      als   ON cs.id = als.calon_siswa_id
            LEFT JOIN wilayah_kelurahan wkel  ON als.kelurahan_id = wkel.id
            LEFT JOIN wilayah_kecamatan wkec  ON als.kecamatan_id = wkec.id
            LEFT JOIN wilayah_kabupaten wkab  ON als.kabupaten_id = wkab.id
            LEFT JOIN wilayah_provinsi  wprov ON als.provinsi_id  = wprov.id
            LEFT JOIN kesehatan_siswa   kes   ON cs.id = kes.calon_siswa_id
            LEFT JOIN minat_bakat_siswa mb    ON cs.id = mb.calon_siswa_id
            LEFT JOIN asal_sekolah      ask   ON cs.id = ask.calon_siswa_id
            LEFT JOIN orang_tua         ot    ON cs.id = ot.calon_siswa_id

            -- 🔥 FIX RELASI PENDAFTARAN
            LEFT JOIN pendaftaran p ON cs.user_id = p.user_id

            -- 🔥 FIX JURUSAN (pakai yang diterima)
            LEFT JOIN jurusan j ON p.jurusan_diterima_id = j.id

            ORDER BY cs.nama_lengkap;
        ");
    }

    public function down(): void
    {
        $this->db->query('DROP VIEW IF EXISTS v_buku_induk_siswa');
    }
}
