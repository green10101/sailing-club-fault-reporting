<?php

namespace App\Controllers;

use App\Models\User;

class AuthController
{
    public function login($username, $password)
    {
        global $pdo;
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user'] = $user;
            return true;
        }
        return false;
    }

    public function logout()
    {
        // Logic for handling user logout
        // Destroy session and redirect to login page
    }

    public function register($request)
    {
        // Logic for handling user registration
        // Validate input and create a new user
    }

    public function showLoginForm()
    {
        // Logic to display the login form
    }
}