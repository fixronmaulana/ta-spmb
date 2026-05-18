<?php

namespace App\Modules\Seleksi\Models;

use CodeIgniter\Model;

class SeleksiModel extends Model
{
    protected $table      = 'pendaftaran';
    protected $primaryKey = 'id';
    protected $returnType = 'object';

    public function getForSeleksiByJurusan(): array
    {
        $result = [];
        $rows   = $this->select('
                pendaftaran.id, pendaftaran.status, pendaftaran.user_id,
                pendaftaran.jurusan_pilihan1_id, pendaftaran.jurusan_pilihan2_id,
                pendaftaran.jurusan_diterima_id, pendaftaran.no_pendaftaran,
                dds.nama_lengkap, dds.nisn, dds.asal_sekolah,
                dds.jenis_kelamin, dds.tanggal_lahir,
                j1.nama as jurusan_pilihan1_nama, j1.kode as jurusan_pilihan1_kode, j1.kuota as kuota1,
                j2.nama as jurusan_pilihan2_nama, j2.kode as jurusan_pilihan2_kode,
                jd.nama as jurusan_diterima_nama,
                u.email as email_calon
            ')
            ->join('data_diri_siswas dds', 'dds.pendaftaran_id = pendaftaran.id', 'left')
            ->join('users u',              'u.id = pendaftaran.user_id')
            ->join('jurusan j1',           'j1.id = pendaftaran.jurusan_pilihan1_id', 'left')
            ->join('jurusan j2',           'j2.id = pendaftaran.jurusan_pilihan2_id', 'left')
            ->join('jurusan jd',           'jd.id = pendaftaran.jurusan_diterima_id', 'left')
            ->whereIn('pendaftaran.status', ['seleksi', 'lulus', 'tidak_lulus'])
            ->orderBy('pendaftaran.created_at', 'ASC')
            ->findAll();

        foreach ($rows as $row) {
            $result[] = $row;
        }

        return $result;
    }

    public function tetapkanLulus(array $lulusIds, array $tidakLulusIds, array $jurusanDiterimMap): bool
    {
        $db = db_connect();
        $db->transStart();

        try {
            foreach ($lulusIds as $id) {
                $jurusanDiterima = $jurusanDiterimMap[$id] ?? null;
                $db->table('pendaftaran')->where('id', $id)->update([
                    'status'              => 'lulus',
                    'jurusan_diterima_id' => $jurusanDiterima,
                    'updated_at'          => date('Y-m-d H:i:s'),
                ]);
            }

            foreach ($tidakLulusIds as $id) {
                $db->table('pendaftaran')->where('id', $id)->update([
                    'status'     => 'tidak_lulus',
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            }

            $db->transComplete();
            return $db->transStatus();
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'SeleksiModel::tetapkanLulus - ' . $e->getMessage());
            return false;
        }
    }

    public function getByPendaftaranId(int $id): ?object
    {
        return db_connect()->table('pendaftaran')->where('id', $id)->get()->getRow();
    }
}
