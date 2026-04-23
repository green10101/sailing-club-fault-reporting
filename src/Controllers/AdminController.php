<?php

namespace src\Controllers;

use src\Models\User;
use src\Models\BoatCheckin;

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
        $supportsNotifyPreference = User::supportsFaultNotificationPreference();
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

        // Verify CSRF token
        if (!verifyCsrfToken()) {
            $error = 'Security token validation failed. Please try again.';
            include '../src/Views/admin/user_new.php';
            exit;
        }

        $password = $_POST['password'] ?? '';
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $role = $_POST['role'] ?? 'bosun';
        $supportsNotifyPreference = User::supportsFaultNotificationPreference();
        $notifyNewReports = isset($_POST['notify_new_reports']) ? 1 : 0;

        // Validate email format
        if (!isValidEmail($email)) {
            $error = 'Invalid email format.';
            $prefill = compact('name', 'email', 'role', 'notifyNewReports');
            include '../src/Views/admin/user_new.php';
            exit;
        }

        // Validate password length
        if (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters long.';
            $prefill = compact('name', 'email', 'role', 'notifyNewReports');
            include '../src/Views/admin/user_new.php';
            exit;
        }

        try {
            User::createUser($password, $name, $email, $role, $notifyNewReports);
            header('Location: index.php?route=/admin/users');
        } catch (\PDOException $e) {
            $error = 'Failed to create user: ' . $e->getMessage();
            $prefill = compact('name', 'email', 'role', 'notifyNewReports');
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
        $supportsNotifyPreference = User::supportsFaultNotificationPreference();
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

        // Verify CSRF token
        if (!verifyCsrfToken()) {
            $error = 'Security token validation failed. Please try again.';
            $user = User::getUserById($userId);
            include '../src/Views/admin/user_edit.php';
            exit;
        }

        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $role = $_POST['role'] ?? 'bosun';
        $notifyNewReports = isset($_POST['notify_new_reports']) ? 1 : 0;
        $supportsNotifyPreference = User::supportsFaultNotificationPreference();

        // Validate email format
        if (!isValidEmail($email)) {
            $error = 'Invalid email format.';
            $user = User::getUserById($userId);
            include '../src/Views/admin/user_edit.php';
            exit;
        }

        try {
            User::updateUser($userId, $name, $email, $role, $notifyNewReports);
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
            // Verify CSRF token
            if (!verifyCsrfToken()) {
                $error = 'Security token validation failed. Please try again.';
                include '../src/Views/admin/reset_password.php';
                exit;
            }

            $newPassword = $_POST['new_password'] ?? '';
            if ($newPassword) {
                // Validate password length
                if (strlen($newPassword) < 6) {
                    $error = 'Password must be at least 6 characters long.';
                    include '../src/Views/admin/reset_password.php';
                    exit;
                }

                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                User::resetPassword($userId, $hashedPassword);
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
        $role = isset($_SESSION['user']['role']) ? strtolower(trim((string) $_SESSION['user']['role'])) : '';
        return $role === 'admin';
    }

    public function deleteReport($id)
    {
        if (!$this->isAdmin()) {
            header('Location: index.php?route=/login');
            exit;
        }

        $reportModel = new \src\Models\Report();
        $deleted = $reportModel->deleteReport($id);

        if ($deleted) {
            $_SESSION['flash_message'] = 'Fault report deleted successfully.';
        } else {
            $_SESSION['flash_message'] = 'Could not delete the fault report. Please try again.';
        }

        header('Location: index.php?route=/bosun/dashboard');
        exit;
    }

    public function deleteCheckin($id)
    {
        if (!$this->isAdmin()) {
            header('Location: index.php?route=/login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCsrfToken()) {
            $_SESSION['flash_message'] = 'Security token validation failed. Please try again.';
            header('Location: index.php?route=/bosun/checkins');
            exit;
        }

        $boatCheckinModel = new BoatCheckin();
        $deleted = $boatCheckinModel->deleteCheckin((int) $id);

        if ($deleted) {
            $_SESSION['flash_message'] = 'Boat check-in deleted successfully.';
        } else {
            $_SESSION['flash_message'] = 'Could not delete the boat check-in. Please try again.';
        }

        header('Location: index.php?route=/bosun/checkins');
        exit;
    }
}
