PressApp - Sistem Presensi Online Berbasis Web

PressApp adalah aplikasi presensi (absensi) online berbasis web yang dirancang untuk memudahkan pencatatan kehadiran karyawan menggunakan lokasi GPS. Aplikasi ini terdiri dari dua bagian utama: portal untuk karyawan dan panel administrasi untuk pengelolaan.

Fitur Utama

Portal Karyawan (/karyawan)

Login Karyawan: Menggunakan username dan password.

Dashboard Presensi:

Menampilkan jam dan tanggal real-time.

Tombol dinamis untuk Check In / Check Out berdasarkan status dan jam kerja.

Meminta izin lokasi browser saat presensi.

Validasi jarak berdasarkan radius kantor yang ditentukan admin.

Validasi jam kerja (fleksibel atau ketat sesuai konfigurasi terakhir).

Notifikasi modal interaktif (Sukses, Gagal, Izin Ditolak, Terlambat).

Riwayat Presensi: Menampilkan daftar riwayat check-in dan check-out pribadi, termasuk catatan (Tepat Waktu, Terlambat (detail), Auto Checkout).

Pengajuan:

Form untuk mengajukan izin tidak masuk atau izin pulang awal.

Upload file bukti (misalnya surat dokter).

Melihat riwayat dan status pengajuan (Menunggu, Disetujui, Ditolak).

Tombol "Perbarui Status" untuk melihat status terbaru.

Profil Karyawan: Melihat data diri dan mengubah password.

Desain Mobile-First: Antarmuka dirancang menyerupai aplikasi seluler dengan navigasi bawah.

Panel Administrasi (/sistem)

Login Admin: Terpisah dari login karyawan, hanya untuk role 'administrator'.

Dashboard Admin: Menampilkan statistik cepat (Total Karyawan, Hadir Hari Ini, Terlambat, Pengajuan Menunggu).

Manajemen Karyawan:

Melihat daftar semua karyawan (Staff & Magang).

Menambah, Mengedit, dan Menghapus data karyawan.

Pencarian dan paginasi tabel.

Notifikasi toast untuk aksi CRUD.

Manajemen Presensi:

Melihat detail data presensi harian dengan filter tanggal.

Menampilkan jam masuk, jam pulang, role, dan detail keterlambatan (jika ada).

Link ke Google Maps untuk melihat lokasi presensi.

Cetak Laporan Harian (PDF): Mengekspor data harian ke PDF dengan format tabel.

Tombol Reset Presensi Harian.

Rekap Laporan:

Melihat rekapitulasi total per karyawan dalam rentang tanggal tertentu (Jumlah Masuk, Terlambat, Cuti, Lembur).

Cetak Laporan Rekap (PDF).

Manajemen Pengajuan:

Melihat semua pengajuan dari karyawan dalam satu tabel.

Filter berdasarkan Tipe Pengajuan atau Status.

Melihat detail pengajuan, termasuk link ke file bukti.

Tombol Approve dan Reject.

Notifikasi toast setelah aksi.

Pengaturan:

Jam Kerja: Mengatur jam masuk, jam pulang, dan toleransi keterlambatan.

Lokasi Kantor: Mengatur titik koordinat kantor dan radius presensi menggunakan peta interaktif (Leaflet.js).

Manajemen Admin: CRUD untuk akun dengan role 'administrator'.

Logout: Mengakhiri sesi admin.

Desain Profesional: Antarmuka yang bersih dan fungsional.

Teknologi yang Digunakan

Backend: PHP 8.x (Native/Prosedural)

Database: MySQL 8.x

Frontend:

HTML5

Tailwind CSS (via CDN) - Untuk styling modern dan responsif.

JavaScript (ES6+) - Untuk interaktivitas (jam, validasi, AJAX/Fetch, notifikasi).

SweetAlert2 - Untuk notifikasi toast yang menarik.

Leaflet.js - Untuk peta interaktif di pengaturan lokasi.

Laporan PDF: FPDF Library

Instalasi & Setup Lokal

Clone Repository:

git clone [https://github.com/USERNAME_ANDA/NAMA_REPO_ANDA.git](https://github.com/USERNAME_ANDA/NAMA_REPO_ANDA.git) pressapp
cd pressapp


Web Server: Pastikan Anda memiliki web server lokal yang mendukung PHP 8.x dan MySQL 8.x (misalnya XAMPP, WAMP, Laragon, atau setup manual LAMP/LEMP).

Database Setup:

Buat database baru di MySQL (misalnya pressapp_db).

Impor file struktur database .sql yang disediakan (jika ada) atau jalankan query SQL manual yang ada di dokumentasi/komentar kode untuk membuat tabel (karyawan, presensi, pengajuan, pengaturan).

Jalankan skrip register_sementara.php sekali melalui browser (http://localhost/pressapp/register_sementara.php) untuk membuat data admin dan contoh karyawan awal. Hapus atau pindahkan file ini setelah dijalankan.

Konfigurasi Koneksi Database:

Salin config/database.example.php menjadi config/database.php.

Edit file config/database.php dan sesuaikan detail koneksi database Anda (host, username, password, nama database).

Folder Upload: Buat folder uploads di root direktori proyek, dan di dalamnya buat folder bukti. Pastikan folder uploads/bukti/ memiliki izin write oleh web server Anda.

mkdir uploads
mkdir uploads/bukti
# Sesuaikan permission jika perlu (misal di Linux):
# sudo chown -R www-data:www-data uploads
# sudo chmod -R 755 uploads


Jalankan Aplikasi: Arahkan web server Anda ke root folder proyek pressapp.

Akses portal karyawan di http://localhost/pressapp/karyawan/.

Akses panel admin di http://localhost/pressapp/sistem/.

Akun Demo (setelah menjalankan register_sementara.php)

Admin:

Username: admin

Password: admin123

Karyawan (Staff):

Username: budi.staff

Password: staff123

Karyawan (Magang):

Username: citra.magang

Password: magang123

Struktur Folder Utama

pressapp/
├── config/           # File konfigurasi (database, dll)
├── karyawan/         # Portal untuk karyawan
│   ├── index.php     # Login karyawan
│   ├── dashboard.php # Halaman utama presensi
│   ├── riwayat.php
│   ├── pengajuan.php
│   ├── profil.php
│   └── proses_*.php  # File backend untuk karyawan
├── sistem/           # Panel untuk administrator
│   ├── index.php     # Login admin
│   ├── dashboard.php # Dashboard admin
│   ├── manajemen_karyawan/
│   ├── manajemen_presensi/
│   ├── pengajuan/
