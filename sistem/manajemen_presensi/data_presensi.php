<?php
// 1. Panggil file konfigurasi dan header
require_once '../../config/database.php';
$pageTitle = "Data Presensi";
require_once __DIR__ . '/../template/header.php';

// --- LOGIKA UNTUK FILTER DAN PENGAMBILAN DATA ---
$conn = connect_db();
$filter_tanggal_mulai = $_GET['tanggal_mulai'] ?? date('Y-m-01');
$filter_tanggal_selesai = $_GET['tanggal_selesai'] ?? date('Y-m-d');

// Ambil pengaturan jam kerja untuk perhitungan keterlambatan
$sql_pengaturan = "SELECT jam_masuk, toleransi_terlambat FROM pengaturan WHERE id = 1";
$pengaturan = $conn->query($sql_pengaturan)->fetch_assoc();
$jam_kerja_mulai = $pengaturan['jam_masuk'] ?? '08:00:00';
$toleransi_menit = $pengaturan['toleransi_terlambat'] ?? 0;

// Query sederhana untuk mengambil data mentah (Anti-Error)
$sql = "
    SELECT 
        DATE(p.waktu) as tanggal,
        k.nama_lengkap,
        k.role,
        p.tipe,
        TIME(p.waktu) as jam,
        p.catatan,
        p.latitude,
        p.longitude
    FROM presensi p
    JOIN karyawan k ON p.id_karyawan = k.id
    WHERE DATE(p.waktu) BETWEEN ? AND ?
    ORDER BY k.nama_lengkap ASC, p.waktu ASC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $filter_tanggal_mulai, $filter_tanggal_selesai);
$stmt->execute();
$semua_presensi = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();

// --- OLAH DATA MENGGUNAKAN PHP ---
$laporan_harian = [];
foreach ($semua_presensi as $presensi) {
    $key = $presensi['tanggal'] . '_' . $presensi['nama_lengkap'];

    if (!isset($laporan_harian[$key])) {
        $laporan_harian[$key] = [
            'tanggal' => $presensi['tanggal'],
            'nama_lengkap' => $presensi['nama_lengkap'],
            'role' => $presensi['role'],
            'jam_masuk' => null,
            'jam_pulang' => null,
            'catatan' => null,
            'keterlambatan' => null, // Kolom baru untuk keterlambatan
            'latitude' => null,
            'longitude' => null
        ];
    }

    if ($presensi['tipe'] == 'masuk' && $laporan_harian[$key]['jam_masuk'] === null) {
        $laporan_harian[$key]['jam_masuk'] = $presensi['jam'];
        $laporan_harian[$key]['catatan'] = $presensi['catatan'];
        $laporan_harian[$key]['latitude'] = $presensi['latitude'];
        $laporan_harian[$key]['longitude'] = $presensi['longitude'];

        // --- LOGIKA PERHITUNGAN KETERLAMBATAN ---
        $jam_masuk_dt = new DateTime($presensi['tanggal'] . ' ' . $jam_kerja_mulai);
        $batas_toleransi_dt = (clone $jam_masuk_dt)->modify('+' . $toleransi_menit . ' minutes');
        $waktu_presensi_dt = new DateTime($presensi['tanggal'] . ' ' . $presensi['jam']);

        if ($waktu_presensi_dt > $batas_toleransi_dt) {
            $keterlambatan = $waktu_presensi_dt->diff($jam_masuk_dt);
            $laporan_harian[$key]['keterlambatan'] = $keterlambatan->format('%h jam, %i menit, %s detik');
        }
        // --- AKHIR LOGIKA ---

    } elseif ($presensi['tipe'] == 'pulang') {
        $laporan_harian[$key]['jam_pulang'] = $presensi['jam'];
    }
}
?>

<!-- KONTEN UTAMA HALAMAN DIMULAI DI SINI -->
<?php 
require_once __DIR__ . '/../template/sidebar.php'; 
?>

