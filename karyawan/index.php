<?php
// Memulai sesi
session_start();

// Memanggil file database
require_once '../config/database.php';

// Jika karyawan sudah login, langsung arahkan ke dashboard
if (isset($_SESSION['karyawan_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error_message = '';

// Cek jika form telah disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn = connect_db();
    
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $error_message = "Username dan password tidak boleh kosong.";
    } else {
        $sql = "SELECT id, nama_lengkap, password FROM karyawan WHERE username = ?";
        
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows == 1) {
                $stmt->bind_result($id, $nama_lengkap, $hashed_password);
                if ($stmt->fetch()) {
                    // Verifikasi password
                    if (password_verify($password, $hashed_password)) {
                        // Password benar, simpan data ke session
                        $_SESSION['karyawan_id'] = $id;
                        $_SESSION['karyawan_nama'] = $nama_lengkap;
                        
                        // Arahkan ke dashboard karyawan
                        header("Location: dashboard.php");
                        exit;
                    } else {
                        $error_message = "Username atau password salah.";
                    }
                }
            } else {
                $error_message = "Username atau password salah.";
            }
            $stmt->close();
        }
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Karyawan - PressApp</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body>

<!-- Kontainer utama yang mensimulasikan layar ponsel -->
<div class="bg-gray-200 flex justify-center items-center min-h-screen">
    <div class="w-full max-w-md bg-white shadow-lg flex flex-col h-screen sm:h-auto sm:rounded-2xl p-8">

        <!-- Header -->
        <div class="text-center mb-10">
            <div class="flex items-center justify-center mb-4">
                <img src="../assets/images/logo.png" class="text-2xl font-bold"></img>
            </div>
            <p class="text-gray-500">Silakan masuk untuk melakukan presensi.</p>
        </div>

        <!-- Form Login -->
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" class="space-y-6">
            
            <?php if (!empty($error_message)): ?>
            <div class="p-4 text-sm text-red-800 bg-red-100 rounded-lg" role="alert">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
            <?php endif; ?>

            <div>
                <label for="username" class="block mb-2 text-sm font-medium text-gray-700">Username</label>
                <input type="text" id="username" name="username" class="bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-3" placeholder="Masukkan username Anda" required>
            </div>
            
            <div>
                <label for="password" class="block mb-2 text-sm font-medium text-gray-700">Password</label>
                <input type="password" id="password" name="password" class="bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-3" placeholder="••••••••" required>
            </div>

            <div class="pt-4">
                <button type="submit" class="w-full py-3 px-6 bg-blue-600 text-white font-semibold rounded-xl shadow-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-transform transform active:scale-95">
                    Masuk
                </button>
            </div>
        </form>

        <div class="text-center mt-auto pt-8">
            <p class="text-sm text-gray-400">&copy; <?php echo date("Y"); ?> - PressApp Dinas</p>
        </div>

    </div>
</div>

</body>
</html>
