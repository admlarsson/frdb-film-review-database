<?php
require_once "../models/session_user.php";
requireLoginOrRedirect();

require_once "../models/db.php";


if (!isset($_POST['movie_id'])) {
    header("Location: ratings.php?error=missing_movie_id");
    exit;
}

$userId = $_SESSION['user_id'];
$movieId = intval($_POST['movie_id']);

try {
    $pdo = getPDO();
    $stmt = $pdo->prepare("DELETE FROM ratings WHERE user_id = ? AND movie_id = ?");
    $stmt->execute([$userId, $movieId]);

    header("Location: ../ratings.php?success=rating_removed");
    exit;

} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
}
