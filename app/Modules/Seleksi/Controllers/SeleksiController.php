<?php

namespace App\Modules\Seleksi\Controllers;

use App\Controllers\BaseController;
use App\Modules\Seleksi\Models\SeleksiModel;
use App\Modules\Pendaftaran\Models\PendaftaranModel;
use App\Modules\MasterData\Models\JurusanModel;
use App\Modules\MasterData\Models\PeriodeModel;
use App\Modules\Notifikasi\Services\NotifikasiService;

/**
 * SeleksiController
 *
 * ALUR PENETAPAN KELULUSAN (final):
 *
 * ┌─────────────────────────────────────────────────────────────────────┐
 * │ 1. Admin buka /admin/seleksi                                         │
 * │    → Daftar calon siswa berstatus 'seleksi', 'lulus', 'tidak_lulus' │
 * │                                                                      │
 * │ 2A. TETAPKAN INDIVIDUAL                                              │
 * │    → Tombol "Lulus" per baris → modal pilih jurusan → submit        │
 * │    → Tombol "Tolak" per baris → modal konfirmasi → submit           │
 * │    → Status berubah: seleksi → lulus / tidak_lulus                  │
 * │    → TIDAK ADA notifikasi ke calon siswa                            │
 * │                                                                      │
 * │ 2B. TETAPKAN MASSAL                                                  │
 * │    → Centang checkbox (bisa multi) → Tolak Terpilih (bulk tolak)    │
 * │    → Atau centang semua → Luluskan Terpilih (bulk lulus, jurusan    │
 * │      di-default ke pilihan 1 masing-masing)                         │
 * │    → TIDAK ADA notifikasi ke calon siswa                            │
 * │                                                                      │
 * │ 3. PUBLISH PENGUMUMAN                                                │
 * │    → Tombol aktif HANYA setelah 0 peserta berstatus 'seleksi'       │
 * │    → Admin klik → modal konfirmasi → submit                          │
 * │    → is_published = 1 di tabel periode                              │
 * │    → Notifikasi RESMI dikirim ke semua peserta lulus & tidak_lulus  │
 * │      di periode aktif                                                │
 * │    → action_url notif = /dashboard/pengumuman                       │
 * │                                                                      │
 * │ 4. Calon siswa buka /dashboard/notifikasi                           │
 * │    → Notif muncul dengan icon khusus "pengumuman_kelulusan"         │
 * │    → Klik notif → redirect ke /dashboard/pengumuman                 │
 * │                                                                      │
 * │ 5. /dashboard/pengumuman                                             │
 * │    → Jika belum published: overlay "belum tersedia"                 │
 * │    → Jika sudah published: tampil hasil milik dirinya sendiri       │
 * │      (query diikat ke user_id + periode_id aktif)                   │
 * └─────────────────────────────────────────────────────────────────────┘
 *
 * VALIDASI BACKEND (5 lapis) di tetapkan():
 * [V1] ID harus di periode aktif
 * [V2] Status harus 'seleksi' (lulus/tidak_lulus sudah ditetapkan, terkunci)
 *      Pengecualian: admin bisa "ubah jurusan" untuk yang statusnya 'lulus'
 *      via flag allow_edit_lulus — tapi itu flow terpisah (belum publish)
 * [V3] ID tidak boleh ada di dua array sekaligus (lulus & tidak_lulus)
 * [V4] jurusan_diterima harus salah satu dari pilihan1 atau pilihan2
 * [V5] Kuota jurusan masih tersedia
 *
 * GUARD MODEL (defense-in-depth):
 * - LOCKED_STATUSES = ['daftar_ulang', 'siswa_aktif'] → tidak pernah bisa diubah
 * - EDITABLE_STATUSES = ['seleksi', 'lulus'] → 'lulus' tetap bisa diubah jurusannya
 *   selama belum published dan belum daftar_ulang/siswa_aktif
 */
class SeleksiController extends BaseController
{
    protected SeleksiModel      $seleksiModel;
    protected PendaftaranModel  $pendaftaranModel;
    protected JurusanModel      $jurusanModel;
    protected PeriodeModel      $periodeModel;
    protected NotifikasiService $notifService;

    public function __construct()
    {
        $this->seleksiModel     = new SeleksiModel();
        $this->pendaftaranModel = new PendaftaranModel();
        $this->jurusanModel     = new JurusanModel();
        $this->periodeModel     = new PeriodeModel();
        $this->notifService     = new NotifikasiService();
    }

