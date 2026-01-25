<?php
session_start();

require_once __DIR__ . "/../models/db.php";
require_once __DIR__ . "/../models/session_user.php";

if (!isAdmin()) {
    header("Location: ../index.php?error=no_admin_rights");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
    $userId = intval($_POST['user_id']);

    // Skydda så att en admin inte kan ta bort sig själv
    if ($userId === $_SESSION['user_id']) {
        header("Location: ../admin.php?error=cannot_delete_self");
        exit;
    }

    try {
        $pdo = getPDO();
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$userId]);

        header("Location: ../admin.php?success=user_deleted");
        exit;
    } catch (PDOException $e) {
        header("Location: ../admin.php?error=delete_failed");
        exit;
    }
} else {
    header("Location: ../admin.php?error=invalid_request");
    exit;
}
