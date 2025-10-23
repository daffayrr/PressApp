<?php
// 1. Panggil file konfigurasi dan header
require_once '../../config/database.php';
$pageTitle = "Data Pengajuan Cuti";
require_once __DIR__ . '/../template/header.php';

// 2. Jalankan logika database
$conn = connect_db();
$filter_status = $_GET['status'] ?? 'semua';

$sql = "SELECT p.id, p.keterangan, p.bukti, p.status_pengajuan, k.nama_lengkap, k.jabatan, k.role
        FROM pengajuan p
        JOIN karyawan k ON p.id_karyawan = k.id
        WHERE p.tipe_pengajuan = 'cuti'";

if ($filter_status !== 'semua') {
    $sql .= " AND p.status_pengajuan = ?";
}
$sql .= " ORDER BY p.created_at DESC";

$stmt = $conn->prepare($sql);
if ($filter_status !== 'semua') {
    $stmt->bind_param("s", $filter_status);
}
$stmt->execute();
$result = $stmt->get_result();

// 3. Panggil sidebar
require_once __DIR__ . '/../template/sidebar.php';
?>

<!-- KONTEN UTAMA HALAMAN DIMULAI DI SINI -->
<main class="main-content">
    <div class="content-panel">
        <h1 style="font-size: 1.5rem; font-weight: 700; color: #0d6efd; margin-bottom: 0.5rem;">Dashboard - Data Pengajuan Cuti</h1>
        <hr style="margin-bottom: 1.5rem;">

        <!-- Form Filter Status -->
        <div style="background-color: #f8f9fa; padding: 1rem; border-radius: 6px; margin-bottom: 1.5rem; border: 1px solid #dee2e6;">
            <form action="data_cuti.php" method="GET" style="display: flex; align-items: center; gap: 1rem;">
                <div>
                    <label for="status" style="font-size: 0.875rem; font-weight: 500; color: #495057;">Filter Status:</label>
                    <select id="status" name="status" onchange="this.form.submit()" style="margin-top: 0.25rem; border: 1px solid #ced4da; border-radius: 4px; padding: 0.5rem;">
                        <option value="semua" <?php echo ($filter_status == 'semua') ? 'selected' : ''; ?>>Semua</option>
                        <option value="menunggu" <?php echo ($filter_status == 'menunggu') ? 'selected' : ''; ?>>Menunggu</option>
                        <option value="disetujui" <?php echo ($filter_status == 'disetujui') ? 'selected' : ''; ?>>Disetujui</option>
                        <option value="ditolak" <?php echo ($filter_status == 'ditolak') ? 'selected' : ''; ?>>Ditolak</option>
                    </select>
                </div>
            </form>
        </div>

        <!-- TABEL HTML SEDERHANA -->
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead style="background-color: #343a40; color: white;">
                    <tr>
                        <th style="padding: 12px 15px;">No</th>
                        <th style="padding: 12px 15px;">Nama Lengkap</th>
                        <th style="padding: 12px 15px;">Jabatan</th>
                        <th style="padding: 12px 15px;">Role</th>
                        <th style="padding: 12px 15px;">Keterangan</th>
                        <th style="padding: 12px 15px; text-align: center;">Bukti</th>
                        <th style="padding: 12px 15px;">Status</th>
                        <th style="padding: 12px 15px; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): $no = 1; ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr style="border-bottom: 1px solid #dee2e6;">
                            <td style="padding: 12px 15px;"><?php echo $no++; ?></td>
                            <td style="padding: 12px 15px; font-weight: 500;"><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                            <td style="padding: 12px 15px;"><?php echo htmlspecialchars($row['jabatan']); ?></td>
                            <td style="padding: 12px 15px;"><?php echo ucfirst(htmlspecialchars($row['role'])); ?></td>
                            <td style="padding: 12px 15px;"><?php echo htmlspecialchars($row['keterangan']); ?></td>
                            <td style="padding: 12px 15px; text-align: center;">
                                <?php if (!empty($row['bukti'])): ?>
                                    <!-- Asumsi file bukti disimpan di folder assets/bukti_cuti/ -->
                                    <a href="../assets/bukti_cuti/<?php echo htmlspecialchars($row['bukti']); ?>" target="_blank" style="color: #0d6efd; text-decoration: underline;">Lihat</a>
                                <?php else: echo '-'; endif; ?>
                            </td>
                            <td style="padding: 12px 15px;">
                                <?php
                                    $status_class = 'background-color: #fff3cd; color: #664d03;'; // Menunggu
                                    if ($row['status_pengajuan'] == 'disetujui') $status_class = 'background-color: #d1e7dd; color: #0f5132;';
                                    if ($row['status_pengajuan'] == 'ditolak') $status_class = 'background-color: #f8d7da; color: #842029;';
                                ?>
                                <span style="padding: 4px 8px; font-size: 0.8rem; font-weight: 600; border-radius: 1rem; <?= $status_class; ?>">
                                    <?php echo ucfirst(htmlspecialchars($row['status_pengajuan'])); ?>
                                </span>
                            </td>
                            <td style="padding: 12px 15px; text-align: center;">
                                <?php if ($row['status_pengajuan'] == 'menunggu'): ?>
                                    <a href="proses.php?action=approve&id=<?php echo $row['id']; ?>" style="color: #198754; text-decoration: none; margin-right: 10px;" onclick="return confirm('Approve pengajuan ini?');">Approve</a>
                                    <a href="proses.php?action=reject&id=<?php echo $row['id']; ?>" style="color: #dc3545; text-decoration: none;" onclick="return confirm('Reject pengajuan ini?');">Reject</a>
                                <?php else: echo '-'; endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="8" style="padding: 12px 15px; text-align: center; color: #6c757d;">Tidak ada data pengajuan cuti.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php
$stmt->close();
$conn->close();
require_once __DIR__ . '/../template/footer.php';
?>
