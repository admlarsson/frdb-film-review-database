// Filen hanterar vänförfrågningar när man skickar dem tar emot dom och svarar

// Laddar in hela friends.php 
document.addEventListener("DOMContentLoaded", () => {

    // Hämtar alla formulär som finns inuti friend request form 
    const friendRequestForms = document.querySelectorAll(".friend-requests form");
  
    // Loopa igenom varje formulär en per förfrågning
    friendRequestForms.forEach(form => {

      form.addEventListener("submit", async (e) => {
        e.preventDefault();
  
        const submitter = e.submitter; // Hittar vilken knapp som blev tryckt på  Accept eller decline
        if (!submitter) {
          alert("Could not determine which button was clicked.");
          return;
        }
  
        const action = submitter.value; 
        const formData = new FormData(form);
        formData.set("action", action); // skriver över action med rätt värde
  
        try {
          const response = await fetch("processes/respond_friend_request.php", {
            method: "POST",
            body: formData
          });
  
          if (!response.ok) throw new Error("Network error");
  
          // Ta bort förfrågan från listan visuellt
          form.closest("li").remove();
  
          // Visa bekräftelsemeddelande
          const msg = document.createElement("div");
          msg.className = "confirmation-message";
          msg.textContent = action === "accept" ? "Friend request accepted!" : "Friend request declined!";
          document.querySelector(".friend-requests").prepend(msg);
  
          // Ta bort meddelandet efter 3 sekunder
          setTimeout(() => msg.remove(), 3000);
  
          // Om något går fel så visas detta
        } catch (err) {
          alert("Something went wrong, please try again.");
          console.error(err);
        }
      });
    });
  });
  