<?php
session_start();
date_default_timezone_set('Asia/Jakarta');
require_once '../config/database.php';

// Redirect ke login jika belum login
if (!isset($_SESSION['karyawan_id'])) {
    header("Location: index.php");
    exit;
}

// Ambil data dari sesi
$karyawan_id = $_SESSION['karyawan_id'];
$karyawan_nama = $_SESSION['karyawan_nama'] ?? 'Karyawan';

// --- LOGIKA UNTUK MENENTUKAN STATUS PRESENSI ---
$conn = connect_db();
$today = date("Y-m-d");
$jam_masuk_hari_ini = '--:--';
$jam_pulang_hari_ini = '--:--';
$status_terakhir = null;

// Ambil semua data presensi hari ini, diurutkan dari yang paling awal
$sql_check = "SELECT tipe, TIME(waktu) as jam FROM presensi WHERE id_karyawan = ? AND DATE(waktu) = ? ORDER BY waktu ASC";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("is", $karyawan_id, $today);
$stmt_check->execute();
$presensi_hari_ini = $stmt_check->get_result()->fetch_all(MYSQLI_ASSOC);

if (!empty($presensi_hari_ini)) {
    foreach ($presensi_hari_ini as $p) {
        if ($p['tipe'] == 'masuk' && $jam_masuk_hari_ini == '--:--') {
            $jam_masuk_hari_ini = date('H:i', strtotime($p['jam']));
        }
        if ($p['tipe'] == 'pulang') {
            $jam_pulang_hari_ini = date('H:i', strtotime($p['jam']));
        }
    }
    $status_terakhir = end($presensi_hari_ini)['tipe'];
}

// Tentukan status tombol berdasarkan presensi terakhir
$button_text = 'CHECK IN';
$button_color = 'bg-indigo-600 hover:bg-indigo-700';
$button_action = 'masuk';
$button_disabled = '';

if ($status_terakhir === 'masuk') {
    $button_text = 'CHECK OUT';
    $button_color = 'bg-red-600 hover:bg-red-700';
    $button_action = 'pulang';
} elseif ($status_terakhir === 'pulang') {
    $button_text = 'SUDAH PRESENSI';
    $button_color = 'bg-gray-400 cursor-not-allowed';
    $button_action = 'selesai';
    $button_disabled = 'disabled';
}

$stmt_check->close();
$conn->close();
$halaman_aktif = 'dashboard';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - PressApp</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style> 
        body { font-family: 'Inter', sans-serif; } 
    </style>
</head>
<body class="bg-gray-100">

<div class="container mx-auto max-w-md h-screen bg-gray-100 flex flex-col">

    <!-- Header Aplikasi -->
    <header class="p-6 flex justify-between items-center flex-shrink-0">
        <div>
            <p class="text-sm text-gray-500">Selamat Datang,</p>
            <h1 class="text-lg font-bold text-gray-800"><?php echo htmlspecialchars($karyawan_nama); ?></h1>
        </div>
        <div class="text-lg font-semibold text-gray-700" id="live-clock">
            --:--:--
        </div>
    </header>

    <!-- Konten Utama -->
    <main class="flex-1 flex flex-col justify-center items-center p-6 text-center">
        
        <p class="text-gray-500" id="tanggal-sekarang">Memuat tanggal...</p>
        
        <button id="btn-presensi" data-action="<?php echo $button_action; ?>" class="w-full max-w-xs mt-6 py-5 text-lg text-white font-bold rounded-2xl shadow-lg focus:outline-none transition-all transform active:scale-95 <?php echo $button_color; ?>" <?php echo $button_disabled; ?>>
            <span id="btn-presensi-text"><?php echo $button_text; ?></span>
            <div id="spinner" class="hidden animate-spin rounded-full h-6 w-6 border-b-2 border-white mx-auto"></div>
        </button>

        <div class="mt-8 w-full max-w-xs grid grid-cols-2 gap-4 text-sm">
            <div class="text-left bg-white p-4 rounded-xl shadow-sm">
                <p class="text-gray-400">Jam Masuk</p>
                <p id="status-masuk" class="font-bold text-green-600 text-base"><?php echo $jam_masuk_hari_ini; ?></p>
            </div>
            <div class="text-right bg-white p-4 rounded-xl shadow-sm">
                <p class="text-gray-400">Jam Pulang</p>
                <p id="status-pulang" class="font-bold text-red-600 text-base"><?php echo $jam_pulang_hari_ini; ?></p>
            </div>
        </div>

    </main>

    <!-- Bottom Navigation Bar -->
    <nav class="bg-white border-t border-gray-200 p-2 rounded-t-2xl flex-shrink-0">
        <div class="flex justify-around">
            <?php $aktif = $halaman_aktif ?? ''; ?>
            <a href="dashboard.php" class="flex flex-col items-center justify-center text-center w-full p-2 rounded-lg <?php echo ($aktif == 'dashboard') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-500 hover:bg-gray-100'; ?>">
                <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                <span class="text-xs font-medium">Home</span>
            </a>
            <a href="riwayat.php" class="flex flex-col items-center justify-center text-center w-full p-2 rounded-lg <?php echo ($aktif == 'riwayat') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-500 hover:bg-gray-100'; ?>">
                <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span class="text-xs font-medium">Riwayat</span>
            </a>
            <a href="pengajuan.php" class="flex flex-col items-center justify-center text-center w-full p-2 rounded-lg <?php echo ($aktif == 'pengajuan') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-500 hover:bg-gray-100'; ?>">
                <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                <span class="text-xs font-medium">Pengajuan</span>
            </a>
            <a href="profil.php" class="flex flex-col items-center justify-center text-center w-full p-2 rounded-lg <?php echo ($aktif == 'profil') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-500 hover:bg-gray-100'; ?>">
                <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                <span class="text-xs font-medium">Profil</span>
            </a>
        </div>
    </nav>
