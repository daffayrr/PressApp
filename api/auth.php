<?php
// api/auth.php

// Set header agar output berupa JSON
header('Content-Type: application/json');

// Memasukkan file koneksi database
// Sebaiknya letakkan file config di luar folder publik untuk keamanan
require_once '../config/database.php';

// Fungsi untuk mengirim respons JSON
function json_response($status, $message, $data = null) {
    http_response_code($status);
    echo json_encode(['status' => $status, 'message' => $message, 'data' => $data]);
    exit;
}

// Pastikan request menggunakan metode POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(405, 'Method Not Allowed');
}

// Mengambil data JSON dari body request
$input = json_decode(file_get_contents('php://input'), true);

$username = $input['username'] ?? '';
$password = $input['password'] ?? '';

// Validasi input
if (empty($username) || empty($password)) {
    json_response(400, 'Username dan password tidak boleh kosong.');
}

// Buat koneksi database
$conn = connect_db();

try {
    // Siapkan query untuk mencari user
    $stmt = $conn->prepare("SELECT id, username, password FROM karyawan WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Verifikasi password
        if (password_verify($password, $user['password'])) {
            // Password cocok, login berhasil
            // Di aplikasi nyata, Anda akan membuat token (misal: JWT)
            // Untuk contoh ini, kita kirim pesan sukses saja
            $responseData = [
                'user_id' => $user['id'],
                'username' => $user['username']
            ];
            json_response(200, 'Login berhasil.', $responseData);
        } else {
            // Password tidak cocok
            json_response(401, 'Username atau password salah.');
        }
    } else {
        // User tidak ditemukan
        json_response(401, 'Username atau password salah.');
    }

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    // Tangani error database
    json_response(500, 'Terjadi kesalahan pada server.');
}

?>