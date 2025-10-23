<?php
// Selalu mulai sesi di paling atas
session_start();
// Atur zona waktu agar konsisten
date_default_timezone_set('Asia/Jakarta');

// Gunakan path absolut yang andal untuk memanggil file konfigurasi
require_once __DIR__ . '/../config/database.php';

// Fungsi helper untuk mengirim respons dalam format JSON
function json_response($status, $message, $data = null) {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode(['message' => $message, 'data' => $data]);
    exit;
}

// --- PERBAIKAN: Tangani akses langsung melalui browser (GET) ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Jika diakses via GET, berikan pesan HTML yang jelas, bukan JSON.
    if (isset($_SERVER['HTTP_REFERER']) === false) {
        http_response_code(403);
        echo "<!DOCTYPE html><html><head><title>Akses Ditolak</title>";
        echo "<style>body { font-family: sans-serif; text-align: center; padding: 50px; }</style></head>";
        echo "<body><h1>Akses Ditolak</h1>";
        echo "<p>Halaman ini tidak dapat diakses secara langsung.</p></body></html>";
        exit;
    }
    // Untuk metode lain yang salah (PUT, DELETE, dll), tetap kirim JSON.
    json_response(405, 'Metode tidak diizinkan.');
}

// --- PENJAGA KEAMANAN ---
// Pastikan hanya karyawan yang sudah login yang bisa mengakses skrip ini
if (!isset($_SESSION['karyawan_id'])) {
    json_response(401, 'Akses ditolak. Silakan login terlebih dahulu.');
}

// Ambil ID karyawan dari sesi
$karyawan_id = $_SESSION['karyawan_id'];

// --- VALIDASI INPUT ---
$tipe_pengajuan = $_POST['tipe_pengajuan'] ?? '';
$tanggal_mulai = $_POST['tanggal_mulai'] ?? '';
$tanggal_selesai = $_POST['tanggal_selesai'] ?? '';
$keterangan = trim($_POST['keterangan'] ?? '');

$allowed_types = ['izin', 'pulang_awal', 'sakit', 'cuti'];
if (!in_array($tipe_pengajuan, $allowed_types)) {
    json_response(400, 'Tipe pengajuan tidak valid.');
}
if (empty($tanggal_mulai) || empty($keterangan)) {
    json_response(400, 'Semua kolom wajib diisi.');
}

// Jika tipe bukan 'pulang_awal', maka tanggal selesai wajib diisi
if ($tipe_pengajuan !== 'pulang_awal' && empty($tanggal_selesai)) {
    json_response(400, 'Tanggal selesai wajib diisi untuk tipe pengajuan ini.');
}

// Untuk 'pulang_awal', samakan tanggal selesai dengan tanggal mulai
if ($tipe_pengajuan === 'pulang_awal') {
    $tanggal_selesai = $tanggal_mulai;
}

// --- PROSES UPLOAD FILE BUKTI (JIKA ADA) ---
$nama_file_bukti = null;
if (isset($_FILES['bukti']) && $_FILES['bukti']['error'] === UPLOAD_ERR_OK) {
    
    // Tentukan path folder upload yang benar dari lokasi file ini
    $upload_dir = __DIR__ . '/../uploads/bukti/';

    // Buat folder jika belum ada
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $file = $_FILES['bukti'];
    $file_info = pathinfo($file['name']);
    $file_extension = strtolower($file_info['extension']);
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'pdf'];

    if (!in_array($file_extension, $allowed_extensions)) {
        json_response(400, 'Format file bukti tidak diizinkan. Hanya JPG, PNG, atau PDF.');
    }

    if ($file['size'] > 2097152) { // Batas 2 MB
        json_response(400, 'Ukuran file bukti tidak boleh lebih dari 2 MB.');
    }

    // Buat nama file yang unik untuk mencegah penimpaan file
    $nama_file_bukti = 'bukti_' . $karyawan_id . '_' . time() . '.' . $file_extension;
    $target_file = $upload_dir . $nama_file_bukti;

    if (!move_uploaded_file($file['tmp_name'], $target_file)) {
        json_response(500, 'Gagal mengunggah file bukti.');
    }
}

// --- SIMPAN DATA KE DATABASE ---
$conn = connect_db();
$sql = "INSERT INTO pengajuan (id_karyawan, tipe_pengajuan, tanggal_mulai, tanggal_selesai, keterangan, bukti, status_pengajuan) 
        VALUES (?, ?, ?, ?, ?, ?, 'menunggu')";
        
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    json_response(500, 'Gagal menyiapkan statement database.');
}

$stmt->bind_param("isssss", $karyawan_id, $tipe_pengajuan, $tanggal_mulai, $tanggal_selesai, $keterangan, $nama_file_bukti);

if ($stmt->execute()) {
    json_response(200, 'Pengajuan Anda telah berhasil dikirim.');
} else {
    json_response(500, 'Gagal menyimpan data pengajuan ke database.');
}

$stmt->close();
$conn->close();
?>

