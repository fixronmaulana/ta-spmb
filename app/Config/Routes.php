<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 *
 */

$routes->setDefaultNamespace('');

// PUBLIC
$routes->get('/', 'App\Modules\Auth\Controllers\AuthController::landing');
$routes->get('profil-sekolah', 'App\Modules\Auth\Controllers\AuthController::profilSekolah');
$routes->get('jurusan', 'App\Modules\Auth\Controllers\AuthController::jurusan');
$routes->get('panduan', 'App\Modules\Auth\Controllers\AuthController::panduan');
$routes->get('kontak', 'App\Modules\Auth\Controllers\AuthController::kontak');

// AUTH (hanya untuk tamu / belum login)
$routes->group('auth', ['filter' => 'guest'], function ($routes) {
    $routes->get('login',    'App\Modules\Auth\Controllers\AuthController::login');
    $routes->post('login',   'App\Modules\Auth\Controllers\AuthController::doLogin');
    $routes->get('register', 'App\Modules\Auth\Controllers\AuthController::register');
    $routes->post('register', 'App\Modules\Auth\Controllers\AuthController::doRegister');
    $routes->get('forgot-password',  'App\Modules\Auth\Controllers\AuthController::forgotPassword');
    $routes->post('forgot-password', 'App\Modules\Auth\Controllers\AuthController::doForgotPassword');
    $routes->get('reset-password/(:segment)',  'App\Modules\Auth\Controllers\AuthController::resetPassword/$1');
    $routes->post('reset-password',            'App\Modules\Auth\Controllers\AuthController::doResetPassword');
});

// Verifikasi OTP
$routes->get('auth/verify-otp',  'App\Modules\Auth\Controllers\AuthController::verifyOtp');
$routes->post('auth/verify-otp', 'App\Modules\Auth\Controllers\AuthController::doVerifyOtp');
$routes->post('auth/resend-otp', 'App\Modules\Auth\Controllers\AuthController::resendOtp');

// Force login — akhiri sesi lain dan login di browser ini
$routes->post('auth/force-login', 'App\Modules\Auth\Controllers\AuthController::doForceLogin');

// Logout
$routes->get('auth/logout',  'App\Modules\Auth\Controllers\AuthController::logout', ['filter' => 'auth:calon_siswa,admin_tu,kepala_sekolah']);
$routes->post('auth/logout', 'App\Modules\Auth\Controllers\AuthController::logout', ['filter' => 'auth:calon_siswa,admin_tu,kepala_sekolah']);

// ══════════════════════════════════════════════════════════════════════════════
// CALON SISWA
// ══════════════════════════════════════════════════════════════════════════════
$routes->group('dashboard', ['filter' => 'auth:calon_siswa'], function ($routes) {
    $routes->get('/',                                    'App\Modules\Dashboard\Controllers\DashboardController::calonSiswa');
    $routes->get('formulir',                             'App\Modules\Pendaftaran\Controllers\PendaftaranController::index');
    $routes->get('formulir/step/(:num)',                 'App\Modules\Pendaftaran\Controllers\PendaftaranController::step/$1');
    $routes->post('formulir/step/(:num)',                'App\Modules\Pendaftaran\Controllers\PendaftaranController::saveStep/$1');
    $routes->post('formulir/autosave',                   'App\Modules\Pendaftaran\Controllers\PendaftaranController::autosave');
    $routes->get('formulir/preview',                     'App\Modules\Pendaftaran\Controllers\PendaftaranController::preview');
    $routes->post('formulir/submit',                     'App\Modules\Pendaftaran\Controllers\PendaftaranController::submit');
    $routes->post('formulir/upload-dokumen',             'App\Modules\Pendaftaran\Controllers\DokumenController::upload');
    $routes->post('formulir/upload-ulang-dokumen',       'App\Modules\Pendaftaran\Controllers\DokumenController::uploadUlang');
    $routes->delete('formulir/hapus-dokumen/(:num)',     'App\Modules\Pendaftaran\Controllers\DokumenController::hapus/$1');
    $routes->get('dokumen/lihat/(:num)',                 'App\Modules\Pendaftaran\Controllers\PendaftaranController::lihatDokumen/$1');
    $routes->get('status',                               'App\Modules\Pendaftaran\Controllers\PendaftaranController::status');
    $routes->get('sukses',                               'App\Modules\Pendaftaran\Controllers\PendaftaranController::sukses');
    $routes->get('cetak-bukti',                          'App\Modules\Pendaftaran\Controllers\PendaftaranController::cetakBukti');
    $routes->get('pengumuman',                           'App\Modules\Seleksi\Controllers\PengumumanController::index');
    $routes->post('pengumuman/cari',                     'App\Modules\Seleksi\Controllers\PengumumanController::cari');

    // ── Daftar Ulang (Siswa) ────────────────────────────────────────────────
    $routes->get('daftar-ulang',                         'App\Modules\DaftarUlang\Controllers\DaftarUlangController::form');
    $routes->post('daftar-ulang',                        'App\Modules\DaftarUlang\Controllers\DaftarUlangController::submit');
    $routes->get('daftar-ulang/status',                  'App\Modules\DaftarUlang\Controllers\DaftarUlangController::status');

    $routes->get('notifikasi',                           'App\Modules\Notifikasi\Controllers\NotifikasiController::index');
});

