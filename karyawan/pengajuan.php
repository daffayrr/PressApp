<?php
// Selalu mulai sesi di paling atas
session_start();
// Atur zona waktu agar konsisten
date_default_timezone_set('Asia/Jakarta');

// Gunakan path absolut yang andal untuk memanggil file konfigurasi
require_once __DIR__ . '/../config/database.php';

// --- PENJAGA KEAMANAN ---
// Redirect ke halaman login jika karyawan belum login
if (!isset($_SESSION['karyawan_id'])) {
    header("Location: index.php");
    exit;
}

// Ambil data penting dari sesi
$karyawan_id = $_SESSION['karyawan_id'];
$halaman_aktif = 'pengajuan'; // Untuk menandai menu aktif di navigasi bawah

// --- LOGIKA PENGAMBILAN DATA ---
// Ambil semua riwayat pengajuan untuk karyawan yang sedang login
$conn = connect_db();
$sql = "SELECT id, tipe_pengajuan, tanggal_mulai, tanggal_selesai, keterangan, status_pengajuan, bukti 
        FROM pengajuan 
        WHERE id_karyawan = ? 
        ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $karyawan_id);
$stmt->execute();
$result = $stmt->get_result();
$riwayat_pengajuan = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Pengajuan - PressApp</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-100">

<div class="container mx-auto max-w-md h-screen bg-white flex flex-col shadow-lg">

    <!-- Header Aplikasi -->
    <header class="bg-white p-4 border-b border-gray-200 flex-shrink-0">
        <h1 class="text-xl font-bold text-gray-800">Buat Pengajuan</h1>
    </header>

    <!-- Konten Utama (Scrollable) -->
    <main class="flex-1 overflow-y-auto p-6 space-y-6">
        
        <!-- Form Pengajuan -->
        <div class="bg-white p-6 rounded-lg shadow-sm border">
            <form id="form-pengajuan" enctype="multipart/form-data" class="space-y-4">
                <div>
                    <label for="tipe_pengajuan" class="block text-sm font-medium text-gray-700">Tipe Pengajuan</label>
                    <select id="tipe_pengajuan" name="tipe_pengajuan" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="izin">Izin Tidak Masuk</option>
                        <option value="pulang_awal">Izin Pulang Awal</option>
                        <option value="sakit">Sakit</option>
                        <option value="cuti">Cuti</option>
                    </select>
                </div>
                <div>
                    <label for="tanggal_mulai" class="block text-sm font-medium text-gray-700">Tanggal Mulai</label>
                    <input type="date" id="tanggal_mulai" name="tanggal_mulai" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500" required>
                </div>
                <div id="kolom_tanggal_selesai">
                    <label for="tanggal_selesai" class="block text-sm font-medium text-gray-700">Tanggal Selesai</label>
                    <input type="date" id="tanggal_selesai" name="tanggal_selesai" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label for="keterangan" class="block text-sm font-medium text-gray-700">Keterangan</label>
                    <textarea id="keterangan" name="keterangan" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm p-2 focus:ring-blue-500 focus:border-blue-500" required></textarea>
                </div>
                <div>
                    <label for="bukti" class="block text-sm font-medium text-gray-700">Upload Bukti (Opsional, maks 2MB)</label>
                    <input type="file" id="bukti" name="bukti" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                </div>
                <button type="submit" id="submit-button" class="w-full py-3 px-4 bg-blue-600 text-white font-semibold rounded-lg shadow-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all transform active:scale-95">
                    Kirim Pengajuan
                </button>
            </form>
        </div>

        <!-- Riwayat Pengajuan -->
        <div class="bg-white p-6 rounded-lg shadow-sm border">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-bold text-gray-800">Riwayat Pengajuan Anda</h2>
                <button onclick="window.location.reload()" class="text-sm text-blue-600 hover:text-blue-800 font-medium flex items-center">
                    <svg class="w-5 h-5 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h5M20 20v-5h-5M20 4l-5 5M4 20l5-5"></path></svg>
                    Perbarui
                </button>
            </div>
            <div id="riwayat-list" class="space-y-3">
                 <?php if (empty($riwayat_pengajuan)): ?>
                    <p class="text-center text-gray-500 py-4">Belum ada riwayat pengajuan.</p>
                <?php else: ?>
                    <?php foreach ($riwayat_pengajuan as $pengajuan): ?>
                        <div class="border rounded-md p-3">
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="font-bold text-gray-800"><?= ucfirst(str_replace('_', ' ', $pengajuan['tipe_pengajuan'])); ?></p>
                                    <p class="text-sm text-gray-600">
                                        <?= date('d M Y', strtotime($pengajuan['tanggal_mulai'])); ?>
                                        <?php if($pengajuan['tanggal_mulai'] != $pengajuan['tanggal_selesai']): ?>
                                            - <?= date('d M Y', strtotime($pengajuan['tanggal_selesai'])); ?>
                                        <?php endif; ?>
                                    </p>
                                </div>
                                <span class="text-xs font-medium py-1 px-3 rounded-full text-white
                                    <?= ($pengajuan['status_pengajuan'] == 'menunggu') ? 'bg-yellow-500' : '' ?>
                                    <?= ($pengajuan['status_pengajuan'] == 'disetujui') ? 'bg-green-500' : '' ?>
                                    <?= ($pengajuan['status_pengajuan'] == 'ditolak') ? 'bg-red-500' : '' ?>
                                ">
                                    <?= ucfirst($pengajuan['status_pengajuan']); ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

    </main>
    
    <!-- Bottom Navigation -->
    <?php require_once __DIR__ . '/template/bottom_nav.php'; ?>

