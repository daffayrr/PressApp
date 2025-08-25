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

// --- LANGKAH 1: AMBIL DATA MENTAH DARI DATABASE ---
// Query ini jauh lebih sederhana dan tidak akan menyebabkan error.
$sql = "
    SELECT 
        DATE(waktu) as tanggal, 
        TIME(waktu) as jam, 
        tipe, 
        catatan 
    FROM presensi 
    WHERE id_karyawan = ? AND waktu >= DATE_SUB(NOW(), INTERVAL 30 DAY) 
    ORDER BY waktu ASC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $karyawan_id);
$stmt->execute();
$semua_presensi = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();

// --- LANGKAH 2: OLAH DATA MENTAH MENGGUNAKAN PHP ---
$riwayat_per_hari = [];
foreach ($semua_presensi as $presensi) {
    $tanggal = $presensi['tanggal'];

    // Jika tanggal ini belum ada di array, buat entri baru
    if (!isset($riwayat_per_hari[$tanggal])) {
        $riwayat_per_hari[$tanggal] = [
            'tanggal' => $tanggal,
            'jam_masuk' => null,
            'jam_pulang' => null,
            'catatan_masuk' => null,
            'catatan_pulang' => null
        ];
    }

    // Isi data jam masuk dan pulang
    if ($presensi['tipe'] == 'masuk') {
        // Hanya ambil jam masuk yang pertama
        if ($riwayat_per_hari[$tanggal]['jam_masuk'] === null) {
            $riwayat_per_hari[$tanggal]['jam_masuk'] = $presensi['jam'];
            $riwayat_per_hari[$tanggal]['catatan_masuk'] = $presensi['catatan'];
        }
    } elseif ($presensi['tipe'] == 'pulang') {
        // Selalu timpa jam pulang untuk mendapatkan yang terakhir
        $riwayat_per_hari[$tanggal]['jam_pulang'] = $presensi['jam'];
        $riwayat_per_hari[$tanggal]['catatan_pulang'] = $presensi['catatan'];
    }
}
// Urutkan hasilnya dari tanggal terbaru ke terlama
krsort($riwayat_per_hari);


$halaman_aktif = 'riwayat';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Presensi - PressApp</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style> body { font-family: 'Inter', sans-serif; } </style>
</head>
<body class="bg-gray-200">

<div class="container mx-auto max-w-md h-screen bg-white flex flex-col shadow-lg">
    <header class="bg-white p-4 border-b border-gray-200">
        <h1 class="text-xl font-bold text-gray-800">Riwayat Presensi</h1>
        <p class="text-gray-600">Daftar kehadiran Anda 30 hari terakhir.</p>
    </header>

    <main class="flex-1 overflow-y-auto p-6 bg-gray-50 space-y-4">
        
        <?php if (empty($riwayat_per_hari)): ?>
            <div class="text-center py-10">
                <p class="text-gray-500">Belum ada riwayat presensi.</p>
            </div>
        <?php else: ?>
            <?php foreach ($riwayat_per_hari as $riwayat): ?>
            <!-- Kartu Riwayat per Hari -->
            <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-200">
                <p class="font-bold text-gray-800"><?php echo date('l, d F Y', strtotime($riwayat['tanggal'])); ?></p>
                <div class="grid grid-cols-2 gap-4 mt-2">
                    <div>
                        <p class="text-sm text-gray-500">Masuk</p>
                        <p class="font-semibold text-green-600"><?php echo $riwayat['jam_masuk'] ? date('H:i', strtotime($riwayat['jam_masuk'])) : '--:--'; ?></p>
                        <?php if($riwayat['catatan_masuk']): ?>
                            <p class="text-xs text-yellow-600 mt-1"><?php echo htmlspecialchars($riwayat['catatan_masuk']); ?></p>
                        <?php endif; ?>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Pulang</p>
                        <p class="font-semibold text-red-600"><?php echo $riwayat['jam_pulang'] ? date('H:i', strtotime($riwayat['jam_pulang'])) : '--:--'; ?></p>
                         <?php if($riwayat['catatan_pulang']): ?>
                            <p class="text-xs text-yellow-600 mt-1"><?php echo htmlspecialchars($riwayat['catatan_pulang']); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>

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
