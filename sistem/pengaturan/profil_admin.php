<?php
// 1. Panggil file konfigurasi dan header
require_once '../../config/database.php';
$pageTitle = "Manajemen Administrator";
require_once __DIR__ . '/../template/header.php';

// 2. Jalankan logika database
$conn = connect_db();
// Ambil semua data pengguna dengan role 'administrator'
$sql = "SELECT id, nama_lengkap, username FROM karyawan WHERE role = 'administrator' ORDER BY nama_lengkap ASC";
$result = $conn->query($sql);

// 3. Panggil sidebar
require_once __DIR__ . '/../template/sidebar.php';
?>

<!-- Tambahkan SweetAlert2 di <head> (biasanya sudah ada di header.php, tapi ditambahkan di sini untuk memastikan) -->
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- KONTEN UTAMA HALAMAN DIMULAI DI SINI -->
<main class="main-content">
    <div class="content-panel">
        <h1 style="font-size: 1.5rem; font-weight: 700; color: #0d6efd; margin-bottom: 0.5rem;">Dashboard - Manajemen Administrator</h1>
        <hr style="margin-bottom: 1.5rem;">

        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h2 style="font-size: 1.25rem; font-weight: 600;">Data Administrator</h2>
            <a href="tambah_admin.php" style="padding: 0.6rem 1.2rem; background-color: #0d6efd; color: white; text-decoration: none; border-radius: 6px; font-size: 0.9rem;">
                <i class="fa-solid fa-plus" style="margin-right: 8px;"></i> Tambah Admin Baru
            </a>
        </div>
        
        <!-- DIV Notifikasi statis dihapus, akan digantikan oleh Toast -->

        <!-- TABEL HTML SEDERHANA -->
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead style="background-color: #343a40; color: white;">
                    <tr>
                        <th style="padding: 12px 15px;">No</th>
                        <th style="padding: 12px 15px;">Nama Lengkap</th>
                        <th style="padding: 12px 15px;">Username</th>
                        <th style="padding: 12px 15px; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): $no = 1; ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr style="border-bottom: 1px solid #dee2e6;">
                            <td style="padding: 12px 15px;"><?php echo $no++; ?></td>
                            <td style="padding: 12px 15px; font-weight: 500;"><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                            <td style="padding: 12px 15px;"><?php echo htmlspecialchars($row['username']); ?></td>
                            <td style="padding: 12px 15px; text-align: center;">
                                <a href="edit_admin.php?id=<?php echo $row['id']; ?>" style="color: #ffc107; text-decoration: none; margin-right: 15px;">Edit</a>
                                <?php
                                // Tambahkan kondisi: admin tidak bisa menghapus akunnya sendiri
                                if ($_SESSION['admin_id'] != $row['id']):
                                ?>
                                <a href="proses_admin.php?action=hapus&id=<?php echo $row['id']; ?>" style="color: #dc3545; text-decoration: none;" onclick="return confirm('Apakah Anda yakin ingin menghapus admin ini?');">Hapus</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="4" style="padding: 12px 15px; text-align: center; color: #6c757d;">Tidak ada data administrator.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- LOGIKA BARU UNTUK NOTIFIKASI TOAST (diletakkan sebelum footer) -->
<?php if (isset($_SESSION['notification'])): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000, // Notifikasi akan hilang setelah 3 detik
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });

        Toast.fire({
            icon: '<?php echo $_SESSION['notification']['type']; ?>', // 'success' atau 'error'
            title: '<?php echo addslashes($_SESSION['notification']['message']); ?>'
        });
    });
</script>
<?php 
    // Hapus notifikasi dari sesi setelah script-nya dibuat
    unset($_SESSION['notification']); 
?>
<?php endif; ?>

<?php
$conn->close();
require_once __DIR__ . '/../template/footer.php';
?>
