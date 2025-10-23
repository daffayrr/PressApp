<?php
$pageTitle = "Data Pengajuan Karyawan";
require_once '../../config/database.php';
require_once __DIR__ . '/../template/header.php';

// Logika untuk filter
$filter_tipe = $_GET['tipe'] ?? 'semua';
$filter_status = $_GET['status'] ?? 'semua';

$conn = connect_db();

// Membangun query SQL secara dinamis berdasarkan filter
$sql = "SELECT p.id, p.tipe_pengajuan, p.tanggal_mulai, p.tanggal_selesai, p.keterangan, p.status_pengajuan, p.bukti, k.nama_lengkap, k.jabatan, k.role
        FROM pengajuan p
        JOIN karyawan k ON p.id_karyawan = k.id";

$where_clauses = [];
$bind_types = '';
$bind_values = [];

if ($filter_tipe !== 'semua') {
    $where_clauses[] = "p.tipe_pengajuan = ?";
    $bind_types .= 's';
    $bind_values[] = $filter_tipe;
}

if ($filter_status !== 'semua') {
    $where_clauses[] = "p.status_pengajuan = ?";
    $bind_types .= 's';
    $bind_values[] = $filter_status;
}

if (!empty($where_clauses)) {
    $sql .= " WHERE " . implode(" AND ", $where_clauses);
}

$sql .= " ORDER BY p.created_at DESC";

$stmt = $conn->prepare($sql);
if (!empty($bind_values)) {
    $stmt->bind_param($bind_types, ...$bind_values);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<?php require_once __DIR__ . '/../template/sidebar.php'; ?>

<main class="main-content">
    <div class="content-panel">
        <h1 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 1.5rem;">Manajemen Data Pengajuan</h1>

        <!-- Form Filter -->
        <div style="background-color: #f8f9fa; padding: 1rem; border-radius: 6px; margin-bottom: 1.5rem; border: 1px solid #dee2e6;">
            <form action="data_pengajuan.php" method="GET" style="display: flex; align-items: center; gap: 1rem;">
                <div>
                    <label for="tipe" style="font-size: 0.9rem; font-weight: 500;">Tipe Pengajuan:</label>
                    <select id="tipe" name="tipe" style="padding: 0.5rem; border-radius: 4px; border: 1px solid #ced4da;">
                        <option value="semua" <?= $filter_tipe == 'semua' ? 'selected' : '' ?>>Semua Tipe</option>
                        <option value="cuti" <?= $filter_tipe == 'cuti' ? 'selected' : '' ?>>Cuti</option>
                        <option value="lembur" <?= $filter_tipe == 'lembur' ? 'selected' : '' ?>>Lembur</option>
                        <option value="sakit" <?= $filter_tipe == 'sakit' ? 'selected' : '' ?>>Sakit</option>
                        <option value="izin" <?= $filter_tipe == 'izin' ? 'selected' : '' ?>>Izin</option>
                        <option value="pulang_awal" <?= $filter_tipe == 'pulang_awal' ? 'selected' : '' ?>>Pulang Awal</option>
                    </select>
                </div>
                <div>
                    <label for="status" style="font-size: 0.9rem; font-weight: 500;">Status:</label>
                    <select id="status" name="status" style="padding: 0.5rem; border-radius: 4px; border: 1px solid #ced4da;">
                        <option value="semua" <?= $filter_status == 'semua' ? 'selected' : '' ?>>Semua Status</option>
                        <option value="menunggu" <?= $filter_status == 'menunggu' ? 'selected' : '' ?>>Menunggu</option>
                        <option value="disetujui" <?= $filter_status == 'disetujui' ? 'selected' : '' ?>>Disetujui</option>
                        <option value="ditolak" <?= $filter_status == 'ditolak' ? 'selected' : '' ?>>Ditolak</option>
                    </select>
                </div>
                <button type="submit" style="padding: 0.5rem 1rem; background-color: #0d6efd; color: white; border: none; border-radius: 4px; cursor: pointer;">Filter</button>
            </form>
        </div>

        <div style="overflow-x: auto;">
            <table class="k-grid-table" style="width: 100%; border-collapse: collapse;">
                <thead style="background-color: #343a40; color: white;">
                    <tr>
                        <th style="padding: 12px 15px;">No</th>
                        <th style="padding: 12px 15px;">Nama Lengkap</th>
                        <th style="padding: 12px 15px;">Tipe</th>
                        <th style="padding: 12px 15px;">Tanggal</th>
                        <th style="padding: 12px 15px;">Keterangan</th>
                        <th style="padding: 12px 15px;">Bukti</th>
                        <th style="padding: 12px 15px;">Status</th>
                        <th style="padding: 12px 15px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): $no = 1; ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr style="border-bottom: 1px solid #dee2e6;">
                                <td style="padding: 12px 15px;"><?= $no++; ?></td>
                                <td style="padding: 12px 15px;"><?= htmlspecialchars($row['nama_lengkap']); ?></td>
                                <td style="padding: 12px 15px;"><?= ucfirst(str_replace('_', ' ', $row['tipe_pengajuan'])); ?></td>
                                <td style="padding: 12px 15px;">
                                    <?= date('d M Y', strtotime($row['tanggal_mulai'])); ?>
                                    <?php if($row['tanggal_mulai'] != $row['tanggal_selesai']): ?>
                                        - <?= date('d M Y', strtotime($row['tanggal_selesai'])); ?>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 12px 15px;"><?= htmlspecialchars($row['keterangan']); ?></td>
                                <td style="padding: 12px 15px;">
                                    <?php if(!empty($row['bukti'])): ?>
                                        <a href="/PressApp/uploads/bukti/<?= htmlspecialchars($row['bukti']); ?>" target="_blank" style="color: #0d6efd;">Lihat Bukti</a>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 12px 15px;">
                                    <span style="padding: 4px 8px; border-radius: 12px; font-size: 0.8rem; color: white; background-color: <?= ($row['status_pengajuan'] == 'menunggu' ? '#ffc107' : ($row['status_pengajuan'] == 'disetujui' ? '#198754' : '#dc3545')); ?>">
                                        <?= ucfirst($row['status_pengajuan']); ?>
                                    </span>
                                </td>
                                <td style="padding: 12px 15px;">
                                    <?php if($row['status_pengajuan'] == 'menunggu'): ?>
                                        <a href="proses.php?action=setuju&id=<?= $row['id']; ?>" onclick="return confirm('Anda yakin ingin menyetujui pengajuan ini?')" style="color: green; text-decoration: none; margin-right: 10px;">Approve</a>
                                        <a href="proses.php?action=tolak&id=<?= $row['id']; ?>" onclick="return confirm('Anda yakin ingin menolak pengajuan ini?')" style="color: red; text-decoration: none;">Reject</a>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="8" style="text-align: center; padding: 20px;">Tidak ada data pengajuan yang sesuai dengan filter.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- SCRIPT UNTUK NOTIFIKASI TOAST -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    <?php if (isset($_SESSION['notification'])): ?>
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });

        Toast.fire({
            icon: '<?php echo $_SESSION['notification']['type']; ?>',
            title: '<?php echo addslashes($_SESSION['notification']['message']); ?>'
        });

        <?php unset($_SESSION['notification']); ?>
    <?php endif; ?>
</script>

<?php
$stmt->close();
$conn->close();
require_once __DIR__ . '/../template/footer.php';
?>