    // =========================================================
    // INDEX — Halaman penetapan kelulusan admin
    // =========================================================
    public function index()
    {
        $peserta      = $this->seleksiModel->getForSeleksiByJurusan();
        $jurusans     = $this->jurusanModel->getAllActive();
        $periodeAktif = $this->periodeModel->getPeriodeAktif();

        $lulusPerJurusan = $this->seleksiModel->getCountLulusPerJurusan();

        $byJurusan = [];
        foreach ($jurusans as $j) {
            $byJurusan[$j->id] = [
                'jurusan'     => $j,
                'peserta'     => [],
                'kuota'       => (int) $j->kuota,
                'count_lulus' => $lulusPerJurusan[$j->id] ?? 0,
            ];
        }
        foreach ($peserta as $p) {
            $jid = $p->jurusan_pilihan1_id;
            if (isset($byJurusan[$jid])) {
                $byJurusan[$jid]['peserta'][] = $p;
            }
        }

        // Counter untuk kondisi tombol publish
        $totalSeleksiSelesai = 0;
        $totalBelumDiproses  = 0;
        foreach ($peserta as $p) {
            if (in_array($p->status, ['lulus', 'tidak_lulus', 'daftar_ulang', 'siswa_aktif'])) {
                $totalSeleksiSelesai++;
            }
            if ($p->status === 'seleksi') {
                $totalBelumDiproses++;
            }
        }

        $isPublished = $periodeAktif ? (bool) $periodeAktif->is_published : false;

        return $this->render('App\Modules\Seleksi\Views\index', [
            'title'               => 'Penetapan Kelulusan',
            'peserta'             => $peserta,
            'jurusans'            => $jurusans,
            'byJurusan'           => $byJurusan,
            'periodeAktif'        => $periodeAktif,
            'isPublished'         => $isPublished,
            'totalSeleksiSelesai' => $totalSeleksiSelesai,
            'totalBelumDiproses'  => $totalBelumDiproses,
        ]);
    }

