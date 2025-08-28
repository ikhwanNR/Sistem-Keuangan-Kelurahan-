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
</head>
<body class="p-4">
<div class="container">
    <h2 class="mb-4">Edit User</h2>
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
        <div class="mb-3">
            <label class="form-label">Role</label>
            <select name="role" class="form-select">
                <option value="user" <?= $user['role']=='user'?'selected':'' ?>>User</option>
                <option value="admin" <?= $user['role']=='admin'?'selected':'' ?>>Admin</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
        <a href="users.php" class="btn btn-secondary">Batal</a>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
