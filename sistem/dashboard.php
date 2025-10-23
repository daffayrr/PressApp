<?php
// 1. Panggil file konfigurasi dan header
require_once '../config/database.php';
$pageTitle = "Dashboard";
require_once __DIR__ . '/template/header.php';

// 2. Jalankan logika database
$conn = connect_db();
$total_karyawan = $conn->query("SELECT COUNT(id) as total FROM karyawan WHERE role != 'administrator'")->fetch_assoc()['total'];
$today = date("Y-m-d");
$hadir_hari_ini = $conn->query("SELECT COUNT(DISTINCT id_karyawan) as total FROM presensi WHERE DATE(waktu) = '$today'")->fetch_assoc()['total'];
$terlambat_hari_ini = $conn->query("SELECT COUNT(id) as total FROM presensi WHERE DATE(waktu) = '$today' AND status = 'Terlambat'")->fetch_assoc()['total'];
$pengajuan_menunggu = $conn->query("SELECT COUNT(id) as total FROM pengajuan WHERE status_pengajuan = 'menunggu'")->fetch_assoc()['total'];
$conn->close();

// 3. Panggil sidebar
require_once __DIR__ . '/template/sidebar.php';
?>

<!-- KONTEN UTAMA HALAMAN DIMULAI DI SINI -->
<main class="main-content">
    <div class="content-panel" style="display: flex; flex-direction: column; gap: 1.5rem;">
        
        <!-- Kartu Selamat Datang -->
        <div style="background: linear-gradient(90deg, #0d6efd, #0dcaf0); color: white; padding: 24px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <h1 style="font-size: 1.75rem; font-weight: 700;">Selamat Datang Kembali, <?php echo htmlspecialchars($_SESSION['admin_nama'] ?? 'Admin'); ?>!</h1>
            <p style="margin-top: 4px; opacity: 0.9;">Berikut adalah ringkasan aktivitas sistem presensi hari ini.</p>
        </div>

        <!-- Grid untuk Kartu Statistik -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.5rem;">
            
            <!-- Kartu 1: Total Karyawan -->
            <div style="background-color: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 1rem;">
                <div style="background-color: #e7f1ff; padding: 12px; border-radius: 50%;"><i class="fa-solid fa-users" style="font-size: 1.5rem; color: #0d6efd;"></i></div>
                <div>
                    <p style="color: #6c757d; font-size: 0.9rem;">Total Karyawan</p>
                    <p style="font-size: 2rem; font-weight: 700;"><?= $total_karyawan; ?></p>
                </div>
            </div>

            <!-- Kartu 2: Hadir Hari Ini -->
            <div style="background-color: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 1rem;">
                <div style="background-color: #d1e7dd; padding: 12px; border-radius: 50%;"><i class="fa-solid fa-user-check" style="font-size: 1.5rem; color: #198754;"></i></div>
                <div>
                    <p style="color: #6c757d; font-size: 0.9rem;">Hadir Hari Ini</p>
                    <p style="font-size: 2rem; font-weight: 700;"><?= $hadir_hari_ini; ?></p>
                </div>
            </div>

            <!-- Kartu 3: Terlambat Hari Ini -->
            <div style="background-color: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 1rem;">
                <div style="background-color: #fff3cd; padding: 12px; border-radius: 50%;"><i class="fa-solid fa-user-clock" style="font-size: 1.5rem; color: #ffc107;"></i></div>
                <div>
                    <p style="color: #6c757d; font-size: 0.9rem;">Terlambat Hari Ini</p>
                    <p style="font-size: 2rem; font-weight: 700;"><?= $terlambat_hari_ini; ?></p>
                </div>
            </div>

            <!-- Kartu 4: Pengajuan Menunggu -->
            <div style="background-color: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 1rem;">
                <div style="background-color: #f8d7da; padding: 12px; border-radius: 50%;"><i class="fa-solid fa-inbox" style="font-size: 1.5rem; color: #dc3545;"></i></div>
                <div>
                    <p style="color: #6c757d; font-size: 0.9rem;">Pengajuan Menunggu</p>
                    <p style="font-size: 2rem; font-weight: 700;"><?= $pengajuan_menunggu; ?></p>
                </div>
            </div>
        </div>
    </div>
</main>

<?php
// Panggil footer
require_once __DIR__ . '/template/footer.php';
?>
