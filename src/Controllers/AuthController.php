<?php

namespace App\Controllers;

use App\Models\User;

class AuthController
{
    public function login($username, $password)
    {
        $user = User::findByUsername($username);
        if ($user && $user['password'] === $password) { // Plain text comparison
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