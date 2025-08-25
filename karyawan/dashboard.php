<?php
session_start();
date_default_timezone_set('Asia/Jakarta');
require_once '../config/database.php';

if (!isset($_SESSION['karyawan_id'])) {
    header("Location: index.php");
    exit;
}

$karyawan_id = $_SESSION['karyawan_id'];
$karyawan_nama = $_SESSION['karyawan_nama'] ?? 'Karyawan';

$conn = connect_db();
$today = date("Y-m-d");
$jam_masuk_hari_ini = '--:--';
$jam_pulang_hari_ini = '--:--';
$status_terakhir = null;
$id_presensi_masuk = null;

// Ambil pengaturan
$sql_pengaturan = "SELECT jam_masuk, jam_pulang, toleransi_terlambat FROM pengaturan WHERE id = 1";
$pengaturan = $conn->query($sql_pengaturan)->fetch_assoc();
$jam_kerja_mulai = $pengaturan['jam_masuk'];
$jam_kerja_selesai = $pengaturan['jam_pulang'];
$toleransi_terlambat = $pengaturan['toleransi_terlambat']; // menit

// Ambil semua data presensi hari ini
$sql_check = "SELECT id, tipe, TIME(waktu) as jam FROM presensi WHERE id_karyawan = ? AND DATE(waktu) = ? ORDER BY waktu ASC";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("is", $karyawan_id, $today);
$stmt_check->execute();
$presensi_hari_ini = $stmt_check->get_result()->fetch_all(MYSQLI_ASSOC);

if (!empty($presensi_hari_ini)) {
    foreach ($presensi_hari_ini as $p) {
        if ($p['tipe'] == 'masuk') {
            if ($jam_masuk_hari_ini == '--:--') {
                $jam_masuk_hari_ini = date('H:i', strtotime($p['jam']));
                $id_presensi_masuk = $p['id'];
            }
        }
        if ($p['tipe'] == 'pulang') {
            $jam_pulang_hari_ini = date('H:i', strtotime($p['jam']));
        }
    }
    $status_terakhir = end($presensi_hari_ini)['tipe'];
}

// --- LOGIKA AUTO CHECKOUT ---
$waktu_sekarang_dt = new DateTime();
$jam_pulang_dt = new DateTime($today . ' ' . $jam_kerja_selesai);
$batas_auto_checkout = (clone $jam_pulang_dt)->modify('+1 hour');

if ($status_terakhir === 'masuk' && $waktu_sekarang_dt > $batas_auto_checkout) {
    $sql_auto_checkout = "INSERT INTO presensi (id_karyawan, tipe, waktu, status, catatan) VALUES (?, 'pulang', ?, 'Tepat Waktu', 'Auto Checkout (Lupa Absen)')";
    $waktu_auto = $batas_auto_checkout->format('Y-m-d H:i:s');
    $stmt_auto = $conn->prepare($sql_auto_checkout);
    $stmt_auto->bind_param("is", $karyawan_id, $waktu_auto);
    $stmt_auto->execute();
    // Refresh halaman untuk update tampilan
    header("Location: dashboard.php");
    exit;
}
// --- AKHIR LOGIKA AUTO CHECKOUT ---

// Tentukan status tombol
$button_text = 'CHECK IN';
$button_color = 'bg-blue-600 hover:bg-blue-700';
$button_action = 'masuk';
$button_disabled = '';

