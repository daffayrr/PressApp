<?php
// 1. Panggil file konfigurasi database paling pertama.
require_once '../../config/database.php';

// 2. Atur variabel halaman dan panggil header.
$pageTitle = "Data Presensi";
require_once __DIR__ . '/../template/header.php';

// 3. Jalankan logika untuk filter dan pengambilan data.
$filter_tanggal = $_GET['tanggal'] ?? date('Y-m-d');
$conn = connect_db();
$sql = "SELECT p.id, p.waktu, p.tipe, p.status, p.catatan, p.latitude, p.longitude, k.nama_lengkap
        FROM presensi p
        JOIN karyawan k ON p.id_karyawan = k.id
        WHERE DATE(p.waktu) = ?
        ORDER BY p.waktu DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $filter_tanggal);
$stmt->execute();
$result = $stmt->get_result();
?>

<!-- KONTEN UTAMA HALAMAN DIMULAI DI SINI -->
<?php 
// Panggil sidebar untuk navigasi.
require_once __DIR__ . '/../template/sidebar.php'; 
?>

<main class="main-content">
    <div class="content-panel">
        <h1 style="font-size: 1.5rem; font-weight: 700; color: #0d6efd; margin-bottom: 0.5rem;">Dashboard - Data Presensi</h1>
        <hr style="margin-bottom: 1.5rem;">

        <!-- Form Filter dan Tombol Aksi -->
        <div style="background-color: #f8f9fa; padding: 1rem; border-radius: 6px; margin-bottom: 1.5rem; border: 1px solid #dee2e6;">
            <form action="data_presensi.php" method="GET" style="display: flex; align-items: center; gap: 1rem;">
                <div>
                    <label for="tanggal" style="font-size: 0.875rem; font-weight: 500; color: #495057;">Pilih Tanggal:</label>
                    <input type="date" id="tanggal" name="tanggal" value="<?php echo htmlspecialchars($filter_tanggal); ?>" style="margin-top: 0.25rem; border: 1px solid #ced4da; border-radius: 4px; padding: 0.5rem;">
                </div>
                <button type="submit" style="padding: 0.5rem 1rem; color: white; background-color: #0d6efd; border: none; border-radius: 4px; cursor: pointer;">
                    Filter
                </button>
                <a href="proses.php?action=reset&tanggal=<?php echo htmlspecialchars($filter_tanggal); ?>" 
                   style="padding: 0.5rem 1rem; color: white; background-color: #dc3545; border: none; border-radius: 4px; text-decoration: none;"
                   onclick="return confirm('PERINGATAN: Anda akan menghapus SEMUA data presensi pada tanggal <?php echo date('d M Y', strtotime($filter_tanggal)); ?>. Lanjutkan?');">
                    Reset Presensi
                </a>
            </form>
        </div>

        <h2 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1rem;">
            Laporan Tanggal: <?php echo date('d F Y', strtotime($filter_tanggal)); ?>
        </h2>

        <!-- TABEL HTML SEDERHANA -->
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead style="background-color: #343a40; color: white;">
                    <tr>
                        <th style="padding: 12px 15px;">No</th>
                        <th style="padding: 12px 15px;">Nama Karyawan</th>
                        <th style="padding: 12px 15px;">Waktu</th>
                        <th style="padding: 12px 15px;">Tipe</th>
                        <th style="padding: 12px 15px;">Status</th>
                        <th style="padding: 12px 15px;">Catatan</th>
                        <th style="padding: 12px 15px; text-align: center;">Lokasi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): $no = 1; ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr style="border-bottom: 1px solid #dee2e6;">
                            <td style="padding: 12px 15px;"><?php echo $no++; ?></td>
                            <td style="padding: 12px 15px; font-weight: 500;"><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                            <td style="padding: 12px 15px;"><?php echo date('H:i:s', strtotime($row['waktu'])); ?></td>
                            <td style="padding: 12px 15px;">
                                <?php if($row['tipe'] == 'masuk'): ?>
                                    <span style="padding: 4px 8px; font-size: 0.8rem; font-weight: 600; color: #0a58ca; background-color: #cfe2ff; border-radius: 1rem;">Masuk</span>
                                <?php else: ?>
                                    <span style="padding: 4px 8px; font-size: 0.8rem; font-weight: 600; color: #b02a37; background-color: #f8d7da; border-radius: 1rem;">Pulang</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 12px 15px;"><?php echo htmlspecialchars($row['status']); ?></td>
                            <td style="padding: 12px 15px; font-style: italic; color: #6c757d;"><?php echo htmlspecialchars($row['catatan'] ?? '-'); ?></td>
                            <td style="padding: 12px 15px; text-align: center;">
                                <a href="https://www.google.com/maps?q=<?php echo $row['latitude']; ?>,<?php echo $row['longitude']; ?>" target="_blank" style="color: #0d6efd; text-decoration: underline;">
                                    Lihat Peta
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="padding: 12px 15px; text-align: center; color: #6c757d;">
                                Tidak ada data presensi untuk tanggal ini.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php
$stmt->close();
$conn->close();
// Panggil footer baru Anda
require_once __DIR__ . '/../template/footer.php';
?>
