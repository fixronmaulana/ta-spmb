<?php

namespace App\Modules\MasterData\Models;

use CodeIgniter\Model;

class PeriodeModel extends Model
{
    protected $table         = 'periode';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $allowedFields = [
        'nama',
        'tahun_ajaran',
        'tanggal_mulai',
        'tanggal_selesai',
        'tanggal_pengumuman',
        'tanggal_daftar_ulang_mulai',
        'tanggal_daftar_ulang_selesai',
        'is_active',
        'is_published',
        'deskripsi',
        'wa_grup_link',   // link join grup WA pendaftar
        'wa_cp_no',       // nomor WA contact person panitia
    ];
    protected $useTimestamps = true;

    public function getPeriodeAktif(): ?object
    {
        return $this->where('is_active', 1)->first();
    }

    /**
     * Aktifkan periode ini, nonaktifkan yang lain.
     */
    public function setAktif(int $id): bool
    {
        $db = db_connect();
        $db->table('periode')->update(['is_active' => 0]);
        return $this->update($id, ['is_active' => 1]);
    }

    /**
     * Publish pengumuman kelulusan.
     */
    public function publish(int $id): bool
    {
        return $this->update($id, ['is_published' => 1]);
    }

    public function isPengumumanPublished(): bool
    {
        $p = $this->getPeriodeAktif();
        return $p && (bool) $p->is_published;
    }

    // =========================================================
    // HELPER WA — ambil dari periode aktif
    // =========================================================

    /**
     * Link grup WA dari periode aktif.
     * Fallback ke env WA_GRUP_LINK jika kolom belum diisi.
     */
    public function getWaGrupLink(): string
    {
        $p = $this->getPeriodeAktif();
        if ($p && ! empty($p->wa_grup_link)) {
            return $p->wa_grup_link;
        }
        return env('WA_GRUP_LINK', '#');
    }

    /**
     * Nomor WA CP panitia (format display: 0812-xxxx-xxxx)
     * dari periode aktif. Fallback ke env WA_KONTAK_NO.
     */
    public function getWaCpNo(): string
    {
        $p = $this->getPeriodeAktif();
        if ($p && ! empty($p->wa_cp_no)) {
            return $p->wa_cp_no;
        }
        return env('WA_KONTAK_NO', '0812-xxxx-xxxx');
    }

    /**
     * Link wa.me dari nomor WA CP.
     * Konversi 08xxx ke 628xxx otomatis.
     */
    public function getWaCpLink(): string
    {
        $no  = $this->getWaCpNo();
        $raw = preg_replace('/[^0-9]/', '', $no);
        if (str_starts_with($raw, '0')) {
            $raw = '62' . substr($raw, 1);
        }
        return "https://wa.me/{$raw}";
    }
}
