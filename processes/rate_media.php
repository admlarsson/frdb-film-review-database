<?php
require_once "../models/session_user.php";
requireLoginOrRedirect(); // Säkerställer att användaren är inloggad

require_once "../models/db.php";

if (!isset($_POST['movie_id']) || !isset($_POST['score'])) {
  header("Location: ../watchlist.php?error=missing_data");
  exit;
}

$userId = $_SESSION['user_id'];
$movieId = intval($_POST['movie_id']);
$score = floatval($_POST['score']);

if ($score < 1.0 || $score > 10.0) {
  header("Location: ../watchlist.php?error=invalid_score");
  exit;
}

try {
  $pdo = getPDO();

  // Spara betyg (ersätt om det redan finns)
  $stmt = $pdo->prepare("
    INSERT INTO ratings (user_id, movie_id, score)
    VALUES (?, ?, ?)
    ON CONFLICT(user_id, movie_id) DO UPDATE SET score = excluded.score, date_rated = CURRENT_TIMESTAMP
  ");
  $stmt->execute([$userId, $movieId, $score]);

  // Ta bort från watchlist
  $del = $pdo->prepare("DELETE FROM watchlist WHERE user_id = ? AND movie_id = ?");
  $del->execute([$userId, $movieId]);

  header("Location: " . $_SERVER['HTTP_REFERER']);
  exit;

} catch (PDOException $e) {
  echo "Database error: " . $e->getMessage();
}
