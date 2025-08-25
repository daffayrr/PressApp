            </div> <!-- Penutup .main-body dari header.php -->
        <footer class="app-footer">
            &copy; <?= date("Y"); ?> Balai Teknologi dan Komunikasi Pendidikan DIY
        </footer>
    </div> <!-- Penutup .app-container dari header.php -->

    <!-- SEMUA SCRIPT DIPINDAHKAN KE SINI -->
    <script src="https://kendo.cdn.telerik.com/2023.2.829/js/jquery.min.js"></script>
    <script src="https://kendo.cdn.telerik.com/2023.2.829/js/kendo.all.min.js"></script>
    
    <!-- Script untuk jam digital di header -->
    <script>
        function updateClock() {
            const clockElement = document.getElementById('live-clock');
            if (clockElement) {
                const now = new Date();
                const timeString = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false }).replace(/\./g, ' : ');
                clockElement.textContent = timeString;
            }
        }
        setInterval(updateClock, 1000);
        updateClock();
    </script>
    
    <!-- Script spesifik halaman (seperti Kendo Grid) akan dimuat setelah ini -->

</body>
</html>
