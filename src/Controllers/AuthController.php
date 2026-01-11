<?php

namespace App\Controllers;

use App\Models\User;

class AuthController
{
    public function login($request)
    {
        // Logic for handling user login
        // Validate credentials and start session
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