<?php

namespace App\Controllers;

use App\Models\User;

class AdminController
{
    public function users()
    {
        if (!$this->isAdmin()) {
            header('Location: index.php?route=/login');
            exit;
        }
        $users = User::getAllUsers();
        include '../src/Views/admin/users.php';
    }

    public function newUser()
    {
        if (!$this->isAdmin()) {
            header('Location: index.php?route=/login');
            exit;
        }
        include '../src/Views/admin/user_new.php';
    }

    public function createUser()
    {
        if (!$this->isAdmin()) {
            header('Location: index.php?route=/login');
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?route=/admin/users');
            exit;
        }

        $password = $_POST['password'] ?? '';
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $role = $_POST['role'] ?? 'bosun';

        try {
            User::createUser($password, $name, $email, $role);
            header('Location: index.php?route=/admin/users');
        } catch (\PDOException $e) {
            $error = 'Failed to create user: ' . $e->getMessage();
            $prefill = compact('name', 'email', 'role');
            include '../src/Views/admin/user_new.php';
        }
    }

    public function editUser($userId)
    {
        if (!$this->isAdmin()) {
            header('Location: index.php?route=/login');
            exit;
        }
        $user = User::getUserById($userId);
        if (!$user) {
            header('Location: index.php?route=/admin/users');
            exit;
        }
        include '../src/Views/admin/user_edit.php';
    }

    public function updateUser($userId)
    {
        if (!$this->isAdmin()) {
            header('Location: index.php?route=/login');
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?route=/admin/users');
            exit;
        }

        $password = $_POST['password'] ?? '';
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $role = $_POST['role'] ?? 'bosun';

        try {
            User::updateUser($userId, $password, $name, $email, $role);
            header('Location: index.php?route=/admin/users');
        } catch (\PDOException $e) {
            $error = 'Failed to update user: ' . $e->getMessage();
            $user = User::getUserById($userId);
            include '../src/Views/admin/user_edit.php';
        }
    }

    public function deleteUser($userId)
    {
        if (!$this->isAdmin()) {
            header('Location: index.php?route=/login');
            exit;
        }

        if (User::deleteUser($userId)) {
            header('Location: index.php?route=/admin/users');
        } else {
            header('Location: index.php?route=/admin/users?error=1');
        }
    }

    public function resetPassword($userId)
    {
        if (!$this->isAdmin()) {
            header('Location: index.php?route=/login');
            exit;
        }

        $user = User::getUserById($userId);
        if (!$user) {
            header('Location: index.php?route=/admin/users');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $newPassword = $_POST['new_password'] ?? '';
            if ($newPassword) {
                User::resetPassword($userId, $newPassword);
                header('Location: index.php?route=/admin/users');
            } else {
                $error = 'Password cannot be empty.';
                include '../src/Views/admin/reset_password.php';
            }
        } else {
            include '../src/Views/admin/reset_password.php';
        }
    }

    private function isAdmin()
    {
        return isset($_SESSION['user']) && isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin';
    }
}
