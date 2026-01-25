<?php
// Denna API-fil hämtar en femdagars väderprognos från Open-Meteo för Uppsala,
// och returnerar både prognosen och en rekommendation beroende på vädret:
// - Vid regn: rekommenderas en film från TMDb
// - Vid sol: rekommenderas en utomhusaktivitet
// - Vid blandat väder: rekommenderas både film och aktivitet
// Resultatet returneras som JSON till klienten
header('Content-Type: application/json');
require_once "../api/tmdb.php"; // Innehåller TMDB_API_KEY

$lat = 59.8586;
$lon = 17.6389;

$weatherUrl = "https://api.open-meteo.com/v1/forecast?latitude=$lat&longitude=$lon&daily=weathercode,precipitation_sum,temperature_2m_min,temperature_2m_max&timezone=Europe/Stockholm";
$weatherResponse = file_get_contents($weatherUrl);
$weatherData = json_decode($weatherResponse, true);

$days = $weatherData['daily']['time'];
$precip = $weatherData['daily']['precipitation_sum'];
$tempMin = $weatherData['daily']['temperature_2m_min'];
$tempMax = $weatherData['daily']['temperature_2m_max'];

$activities = [
    "Go for a walk in the park 🌳",
    "Play football with friends ⚽",
    "Have a picnic 🧺",
    "Go for a bike ride 🚴‍♀️",
    "Explore the city 🏙️",
    "Visit an outdoor market 🛍️",
    "Do yoga in the garden 🧘‍♀️",
    "Take photos in nature 📸"
];

function getWeatherSymbol($rain) {
    if ($rain > 3) return "🌧️";     // heavy rain
    if ($rain > 1) return "🌦️";     // some rain
    if ($rain > 0) return "☁️";     // cloudy
    return "☀️";                     // sunny
}

function getRandomTMDbMovie() {
    $apiKey = TMDB_API_KEY;
    $page = rand(1, 20);
    $url = "https://api.themoviedb.org/3/discover/movie?api_key=$apiKey&sort_by=popularity.desc&page=$page&language=en-US";

    $response = file_get_contents($url);
    $data = json_decode($response, true);

    if (isset($data['results']) && count($data['results']) > 0) {
        $movie = $data['results'][array_rand($data['results'])];
        return [
            'type' => 'movie',
            'id' => $movie['id'],                // <-- Lägg till detta
            'title' => $movie['title'],
            'poster' => $movie['poster_path']
                ? "https://image.tmdb.org/t/p/w300" . $movie['poster_path']
                : "IMG/Placeholder.png"
        ];
    }

    return null;
}


// Skapa 5-dagarsprognos
$forecast = [];
for ($i = 0; $i < min(5, count($days)); $i++) {
    $forecast[] = [
        'date' => strtoupper(date("D", strtotime($days[$i]))), // t.ex. FRI
        'symbol' => getWeatherSymbol($precip[$i]),
        'min_temp' => round($tempMin[$i]),
        'max_temp' => round($tempMax[$i]),
        'rain' => round($precip[$i], 1)
    ];
}


// Baserat på första dagens väder avgör vi vad som rekommenderas
$hasRain = $precip[0] > 0.8;
$hasSun = $precip[0] <= 1.5 && $tempMax[0] > 10;

if ($hasRain && $hasSun) {
    // Blandat väder: rekommendera både film och aktivitet
    $recommendation = [
        'type' => 'both',
        'movie' => getRandomTMDbMovie(),
        'activity' => $activities[array_rand($activities)]
    ];
} elseif ($hasRain) {
    $recommendation = getRandomTMDbMovie();
} else {
    $recommendation = [
        'type' => 'activity',
        'activity' => $activities[array_rand($activities)]
    ];
}


echo json_encode([
    'forecast' => $forecast,
    'recommendation' => $recommendation
]);
