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

<!-- KONTEN UTAMA HALAMAN DIMULAI DI SINI -->
<?php 
// Panggil sidebar untuk navigasi.
require_once __DIR__ . '/../template/sidebar.php'; 
?>

<main class="main-content">
    <div class="content-panel">
        <h1 style="font-size: 1.5rem; font-weight: 700; color: #0d6efd; margin-bottom: 0.5rem;">Dashboard - Data Karyawan</h1>
        <hr style="margin-bottom: 1.5rem;">
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h2 style="font-size: 1.25rem; font-weight: 600;">Data Karyawan</h2>
            <a href="tambah_karyawan.php" style="padding: 0.6rem 1.2rem; background-color: #0d6efd; color: white; text-decoration: none; border-radius: 6px; font-size: 0.9rem;">
                <i class="fa-solid fa-plus" style="margin-right: 8px;"></i> Tambah Karyawan
            </a>
        </div>
        
        <?php if (isset($_SESSION['notification'])): ?>
            <div style="margin-bottom: 1.5rem; padding: 1rem; border-radius: 6px; color: #0f5132; background-color: #d1e7dd; border: 1px solid #badbcc;">
                <?php echo htmlspecialchars($_SESSION['notification']['message']); ?>
            </div>
            <?php unset($_SESSION['notification']); ?>
        <?php endif; ?>

        <!-- TABEL HTML INTERAKTIF -->
        <div style="overflow-x: auto;">
            <table id="interactive-table" style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead style="background-color: #343a40; color: white;">
                    <tr>
                        <th style="padding: 12px 15px;">No</th>
                        <th style="padding: 12px 15px;">Nama Lengkap</th>
                        <th style="padding: 12px 15px;">Username</th>
                        <th style="padding: 12px 15px;">Jabatan</th>
                        <th style="padding: 12px 15px;">Role</th>
                        <th style="padding: 12px 15px; text-align: center;">Aksi</th>
                    </tr>
                    <!-- Baris Filter -->
                    <tr style="background-color: #495057;">
                        <th></th>
                        <th style="padding: 8px;"><input type="text" placeholder="Cari Nama..." data-column="1" class="table-filter"></th>
                        <th style="padding: 8px;"><input type="text" placeholder="Cari Username..." data-column="2" class="table-filter"></th>
                        <th style="padding: 8px;"><input type="text" placeholder="Cari Jabatan..." data-column="3" class="table-filter"></th>
                        <th></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr style="border-bottom: 1px solid #dee2e6;">
                            <td style="padding: 12px 15px;"></td> <!-- Nomor akan diisi oleh JS -->
                            <td style="padding: 12px 15px; font-weight: 500;"><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                            <td style="padding: 12px 15px;"><?php echo htmlspecialchars($row['username']); ?></td>
                            <td style="padding: 12px 15px;"><?php echo htmlspecialchars($row['jabatan']); ?></td>
                            <td style="padding: 12px 15px;"><?php echo ucfirst(htmlspecialchars($row['role'])); ?></td>
                            <td style="padding: 12px 15px; text-align: center;">
                                <a href="edit_karyawan.php?id=<?php echo $row['id']; ?>" style="color: #ffc107; text-decoration: none; margin-right: 15px;">Edit</a>
                                <a href="proses.php?action=hapus&id=<?php echo $row['id']; ?>" style="color: #dc3545; text-decoration: none;" onclick="return confirm('Apakah Anda yakin?');">Hapus</a>
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
<style>
    .table-filter {
        width: 100%;
        padding: 6px 8px;
        border-radius: 4px;
        border: 1px solid #6c757d;
        background-color: #343a40;
        color: white;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const table = document.getElementById('interactive-table');
    const tbody = table.querySelector('tbody');
    const allRows = Array.from(tbody.querySelectorAll('tr'));
    const filterInputs = document.querySelectorAll('.table-filter');
    const paginationControls = document.getElementById('pagination-controls');
    const pageInfo = document.getElementById('page-info');
    const prevButton = document.getElementById('prev-page');
    const nextButton = document.getElementById('next-page');

    let currentPage = 1;
    const rowsPerPage = 5;
    let filteredRows = allRows;

    function displayPage(page) {
        tbody.innerHTML = '';
        page--; // Adjust for zero-based index

        const start = rowsPerPage * page;
        const end = start + rowsPerPage;
        const paginatedItems = filteredRows.slice(start, end);

        paginatedItems.forEach((row, index) => {
            // Update nomor urut
            row.querySelector('td:first-child').textContent = start + index + 1;
            tbody.appendChild(row);
        });

        const totalPages = Math.ceil(filteredRows.length / rowsPerPage);
        pageInfo.textContent = `Halaman ${currentPage} dari ${totalPages}`;
        
        prevButton.disabled = currentPage === 1;
        nextButton.disabled = currentPage === totalPages || totalPages === 0;
        
        prevButton.style.cursor = prevButton.disabled ? 'not-allowed' : 'pointer';
        nextButton.style.cursor = nextButton.disabled ? 'not-allowed' : 'pointer';
    }

    function applyFilters() {
        const filterValues = Array.from(filterInputs).map(input => input.value.toLowerCase());

        filteredRows = allRows.filter(row => {
            const cells = row.querySelectorAll('td');
            return filterValues.every((filter, index) => {
                if (!filter) return true;
                const colIndex = parseInt(filterInputs[index].dataset.column, 10);
                const cellValue = cells[colIndex].textContent.toLowerCase();
                return cellValue.includes(filter);
            });
        });

        currentPage = 1;
        displayPage(currentPage);
    }

    filterInputs.forEach(input => {
        input.addEventListener('keyup', applyFilters);
    });

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

<?php
$conn->close();
// Panggil footer baru Anda
require_once __DIR__ . '/../template/footer.php';