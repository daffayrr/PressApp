<?php
// 1. Panggil file konfigurasi dan header
require_once '../../config/database.php';
$pageTitle = "Tambah Karyawan";
require_once __DIR__ . '/../template/header.php';

// 2. Panggil sidebar
require_once __DIR__ . '/../template/sidebar.php';
?>

<!-- KONTEN UTAMA HALAMAN TAMBAH -->
<main class="main-content">
    <div class="content-panel">
        <h1 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 1.5rem;">Tambah Karyawan Baru</h1>
        
        <form action="proses.php" method="POST" style="max-width: 600px;">
            <input type="hidden" name="action" value="tambah">
            
            <div style="display: grid; grid-template-columns: 1fr; gap: 1.25rem;">
                <div>
                    <label for="nama_lengkap" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Nama Lengkap</label>
                    <input type="text" id="nama_lengkap" name="nama_lengkap" style="width: 100%; padding: 0.75rem; border: 1px solid #dee2e6; border-radius: 6px;" required>
                </div>
                <div>
                    <label for="username" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Username</label>
                    <input type="text" id="username" name="username" style="width: 100%; padding: 0.75rem; border: 1px solid #dee2e6; border-radius: 6px;" required>
                </div>
                <div>
                    <label for="password" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Password</label>
                    <input type="password" id="password" name="password" style="width: 100%; padding: 0.75rem; border: 1px solid #dee2e6; border-radius: 6px;" required>
                </div>
                <div>
                    <label for="jabatan" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Jabatan</label>
                    <input type="text" id="jabatan" name="jabatan" style="width: 100%; padding: 0.75rem; border: 1px solid #dee2e6; border-radius: 6px;">
                </div>
                <div>
                    <label for="role" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Role</label>
                    <select id="role" name="role" style="width: 100%; padding: 0.75rem; border: 1px solid #dee2e6; border-radius: 6px; background-color: white;" required>
                        <option value="staff">Staff</option>
                        <option value="magang">Magang</option>
                    </select>
                </div>
            </div>
            <div style="margin-top: 2rem; display: flex; gap: 1rem;">
                <button type="submit" style="padding: 0.75rem 1.5rem; background-color: #0d6efd; color: white; border: none; border-radius: 6px; cursor: pointer;">Simpan Data</button>
                <a href="data_karyawan.php" style="padding: 0.75rem 1.5rem; background-color: #6c757d; color: white; text-decoration: none; border-radius: 6px;">Batal</a>
            </div>
        </form>
    </div>
</main>

<?php
// Panggil footer
require_once __DIR__ . '/../template/footer.php';
?>
