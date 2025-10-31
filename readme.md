# PressApp - Sistem Presensi Online Berbasis Web

[![PHP Version](https://img.shields.io/badge/PHP-%5E8.0-777BB4?style=flat&logo=php)](https://www.php.net/)
[![MySQL Version](https://img.shields.io/badge/MySQL-%5E8.0-4479A1?style=flat&logo=mysql)](https://www.mysql.com/)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

**PressApp** adalah aplikasi presensi (absensi) online berbasis web yang dirancang untuk memudahkan pencatatan kehadiran karyawan menggunakan lokasi GPS. Aplikasi ini terdiri dari dua bagian utama: portal *mobile-first* untuk karyawan dan panel administrasi yang komprehensif.

![Contoh Tampilan PressApp](https://placehold.co/800x400/343a40/f8f9fa?text=Screenshot+Aplikasi+PressApp)

## Fitur Utama

### Portal Karyawan (`/karyawan`)
Portal ini dirancang dengan pendekatan *mobile-first* untuk pengalaman pengguna yang optimal di ponsel.
* **Login Karyawan**: Sistem login terpisah untuk karyawan.
* **Dashboard Presensi**:
    * Menampilkan jam dan tanggal *real-time*.
    * Tombol dinamis (**Check In**, **Check Out**, **Sudah Presensi**, **Waktu Habis**) berdasarkan status dan jam kerja.
    * Secara otomatis meminta izin lokasi GPS dari browser saat presensi.
    * Validasi jarak (radius) dari lokasi kantor yang ditentukan admin.
    * Validasi jam kerja (termasuk penanganan terlambat dan auto-checkout).
    * Notifikasi modal interaktif (Sukses, Gagal, Izin Ditolak, Konfirmasi Terlambat).
* **Riwayat Presensi**:
    * Menampilkan daftar riwayat check-in dan check-out pribadi.
    * Menampilkan catatan khusus (mis: "Terlambat 15m 10d", "Auto Checkout").
* **Pengajuan Izin**:
    * Formulir untuk mengajukan **Izin Tidak Masuk** atau **Izin Pulang Awal**.
    * Fitur **upload file bukti** (misalnya surat dokter).
    * Melihat riwayat dan status pengajuan (Menunggu, Disetujui, Ditolak).
    * Tombol "Perbarui Status" untuk me-refresh data.
* **Profil Karyawan**: Melihat data diri dan mengubah password.

### Panel Administrasi (`/sistem`)
Portal manajemen terpusat dengan desain profesional untuk mengelola seluruh aspek sistem.
* **Login Admin**: Sistem login terpisah khusus untuk *role* 'administrator'.
* **Dashboard Admin**: Menampilkan statistik ringkas (Total Karyawan, Hadir Hari Ini, Terlambat, Pengajuan Menunggu).
* **Manajemen Karyawan**:
    * Menampilkan daftar semua karyawan (Staff & Magang) dalam tabel interaktif.
    * Fitur **pencarian** *real-time* di semua kolom.
    * **Paginasi** tabel (Previous/Next).
    * Fungsionalitas **CRUD** (Tambah, Edit, Hapus) data karyawan.
    * Notifikasi *toast* (SweetAlert2) untuk setiap aksi (Berhasil Ditambah, Dihapus, dll.).
* **Manajemen Presensi**:
    * Menampilkan rekap presensi harian dengan **filter tanggal**.
    * Tabel menampilkan **Jam Masuk**, **Jam Pulang**, **Role**, **Detail Keterlambatan** (jam, menit, detik), dan **Catatan**.
    * Link ke **Google Maps** untuk verifikasi lokasi presensi.
    * Fitur **Cetak Laporan Harian (PDF)** menggunakan FPDF, dengan format yang identik dengan tabel web.
    * Tombol Reset Presensi Harian.
* **Rekap Laporan Total**:
    * Menampilkan rekapitulasi total per karyawan dalam rentang tanggal tertentu.
    * Kolom: **Nama**, **Username**, **Jumlah Masuk**, **Jumlah Terlambat**, **Jumlah Cuti**, **Jumlah Lembur**.
    * Fitur **Cetak Laporan Rekap (PDF)**.
* **Manajemen Pengajuan**:
    * Melihat semua pengajuan (Cuti, Lembur, Izin, dll.) dalam satu tabel.
    * **Filter** berdasarkan Tipe Pengajuan atau Status.
    * Melihat detail pengajuan, termasuk link untuk melihat/mengunduh **file bukti**.
    * Tombol aksi **Approve** dan **Reject**.
* **Pengaturan**:
    * **Jam Kerja**: Form untuk mengatur jam masuk, jam pulang, dan toleransi keterlambatan.
    * **Lokasi Kantor**: Mengatur titik koordinat kantor pusat dan radius presensi menggunakan **peta interaktif (Leaflet.js)**.
    * **Manajemen Admin**: Halaman CRUD terpisah untuk mengelola akun administrator.
* **Keamanan**: Semua halaman admin dan file proses dilindungi oleh pengecekan Sesi (`$_SESSION`).

## Teknologi yang Digunakan

* **Backend**: PHP 8.x (Native/Prosedural)
* **Database**: MySQL 8.x
* **Frontend**:
    * HTML5 & CSS3 (dengan Variabel CSS)
    * JavaScript (ES6+) - Untuk semua interaktivitas (jam, validasi, AJAX/Fetch, filter tabel).
    * SweetAlert2 - Untuk notifikasi modal dan *toast* yang modern.
    * Leaflet.js - Untuk peta interaktif di pengaturan lokasi.
* **Laporan PDF**: FPDF Library (Versi 1.8x)
* **Desain Admin**: Tampilan profesional kustom (terinspirasi dari Kendo UI) menggunakan CSS murni.
* **Desain Karyawan**: Tampilan *mobile-first* kustom menggunakan Tailwind CSS (via CDN).

## Instalasi & Setup Lokal

1.  **Clone Repository**:
    ```bash
    git clone [https://github.com/USERNAME_ANDA/NAMA_REPO_ANDA.git](https://github.com/USERNAME_ANDA/NAMA_REPO_ANDA.git) pressapp
    cd pressapp
    ```
2.  **Web Server**: Pastikan Anda memiliki web server lokal yang mendukung PHP 8.x dan MySQL 8.x (misalnya XAMPP, WAMP, Laragon, atau setup manual di IIS/Apache).

3.  **Database Setup**:
    * Buat database baru di MySQL (misalnya `pressapp_db`).
    * Impor file struktur database `.sql` yang ada di proyek (jika Anda membuatnya) atau jalankan query SQL manual dari file `database_schema.sql` (disarankan untuk membuatnya).
    * Jalankan skrip `register_sementara.php` sekali melalui browser (`http://localhost/pressapp/register_sementara.php`) untuk membuat data admin dan contoh karyawan awal. **(PENTING: Hapus atau pindahkan file ini setelah dijalankan)**.

4.  **Konfigurasi Koneksi Database**:
    * Edit file `config/database.php`.
    * Sesuaikan variabel `$db_host`, `$db_user`, `$db_pass`, dan `$db_name` dengan pengaturan database lokal Anda.

5.  **Folder Upload & Library**:
    * Buat folder `uploads` di *root* direktori proyek. Di dalamnya, buat folder `bukti`. Pastikan `uploads/bukti/` memiliki izin *write* oleh web server Anda.
    * Buat folder `sistem/lib` dan letakkan file `fpdf.php` beserta folder `font` dari [FPDF](http://www.fpdf.org/ 'FPDF Website') di dalamnya.

6.  **Jalankan Aplikasi**: Arahkan web server Anda ke *root* folder proyek `pressapp`.
    * Akses portal karyawan di `http://localhost/pressapp/karyawan/`
    * Akses panel admin di `http://localhost/pressapp/sistem/`

## Akun Demo (setelah menjalankan `register_sementara.php`)

* **Admin**:
    * Username: `admin`
    * Password: `admin123`
* **Karyawan (Staff)**:
    * Username: `budi.staff`
    * Password: `staff123`
* **Karyawan (Magang)**:
    * Username: `citra.magang`
    * Password: `magang123`

## Struktur Folder Utama
