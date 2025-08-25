<?php
require_once __DIR__ . '/../../config/database.php';

// Fungsi untuk mengatur notifikasi
function set_notification($message, $type) {
    $_SESSION['notification'] = [
        'message' => $message,
        'type' => $type
    ];
}

$action = $_GET['action'] ?? '';

if ($action === 'reset') {
    $tanggal = $_GET['tanggal'] ?? '';

    if (empty($tanggal)) {
        set_notification("Tanggal tidak valid untuk direset.", "error");
        header("Location: data_presensi.php");
        exit;
    }

    $conn = connect_db();
    $sql = "DELETE FROM presensi WHERE DATE(waktu) = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $tanggal);

    if ($stmt->execute()) {
        $affected_rows = $stmt->affected_rows;
        set_notification("$affected_rows data presensi pada tanggal $tanggal berhasil direset.", "success");
    } else {
        set_notification("Gagal mereset data presensi.", "error");
    }

    $stmt->close();
    $conn->close();

    // Redirect kembali ke halaman data presensi dengan tanggal yang sama
    header("Location: data_presensi.php?tanggal=" . urlencode($tanggal));
    exit;

} else {
    // Aksi lain bisa ditambahkan di sini jika perlu
    header("Location: data_presensi.php");
    exit;
}
?>
