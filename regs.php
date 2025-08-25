<?php
// File: register_sementara.php
// TUJUAN: Untuk membuat data user awal dengan password yang aman.
// CARA PAKAI: Cukup akses file ini satu kali melalui browser Anda (misal: http://localhost/PressApp/register_sementara.php)

require_once 'config/database.php';

echo "<pre>"; // Untuk tampilan output yang lebih rapi

// Daftar user yang akan dibuat
$users = [
    [
        'nama_lengkap' => 'Admin Utama',
        'username' => 'admin',
        'password' => 'admin123', // Password sementara sebelum di-hash
        'jabatan' => 'System Administrator',
        'role' => 'administrator'
    ],
    [
        'nama_lengkap' => 'Budi Sanjaya',
        'username' => 'budi.staff',
        'password' => 'staff123',
        'jabatan' => 'Staff Keuangan',
        'role' => 'staff'
    ],
    [
        'nama_lengkap' => 'Citra Lestari',
        'username' => 'citra.magang',
        'password' => 'magang123',
        'jabatan' => 'Intern Marketing',
        'role' => 'magang'
    ]
];

// Buat koneksi database
$conn = connect_db();

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

echo "Memulai proses registrasi sementara...\n\n";

// Loop untuk setiap user
foreach ($users as $user) {
    $nama_lengkap = $user['nama_lengkap'];
    $username = $user['username'];
    // HASH PASSWORD! Ini adalah langkah keamanan yang sangat penting.
    $hashed_password = password_hash($user['password'], PASSWORD_BCRYPT);
    $jabatan = $user['jabatan'];
    $role = $user['role'];

    // Cek apakah username sudah ada
    $stmt_check = $conn->prepare("SELECT id FROM karyawan WHERE username = ?");
    $stmt_check->bind_param("s", $username);
    $stmt_check->execute();
    $result = $stmt_check->get_result();

    if ($result->num_rows > 0) {
        echo "--> GAGAL: Username '$username' sudah terdaftar. Dilewati.\n";
    } else {
        // Jika belum ada, masukkan data baru
        $stmt_insert = $conn->prepare("INSERT INTO karyawan (nama_lengkap, username, password, jabatan, role) VALUES (?, ?, ?, ?, ?)");
        $stmt_insert->bind_param("sssss", $nama_lengkap, $username, $hashed_password, $jabatan, $role);

        if ($stmt_insert->execute()) {
            echo "--> SUKSES: User '$nama_lengkap' dengan username '$username' berhasil dibuat.\n";
        } else {
            echo "--> GAGAL: Terjadi error saat membuat user '$nama_lengkap'.\n";
        }
        $stmt_insert->close();
    }
    $stmt_check->close();
}

echo "\nProses selesai.";
echo "</pre>";

$conn->close();

?>