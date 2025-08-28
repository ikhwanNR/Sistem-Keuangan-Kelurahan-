<?php
session_start();
require 'config.php';
require 'functions.php';

require_login();
if (!is_admin()) {
    die("Akses ditolak!");
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: users.php");
    exit;
}

// Cek user yang akan dihapus
$stmt = $pdo->prepare("SELECT * FROM users WHERE id_user = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();
if (!$user) {
    die("User tidak ditemukan.");
}

// Cegah admin menghapus dirinya sendiri
if ($user['id_user'] == $_SESSION['id_user']) {
    die("Anda tidak bisa menghapus akun Anda sendiri.");
}

// Hapus user
$stmt = $pdo->prepare("DELETE FROM users WHERE id_user = ?");
$stmt->execute([$id]);

// Redirect kembali ke halaman users
header("Location: users.php");
exit;
?>
