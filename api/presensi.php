<?php
// api/presensi.php

header('Content-Type: application/json');
require_once '../config/database.php';

// Fungsi untuk mengirim respons JSON
function json_response($status, $message, $data = null) {
    http_response_code($status);
    echo json_encode(['status' => $status, 'message' => $message, 'data' => $data]);
    exit;
}

/**
 * Fungsi untuk menghitung jarak antara dua titik koordinat geografis.
 * Menggunakan Rumus Haversine.
 * @return float Jarak dalam meter.
 */
function hitung_jarak($lat1, $lon1, $lat2, $lon2) {
    $earthRadius = 6371000; // Radius bumi dalam meter
    $latFrom = deg2rad($lat1);
    $lonFrom = deg2rad($lon1);
    $latTo = deg2rad($lat2);
    $lonTo = deg2rad($lon2);

    $latDelta = $latTo - $latFrom;
    $lonDelta = $lonTo - $lonFrom;

    $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
        cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
    return $angle * $earthRadius;
}

// Pastikan request menggunakan metode POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(405, 'Method Not Allowed');
}

// Mengambil data JSON dari body request
$input = json_decode(file_get_contents('php://input'), true);

$user_id = $input['user_id'] ?? 0;
$tipe = $input['tipe'] ?? ''; // 'masuk' atau 'pulang'
$latitude = $input['latitude'] ?? 0;
$longitude = $input['longitude'] ?? 0;

// Validasi input
if (empty($user_id) || empty($tipe) || empty($latitude) || empty($longitude)) {
    json_response(400, 'Data tidak lengkap.');
}
if ($tipe !== 'masuk' && $tipe !== 'pulang') {
    json_response(400, 'Tipe presensi tidak valid.');
}

// Buat koneksi database
$conn = connect_db();

try {
    // 1. Ambil data pengaturan lokasi kantor
    // Anggap kita punya tabel 'pengaturan' dengan id=1 untuk lokasi utama
    $stmt_config = $conn->prepare("SELECT latitude_kantor, longitude_kantor, radius_presensi FROM pengaturan WHERE id = 1");
    $stmt_config->execute();
    $config = $stmt_config->get_result()->fetch_assoc();
    $stmt_config->close();

    if (!$config) {
        json_response(500, 'Pengaturan lokasi kantor tidak ditemukan.');
    }

    $lat_kantor = $config['latitude_kantor'];
    $lon_kantor = $config['longitude_kantor'];
    $radius_valid = $config['radius_presensi']; // dalam meter

    // 2. Hitung jarak
    $jarak = hitung_jarak($latitude, $longitude, $lat_kantor, $lon_kantor);

    // 3. Cek apakah jarak berada dalam radius yang valid
    if ($jarak > $radius_valid) {
        json_response(403, 'Anda berada di luar jangkauan area presensi. Jarak Anda: ' . round($jarak) . ' meter.');
    }

    // 4. Cek apakah karyawan sudah absen untuk tipe yang sama hari ini
    $today = date("Y-m-d");
    $stmt_check = $conn->prepare("SELECT id FROM presensi WHERE id_karyawan = ? AND tipe = ? AND DATE(waktu) = ?");
    $stmt_check->bind_param("iss", $user_id, $tipe, $today);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        json_response(409, "Anda sudah melakukan presensi '$tipe' hari ini.");
    }
    $stmt_check->close();

    // 5. Jika semua valid, masukkan data presensi ke database
    $waktu_sekarang = date("Y-m-d H:i:s");
    $status = 'Tepat Waktu'; // Logika untuk status (misal: Terlambat) bisa ditambahkan di sini

    $stmt_insert = $conn->prepare("INSERT INTO presensi (id_karyawan, tipe, waktu, latitude, longitude, status) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt_insert->bind_param("issdds", $user_id, $tipe, $waktu_sekarang, $latitude, $longitude, $status);
    
    if ($stmt_insert->execute()) {
        json_response(201, "Presensi '$tipe' berhasil direkam.");
    } else {
        json_response(500, 'Gagal menyimpan data presensi.');
    }

    $stmt_insert->close();
    $conn->close();

} catch (Exception $e) {
    json_response(500, 'Terjadi kesalahan pada server: ' . $e->getMessage());
}
?>