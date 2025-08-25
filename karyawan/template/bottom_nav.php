<?php
// $halaman_aktif harus didefinisikan di halaman utama sebelum memanggil file ini
$aktif = $halaman_aktif ?? '';
?>
<!-- Bottom Navigation Bar -->
<nav class="bg-white border-t border-gray-200 p-2">
    <div class="flex justify-around">
        <!-- Menu Home/Presensi -->
        <a href="dashboard.php" class="flex flex-col items-center justify-center text-center w-full p-2 rounded-lg transition-colors duration-200 <?php echo ($aktif == 'dashboard') ? 'bg-blue-50 text-blue-600' : 'text-gray-500 hover:bg-gray-100'; ?>">
            <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
            <span class="text-xs font-medium">Home</span>
        </a>
        <!-- Menu Riwayat -->
        <a href="riwayat.php" class="flex flex-col items-center justify-center text-center w-full p-2 rounded-lg transition-colors duration-200 <?php echo ($aktif == 'riwayat') ? 'bg-blue-50 text-blue-600' : 'text-gray-500 hover:bg-gray-100'; ?>">
            <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span class="text-xs font-medium">Riwayat</span>
        </a>
        <!-- Menu Pengajuan -->
        <a href="pengajuan.php" class="flex flex-col items-center justify-center text-center w-full p-2 rounded-lg transition-colors duration-200 <?php echo ($aktif == 'pengajuan') ? 'bg-blue-50 text-blue-600' : 'text-gray-500 hover:bg-gray-100'; ?>">
            <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
            <span class="text-xs font-medium">Pengajuan</span>
        </a>
        <!-- Menu Profil -->
        <a href="profil.php" class="flex flex-col items-center justify-center text-center w-full p-2 rounded-lg transition-colors duration-200 <?php echo ($aktif == 'profil') ? 'bg-blue-50 text-blue-600' : 'text-gray-500 hover:bg-gray-100'; ?>">
            <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
            <span class="text-xs font-medium">Profil</span>
        </a>
    </div>
</nav>
