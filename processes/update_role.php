<?php
session_start();
require_once "models/db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$userId = $_POST['user_id'] ?? null;
$newRole = $_POST['role'] ?? null;

if (!$userId || !in_array($newRole, ['user', 'admin'])) {
    header("Location: admin.php?error=invalid_input");
    exit;
}

try {
    $pdo = getPDO();
    $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
    $stmt->execute([$newRole, $userId]);
    header("Location: admin.php?success=role_updated");
    exit;
} catch (PDOException $e) {
    header("Location: admin.php?error=update_failed");
    exit;
}
