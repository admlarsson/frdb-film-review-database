<?php
// Denna fil är en adminpanel som visar alla användare i systemet.
// Admin kan se användarinformation, ändra roll (user/admin) och radera andra användare.
// Inloggad användare kan inte radera eller ändra sin egen roll.
// Data hämtas från users-tabellen via PDO och visas i en HTML-tabell.
session_start();
require_once __DIR__ . "/models/session_user.php";
require_once "models/db.php";


try {
    $pdo = getPDO();
    $stmt = $pdo->query("SELECT id, username, email, role, date_joined FROM users");
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Panel</title>
  <link rel="stylesheet" href="css/admin.css"> <!-- ändrade till gemener om resten av projektet använder det -->
  <link rel="stylesheet" href="css/topbar.css">
</head>
<body class="admin-page">
  <?php include "views/topbar.php"; ?>

  <div class="admin-container">
    <h1 class="admin-title">Admin Panel</h1>
    <table class="admin-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Username</th>
          <th>Email</th>
          <th>Role</th>
          <th>Date joined</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($users as $user): ?>
          <tr>
            <td><?= htmlspecialchars($user['id']) ?></td>
            <td><?= htmlspecialchars($user['username']) ?></td>
            <td><?= htmlspecialchars($user['email']) ?></td>
            <td>
              <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                <form method="POST" action="processes/update_role.php" style="display:inline;">
                  <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                  <select name="role" onchange="this.form.submit()">
                    <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>user</option>
                    <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>admin</option>
                  </select>
                </form>
              <?php else: ?>
                <?= htmlspecialchars($user['role']) ?>
              <?php endif; ?>
            </td>
            <td><?= htmlspecialchars($user['date_joined']) ?></td>
            <td>
              <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                <form method="POST" action="processes/delete_user.php" onsubmit="return confirm('Are you sure you want to delete this profile?');">
                  <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                  <button type="submit" class="delete-btn">Radera</button>
                </form>
              <?php else: ?>
                —
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</body>
</html>
