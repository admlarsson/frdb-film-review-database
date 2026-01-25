<?php
// Denna fil innehåller din TMDb API-nyckel samt en generell funktion för att göra API-anrop.
// - define('TMDB_API_KEY', ...) sätter din privata nyckel för TMDb.
// - tmdbApiRequest() skickar ett anrop till ett valfritt endpoint med parametrar och returnerar svaret som array.
define('TMDB_API_KEY', '656276654d7dd1a9e2681b614c026f28'); // api anropet till TMDb

function tmdbApiRequest($endpoint, $params = []) {
    $baseUrl = "https://api.themoviedb.org/3";
    $params['api_key'] = TMDB_API_KEY;
    $url = $baseUrl . $endpoint . '?' . http_build_query($params);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    if ($response === false) {
        curl_close($ch);
        return null;
    }

    curl_close($ch);
    return json_decode($response, true);
}
