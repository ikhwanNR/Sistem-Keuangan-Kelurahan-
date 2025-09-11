<?php
session_start();
require 'config.php';
require 'functions.php';
require_login();

if (!is_admin()) {
    die("Akses ditolak!");
}

// Proses hapus log satuan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['hapus_id'])) {
    $hapus_id = (int)$_POST['hapus_id'];
    $stmt = $pdo->prepare("DELETE FROM log_aktivitas WHERE id_log = ?");
    $stmt->execute([$hapus_id]);
    header("Location: log.php");
    exit;
}

// Proses hapus semua log
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['hapus_semua'])) {
    $pdo->query("TRUNCATE TABLE log_aktivitas");
    header("Location: log.php");
    exit;
}

// Ambil data log
$stmt = $pdo->query("
    SELECT l.*, u.nama 
    FROM log_aktivitas l
    LEFT JOIN users u ON l.id_user = u.id_user
    ORDER BY l.waktu DESC
");
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Log Aktivitas - SIKEURAHAN</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    body {
        font-family: 'Poppins', sans-serif;
        background: linear-gradient(135deg, #3b82f6, #60a5fa);
        min-height: 100vh;
        padding-top: 80px;
        display: flex;
        flex-direction: column;
    }
    .card-custom {
        background-color: #fff;
        border-radius: 15px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        padding: 30px;
        margin: auto;
    }
    h2 {
        color: #fff;
        text-align: center;
        margin-bottom: 30px;
    }
    .btn-danger {
        background-color: #ef4444;
        border: none;
    }
    .btn-danger:hover {
        background-color: #b91c1c;
    }
    .table th, .table td {
        vertical-align: middle;
    }
    footer {
        background: rgba(30, 58, 138, 0.95);
        color: #fff;
        text-align: center;
        padding: 15px 0;
        margin-top: auto;
    }
</style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark fixed-top shadow-sm" style="background: rgba(30, 58, 138, 0.95);">
  <div class="container">
    <a class="navbar-brand fw-bold" href="dashboard.php">
      <i class="fas fa-coins me-1"></i> SIKEURAHAN
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMenu">
      <span class="navbar-toggler-icon"></span>
    </button>
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

<!-- MAIN CONTENT -->
<div class="container py-4">
    <h2>Log Aktivitas</h2>
    <div class="card card-custom">
        <div class="mb-3 d-flex justify-content-end">
            <?php if ($logs): ?>
            <form method="post" onsubmit="return confirm('Yakin ingin menghapus semua log?');">
                <button type="submit" name="hapus_semua" class="btn btn-danger">
                    <i class="fas fa-trash-alt"></i> Hapus Semua
                </button>
            </form>
            <?php endif; ?>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-bordered text-center align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>No</th>
                        <th>User</th>
                        <th>Aktivitas</th>
                        <th>Waktu</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($logs): ?>
                        <?php foreach ($logs as $i => $log): ?>
                            <tr>
                                <td><?= $i+1 ?></td>
                                <td><?= htmlspecialchars($log['nama'] ?? 'Tidak diketahui') ?></td>
                                <td><?= htmlspecialchars($log['aktivitas']) ?></td>
                                <td><?= date('d-m-Y H:i:s', strtotime($log['waktu'])) ?></td>
                                <td>
                                    <form method="post" onsubmit="return confirm('Yakin ingin menghapus log ini?');">
                                        <input type="hidden" name="hapus_id" value="<?= $log['id_log'] ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i> Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5">Belum ada log aktivitas.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- FOOTER -->
<footer>
    <div class="container">
        <p class="mb-0">&copy; <?= date('Y') ?> SIKEURAHAN | Sistem Keuangan Kelurahan
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
