<?php
session_start();
require_once 'config.php';
require_once 'functions.php';
require_login();

if (!is_admin()) {
    die("Akses ditolak!");
}

// Tambah Kategori
if (isset($_POST['tambah'])) {
    $nama_kategori = trim($_POST['nama_kategori']);
    if ($nama_kategori !== '') {
        $stmt = $pdo->prepare("INSERT INTO kategori (nama_kategori) VALUES (?)");
        $stmt->execute([$nama_kategori]);
        catat_log($pdo, $_SESSION['user_id'], "Menambahkan kategori '$nama_kategori'");
        header("Location: kategori.php");
        exit;
    }
}

// Hapus Kategori
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $stmt = $pdo->prepare("SELECT nama_kategori FROM kategori WHERE id_kategori = ?");
    $stmt->execute([$id]);
    $kategori_hapus = $stmt->fetchColumn();

    $stmt = $pdo->prepare("DELETE FROM kategori WHERE id_kategori = ?");
    $stmt->execute([$id]);
    catat_log($pdo, $_SESSION['user_id'], "Menghapus kategori '$kategori_hapus'");

    header("Location: kategori.php");
    exit;
}

// Edit Kategori
if (isset($_POST['edit'])) {
    $id = $_POST['id_kategori'];
    $nama_kategori = trim($_POST['nama_kategori']);
    if ($nama_kategori !== '') {
        $stmt = $pdo->prepare("SELECT nama_kategori FROM kategori WHERE id_kategori = ?");
        $stmt->execute([$id]);
        $kategori_lama = $stmt->fetchColumn();

        $stmt = $pdo->prepare("UPDATE kategori SET nama_kategori = ? WHERE id_kategori = ?");
        $stmt->execute([$nama_kategori, $id]);
        catat_log($pdo, $_SESSION['user_id'], "Mengubah kategori '$kategori_lama' menjadi '$nama_kategori'");

        header("Location: kategori.php");
        exit;
    }
}

// Ambil Data Kategori
$stmt = $pdo->query("SELECT * FROM kategori ORDER BY id_kategori DESC");
$kategori = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kategori - SIKEURAHAN</title>
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
    .main-content { padding: 100px 15px 50px; flex: 1; }
    .card-custom {
        background-color: #fff;
        border-radius: 15px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        margin-bottom: 20px;
        padding: 20px;
    }
    .btn-primary { background-color: #2563eb; border: none; }
    .btn-primary:hover { background-color: #1d4ed8; }
    .btn-success { background-color: #22c55e; border: none; }
    .btn-success:hover { background-color: #15803d; }
    .btn-danger { background-color: #ef4444; border: none; }
    .btn-danger:hover { background-color: #b91c1c; }
    table th, table td { vertical-align: middle; }
    table thead { background-color: #1e40af; color: #fff; }
    footer {
        background-color: rgba(30, 58, 138, 0.95);
        color: #fff;
        text-align: center;
        padding: 15px 0;
        font-size: 0.9rem;
        margin-top: auto;
    }
</style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark fixed-top shadow-sm">
  <div class="container">
    <a class="navbar-brand fw-bold" href="dashboard.php">
      <i class="fas fa-coins me-1"></i> SIKEURAHAN
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMenu">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarMenu">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link <?= basename($_SERVER['PHP_SELF'])=='dashboard.php'?'active':'' ?>" href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
        <li class="nav-item"><a class="nav-link <?= basename($_SERVER['PHP_SELF'])=='transaksi.php'?'active':'' ?>" href="transaksi.php"><i class="fas fa-exchange-alt"></i> Transaksi</a></li>
        <li class="nav-item"><a class="nav-link <?= basename($_SERVER['PHP_SELF'])=='kategori.php'?'active':'' ?>" href="kategori.php"><i class="fas fa-tags"></i> Kategori</a></li>
        <li class="nav-item"><a class="nav-link <?= basename($_SERVER['PHP_SELF'])=='laporan.php'?'active':'' ?>" href="laporan.php"><i class="fas fa-chart-bar"></i> Laporan</a></li>
        <?php if (is_admin()): ?>
        <li class="nav-item"><a class="nav-link <?= basename($_SERVER['PHP_SELF'])=='users.php'?'active':'' ?>" href="users.php"><i class="fas fa-users"></i> User</a></li>
        <li class="nav-item"><a class="nav-link <?= basename($_SERVER['PHP_SELF'])=='log.php'?'active':'' ?>" href="log.php"><i class="fas fa-clipboard-list"></i> Log</a></li>
        <?php endif; ?>
      </ul>
      <div class="d-flex align-items-center">
        <span class="navbar-text text-white me-3">Selamat Datang, <strong><?= htmlspecialchars($_SESSION['nama'] ?? 'Pengguna') ?></strong></span>
        <a class="btn btn-outline-light btn-sm" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
      </div>
    </div>
  </div>
</nav>

<div class="container main-content">
    <h2 class="mb-4 text-center fw-bold text-white" style="text-shadow: 2px 2px 6px rgba(0,0,0,0.4); letter-spacing: 1px;">
        Kelola Kategori 
    </h2>

    <!-- Form Tambah -->
    <div class="card card-custom">
        <div class="card-header card-header-primary">Tambah Kategori</div>
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <label for="nama_kategori" class="form-label">Nama Kategori</label>
                    <input type="text" name="nama_kategori" id="nama_kategori" class="form-control" required>
                </div>
                <button type="submit" name="tambah" class="btn btn-success"><i class="fas fa-plus me-2"></i> Tambah</button>
            </form>
        </div>
    </div>

    <!-- Tabel Data -->
    <div class="card card-custom">
        <div class="card-header card-header-secondary">Daftar Kategori</div>
        <div class="card-body">
            <table class="table table-striped table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th width="60">No</th>
                        <th>Nama Kategori</th>
                        <th width="180">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(count($kategori)>0): $no=1; foreach($kategori as $k): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= htmlspecialchars($k['nama_kategori']) ?></td>
                        <td>
                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?= $k['id_kategori'] ?>"><i class="fas fa-edit"></i> Edit</button>
                            <a href="kategori.php?hapus=<?= $k['id_kategori'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin hapus kategori ini?')"><i class="fas fa-trash"></i> Hapus</a>
                        </td>
                    </tr>

                    <!-- Modal Edit -->
                    <div class="modal fade" id="editModal<?= $k['id_kategori'] ?>" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="POST">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Edit Kategori</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <input type="hidden" name="id_kategori" value="<?= $k['id_kategori'] ?>">
                                        <div class="mb-3">
                                            <label class="form-label">Nama Kategori</label>
                                            <input type="text" name="nama_kategori" class="form-control" value="<?= htmlspecialchars($k['nama_kategori']) ?>" required>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" name="edit" class="btn btn-primary">Simpan</button>
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; else: ?>
                    <tr><td colspan="3" class="text-center">Belum ada kategori.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<footer>
    <div class="container">
        &copy; <?= date('Y') ?> SIKEURAHAN | Sistem Keuangan Kelurahan
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
