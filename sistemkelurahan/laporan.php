<?php
session_start();
require_once 'config.php';
require_once 'functions.php';
require_login();

$tanggal_awal = $_GET['tanggal_awal'] ?? date('Y-m-01');
$tanggal_akhir = $_GET['tanggal_akhir'] ?? date('Y-m-t');

$stmt = $pdo->prepare("
    SELECT t.*, k.nama_kategori, u.nama AS nama_user
    FROM transaksi t
    JOIN kategori k ON t.id_kategori = k.id_kategori
    JOIN users u ON t.id_user = u.id_user
    WHERE t.tanggal BETWEEN ? AND ?
    ORDER BY t.tanggal ASC
");
$stmt->execute([$tanggal_awal, $tanggal_akhir]);
$transaksi = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT SUM(jumlah) FROM transaksi WHERE jenis_transaksi='pemasukan' AND tanggal BETWEEN ? AND ?");
$stmt->execute([$tanggal_awal, $tanggal_akhir]);
$total_pemasukan = $stmt->fetchColumn() ?? 0;

$stmt = $pdo->prepare("SELECT SUM(jumlah) FROM transaksi WHERE jenis_transaksi='pengeluaran' AND tanggal BETWEEN ? AND ?");
$stmt->execute([$tanggal_awal, $tanggal_akhir]);
$total_pengeluaran = $stmt->fetchColumn() ?? 0;

$saldo = $total_pemasukan - $total_pengeluaran;
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Laporan Keuangan</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    body {
        font-family: 'Poppins', sans-serif;
        margin: 0;
        background: linear-gradient(135deg, #3b82f6, #60a5fa);
        color: #333;
        min-height: 100vh;
        display: flex;
        flex-direction: column;
    }
    .navbar { background-color: rgba(30, 58, 138, 0.9) !important; }
    .navbar-brand, .navbar-text, .navbar .btn { color: #fff !important; }

    /* Main content harus fleksibel biar footer di bawah */
    .main-content { 
        flex: 1;
        padding: 100px 15px 50px;
    }

    /* CARD RINGKASAN */
    .summary-card {
        border-radius: 20px;
        padding: 20px;
        text-align: center;
        color: #fff;
        backdrop-filter: blur(15px);
        background: rgba(255, 255, 255, 0.15);
        border: 1px solid rgba(255,255,255,0.2);
        box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .summary-card:hover {
        transform: translateY(-6px) scale(1.03);
        box-shadow: 0 12px 35px rgba(0,0,0,0.3);
    }
    .summary-card h5 {
        font-weight: 700;
        margin-bottom: 10px;
        text-shadow: 0 3px 6px rgba(0,0,0,0.3);
    }
    .summary-card p {
        font-size: 1.8rem;
        font-weight: 800;
        margin: 0;
        text-shadow: 0 4px 10px rgba(0,0,0,0.4);
    }

    .income-card {
        background: linear-gradient(135deg, rgba(34,197,94,0.85), rgba(16,185,129,0.85));
    }
    .expense-card {
        background: linear-gradient(135deg, rgba(239,68,68,0.85), rgba(220,38,38,0.85));
    }
    .balance-card {
        background: linear-gradient(135deg, rgba(59,130,246,0.85), rgba(37,99,235,0.85));
    }

    .btn-primary { background-color: #2563eb; border: none; }
    .btn-primary:hover { background-color: #1d4ed8; }
    .btn-success { background-color: #22c55e; border: none; }
    .btn-success:hover { background-color: #15803d; }
    .btn-danger { background-color: #ef4444; border: none; }
    .btn-danger:hover { background-color: #b91c1c; }

    .badge-success { background-color: #22c55e; }
    .badge-danger { background-color: #ef4444; }

    table th, table td { vertical-align: middle; }
    table thead { background-color: #1e40af; color: #fff; }

    /* FOOTER */
    footer {
        background: rgba(30, 58, 138, 0.9);
        color: #fff;
        text-align: center;
        padding: 15px 0;
        font-size: 0.9rem;
        margin-top: auto;
    }
</style>
</head>
<body>

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
    <h2 class="text-center text-white mb-4 fw-bold">Laporan Keuangan</h2>
    <div class="card card-custom">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Tanggal Awal</label>
                <input type="date" name="tanggal_awal" value="<?= htmlspecialchars($tanggal_awal) ?>" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label">Tanggal Akhir</label>
                <input type="date" name="tanggal_akhir" value="<?= htmlspecialchars($tanggal_akhir) ?>" class="form-control">
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-fill"><i class="fas fa-filter me-2"></i> Filter</button>
                <a href="export_pdf.php?tanggal_awal=<?= $tanggal_awal ?>&tanggal_akhir=<?= $tanggal_akhir ?>" class="btn btn-danger flex-fill"><i class="fas fa-file-pdf me-2"></i> PDF</a>
            </div>
        </form>
    </div>

    <div class="card card-custom mt-4">
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle text-center mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Kategori</th>
                        <th>Deskripsi</th>
                        <th>Jenis</th>
                        <th>Jumlah</th>
                        <th>Petugas</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(count($transaksi)>0): foreach($transaksi as $i=>$row): ?>
                    <tr>
                        <td><?= $i+1 ?></td>
                        <td><?= htmlspecialchars($row['tanggal']) ?></td>
                        <td><?= htmlspecialchars($row['nama_kategori']) ?></td>
                        <td><?= htmlspecialchars($row['deskripsi']) ?></td>
                        <td>
                            <?php if($row['jenis_transaksi']=='pemasukan'): ?>
                                <span class="badge badge-success">Pemasukan</span>
                            <?php else: ?>
                                <span class="badge badge-danger">Pengeluaran</span>
                            <?php endif; ?>
                        </td>
                        <td>Rp <?= number_format($row['jumlah'],2,',','.') ?></td>
                        <td><?= htmlspecialchars($row['nama_user']) ?></td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr><td colspan="7" class="text-center">Tidak ada data transaksi</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-4">
            <div class="summary-card income-card">
                <h5>Total Pemasukan</h5>
                <p>Rp <?= number_format($total_pemasukan,2,',','.') ?></p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="summary-card expense-card">
                <h5>Total Pengeluaran</h5>
                <p>Rp <?= number_format($total_pengeluaran,2,',','.') ?></p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="summary-card balance-card">
                <h5>Saldo Akhir</h5>
                <p>Rp <?= number_format($saldo,2,',','.') ?></p>
            </div>
        </div>
    </div>
</div>

<!-- FOOTER -->
<footer>
    <div class="container">
        <p class="mb-0">&copy; <?= date('Y') ?> SIKEURAHAN | Sistem Keuangan Kelurahan</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