</div>

<!-- Modal Notifikasi -->
<div id="notif-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center z-50 p-4">
    <div class="bg-white p-6 rounded-2xl shadow-xl text-center max-w-sm">
        <div id="notif-icon" class="mx-auto mb-4 w-16 h-16 rounded-full flex items-center justify-center"></div>
        <h3 id="notif-title" class="text-lg font-bold text-gray-800"></h3>
        <p id="notif-message" class="text-sm text-gray-600 mt-2"></p>
        <button id="btn-tutup-modal" class="mt-6 w-full py-2 px-4 bg-gray-200 text-gray-800 font-semibold rounded-lg hover:bg-gray-300">Tutup</button>
    </div>
</div>

<script>
// Fungsi untuk update jam dan tanggal
function updateClock() {
    const now = new Date();
    const elWaktu = document.getElementById('live-clock');
    const elTanggal = document.getElementById('tanggal-sekarang');

    if (elWaktu) {
        elWaktu.textContent = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' }).replace(/\./g, ':');
    }
    if (elTanggal) {
        elTanggal.textContent = now.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
    }
}
setInterval(updateClock, 1000);
updateClock();

// --- Sisa JavaScript (untuk modal dan presensi) ---
const btnPresensi = document.getElementById('btn-presensi');
const btnPresensiText = document.getElementById('btn-presensi-text');
const spinner = document.getElementById('spinner');
const modal = document.getElementById('notif-modal');
const modalIcon = document.getElementById('notif-icon');
const modalTitle = document.getElementById('notif-title');
const modalMessage = document.getElementById('notif-message');
const btnTutupModal = document.getElementById('btn-tutup-modal');

function tampilkanNotifikasi(tipe, judul, pesan) {
    let iconSvg = '';
    let iconBg = '';
    if (tipe === 'sukses') {
        iconSvg = `<svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>`;
        iconBg = 'bg-green-100';
    } else {
        iconSvg = `<svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>`;
        iconBg = 'bg-red-100';
    }
    modalIcon.innerHTML = iconSvg;
    modalIcon.className = `mx-auto mb-4 w-16 h-16 rounded-full flex items-center justify-center ${iconBg}`;
    modalTitle.textContent = judul;
    modalMessage.textContent = pesan;
    modal.classList.remove('hidden');
}

btnTutupModal.addEventListener('click', () => modal.classList.add('hidden'));

function resetButton() {
    btnPresensiText.classList.remove('hidden');
    spinner.classList.add('hidden');
    btnPresensi.disabled = false;
}

btnPresensi.addEventListener('click', () => {
    if (btnPresensi.dataset.action === 'selesai') return;

    btnPresensiText.classList.add('hidden');
    spinner.classList.remove('hidden');
    btnPresensi.disabled = true;

    if (!navigator.geolocation) {
        tampilkanNotifikasi('gagal', 'Error', 'Browser Anda tidak mendukung Geolocation.');
        resetButton();
        return;
    }

    navigator.geolocation.getCurrentPosition(
        async (posisi) => {
            const data = {
                user_id: <?php echo $karyawan_id; ?>,
                tipe: btnPresensi.dataset.action,
                latitude: posisi.coords.latitude,
                longitude: posisi.coords.longitude
            };

            try {
                const response = await fetch('proses_presensi.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                const hasil = await response.json();
                
                if (response.ok) {
                    tampilkanNotifikasi('sukses', 'Berhasil!', hasil.message);
                    setTimeout(() => { window.location.reload(); }, 2000);
                } else {
                    tampilkanNotifikasi('gagal', 'Gagal!', hasil.message);
                    resetButton();
                }
            } catch (error) {
                tampilkanNotifikasi('gagal', 'Error Jaringan', 'Tidak dapat terhubung ke server.');
                resetButton();
            }
        },
        (error) => {
            let judul = 'Error Lokasi';
            let pesan = 'Tidak dapat mengakses lokasi Anda. Pastikan GPS aktif.';
            if (error.code === error.PERMISSION_DENIED) {
                judul = 'Izin Ditolak';
                pesan = 'Anda telah menolak izin lokasi. Harap aktifkan izin lokasi untuk situs ini di pengaturan browser Anda.';
            }
            tampilkanNotifikasi('gagal', judul, pesan);
            resetButton();
        },
        { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
    );
});
</script>

</body>
</html>
