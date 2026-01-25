// Hanterar vår dropdown meny i topbaren och gör så att man kan klicka på profil namnet för att öppna den

document.addEventListener("DOMContentLoaded", function () {

    // Hämtar vad som sak visas i menyn
    const toggle = document.getElementById("profileToggle");

    // hämtar själva menyn
    const menu = document.getElementById("profileMenu");
  
    // När användaren klickar på profilikonen
    toggle.addEventListener("click", function (e) {
      e.stopPropagation();

      // Växlar mellan att visa och dölja menyn
      menu.style.display = (menu.style.display === "block") ? "none" : "block";
    });
  
    // Om man klickar utanför menyn så stängs den
    document.addEventListener("click", function () {
      menu.style.display = "none";
    });
  });
  