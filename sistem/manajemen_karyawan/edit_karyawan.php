<?php
// 1. Panggil file konfigurasi dan header
require_once '../../config/database.php';
$pageTitle = "Edit Karyawan";
require_once __DIR__ . '/../template/header.php';

// 2. Ambil ID dari URL dan data dari database
if (!isset($_GET['id']) || empty($_GET['id'])) {
    // Redirect jika tidak ada ID
    header("Location: data_karyawan.php");
    exit;
}

$id = $_GET['id'];
$conn = connect_db();
$sql = "SELECT id, nama_lengkap, username, jabatan, role FROM karyawan WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Redirect jika data tidak ditemukan
    header("Location: data_karyawan.php");
    exit;
}
$karyawan = $result->fetch_assoc();
$stmt->close();
$conn->close();

// 3. Panggil sidebar
require_once __DIR__ . '/../template/sidebar.php';
?>

<!-- KONTEN UTAMA HALAMAN EDIT -->
<main class="main-content">
    <div class="content-panel">
        <h1 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 1.5rem;">Edit Data Karyawan</h1>
        
        <form action="proses.php" method="POST" style="max-width: 600px;">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" value="<?php echo $karyawan['id']; ?>">
            
            <div style="display: grid; grid-template-columns: 1fr; gap: 1rem;">
                <div>
                    <label for="nama_lengkap" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Nama Lengkap</label>
                    <input type="text" name="nama_lengkap" value="<?php echo htmlspecialchars($karyawan['nama_lengkap']); ?>" style="width: 100%; padding: 0.75rem; border: 1px solid #dee2e6; border-radius: 6px;" required>
                </div>
                <div>
                    <label for="username" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Username</label>
                    <input type="text" name="username" value="<?php echo htmlspecialchars($karyawan['username']); ?>" style="width: 100%; padding: 0.75rem; border: 1px solid #dee2e6; border-radius: 6px;" required>
                </div>
                <div>
                    <label for="password" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Password Baru (Opsional)</label>
                    <input type="password" name="password" placeholder="Kosongkan jika tidak ingin diubah" style="width: 100%; padding: 0.75rem; border: 1px solid #dee2e6; border-radius: 6px;">
                </div>
                <div>
                    <label for="jabatan" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Jabatan</label>
                    <input type="text" name="jabatan" value="<?php echo htmlspecialchars($karyawan['jabatan']); ?>" style="width: 100%; padding: 0.75rem; border: 1px solid #dee2e6; border-radius: 6px;">
                </div>
                <div>
                    <label for="role" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Role</label>
                    <select name="role" style="width: 100%; padding: 0.75rem; border: 1px solid #dee2e6; border-radius: 6px;" required>
                        <option value="staff" <?php echo ($karyawan['role'] == 'staff') ? 'selected' : ''; ?>>Staff</option>
                        <option value="magang" <?php echo ($karyawan['role'] == 'magang') ? 'selected' : ''; ?>>Magang</option>
                    </select>
                </div>
            </div>
            <div style="margin-top: 2rem; display: flex; gap: 1rem;">
                <button type="submit" style="padding: 0.75rem 1.5rem; background-color: #0d6efd; color: white; border: none; border-radius: 6px; cursor: pointer;">Simpan Perubahan</button>
                <a href="data_karyawan.php" style="padding: 0.75rem 1.5rem; background-color: #6c757d; color: white; text-decoration: none; border-radius: 6px;">Batal</a>
            </div>
        </form>
    </div>
</main>

<?php
// Panggil footer
require_once __DIR__ . '/../template/footer.php';
?>
