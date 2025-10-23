<?php
// Selalu mulai sesi di paling atas untuk menangani notifikasi
session_start();

// --- PENJAGA KEAMANAN ---
// Pastikan hanya admin yang login yang bisa mengakses file ini
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // Kirim header 'Forbidden' dan hentikan skrip
    header("HTTP/1.1 403 Forbidden");
    exit('Akses ditolak.');
}

// Gunakan path absolut yang andal untuk memanggil file konfigurasi
require_once '../../config/database.php';

// Ambil aksi dan ID dari URL
$action = $_GET['action'] ?? null;
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (($action === 'setuju' || $action === 'tolak') && $id > 0) {
    
    // Tentukan status baru berdasarkan aksi
    $new_status = ($action === 'setuju') ? 'disetujui' : 'ditolak';

    $conn = connect_db();
    
    // Siapkan query UPDATE yang aman
    $sql = "UPDATE pengajuan SET status_pengajuan = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    
    // Periksa apakah prepare berhasil
    if ($stmt) {
        $stmt->bind_param("si", $new_status, $id);
        
        // Eksekusi query dan periksa hasilnya
        if ($stmt->execute()) {
            // Jika berhasil, siapkan notifikasi sukses
            $_SESSION['notification'] = [
                'type' => 'success',
                'message' => 'Status pengajuan berhasil diperbarui menjadi ' . ucfirst($new_status) . '.'
            ];
        } else {
            // Jika gagal, siapkan notifikasi error
            $_SESSION['notification'] = [
                'type' => 'error',
                'message' => 'Gagal memperbarui status: ' . $stmt->error
            ];
        }
        $stmt->close();
    } else {
        // Jika prepare gagal
         $_SESSION['notification'] = [
            'type' => 'error',
            'message' => 'Gagal menyiapkan query: ' . $conn->error
        ];
    }
    
    $conn->close();

} else {
    // Jika aksi atau ID tidak valid
    $_SESSION['notification'] = [
        'type' => 'error',
        'message' => 'Aksi tidak valid atau ID tidak ditemukan.'
    ];
}

// Selalu kembalikan admin ke halaman data pengajuan
header("Location: data_pengajuan.php");
exit;
?>