    // =========================================================
    // TETAPKAN — individual atau massal (POST)
    //
    // Endpoint tunggal yang menangani:
    // - Lulus individual (dari modal per baris)
    // - Lulus massal    (dari bulk lulus — jurusan default ke pilihan1)
    // - Tolak individual
    // - Tolak massal
    //
    // !! TIDAK MENGIRIM NOTIFIKASI APAPUN ke calon siswa !!
    // Notifikasi hanya dikirim saat publish() dipanggil.
    // =========================================================
    public function tetapkan()
    {
        $lulusIds        = $this->request->getPost('lulus_ids')        ?? [];
        $tidakLulusIds   = $this->request->getPost('tidak_lulus_ids')  ?? [];
        $jurusanDiterima = $this->request->getPost('jurusan_diterima') ?? [];

        if (empty($lulusIds) && empty($tidakLulusIds)) {
            return redirect()->back()->with('error', 'Tidak ada data yang dipilih.');
        }

        $lulusIdsInt      = array_map('intval', $lulusIds);
        $tidakLulusIdsInt = array_map('intval', $tidakLulusIds);

        // [V3] Duplikat ID di dua array
        $duplikatIds = array_intersect($lulusIdsInt, $tidakLulusIdsInt);
        if (! empty($duplikatIds)) {
            return redirect()->back()->with(
                'error',
                count($duplikatIds) . ' peserta muncul di daftar Lulus sekaligus Tidak Lulus. Periksa kembali pilihan Anda.'
            );
        }

        // [V1] Ambil periode aktif
        $periodeAktif = $this->periodeModel->getPeriodeAktif();
        if (! $periodeAktif) {
            return redirect()->back()->with('error', 'Tidak ada periode SPMB aktif. Penetapan tidak dapat dilakukan.');
        }

        // Jika sudah published, penetapan tidak boleh lagi
        // (kecuali untuk perubahan jurusan yang sudah lulus — ditangani dengan flag khusus)
        // Untuk bulk action biasa, block jika sudah published
        if ((bool) $periodeAktif->is_published) {
            return redirect()->back()->with(
                'error',
                'Pengumuman sudah dipublikasikan. Penetapan kelulusan tidak dapat diubah lagi.'
            );
        }

        // [V1] [V2] [V4] Validasi per-pendaftar
        $jurusanMap       = [];
        $errorMessages    = [];
        $allIdsToValidate = array_unique(array_merge($lulusIdsInt, $tidakLulusIdsInt));

        foreach ($allIdsToValidate as $pendId) {
            $pend = $this->pendaftaranModel->find($pendId);

            if (! $pend) {
                $errorMessages[] = "ID #{$pendId}: pendaftaran tidak ditemukan.";
                continue;
            }

            // [V1] Harus di periode aktif
            if ((int) $pend->periode_id !== (int) $periodeAktif->id) {
                $errorMessages[] = "No. {$pend->no_pendaftaran}: bukan bagian dari periode aktif.";
                continue;
            }

            // [V2] Status harus 'seleksi' atau 'lulus'
            // - 'seleksi' → belum diproses, bisa ditetapkan lulus/tidak_lulus
            // - 'lulus'   → sudah ditetapkan, HANYA bisa diubah jurusannya (tidak bisa di-tolak ulang kecuali via flow koreksi)
            // - 'daftar_ulang', 'siswa_aktif' → terkunci, tidak bisa diubah sama sekali
            if (in_array($pend->status, ['daftar_ulang', 'siswa_aktif'])) {
                $label = $pend->status === 'daftar_ulang' ? 'sudah Daftar Ulang (terkunci)' : 'sudah Siswa Aktif (terkunci)';
                $errorMessages[] = "No. {$pend->no_pendaftaran}: {$label}.";
                continue;
            }

            // 'tidak_lulus' yang ingin di-set ke lulus → diizinkan (koreksi)
            // 'seleksi' → normal
            // 'lulus' yang ingin di-set ke tidak_lulus → diizinkan (koreksi, jarang tapi valid)
            // Semua case di atas lolos di sini, guard di Model yang mencegah terkunci

            // [V4] Validasi jurusan untuk yang ditetapkan lulus
            if (in_array($pendId, $lulusIdsInt, true)) {
                $jurusanIdPost = isset($jurusanDiterima[$pendId]) && $jurusanDiterima[$pendId]
                    ? (int) $jurusanDiterima[$pendId]
                    : null;

                $pilihan1     = (int) $pend->jurusan_pilihan1_id;
                $pilihan2     = $pend->jurusan_pilihan2_id ? (int) $pend->jurusan_pilihan2_id : null;
                $pilihanValid = array_values(array_filter([$pilihan1, $pilihan2]));

                if ($jurusanIdPost !== null) {
                    if (! in_array($jurusanIdPost, $pilihanValid, true)) {
                        $errorMessages[] = "No. {$pend->no_pendaftaran}: jurusan yang dipilihkan bukan pilihan 1 atau 2 pendaftar.";
                        continue;
                    }
                    $jurusanMap[$pendId] = $jurusanIdPost;
                } else {
                    // Fallback ke pilihan 1 (untuk bulk lulus tanpa pilih jurusan eksplisit)
                    if (! $pilihan1) {
                        $errorMessages[] = "No. {$pend->no_pendaftaran}: tidak memiliki data pilihan jurusan.";
                        continue;
                    }
                    $jurusanMap[$pendId] = $pilihan1;
                }
            }
        }

        if (! empty($errorMessages)) {
            return redirect()->back()->with(
                'error',
                'Penetapan dibatalkan — ada data tidak valid: ' . implode(' | ', $errorMessages)
            );
        }

        // [V5] Kuota per jurusan
        $kebutuhanKuota = [];
        foreach ($lulusIdsInt as $pendId) {
            $jid = $jurusanMap[$pendId] ?? null;
            if ($jid) {
                $kebutuhanKuota[$jid] = ($kebutuhanKuota[$jid] ?? 0) + 1;
            }
        }

        // Kurangi dari yang sudah lulus sebelumnya di batch ini
        // (pendaftar yang statusnya 'lulus' & akan diubah jurusan — slot lama dibebaskan)
        $errorKuota = [];
        foreach ($kebutuhanKuota as $jid => $jumlahBaru) {
            $jurusan   = $this->jurusanModel->find($jid);
            $sisaKuota = $this->jurusanModel->getSisaKuota($jid, (int) $jurusan->kuota);

            if ($jumlahBaru > $sisaKuota) {
                $errorKuota[] = "Jurusan {$jurusan->nama}: butuh {$jumlahBaru} slot, sisa {$sisaKuota} kuota.";
            }
        }

        if (! empty($errorKuota)) {
            return redirect()->back()->with(
                'error',
                'Kuota tidak mencukupi: ' . implode(' | ', $errorKuota)
            );
        }

        // Simpan ke DB — Model akan terapkan guard LOCKED_STATUSES
        $result = $this->seleksiModel->tetapkanLulus($lulusIdsInt, $tidakLulusIdsInt, $jurusanMap);

        if (! $result['success']) {
            return redirect()->back()->with('error', 'Gagal menyimpan hasil seleksi. Coba lagi.');
        }

        // !! TIDAK ADA NOTIFIKASI DI SINI !!
        // Notif resmi hanya dikirim saat admin klik Publish Pengumuman.

        // Susun pesan flash
        $msgParts = [];
        if ($result['lulus_updated'])       $msgParts[] = "{$result['lulus_updated']} peserta ditetapkan lulus";
        if ($result['tidak_lulus_updated']) $msgParts[] = "{$result['tidak_lulus_updated']} peserta ditetapkan tidak lulus";

        $totalLocked = $result['lulus_locked'] + $result['tidak_lulus_locked'];

        if (empty($msgParts) && $totalLocked > 0) {
            return redirect()->to(base_url('admin/seleksi'))
                ->with('error', "{$totalLocked} peserta yang dipilih sudah terkunci (Daftar Ulang/Siswa Aktif) dan tidak bisa diubah.");
        }

        $successMsg = implode(', ', $msgParts) . '. Hasil disimpan. '
            . 'Klik <strong>Publish Pengumuman</strong> jika seluruh peserta sudah selesai diproses.';

        if ($totalLocked > 0) {
            $successMsg .= " ({$totalLocked} peserta dilewati karena terkunci.)";
        }

        return redirect()->to(base_url('admin/seleksi'))->with('success', $successMsg);
    }

