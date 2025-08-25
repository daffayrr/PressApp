<?php
session_start();
require_once '../config/database.php';

// Redirect ke login jika belum login
if (!isset($_SESSION['karyawan_id'])) {
    header("Location: index.php");
    exit;
}

$karyawan_id = $_SESSION['karyawan_id'];
$conn = connect_db();

// --- PROSES SIMPAN PERUBAHAN ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_lengkap = $_POST['nama_lengkap'];
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $sql = "UPDATE karyawan SET nama_lengkap = ?, username = ?, password = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $nama_lengkap, $username, $hashed_password, $karyawan_id);
    } else {
        $sql = "UPDATE karyawan SET nama_lengkap = ?, username = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $nama_lengkap, $username, $karyawan_id);
    }

    if ($stmt->execute()) {
        $_SESSION['notification'] = ['message' => 'Profil berhasil diperbarui.', 'type' => 'success'];
        $_SESSION['karyawan_nama'] = $nama_lengkap; // Update nama di sesi
    } else {
        $_SESSION['notification'] = ['message' => 'Gagal memperbarui profil.', 'type' => 'error'];
    }
    $stmt->close();
    header("Location: profil.php");
    exit;
}

// --- AMBIL DATA PROFIL SAAT INI ---
$sql = "SELECT nama_lengkap, username, jabatan FROM karyawan WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $karyawan_id);
$stmt->execute();
$profil = $stmt->get_result()->fetch_assoc();
$stmt->close();
$conn->close();

$halaman_aktif = 'profil';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - PressApp</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-200">

<div class="container mx-auto max-w-md h-screen bg-white flex flex-col shadow-lg">

    <!-- Header Aplikasi -->
    <header class="bg-white p-4 border-b border-gray-200">
        <h1 class="text-xl font-bold text-gray-800">Profil Saya</h1>
    </header>

    <!-- Konten Utama -->
    <main class="flex-1 overflow-y-auto p-6 bg-gray-50">
        
        <?php if (isset($_SESSION['notification'])): ?>
            <div class="mb-4 p-4 text-sm rounded-lg <?php echo $_SESSION['notification']['type'] === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                <?php echo htmlspecialchars($_SESSION['notification']['message']); ?>
            </div>
            <?php unset($_SESSION['notification']); ?>
        <?php endif; ?>

        <div class="text-center mb-6">
            <div class="w-24 h-24 bg-blue-600 rounded-full flex items-center justify-center text-white text-4xl font-bold mx-auto">
                <?php echo strtoupper(substr($profil['nama_lengkap'], 0, 1)); ?>
            </div>
            <h2 class="mt-4 text-2xl font-bold text-gray-800"><?php echo htmlspecialchars($profil['nama_lengkap']); ?></h2>
            <p class="text-gray-500"><?php echo htmlspecialchars($profil['jabatan']); ?></p>
        </div>

        <form action="profil.php" method="POST" class="space-y-4">
            <div>
                <label for="nama_lengkap" class="block mb-2 text-sm font-medium text-gray-700">Nama Lengkap</label>
                <input type="text" name="nama_lengkap" value="<?php echo htmlspecialchars($profil['nama_lengkap']); ?>" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg w-full p-3" required>
            </div>
            <div>
                <label for="username" class="block mb-2 text-sm font-medium text-gray-700">Username</label>
                <input type="text" name="username" value="<?php echo htmlspecialchars($profil['username']); ?>" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg w-full p-3" required>
            </div>
            <div>
                <label for="password" class="block mb-2 text-sm font-medium text-gray-700">Password Baru (Opsional)</label>
                <input type="password" name="password" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg w-full p-3" placeholder="Kosongkan jika tidak ingin diubah">
            </div>
            <div class="pt-2">
                <button type="submit" class="w-full py-3 px-6 bg-blue-600 text-white font-semibold rounded-xl hover:bg-blue-700">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </main>

    <!-- Bottom Navigation Bar -->
    <nav class="bg-white border-t border-gray-200 p-2">
        <div class="flex justify-around">
            <?php $aktif = $halaman_aktif ?? ''; ?>
            <a href="dashboard.php" class="flex flex-col items-center justify-center text-center w-full p-2 rounded-lg <?php echo ($aktif == 'dashboard') ? 'bg-blue-50 text-blue-600' : 'text-gray-500 hover:bg-gray-100'; ?>">
                <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                <span class="text-xs font-medium">Home</span>
            </a>
            <a href="riwayat.php" class="flex flex-col items-center justify-center text-center w-full p-2 rounded-lg <?php echo ($aktif == 'riwayat') ? 'bg-blue-50 text-blue-600' : 'text-gray-500 hover:bg-gray-100'; ?>">
                <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span class="text-xs font-medium">Riwayat</span>
            </a>
            <a href="pengajuan.php" class="flex flex-col items-center justify-center text-center w-full p-2 rounded-lg <?php echo ($aktif == 'pengajuan') ? 'bg-blue-50 text-blue-600' : 'text-gray-500 hover:bg-gray-100'; ?>">
                <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                <span class="text-xs font-medium">Pengajuan</span>
            </a>
            <a href="profil.php" class="flex flex-col items-center justify-center text-center w-full p-2 rounded-lg <?php echo ($aktif == 'profil') ? 'bg-blue-50 text-blue-600' : 'text-gray-500 hover:bg-gray-100'; ?>">
                <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                <span class="text-xs font-medium">Profil</span>
            </a>
        </div>
    </nav>
</div>
</body>
</html>