// ══════════════════════════════════════════════════════════════════════════════
// ADMIN TU
// ══════════════════════════════════════════════════════════════════════════════
$routes->group('admin', ['filter' => 'auth:admin_tu'], function ($routes) {
    $routes->get('/',                                   'App\Modules\Dashboard\Controllers\DashboardController::adminTu');

    // Verifikasi Dokumen
    $routes->get('verifikasi',                          'App\Modules\Verifikasi\Controllers\VerifikasiController::index');
    $routes->get('verifikasi/(:num)',                   'App\Modules\Verifikasi\Controllers\VerifikasiController::detail/$1');
    $routes->post('verifikasi/(:num)/approve-dokumen',  'App\Modules\Verifikasi\Controllers\VerifikasiController::approveDokumen/$1');
    $routes->post('verifikasi/(:num)/reject-dokumen',   'App\Modules\Verifikasi\Controllers\VerifikasiController::rejectDokumen/$1');
    $routes->post('verifikasi/(:num)/approve-semua',    'App\Modules\Verifikasi\Controllers\VerifikasiController::approveSemua/$1');
    $routes->post('verifikasi/(:num)/tolak',            'App\Modules\Verifikasi\Controllers\VerifikasiController::tolakPendaftaran/$1');
    $routes->post('verifikasi/(:num)/catatan',          'App\Modules\Verifikasi\Controllers\VerifikasiController::kirimCatatan/$1');
    $routes->get('dokumen/(:num)',                      'App\Modules\Verifikasi\Controllers\VerifikasiController::streamDokumen/$1');

    // Seleksi / Penetapan Kelulusan — Admin
    $routes->get('seleksi',                             'App\Modules\Seleksi\Controllers\SeleksiController::index');
    $routes->post('seleksi/tetapkan',                   'App\Modules\Seleksi\Controllers\SeleksiController::tetapkan');
    $routes->post('seleksi/publish',                    'App\Modules\Seleksi\Controllers\SeleksiController::publish');

    // ── Verifikasi Daftar Ulang (Admin) ────────────────────────────────────
    $routes->get('daftar-ulang',                        'App\Modules\DaftarUlang\Controllers\DaftarUlangAdminController::index');
    $routes->post('daftar-ulang/(:num)/konfirmasi',     'App\Modules\DaftarUlang\Controllers\DaftarUlangAdminController::konfirmasi/$1');
    $routes->post('daftar-ulang/(:num)/tolak',          'App\Modules\DaftarUlang\Controllers\DaftarUlangAdminController::tolak/$1');
    $routes->get('daftar-ulang/(:num)/bukti',           'App\Modules\DaftarUlang\Controllers\DaftarUlangAdminController::streamBukti/$1');
    $routes->get('daftar-ulang/(:num)/detail',          'App\Modules\DaftarUlang\Controllers\DaftarUlangAdminController::detail/$1');

    // Buku Induk
    $routes->get('buku-induk/konversi',                 'App\Modules\BukuInduk\Controllers\BukuIndukController::konversiPage');
    $routes->post('buku-induk/konversi-satu',           'App\Modules\BukuInduk\Controllers\BukuIndukController::konversi');
    $routes->post('buku-induk/konversi-bulk',           'App\Modules\BukuInduk\Controllers\BukuIndukController::konversiBulk');
    $routes->post('buku-induk/konversi-bulk-selected',  'App\Modules\BukuInduk\Controllers\BukuIndukController::konversiBulkSelected');
    $routes->get('buku-induk/export-excel',             'App\Modules\BukuInduk\Controllers\BukuIndukController::exportExcel');
    $routes->post('buku-induk/export-excel-selected',   'App\Modules\BukuInduk\Controllers\BukuIndukController::exportExcelSelected');
    $routes->get('buku-induk',                          'App\Modules\BukuInduk\Controllers\BukuIndukController::index');
    $routes->get('buku-induk/(:num)',                   'App\Modules\BukuInduk\Controllers\BukuIndukController::detail/$1');
    $routes->get('buku-induk/(:num)/cetak',             'App\Modules\BukuInduk\Controllers\BukuIndukController::cetak/$1');
    $routes->get('buku-induk/(:num)/cetak-kartu',       'App\Modules\BukuInduk\Controllers\BukuIndukController::cetakKartu/$1');
    $routes->get('buku-induk/(:num)/export-excel',      'App\Modules\BukuInduk\Controllers\BukuIndukController::exportExcelSingle/$1');
    $routes->post('buku-induk/(:num)/pribadi',          'App\Modules\BukuInduk\Controllers\BukuIndukController::updatePribadi/$1');
    $routes->post('buku-induk/(:num)/kesehatan',        'App\Modules\BukuInduk\Controllers\BukuIndukController::updateKesehatan/$1');
    $routes->post('buku-induk/(:num)/kelas',            'App\Modules\BukuInduk\Controllers\BukuIndukController::updateKelas/$1');

    // ── Master Data ─────────────────────────────────────────────────────────
    $routes->get('master-data',                                   'App\Modules\MasterData\Controllers\MasterDataController::index');

    // Jurusan
    $routes->post('master-data/jurusan/simpan',                   'App\Modules\MasterData\Controllers\MasterDataController::simpanJurusan');
    $routes->post('master-data/jurusan/(:num)/hapus',             'App\Modules\MasterData\Controllers\MasterDataController::hapusJurusan/$1');

    // Kelas
    $routes->post('master-data/kelas/simpan',                     'App\Modules\MasterData\Controllers\MasterDataController::simpanKelas');
    $routes->post('master-data/kelas/(:num)/hapus',               'App\Modules\MasterData\Controllers\MasterDataController::hapusKelas/$1');

    // Periode
    $routes->post('master-data/periode/simpan',                   'App\Modules\MasterData\Controllers\MasterDataController::simpanPeriode');
    $routes->post('master-data/periode/(:num)/aktif',             'App\Modules\MasterData\Controllers\MasterDataController::setAktifPeriode/$1');

    // Dokumen
    $routes->post('master-data/dokumen/simpan',                   'App\Modules\MasterData\Controllers\MasterDataController::simpanJenisDokumen');
    $routes->post('master-data/dokumen/(:num)/toggle',            'App\Modules\MasterData\Controllers\MasterDataController::toggleJenisDokumen/$1');
    $routes->post('master-data/dokumen/(:num)/toggle-wajib',      'App\Modules\MasterData\Controllers\MasterDataController::toggleWajibJenisDokumen/$1');
    $routes->post('master-data/dokumen/(:num)/hapus',             'App\Modules\MasterData\Controllers\MasterDataController::hapusJenisDokumen/$1');
});

