<?php
// Mulai sesi untuk mengaksesnya
session_start();

// Hapus semua variabel sesi
$_SESSION = array();

// Hancurkan sesi
session_destroy();

// Redirect ke halaman login
header("location: index.php");
exit;
?>
