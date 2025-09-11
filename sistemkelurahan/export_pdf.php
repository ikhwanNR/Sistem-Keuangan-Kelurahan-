<?php
session_start();
require 'config.php';
require 'functions.php';
require_login();

// Ambil filter tanggal
$tanggal_awal = $_GET['tanggal_awal'] ?? date('Y-m-01');
$tanggal_akhir = $_GET['tanggal_akhir'] ?? date('Y-m-t');

// Ambil data transaksi
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

// Hitung total pemasukan & pengeluaran
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
    <title>Laporan Keuangan PDF</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h2, h4 { text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        table, th, td { border: 1px solid #000; }
        th, td { padding: 8px; text-align: center; }
        th { background-color: #333; color: #fff; }
        .summary { margin-top: 20px; width: 50%; margin-left: auto; margin-right: auto; }
        .summary td { text-align: left; padding: 5px; }
    </style>
</head>
<body>
    <h2>Laporan Keuangan</h2>
    <p style="text-align:center;">Periode: <?= htmlspecialchars($tanggal_awal) ?> s/d <?= htmlspecialchars($tanggal_akhir) ?></p>

    <table>
        <thead>
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
            <?php if ($transaksi): ?>
                <?php foreach ($transaksi as $i => $row): ?>
                    <tr>
                        <td><?= $i+1 ?></td>
                        <td><?= htmlspecialchars($row['tanggal']) ?></td>
                        <td><?= htmlspecialchars($row['nama_kategori']) ?></td>
                        <td><?= htmlspecialchars($row['deskripsi']) ?></td>
                        <td><?= ($row['jenis_transaksi'] == 'pemasukan') ? 'Pemasukan' : 'Pengeluaran' ?></td>
                        <td>Rp <?= number_format($row['jumlah'], 2, ',', '.') ?></td>
                        <td><?= htmlspecialchars($row['nama_user']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="7">Tidak ada data transaksi</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <table class="summary">
        <tr><td>Total Pemasukan:</td><td>Rp <?= number_format($total_pemasukan, 2, ',', '.') ?></td></tr>
        <tr><td>Total Pengeluaran:</td><td>Rp <?= number_format($total_pengeluaran, 2, ',', '.') ?></td></tr>
        <tr><td>Saldo Akhir:</td><td>Rp <?= number_format($saldo, 2, ',', '.') ?></td></tr>
    </table>

    <script>
        // Tampilkan print dialog saat halaman dibuka
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
