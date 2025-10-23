<?php
// 1. Panggil file konfigurasi dan header
require_once '../../config/database.php';
$pageTitle = "Rekap Laporan Total";
require_once __DIR__ . '/../template/header.php';

// 2. Logika untuk filter dan pengambilan data
$conn = connect_db();
$filter_tanggal_mulai = $_GET['tanggal_mulai'] ?? date('Y-m-01');
$filter_tanggal_selesai = $_GET['tanggal_selesai'] ?? date('Y-m-d');

// Query utama yang menggabungkan semua data rekap per karyawan
$sql = "
    SELECT
        k.id,
        k.nama_lengkap,
        k.username,
        (SELECT COUNT(DISTINCT DATE(waktu)) FROM presensi WHERE id_karyawan = k.id AND tipe = 'masuk' AND DATE(waktu) BETWEEN ? AND ?) as jumlah_masuk,
        (SELECT COUNT(*) FROM presensi WHERE id_karyawan = k.id AND status = 'Terlambat' AND DATE(waktu) BETWEEN ? AND ?) as jumlah_keterlambatan,
        (SELECT COUNT(*) FROM pengajuan WHERE id_karyawan = k.id AND tipe_pengajuan = 'cuti' AND status_pengajuan = 'disetujui' AND tanggal_mulai BETWEEN ? AND ?) as jumlah_cuti,
        (SELECT COUNT(*) FROM pengajuan WHERE id_karyawan = k.id AND tipe_pengajuan = 'lembur' AND status_pengajuan = 'disetujui' AND tanggal_mulai BETWEEN ? AND ?) as jumlah_lembur
    FROM
        karyawan k
    WHERE
        k.role != 'administrator'
    GROUP BY
        k.id, k.nama_lengkap, k.username
    ORDER BY
        k.nama_lengkap ASC;
";

$stmt = $conn->prepare($sql);
// Bind parameter untuk setiap subquery
$stmt->bind_param("ssssssss", 
    $filter_tanggal_mulai, $filter_tanggal_selesai, 
    $filter_tanggal_mulai, $filter_tanggal_selesai,
    $filter_tanggal_mulai, $filter_tanggal_selesai,
    $filter_tanggal_mulai, $filter_tanggal_selesai
);
$stmt->execute();
$rekap_data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();

// 3. Panggil sidebar
require_once __DIR__ . '/../template/sidebar.php';
?>

<!-- KONTEN UTAMA HALAMAN DIMULAI DI SINI -->
<main class="main-content">
    <div class="content-panel">
        <h1 style="font-size: 1.5rem; font-weight: 700; color: #0d6efd; margin-bottom: 0.5rem;">Dashboard - Rekap Laporan Karyawan</h1>
        <hr style="margin-bottom: 1.5rem;">

        <!-- Form Filter dan Tombol Aksi -->
        <div style="background-color: #f8f9fa; padding: 1rem; border-radius: 6px; margin-bottom: 1.5rem; border: 1px solid #dee2e6;">
            <form action="rekap_laporan.php" method="GET" style="display: flex; flex-wrap: wrap; align-items: flex-end; gap: 1rem;">
                <div>
                    <label for="tanggal_mulai" style="font-size: 0.875rem; font-weight: 500;">Dari Tanggal:</label>
                    <input type="date" id="tanggal_mulai" name="tanggal_mulai" value="<?php echo htmlspecialchars($filter_tanggal_mulai); ?>" style="margin-top: 0.25rem; border: 1px solid #ced4da; border-radius: 4px; padding: 0.5rem;">
                </div>
                <div>
                    <label for="tanggal_selesai" style="font-size: 0.875rem; font-weight: 500;">Sampai Tanggal:</label>
                    <input type="date" id="tanggal_selesai" name="tanggal_selesai" value="<?php echo htmlspecialchars($filter_tanggal_selesai); ?>" style="margin-top: 0.25rem; border: 1px solid #ced4da; border-radius: 4px; padding: 0.5rem;">
                </div>
                <button type="submit" style="padding: 0.5rem 1rem; color: white; background-color: #0d6efd; border: none; border-radius: 4px; cursor: pointer;">
                    Tampilkan Rekap
                </button>
                <a href="../lib/cetak_rekap.php?tanggal_mulai=<?php echo $filter_tanggal_mulai; ?>&tanggal_selesai=<?php echo $filter_tanggal_selesai; ?>" 
                   target="_blank"
                   style="padding: 0.5rem 1rem; color: white; background-color: #dc3545; border: none; border-radius: 4px; text-decoration: none;">
                    <i class="fa-solid fa-file-pdf" style="margin-right: 8px;"></i> Cetak Rekap
                </a>
            </form>
        </div>

        <!-- TABEL HTML SEDERHANA -->
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead style="background-color: #343a40; color: white;">
                    <tr>
                        <th style="padding: 12px 15px;">No</th>
                        <th style="padding: 12px 15px;">Nama Lengkap</th>
                        <th style="padding: 12px 15px;">Username</th>
                        <th style="padding: 12px 15px; text-align: center;">Jumlah Masuk</th>
                        <th style="padding: 12px 15px; text-align: center;">Jumlah Terlambat</th>
                        <th style="padding: 12px 15px; text-align: center;">Jumlah Cuti</th>
                        <th style="padding: 12px 15px; text-align: center;">Jumlah Lembur</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($rekap_data) > 0): $no = 1; ?>
                        <?php foreach($rekap_data as $row): ?>
                        <tr style="border-bottom: 1px solid #dee2e6;">
                            <td style="padding: 12px 15px;"><?php echo $no++; ?></td>
                            <td style="padding: 12px 15px; font-weight: 500;"><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                            <td style="padding: 12px 15px;"><?php echo htmlspecialchars($row['username']); ?></td>
                            <td style="padding: 12px 15px; text-align: center;"><?php echo $row['jumlah_masuk']; ?> hari</td>
                            <td style="padding: 12px 15px; text-align: center; color: <?php echo $row['jumlah_keterlambatan'] > 0 ? '#dc3545' : 'inherit'; ?>;"><?php echo $row['jumlah_keterlambatan']; ?> kali</td>
                            <td style="padding: 12px 15px; text-align: center;"><?php echo $row['jumlah_cuti']; ?> kali</td>
                            <td style="padding: 12px 15px; text-align: center;"><?php echo $row['jumlah_lembur']; ?> kali</td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="padding: 12px 15px; text-align: center; color: #6c757d;">
                                Tidak ada data untuk ditampilkan.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php
// Panggil footer
require_once __DIR__ . '/../template/footer.php';
?>
