<?php
// 1. Panggil file konfigurasi dan header
require_once '../../config/database.php';
$pageTitle = "Pengaturan Lokasi Kantor";
require_once __DIR__ . '/../template/header.php';

$conn = connect_db();

// --- PROSES SIMPAN DATA (JIKA ADA FORM SUBMIT) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];
    $radius = $_POST['radius'];

    $sql = "UPDATE pengaturan SET latitude_kantor = ?, longitude_kantor = ?, radius_presensi = ? WHERE id = 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ddi", $latitude, $longitude, $radius);

    if ($stmt->execute()) {
        $_SESSION['notification'] = ['message' => 'Pengaturan lokasi berhasil diperbarui.', 'type' => 'success'];
    } else {
        $_SESSION['notification'] = ['message' => 'Gagal memperbarui pengaturan.', 'type' => 'error'];
    }
    $stmt->close();
    // Redirect untuk menghindari resubmit form
    header("Location: lokasi_kantor.php");
    exit;
}

// --- AMBIL DATA PENGATURAN SAAT INI ---
$sql = "SELECT latitude_kantor, longitude_kantor, radius_presensi FROM pengaturan WHERE id = 1";
$result = $conn->query($sql);
$pengaturan = $result->fetch_assoc();
$conn->close();
?>

<!-- Tambahan CSS untuk Leaflet.js -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>

<!-- KONTEN UTAMA HALAMAN DIMULAI DI SINI -->
<?php 
// Panggil sidebar
require_once __DIR__ . '/../template/sidebar.php'; 
?>

<main class="main-content">
    <div class="content-panel">
        <h1 style="font-size: 1.5rem; font-weight: 700; color: #0d6efd; margin-bottom: 0.5rem;">Dashboard - Pengaturan Lokasi Kantor</h1>
        <hr style="margin-bottom: 1.5rem;">

        <?php if (isset($_SESSION['notification'])): ?>
            <div style="margin-bottom: 1.5rem; padding: 1rem; border-radius: 6px; color: #0f5132; background-color: #d1e7dd; border: 1px solid #badbcc;">
                <?php echo htmlspecialchars($_SESSION['notification']['message']); ?>
            </div>
            <?php unset($_SESSION['notification']); ?>
        <?php endif; ?>

        <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 1.5rem;">
            <!-- Kolom Form -->
            <div>
                <h3 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1rem;">Detail Lokasi</h3>
                <p style="font-size: 0.9rem; color: #6c757d; margin-bottom: 1.5rem;">Klik atau geser pin di peta untuk menentukan titik koordinat kantor. Sesuaikan radius toleransi absensi.</p>
                
                <form action="lokasi_kantor.php" method="POST">
                    <div style="display: grid; grid-template-columns: 1fr; gap: 1.25rem;">
                        <div>
                            <label for="latitude" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Latitude</label>
                            <input type="text" id="latitude" name="latitude" value="<?php echo htmlspecialchars($pengaturan['latitude_kantor']); ?>" style="width: 80%; padding: 0.75rem; border: 1px solid #dee2e6; border-radius: 6px; background-color: #e9ecef;" readonly>
                        </div>
                        <div>
                            <label for="longitude" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Longitude</label>
                            <input type="text" id="longitude" name="longitude" value="<?php echo htmlspecialchars($pengaturan['longitude_kantor']); ?>" style="width: 80%; padding: 0.75rem; border: 1px solid #dee2e6; border-radius: 6px; background-color: #e9ecef;" readonly>
                        </div>
                        <div>
                            <label for="radius" style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Radius Presensi (meter)</label>
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <input type="range" id="radius-slider" min="10" max="500" value="<?php echo htmlspecialchars($pengaturan['radius_presensi']); ?>" style="width: 100%;">
                                <input type="number" id="radius" name="radius" value="<?php echo htmlspecialchars($pengaturan['radius_presensi']); ?>" style="width: 80px; padding: 0.75rem; border: 1px solid #dee2e6; border-radius: 6px;">
                            </div>
                        </div>
                    </div>
                    <div style="margin-top: 2rem;">
                        <button type="submit" style="padding: 0.75rem 1.5rem; background-color: #0d6efd; color: white; border: none; border-radius: 6px; cursor: pointer;">
                            Simpan Pengaturan
                        </button>
                    </div>
                </form>
            </div>

            <!-- Kolom Peta -->
            <div style="border-radius: 6px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <div id="map" style="height: 100%; width: 100%; min-height: 400px; cursor: pointer;"></div>
            </div>
        </div>
    </div>
</main>

<!-- Tambahan JS untuk Leaflet.js (diletakkan sebelum footer) -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const initialLat = <?php echo $pengaturan['latitude_kantor']; ?>;
    const initialLng = <?php echo $pengaturan['longitude_kantor']; ?>;
    const initialRadius = <?php echo $pengaturan['radius_presensi']; ?>;

    const map = L.map('map').setView([initialLat, initialLng], 17);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    const marker = L.marker([initialLat, initialLng], { draggable: true }).addTo(map);
    const circle = L.circle([initialLat, initialLng], {
        color: '#0d6efd', fillColor: '#0d6efd', fillOpacity: 0.2, radius: initialRadius
    }).addTo(map);

    const latInput = document.getElementById('latitude');
    const lngInput = document.getElementById('longitude');
    const radiusInput = document.getElementById('radius');
    const radiusSlider = document.getElementById('radius-slider');

    function updatePosition(latlng) {
        latInput.value = latlng.lat.toFixed(8);
        lngInput.value = latlng.lng.toFixed(8);
        circle.setLatLng(latlng);
    }

    marker.on('dragend', function () { updatePosition(marker.getLatLng()); });
    map.on('click', function(e) { marker.setLatLng(e.latlng); updatePosition(e.latlng); });

    radiusInput.addEventListener('input', function () {
        const newRadius = parseInt(this.value, 10);
        if (!isNaN(newRadius)) { circle.setRadius(newRadius); radiusSlider.value = newRadius; }
    });
    radiusSlider.addEventListener('input', function () {
        const newRadius = parseInt(this.value, 10);
        circle.setRadius(newRadius); radiusInput.value = newRadius;
    });
});
</script>

<?php
// Panggil footer
require_once __DIR__ . '/../template/footer.php';
?>
