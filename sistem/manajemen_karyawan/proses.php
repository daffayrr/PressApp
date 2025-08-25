<?php
// LANGKAH 1: Mulai sesi agar bisa menyimpan pesan notifikasi.
session_start();

// Panggil file konfigurasi database
require_once '../../config/database.php';

// Fungsi untuk mengatur notifikasi di dalam sesi
function set_notification($message, $type) {
    $_SESSION['notification'] = [
        'message' => $message,
        'type' => $type // 'success' atau 'error'
    ];
}

// Menentukan aksi berdasarkan parameter GET atau POST
$action = $_REQUEST['action'] ?? '';

$conn = connect_db();

switch ($action) {
    case 'tambah':
        $nama_lengkap = $_POST['nama_lengkap'];
        $username = $_POST['username'];
        $password = $_POST['password'];
        $jabatan = $_POST['jabatan'];
        $role = $_POST['role'];

        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        $sql = "INSERT INTO karyawan (nama_lengkap, username, password, jabatan, role) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $nama_lengkap, $username, $hashed_password, $jabatan, $role);

        if ($stmt->execute()) {
            set_notification("Karyawan baru berhasil ditambahkan.", "success");
        } else {
            set_notification("Gagal menambahkan karyawan: " . $stmt->error, "error");
        }
        $stmt->close();
        break;

    case 'edit':
        $id = $_POST['id'];
        $nama_lengkap = $_POST['nama_lengkap'];
        $username = $_POST['username'];
        $password = $_POST['password'];
        $jabatan = $_POST['jabatan'];
        $role = $_POST['role'];

        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $sql = "UPDATE karyawan SET nama_lengkap=?, username=?, password=?, jabatan=?, role=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssi", $nama_lengkap, $username, $hashed_password, $jabatan, $role, $id);
        } else {
            $sql = "UPDATE karyawan SET nama_lengkap=?, username=?, jabatan=?, role=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssi", $nama_lengkap, $username, $jabatan, $role, $id);
        }

        if ($stmt->execute()) {
            set_notification("Data karyawan berhasil diperbarui.", "success");
        } else {
            set_notification("Gagal memperbarui data: " . $stmt->error, "error");
        }
        $stmt->close();
        break;

    case 'hapus':
        $id = $_GET['id'];
        $sql = "DELETE FROM karyawan WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            set_notification("Karyawan berhasil dihapus.", "success");
        } else {
            set_notification("Gagal menghapus karyawan.", "error");
        }
        $stmt->close();
        break;

    default:
        set_notification("Aksi tidak dikenal.", "error");
        break;
}

$conn->close();
// Redirect kembali ke halaman utama
header("Location: data_karyawan.php");
exit;
?>
