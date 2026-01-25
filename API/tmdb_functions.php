<?php
// Denna fil innehåller två hjälpfunktioner för att kommunicera med TMDb API:
// - tmdbSearch: söker efter filmer eller serier baserat på söksträng och typ (movie/tv)
// - getMovieDetails: hämtar detaljerad information om en specifik titel via TMDb-ID
// Kräver att TMDB_API_KEY är definierad i tmdb.php
require_once "tmdb.php"; // där du har define 

function tmdbSearch($query, $type = "movie") {
    $url = "https://api.themoviedb.org/3/search/$type?api_key=" . TMDB_API_KEY . "&query=" . urlencode($query) . "&language=en-US";

    $response = file_get_contents($url);
    if (!$response) return [];

    $data = json_decode($response, true);
    return $data['results'] ?? [];
}

function getMovieDetails($tmdbId, $type = "movie") {
    $url = "https://api.themoviedb.org/3/$type/$tmdbId?api_key=" . TMDB_API_KEY . "&language=en-US";

    $response = file_get_contents($url);
    if (!$response) return null;

    return json_decode($response, true);
}
?>
