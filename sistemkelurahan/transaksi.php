<?php
session_start();
require_once 'config.php';
require_once 'functions.php';
require_login();

// Ambil kategori
$stmt = $pdo->query("SELECT * FROM kategori ORDER BY nama_kategori ASC");
$kategori = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ambil data user login
$stmt = $pdo->prepare("SELECT * FROM users WHERE id_user = ?");
$stmt->execute([$_SESSION['user_id']]);
$current_user = $stmt->fetch(PDO::FETCH_ASSOC);

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_user = $_SESSION['user_id'];
    $id_kategori = $_POST['id_kategori'] ?? '';
    $tanggal = $_POST['tanggal'] ?? '';
    $jumlah = $_POST['jumlah'] ?? '';
    $deskripsi = $_POST['deskripsi'] ?? '';
    $jenis_transaksi = $_POST['jenis_transaksi'] ?? '';

    // Hilangkan format rupiah sebelum simpan
    $jumlah = preg_replace('/[^0-9]/', '', $jumlah);

    if ($id_kategori === '' || $tanggal === '' || $jumlah === '' || $jenis_transaksi === '') {
        $errors[] = "Semua field wajib diisi!";
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("
            INSERT INTO transaksi (id_user, id_kategori, tanggal, jumlah, deskripsi, jenis_transaksi, created_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");

        catat_log($pdo, $_SESSION['user_id'], "Menambah transaksi ($jenis_transaksi) sebesar Rp $jumlah");

        $stmt->execute([$id_user, $id_kategori, $tanggal, $jumlah, $deskripsi, $jenis_transaksi]);

        header("Location: transaksi.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tambah Transaksi - SIKEURAHAN</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    body { 
        font-family: 'Poppins', sans-serif; 
        background: linear-gradient(135deg, #3b82f6, #60a5fa); 
        margin: 0; 
        display: flex;
        flex-direction: column;
        min-height: 100vh;
    }
    .navbar { background-color: rgba(30, 58, 138, 0.95) !important; }
    .navbar-brand { font-weight: 700; color: #fff !important; }
    .nav-link { color: #e5e7eb !important; }
    .nav-link.active, .nav-link:hover { color: #fff !important; font-weight: 600; }

    .main-content { 
        flex: 1;
        padding: 50px 15px; 
        display: flex; 
        justify-content: center; 
        margin-top: 80px;
    }

    .card-form { 
        width: 100%; 
        max-width: 600px; 
        background-color: #fff; 
        border-radius: 15px; 
        padding: 30px; 
        box-shadow: 0 8px 25px rgba(0,0,0,0.15); 
    }
    .btn-primary { background-color: #2563eb; }
    .btn-primary:hover { background-color: #1d4ed8; }
    .btn-secondary { background-color: #3b82f6; color: #fff; }
    .btn-secondary:hover { background-color: #1e40af; color: #fff; }
    .btn-success { background-color: #22c55e; }
    .btn-success:hover { background-color: #15803d; }

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
        <li class="nav-item"><a class="nav-link <?= basename($_SERVER['PHP_SELF'])=='dashboard.php'?'active':'' ?>" href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
        <li class="nav-item"><a class="nav-link <?= basename($_SERVER['PHP_SELF'])=='transaksi.php'?'active':'' ?>" href="transaksi.php"><i class="fas fa-exchange-alt"></i> Transaksi</a></li>
        <?php if (is_admin()): ?>
        <li class="nav-item"><a class="nav-link <?= basename($_SERVER['PHP_SELF'])=='kategori.php'?'active':'' ?>" href="kategori.php"><i class="fas fa-tags"></i> Kategori</a></li>
        <?php endif; ?>
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

<!-- MAIN CONTENT -->
<div class="container main-content">
    <div class="card-form">
        <h3 class="mb-4">Tambah Transaksi</h3>

        <?php if (!empty($errors)): ?>
        <div class="alert alert-danger"><?= implode("<br>", $errors) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">User</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($current_user['nama']) ?>" disabled>
                <input type="hidden" name="id_user" value="<?= htmlspecialchars($current_user['id_user']) ?>">
            </div>

            <div class="mb-3">
                <label for="id_kategori" class="form-label">Kategori</label>
                <select name="id_kategori" id="id_kategori" class="form-select" required>
                    <option value="">-- Pilih Kategori --</option>
                    <?php foreach($kategori as $k): ?>
                    <option value="<?= $k['id_kategori'] ?>"><?= htmlspecialchars($k['nama_kategori']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="tanggal" class="form-label">Tanggal</label>
                <input type="date" name="tanggal" id="tanggal" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="jumlah" class="form-label">Jumlah (Rp)</label>
                <input type="text" name="jumlah" id="jumlah" class="form-control" placeholder="Masukkan jumlah" required>
            </div>

            <div class="mb-3">
                <label for="jenis_transaksi" class="form-label">Jenis Transaksi</label>
                <select name="jenis_transaksi" id="jenis_transaksi" class="form-select" required>
                    <option value="">-- Pilih Jenis --</option>
                    <option value="pemasukan">Pemasukan</option>
                    <option value="pengeluaran">Pengeluaran</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="deskripsi" class="form-label">Deskripsi</label>
                <textarea name="deskripsi" id="deskripsi" class="form-control" rows="3"></textarea>
            </div>

            <button type="submit" class="btn btn-success"><i class="fas fa-save me-2"></i> Simpan</button>
            <button type="button" class="btn btn-secondary" onclick="history.back()"><i class="fas fa-arrow-left me-2"></i> Kembali</button>
        </form>
    </div>
</div>

<!-- FOOTER -->
<footer>
    <div class="container">
        &copy; <?= date('Y') ?> SIKEURAHAN | Sistem Keuangan Kelurahan
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Format input ke Rupiah
    const jumlahInput = document.getElementById('jumlah');
    jumlahInput.addEventListener('input', function() {
        let value = this.value.replace(/[^0-9]/g, '');
        if (value) {
            this.value = formatRupiah(value);
        } else {
            this.value = '';
        }
    });

    function formatRupiah(angka) {
        let reverse = angka.toString().split('').reverse().join('');
        let ribuan = reverse.match(/\d{1,3}/g);
        return 'Rp ' + ribuan.join('.').split('').reverse().join('');
    }
</script>
</body>
</html>
