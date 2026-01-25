<?php
require_once __DIR__ . "/../models/session_user.php";


?>
<div class="topbar">
  <div class="topbar-left">
    <a href="index.php">
      <img src="IMG/FRDbTransperent.png" alt="FRDb" class="logo">
    </a>
  </div>

  <div class="topbar-center">
    <form action="search.php" method="GET">
      <input type="text" name="q" placeholder="Search After Movies, Series and more..." class="search-input">
    </form>
  </div>

  <div class="topbar-right">
    <?php if (isLoggedIn()): ?>
      <div class="profile-dropdown">
        <div class="profile-toggle" id="profileToggle">
          <img src="IMG/profile_icon.png" alt="Profile" class="profile-icon">
          <span class="profile-name"><?= htmlspecialchars($_SESSION['username']) ?></span>
        </div>

        <div class="dropdown-menu" id="profileMenu">
          <a href="profile.php">Your Profile</a>
          <a href="friends.php">Your Friends</a>
          <a href="watchlist.php">Your Watchlist</a>
          <a href="ratings.php">Your Ratings</a>
          <?php if (isAdmin()): ?>
            <a href="admin.php">Admin</a>
          <?php endif; ?>

          <a href="logout.php">Sign Out</a>

          
        </div>
      </div>
    <?php else: ?>
      <a href="login.php" class="topbar-button">Sign In</a>
    <?php endif; ?>
  </div>
</div>

<script src="JS/topbar.js"></script>
