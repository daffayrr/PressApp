<?php
// Menggunakan path yang benar untuk file di dalam sub-folder
require_once __DIR__ . '/../../config/database.php';
$page_title = "Rekap Laporan Presensi";
require_once '../template/header.php';

$conn = connect_db();

// --- LOGIKA UNTUK FILTER ---
// Set default tanggal: dari awal bulan ini sampai hari ini
$default_start_date = date('Y-m-01');
$default_end_date = date('Y-m-d');

$start_date = $_GET['start_date'] ?? $default_start_date;
$end_date = $_GET['end_date'] ?? $default_end_date;
$karyawan_id = $_GET['karyawan_id'] ?? 'semua';

// Ambil daftar karyawan untuk dropdown filter
$karyawan_list_result = $conn->query("SELECT id, nama_lengkap FROM karyawan WHERE role != 'administrator' ORDER BY nama_lengkap ASC");

// --- MEMBUAT QUERY UTAMA ---
// Query ini sedikit kompleks karena menggabungkan data 'masuk' dan 'pulang' ke dalam satu baris per hari
$sql = "
    SELECT
        DATE(p.waktu) as tanggal,
        k.id as id_karyawan,
        k.nama_lengkap,
        MIN(CASE WHEN p.tipe = 'masuk' THEN TIME(p.waktu) END) as jam_masuk,
        MAX(CASE WHEN p.tipe = 'pulang' THEN TIME(p.waktu) END) as jam_pulang
    FROM presensi p
    JOIN karyawan k ON p.id_karyawan = k.id
    WHERE DATE(p.waktu) BETWEEN ? AND ?
";

// Tambahkan filter karyawan jika dipilih
if ($karyawan_id !== 'semua') {
    $sql .= " AND k.id = ?";
}

$sql .= " GROUP BY tanggal, k.id, k.nama_lengkap ORDER BY tanggal ASC, k.nama_lengkap ASC";

$stmt = $conn->prepare($sql);

// Bind parameter sesuai dengan filter
if ($karyawan_id !== 'semua') {
    $stmt->bind_param("ssi", $start_date, $end_date, $karyawan_id);
} else {
    $stmt->bind_param("ss", $start_date, $end_date);
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
                
                <!-- Form Filter -->
                <div class="bg-white p-4 rounded-xl shadow-md mb-6">
                    <form action="rekap_laporan.php" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                        <div>
                            <label for="start_date" class="text-sm font-medium text-gray-700">Tanggal Mulai:</label>
                            <input type="date" id="start_date" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div>
                            <label for="end_date" class="text-sm font-medium text-gray-700">Tanggal Selesai:</label>
                            <input type="date" id="end_date" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div>
                            <label for="karyawan_id" class="text-sm font-medium text-gray-700">Pilih Karyawan:</label>
                            <select id="karyawan_id" name="karyawan_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="semua">-- Semua Karyawan --</option>
                                <?php while($k = $karyawan_list_result->fetch_assoc()): ?>
                                    <option value="<?php echo $k['id']; ?>" <?php echo ($karyawan_id == $k['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($k['nama_lengkap']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">
                            Tampilkan Laporan
                        </button>
                    </form>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-md">
                    <h3 class="text-xl font-semibold text-gray-800 mb-4">
                        Laporan Presensi Periode <?php echo date('d M Y', strtotime($start_date)); ?> s/d <?php echo date('d M Y', strtotime($end_date)); ?>
                    </h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3">No</th>
                                    <th scope="col" class="px-6 py-3">Tanggal</th>
                                    <th scope="col" class="px-6 py-3">Nama Karyawan</th>
                                    <th scope="col" class="px-6 py-3">Jam Masuk</th>
                                    <th scope="col" class="px-6 py-3">Jam Pulang</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result->num_rows > 0): $no = 1; ?>
                                    <?php while($row = $result->fetch_assoc()): ?>
                                    <tr class="bg-white border-b hover:bg-gray-50">
                                        <td class="px-6 py-4"><?php echo $no++; ?></td>
                                        <td class="px-6 py-4"><?php echo date('d M Y', strtotime($row['tanggal'])); ?></td>
                                        <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                                            <?php echo htmlspecialchars($row['nama_lengkap']); ?>
                                        </th>
                                        <td class="px-6 py-4 text-green-600 font-medium">
                                            <?php echo $row['jam_masuk'] ?? '-'; ?>
                                        </td>
                                        <td class="px-6 py-4 text-red-600 font-medium">
                                            <?php echo $row['jam_pulang'] ?? '-'; ?>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr class="bg-white border-b">
                                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                            Tidak ada data presensi untuk periode dan filter yang dipilih.
                                        </td>
                                    </tr>
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
