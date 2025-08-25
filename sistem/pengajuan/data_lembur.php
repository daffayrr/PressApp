<?php
require_once __DIR__ . '/../../config/database.php';
$page_title = "Data Pengajuan Lembur";
require_once '../template/header.php';

$conn = connect_db();

$filter_status = $_GET['status'] ?? 'semua';

$sql = "SELECT p.id, p.tanggal_mulai, p.tanggal_selesai, p.keterangan, p.status_pengajuan, k.nama_lengkap
        FROM pengajuan p
        JOIN karyawan k ON p.id_karyawan = k.id
        WHERE p.tipe_pengajuan = 'lembur'";

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
?>

<!-- Area Konten Utama -->
<div class="flex h-screen bg-gray-100">
    <?php require_once '../template/sidebar.php'; ?>

    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="flex justify-between items-center p-4 bg-white border-b border-gray-200 shadow-sm">
            <h1 class="text-xl font-semibold text-gray-700"><?php echo htmlspecialchars($page_title); ?></h1>
        </header>

        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">
            <div class="container mx-auto">
                
                <?php if (isset($_SESSION['notification'])): ?>
                    <div class="mb-4 p-4 text-sm rounded-lg <?php echo $_SESSION['notification']['type'] === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>" role="alert">
                        <?php echo htmlspecialchars($_SESSION['notification']['message']); ?>
                    </div>
                    <?php unset($_SESSION['notification']); ?>
                <?php endif; ?>

                <!-- Form Filter Status -->
                <div class="bg-white p-4 rounded-xl shadow-md mb-6">
                    <form action="data_lembur.php" method="GET" class="flex items-center space-x-4">
                        <div>
                            <label for="status" class="text-sm font-medium text-gray-700">Filter Status:</label>
                            <select id="status" name="status" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 rounded-md" onchange="this.form.submit()">
                                <option value="semua" <?php echo ($filter_status == 'semua') ? 'selected' : ''; ?>>Semua</option>
                                <option value="menunggu" <?php echo ($filter_status == 'menunggu') ? 'selected' : ''; ?>>Menunggu</option>
                                <option value="disetujui" <?php echo ($filter_status == 'disetujui') ? 'selected' : ''; ?>>Disetujui</option>
                                <option value="ditolak" <?php echo ($filter_status == 'ditolak') ? 'selected' : ''; ?>>Ditolak</option>
                            </select>
                        </div>
                    </form>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-md">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3">Nama Karyawan</th>
                                    <th scope="col" class="px-6 py-3">Tanggal Lembur</th>
                                    <th scope="col" class="px-6 py-3">Keterangan</th>
                                    <th scope="col" class="px-6 py-3">Status</th>
                                    <th scope="col" class="px-6 py-3">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result->num_rows > 0): ?>
                                    <?php while($row = $result->fetch_assoc()): ?>
                                    <tr class="bg-white border-b hover:bg-gray-50">
                                        <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap"><?php echo htmlspecialchars($row['nama_lengkap']); ?></th>
                                        <td class="px-6 py-4"><?php echo date('d M Y', strtotime($row['tanggal_mulai'])); ?></td>
                                        <td class="px-6 py-4"><?php echo htmlspecialchars($row['keterangan']); ?></td>
                                        <td class="px-6 py-4">
                                            <?php
                                                $status_class = 'bg-yellow-100 text-yellow-800';
                                                if ($row['status_pengajuan'] == 'disetujui') $status_class = 'bg-green-100 text-green-800';
                                                if ($row['status_pengajuan'] == 'ditolak') $status_class = 'bg-red-100 text-red-800';
                                            ?>
                                            <span class="px-2 py-1 font-semibold leading-tight rounded-full <?php echo $status_class; ?>">
                                                <?php echo ucfirst(htmlspecialchars($row['status_pengajuan'])); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <?php if ($row['status_pengajuan'] == 'menunggu'): ?>
                                                <a href="proses.php?action=setujui&id=<?php echo $row['id']; ?>" class="font-medium text-green-600 hover:underline mr-3" onclick="return confirm('Setujui pengajuan ini?');">Setujui</a>
                                                <a href="proses.php?action=tolak&id=<?php echo $row['id']; ?>" class="font-medium text-red-600 hover:underline" onclick="return confirm('Tolak pengajuan ini?');">Tolak</a>
                                            <?php else: echo '-'; endif; ?>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr class="bg-white border-b"><td colspan="5" class="px-6 py-4 text-center text-gray-500">Tidak ada data pengajuan lembur.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php
$stmt->close();
$conn->close();
require_once '../template/footer.php';
?>
