<?php

namespace src\Controllers;

use src\Models\User;

class UserProfileController
{
    public function profile()
    {
        if (!isset($_SESSION['user'])) {
            header('Location: index.php?route=/login');
            exit;
        }
        $userId = $_SESSION['user']['id'];
        $user = User::getUserById($userId);
        if (!$user) {
            header('Location: index.php?route=/bosun/boats');
            exit;
        }
        $supportsNotifyPreference = User::supportsFaultNotificationPreference();
        include '../src/Views/user/profile.php';
    }

    public function editProfile()
    {
        if (!isset($_SESSION['user'])) {
            header('Location: index.php?route=/login');
            exit;
        }
        $userId = $_SESSION['user']['id'];
        $user = User::getUserById($userId);
        if (!$user) {
            header('Location: index.php?route=/bosun/boats');
            exit;
        }
        $supportsNotifyPreference = User::supportsFaultNotificationPreference();
        include '../src/Views/user/profile_edit.php';
    }

    public function updateProfile()
    {
        if (!isset($_SESSION['user'])) {
            header('Location: index.php?route=/login');
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?route=/profile');
            exit;
        }

        // Verify CSRF token
        if (!verifyCsrfToken()) {
            $error = 'Security token validation failed. Please try again.';
            $user = User::getUserById($_SESSION['user']['id']);
            include '../src/Views/user/profile_edit.php';
            exit;
        }

        $userId = $_SESSION['user']['id'];
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $notifyNewReports = isset($_POST['notify_new_reports']) ? 1 : 0;
        $supportsNotifyPreference = User::supportsFaultNotificationPreference();

        // Validate email format
        if (!isValidEmail($email)) {
            $error = 'Invalid email format.';
            $user = User::getUserById($userId);
            include '../src/Views/user/profile_edit.php';
            exit;
        }

        try {
            // Update the user without changing role
            User::updateUserProfile($userId, $name, $email, $notifyNewReports);
            // Update session data
            $_SESSION['user']['name'] = $name;
            $_SESSION['user']['email'] = $email;
            header('Location: index.php?route=/profile');
            exit;
        } catch (\PDOException $e) {
            $error = 'Failed to update profile: ' . $e->getMessage();
            $user = User::getUserById($userId);
            include '../src/Views/user/profile_edit.php';
        }
    }

    public function editPassword()
    {
        if (!isset($_SESSION['user'])) {
            header('Location: index.php?route=/login');
            exit;
        }
        $userId = $_SESSION['user']['id'];
        include '../src/Views/user/password_change.php';
    }

    public function updatePassword()
    {
        if (!isset($_SESSION['user'])) {
            header('Location: index.php?route=/login');
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index.php?route=/profile/change-password');
            exit;
        }

        // Verify CSRF token
        if (!verifyCsrfToken()) {
            $error = 'Security token validation failed. Please try again.';
            include '../src/Views/user/password_change.php';
            exit;
        }

        $userId = $_SESSION['user']['id'];
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        $user = User::getUserById($userId);
        
        // Verify current password
        if (!password_verify($currentPassword, $user['password'])) {
            $error = 'Current password is incorrect.';
            include '../src/Views/user/password_change.php';
            exit;
        }

        // Verify passwords match
        if ($newPassword !== $confirmPassword) {
            $error = 'New passwords do not match.';
            include '../src/Views/user/password_change.php';
            exit;
        }

        // Verify password is not empty and meets minimum length
        if (empty($newPassword)) {
            $error = 'New password cannot be empty.';
            include '../src/Views/user/password_change.php';
            exit;
        }

        if (strlen($newPassword) < 6) {
            $error = 'New password must be at least 6 characters long.';
            include '../src/Views/user/password_change.php';
            exit;
        }

        try {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            User::resetPassword($userId, $hashedPassword);
            $success = 'Password changed successfully.';
            include '../src/Views/user/password_change.php';
        } catch (\PDOException $e) {
            $error = 'Failed to change password: ' . $e->getMessage();
            include '../src/Views/user/password_change.php';
        }
    }
}
