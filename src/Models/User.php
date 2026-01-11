<?php

class User {
    private $id;
    private $username;
    private $password;
    private $email;

    public function __construct($id, $username, $password, $email) {
        $this->id = $id;
        $this->username = $username;
        $this->password = $password;
        $this->email = $email;
    }

    public function getId() {
        return $this->id;
    }

    public function getUsername() {
        return $this->username;
    }

    public function getEmail() {
        return $this->email;
    }

    public function verifyPassword($password) {
        return password_verify($password, $this->password);
    }

    public static function findById($id) {
        // Logic to find a user by ID from the database
    }

    public static function findByUsername($username) {
        // Logic to find a user by username from the database
    }

    public function save() {
        // Logic to save the user to the database
    }
}