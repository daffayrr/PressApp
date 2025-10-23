<?php
// Memulai sesi di paling atas file
session_start();

// --- PERBAIKAN 1: Path redirect untuk user yang sudah login ---
// Jika user sudah login, arahkan ke dashboard.php di folder yang sama.
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: dashboard.php");
    exit;
}

// Memasukkan file konfigurasi database
// Path ini mengasumsikan 'config' berada satu level di atas 'sistem', 
// jika sejajar, gunakan 'config/database.php'
require_once __DIR__ . '/../config/database.php';

// Variabel untuk menyimpan pesan error
$error_message = '';

// Cek apakah form telah disubmit menggunakan metode POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Ambil data dari form dan bersihkan
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $error_message = "Nama pengguna dan kata sandi tidak boleh kosong.";
    } else {
        // Buat koneksi ke database
        $conn = connect_db();

        // Siapkan query menggunakan prepared statement untuk keamanan
        $sql = "SELECT id, nama_lengkap, username, password, role FROM karyawan WHERE username = ?";
        
        if ($stmt = $conn->prepare($sql)) {
            // Bind variabel ke prepared statement sebagai parameter
            $stmt->bind_param("s", $username);

            // Eksekusi statement
            if ($stmt->execute()) {
                // Simpan hasil
                $stmt->store_result();

                // Cek jika username ada, lalu verifikasi password
                if ($stmt->num_rows == 1) {
                    // Bind hasil ke variabel
                    $stmt->bind_result($id, $nama_lengkap, $db_username, $hashed_password, $role);
                    if ($stmt->fetch()) {
                        // Verifikasi password
                        if (password_verify($password, $hashed_password)) {
                            
                            // Cek apakah rolenya adalah administrator
                            if ($role === 'administrator') {
                                // Password dan role benar, mulai session baru
                                session_regenerate_id(); // Mencegah session fixation
                                
                                $_SESSION['admin_logged_in'] = true;
                                $_SESSION['admin_id'] = $id;
                                $_SESSION['admin_username'] = $db_username;
                                $_SESSION['admin_nama'] = $nama_lengkap;

                                // --- PERBAIKAN 2: Path redirect setelah login sukses ---
                                // Arahkan ke dashboard.php di folder yang sama.
                                header("Location: dashboard.php");
                                exit;
                            } else {
                                // Jika bukan administrator
                                $error_message = "Akses ditolak. Anda bukan administrator.";
                            }
                        } else {
                            // Password salah
                            $error_message = "Nama pengguna atau kata sandi salah.";
                        }
                    }
                } else {
                    // Username tidak ditemukan
                    $error_message = "Nama pengguna atau kata sandi salah.";
                }
            } else {
                $error_message = "Terjadi kesalahan. Silakan coba lagi.";
            }
            // Tutup statement
            $stmt->close();
        }
        // Tutup koneksi
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Administrator - Sistem Presensi PressApp</title>
    
    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Inter', sans-serif; }
        .gradient-bg {
            background-color: #f0f4f8;
            background-image: radial-gradient(circle at top left, #e0e7ff, transparent 50%),
                              radial-gradient(circle at bottom right, #d1fae5, transparent 50%);
        }
    </style>
</head>
<body class="gradient-bg min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-4xl bg-white rounded-2xl shadow-2xl overflow-hidden flex flex-col md:flex-row">
        
        <!-- Kolom Kiri: Informasi & Branding -->
        <div class="w-full md:w-1/2 p-8 sm:p-12 text-white bg-blue-500 flex flex-col justify-center items-center md:items-start text-center md:text-left">
            <div class="flex items-center justify-center md:justify-start mb-6">
                <!-- Ganti dengan logo dinas Anda -->
                <img src="../assets/images/logo.png" class="h-12"> <!-- Contoh path logo -->
            </div>
            <p class="text-lg font-medium mb-2">Selamat Datang di Portal Administrator</p>
            <p class="text-blue-100 leading-relaxed">Silakan masuk untuk mengelola data kepegawaian, memantau kehadiran, dan mengakses laporan presensi.</p>
        </div>

        <!-- Kolom Kanan: Form Login -->
        <div class="w-full md:w-1/2 p-8 sm:p-12 bg-gray-50 flex flex-col justify-center">
            <h2 class="text-2xl font-bold text-gray-800 mb-2">Masuk ke Akun Anda</h2>
            <p class="text-gray-600 mb-6">Gunakan akun yang telah terdaftar.</p>

            <?php if (!empty($error_message)): ?>
            <!-- Menampilkan Pesan Error jika ada -->
            <div class="p-4 mb-4 text-sm text-red-800 bg-red-100 rounded-lg" role="alert">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
            <?php endif; ?>

            <!-- Form mengirim data ke halaman ini sendiri -->
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                <!-- Input Nama Pengguna -->
                <div class="mb-5">
                    <label for="username" class="block mb-2 text-sm font-medium text-gray-700">Nama Pengguna</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a4 4 0 00-4 4v1H5a1 1 0 00-.993.883L4 8v8a2 2 0 002 2h8a2 2 0 002-2V8a1 1 0 00-1-1h-1V6a4 4 0 00-4-4zm0 2a2 2 0 012 2v1H8V6a2 2 0 012-2z"></path></svg>
                        </div>
                        <input type="text" id="username" name="username" class="bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5" placeholder="Masukkan nama pengguna" required>
                    </div>
                </div>

                <!-- Input Kata Sandi -->
                <div class="mb-6">
                    <label for="password" class="block mb-2 text-sm font-medium text-gray-700">Kata Sandi</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path></svg>
                        </div>
                        <input type="password" id="password" name="password" class="bg-gray-100 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2.5" placeholder="••••••••" required>
                    </div>
                </div>

                <!-- Tombol Masuk -->
                <div>
                    <button type="submit" class="w-full text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-3 text-center transition duration-300 ease-in-out">
                        Masuk
                    </button>
                </div>
            </form>

            <div class="text-center mt-8">
                <p class="text-sm text-gray-500">
                    &copy; <?php echo date("Y"); ?> - Hak Cipta Dilindungi.
                </p>
            </div>
        </div>
    </div>

</body>
</html>