if ($status_terakhir === 'masuk') {
    $button_text = 'CHECK OUT';
    $button_color = 'bg-red-600 hover:bg-red-700';
    $button_action = 'pulang';
    // Nonaktifkan tombol checkout jika belum waktunya
    if ($waktu_sekarang_dt < $jam_pulang_dt) {
        $button_text = 'BELUM WAKTUNYA PULANG';
        $button_color = 'bg-gray-400 cursor-not-allowed';
        $button_disabled = 'disabled';
    }
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
<body class="bg-gray-200">

<div class="container mx-auto max-w-md h-screen bg-white flex flex-col shadow-lg">

    <!-- Header Aplikasi (Fixed Height) -->
    <header class="bg-white p-4 border-b border-gray-200">
        <h1 class="text-xl font-bold text-gray-800">Selamat Datang,</h1>
        <p class="text-gray-600"><?php echo htmlspecialchars($karyawan_nama); ?></p>
    </header>

    <!-- Konten Utama (Scrollable) -->
    <main class="flex-1 overflow-y-auto p-6 bg-gray-50 space-y-6">
        
        <!-- Kartu Presensi Utama -->
        <div class="bg-white p-6 rounded-2xl shadow-sm text-center">
            <p class="text-gray-500" id="tanggal-sekarang">Memuat tanggal...</p>
            <h2 id="waktu-sekarang" class="text-4xl font-bold text-gray-800 my-2">--:--:--</h2>
            
            <button id="btn-presensi" data-action="<?php echo $button_action; ?>" class="w-full mt-4 py-4 px-6 text-white font-semibold rounded-xl shadow-md focus:outline-none transition-all transform active:scale-95 <?php echo $button_color; ?>" <?php echo $button_disabled; ?>>
                <span id="btn-presensi-text"><?php echo $button_text; ?></span>
                <div id="spinner" class="hidden animate-spin rounded-full h-5 w-5 border-b-2 border-white mx-auto"></div>
            </button>
        </div>

        <!-- Kartu Status Kehadiran -->
        <div class="grid grid-cols-2 gap-4">
            <div class="bg-white p-4 rounded-2xl shadow-sm">
                <p class="text-sm text-gray-500">Jam Masuk</p>
                <p id="status-masuk" class="text-xl font-bold text-green-600"><?php echo $jam_masuk_hari_ini; ?></p>
            </div>
            <div class="bg-white p-4 rounded-2xl shadow-sm">
                <p class="text-sm text-gray-500">Jam Pulang</p>
                <p id="status-pulang" class="text-xl font-bold text-red-600"><?php echo $jam_pulang_hari_ini; ?></p>
            </div>
        </div>
        
        <!-- Konten tambahan untuk demo scroll -->
        <div class="bg-white p-4 rounded-2xl shadow-sm">
            <h3 class="font-bold text-gray-800">Pengumuman</h3>
            <p class="text-sm text-gray-600 mt-2">Diharapkan seluruh karyawan untuk mengikuti rapat koordinasi pada hari Senin pukul 10:00 WIB. Terima kasih.</p>
        </div>
        <div class="bg-white p-4 rounded-2xl shadow-sm">
            <h3 class="font-bold text-gray-800">Info Tambahan</h3>
            <p class="text-sm text-gray-600 mt-2">Jangan lupa untuk selalu menjaga kebersihan area kerja masing-masing. Lingkungan kerja yang bersih meningkatkan produktivitas.</p>
        </div>

    </main>

    <!-- Bottom Navigation Bar (Fixed Height) -->
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
    const elWaktu = document.getElementById('waktu-sekarang');
    const elTanggal = document.getElementById('tanggal-sekarang');

    if (elWaktu) {
        elWaktu.textContent = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
    }
    if (elTanggal) {
        elTanggal.textContent = now.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
    }
}
setInterval(updateClock, 1000);
updateClock();

const btnPresensi = document.getElementById('btn-presensi');
const btnPresensiText = document.getElementById('btn-presensi-text');
const spinner = document.getElementById('spinner');

// Logika Modal
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
    } else { // Gagal
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

// Logika Presensi
btnPresensi.addEventListener('click', () => {
    btnPresensiText.classList.add('hidden');
    spinner.classList.remove('hidden');
    btnPresensi.disabled = true;

    if (!navigator.geolocation) {
        tampilkanNotifikasi('gagal', 'Error', 'Browser Anda tidak mendukung Geolocation.');
        resetButton();
        return;
    }

    // Baris ini akan memicu popup permintaan izin lokasi dari browser
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
        {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 0
        }
    );

    // Cek keterlambatan di frontend untuk warning
    const jamMasukKantor = '<?php echo $jam_kerja_mulai; ?>';
    const waktuSekarang = new Date().toLocaleTimeString('en-GB'); // format HH:mm:ss
    const aksi = btnPresensi.dataset.action;

    if (aksi === 'masuk' && waktuSekarang > jamMasukKantor) {
        if (!confirm("Anda terlambat. Lanjutkan presensi?")) {
            resetButton();
            return;
        }
    }
});

function resetButton() {
    btnPresensiText.classList.remove('hidden');
    spinner.classList.add('hidden');
    btnPresensi.disabled = false;
}
</script>

</body>
</html>
