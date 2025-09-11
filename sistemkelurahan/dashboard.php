<?php
session_start();
require_once 'config.php';
require_once 'functions.php';
require_login();

// Hitung total pemasukan
$stmt = $pdo->query("SELECT SUM(jumlah) AS total FROM transaksi WHERE jenis_transaksi='pemasukan'");
$total_pemasukan = $stmt->fetch()['total'] ?? 0;

// Hitung total pengeluaran
$stmt = $pdo->query("SELECT SUM(jumlah) AS total FROM transaksi WHERE jenis_transaksi='pengeluaran'");
$total_pengeluaran = $stmt->fetch()['total'] ?? 0;

// Hitung saldo
$saldo = ($total_pemasukan ?? 0) - ($total_pengeluaran ?? 0);

// Ambil tahun sekarang
$tahun_sekarang = date("Y");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard SIKEURAHAN</title>
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
        .navbar-brand { font-weight: 700; color: #fff !important; }
        .navbar-text, .navbar .btn { color: #fff !important; }
        .main-content { flex: 1; padding: 50px 15px; }
        .card-custom { border-radius: 15px; box-shadow: 0 6px 20px rgba(0,0,0,0.15); transition: transform 0.3s ease, box-shadow 0.3s ease; text-align: center; }
        .card-custom:hover { transform: translateY(-3px); box-shadow: 0 12px 25px rgba(0,0,0,0.25); }
        .card-title-icon { font-size: 1.5rem; margin-right: 8px; }
        .card-income { background-color: #dbeafe; color: #1e40af; }
        .card-expense { background-color: #fee2e2; color: #991b1b; }
        .card-balance { background-color: #dbeafe; color: #1e3a8a; }
        .btn-menu { font-weight: 600; padding: 12px 20px; border-radius: 12px; display: flex; align-items: center; justify-content: center; transition: background-color 0.3s, transform 0.2s; box-shadow: 0 4px 12px rgba(0,0,0,0.1); text-decoration: none; color: #fff; }
        .btn-menu:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(0,0,0,0.2); text-decoration: none; }
        .btn-primary { background-color: #2563eb; } .btn-primary:hover { background-color: #1d4ed8; }
        .btn-secondary { background-color: #3b82f6; } .btn-secondary:hover { background-color: #1e40af; }
        .btn-info { background-color: #60a5fa; color: #fff; } .btn-info:hover { background-color: #2563eb; color: #fff; }
        .btn-warning { background-color: #fbbf24; color: #fff; } .btn-warning:hover { background-color: #d97706; color: #fff; }
        .btn-success { background-color: #22c55e; } .btn-success:hover { background-color: #15803d; }

        /* Grafik */
        .chart-container { max-width: 700px; margin: 0 auto 100px auto; background: #fff; padding: 20px; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.15); }
        canvas { max-height: 270px; }

        /* Footer */
        footer {
            background-color: rgba(30, 58, 138, 0.9);
            color: #fff;
            text-align: center;
            padding: 15px 10px;
            font-size: 14px;
            margin-top: auto;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg">
    <div class="container">
        <a class="navbar-brand" href="#">SIKEURAHAN</a>
        <div class="ms-auto d-flex align-items-center">
            <span class="navbar-text me-3 d-none d-lg-block">
                Selamat Datang, <strong><?= htmlspecialchars($_SESSION['nama'] ?? 'Pengguna') ?></strong>
            </span>
            <a class="btn btn-outline-light" href="logout.php">Logout <i class="fas fa-sign-out-alt"></i></a>
        </div>
    </div>
</nav>

<div class="container main-content">
    <div class="text-center mb-5 text-white">
        <h2 class="fw-bold">Dashboard Keuangan Kelurahan</h2>
        <p>Ringkasan kondisi keuangan SIKEURAHAN.</p>
    </div>

    <div class="row g-4 mb-5 justify-content-center">
        <div class="col-lg-4 col-md-6 col-sm-10">
            <div class="card card-custom card-income">
                <div class="card-body">
                    <h5 class="card-title d-flex align-items-center mb-3"><i class="fas fa-arrow-down-left card-title-icon"></i> Total Pemasukan</h5>
                    <p class="card-text fs-2 fw-bold">Rp <?= number_format($total_pemasukan, 2, ',', '.') ?></p>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 col-sm-10">
            <div class="card card-custom card-expense">
                <div class="card-body">
                    <h5 class="card-title d-flex align-items-center mb-3"><i class="fas fa-arrow-up-right card-title-icon"></i> Total Pengeluaran</h5>
                    <p class="card-text fs-2 fw-bold">Rp <?= number_format($total_pengeluaran, 2, ',', '.') ?></p>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 col-sm-10">
            <div class="card card-custom card-balance">
                <div class="card-body">
                    <h5 class="card-title d-flex align-items-center mb-3"><i class="fas fa-wallet card-title-icon"></i> Saldo Akhir</h5>
                    <p class="card-text fs-2 fw-bold">Rp <?= number_format($saldo, 2, ',', '.') ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Grafik -->
    <div class="chart-container mb-5">
        <h5 class="text-center mb-3">Grafik Keuangan <?= $tahun_sekarang; ?></h5>
        <canvas id="financeChart"></canvas>
    </div>

    <div class="text-center mb-4 text-white">
        <h3 class="fw-bold">Menu Utama</h3>
        <p>Akses cepat ke fitur-fitur penting.</p>
    </div>

    <div class="row justify-content-center g-3 mb-5">
        <div class="col-lg-3 col-md-4 col-sm-6">
            <a href="transaksi.php" class="btn btn-primary w-100 btn-menu">
                <i class="fas fa-exchange-alt me-2"></i> Kelola Transaksi
            </a>
        </div>

        <?php if (is_admin()): ?>
        <div class="col-lg-3 col-md-4 col-sm-6">
            <a href="kategori.php" class="btn btn-secondary w-100 btn-menu">
                <i class="fas fa-tags me-2"></i> Kelola Kategori
            </a>
        </div>
        <div class="col-lg-3 col-md-4 col-sm-6">
            <a href="users.php" class="btn btn-info w-100 btn-menu">
                <i class="fas fa-users me-2"></i> Kelola User
            </a>
        </div>
        <div class="col-lg-3 col-md-4 col-sm-6">
            <a href="log.php" class="btn btn-warning w-100 btn-menu">
                <i class="fas fa-clipboard-list me-2"></i> Log Aktivitas
            </a>
        </div>
        <?php endif; ?>

        <div class="col-lg-3 col-md-4 col-sm-6">
            <a href="laporan.php" class="btn btn-success w-100 btn-menu">
                <i class="fas fa-chart-bar me-2"></i> Laporan
            </a>
        </div>
    </div>
</div>

<!-- Footer -->
<footer>
    <div class="container">
        &copy; <?= date('Y') ?> SIKEURAHAN | Sistem Keuangan Kelurahan
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('financeChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Pemasukan', 'Pengeluaran', 'Saldo'],
            datasets: [{
                label: 'Jumlah (Rp)',
                data: [<?= $total_pemasukan ?>, <?= $total_pengeluaran ?>, <?= $saldo ?>],
                backgroundColor: ['#3b82f6', '#ef4444', '#10b981'],
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                title: { display: false }
            },
            scales: { y: { beginAtZero: true } }
        }
    });
</script>
</body>
</html>
