<?php
// import_and_redirect.php
// Tar emot TMDb film-ID via GET, kollar om filmen finns i lokal databas.
// Om inte, hämtar från TMDb API och sparar i databasen.
// Sedan redirectar den till movies.php med det lokala ID:t.

// Inkludera databas och TMDb API-konstant
require_once __DIR__ . "/../models/db.php";
require_once __DIR__ . "/../api/tmdb.php";

// Kontrollera att tmdb_id finns i GET-parametrar
if (!isset($_GET['tmdb_id'])) {
    // Om inget ID skickats, skicka tillbaka till startsidan
    header("Location: index.php");
    exit;
}

// Rensa TMDb ID så att det är heltal
$tmdb_id = intval($_GET['tmdb_id']);

// Skapa PDO-anslutning
$pdo = getPDO();

// Steg 1: Kontrollera om filmen redan finns i databasen (kolumn "tmdb_id" enligt din admin_import.php)
$stmt = $pdo->prepare("SELECT id FROM media WHERE tmdb_id = ?");
$stmt->execute([$tmdb_id]);
$local_id = $stmt->fetchColumn();

if (!$local_id) {
    // Steg 2: Filmen finns inte i databasen - hämta från TMDb API
    $url = "https://api.themoviedb.org/3/movie/$tmdb_id?api_key=" . TMDB_API_KEY . "&language=en-US";
    $response = file_get_contents($url);
    $data = json_decode($response, true);

    if ($data && isset($data['id'])) {
        // Förbered genre-sträng
        $genreList = array_map(fn($g) => $g['name'], $data['genres']);
        $genres = implode(", ", $genreList);

        // Poster URL - kontrollera att poster_path finns
        $poster_url = isset($data['poster_path']) && $data['poster_path'] ? "https://image.tmdb.org/t/p/w500" . $data['poster_path'] : "";

        // Steg 3: Spara filmen i databasen
        $stmt = $pdo->prepare("
            INSERT INTO media (tmdb_id, title, year, genre, poster_url, type, description)
            VALUES (?, ?, ?, ?, ?, 'movie', ?)
        ");

        $stmt->execute([
            $data['id'],
            $data['title'] ?? '',
            substr($data['release_date'] ?? '', 0, 4),
            $genres,
            $poster_url,
            $data['overview'] ?? ''
        ]);

        // Hämta ID på den nya raden
        $local_id = $pdo->lastInsertId();
    } else {
        // Om TMDb API misslyckades, skicka tillbaka till index
        header("Location: index.php");
        exit;
    }
}

// Steg 4: Redirect till movies.php med det lokala ID:t
header("Location: ../movies.php?id=" . $local_id);
exit;
