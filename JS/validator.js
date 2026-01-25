// Validerar de uppgifter som läggs in i register och när man ska logga in

// Visar felmeddelande direkt bredvid formuläret
function showError(id, message) {
  const errorElement = document.getElementById(id + "-error");
  if (errorElement) {
    errorElement.textContent = message;
  }
}

// Rensar alla tidigare felmeddelanden
function clearErrors() {
  const errors = document.querySelectorAll(".js-error");
  errors.forEach(e => e.textContent = "");
}

// Validerar register formuläret

function validateForm() {
  clearErrors();
  let valid = true;

  // Hämtar och trimmar alla inmatrningsfält
  const username = document.getElementById("username").value.trim();
  const email = document.getElementById("email").value.trim();
  const password = document.getElementById("password").value;
  const confirmPassword = document.getElementById("confirmPassword").value;

  const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

  // Validering för username
  if (!username) {
    showError("username", "Username is required.");
    valid = false;
  }

  // Validering för email
  if (!email) {
    showError("email", "Email is required.");
    valid = false;
  } else if (!emailPattern.test(email)) {
    showError("email", "Invalid email format.");
    valid = false;
  }

   // Validering för password
  if (!password) {
    showError("password", "Password is required.");
    valid = false;
  } else if (password.length < 8) {
    showError("password", "Password must be at least 8 characters.");
    valid = false;
  }

  // validering för confirm password men matchas bara om lösenordet har rätt format
  if (password.length >= 8) {
    if (!confirmPassword) {
      showError("confirmPassword", "Please confirm your password.");
      valid = false;
    } else if (password !== confirmPassword) {
      showError("confirmPassword", "Passwords do not match.");
      valid = false;
    }
  }

  return valid;
}


// Validerar login formuläret
function validateLoginForm() {
  clearErrors();
  let valid = true;

  const email = document.getElementById("email").value.trim();
  const password = document.getElementById("password").value;
  const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

  // Validering för email
  if (!email) {
    showError("email", "Email is required.");
    valid = false;
  } else if (!emailPattern.test(email)) {
    showError("email", "Invalid email format.");
    valid = false;
  }

  // Validering för password
  if (!password) {
    showError("password", "Password is required.");
    valid = false;
  }

  return valid;
}

