<?php

namespace App\Controllers;

use App\Models\User;

class AuthController
{
    public function login($username, $password)
    {
        $user = User::findByUsername($username);
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user'] = $user;
            // Increment the login count
            User::incrementLoginCount($user['id']);
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