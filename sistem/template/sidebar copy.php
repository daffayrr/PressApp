<?php
// Ganti '/PressApp/' dengan nama folder proyek Anda jika berbeda.
$base_url = '/_Tekkomdik/PressApp/sistem/';
$current_uri = $_SERVER['REQUEST_URI'];

// Fungsi yang diperbarui untuk mengecek menu aktif secara lebih spesifik
function is_active($uri_segment) {
    global $current_uri;
    // Gunakan strpos untuk folder, dan basename untuk file spesifik jika perlu
    if (strpos($current_uri, $uri_segment) !== false) {
        // Khusus untuk 'manajemen_presensi', kita cek lebih detail
        if ($uri_segment === 'data_presensi.php') {
            // Hanya aktif jika nama filenya persis 'data_presensi.php'
            return basename($current_uri) === 'data_presensi.php';
        }
        if ($uri_segment === 'rekap_laporan.php') {
            // Hanya aktif jika nama filenya persis 'rekap_laporan.php'
            return basename($current_uri) === 'rekap_laporan.php';
        }
        return true;
    }
    return false;
}
?>
<aside class="sidebar">
    <ul>
        <li class="menu-header">Utama</li>
        <li class="<?= is_active('dashboard.php'); ?>">
            <a href="<?= $base_url ?>dashboard.php"><i class="fa-solid fa-house"></i> Dashboard</a>
        </li>

        <li class="menu-header">Manajemen</li>
        <li class="<?= is_active('manajemen_karyawan'); ?>">
            <a href="<?= $base_url ?>manajemen_karyawan/data_karyawan.php"><i class="fa-solid fa-users"></i> Data Karyawan</a>
        </li>
        <!-- PERBAIKAN DI SINI -->
        <li class="<?= is_active('data_presensi.php') ? 'active' : ''; ?>">
            <a href="<?= $base_url ?>manajemen_presensi/data_presensi.php"><i class="fa-solid fa-clipboard-user"></i> Data Presensi</a>
        </li>
        <li class="<?= is_active('rekap_laporan.php') ? 'active' : ''; ?>">
            <a href="<?= $base_url ?>manajemen_presensi/rekap_laporan.php"><i class="fa-solid fa-file-invoice"></i> Rekap Laporan</a>
        </li>
        <!-- AKHIR PERBAIKAN -->

        <li class="menu-header">Pengajuan</li>
        <li class="<?= is_active('data_cuti.php'); ?>">
            <a href="<?= $base_url ?>pengajuan/data_cuti.php"><i class="fa-solid fa-calendar-alt"></i> Data Cuti</a>
        </li>
        <li class="<?= is_active('data_lembur.php'); ?>">
            <a href="<?= $base_url ?>pengajuan/data_lembur.php"><i class="fa-solid fa-business-time"></i> Data Lembur</a>
        </li>

        <li class="menu-header">Pengaturan</li>
        <li class="<?= is_active('jam_kerja.php'); ?>">
            <a href="<?= $base_url ?>pengaturan/jam_kerja.php"><i class="fa-solid fa-clock"></i> Jam Kerja</a>
        </li>
        <li class="<?= is_active('lokasi_kantor.php'); ?>">
            <a href="<?= $base_url ?>pengaturan/lokasi_kantor.php"><i class="fa-solid fa-map-marker-alt"></i> Lokasi Kantor</a>
        </li>
        <li class="<?= is_active('profil_admin.php'); ?>">
            <a href="<?= $base_url ?>pengaturan/profil_admin.php"><i class="fa-solid fa-user-cog"></i> Profil Admin</a>
        </li>

        <li class="menu-header">Akun</li>
        <li>
            <a href="<?= $base_url ?>logout.php"><i class="fa-solid fa-sign-out-alt"></i> Logout</a>
        </li>
    </ul>
</aside>
