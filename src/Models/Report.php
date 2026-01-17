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

    public function create($boatId, $faultDescription, $reporterName = '', $reporterEmail = '')
    {
        $stmt = $this->db->prepare("INSERT INTO " . $this->table . " (boat_id, fault_description, reporter_name, reporter_email, created_at) VALUES (:boat_id, :fault_description, :reporter_name, :reporter_email, NOW())");
        $stmt->bindParam(':boat_id', $boatId);
        $stmt->bindParam(':fault_description', $faultDescription);
        $stmt->bindParam(':reporter_name', $reporterName);
        $stmt->bindParam(':reporter_email', $reporterEmail);
        return $stmt->execute();
    }

    public function getAllReports($filter = 'all', $sortBy = 'created_at', $sortOrder = 'DESC', $boatId = null)
    {
        try {
            $query = "SELECT r.*, b.boat_name FROM " . $this->table . " r LEFT JOIN boats b ON r.boat_id = b.id";
            $params = [];

            if ($filter === 'active') {
                $query .= " WHERE r.status IN ('New', 'In progress', 'Waiting parts')";
            }

            if ($boatId !== null) {
                $query .= ($filter === 'active' ? " AND" : " WHERE") . " r.boat_id = :boat_id";
                $params[':boat_id'] = $boatId;
            }

            // Validate sort column to prevent SQL injection
            $allowedSortColumns = ['r.id', 'b.boat_name', 'r.status', 'r.created_at'];
            if (!in_array($sortBy, $allowedSortColumns)) {
                $sortBy = 'r.created_at';
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
        $stmt = $this->db->prepare("SELECT r.*, b.boat_name FROM " . $this->table . " r LEFT JOIN boats b ON r.boat_id = b.id WHERE r.id = :id");
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

    public function updateReport($id, $boatId, $faultDescription, $status, $bosunNotes, $bosunAssessment = null, $partRequired = null, $partStatus = null, $completionDate = null)
    {
        $query = "UPDATE " . $this->table . " SET boat_id = :boat_id, fault_description = :fault_description, status = :status, bosun_notes = :bosun_notes";
        
        $params = [
            ':boat_id' => $boatId,
            ':fault_description' => $faultDescription,
            ':status' => $status,
            ':bosun_notes' => $bosunNotes,
            ':id' => $id
        ];
        
        // Check if new columns exist and add them to the query
        try {
            $columnsCheck = $this->db->query("SHOW COLUMNS FROM " . $this->table . " LIKE 'bosun_assessment'");
            if ($columnsCheck->rowCount() > 0) {
                $query .= ", bosun_assessment = :bosun_assessment, part_required = :part_required, part_status = :part_status";
                $params[':bosun_assessment'] = $bosunAssessment;
                $params[':part_required'] = $partRequired;
                $params[':part_status'] = $partStatus;
            }
            
            // Check for completion_date column separately
            $completionCheck = $this->db->query("SHOW COLUMNS FROM " . $this->table . " LIKE 'completion_date'");
            if ($completionCheck->rowCount() > 0) {
                $query .= ", completion_date = :completion_date";
                $params[':completion_date'] = $completionDate;
            }
        } catch (\PDOException $e) {
            // Columns don't exist yet, continue with basic update
        }
        
        $query .= " WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute($params);
    }
}