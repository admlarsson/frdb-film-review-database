<?php
require_once "models/session_user.php";
requireLoginOrRedirect();

require_once "models/db.php";


$userId = $_SESSION['user_id'];
$pdo = getPDO();

// Hämta användarens join-datum
$stmt = $pdo->prepare("SELECT date_joined FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();
$joinedDate = date("F Y", strtotime($user['date_joined']));

// Hämta statistik: antal ratings, watchlist och vänner
$stmtStats = $pdo->prepare("
    SELECT 
      (SELECT COUNT(*) FROM ratings WHERE user_id = ?) AS ratings_count,
      (SELECT COUNT(*) FROM watchlist WHERE user_id = ?) AS watchlist_count,
      (SELECT COUNT(*) FROM friends f WHERE (f.user_id = ? OR f.friend_id = ?) AND f.status = 'accepted') AS friends_count
");
$stmtStats->execute([$userId, $userId, $userId, $userId]);
$stats = $stmtStats->fetch();

// Senaste 4 betygsatta filmer
$stmtRecentRatings = $pdo->prepare("
  SELECT r.score, m.title, m.poster_url, m.id
  FROM ratings r
  JOIN media m ON r.movie_id = m.id
  WHERE r.user_id = ?
  ORDER BY r.date_rated DESC
  LIMIT 4
");
$stmtRecentRatings->execute([$userId]);
$recentRatings = $stmtRecentRatings->fetchAll();

// Senaste 4 filmer i watchlist
$stmtWatchlistItems = $pdo->prepare("
  SELECT m.title, m.poster_url, m.id
  FROM watchlist w
  JOIN media m ON w.movie_id = m.id
  WHERE w.user_id = ?
  LIMIT 4
");
$stmtWatchlistItems->execute([$userId]);
$watchlistItems = $stmtWatchlistItems->fetchAll();

?>

<!DOCTYPE html>
<html lang="sv">
<head>
  <meta charset="UTF-8" />
  <title>Profile – FRDb</title>
  <link rel="stylesheet" href="css/topbar.css" />
  <link rel="stylesheet" href="css/profile.css" />
</head>
<body>

<?php include 'views/topbar.php'; ?>

<main class="profile-container">

  <!-- Headerdelen: profilbild, namn, join-datum, boxar -->
  <section class="profile-header">
    <div class="profile-info">
      <img src="IMG/profile_icon.png" alt="Profile Picture" class="profile-pic" />
      <div class="profile-details">
        <h1 class="profile-name"><?= htmlspecialchars($_SESSION['username']) ?></h1>

        <div class="profile-joined">
          <img src="IMG/Joined_icon.png" alt="Joined" class="joined-icon" />
          <span>Joined <?= $joinedDate ?></span>
        </div>
      </div>
    </div>

    <div class="profile-stats">
      <a href="ratings.php" class="stat-box">Ratings<br><strong><?= $stats['ratings_count'] ?></strong></a>
      <a href="watchlist.php" class="stat-box">Watchlist<br><strong><?= $stats['watchlist_count'] ?></strong></a>
      <a href="friends.php" class="stat-box">Friends<br><strong><?= $stats['friends_count'] ?></strong></a>
      <a class="stat-box">More<br><strong>⋯</strong></a>
    </div>
  </section>

  <!-- Undersektion med filmer i två kolumner -->
  <section class="profile-lists-container">

    <section class="profile-section">
      <h2>Recent Ratings</h2>
      <?php if (!empty($recentRatings)): ?>
        <div class="media-grid">
          <?php foreach ($recentRatings as $rating): ?>
            <div class="media-card">
              <a href="movies.php?id=<?= $rating['id'] ?>">
                <img src="<?= htmlspecialchars($rating['poster_url']) ?>" alt="<?= htmlspecialchars($rating['title']) ?>" />
                <div class="media-info">
                  <span class="media-title"><?= htmlspecialchars($rating['title']) ?></span>
                  <span class="media-score">★ <?= htmlspecialchars($rating['score']) ?>/10</span>
                </div>
              </a>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <p>You haven't rated any movies yet.</p>
      <?php endif; ?>
    </section>

    <section class="profile-section">
      <h2>Your Watchlist</h2>
      <?php if (!empty($watchlistItems)): ?>
        <div class="media-grid">
          <?php foreach ($watchlistItems as $item): ?>
            <div class="media-card">
              <a href="movies.php?id=<?= $item['id'] ?>">
                <img src="<?= htmlspecialchars($item['poster_url']) ?>" alt="<?= htmlspecialchars($item['title']) ?>" />
                <div class="media-info">
                  <span class="media-title"><?= htmlspecialchars($item['title']) ?></span>
                </div>
              </a>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <p>Your watchlist is empty.</p>
      <?php endif; ?>
    </section>

  </section>
</main>

</body>
</html>
