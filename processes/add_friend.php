<?php
require_once "../models/db.php";
session_start();

if (!isset($_SESSION['user_id']) || !isset($_POST['friend_username'])) {
    header("Location: ../friends.php?error=missing_data");
    exit;
}

$userId = $_SESSION['user_id'];
$friendUsername = trim($_POST['friend_username']);

try {
    $pdo = getPDO();

    // Hitta friend_id på användarnamn
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$friendUsername]);
    $friendId = $stmt->fetchColumn();

    if (!$friendId) {
        header("Location: ../friends.php?error=user_not_found");
        exit;
    }

    if ($friendId == $userId) {
        header("Location: ../friends.php?error=cannot_add_self");
        exit;
    }

    // Kolla om redan vän eller väntande
    $stmt = $pdo->prepare("SELECT * FROM friends WHERE (user_id = ? AND friend_id = ?) OR (user_id = ? AND friend_id = ?)");
    $stmt->execute([$userId, $friendId, $friendId, $userId]);
    if ($stmt->fetch()) {
        header("Location: ../friends.php?error=already_friends_or_pending");
        exit;
    }

    // Lägg till vänförfrågan med status 'pending'
    $stmt = $pdo->prepare("INSERT INTO friends (user_id, friend_id, status) VALUES (?, ?, 'pending')");
    $stmt->execute([$userId, $friendId]);

    header("Location: ../friends.php?success=request_sent");
    exit;

} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
}
