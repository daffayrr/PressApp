<div class="space-y-8">
    <!-- Bagian Header Sambutan -->
    <div>
        <h2 class="text-3xl font-bold text-gray-800">Selamat Datang, <?php echo htmlspecialchars($_SESSION['admin_nama']); ?>!</h2>
        <p class="mt-2 text-gray-600">Berikut adalah ringkasan aktivitas sistem presensi hari ini.</p>
    </div>

    <!-- Grid untuk Kartu Statistik -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white p-6 rounded-xl shadow-md flex items-center space-x-4 border-l-4 border-blue-500">
            <div class="bg-blue-100 p-3 rounded-full"><svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg></div>
            <div><p class="text-gray-500 text-sm font-medium">Total Karyawan</p><p class="text-2xl font-bold text-gray-800"><?php echo $total_karyawan; ?></p></div>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-md flex items-center space-x-4 border-l-4 border-green-500">
            <div class="bg-green-100 p-3 rounded-full"><svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></div>
            <div><p class="text-gray-500 text-sm font-medium">Hadir Hari Ini</p><p class="text-2xl font-bold text-gray-800"><?php echo $hadir_hari_ini; ?></p></div>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-md flex items-center space-x-4 border-l-4 border-yellow-500">
            <div class="bg-yellow-100 p-3 rounded-full"><svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></div>
            <div><p class="text-gray-500 text-sm font-medium">Terlambat Hari Ini</p><p class="text-2xl font-bold text-gray-800"><?php echo $terlambat_hari_ini; ?></p></div>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-md flex items-center space-x-4 border-l-4 border-red-500">
            <div class="bg-red-100 p-3 rounded-full"><svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg></div>
            <div><p class="text-gray-500 text-sm font-medium">Pengajuan Menunggu</p><p class="text-2xl font-bold text-gray-800"><?php echo $pengajuan_menunggu; ?></p></div>
        </div>
    </div>

    <!-- Tabel Aktivitas Presensi Terkini -->
    <div class="bg-white p-6 rounded-xl shadow-md">
        <h3 class="text-xl font-semibold text-gray-800 mb-4">Aktivitas Presensi Terkini</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr><th scope="col" class="px-6 py-3">Nama Karyawan</th><th scope="col" class="px-6 py-3">Jabatan</th><th scope="col" class="px-6 py-3">Waktu</th><th scope="col" class="px-6 py-3">Tipe</th></tr>
                </thead>
                <tbody>
                    <?php if ($presensi_terkini_result->num_rows > 0): while($row = $presensi_terkini_result->fetch_assoc()): ?>
                    <tr class="bg-white border-b hover:bg-gray-50">
                        <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap"><?php echo htmlspecialchars($row['nama_lengkap']); ?></th>
                        <td class="px-6 py-4"><?php echo htmlspecialchars($row['jabatan']); ?></td>
                        <td class="px-6 py-4"><?php echo date('d M Y, H:i', strtotime($row['waktu'])); ?></td>
                        <td class="px-6 py-4"><?php if($row['tipe'] == 'masuk'): ?><span class="px-2 py-1 font-semibold leading-tight text-green-700 bg-green-100 rounded-full">Masuk</span><?php else: ?><span class="px-2 py-1 font-semibold leading-tight text-red-700 bg-red-100 rounded-full">Pulang</span><?php endif; ?></td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr class="bg-white border-b"><td colspan="4" class="px-6 py-4 text-center text-gray-500">Belum ada aktivitas presensi hari ini.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