<main class="main-content">
    <div class="content-panel">
        <h1 style="font-size: 1.5rem; font-weight: 700; color: #0d6efd; margin-bottom: 0.5rem;">Dashboard - Laporan Presensi</h1>
        <hr style="margin-bottom: 1.5rem;">

        <!-- Form Filter dan Tombol Aksi -->
        <div style="background-color: #f8f9fa; padding: 1rem; border-radius: 6px; margin-bottom: 1.5rem; border: 1px solid #dee2e6;">
            <form action="data_presensi.php" method="GET" style="display: flex; flex-wrap: wrap; align-items: flex-end; gap: 1rem;">
                <div>
                    <label for="tanggal_mulai" style="font-size: 0.875rem; font-weight: 500; color: #495057;">Dari Tanggal:</label>
                    <input type="date" id="tanggal_mulai" name="tanggal_mulai" value="<?php echo htmlspecialchars($filter_tanggal_mulai); ?>" style="margin-top: 0.25rem; border: 1px solid #ced4da; border-radius: 4px; padding: 0.5rem;">
                </div>
                <div>
                    <label for="tanggal_selesai" style="font-size: 0.875rem; font-weight: 500; color: #495057;">Sampai Tanggal:</label>
                    <input type="date" id="tanggal_selesai" name="tanggal_selesai" value="<?php echo htmlspecialchars($filter_tanggal_selesai); ?>" style="margin-top: 0.25rem; border: 1px solid #ced4da; border-radius: 4px; padding: 0.5rem;">
                </div>
                <button type="submit" style="padding: 0.5rem 1rem; color: white; background-color: #0d6efd; border: none; border-radius: 4px; cursor: pointer;">
                    Filter
                </button>
                <a href="../lib/cetak_laporan.php?tanggal_mulai=<?php echo $filter_tanggal_mulai; ?>&tanggal_selesai=<?php echo $filter_tanggal_selesai; ?>" 
                   target="_blank"
                   style="padding: 0.5rem 1rem; color: white; background-color: #dc3545; border: none; border-radius: 4px; text-decoration: none;">
                    <i class="fa-solid fa-file-pdf" style="margin-right: 8px;"></i> Cetak PDF
                </a>
            </form>
        </div>

        <!-- TABEL HTML SEDERHANA -->
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead style="background-color: #343a40; color: white;">
                    <tr>
                        <th style="padding: 12px 15px;">Tanggal</th>
                        <th style="padding: 12px 15px;">Nama Karyawan</th>
                        <th style="padding: 12px 15px;">Role</th>
                        <th style="padding: 12px 15px;">Jam Masuk</th>
                        <th style="padding: 12px 15px;">Jam Pulang</th>
                        <th style="padding: 12px 15px;">Keterlambatan</th>
                        <th style="padding: 12px 15px;">Catatan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($laporan_harian) > 0): ?>
                        <?php foreach($laporan_harian as $row): ?>
                        <tr style="border-bottom: 1px solid #dee2e6;">
                            <td style="padding: 12px 15px;"><?php echo date('d M Y', strtotime($row['tanggal'])); ?></td>
                            <td style="padding: 12px 15px; font-weight: 500;"><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                            <td style="padding: 12px 15px;"><?php echo ucfirst(htmlspecialchars($row['role'])); ?></td>
                            <td style="padding: 12px 15px;"><?php echo $row['jam_masuk'] ? date('H:i:s', strtotime($row['jam_masuk'])) : '-'; ?></td>
                            <td style="padding: 12px 15px;"><?php echo $row['jam_pulang'] ? date('H:i:s', strtotime($row['jam_pulang'])) : '-'; ?></td>
                            <td style="padding: 12px 15px; color: <?php echo $row['keterlambatan'] ? '#dc3545' : 'inherit'; ?>;">
                                <?php echo htmlspecialchars($row['keterlambatan'] ?? '-'); ?>
                            </td>
                            <td style="padding: 12px 15px; font-style: italic; color: #6c757d;"><?php echo htmlspecialchars($row['catatan'] ?? '-'); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="padding: 12px 15px; text-align: center; color: #6c757d;">
                                Tidak ada data presensi untuk periode ini.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php
// Panggil footer baru Anda
require_once __DIR__ . '/../template/footer.php';
?>
