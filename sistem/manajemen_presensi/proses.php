<?php
session_start();
date_default_timezone_set('Asia/Jakarta');
require_once '../config/database.php';

header('Content-Type: application/json');

function json_response($status, $message, $data = null) {
    http_response_code($status);
    echo json_encode(['message' => $message, 'data' => $data]);
    exit;
}

function hitung_jarak($lat1, $lon1, $lat2, $lon2) {
    $earthRadius = 6371000;
    $latFrom = deg2rad($lat1); $lonFrom = deg2rad($lon1);
    $latTo = deg2rad($lat2); $lonTo = deg2rad($lon2);
    $latDelta = $latTo - $latFrom; $lonDelta = $lonTo - $lonFrom;
    $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) + cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
    return $angle * $earthRadius;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(405, 'Method Not Allowed');
}

$input = json_decode(file_get_contents('php://input'), true);
$user_id = $input['user_id'] ?? 0;
$tipe_presensi = $input['tipe'] ?? '';
$latitude = $input['latitude'] ?? 0;
$longitude = $input['longitude'] ?? 0;

if (empty($user_id) || empty($tipe_presensi) || empty($latitude) || empty($longitude)) {
    json_response(400, 'Data tidak lengkap.');
}

$conn = connect_db();

try {
    $stmt_config = $conn->prepare("SELECT latitude_kantor, longitude_kantor, radius_presensi, jam_masuk, jam_pulang, toleransi_terlambat FROM pengaturan WHERE id = 1");
    $stmt_config->execute();
    $config = $stmt_config->get_result()->fetch_assoc();
    if (!$config) {
        json_response(500, 'Pengaturan sistem tidak ditemukan.');
    }

    $jarak = hitung_jarak($latitude, $longitude, $config['latitude_kantor'], $config['longitude_kantor']);
    if ($jarak > $config['radius_presensi']) {
        json_response(403, 'Anda berada di luar jangkauan area presensi.');
    }

    $today = date("Y-m-d");
    $waktu_sekarang = new DateTime();
    $status = 'Tepat Waktu';
    $catatan = null;

    if ($tipe_presensi === 'masuk') {
        $jam_masuk_dt = new DateTime($today . ' ' . $config['jam_masuk']);
        $batas_toleransi = (clone $jam_masuk_dt)->modify('+' . $config['toleransi_terlambat'] . ' minutes');

        if ($waktu_sekarang > $batas_toleransi) {
            $status = 'Terlambat';
            $keterlambatan = $waktu_sekarang->diff($jam_masuk_dt);
            // PERUBAHAN: Format keterlambatan diubah untuk menyertakan detik
            $catatan = 'Terlambat ' . $keterlambatan->format('%h jam %i menit %s detik');
        }
    } elseif ($tipe_presensi === 'pulang') {
        $jam_pulang_dt = new DateTime($today . ' ' . $config['jam_pulang']);
        if ($waktu_sekarang < $jam_pulang_dt) {
            json_response(403, 'Belum waktunya untuk Check Out.');
        }
    }

    $waktu_db = $waktu_sekarang->format("Y-m-d H:i:s");
    $stmt_insert = $conn->prepare("INSERT INTO presensi (id_karyawan, tipe, waktu, latitude, longitude, status, catatan) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt_insert->bind_param("issddss", $user_id, $tipe_presensi, $waktu_db, $latitude, $longitude, $status, $catatan);
    
    if ($stmt_insert->execute()) {
        json_response(201, "Presensi '$tipe_presensi' berhasil direkam.");
    } else {
        json_response(500, 'Gagal menyimpan data presensi.');
    }

} catch (Exception $e) {
    json_response(500, 'Terjadi kesalahan pada server.');
} finally {
    if (isset($conn)) $conn->close();
}
?>
