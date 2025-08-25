<?php
require_once __DIR__ . '/../../config/database.php';
$page_title = "Profil Administrator";
require_once '../template/header.php';

$conn = connect_db();
$admin_id = $_SESSION['admin_id']; // Ambil ID dari sesi

// --- PROSES SIMPAN DATA ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_lengkap = $_POST['nama_lengkap'];
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (!empty($password)) {
        // Jika password baru diisi, update password
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $sql = "UPDATE karyawan SET nama_lengkap = ?, username = ?, password = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $nama_lengkap, $username, $hashed_password, $admin_id);
    } else {
        // Jika password tidak diisi, jangan update password
        $sql = "UPDATE karyawan SET nama_lengkap = ?, username = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $nama_lengkap, $username, $admin_id);
    }

    if ($stmt->execute()) {
        $_SESSION['notification'] = ['message' => 'Profil berhasil diperbarui.', 'type' => 'success'];
        // Update nama di session
        $_SESSION['admin_nama'] = $nama_lengkap;
    } else {
        $_SESSION['notification'] = ['message' => 'Gagal memperbarui profil.', 'type' => 'error'];
    }
    $stmt->close();
    header("Location: profil_admin.php");
    exit;
}

// --- AMBIL DATA ADMIN SAAT INI ---
$sql = "SELECT nama_lengkap, username FROM karyawan WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();
$stmt->close();
$conn->close();
?>

<!-- Area Konten Utama -->
<div class="flex h-screen bg-gray-100">
    <?php require_once '../template/sidebar.php'; ?>
    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="flex justify-between items-center p-4 bg-white border-b shadow-sm">
            <h1 class="text-xl font-semibold text-gray-700"><?php echo htmlspecialchars($page_title); ?></h1>
        </header>
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">
            <div class="container mx-auto">
                <?php if (isset($_SESSION['notification'])): ?>
                    <div class="mb-4 p-4 text-sm rounded-lg <?php echo $_SESSION['notification']['type'] === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                        <?php echo htmlspecialchars($_SESSION['notification']['message']); ?>
                    </div>
                    <?php unset($_SESSION['notification']); ?>
                <?php endif; ?>
                <div class="bg-white p-8 rounded-xl shadow-md max-w-2xl mx-auto">
                    <h3 class="text-xl font-semibold text-gray-800 mb-6">Formulir Profil</h3>
                    <form action="profil_admin.php" method="POST">
                        <div class="space-y-6">
                            <div>
                                <label for="nama_lengkap" class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
                                <input type="text" id="nama_lengkap" name="nama_lengkap" value="<?php echo htmlspecialchars($admin['nama_lengkap']); ?>" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                            </div>
                            <div>
                                <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($admin['username']); ?>" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                            </div>
                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700">Password Baru (Opsional)</label>
                                <input type="password" id="password" name="password" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" placeholder="Kosongkan jika tidak ingin diubah">
                            </div>
                        </div>
                        <div class="mt-8">
                            <button type="submit" class="w-full px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>
<?php require_once '../template/footer.php'; ?>