</div>

<!-- Modal Notifikasi -->
<div id="modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
    <div class="bg-white rounded-lg p-6 shadow-xl text-center w-full max-w-sm">
        <h3 id="modal-title" class="text-lg font-bold"></h3>
        <p id="modal-message" class="text-gray-600 my-4"></p>
        <button id="modal-close" class="mt-4 px-6 py-2 bg-blue-600 text-white rounded-lg w-full">Tutup</button>
    </div>
</div>

<script>
    const formPengajuan = document.getElementById('form-pengajuan');
    const submitButton = document.getElementById('submit-button');
    const modal = document.getElementById('modal');
    const modalTitle = document.getElementById('modal-title');
    const modalMessage = document.getElementById('modal-message');
    const modalClose = document.getElementById('modal-close');

    function tampilkanModal(judul, pesan) {
        modalTitle.textContent = judul;
        modalMessage.textContent = pesan;
        modal.classList.remove('hidden');
    }

    modalClose.addEventListener('click', () => {
        modal.classList.add('hidden');
    });

    formPengajuan.addEventListener('submit', async function(e) {
        e.preventDefault();
        submitButton.disabled = true;
        submitButton.textContent = 'Mengirim...';

        const formData = new FormData(this);

        try {
            const response = await fetch('proses_pengajuan.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (response.ok) {
                tampilkanModal('Sukses', result.message);
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                tampilkanModal('Error', result.message);
                submitButton.disabled = false;
                submitButton.textContent = 'Kirim Pengajuan';
            }
        } catch (error) {
            tampilkanModal('Error Jaringan', 'Tidak dapat terhubung ke server. Periksa koneksi Anda.');
            submitButton.disabled = false;
            submitButton.textContent = 'Kirim Pengajuan';
        }
    });

    // Logika untuk menyembunyikan/menampilkan tanggal selesai
    const tipeSelect = document.getElementById('tipe_pengajuan');
    const kolomTanggalSelesai = document.getElementById('kolom_tanggal_selesai');
    
    tipeSelect.addEventListener('change', function() {
        if (this.value === 'pulang_awal') {
            kolomTanggalSelesai.style.display = 'none';
        } else {
            kolomTanggalSelesai.style.display = 'block';
        }
    });

    // Panggil sekali saat halaman dimuat untuk menyesuaikan tampilan awal
    tipeSelect.dispatchEvent(new Event('change'));
</script>

</body>
</html>
