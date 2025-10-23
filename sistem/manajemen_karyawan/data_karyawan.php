<?php
// 1. Panggil file konfigurasi database paling pertama.
require_once '../../config/database.php';

// 2. Atur variabel halaman dan panggil header.
$pageTitle = "Data Karyawan";
require_once __DIR__ . '/../template/header.php';

// 3. Jalankan logika database.
$conn = connect_db();
$sql = "SELECT id, nama_lengkap, username, jabatan, role FROM karyawan WHERE role != 'administrator' ORDER BY nama_lengkap ASC";
$result = $conn->query($sql);
?>

<!-- Tambahkan SweetAlert2 di <head> -->
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- KONTEN UTAMA HALAMAN DIMULAI DI SINI -->
<?php 
// Panggil sidebar untuk navigasi.
require_once __DIR__ . '/../template/sidebar.php'; 
?>

<main class="main-content">
    <div class="content-panel">
        <h1 style="font-size: 1.5rem; font-weight: 700; color: #0d6efd; margin-bottom: 0.5rem;">Dashboard - Data Karyawan</h1>
        <hr style="margin-bottom: 1.5rem;">
        
        <!-- BAGIAN KONTROL ATAS (PENCARIAN & TOMBOL TAMBAH) -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <div style="position: relative;">
                <i class="fa-solid fa-search" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #6c757d;"></i>
                <input type="text" id="search-box" placeholder="Cari karyawan..." style="width: 300px; padding: 0.6rem 0.6rem 0.6rem 2.5rem; border: 1px solid #dee2e6; border-radius: 6px; font-size: 0.9rem;">
            </div>
            <a href="tambah_karyawan.php" style="padding: 0.6rem 1.2rem; background-color: #0d6efd; color: white; text-decoration: none; border-radius: 6px; font-size: 0.9rem;">
                <i class="fa-solid fa-plus" style="margin-right: 8px;"></i> Tambah Karyawan
            </a>
        </div>
        
        <!-- TABEL HTML YANG BERSIH -->
        <div style="overflow-x: auto;">
            <table id="interactive-table" style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead style="background-color: #343a40; color: white;">
                    <tr>
                        <th style="padding: 12px 15px; width: 5%;">No</th>
                        <th style="padding: 12px 15px;">Nama Lengkap</th>
                        <th style="padding: 12px 15px;">Username</th>
                        <th style="padding: 12px 15px;">Jabatan</th>
                        <th style="padding: 12px 15px; width: 15%;">Role</th>
                        <th style="padding: 12px 15px; text-align: center; width: 10%;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): $no = 1; ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr style="border-bottom: 1px solid #dee2e6;">
                            <td style="padding: 12px 15px; text-align: center;"><?php echo $no++; ?></td>
                            <td style="padding: 12px 15px; font-weight: 500;"><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                            <td style="padding: 12px 15px;"><?php echo htmlspecialchars($row['username']); ?></td>
                            <td style="padding: 12px 15px;"><?php echo htmlspecialchars($row['jabatan']); ?></td>
                            <td style="padding: 12px 15px;"><?php echo ucfirst(htmlspecialchars($row['role'])); ?></td>
                            <td style="padding: 12px 15px; text-align: center;">
                                <a href="edit_karyawan.php?id=<?php echo $row['id']; ?>" style="color: #ffc107; text-decoration: none; margin-right: 15px;">Edit</a>
                                <a href="proses.php?action=hapus&id=<?php echo $row['id']; ?>" style="color: #dc3545; text-decoration: none;" onclick="return confirm('Apakah Anda yakin ingin menghapus karyawan ini?');">Hapus</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="padding: 12px 15px; text-align: center; color: #6c757d;">
                                Tidak ada data karyawan.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Kontrol Paginasi -->
        <div id="pagination-controls" style="display: flex; justify-content: space-between; align-items: center; margin-top: 1rem; font-size: 0.9rem; color: #6c757d;">
            <span id="page-info"></span>
            <div>
                <button id="prev-page" style="padding: 5px 10px; border: 1px solid #dee2e6; border-radius: 4px; cursor: pointer;">Previous</button>
                <button id="next-page" style="padding: 5px 10px; border: 1px solid #dee2e6; border-radius: 4px; cursor: pointer;">Next</button>
            </div>
        </div>
    </div>
</main>

<!-- SCRIPT UNTUK TABEL INTERAKTIF -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchBox = document.getElementById('search-box');
    const table = document.getElementById('interactive-table');
    const tbody = table.querySelector('tbody');
    const allRows = Array.from(tbody.querySelectorAll('tr'));
    
    const pageInfo = document.getElementById('page-info');
    const prevButton = document.getElementById('prev-page');
    const nextButton = document.getElementById('next-page');
    const paginationControls = document.getElementById('pagination-controls');

    let currentPage = 1;
    const rowsPerPage = 5;
    let filteredRows = allRows;

    function displayPage(page) {
        tbody.innerHTML = '';
        page--; 

        const start = rowsPerPage * page;
        const end = start + rowsPerPage;
        const paginatedItems = filteredRows.slice(start, end);

        paginatedItems.forEach(row => {
            tbody.appendChild(row);
        });

        const totalPages = Math.ceil(filteredRows.length / rowsPerPage);
        pageInfo.textContent = `Halaman ${currentPage} dari ${totalPages > 0 ? totalPages : 1}`;
        
        prevButton.disabled = currentPage === 1;
        nextButton.disabled = currentPage === totalPages || totalPages === 0;
    }

    function applySearch() {
        const searchTerm = searchBox.value.toLowerCase();

        filteredRows = allRows.filter(row => {
            // Cari di kolom Nama, Username, dan Jabatan (indeks 1, 2, 3)
            const nama = row.cells[1].textContent.toLowerCase();
            const username = row.cells[2].textContent.toLowerCase();
            const jabatan = row.cells[3].textContent.toLowerCase();
            
            return nama.includes(searchTerm) || 
                   username.includes(searchTerm) || 
                   jabatan.includes(searchTerm);
        });

        currentPage = 1;
        displayPage(currentPage);
    }

    searchBox.addEventListener('keyup', applySearch);

    prevButton.addEventListener('click', () => {
        if (currentPage > 1) {
            currentPage--;
            displayPage(currentPage);
        }
    });

    nextButton.addEventListener('click', () => {
        const totalPages = Math.ceil(filteredRows.length / rowsPerPage);
        if (currentPage < totalPages) {
            currentPage++;
            displayPage(currentPage);
        }
    });

    if (allRows.length > 0) {
        displayPage(currentPage);
    } else {
        paginationControls.style.display = 'none';
    }
});
</script>

<!-- LOGIKA UNTUK NOTIFIKASI TOAST -->
<?php if (isset($_SESSION['notification'])): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });

        Toast.fire({
            icon: '<?php echo $_SESSION['notification']['type']; ?>',
            title: '<?php echo addslashes($_SESSION['notification']['message']); ?>'
        });
    });
</script>
<?php 
    unset($_SESSION['notification']); 
?>
<?php endif; ?>

<?php
$conn->close();
require_once __DIR__ . '/../template/footer.php';
?>
