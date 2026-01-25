<?php
require_once "../models/db.php";
session_start();

if (!isset($_SESSION['user_id']) || !isset($_POST['requester_id']) || !isset($_POST['action'])) {
    header("Location: ../friends.php?error=missing_data");
    exit;
}

$userId = $_SESSION['user_id'];
$requesterId = intval($_POST['requester_id']);
$action = $_POST['action'];

if (!in_array($action, ['accept', 'decline'])) {
    header("Location: ../friends.php?error=invalid_action");
    exit;
}

try {
    $pdo = getPDO();

    if ($action === 'accept') {
        // Uppdatera status till accepted för vänförfrågan
        $stmt = $pdo->prepare("UPDATE friends SET status = 'accepted' WHERE user_id = ? AND friend_id = ?");
        $stmt->execute([$requesterId, $userId]);

        // Lägg till omvänd relation också för att båda ska vara vänner (om du vill)
        $stmtCheck = $pdo->prepare("SELECT * FROM friends WHERE user_id = ? AND friend_id = ?");
        $stmtCheck->execute([$userId, $requesterId]);
        if (!$stmtCheck->fetch()) {
            $stmtInsert = $pdo->prepare("INSERT INTO friends (user_id, friend_id, status) VALUES (?, ?, 'accepted')");
            $stmtInsert->execute([$userId, $requesterId]);
        }

    } else if ($action === 'decline') {
        // Ta bort vänförfrågan
        $stmt = $pdo->prepare("DELETE FROM friends WHERE user_id = ? AND friend_id = ? AND status = 'pending'");
        $stmt->execute([$requesterId, $userId]);
    }

    header("Location: ../friends.php");
    exit;

} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
}
