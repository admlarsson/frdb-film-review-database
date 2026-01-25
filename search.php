<?php
require_once "api/tmdb_functions.php";
require_once "models/db.php";

$query = $_GET['q'] ?? '';
$results = [];

if ($query) {
    $results = tmdbSearch($query, 'multi'); // tillåter både filmer & serier
    $results = array_filter($results, function($item) {
        return in_array($item['media_type'] ?? 'movie', ['movie', 'tv']);
    });
}
?>

<!DOCTYPE html>
<html lang="sv">
<head>
  <meta charset="UTF-8">
  <title>Search – FRDb</title>
  <link rel="stylesheet" href="css/topbar.css" />
  <link rel="stylesheet" href="css/index.css" />
</head>
<body>

<?php include 'views/topbar.php'; ?>

<main class="main-content">
  <h2 class="section-title">Search Results for: "<?= htmlspecialchars($query) ?>"</h2>

  <?php if (!empty($results)): ?>
    <div class="movie-grid">
      <?php foreach ($results as $item): ?>
        <?php
          $isMovie = ($item['media_type'] ?? '') === 'movie' || isset($item['title']);
          $title = $item['title'] ?? $item['name'];
          $year = substr($item['release_date'] ?? $item['first_air_date'] ?? '', 0, 4);
          $poster = $item['poster_path'] 
              ? "https://image.tmdb.org/t/p/w300" . $item['poster_path']
              : "IMG/Placeholder.png"; // lägg till denna bild i din IMG-mapp
              $type = $isMovie ? 'movie' : 'tv';
        ?>
        <div class="movie-card">
        <a href="movies.php?tmdb_id=<?= $item['id'] ?>&type=<?= $item['media_type'] ?>">
            <img src="<?= $poster ?>" class="movie-poster" alt="<?= htmlspecialchars($title) ?>">
            <div class="movie-info">
              <h3><?= htmlspecialchars($title) ?></h3>
              <p><?= $year ?></p>
            </div>
          </a>
        </div>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <p style="color:white;">No results found.</p>
  <?php endif; ?>
</main>

</body>
</html>
