// Visar en fem dagars prognos för uppsala och ger en rekomendation beroende på om det ska regna eller ej


// laddar in allt innehåll på sidan innan koden körs
document.addEventListener("DOMContentLoaded", function () {

// Hämtar väderprognos + rekomendation via api 
fetch("api/weather_api.php")
  .then(response => response.json())
  .then(data => {
    const weatherBox = document.getElementById("weatherBox");
    const content = document.getElementById("weatherContent");

    // Presenterar genom HTML varje dag i väderprognosen 
    const forecastHTML = data.forecast.map(day => `
        <div class="weather-day">
          <div class="weather-row">
            <div class="weather-date-icon">
              <span class="weather-date">${day.date}</span>
              <span class="weather-icon">${day.symbol}</span>
            </div>
            <div class="weather-info">
              <span class="weather-temp">${day.min_temp}° / ${day.max_temp}°</span>
              <span class="separator">|</span>
              <span class="weather-rain">${day.rain} MM</span>
            </div>
          </div>
        </div>
      `).join(""); // Slår ihop allt till en sträng
      

    // Skapar en rekommendation anntingen film eller aktivitet 
    // Skapar en rekommendation baserat på vädertyp
    let recommendationHTML = `<h4>Recommendation:</h4>`;

    if (data.recommendation.type === "movie") {
      // Endast film rekommenderas
      recommendationHTML += `
        <a href="processes/import_and_redirect.php?tmdb_id=${data.recommendation.id}" class="recommendation-link" title="${data.recommendation.title}">
          <p>${data.recommendation.title}</p>
          <img src="${data.recommendation.poster}" alt="${data.recommendation.title}" class="weather-movie-poster">
        </a>
      `;
    } else if (data.recommendation.type === "activity") {
      // Endast aktivitet rekommenderas
      recommendationHTML += `<p>${data.recommendation.activity}</p>`;
    } else if (data.recommendation.type === "both") {
      // Blandat väder – visa både film och aktivitet
      recommendationHTML += `
        <div class="weather-mixed">
          <p>Looks like mixed weather! Why not...</p>
          <div class="weather-suggestion">
            <div class="weather-movie">
              <a href="processes/import_and_redirect.php?tmdb_id=${data.recommendation.movie.id}" class="recommendation-link" title="${data.recommendation.movie.title}">
                <p>${data.recommendation.movie.title}</p>
                <img src="${data.recommendation.movie.poster}" alt="${data.recommendation.movie.title}" class="weather-movie-poster">
              </a>
            </div>
            <div class="weather-activity">
              <p>...and also: ${data.recommendation.activity}</p>
            </div>
          </div>
        </div>
      `;
    }

    

    // Fyler hela containern med prognos och rekomendation
    content.innerHTML = `
      <div class="weather-forecast">${forecastHTML}</div>
      <div class="recommendation-box">${recommendationHTML}</div>
    `;
  })
  // Om något går fel visas detta
  .catch(error => {
    console.error("Kunde inte hämta väderdata:", error);
    document.getElementById("weatherContent").innerHTML = "Could not load weather data.";
  });
});
