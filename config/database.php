<?php
// DEFINISIKAN URL DASAR APLIKASI ANDA
// Sesuaikan 'http://localhost/sistem/' dengan URL folder 'sistem' Anda.
define('BASE_URL', 'http://localhost/_Tekkomdik/PressApp/sistem/');
// config/database.php

/**
 * Fungsi ini HANYA untuk membuat dan mengembalikan koneksi database.
 */
function connect_db() {
    $db_host = 'localhost'; 
    $db_user = 'root';      
    $db_pass = 'abc_123';          
    $db_name = 'pressapp_db'; 

    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

    if ($conn->connect_error) {
        die("Koneksi ke database gagal: " . $conn->connect_error);
    }

    return $conn;
}
