<?php
require_once "../models/session_user.php";
requireLoginOrRedirect("login.php"); // skickar ej inloggade till login

require_once "../models/db.php";


if (!isset($_POST['movie_id'])) {
  header("Location: ../watchlist.php?error=missing_movie_id");
  exit;
}

$userId = $_SESSION['user_id'];
$movieId = intval($_POST['movie_id']);

try {
  $pdo = getPDO();

  $stmt = $pdo->prepare("DELETE FROM watchlist WHERE user_id = ? AND movie_id = ?");
  $stmt->execute([$userId, $movieId]);

  header("Location: ../watchlist.php?removed=1");
  exit;

} catch (PDOException $e) {
  echo "Database error: " . $e->getMessage();
}
