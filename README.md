# SPMB — Sistem Penerimaan Murid Baru
### SMK Al-Munawwir IIBS

Aplikasi web untuk mengelola proses Penerimaan Murid Baru (PMB) secara daring, mulai dari pendaftaran calon siswa, verifikasi berkas, seleksi, pengumuman kelulusan, daftar ulang, hingga konversi otomatis ke Buku Induk siswa.

Proyek ini merupakan **Tugas Akhir (TA)** Program Studi Sarjana Terapan Teknologi Rekayasa Perangkat Lunak, Politeknik Negeri Banyuwangi, yang dikembangkan menggunakan metodologi **Iterative Incremental**.

---

## Daftar Isi

1. [Gambaran Umum Sistem](#1-gambaran-umum-sistem)
2. [Fitur Utama](#2-fitur-utama)
3. [Alur Sistem](#3-alur-sistem)
4. [Teknologi yang Digunakan](#4-teknologi-yang-digunakan)
5. [Cara Menjalankan Proyek](#5-cara-menjalankan-proyek)
6. [Pengembang](#6-pengembang)
7. [Struktur Proyek](#7-struktur-proyek)
8. [Pengujian](#8-pengujian)
9. [Lisensi](#9-lisensi)

---

## 1. Gambaran Umum Sistem

SPMB adalah sistem informasi berbasis web yang dirancang untuk menggantikan proses penerimaan murid baru yang sebelumnya dilakukan secara manual. Sistem ini mengelola seluruh siklus PMB dalam satu platform terintegrasi dengan tiga peran (*role*) pengguna:

| Peran | Deskripsi |
|---|---|
| **Calon Siswa** | Mendaftar, mengunggah berkas, memilih jurusan, memantau status seleksi, dan melakukan daftar ulang. |
| **Admin TU (Tata Usaha)** | Memverifikasi berkas, mengelola kuota jurusan, melakukan proses seleksi, menerbitkan pengumuman, dan mengelola daftar ulang. |
| **Kepala Sekolah** | Memantau rekapitulasi dan laporan hasil PMB (dashboard & ekspor laporan). |

Sistem dibangun secara modular mengikuti arsitektur CodeIgniter 4, dengan setiap domain fungsional dipisahkan ke dalam modul tersendiri agar mudah dikembangkan dan diuji secara bertahap (iteratif).

## 2. Fitur Utama

- **Autentikasi & Otorisasi**
  - Registrasi & login calon siswa dengan verifikasi OTP email
  - Reset password via email (Brevo SMTP)
  - Single active session per akun (`session_token`) untuk mencegah login ganda
  - Role-based access control (calon siswa, admin TU, kepala sekolah)

- **Pendaftaran (Pendaftaran Module)**
  - Form pendaftaran bertahap (*step form*) dengan validasi per langkah
  - Pemilihan jurusan dengan kuota real-time (defense-in-depth terhadap race condition)
  - Guard pendaftaran berbasis periode PMB aktif
  - Generate nomor pendaftaran unik secara aman (anti duplikasi)
  - Cetak bukti pendaftaran (PDF)

- **Verifikasi Berkas (Verifikasi Module)**
  - Admin TU memverifikasi dokumen yang diunggah calon siswa
  - Status verifikasi per berkas dengan catatan revisi

- **Seleksi (Seleksi Module)**
  - Penetapan hasil seleksi (`tetapkan`) terpisah dari penerbitan pengumuman (`publish`)
  - Aksi massal (bulk) lulus/tolak dengan modal pemilihan jurusan per baris
  - Multi-layer status locking (`EDITABLE_STATUSES` / `LOCKED_STATUSES`) untuk menjaga konsistensi data setelah publish

- **Pengumuman (Pengumuman Module)**
  - Publikasi hasil kelulusan kepada calon siswa secara terjadwal

- **Daftar Ulang (DaftarUlang Module)**
  - Konfirmasi daftar ulang oleh siswa yang dinyatakan lulus
  - Verifikasi ulang berkas dan status registrasi ulang oleh Admin TU

- **Buku Induk (BukuInduk Module)**
  - Konversi otomatis data siswa yang telah daftar ulang menjadi data Buku Induk
  - Generate Nomor Induk Siswa (NIS) otomatis

- **Laporan & Dashboard**
  - Dashboard rekap untuk Admin TU dan Kepala Sekolah
  - Ekspor laporan context-aware ke PDF dan Excel

- **Notifikasi**
  - Notifikasi email otomatis pada tahapan penting alur PMB

- **Lain-lain**
  - Desain responsif (mobile-first) di seluruh halaman
  - Integrasi Google Maps pada halaman kontak/profil sekolah
  - Modal konfirmasi logout, favicon, dan branding logo sekolah

## 3. Alur Sistem

Secara garis besar, alur penggunaan sistem SPMB adalah sebagai berikut:

```
┌─────────────────┐     ┌──────────────────┐     ┌───────────────────┐
│  1. Registrasi   │ --> │  2. Verifikasi   │ --> │  3. Pendaftaran &  │
│  & Login (OTP)   │     │      OTP Email   │     │  Pengisian Berkas  │
└─────────────────┘     └──────────────────┘     └───────────────────┘
                                                            │
                                                            v
┌─────────────────┐     ┌──────────────────┐     ┌───────────────────┐
│ 6. Daftar Ulang  │ <-- │  5. Pengumuman   │ <-- │ 4. Verifikasi      │
│  (jika lulus)    │     │    Kelulusan     │     │  Berkas oleh Admin │
└─────────────────┘     └──────────────────┘     │ TU & Proses Seleksi│
        │                                        └───────────────────┘
        v
┌─────────────────┐     ┌──────────────────┐
│ 7. Konversi ke   │ --> │ 8. Rekap & Ekspor│
│   Buku Induk     │     │ Laporan (Kepsek) │
└─────────────────┘     └──────────────────┘
```

Penjelasan tiap tahap:

1. **Registrasi & Login** — Calon siswa membuat akun dan memverifikasi email melalui kode OTP.
2. **Pendaftaran** — Calon siswa mengisi biodata, memilih jurusan (sesuai kuota tersedia), dan mengunggah berkas persyaratan.
3. **Verifikasi Berkas** — Admin TU memeriksa kelengkapan dan keabsahan berkas yang diunggah.
4. **Seleksi** — Admin TU menetapkan status kelulusan berdasarkan kriteria seleksi (`tetapkan`), kemudian mempublikasikannya (`publish`) ketika sudah final.
5. **Pengumuman** — Calon siswa dapat melihat hasil kelulusan melalui akun masing-masing.
6. **Daftar Ulang** — Calon siswa yang lulus melakukan konfirmasi daftar ulang beserta kelengkapan berkas tambahan.
7. **Buku Induk** — Data siswa yang telah daftar ulang dikonversi otomatis menjadi entri Buku Induk dengan NIS yang digenerate sistem.
8. **Laporan** — Admin TU dan Kepala Sekolah dapat memantau rekapitulasi serta mengunduh laporan (PDF/Excel) dari setiap tahapan.

## 4. Teknologi yang Digunakan

**Backend**
- [CodeIgniter 4](https://codeigniter.com/) (PHP ^8.2) — framework utama
- MySQL / MariaDB — basis data
- [dompdf](https://github.com/dompdf/dompdf) — pembuatan dokumen PDF (bukti pendaftaran, laporan)
- [PhpSpreadsheet](https://github.com/PHPOffice/PhpSpreadsheet) — ekspor laporan Excel

**Frontend**
- [Tailwind CSS](https://tailwindcss.com/) — utility-first styling
- [Alpine.js](https://alpinejs.dev/) — interaktivitas ringan sisi klien

**Infrastruktur & Tooling**
- [Brevo SMTP](https://www.brevo.com/) — pengiriman email (OTP, notifikasi, reset password)
- [Coolify](https://coolify.io/) — self-hosted PaaS untuk deployment
- Docker — containerization (`DockerFile/Dockerfile`, `nixpacks.toml`)
- Git & GitHub — version control (`fixronmaulana/ta-spmb`)
- [Apache JMeter](https://jmeter.apache.org/) — pengujian performa (Load Testing & Stress Testing)

## 5. Cara Menjalankan Proyek

### Prasyarat

- PHP >= 8.2 dengan ekstensi `intl`, `mbstring`, `json`, `mysqlnd`, `curl`
- Composer
- MySQL/MariaDB
- Node.js (opsional, jika ingin build ulang aset Tailwind)

### Langkah Instalasi

1. **Clone repository**
   ```bash
   git clone https://github.com/fixronmaulana/ta-spmb.git
   cd ta-spmb
   ```

2. **Install dependency PHP**
   ```bash
   composer install
   ```

3. **Konfigurasi environment**

   Salin `.env.example` menjadi `.env`, lalu sesuaikan konfigurasi berikut:
   ```bash
   cp .env.example .env
   ```
   - `app.baseURL` — URL aplikasi (contoh: `http://localhost:8080/`)
   - `database.default.*` — kredensial database (hostname, database, username, password, port)
   - `session.*` — konfigurasi session (path, nama cookie, expiration)
   - `email.*` — konfigurasi SMTP (Brevo) untuk fitur OTP dan notifikasi email

4. **Buat database dan jalankan migrasi**
   ```bash
   php spark migrate
   ```
   Jalankan seeder jika tersedia data awal (master data jurusan, dsb):
   ```bash
   php spark db:seed NamaSeeder
   ```

5. **Jalankan server lokal**
   ```bash
   php spark serve
   ```
   Aplikasi dapat diakses melalui `http://localhost:8080`.

6. **(Opsional) Jalankan pengujian**
   ```bash
   composer test
   ```

### Deployment

Proyek ini di-deploy menggunakan **Coolify** yang terhubung langsung dengan repository GitHub `fixronmaulana/ta-spmb`, dengan database MariaDB yang berjalan pada VPS terpisah. Build image mengikuti konfigurasi pada `DockerFile/Dockerfile` dan `nixpacks.toml`.

## 6. Pengembang

| | |
|---|---|
| **Nama** | Fikron |
| **Program Studi** | Sarjana Terapan Teknologi Rekayasa Perangkat Lunak |
| **Institusi** | Politeknik Negeri Banyuwangi |
| **Jenis Proyek** | Tugas Akhir (TA) |
| **Metodologi** | Iterative Incremental |

## 7. Struktur Proyek

Struktur direktori utama mengikuti konvensi CodeIgniter 4 dengan modul-modul fungsional pada `app/Modules`:

```
app/
├── Config/          # Konfigurasi aplikasi, routing, filter, dsb.
├── Controllers/      # Controller bawaan CI4 (Home, BaseController)
├── Modules/
│   ├── Auth/          # Registrasi, login, OTP, reset password
│   ├── Pendaftaran/   # Form pendaftaran, kuota jurusan, no. pendaftaran
│   ├── Verifikasi/    # Verifikasi berkas oleh Admin TU
│   ├── Seleksi/       # Penetapan & publikasi hasil seleksi
│   ├── DaftarUlang/   # Konfirmasi & verifikasi daftar ulang
│   ├── BukuInduk/     # Konversi data siswa ke Buku Induk + generate NIS
│   ├── Dashboard/     # Dashboard tiap role
│   ├── Laporan/       # Ekspor laporan PDF/Excel
│   ├── MasterData/    # Data master (jurusan, periode, dsb.)
│   └── Notifikasi/    # Notifikasi email
├── Models/
├── Views/
├── Filters/           # Filter otorisasi & guard periode
├── Helpers/
├── Libraries/
└── Validation/
```

Setiap modul umumnya berisi `Controllers/`, `Models/`, `Views/`, dan `Services/` (atau `Repositories/`) sendiri agar logic bisnis tetap terisolasi per domain.

## 8. Pengujian

Pengujian sistem dilakukan secara bertahap mengikuti tiap iterasi pengembangan:

- **Black Box Testing** — pengujian fungsional berdasarkan skenario penggunaan tiap fitur
- **User Acceptance Test (UAT)** — kuesioner skala Likert per peran pengguna
- **System Usability Scale (SUS)** — pengukuran usabilitas sistem
- **Pengujian Performa (Apache JMeter)** — Load Testing dan Stress Testing untuk mengukur ketahanan sistem terhadap beban pengguna

## 9. Lisensi

Proyek ini menggunakan lisensi [MIT](LICENSE), mengikuti lisensi bawaan dari framework CodeIgniter 4 yang menjadi basis pengembangan.