<?php
// 1. Panggil file konfigurasi dan header
require_once '../../config/database.php';
$pageTitle = "Pengaturan Jam Kerja";
require_once __DIR__ . '/../template/header.php';

$conn = connect_db();

// --- PROSES SIMPAN DATA (JIKA ADA FORM SUBMIT) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jam_masuk = $_POST['jam_masuk'];
    $jam_pulang = $_POST['jam_pulang'];
    $toleransi_terlambat = $_POST['toleransi_terlambat'];

    $sql = "UPDATE pengaturan SET jam_masuk = ?, jam_pulang = ?, toleransi_terlambat = ? WHERE id = 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $jam_masuk, $jam_pulang, $toleransi_terlambat);

    if ($stmt->execute()) {
        $_SESSION['notification'] = ['message' => 'Pengaturan jam kerja berhasil diperbarui.', 'type' => 'success'];
    } else {
        $_SESSION['notification'] = ['message' => 'Gagal memperbarui pengaturan.', 'type' => 'error'];
    }
    $stmt->close();
    // Redirect untuk menghindari resubmit form dan menampilkan notifikasi
    header("Location: jam_kerja.php");
    exit;
}

// --- AMBIL DATA PENGATURAN SAAT INI ---
$sql = "SELECT jam_masuk, jam_pulang, toleransi_terlambat FROM pengaturan WHERE id = 1";
$result = $conn->query($sql);
$pengaturan = $result->fetch_assoc();
$conn->close();

// 2. Panggil sidebar
require_once __DIR__ . '/../template/sidebar.php';
?>

<!-- KONTEN UTAMA HALAMAN DIMULAI DI SINI -->
<main class="main-content">
    <div class="content-panel">
        <h1 style="font-size: 1.5rem; font-weight: 700; color: #0d6efd; margin-bottom: 0.5rem;">Dashboard - Pengaturan Jam Kerja</h1>
        <hr style="margin-bottom: 1.5rem;">

        <?php if (isset($_SESSION['notification'])): ?>
            <div style="margin-bottom: 1.5rem; padding: 1rem; border-radius: 6px; color: #0f5132; background-color: #d1e7dd; border: 1px solid #badbcc;">
                <?php echo htmlspecialchars($_SESSION['notification']['message']); ?>
            </div>
            <?php unset($_SESSION['notification']); ?>
        <?php endif; ?>

        <form action="jam_kerja.php" method="POST" style="max-width: 600px;">
            <div style="display: grid; grid-template-columns: 1fr; gap: 1.25rem;">
                <div>
                    <label for="jam_masuk" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Jam Masuk Standar</label>
                    <input type="time" id="jam_masuk" name="jam_masuk" value="<?php echo htmlspecialchars($pengaturan['jam_masuk']); ?>" style="width: 100%; padding: 0.75rem; border: 1px solid #dee2e6; border-radius: 6px;">
                </div>
                <div>
                    <label for="jam_pulang" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Jam Pulang Standar</label>
                    <input type="time" id="jam_pulang" name="jam_pulang" value="<?php echo htmlspecialchars($pengaturan['jam_pulang']); ?>" style="width: 100%; padding: 0.75rem; border: 1px solid #dee2e6; border-radius: 6px;">
                </div>
                <div>
                    <label for="toleransi_terlambat" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Toleransi Keterlambatan (menit)</label>
                    <input type="number" id="toleransi_terlambat" name="toleransi_terlambat" value="<?php echo htmlspecialchars($pengaturan['toleransi_terlambat']); ?>" style="width: 100%; padding: 0.75rem; border: 1px solid #dee2e6; border-radius: 6px;">
                </div>
            </div>
            <div style="margin-top: 2rem;">
                <button type="submit" style="padding: 0.75rem 1.5rem; background-color: #0d6efd; color: white; border: none; border-radius: 6px; cursor: pointer;">
                    Simpan Pengaturan
                </button>
            </div>
        </form>
    </div>
</main>

<?php
// Panggil footer
require_once __DIR__ . '/../template/footer.php';
?>
