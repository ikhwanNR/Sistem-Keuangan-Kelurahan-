<?php
session_start(); // Mulai session
require 'config.php';
require 'functions.php'; // Pastikan ada fungsi catat_log()

// Catat aktivitas logout sebelum session dihapus
if (isset($_SESSION['user_id'])) {
    catat_log($pdo, $_SESSION['user_id'], "Logout dari sistem");
}

// Hapus semua data session
session_unset();
session_destroy();

// Arahkan kembali ke halaman login
header("Location: login.php");
exit;
