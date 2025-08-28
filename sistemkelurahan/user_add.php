<?php
session_start();
require 'config.php';
require 'functions.php';

require_login();
if (!is_admin()) {
    die("Akses ditolak!");
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $role = $_POST['role'] ?? 'user';

    if (!$nama) $errors[] = "Nama wajib diisi.";
    if (!$username) $errors[] = "Username wajib diisi.";
    if (!$password) $errors[] = "Password wajib diisi.";

    if (empty($errors)) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (nama, username, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nama, $username, $hashed, $role]);
        header("Location: users.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tambah User - SIKEURAHAN</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    body {
        font-family: 'Poppins', sans-serif;
        background: linear-gradient(135deg, #3b82f6, #60a5fa);
        color: #333;
        min-height: 100vh;
        padding-top: 80px;
    }
    .logo-container {
        text-align: center;
        margin-bottom: 20px;
    }
    .logo-user {
        width: 130px;
        height: auto;
        border-radius: 50%; /* Lingkaran */
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    }
    h1.page-title {
        color: #fff;
        text-align: center;
        font-weight: 700;
        margin-bottom: 40px;
        text-shadow: 1px 2px 4px rgba(0,0,0,0.3);
    }
    .card-custom {
        background-color: #fff;
        border-radius: 15px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        padding: 30px;
        max-width: 600px;
        margin: auto;
    }
    h2 {
        color: #3b82f6;
        text-align: center;
        margin-bottom: 30px;
        font-weight: 600;
    }
    .btn-success { background-color: #22c55e; border: none; }
    .btn-success:hover { background-color: #15803d; }
    .btn-secondary { background-color: #6b7280; border: none; color: #fff; }
    .btn-secondary:hover { background-color: #4b5563; }
</style>
</head>
<body>

<!-- Logo User di atas Judul -->
<div class="logo-container">
    <img src="user.jpg" alt="Logo User" class="logo-user">
</div>

<!-- Judul besar di atas card -->
<h1 class="page-title">Manajemen User - Tambah User</h1>

<div class="card card-custom">
    <h2>Form Tambah User</h2>
    <?php if ($errors): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach ($errors as $e) echo "<li>$e</li>"; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post">
        <div class="mb-3">
            <label class="form-label">Nama</label>
            <input type="text" name="nama" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Username</label>
            <input type="text" name="username" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Role</label>
            <select name="role" class="form-select">
                <option value="user">User</option>
                <option value="admin">Admin</option>
            </select>
        </div>
        <div class="d-flex justify-content-between">
            <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Simpan</button>
            <a href="users.php" class="btn btn-secondary"><i class="fas fa-times"></i> Batal</a>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
</body>
</html>
