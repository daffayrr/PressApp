<?php
require_once '../../config/database.php';
require_once 'fpdf.php';

// Ambil data dari database (logika sama seperti di rekap_laporan.php)
$conn = connect_db();
$filter_tanggal_mulai = $_GET['tanggal_mulai'] ?? date('Y-m-01');
$filter_tanggal_selesai = $_GET['tanggal_selesai'] ?? date('Y-m-d');

$sql = "
    SELECT
        k.nama_lengkap, k.username,
        (SELECT COUNT(DISTINCT DATE(waktu)) FROM presensi WHERE id_karyawan = k.id AND tipe = 'masuk' AND DATE(waktu) BETWEEN ? AND ?) as jumlah_masuk,
        (SELECT COUNT(*) FROM presensi WHERE id_karyawan = k.id AND status = 'Terlambat' AND DATE(waktu) BETWEEN ? AND ?) as jumlah_keterlambatan,
        (SELECT COUNT(*) FROM pengajuan WHERE id_karyawan = k.id AND tipe_pengajuan = 'cuti' AND status_pengajuan = 'disetujui' AND tanggal_mulai BETWEEN ? AND ?) as jumlah_cuti,
        (SELECT COUNT(*) FROM pengajuan WHERE id_karyawan = k.id AND tipe_pengajuan = 'lembur' AND status_pengajuan = 'disetujui' AND tanggal_mulai BETWEEN ? AND ?) as jumlah_lembur
    FROM karyawan k
    WHERE k.role != 'administrator'
    GROUP BY k.id, k.nama_lengkap, k.username ORDER BY k.nama_lengkap ASC;
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssssss", $filter_tanggal_mulai, $filter_tanggal_selesai, $filter_tanggal_mulai, $filter_tanggal_selesai, $filter_tanggal_mulai, $filter_tanggal_selesai, $filter_tanggal_mulai, $filter_tanggal_selesai);
$stmt->execute();
$rekap_data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();

// Buat Dokumen PDF
$pdf = new FPDF('L', 'mm', 'A4');
$pdf->AddPage();

// Header Dokumen
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'Laporan Rekapitulasi Total Karyawan', 0, 1, 'C');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 7, 'Periode: ' . date('d M Y', strtotime($filter_tanggal_mulai)) . ' - ' . date('d M Y', strtotime($filter_tanggal_selesai)), 0, 1, 'C');
$pdf->Ln(10);

// Header Tabel
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetFillColor(52, 58, 64);
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell(10, 10, 'No', 1, 0, 'C', true);
$pdf->Cell(70, 10, 'Nama Lengkap', 1, 0, 'C', true);
$pdf->Cell(50, 10, 'Username', 1, 0, 'C', true);
$pdf->Cell(35, 10, 'Jml Masuk', 1, 0, 'C', true);
$pdf->Cell(35, 10, 'Jml Terlambat', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Jml Cuti', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Jml Lembur', 1, 1, 'C', true);

// Isi Tabel
$pdf->SetFont('Arial', '', 9);
$pdf->SetTextColor(0, 0, 0);
$no = 1;
if (count($rekap_data) > 0) {
    foreach ($rekap_data as $row) {
        $pdf->Cell(10, 8, $no++, 1, 0, 'C');
        $pdf->Cell(70, 8, $row['nama_lengkap'], 1);
        $pdf->Cell(50, 8, $row['username'], 1);
        $pdf->Cell(35, 8, $row['jumlah_masuk'] . ' hari', 1, 0, 'C');
        $pdf->Cell(35, 8, $row['jumlah_keterlambatan'] . ' kali', 1, 0, 'C');
        $pdf->Cell(30, 8, $row['jumlah_cuti'] . ' kali', 1, 0, 'C');
        $pdf->Cell(30, 8, $row['jumlah_lembur'] . ' kali', 1, 1, 'C');
    }
} else {
    $pdf->Cell(260, 10, 'Tidak ada data untuk ditampilkan.', 1, 1, 'C');
}

$pdf->Output('D', 'Rekap_Total_'.date('Ymd').'.pdf');
