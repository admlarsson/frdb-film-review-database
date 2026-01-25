<?php
// Startar session och laddar in säkerhets- och databasfunktioner
require_once "models/session_user.php";
requireLoginOrRedirect(); 

require_once "models/db.php";

// Skapar PDO-anslutning till databasen
$pdo = getPDO();
// Hämtar användarens ID från sessionen
$userId = $_SESSION['user_id'];

// Förbereder SQL-fråga som hämtar filmer i användarens watchlist
// Inkluderar även användarens eget betyg (user_score) och snittbetyg (average_score)
$stmt = $pdo->prepare("
  SELECT m.*, 
         ROUND(ur.score, 1) AS user_score,
         ROUND(AVG(r.score), 1) AS average_score
  FROM watchlist w
  JOIN media m ON w.movie_id = m.id
  LEFT JOIN ratings ur ON ur.movie_id = m.id AND ur.user_id = ?
  LEFT JOIN ratings r ON r.movie_id = m.id
  WHERE w.user_id = ?
  GROUP BY m.id
");
// Kör frågan med användarens ID som parameter två gånger (för ur och w)
$stmt->execute([$userId, $userId]);
// Hämtar alla resultat som en array
$watchlist = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="sv">
<head>
  <meta charset="UTF-8">
  <title>Your Watchlist – FRDb</title>
  <!-- Laddar CSS för topbar och watchlist -->
  <link rel="stylesheet" href="css/topbar.css">
  <link rel="stylesheet" href="css/watchlist.css">
</head>
<body>

  <!-- Inkluderar toppmenyn -->
  <?php include 'views/topbar.php'; ?>

  <main class="watchlist-container">
    <!-- Sektion för sidans header med titel och beskrivning -->
    <section class="watchlist-header-box">
      <div class="watchlist-header">
        <h2 class="section-title">Watchlist</h2>
        <p class="watchlist-subtitle">by <?= htmlspecialchars($_SESSION['username']) ?></p>
        <p class="watchlist-intro">Your Watchlist helps you keep track of titles you want to watch.</p>
      </div>
    </section>

    <!-- Om användaren har filmer i sin watchlist -->
    <?php if (count($watchlist) > 0): ?>
      <div class="watchlist-grid">
        <!-- Loopar igenom varje film i listan -->
        <?php foreach ($watchlist as $index => $item): ?>
          <!-- Kort för en film -->
          <div class="watchlist-card">
            <!-- Filmens posterbild -->
            <img src="<?= htmlspecialchars($item["poster_url"]) ?>" alt="<?= htmlspecialchars($item["title"]) ?>" class="watchlist-poster">

            <!-- Detaljer till höger om bilden -->
            <div class="watchlist-details">
              <!-- Meta-information: rank och titel -->
              <div class="watchlist-meta">
                <span class="watchlist-rank"><?= $index + 1 ?>.</span>
                <span class="watchlist-title"><?= htmlspecialchars($item["title"]) ?></span>
              </div>

              <!-- Visar både snittbetyg och användarens eget betyg (om det finns) -->
              <div class="watchlist-score">
                <span class="star avg-score">★</span>
                <?= $item['average_score'] ?? '–' ?>

                <?php if ($item['user_score'] !== null): ?>
                  <span class="user-rating" title="You rated this">
                    <span class="star">★</span>
                    <?= htmlspecialchars($item['user_score']) ?>
                  </span>
                <?php endif; ?>
              </div>

              <!-- Årtal -->
              <div class="watchlist-year"><?= $item["year"] ?></div>
              <!-- Kort beskrivning av filmen -->
              <p class="watchlist-description"><?= $item["description"] ?></p>

              <!-- Åtgärder, t.ex. ta bort film och betygsformulär -->
              <div class="watchlist-actions">
                <!-- Formulär för att ta bort filmen från watchlist -->
                <form method="POST" action="processes/remove_from_watchlist.php">
                  <input type="hidden" name="movie_id" value="<?= $item['id'] ?>">
                  <button type="submit" class="remove-button">Remove from Watchlist</button>
                </form>

                <!-- Endast visa betygsformulär om användaren inte redan har betygsatt -->
                <?php if ($item['user_score'] === null): ?>
                  <div class="rate-wrapper">
                    <!-- Stjärna som kan klickas på för att visa betygsformuläret -->
                    <button type="button" class="transparent-star">★</button>
                    <!-- Formulär för att skicka in betyg -->
                    <form method="POST" action="processes/rate_media.php" class="inline-rate-form" style="display: none;">
                      <input type="hidden" name="movie_id" value="<?= $item['id'] ?>">
                      <input type="number" name="score" step="0.1" min="1" max="10" required class="inline-score-input" placeholder="1–10">
                      <button type="submit" class="rate-button-inline">Rate</button>
                    </form>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <!-- Meddelande om watchlist är tom -->
    <?php else: ?>
      <p style="color: white; font-size: 16px; margin-top: 40px;">You haven't added anything to your watchlist yet.</p>
    <?php endif; ?>
  </main>

  <!-- Laddar JS som hanterar betygsformulär mm -->
  <script src="JS/index.js"></script>

</body>
</html>
