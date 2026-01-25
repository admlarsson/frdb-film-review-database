<?php
require_once "../models/db.php";
session_start();

if (!isset($_SESSION['user_id']) || !isset($_POST['movie_id'])) {
  header("Location: ../index.php");
  exit;
}

$userId = $_SESSION['user_id'];
$movieId = intval($_POST['movie_id']);

try {
  $pdo = getPDO();

  // Undvik dubletter
  $check = $pdo->prepare("SELECT * FROM watchlist WHERE user_id = ? AND movie_id = ?");
  $check->execute([$userId, $movieId]);

  if (!$check->fetch()) {
    $stmt = $pdo->prepare("INSERT INTO watchlist (user_id, movie_id) VALUES (?, ?)");
    $stmt->execute([$userId, $movieId]); // added_at sätter SQLite automatiskt
  }

  header("Location: ../index.php?success=added");
  exit;

} catch (PDOException $e) {
  echo "Database error: " . $e->getMessage();
}
