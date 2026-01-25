// Hanterar när en inloggad användare vill ratea direkt på index sidan 


// Laddar in hela dokumentet
document.addEventListener("DOMContentLoaded", function () {

  // Hämtar stjärnor för att lägga till nytt betyg
  const transparentStars = document.querySelectorAll(".transparent-star");

  // Hämtar alla stjärnor för användarens befintliga betyg
  const userRatingButtons = document.querySelectorAll(".user-rating");

  // går igenom varje transparent stjärna på sidan
  transparentStars.forEach(function (btn) {
    btn.addEventListener("click", function (e) {
      e.stopPropagation(); // Hindrar att klicket stänger andra element (som dropdowns)

      const wrapper = btn.closest(".rate-wrapper"); // Hittar närmaste betygscontainer
      const form = wrapper.querySelector(".inline-rate-form"); // Tar fram det formulär so mligger dolt under stjärnan

      // Visar formuläret oxh döljer samtidigt stjärnan och sätter fokus på betygsfältet
      if (form) {
        form.style.display = "flex";
        btn.style.display = "none";
        const input = form.querySelector(".inline-score-input");
        if (input) input.focus();
      }
    });
  });

  // Går igenom varje knapp som visar användarens tidigare betyg (blå stjärnor med siffror)
  userRatingButtons.forEach(function (btn) {
    btn.addEventListener("click", function (e) {
      e.stopPropagation();
      const wrapper = btn.closest(".rate-wrapper");
      const form = wrapper.querySelector(".inline-rate-form");

      if (form) {
        form.style.display = "flex";
        btn.style.display = "none";
        const input = form.querySelector(".inline-score-input");
        if (input) input.focus();
      }
    });
  });
});
