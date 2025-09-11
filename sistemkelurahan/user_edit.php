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

// Ambil data user
$stmt = $pdo->prepare("SELECT * FROM users WHERE id_user = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();
if (!$user) {
    die("User tidak ditemukan.");
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $role = $_POST['role'] ?? 'user';

    if (!$nama) $errors[] = "Nama wajib diisi.";
    if (!$username) $errors[] = "Username wajib diisi.";

    if (empty($errors)) {
        if ($password) {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET nama=?, username=?, password=?, role=? WHERE id_user=?");
            $stmt->execute([$nama, $username, $hashed, $role, $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET nama=?, username=?, role=? WHERE id_user=?");
            $stmt->execute([$nama, $username, $role, $id]);
        }
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
  <title>Edit User - SIKEURAHAN</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    body {
      font-family:'Poppins',sans-serif;
      margin:0;
      background:linear-gradient(135deg,#3b82f6,#60a5fa);
      min-height:100vh;
      display:flex;
      flex-direction:column;
    }
    .navbar {background-color:rgba(30,58,138,0.9)!important;}
    .navbar-brand,.navbar-text,.navbar .btn {color:#fff!important;}
    .main-content {flex:1;display:flex;justify-content:center;align-items:center;padding:20px;}
    .card-custom {
      border-radius:15px;
      box-shadow:0 6px 20px rgba(0,0,0,0.15);
      width:100%;
      max-width:500px;
    }
    footer {
      background-color:rgba(30,58,138,0.9);
      color:#fff;
      text-align:center;
      padding:10px;
      font-size:14px;
      margin-top:auto;
    }
  </style>
</head>
<body>
<nav class="navbar navbar-expand-lg">
  <div class="container">
    <a class="navbar-brand fw-bold" href="dashboard.php">SIKEURAHAN</a>
    <div class="ms-auto">
      <a class="btn btn-outline-light btn-sm" href="logout.php">
        <i class="fas fa-sign-out-alt"></i> Logout
      </a>
    </div>
  </div>
</nav>

<div class="main-content">
  <div class="card card-custom p-4 bg-white">
    <h3 class="text-center mb-4 fw-bold text-primary">Edit User</h3>

    <?php if ($errors): ?>
      <div class="alert alert-danger">
        <ul class="mb-0">
          <?php foreach ($errors as $e) echo "<li>$e</li>"; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form method="post">
      <div class="mb-3">
        <label class="form-label">Nama</label>
        <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($user['nama']) ?>" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Username</label>
        <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Password <small>(Kosongkan jika tidak ingin diubah)</small></label>
        <input type="password" name="password" class="form-control">
      </div>
      <div class="mb-4">
        <label class="form-label">Role</label>
        <select name="role" class="form-select">
          <option value="user" <?= $user['role']=='user'?'selected':'' ?>>User</option>
          <option value="admin" <?= $user['role']=='admin'?'selected':'' ?>>Admin</option>
        </select>
      </div>
      <div class="d-flex justify-content-between">
        <a href="users.php" class="btn btn-secondary">
          <i class="fas fa-arrow-left"></i> Batal
        </a>
        <button type="submit" class="btn btn-primary">
          <i class="fas fa-save"></i> Update
        </button>
      </div>
    </form>
  </div>
</div>

<footer>
  &copy; <?= date('Y') ?> SIKEURAHAN | Sistem Keuangan Kelurahan
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
