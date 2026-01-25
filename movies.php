<?php
require_once "models/db.php";
require_once "api/tmdb_functions.php";
session_start();

$pdo = getPDO();

// Om användaren kommer från sökresultat (via tmdb_id & type)
if (isset($_GET['tmdb_id']) && isset($_GET['type'])) {
  $tmdbId = $_GET['tmdb_id']; // behåll som sträng, TMDb ID kan vara långt
    $mediaType = $_GET['type'] === 'tv' ? 'tv' : 'movie'; // korrekt namn för TMDb API

    // Kontrollera om den redan finns i vår databas
    $stmt = $pdo->prepare("SELECT id FROM media WHERE tmdb_id = ? AND type = ?");
    $stmt->execute([$tmdbId, $mediaType]);

    $existingId = $stmt->fetchColumn();

    if (!$existingId) {
        $details = getMovieDetails($tmdbId, $mediaType);

        if ($details) {
            $stmtInsert = $pdo->prepare("
                INSERT INTO media (tmdb_id, title, year, genre, poster_url, type, description)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");

            $genres = implode(", ", array_map(fn($g) => $g['name'], $details['genres']));
            $title = $mediaType === 'movie' ? $details['title'] : $details['name'];
            $year = $mediaType === 'movie'
                ? substr($details['release_date'] ?? '', 0, 4)
                : substr($details['first_air_date'] ?? '', 0, 4);
            $poster = $details['poster_path']
                ? "https://image.tmdb.org/t/p/w500" . $details['poster_path']
                : "IMG/Placeholder.png";

            $stmtInsert->execute([
                $details['id'],
                $title,
                $year,
                $genres,
                $poster,
                $mediaType,
                $details['overview'] ?? ''
            ]);

            $existingId = $pdo->lastInsertId();
        }
    }

    if ($existingId) {
        header("Location: movies.php?id=" . $existingId);
        exit;
    } else {
        echo "Could not fetch media from TMDb.";
        exit;
    }
}

// Direktvisning via ID i databasen
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = intval($_GET['id']);

$stmt = $pdo->prepare("
    SELECT media.*, ROUND(AVG(ratings.score), 1) AS average_score
    FROM media
    LEFT JOIN ratings ON media.id = ratings.movie_id
    WHERE media.id = ?
    GROUP BY media.id
");
$stmt->execute([$id]);
$media = $stmt->fetch();
$type = $media['type']; // 'movie' eller 'tv'

if (!$media) {
    echo "Media not found.";
    exit;
}

// Kolla om i watchlist
$alreadyAdded = false;
if (isset($_SESSION['user_id'])) {
    $stmtCheck = $pdo->prepare("SELECT 1 FROM watchlist WHERE user_id = ? AND movie_id = ?");
    $stmtCheck->execute([$_SESSION['user_id'], $id]);
    $alreadyAdded = $stmtCheck->fetchColumn() !== false;
}
?>

<!DOCTYPE html>
<html lang="sv">
<head>
  <meta charset="UTF-8" />
  <title><?= htmlspecialchars($media['title']) ?> – FRDb</title>
  <link rel="stylesheet" href="css/topbar.css" />
  <link rel="stylesheet" href="css/movies.css" />
</head>
<body>

<?php include 'views/topbar.php'; ?>

<?php if (isset($_GET['added']) && $_GET['added'] == 1): ?>
  <div class="confirmation-message">Added to your watchlist!</div>
<?php endif; ?>

<main class="movie-detail-container">
  <img src="<?= htmlspecialchars($media['poster_url']) ?>" alt="<?= htmlspecialchars($media['title']) ?>" class="movie-detail-poster" />

  <div class="movie-info-wrapper">
    <h1 class="movie-title"><?= htmlspecialchars($media['title']) ?></h1>

    <div class="movie-subtitle">
      <?= htmlspecialchars($media['year']) ?> |
      <?= htmlspecialchars($media['genre']) ?> |
      <?= ucfirst($media['type']) ?>
    </div>

    <div class="movie-rating" title="Average rating">
      <span class="star">★</span>
      <span><?= $media['average_score'] !== null ? $media['average_score'] : '–' ?></span>
    </div>

    <p class="movie-description"><?= nl2br(htmlspecialchars($media['description'])) ?></p>

    <?php if (isset($_SESSION['user_id'])): ?>
      <form method="POST" action="processes/add_to_watchlist.php" style="margin-top: 20px;">
        <input type="hidden" name="movie_id" value="<?= $media['id'] ?>" />
        <?php if ($alreadyAdded): ?>
          <button type="button" disabled class="add-watchlist-btn-large added">Added to Watchlist</button>
        <?php else: ?>
          <button type="submit" class="add-watchlist-btn-large">Add to Watchlist</button>
        <?php endif; ?>
      </form>
    <?php endif; ?>
  </div>
</main>

</body>
</html>
