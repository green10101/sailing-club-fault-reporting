<?php

namespace App\Models;

use PDO;

class Report
{
    private $db;
    private $table = 'reports';

    public function __construct($database)
    {
        $this->db = $database;
    }

    public function create($boatName, $faultDescription)
    {
        $stmt = $this->db->prepare("INSERT INTO " . $this->table . " (boat_name, fault_description, created_at) VALUES (:boat_name, :fault_description, NOW())");
        $stmt->bindParam(':boat_name', $boatName);
        $stmt->bindParam(':fault_description', $faultDescription);
        return $stmt->execute();
    }

    public function getAllReports()
    {
        $stmt = $this->db->prepare("SELECT * FROM " . $this->table . " ORDER BY created_at DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getReportById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM " . $this->table . " WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateReportStatus($id, $status)
    {
        $stmt = $this->db->prepare("UPDATE " . $this->table . " SET status = :status WHERE id = :id");
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}