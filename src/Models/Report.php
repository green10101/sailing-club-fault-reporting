<?php

namespace App\Models;

use PDO;

class Report
{
    private $db;
    private $table = 'reports';

    public function __construct()
    {
        $this->db = $GLOBALS['pdo'];
    }

    public function create($boatName, $faultDescription)
    {
        $stmt = $this->db->prepare("INSERT INTO " . $this->table . " (boat_name, fault_description, created_at) VALUES (:boat_name, :fault_description, NOW())");
        $stmt->bindParam(':boat_name', $boatName);
        $stmt->bindParam(':fault_description', $faultDescription);
        return $stmt->execute();
    }

    public function getAllReports($filter = 'all', $sortBy = 'created_at', $sortOrder = 'DESC')
    {
        try {
            $query = "SELECT * FROM " . $this->table;
            $params = [];

            if ($filter === 'active') {
                $query .= " WHERE status IN ('New', 'In progress', 'Waiting parts')";
            }

            // Validate sort column to prevent SQL injection
            $allowedSortColumns = ['id', 'boat_name', 'status', 'created_at'];
            if (!in_array($sortBy, $allowedSortColumns)) {
                $sortBy = 'created_at';
            }

            // Validate sort order
            $sortOrder = strtoupper($sortOrder);
            if ($sortOrder !== 'ASC' && $sortOrder !== 'DESC') {
                $sortOrder = 'DESC';
            }

            $query .= " ORDER BY {$sortBy} {$sortOrder}";

            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }
        catch (Exception $e) {
            // Log error and return empty array or default sorting
            error_log("Error in getAllReports: " . $e->getMessage());
            // Fallback to basic query without sorting
            $fallbackQuery = "SELECT * FROM " . $this->table;
            if ($filter === 'active') {
                $fallbackQuery .= " WHERE status IN ('New', 'In progress', 'Waiting parts')";
            }
            $fallbackQuery .= " ORDER BY created_at DESC";
            
            $stmt = $this->db->prepare($fallbackQuery);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }
    }

    public function getReportById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM " . $this->table . " WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function updateReportStatus($id, $status)
    {
        $stmt = $this->db->prepare("UPDATE " . $this->table . " SET status = :status WHERE id = :id");
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function updateReportNotes($id, $notes)
    {
        $stmt = $this->db->prepare("UPDATE " . $this->table . " SET bosun_notes = :notes WHERE id = :id");
        $stmt->bindParam(':notes', $notes);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function updateReport($id, $boatName, $faultDescription, $status, $bosunNotes)
    {
        $stmt = $this->db->prepare("UPDATE " . $this->table . " SET boat_name = :boat_name, fault_description = :fault_description, status = :status, bosun_notes = :bosun_notes WHERE id = :id");
        $stmt->bindParam(':boat_name', $boatName);
        $stmt->bindParam(':fault_description', $faultDescription);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':bosun_notes', $bosunNotes);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}