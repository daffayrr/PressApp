<?php
require_once __DIR__ . '/../../config/database.php';
$page_title = "Pengaturan Lokasi Kantor";
require_once '../template/header.php';

$conn = connect_db();

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
    header("Location: lokasi_kantor.php");
    exit;
}

$sql = "SELECT latitude_kantor, longitude_kantor, radius_presensi FROM pengaturan WHERE id = 1";
$result = $conn->query($sql);
$pengaturan = $result->fetch_assoc();
$conn->close();
?>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<div class="flex h-screen bg-gray-100">
    <?php require_once '../template/sidebar.php'; ?>
    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="flex justify-between items-center p-4 bg-white border-b shadow-sm">
            <h1 class="text-xl font-semibold text-gray-700"><?php echo htmlspecialchars($page_title); ?></h1>
        </header>
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">
            <div class="container mx-auto">
                <?php if (isset($_SESSION['notification'])): ?>
                    <div class="mb-4 p-4 text-sm rounded-lg <?php echo $_SESSION['notification']['type'] === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                        <?php echo htmlspecialchars($_SESSION['notification']['message']); ?>
                    </div>
                    <?php unset($_SESSION['notification']); ?>
                <?php endif; ?>
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-1 bg-white p-6 rounded-xl shadow-md">
                        <h3 class="text-xl font-semibold text-gray-800 mb-4">Detail Lokasi</h3>
                        <p class="text-sm text-gray-500 mb-6">Klik atau geser pin di peta untuk menentukan titik koordinat kantor.</p>
                        <form action="lokasi_kantor.php" method="POST">
                            <div class="space-y-4">
                                <div>
                                    <label for="latitude" class="block text-sm font-medium text-gray-700">Latitude</label>
                                    <input type="text" id="latitude" name="latitude" value="<?php echo htmlspecialchars($pengaturan['latitude_kantor']); ?>" class="mt-1 block w-full rounded-md bg-gray-100" readonly>
                                </div>
                                <div>
                                    <label for="longitude" class="block text-sm font-medium text-gray-700">Longitude</label>
                                    <input type="text" id="longitude" name="longitude" value="<?php echo htmlspecialchars($pengaturan['longitude_kantor']); ?>" class="mt-1 block w-full rounded-md bg-gray-100" readonly>
                                </div>
                                <div>
                                    <label for="radius" class="block text-sm font-medium text-gray-700">Radius Presensi (meter)</label>
                                    <div class="flex items-center space-x-3 mt-1">
                                        <input type="range" id="radius-slider" min="10" max="500" value="<?php echo htmlspecialchars($pengaturan['radius_presensi']); ?>" class="w-full h-2 bg-gray-200 rounded-lg cursor-pointer">
                                        <input type="number" id="radius" name="radius" value="<?php echo htmlspecialchars($pengaturan['radius_presensi']); ?>" class="w-24 rounded-md">
                                    </div>
                                </div>
                            </div>
                            <div class="mt-8">
                                <button type="submit" class="w-full px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">Simpan Pengaturan</button>
                            </div>
                        </form>
                    </div>
                    <div class="lg:col-span-2 bg-white p-2 rounded-xl shadow-md">
                        <div id="map" class="h-96 lg:h-full w-full rounded-lg z-10 cursor-pointer"></div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
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
        color: 'blue', fillColor: '#3b82f6', fillOpacity: 0.2, radius: initialRadius
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
    marker.on('dragend', function (e) { updatePosition(marker.getLatLng()); });
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
<?php require_once '../template/footer.php'; ?>
