<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Skapa konto – FRDb</title>
  <link rel="stylesheet" href="css/topbar.css">
  <link rel="stylesheet" href="css/login.css">
  <script src="JS/validator.js" defer></script>
</head>
<body>

  <?php include 'views/topbar.php'; ?>

  <?php
    $error = $_GET['error'] ?? null;
    $success = $_GET['success'] ?? null;
  ?>

  <main class="login-container">
    <section class="login-box">
      <h2>Create Account</h2>

      <?php if ($success === 'registered'): ?>
        <p style="color: green;">Account created! Please log in.</p>
      <?php endif; ?>

      <form action="processes/register_process.php" method="POST" class="login-form" onsubmit="return validateForm()">
        
        <!-- USERNAME -->
        <label for="username">Username</label>
        <input type="text" id="username" name="username" required>
        <p id="username-error" class="error-message js-error"></p> <!-- JS-fel -->
        <?php if ($error === 'username_missing'): ?>
          <p class="error-message">Username is required.</p>
        <?php elseif ($error === 'username_exists'): ?>
          <p class="error-message">Username is already taken.</p>
        <?php endif; ?>

        <!-- EMAIL -->
        <label for="email">Email</label>
        <input type="email" id="email" name="email" required>
        <p id="email-error" class="error-message js-error"></p> <!-- JS-fel -->
        <?php if ($error === 'email_missing'): ?>
          <p class="error-message">Email is required.</p>
        <?php elseif ($error === 'invalid_email'): ?>
          <p class="error-message">Invalid email format.</p>
        <?php elseif ($error === 'email_exists'): ?>
          <p class="error-message">Email is already in use.</p>
        <?php endif; ?>

        <!-- PASSWORD -->
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>
        <p id="password-error" class="error-message js-error"></p> <!-- JS-fel -->
        <?php if ($error === 'weak_password'): ?>
          <p class="error-message">Password must be at least 8 characters.</p>
        <?php endif; ?>

        <!-- CONFIRM PASSWORD -->
        <label for="confirmPassword">Confirm Password</label>
        <input type="password" id="confirmPassword" name="confirmPassword" required>
        <p id="confirmPassword-error" class="error-message js-error"></p> <!-- JS-fel -->
        <?php if ($error === 'password_mismatch'): ?>
          <p class="error-message">Passwords do not match.</p>
        <?php endif; ?>

        <button type="submit" class="create-account-button">
          Create your FRDb account
        </button>
      </form>

      <p style="font-size: 14px; margin-top: 10px;">
        Already have an account? <a href="login.php">Sign in here</a>
      </p>
    </section>
  </main>

</body>
</html>
