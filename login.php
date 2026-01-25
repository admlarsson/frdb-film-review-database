<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Logga in – FRDb</title>
  <link rel="stylesheet" href="css/topbar.css">
  <link rel="stylesheet" href="css/login.css">
  <script src="JS/validator.js" defer></script>
</head>
<body>

  <?php include 'views/topbar.php'; ?>

  <main class="login-container">
    <!-- Vänstra inloggningsdelen -->
    <section class="login-box">
      <h2>Sign in</h2>
      <?php
        $error = $_GET['error'] ?? null;
        $success = $_GET['success'] ?? null;

        if ($error === 'missing_fields') {
            echo '<p class="error-message">Please fill in all fields.</p>';
        } elseif ($error === 'user_not_exist') {
            echo '<p class="error-message">No account with that email found.</p>';
        } elseif ($error === 'wrong_password') {
            echo '<p class="error-message">Incorrect password.</p>';
        } elseif ($error === 'database_error') {
            echo '<p class="error-message">A server error occurred. Try again later.</p>';
        }
      ?>

      <form action="processes/login_process.php" method="POST" class="login-form" onsubmit="return validateLoginForm()">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" required>
        <p id="email-error" class="error-message js-error"></p> <!-- JS-fel -->

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>
        <p id="password-error" class="error-message js-error"></p> <!-- JS-fel -->

        <button type="submit" class="login-button">
            <img src="IMG/FRDbTransperent.png" alt="FRDb logo" class="login-logo">
            Sign in with FRDb
        </button>

      </form>

      <div class="divider"><span>or</span></div>

      <a href="register.php" class="create-account-button">Create a New Account</a>

      
    </section>

    <!-- Höger infokolumn -->
    <aside class="login-info">
      <h3>Benefits of your free FRDb account</h3>
      <ul>
        <li><strong>Personalized Recommendations</strong><br>Get movie tips based on your taste.</li>
        <li><strong>Your Watchlist</strong><br>Track what you want to watch.</li>
        <li><strong>Your Ratings</strong><br>Rate and remember your favorites.</li>
        <li><strong>Contribute to FRDb</strong><br>Add and improve movie data.</li>
      </ul>
    </aside>
  </main>

</body>
</html>
