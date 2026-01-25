<?php
require_once "models/db.php"; // Inkluderar vår databas och geno mdenna fil skapar den och returnerar den en PDO anslutning
session_start(); // Startar en session så att vi kan använda $_SESSION

$pdo = getPDO(); //Anropar den funktion som ansluter till vår databas

// Detta statement hämtar 4 populära filmer utefter rating på vår sida 
$stmtMovies = $pdo->prepare("
  SELECT media.*, ROUND(AVG(ratings.score), 1) AS average_score
  FROM media
  LEFT JOIN ratings ON media.id = ratings.movie_id
  WHERE media.type = 'movie'
  GROUP BY media.id
  ORDER BY average_score DESC
  LIMIT 4
");
$stmtMovies->execute();
$movies = $stmtMovies->fetchAll();

// Detta statement hämtar 4 populära serier utefter rating på vår sida 
$stmtSeries = $pdo->prepare("
  SELECT media.*, ROUND(AVG(ratings.score), 1) AS average_score
  FROM media
  LEFT JOIN ratings ON media.id = ratings.movie_id
  WHERE media.type = 'tv' OR media.type = 'series'
  GROUP BY media.id
  ORDER BY average_score DESC
  LIMIT 4
");
$stmtSeries->execute();
$series = $stmtSeries->fetchAll();

// Förbereder användarens watchlist och betyg för de olika filmerna/serierna om de är inloggade på sidan
$userWatchlist = [];
$userRatings = [];

if (isset($_SESSION['user_id'])) {
    // Hämtar alla film id som användaren har i sin watchlist 
    $stmtWatchlist = $pdo->prepare("SELECT movie_id FROM watchlist WHERE user_id = ?");
    $stmtWatchlist->execute([$_SESSION['user_id']]);
    $userWatchlist = $stmtWatchlist->fetchAll(PDO::FETCH_COLUMN, 0);

    // Hämtar användarens tidigare betyg på olika filmer
    $stmtRatings = $pdo->prepare("SELECT movie_id, score FROM ratings WHERE user_id = ?");
    $stmtRatings->execute([$_SESSION['user_id']]);
    $userRatings = $stmtRatings->fetchAll(PDO::FETCH_KEY_PAIR);
}

// Visar ett meddelande om att filmen blivit tillagd 
$showAddedMessage = isset($_GET['success']) && $_GET['success'] === 'added';
?>

<!DOCTYPE html>
<html lang="sv">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/png" href="IMG/FRDb.png">
  <title>FRDb – Homepage</title>

<!-- CSS filerna länkas in för sidan-->
  <link rel="stylesheet" href="css/index.css">
  <link rel="stylesheet" href="css/topbar.css">
  <link rel="stylesheet" href="css/index_extra.css">
  <link rel="stylesheet" href="css/weather.css"> 
</head>
<body>

<div class="scale-wrapper">
<!-- Topbaren länkas in så att den syns i toppen av sidan-->
<?php include 'views/topbar.php'; ?>

<!-- Vädret visas till höger om sidan -->
<aside class="weather-column" id="weatherBox">
    <div class="weather-header">
      <h3 class="weather-title">
        Weather Forecast
        <span class="weather-location">Uppsala, Sweden</span>
      </h3>
    </div>
  <div id="weatherContent">Loading forecast...</div> <!-- Vår weather.js fil fyller detta med väderdata-->
</aside>


<!-- Denna del är för populära filmer och visar dem med text osv hämtat med vår TMDb API-->
<main class="main-content">
  <h2 class="section-title">Popular Movies</h2>
  <div class="movie-grid">
    <?php foreach ($movies as $index => $movie): ?>
      <div class="movie-card-extended">

      <!-- Visar filmens posterbild -->
        <div class="poster-wrapper">
          <img src="<?= htmlspecialchars($movie['poster_url']) ?>" alt="<?= htmlspecialchars($movie['title']) ?>" class="movie-poster">

          <!-- En funktion som användaren kan använda för att snabbt lägga till en film i watchlist, endast om man är inloggad-->
          <?php if (isset($_SESSION['user_id'])): ?>
            <form method="POST" action="processes/add_to_watchlist.php" class="watchlist-form">
              <input type="hidden" name="movie_id" value="<?= $movie['id'] ?>">

              <!-- Om den redan finns i watchlist så visas en ikon om detta -->
              <?php if (in_array($movie['id'], $userWatchlist)): ?>
                <button type="button" class="add-watchlist-btn added" title="Already in Watchlist">✓</button>

              <!-- Annars visas ett plus för användaren-->
              <?php else: ?>
                <button type="submit" class="add-watchlist-btn" title="Add to Watchlist">＋</button>
              <?php endif; ?>
            </form>
          <?php endif; ?>


          <!--Betyg för filmerna där det genomsnittliga betyget för just denna serie visas och som baserar på alla ratings från alla användare -->
          <div class="rate-wrapper">
          <div class="movie-rating">

            <!-- Visar ett avrundat betyg eller ett - om ingen har röstat-->
            <span class="star">★</span>
            <span class="value"><?= $movie['average_score'] !== null ? $movie['average_score'] : '–' ?></span>

            <!-- Om man är inloggad visas användarens egan betyg också om det finns-->
                <?php if (isset($_SESSION['user_id'])): ?>
                  <?php if (isset($userRatings[$movie['id']])): ?>

                    <!-- En kanpp som visar användarens egna betyg-->
                    <button class="user-rating" data-movie-id="<?= $movie['id'] ?>" title="Click to change rating">
                      <span class="star">★</span>
                      <?= htmlspecialchars($userRatings[$movie['id']]) ?>
                    </button>

                  <!-- Om användaren inte har betygsatt ännu så visas en genomskinlig stjärna -->
                  <?php else: ?>
                    <button type="button" class="transparent-star">★</button>
                  <?php endif; ?>
                  
                  <!-- Ett dolt inline-formulär för att skicka nytt betyg och detta visas sedan genom ett JS script -->
                  <form method="POST" action="processes/rate_media.php" class="inline-rate-form" style="display:none;">
                    <input type="hidden" name="movie_id" value="<?= $movie['id'] ?>">
                    <input type="number" name="score" step="0.1" min="1" max="10"
                      value="<?= htmlspecialchars($userRatings[$movie['id']] ?? '') ?>"
                      required class="inline-score-input" placeholder="1–10">
                    <button type="submit" class="rate-button-inline">Rate</button>
                  </form>
                <?php endif; ?>
                </div>
        </div>
      </div>

        
        <!-- Visar filmens/seriens titel och dess ranking -->
        <div class="movie-info">
          <span class="ranking"><?= $index + 1 ?>.</span>
          <span class="title"><?= htmlspecialchars($movie['title']) ?></span>
        </div>

        <!-- Länk till movies sidan fär man kan läsa mer om filmen  -->
        <div class="movie-actions">
          <a href="movies.php?id=<?= $movie['id'] ?>" class="read-more">Read More</a>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Denna del är för populära serier och visar dem med text osv hämtat med vår TMDb API-->
  <h2 class="section-title">Popular Series</h2>
  <div class="movie-grid">
    <?php foreach ($series as $index => $serie): ?>
      <div class="movie-card-extended">
      <!-- Visar seriens posterbild -->
        <div class="poster-wrapper">
          <img src="<?= htmlspecialchars($serie['poster_url']) ?>" alt="<?= htmlspecialchars($serie['title']) ?>" class="movie-poster">

          <!-- En funktion som användaren kan använda för att snabbt lägga till en serie i watchlist, endast om man är inloggad-->
          <?php if (isset($_SESSION['user_id'])): ?>
            <form method="POST" action="processes/add_to_watchlist.php" class="watchlist-form">
              <input type="hidden" name="movie_id" value="<?= $serie['id'] ?>">

              <!-- Om den redan finns i watchlist så visas en ikon om detta -->
              <?php if (in_array($serie['id'], $userWatchlist)): ?>
                <button type="button" class="add-watchlist-btn added" title="Already in Watchlist">✓</button>
              <?php else: ?>

                <!-- Annars visas ett plus för användaren-->
                <button type="submit" class="add-watchlist-btn" title="Add to Watchlist">＋</button>
              <?php endif; ?>
            </form>
          <?php endif; ?>

            <!--Betyg för serierna där det genomsnittliga betyget för just denna serie visas och som baserar på alla ratings från alla användare -->
            <div class="rate-wrapper">
            <div class="movie-rating">
              <span class="star">★</span>
              <!-- Visar ett avrundat betyg eller ett - om ingen har röstat-->
              <span class="value"><?= $serie['average_score'] !== null ? $serie['average_score'] : '–' ?></span>

            <!-- Om man är inloggad visas användarens egan betyg också om det finns-->
              <?php if (isset($_SESSION['user_id'])): ?>
                <?php if (isset($userRatings[$serie['id']])): ?>

                  <!-- En kanpp som visar användarens egna betyg-->
                  <button class="user-rating" data-movie-id="<?= $serie['id'] ?>" title="Click to change rating">
                    <span class="star">★</span>
                    <?= htmlspecialchars($userRatings[$serie['id']]) ?>
                  </button>
                
                <!-- Om användaren inte har betygsatt ännu så visas en genomskinlig stjärna -->
                <?php else: ?>
                  <button type="button" class="transparent-star">★</button>
                <?php endif; ?>
              <?php endif; ?>
            </div>

            <!-- Ett dolt inline-formulär för att skicka nytt betyg och detta visas sedan genom ett JS script -->
            <?php if (isset($_SESSION['user_id'])): ?>
              <form method="POST" action="processes/rate_media.php" class="inline-rate-form" style="display:none;">
                <input type="hidden" name="movie_id" value="<?= $serie['id'] ?>">
                <input type="number" name="score" step="0.1" min="1" max="10" required class="inline-score-input" placeholder="1–10">
                <button type="submit" class="rate-button-inline">Rate</button>
              </form>
            <?php endif; ?>

          </div>
        </div>

        <!-- Visar filmens/seriens titel och dess ranking -->
        <div class="movie-info">
          <span class="ranking"><?= $index + 1 ?>.</span>
          <span class="title"><?= htmlspecialchars($serie['title']) ?></span>
        </div>

        <!-- Länk till movies sidan fär man kan läsa mer om filmen  -->
        <div class="movie-actions">
          <a href="movies.php?id=<?= $serie['id'] ?>" class="read-more">Read More</a>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>
</main>

<script src="JS/index.js"></script>
<script src="JS/weather.js"></script>
</body>
</html>