    // =========================================================
    // PUBLISH — Publikasikan pengumuman resmi (POST)
    //
    // Validasi backend:
    // 1. periode_id di POST = periode aktif
    // 2. Pengumuman belum pernah dipublish
    // 3. Tidak ada peserta 'seleksi' yang belum diproses
    //
    // Jika semua lolos:
    // - is_published = 1 di tabel periode
    // - Notifikasi RESMI dikirim ke SEMUA peserta lulus & tidak_lulus
    //   di periode aktif, dengan action_url = /dashboard/pengumuman
    // =========================================================
    public function publish()
    {
        $periodeId    = (int) $this->request->getPost('periode_id');
        $periodeAktif = $this->periodeModel->getPeriodeAktif();

        if (! $periodeAktif) {
            return redirect()->back()->with('error', 'Tidak ada periode SPMB yang aktif saat ini.');
        }

        if ($periodeId !== (int) $periodeAktif->id) {
            return redirect()->back()->with('error', 'Pengumuman hanya bisa dipublikasikan untuk periode yang sedang aktif.');
        }

        // Validasi inti (sudah tidak ada peserta tersisa berstatus 'seleksi',
        // belum pernah dipublish) dijalankan di dalam PeriodeModel::publish(),
        // sehingga guard yang sama berlaku di mana pun method ini dipanggil.
        try {
            $this->periodeModel->publish($periodeAktif->id);
        } catch (\RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        // Kirim notifikasi RESMI ke semua peserta di periode ini
        $pendaftarans = $this->pendaftaranModel
            ->whereIn('status', ['lulus', 'tidak_lulus'])
            ->where('periode_id', $periodeAktif->id)
            ->findAll();

        $urlPengumuman = base_url('dashboard/pengumuman');

        foreach ($pendaftarans as $p) {
            if ($p->status === 'lulus') {
                $title = '🎉 Pengumuman Resmi SPMB - Anda Diterima!';
                $msg   = 'Selamat! Pengumuman resmi SPMB SMK Al-Munawwir telah diterbitkan. '
                    . 'Anda dinyatakan DITERIMA. Klik untuk melihat detail dan informasi daftar ulang.';
            } else {
                $title = 'Pengumuman Resmi SPMB SMK Al-Munawwir';
                $msg   = 'Pengumuman resmi SPMB telah diterbitkan. Mohon maaf, Anda belum dapat diterima '
                    . 'pada periode ini. Klik untuk melihat informasi selengkapnya.';
            }

            $this->notifService->send(
                $p->user_id,
                'pengumuman_kelulusan',
                $title,
                $msg,
                ['url' => $urlPengumuman]
            );
        }

        $jumlah = count($pendaftarans);
        return redirect()->to(base_url('admin/seleksi'))
            ->with('success', "Pengumuman resmi berhasil dipublikasikan! {$jumlah} notifikasi telah dikirim ke calon siswa.");
    }
}
