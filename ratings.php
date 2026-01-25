<?php
require_once "models/session_user.php";
requireLoginOrRedirect();

require_once "models/db.php";

$pdo = getPDO();
$userId = $_SESSION['user_id'];

$stmt = $pdo->prepare("
  SELECT media.*, ratings.score, ratings.date_rated
  FROM ratings
  JOIN media ON ratings.movie_id = media.id
  WHERE ratings.user_id = ?
  ORDER BY ratings.date_rated DESC
");
$stmt->execute([$userId]);
$ratings = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="sv">
<head>
  <meta charset="UTF-8">
  <title>Ratings – FRDb</title>
  <link rel="stylesheet" href="css/topbar.css">
  <link rel="stylesheet" href="css/rating.css">
</head>

<body>

<?php include 'views/topbar.php'; ?>

<main class="ratings-container">
  <section class="ratings-header-box">
    <div class="ratings-header">
      <h2 class="section-title">Ratings</h2>
      <p class="ratings-subtitle">by <?= htmlspecialchars($_SESSION['username']) ?></p>
      <p class="ratings-description">Here you can view all your movie and series ratings on FRDb.</p>
    </div>
  </section>

  <?php if (count($ratings) > 0): ?>
    <div class="rating-grid">
      <?php foreach ($ratings as $index => $item): ?>
        <div class="rating-card">
          <img src="<?= htmlspecialchars($item["poster_url"]) ?>" alt="<?= htmlspecialchars($item["title"]) ?>" class="rating-poster">

          <div class="rating-details">
            <div class="rating-meta">
              <span class="rating-rank"><?= $index + 1 ?>.</span>
              <span class="rating-title"><?= htmlspecialchars($item["title"]) ?></span>
            </div>

            <div class="rating-score">
              <span class="star">★</span>
              <?= number_format($item["score"], 1) ?>
            </div>

            <div class="rating-date"><?= date("Y-m-d", strtotime($item["date_rated"])) ?></div>
            <p class="rating-description"><?= htmlspecialchars($item["description"]) ?></p>

            <div class="rating-actions">
              <form method="POST" action="processes/remove_rating.php">
                <input type="hidden" name="movie_id" value="<?= $item['id'] ?>">
                <button type="submit" class="remove-button">Remove Rating</button>
              </form>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <p style="color: white; font-size: 16px; margin-top: 40px;">You haven't rated anything yet.</p>
  <?php endif; ?>
</main>
<script>
  console.log("CSS laddad:", getComputedStyle(document.querySelector('.remove-button')).backgroundColor);
</script>
</body>
</html>
