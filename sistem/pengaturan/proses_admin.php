<?php
session_start();
require_once '../../config/database.php';

function set_notification($message, $type) {
    $_SESSION['notification'] = ['message' => $message, 'type' => $type];
}

$action = $_REQUEST['action'] ?? '';
$conn = connect_db();

switch ($action) {
    case 'tambah':
        $nama_lengkap = $_POST['nama_lengkap'];
        $username = $_POST['username'];
        $password = $_POST['password'];
        $role = 'administrator'; // Role di-set secara otomatis
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        $sql = "INSERT INTO karyawan (nama_lengkap, username, password, role) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $nama_lengkap, $username, $hashed_password, $role);
        if ($stmt->execute()) {
            set_notification("Admin baru berhasil ditambahkan.", "success");
        } else {
            set_notification("Gagal menambahkan admin: " . $stmt->error, "error");
        }
        $stmt->close();
        break;

    case 'edit':
        $id = $_POST['id'];
        $nama_lengkap = $_POST['nama_lengkap'];
        $username = $_POST['username'];
        $password = $_POST['password'];

        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $sql = "UPDATE karyawan SET nama_lengkap=?, username=?, password=? WHERE id=? AND role='administrator'";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssi", $nama_lengkap, $username, $hashed_password, $id);
        } else {
            $sql = "UPDATE karyawan SET nama_lengkap=?, username=? WHERE id=? AND role='administrator'";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssi", $nama_lengkap, $username, $id);
        }
        if ($stmt->execute()) {
            set_notification("Data admin berhasil diperbarui.", "success");
        } else {
            set_notification("Gagal memperbarui data: " . $stmt->error, "error");
        }
        $stmt->close();
        break;

    case 'hapus':
        $id = $_GET['id'];
        // Tambahkan proteksi agar admin tidak bisa menghapus dirinya sendiri
        if ($id == $_SESSION['admin_id']) {
            set_notification("Anda tidak dapat menghapus akun Anda sendiri.", "error");
        } else {
            $sql = "DELETE FROM karyawan WHERE id = ? AND role='administrator'";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                set_notification("Admin berhasil dihapus.", "success");
            } else {
                set_notification("Gagal menghapus admin.", "error");
            }
            $stmt->close();
        }
        break;
}

$conn->close();
header("Location: profil_admin.php");
exit;
?>
