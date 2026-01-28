<?php

namespace App\Models;

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
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function getAllUsers() {
        $db = $GLOBALS['pdo'];
        $stmt = $db->prepare("SELECT id, password, name, email, role, login_count FROM users ORDER BY email");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getUserById($id) {
        $db = $GLOBALS['pdo'];
        $stmt = $db->prepare("SELECT id, password, name, email, role, login_count FROM users WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function createUser($password, $name, $email, $role = 'bosun') {
        $db = $GLOBALS['pdo'];
        try {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO users (password, name, email, role) VALUES (:password, :name, :email, :role)");
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':role', $role);
            return $stmt->execute();
        } catch (\PDOException $e) {
            throw $e;
        }
    }

    public static function updateUser($id, $name, $email, $role) {
        $db = $GLOBALS['pdo'];
        $stmt = $db->prepare("UPDATE users SET name = :name, email = :email, role = :role WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':role', $role);
        return $stmt->execute();
    }

    public static function deleteUser($id) {
        $db = $GLOBALS['pdo'];
        $stmt = $db->prepare("DELETE FROM users WHERE id = :id");
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public static function resetPassword($id, $newPassword) {
        $db = $GLOBALS['pdo'];
        $stmt = $db->prepare("UPDATE users SET password = :password WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':password', $newPassword);
        return $stmt->execute();
    }

    public static function incrementLoginCount($id) {
        $db = $GLOBALS['pdo'];
        $stmt = $db->prepare("UPDATE users SET login_count = login_count + 1 WHERE id = :id");
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}