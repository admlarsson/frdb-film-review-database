<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isAdmin(): bool {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

function requireLoginOrRedirect($redirectTo = 'login.php') {
    if (!isLoggedIn()) {
        header("Location: $redirectTo?error=not_logged_in");
        exit;
    }
}