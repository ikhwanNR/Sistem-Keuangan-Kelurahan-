<?php
session_start();
require 'config.php';
require 'functions.php';
require_login();
if (!is_admin()) {
    die("Akses ditolak!");
}

$stmt = $pdo->query("SELECT * FROM users ORDER BY nama ASC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kelola User - SIKEURAHAN</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    body {
        font-family: 'Poppins', sans-serif;
        margin: 0;
        background: linear-gradient(135deg, #3b82f6, #60a5fa);
        color: #333;
        display: flex;
        flex-direction: column;
        min-height: 100vh;
    }
    .navbar { background-color: rgba(30, 58, 138, 0.9) !important; }
    .navbar-brand, .navbar-text, .navbar .btn { color: #fff !important; }
    .main-content { padding: 80px 15px; flex: 1; }
    .card-custom {
        background-color: #fff;
        border-radius: 15px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        padding: 20px;
        margin-bottom: 20px;
    }
    .btn-success { background-color: #22c55e; border: none; }
    .btn-success:hover { background-color: #15803d; }
    .btn-primary { background-color: #2563eb; border: none; }
    .btn-primary:hover { background-color: #1d4ed8; }
    .btn-danger { background-color: #ef4444; border: none; }
    .btn-danger:hover { background-color: #b91c1c; }
    table th, table td { vertical-align: middle; }
    table thead { background-color: #1e40af; color: #fff; }
    .nav-link.active { font-weight: 600; color: #fff; background-color: #2563eb; border-radius: 5px; }
    footer {
        background: rgba(30, 58, 138, 0.95);
        color: #fff;
        text-align: center;
        padding: 15px 0;
        font-size: 14px;
        margin-top: auto;
    }
</style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark fixed-top shadow-sm" style="background: rgba(30, 58, 138, 0.95);">
  <div class="container">
    <!-- Brand -->
    <a class="navbar-brand fw-bold" href="dashboard.php">
      <i class="fas fa-coins me-1"></i> SIKEURAHAN
    </a>

    <!-- Toggle button -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMenu">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Menu -->
    <div class="collapse navbar-collapse" id="navbarMenu">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link <?= basename($_SERVER['PHP_SELF'])=='dashboard.php'?'active':'' ?>" href="dashboard.php">
            <i class="fas fa-home"></i> Dashboard
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= basename($_SERVER['PHP_SELF'])=='transaksi.php'?'active':'' ?>" href="transaksi.php">
            <i class="fas fa-exchange-alt"></i> Transaksi
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= basename($_SERVER['PHP_SELF'])=='kategori.php'?'active':'' ?>" href="kategori.php">
            <i class="fas fa-tags"></i> Kategori
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= basename($_SERVER['PHP_SELF'])=='laporan.php'?'active':'' ?>" href="laporan.php">
            <i class="fas fa-chart-bar"></i> Laporan
          </a>
        </li>

        <?php if (is_admin()): ?>
        <li class="nav-item">
          <a class="nav-link <?= basename($_SERVER['PHP_SELF'])=='users.php'?'active':'' ?>" href="users.php">
            <i class="fas fa-users"></i> User
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?= basename($_SERVER['PHP_SELF'])=='log.php'?'active':'' ?>" href="log.php">
            <i class="fas fa-clipboard-list"></i> Log
          </a>
        </li>
        <?php endif; ?>
      </ul>

      <!-- User Info + Logout -->
      <div class="d-flex align-items-center">
        <span class="navbar-text text-white me-3">
          Selamat Datang, <strong><?= htmlspecialchars($_SESSION['nama'] ?? 'Pengguna') ?></strong>
        </span>
        <a class="btn btn-outline-light btn-sm" href="logout.php">
          <i class="fas fa-sign-out-alt"></i> Logout
        </a>
      </div>
    </div>
  </div>
</nav>

<div class="container main-content">
    <div class="mb-4 text-center">
        <h2 class="text-white fw-bold">Kelola User</h2>
        <a href="user_add.php" class="btn btn-success mt-3">
            <i class="fas fa-plus"></i> Tambah User
        </a>
    </div>

    <div class="card card-custom">
        <div class="table-responsive">
            <table class="table table-striped table-bordered mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Nama</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($users): foreach($users as $index=>$user): ?>
                        <tr>
                            <td><?= $index+1 ?></td>
                            <td><?= htmlspecialchars($user['nama']) ?></td>
                            <td><?= htmlspecialchars($user['username']) ?></td>
                            <td><?= htmlspecialchars($user['role']) ?></td>
                            <td>
                                <a href="user_edit.php?id=<?= $user['id_user'] ?>" class="btn btn-primary btn-sm"><i class="fas fa-edit"></i> Edit</a>
                                <a href="user_delete.php?id=<?= $user['id_user'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus user ini?')"><i class="fas fa-trash-alt"></i> Hapus</a>
                            </td>
                        </tr>
                    <?php endforeach; else: ?>
                        <tr><td colspan="5" class="text-center">Belum ada user.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Footer -->
<footer>
&copy; <?= date('Y') ?> SIKEURAHAN | Sistem Keuangan Kelurahan
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
