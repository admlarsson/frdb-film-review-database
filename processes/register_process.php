<?php
require_once "../models/db.php"; 


$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirmPassword'] ?? '';


if (!$username) 
{
    header("Location: ../register.php?error=username_missing");
    exit;
}

if (!$email)
{
    header("Location: ../register.php?error=email_missing");
    exit;
}



if (!filter_var($email, FILTER_VALIDATE_EMAIL)) 
{
    header("Location: ../register.php?error=invalid_email");
    exit;
}


if (strlen($password) < 8) 
{
    header("Location: ../register.php?error=weak_password");
    exit;
}

if ($password !== $confirmPassword)
{
    header("Location: ../register.php?error=password_mismatch");
    exit;
}

try {
    $pdo = getPDO(); 

    // Kontrollera om användarnamnet redan finns
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);

    if ($stmt->fetch()) {
        header("Location: ../register.php?error=username_exists");
        exit;
    }
    //kontrollerar om email används
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);

    if ($stmt->fetch()) {
        header("Location: ../register.php?error=email_exists");
        exit;
    }    

    $salt = bin2hex(random_bytes(16));
    $hashedPassword = hash("sha256", $salt . $password);

    
    

    $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, salt) VALUES (?, ?, ?, ?)");
    $stmt->execute([$username, $email, $hashedPassword, $salt]);

    header("Location: ../login.php?success=registered");
    exit;

} catch (PDOException $e) {
    echo("Databasfel: " . $e->getMessage());
    return;
}

file_put_contents("debug.txt", "Form submitted\n", FILE_APPEND);
