<?php
// Menggunakan path yang benar untuk file di dalam sub-folder
require_once __DIR__ . '/../../config/database.php';
$page_title = "Pengaturan Jam Kerja";
require_once '../template/header.php';

$conn = connect_db();

// --- PROSES SIMPAN DATA ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jam_masuk = $_POST['jam_masuk'];
    $jam_pulang = $_POST['jam_pulang'];
    $toleransi_terlambat = $_POST['toleransi_terlambat']; // dalam menit

    $sql = "UPDATE pengaturan SET jam_masuk = ?, jam_pulang = ?, toleransi_terlambat = ? WHERE id = 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $jam_masuk, $jam_pulang, $toleransi_terlambat);

    if ($stmt->execute()) {
        $_SESSION['notification'] = ['message' => 'Pengaturan jam kerja berhasil diperbarui.', 'type' => 'success'];
    } else {
        $_SESSION['notification'] = ['message' => 'Gagal memperbarui pengaturan.', 'type' => 'error'];
    }
    $stmt->close();
    header("Location: jam_kerja.php");
    exit;
}

// --- AMBIL DATA PENGATURAN SAAT INI ---
$sql = "SELECT jam_masuk, jam_pulang, toleransi_terlambat FROM pengaturan WHERE id = 1";
$result = $conn->query($sql);
$pengaturan = $result->fetch_assoc();

$conn->close();
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

                <div class="bg-white p-8 rounded-xl shadow-md max-w-2xl mx-auto">
                    <h3 class="text-xl font-semibold text-gray-800 mb-6">Formulir Jam Kerja</h3>
                    <form action="jam_kerja.php" method="POST">
                        <div class="space-y-6">
                            <div>
                                <label for="jam_masuk" class="block text-sm font-medium text-gray-700">Jam Masuk Standar</label>
                                <input type="time" id="jam_masuk" name="jam_masuk" value="<?php echo htmlspecialchars($pengaturan['jam_masuk']); ?>" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            </div>
                            <div>
                                <label for="jam_pulang" class="block text-sm font-medium text-gray-700">Jam Pulang Standar</label>
                                <input type="time" id="jam_pulang" name="jam_pulang" value="<?php echo htmlspecialchars($pengaturan['jam_pulang']); ?>" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            </div>
                            <div>
                                <label for="toleransi_terlambat" class="block text-sm font-medium text-gray-700">Toleransi Keterlambatan (menit)</label>
                                <input type="number" id="toleransi_terlambat" name="toleransi_terlambat" value="<?php echo htmlspecialchars($pengaturan['toleransi_terlambat']); ?>" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            </div>
                        </div>
                        <div class="mt-8">
                            <button type="submit" class="w-full px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">
                                Simpan Pengaturan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<?php
require_once '../template/footer.php';
?>
