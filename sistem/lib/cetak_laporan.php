<?php
// 1. Panggil file konfigurasi dan library FPDF
require_once '../../config/database.php';
require_once 'fpdf.php';

// 2. Ambil data dari database
$conn = connect_db();
$filter_tanggal_mulai = $_GET['tanggal_mulai'] ?? date('Y-m-01');
$filter_tanggal_selesai = $_GET['tanggal_selesai'] ?? date('Y-m-d');

// Ambil pengaturan jam kerja untuk perhitungan keterlambatan
$sql_pengaturan = "SELECT jam_masuk, toleransi_terlambat FROM pengaturan WHERE id = 1";
$pengaturan = $conn->query($sql_pengaturan)->fetch_assoc();
$jam_kerja_mulai = $pengaturan['jam_masuk'] ?? '08:00:00';
$toleransi_menit = $pengaturan['toleransi_terlambat'] ?? 0;

// Query sederhana untuk mengambil data mentah
$sql = "
    SELECT 
        DATE(p.waktu) as tanggal,
        k.nama_lengkap,
        k.role,
        p.tipe,
        TIME(p.waktu) as jam,
        p.catatan
    FROM presensi p
    JOIN karyawan k ON p.id_karyawan = k.id
    WHERE DATE(p.waktu) BETWEEN ? AND ?
    ORDER BY p.waktu ASC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $filter_tanggal_mulai, $filter_tanggal_selesai);
$stmt->execute();
$semua_presensi = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();

// 3. Olah data mentah menggunakan PHP
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
            'keterlambatan' => null
        ];
    }

    if ($presensi['tipe'] == 'masuk' && $laporan_harian[$key]['jam_masuk'] === null) {
        $laporan_harian[$key]['jam_masuk'] = $presensi['jam'];
        $laporan_harian[$key]['catatan'] = $presensi['catatan'];

        $jam_masuk_dt = new DateTime($presensi['tanggal'] . ' ' . $jam_kerja_mulai);
        $batas_toleransi_dt = (clone $jam_masuk_dt)->modify('+' . $toleransi_menit . ' minutes');
        $waktu_presensi_dt = new DateTime($presensi['tanggal'] . ' ' . $presensi['jam']);

        if ($waktu_presensi_dt > $batas_toleransi_dt) {
            $keterlambatan = $waktu_presensi_dt->diff($jam_masuk_dt);
            $laporan_harian[$key]['keterlambatan'] = $keterlambatan->format('%h jam, %i m, %s d');
        }
    } elseif ($presensi['tipe'] == 'pulang') {
        $laporan_harian[$key]['jam_pulang'] = $presensi['jam'];
    }
}
ksort($laporan_harian); // Urutkan berdasarkan tanggal dan nama

// 4. Buat Dokumen PDF
$pdf = new FPDF('L', 'mm', 'A4'); // 'L' untuk Landscape
$pdf->AddPage();

// Header Dokumen
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'Laporan Rekap Presensi Karyawan', 0, 1, 'C');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 7, 'Periode: ' . date('d M Y', strtotime($filter_tanggal_mulai)) . ' - ' . date('d M Y', strtotime($filter_tanggal_selesai)), 0, 1, 'C');
$pdf->Ln(10);

// Header Tabel
$pdf->SetFont('Arial', 'B', 9);
$pdf->SetFillColor(52, 58, 64);
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell(25, 10, 'Tanggal', 1, 0, 'C', true);
$pdf->Cell(50, 10, 'Nama Karyawan', 1, 0, 'C', true);
$pdf->Cell(25, 10, 'Role', 1, 0, 'C', true);
$pdf->Cell(25, 10, 'Jam Masuk', 1, 0, 'C', true);
$pdf->Cell(25, 10, 'Jam Pulang', 1, 0, 'C', true);
$pdf->Cell(50, 10, 'Keterlambatan', 1, 0, 'C', true);
$pdf->Cell(77, 10, 'Catatan', 1, 1, 'C', true);

// Isi Tabel
$pdf->SetFont('Arial', '', 8);
$pdf->SetTextColor(0, 0, 0);
if (count($laporan_harian) > 0) {
    foreach ($laporan_harian as $row) {
        $pdf->Cell(25, 8, date('d M Y', strtotime($row['tanggal'])), 1);
        $pdf->Cell(50, 8, $row['nama_lengkap'], 1);
        $pdf->Cell(25, 8, ucfirst($row['role'] ?? ''), 1);
        $pdf->Cell(25, 8, $row['jam_masuk'] ? date('H:i:s', strtotime($row['jam_masuk'])) : '-', 1, 0, 'C');
        $pdf->Cell(25, 8, $row['jam_pulang'] ? date('H:i:s', strtotime($row['jam_pulang'])) : '-', 1, 0, 'C');
        $pdf->Cell(50, 8, $row['keterlambatan'] ?? '-', 1);
        $pdf->Cell(77, 8, $row['catatan'] ?? '-', 1, 1);
    }
} else {
    $pdf->Cell(277, 10, 'Tidak ada data presensi untuk periode ini.', 1, 1, 'C');
}

// 5. Output PDF ke browser
$pdf->Output('D', 'Laporan_Presensi_'.date('Ymd').'.pdf');
