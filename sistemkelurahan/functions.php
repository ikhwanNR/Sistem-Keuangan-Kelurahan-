<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function require_login() {
    if (!is_logged_in()) {
        header("Location: ../auth/login.php");
        exit;
    }
}

function is_admin() {
    return ($_SESSION['role'] ?? '') === 'admin';
}

function catat_log($pdo, $id_user, $aktivitas) {
    $stmt = $pdo->prepare("INSERT INTO log_aktivitas (id_user, aktivitas) VALUES (?, ?)");
    $stmt->execute([$id_user, $aktivitas]);
}



?>
