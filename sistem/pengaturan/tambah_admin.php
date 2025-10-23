<?php
require_once '../../config/database.php';
$pageTitle = "Tambah Admin Baru";
require_once __DIR__ . '/../template/header.php';
require_once __DIR__ . '/../template/sidebar.php';
?>

<main class="main-content">
    <div class="content-panel">
        <h1 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 1.5rem;">Tambah Administrator Baru</h1>
        <form action="proses_admin.php" method="POST" style="max-width: 600px;">
            <input type="hidden" name="action" value="tambah">
            <div style="display: grid; grid-template-columns: 1fr; gap: 1.25rem;">
                <div>
                    <label for="nama_lengkap" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Nama Lengkap</label>
                    <input type="text" name="nama_lengkap" style="width: 100%; padding: 0.75rem; border: 1px solid #dee2e6; border-radius: 6px;" required>
                </div>
                <div>
                    <label for="username" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Username</label>
                    <input type="text" name="username" style="width: 100%; padding: 0.75rem; border: 1px solid #dee2e6; border-radius: 6px;" required>
                </div>
                <div>
                    <label for="password" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Password</label>
                    <input type="password" name="password" style="width: 100%; padding: 0.75rem; border: 1px solid #dee2e6; border-radius: 6px;" required>
                </div>
            </div>
            <div style="margin-top: 2rem; display: flex; gap: 1rem;">
                <button type="submit" style="padding: 0.75rem 1.5rem; background-color: #0d6efd; color: white; border: none; border-radius: 6px; cursor: pointer;">Simpan Admin</button>
                <a href="profil_admin.php" style="padding: 0.75rem 1.5rem; background-color: #6c757d; color: white; text-decoration: none; border-radius: 6px;">Batal</a>
            </div>
        </form>
    </div>
</main>

<?php
require_once __DIR__ . '/../template/footer.php';
?>
