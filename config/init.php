<?php
// config/init.php

// 1. Mulai Sesi di sini, sekali dan untuk selamanya.
// Ini akan mencegah error "session already sent".
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 2. Panggil file database.
// Sekarang, semua file yang memanggil init.php akan otomatis mengenal fungsi connect_db().
require_once __DIR__ . '/database.php';

// 3. Fungsi Pengecekan Otentikasi
// Kita letakkan di sini agar bisa dipakai di semua halaman.
function check_admin_auth() {
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        // Ganti '/PressApp/' dengan nama folder proyek Anda jika berbeda.
        // Ini adalah path absolut dari root web server.
        header("Location: /PressApp/sistem/index.php");
        exit;
    }
}
