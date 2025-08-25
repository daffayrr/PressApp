<?php
$pageTitle = "Dashboard"; // Judul halaman untuk tag <title>
// Panggil header baru Anda. Path disesuaikan.
require_once 'template/header.php';

// Panggil sidebar baru Anda
require_once 'template/sidebar.php';

// Logika untuk mengambil data statistik (tidak berubah)
$conn = connect_db();
$total_karyawan = $conn->query("SELECT COUNT(id) as total FROM karyawan WHERE role != 'administrator'")->fetch_assoc()['total'];
$today = date("Y-m-d");
$hadir_hari_ini = $conn->query("SELECT COUNT(DISTINCT id_karyawan) as total FROM presensi WHERE DATE(waktu) = '$today'")->fetch_assoc()['total'];
$terlambat_hari_ini = $conn->query("SELECT COUNT(id) as total FROM presensi WHERE DATE(waktu) = '$today' AND status = 'Terlambat'")->fetch_assoc()['total'];
$pengajuan_menunggu = $conn->query("SELECT COUNT(id) as total FROM pengajuan WHERE status_pengajuan = 'menunggu'")->fetch_assoc()['total'];
$conn->close();
?>

<!-- KONTEN UTAMA HALAMAN DIMULAI DI SINI -->
<main class="main-content">
    <div class="content-panel">
        <h1 class="text-2xl font-bold mb-6">Dashboard</h1>
        
        <!-- Grid untuk Kartu Statistik -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 24px;">
            
            <!-- Kartu 1: Total Karyawan -->
            <div style="border-left: 4px solid #0d6efd; background-color: #fff; padding: 20px; border-radius: 6px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <p style="color: #6c757d; font-size: 0.9rem;">Total Karyawan</p>
                <p style="font-size: 2rem; font-weight: 700;"><?= $total_karyawan; ?></p>
            </div>

            <!-- Kartu 2: Hadir Hari Ini -->
            <div style="border-left: 4px solid #198754; background-color: #fff; padding: 20px; border-radius: 6px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <p style="color: #6c757d; font-size: 0.9rem;">Hadir Hari Ini</p>
                <p style="font-size: 2rem; font-weight: 700;"><?= $hadir_hari_ini; ?></p>
            </div>

            <!-- Kartu 3: Terlambat Hari Ini -->
            <div style="border-left: 4px solid #ffc107; background-color: #fff; padding: 20px; border-radius: 6px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <p style="color: #6c757d; font-size: 0.9rem;">Terlambat Hari Ini</p>
                <p style="font-size: 2rem; font-weight: 700;"><?= $terlambat_hari_ini; ?></p>
            </div>

            <!-- Kartu 4: Pengajuan Menunggu -->
            <div style="border-left: 4px solid #dc3545; background-color: #fff; padding: 20px; border-radius: 6px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <p style="color: #6c757d; font-size: 0.9rem;">Pengajuan Menunggu</p>
                <p style="font-size: 2rem; font-weight: 700;"><?= $pengajuan_menunggu; ?></p>
            </div>

        </div>
    </div>
</main>

<?php
// Panggil footer baru Anda
require_once 'template/footer.php';
?>
