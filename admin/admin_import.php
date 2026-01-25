<?php
// Denna fil låter en användare söka efter filmer eller serier via TMDb API
// och importera vald titel direkt till media-tabellen i databasen.
// Den hanterar både sökning (GET) och import (POST) i samma vy.
require_once "api/tmdb_functions.php";
require_once "models/db.php";

$searchResults = [];

if (isset($_GET['query'])) {
    $type = $_GET['type'] ?? 'movie';
    $searchResults = tmdbSearch($_GET['query'], $type);
}

if (isset($_POST['tmdb_id'])) {
    $type = $_POST['type'] ?? 'movie';
    $details = getMovieDetails($_POST['tmdb_id'], $type);

    if ($details) {
        $pdo = getPDO();

        $stmt = $pdo->prepare("
            INSERT INTO media (ombd_id, title, year, genre, poster_url, type, description)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $genreList = array_map(fn($g) => $g['name'], $details['genres']);
        $genres = implode(", ", $genreList);

        $stmt->execute([
            $details['id'],
            $details['title'] ?? $details['name'],
            substr($details['release_date'] ?? $details['first_air_date'], 0, 4),
            $genres,
            "https://image.tmdb.org/t/p/w500" . $details['poster_path'],
            $type,
            $details['overview']
        ]);

        echo "<p style='color:green;'>Imported successfully!</p>";
    }
}
?>

<h1>Import Media from TMDb</h1>
<form method="get">
    <input type="text" name="query" placeholder="Search for a movie or series..." required>
    <select name="type">
        <option value="movie">Movie</option>
        <option value="tv">TV Series</option>
    </select>
    <button type="submit">Search</button>
</form>

<?php if (!empty($searchResults)): ?>
    <h2>Results:</h2>
    <ul>
        <?php foreach ($searchResults as $result): ?>
            <li>
                <strong><?= htmlspecialchars($result['title'] ?? $result['name']) ?></strong>
                (<?= substr($result['release_date'] ?? $result['first_air_date'] ?? '', 0, 4) ?>)
                <form method="post" style="display:inline;">
                    <input type="hidden" name="tmdb_id" value="<?= $result['id'] ?>">
                    <input type="hidden" name="type" value="<?= $_GET['type'] ?? 'movie' ?>">
                    <button type="submit">Import</button>
                </form>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