// ══════════════════════════════════════════════════════════════════════════════
// KEPALA SEKOLAH — hanya dashboard monitoring & laporan, tanpa approval seleksi
// ══════════════════════════════════════════════════════════════════════════════
$routes->group('kepala-sekolah', ['filter' => 'auth:kepala_sekolah'], function ($routes) {
    $routes->get('/',                           'App\Modules\Dashboard\Controllers\DashboardController::kepalaSekolah');
    $routes->get('laporan',                     'App\Modules\Laporan\Controllers\LaporanController::index');
    $routes->get('laporan/ekspor-pdf',          'App\Modules\Laporan\Controllers\LaporanController::eksporPdf');
    $routes->get('laporan/ekspor-excel',        'App\Modules\Laporan\Controllers\LaporanController::eksporExcel');
    $routes->get('laporan/arsip',               'App\Modules\Laporan\Controllers\LaporanController::arsip');
});

// ══════════════════════════════════════════════════════════════════════════════
// API
// ══════════════════════════════════════════════════════════════════════════════
$routes->group('api', ['filter' => 'auth:calon_siswa,admin_tu,kepala_sekolah'], function ($routes) {
    $routes->get('notifikasi/count',            'App\Modules\Notifikasi\Controllers\NotifikasiController::count');
    $routes->get('notifikasi/list',             'App\Modules\Notifikasi\Controllers\NotifikasiController::list');
    $routes->post('notifikasi/(:num)/read',     'App\Modules\Notifikasi\Controllers\NotifikasiController::markRead/$1');
    $routes->post('notifikasi/mark-all-read',   'App\Modules\Notifikasi\Controllers\NotifikasiController::markAllRead');
});
