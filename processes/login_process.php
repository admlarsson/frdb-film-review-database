<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


require_once "../models/db.php";

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (!$email || !$password)
{
    header("Location: ../login.php?error=missing_fields");
    exit;
}

try
    {
        $pdo = getPDO();

        $stmt = $pdo->prepare("SELECT id, username, password_hash, salt, role FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user)
        {
            header("Location: ../login.php?error=user_not_exist");
        exit;
        }

        $inputHash = hash("sha256",$user['salt'] . $password);

        if($inputHash === $user['password_hash']) 
        {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            session_regenerate_id(true);  // för att undvika seesion hijacking

            header("Location: ../index.php");
            exit;
        }
        else 
        {
            header("Location: ../login.php?error=wrong_password");
            exit;
         }

        

    } catch (PDOException $e) 
        {
            header("Location: ../login.php?error=database_error");
            exit;
        }


    