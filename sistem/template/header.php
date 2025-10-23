<?php
// Memulai sesi jika belum ada. Ini WAJIB ada di baris paling atas.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- PENJAGA KEAMANAN YANG DIPERBAIKI ---
// Cek apakah variabel sesi 'admin_logged_in' ada DAN nilainya true.
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // Jika tidak, paksa pengguna kembali ke halaman LOGIN (index.php).
    // Ganti '/PressApp/' dengan nama folder proyek Anda jika berbeda.
    header("Location: ../../sistem/index.php");
    exit; // Hentikan eksekusi skrip agar konten di bawah tidak ditampilkan.
}
// --- AKHIR PENJAGA KEAMANAN ---

// Definisikan Base URL agar semua link dan gambar konsisten
$base_url = '/PressApp/sistem/';

// Include file konfigurasi database
require_once '../config/database.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'PressApp'; ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="https://kendo.cdn.telerik.com/2022.3.1109/styles/kendo.default-v2.min.css">

    <style>
        :root {
            --bg-dark: #343a40; --bg-light: #f8f9fa; --bg-white: #ffffff;
            --border-color: #dee2e6; --primary-blue: #0d6efd; --primary-blue-light: #e7f1ff;
            --text-dark: #212529; --text-muted: #6c757d; --text-light: #adb5bd;
        }
        body, html { margin: 0; padding: 0; height: 100%; font-family: 'Roboto', sans-serif; background-color: var(--bg-dark); color: var(--text-dark); }
        .app-container { display: flex; flex-direction: column; height: 100vh; }
        .app-header { display: flex; justify-content: space-between; align-items: center; background-color: var(--bg-white); padding: 18px 24px; border-bottom: 1px solid var(--border-color); flex-shrink: 0; }
        .logo-container { display: flex; align-items: center; }
        .logo-img { height: 40px; src: 'logo.png';}
        .header-extra { display: flex; align-items: center; gap: 20px; }
        .main-body { display: flex; flex-grow: 1; overflow: hidden; }
        .sidebar { width: 250px; background-color: var(--bg-white); padding-top: 16px; border-right: 1px solid var(--border-color); flex-shrink: 0; overflow-y: auto; }
        .sidebar ul { list-style-type: none; padding: 0; margin: 0; }
        .sidebar ul li a { display: flex; align-items: center; padding: 12px 24px; text-decoration: none; color: var(--text-muted); font-size: 0.9rem; font-weight: 500; transition: all 0.2s ease-in-out; border-radius: 6px; margin: 2px 12px; }
        .sidebar ul li a i { width: 20px; margin-right: 15px; font-size: 1rem; text-align: center; }
        .sidebar ul li.active > a { background-color: var(--primary-blue-light); color: var(--primary-blue); font-weight: 700; }
        .sidebar ul li a:hover { background-color: #f1f1f1; }
        .menu-header { padding: 15px 24px 5px; color: #999; font-size: 0.8em; font-weight: bold; text-transform: uppercase; }
        .main-content { flex-grow: 1; padding: 24px; background-color: var(--bg-light); overflow-y: auto; }
        .content-panel { background-color: var(--bg-white); padding: 24px; border-radius: 6px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        .app-footer { text-align: center; padding: 15px; background-color: var(--bg-dark); color: var(--text-light); font-size: 0.8rem; flex-shrink: 0; }
    </style>
    
</head>
<body>
    <div class="app-container">
        <header class="app-header">
            <div class="logo-container">
                <img src="http://localhost/_Tekkomdik/PressApp/assets/images/logo.png" alt="PressApp Logo" class="logo-img">
            </div>
            <div class="header-extra">
                <span id="live-clock">-- : -- : --</span>
                <!-- <span>Halo, </span> -->
            </div>
        </header>
        <div class="main-body">
