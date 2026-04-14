<?php

namespace src\Models;

use PDO;

class User {
    private $db;
    private $table = 'users';
    private $id;
    private $username;
    private $password;
    private $name;
    private $email;
    private $role;

    public function __construct($db = null) {
        if ($db === null) {
            $this->db = $GLOBALS['pdo'];
        } else {
            $this->db = $db;
        }
    }

    public function getId() {
        return $this->id;
    }

    public function getUsername() {
        return $this->username;
    }

    public function getName() {
        return $this->name;
    }

    public function getEmail() {
        return $this->email;
    }

    public function getRole() {
        return $this->role;
    }

    public function verifyPassword($password) {
        // Use password_verify for hashed passwords
        return password_verify($password, $this->password);
    }

    // Static method for database queries
    public static function findByUsername($username) {
        $db = $GLOBALS['pdo'];
        // Use email as the login identifier
        $stmt = $db->prepare("SELECT * FROM users WHERE email = :username");
        $stmt->bindValue(':username', $username);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function getAllUsers() {
        $db = $GLOBALS['pdo'];
        $select = "id, password, name, email, role, login_count";
        if (self::supportsFaultNotificationPreference()) {
            $select .= ", notify_new_reports";
        }

        $stmt = $db->prepare("SELECT " . $select . " FROM users ORDER BY email");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getUserById($id) {
        $db = $GLOBALS['pdo'];
        $select = "id, password, name, email, role, login_count";
        if (self::supportsFaultNotificationPreference()) {
            $select .= ", notify_new_reports";
        }

        $stmt = $db->prepare("SELECT " . $select . " FROM users WHERE id = :id");
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function createUser($password, $name, $email, $role = 'bosun', $notifyNewReports = 0) {
        $db = $GLOBALS['pdo'];
        try {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $supportsNotifyPreference = self::supportsFaultNotificationPreference();

            $query = "INSERT INTO users (password, name, email, role";
            $values = " VALUES (:password, :name, :email, :role";
            if ($supportsNotifyPreference) {
                $query .= ", notify_new_reports";
                $values .= ", :notify_new_reports";
            }
            $query .= ")" . $values . ")";

            $stmt = $db->prepare($query);
            $stmt->bindValue(':password', $hashedPassword);
            $stmt->bindValue(':name', $name);
            $stmt->bindValue(':email', $email);
            $stmt->bindValue(':role', $role);
            if ($supportsNotifyPreference) {
                $stmt->bindValue(':notify_new_reports', $notifyNewReports ? 1 : 0, PDO::PARAM_INT);
            }
            return $stmt->execute();
        } catch (\PDOException $e) {
            throw $e;
        }
    }

    public static function updateUser($id, $name, $email, $role, $notifyNewReports = null) {
        $db = $GLOBALS['pdo'];
        $query = "UPDATE users SET name = :name, email = :email, role = :role";
        if (self::supportsFaultNotificationPreference() && $notifyNewReports !== null) {
            $query .= ", notify_new_reports = :notify_new_reports";
        }
        $query .= " WHERE id = :id";

        $stmt = $db->prepare($query);
        $stmt->bindValue(':id', $id);
        $stmt->bindValue(':name', $name);
        $stmt->bindValue(':email', $email);
        $stmt->bindValue(':role', $role);
        if (self::supportsFaultNotificationPreference() && $notifyNewReports !== null) {
            $stmt->bindValue(':notify_new_reports', $notifyNewReports ? 1 : 0, PDO::PARAM_INT);
        }
        return $stmt->execute();
    }

    public static function updateUserProfile($id, $name, $email, $notifyNewReports = null) {
        $db = $GLOBALS['pdo'];
        $query = "UPDATE users SET name = :name, email = :email";
        if (self::supportsFaultNotificationPreference() && $notifyNewReports !== null) {
            $query .= ", notify_new_reports = :notify_new_reports";
        }
        $query .= " WHERE id = :id";

        $stmt = $db->prepare($query);
        $stmt->bindValue(':id', $id);
        $stmt->bindValue(':name', $name);
        $stmt->bindValue(':email', $email);
        if (self::supportsFaultNotificationPreference() && $notifyNewReports !== null) {
            $stmt->bindValue(':notify_new_reports', $notifyNewReports ? 1 : 0, PDO::PARAM_INT);
        }
        return $stmt->execute();
    }

    public static function deleteUser($id) {
        $db = $GLOBALS['pdo'];
        $stmt = $db->prepare("DELETE FROM users WHERE id = :id");
        $stmt->bindValue(':id', $id);
        return $stmt->execute();
    }

    public static function resetPassword($id, $newPassword) {
        $db = $GLOBALS['pdo'];
        $stmt = $db->prepare("UPDATE users SET password = :password WHERE id = :id");
        $stmt->bindValue(':id', $id);
        $stmt->bindValue(':password', $newPassword);
        return $stmt->execute();
    }

    public static function incrementLoginCount($id) {
        $db = $GLOBALS['pdo'];
        $stmt = $db->prepare("UPDATE users SET login_count = login_count + 1 WHERE id = :id");
        $stmt->bindValue(':id', $id);
        return $stmt->execute();
    }

    public static function getFaultNotificationEmails() {
        $db = $GLOBALS['pdo'];

        try {
            $columns = $db->query("SHOW COLUMNS FROM users")->fetchAll(PDO::FETCH_COLUMN, 0);
            if (!in_array('notify_new_reports', $columns, true) || !in_array('email', $columns, true)) {
                return [];
            }

            $stmt = $db->prepare("SELECT email FROM users WHERE notify_new_reports = 1 AND email IS NOT NULL AND email <> ''");
            $stmt->execute();
            $emails = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

            if (!is_array($emails)) {
                return [];
            }

            return array_values(array_unique(array_filter($emails, function ($email) {
                return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
            })));
        } catch (\Throwable $e) {
            // Keep compatibility with older schemas and installations.
            return [];
        }
    }

    public static function supportsFaultNotificationPreference() {
        $db = $GLOBALS['pdo'];

        try {
            $columns = $db->query("SHOW COLUMNS FROM users")->fetchAll(PDO::FETCH_COLUMN, 0);
            return in_array('notify_new_reports', $columns, true);
        } catch (\Throwable $e) {
            return false;
        }
    }
}