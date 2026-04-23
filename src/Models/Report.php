<?php

namespace src\Models;

use PDO;

class Report
{
    private $db;
    private $table = 'reports';
    private $hasBoatCheckinsTable = null;

    public function __construct()
    {
        $this->db = $GLOBALS['pdo'];
    }

    public function deleteReport($id)
    {
        try {
            $this->db->beginTransaction();

            if ($this->hasBoatCheckinsTable()) {
                $unlinkStmt = $this->db->prepare("UPDATE boat_checkins SET fault_report_id = NULL WHERE fault_report_id = :id");
                $unlinkStmt->bindValue(':id', $id, PDO::PARAM_INT);
                $unlinkStmt->execute();
            }

            $stmt = $this->db->prepare("DELETE FROM " . $this->table . " WHERE id = :id");
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $deleted = $stmt->execute();

            $this->db->commit();
            return $deleted;
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log('Report delete failed for report #' . (int) $id . ': ' . $e->getMessage());
            return false;
        }
    }

    private function hasBoatCheckinsTable(): bool
    {
        if ($this->hasBoatCheckinsTable !== null) {
            return $this->hasBoatCheckinsTable;
        }

        try {
            $stmt = $this->db->query("SHOW TABLES LIKE 'boat_checkins'");
            $this->hasBoatCheckinsTable = (bool) ($stmt && $stmt->fetch());
        } catch (\Throwable $e) {
            $this->hasBoatCheckinsTable = false;
        }

        return $this->hasBoatCheckinsTable;
    }

    public function create($boatId, $faultDescription, $reporterName = '', $reporterEmail = ''): bool
    {
        return $this->createAndReturnId($boatId, $faultDescription, $reporterName, $reporterEmail) !== false;
    }

    public function createAndReturnId($boatId, $faultDescription, $reporterName = '', $reporterEmail = ''): int|false
    {
        $stmt = $this->db->prepare("INSERT INTO " . $this->table . " (boat_id, fault_description, reporter_name, reporter_email, reported_at) VALUES (:boat_id, :fault_description, :reporter_name, :reporter_email, NOW())");
        $stmt->bindValue(':boat_id', $boatId);
        $stmt->bindValue(':fault_description', $faultDescription);
        $stmt->bindValue(':reporter_name', $reporterName);
        $stmt->bindValue(':reporter_email', $reporterEmail);

        if (!$stmt->execute()) {
            return false;
        }

        return (int) $this->db->lastInsertId();
    }

    public function createFromCheckin($boatId, $faultDescription, $reporterName = '', $reporterEmail = '', $boatCheckinId = null): int|false
    {
        $columns = ['boat_id', 'fault_description', 'reporter_name', 'reporter_email', 'reported_at'];
        $placeholders = [':boat_id', ':fault_description', ':reporter_name', ':reporter_email', 'NOW()'];
        $params = [
            ':boat_id' => (int) $boatId,
            ':fault_description' => $faultDescription,
            ':reporter_name' => $reporterName,
            ':reporter_email' => $reporterEmail,
        ];

        try {
            $sourceColumn = $this->db->query("SHOW COLUMNS FROM " . $this->table . " LIKE 'source'");
            if ($sourceColumn && $sourceColumn->rowCount() > 0) {
                $columns[] = 'source';
                $placeholders[] = ':source';
                $params[':source'] = 'boat_checkin';
            }

            if ($boatCheckinId !== null) {
                $checkinColumn = $this->db->query("SHOW COLUMNS FROM " . $this->table . " LIKE 'boat_checkin_id'");
                if ($checkinColumn && $checkinColumn->rowCount() > 0) {
                    $columns[] = 'boat_checkin_id';
                    $placeholders[] = ':boat_checkin_id';
                    $params[':boat_checkin_id'] = (int) $boatCheckinId;
                }
            }
        } catch (\Throwable $e) {
            // Keep backward compatibility with schemas that don't yet have provenance columns.
        }

        $query = "INSERT INTO " . $this->table . " (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
        $stmt = $this->db->prepare($query);

        foreach ($params as $key => $value) {
            if ($key === ':boat_id' || $key === ':boat_checkin_id') {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($key, $value);
            }
        }

        if (!$stmt->execute()) {
            return false;
        }

        return (int) $this->db->lastInsertId();
    }

    public function findRecentDuplicateReportId($boatId, $faultDescription, $reporterName = '', $reporterEmail = '', $windowSeconds = 120): ?int
    {
        $reportedAfter = date('Y-m-d H:i:s', time() - max(1, (int) $windowSeconds));

        $stmt = $this->db->prepare(
            "SELECT id FROM " . $this->table . "
            WHERE boat_id = :boat_id
              AND fault_description = :fault_description
              AND reporter_name = :reporter_name
              AND reporter_email = :reporter_email
              AND reported_at >= :reported_after
            ORDER BY id DESC
            LIMIT 1"
        );
        $stmt->bindValue(':boat_id', $boatId, PDO::PARAM_INT);
        $stmt->bindValue(':fault_description', $faultDescription);
        $stmt->bindValue(':reporter_name', $reporterName);
        $stmt->bindValue(':reporter_email', $reporterEmail);
        $stmt->bindValue(':reported_after', $reportedAfter);
        $stmt->execute();

        $duplicateId = $stmt->fetchColumn();
        return ($duplicateId !== false) ? (int) $duplicateId : null;
    }

    public function getAllReports($filter = 'all', $sortBy = 'created_at', $sortOrder = 'DESC', $boatId = null, $status = null, $page = 1, $perPage = 50)
    {
        try {
            $query = "SELECT r.*, b.boat_name, b.boat_type FROM " . $this->table . " r LEFT JOIN boats b ON r.boat_id = b.id";
            $params = [];

            $whereConditions = [];

            // Apply active filter only if no specific status is selected
            if ($filter === 'active' && $status === null) {
                $whereConditions[] = "r.status IN ('New', 'In progress', 'Waiting parts')";
            }

            // Add specific status filter if specified
            if ($status !== null && $status !== 'All') {
                $whereConditions[] = "r.status = :status";
                $params[':status'] = $status;
            }

            if ($boatId !== null) {
                $whereConditions[] = "r.boat_id = :boat_id";
                $params[':boat_id'] = $boatId;
            }

            if (!empty($whereConditions)) {
                $query .= " WHERE " . implode(" AND ", $whereConditions);
            }

            // Validate sort column to prevent SQL injection
            $allowedSortColumns = ['r.id', 'b.boat_name', 'r.status', 'r.reported_at'];
            if (!in_array($sortBy, $allowedSortColumns)) {
                $sortBy = 'r.reported_at';
            }

            // Validate sort order
            $sortOrder = strtoupper($sortOrder);
            if ($sortOrder !== 'ASC' && $sortOrder !== 'DESC') {
                $sortOrder = 'DESC';
            }

            $query .= " ORDER BY {$sortBy} {$sortOrder}";

            // Add pagination
            $offset = ($page - 1) * $perPage;
            $query .= " LIMIT :limit OFFSET :offset";
            $params[':limit'] = (int)$perPage;
            $params[':offset'] = (int)$offset;

            $stmt = $this->db->prepare($query);
            
            // Bind integer parameters separately to avoid PDO issues
            foreach ($params as $key => $value) {
                if ($key === ':limit' || $key === ':offset') {
                    $stmt->bindValue($key, $value, PDO::PARAM_INT);
                } else {
                    $stmt->bindValue($key, $value);
                }
            }
            
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }
        catch (\Exception $e) {
            // Log error and return empty array or default sorting
            error_log("Error in getAllReports: " . $e->getMessage());
            // Fallback to basic query without sorting
            $fallbackQuery = "SELECT * FROM " . $this->table;
            if ($filter === 'active') {
                $fallbackQuery .= " WHERE status IN ('New', 'In progress', 'Waiting parts')";
            }
            $fallbackQuery .= " ORDER BY reported_at DESC";
            
            $stmt = $this->db->prepare($fallbackQuery);
            $stmt->execute();
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }
    }

    public function getReportById($id)
    {
        $stmt = $this->db->prepare("SELECT r.*, b.boat_name FROM " . $this->table . " r LEFT JOIN boats b ON r.boat_id = b.id WHERE r.id = :id");
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function getReportsByIds($ids)
    {
        if (empty($ids)) {
            return [];
        }
        
        // Sanitize IDs - ensure they are all integers
        $sanitizedIds = [];
        foreach ($ids as $id) {
            $sanitizedId = (int)$id;
            if ($sanitizedId > 0) {
                $sanitizedIds[] = $sanitizedId;
            }
        }
        
        if (empty($sanitizedIds)) {
            return [];
        }
        
        // Build query with proper placeholders
        $placeholders = implode(',', array_fill(0, count($sanitizedIds), '?'));
        $query = "SELECT r.*, b.boat_name, b.boat_type FROM " . $this->table . " r LEFT JOIN boats b ON r.boat_id = b.id WHERE r.id IN ({$placeholders}) ORDER BY b.boat_name ASC, r.reported_at DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute($sanitizedIds);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function updateReportStatus($id, $status)
    {
        $stmt = $this->db->prepare("UPDATE " . $this->table . " SET status = :status WHERE id = :id");
        $stmt->bindValue(':status', $status);
        $stmt->bindValue(':id', $id);
        return $stmt->execute();
    }

    public function updateReportNotes($id, $notes)
    {
        $stmt = $this->db->prepare("UPDATE " . $this->table . " SET bosun_notes = :notes WHERE id = :id");
        $stmt->bindValue(':notes', $notes);
        $stmt->bindValue(':id', $id);
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

    public function getReportsCount($filter = 'all', $boatId = null, $status = null)
    {
        try {
            $query = "SELECT COUNT(*) as total FROM " . $this->table . " r";
            $params = [];
            $whereConditions = [];

            // Apply active filter only if no specific status is selected
            if ($filter === 'active' && $status === null) {
                $whereConditions[] = "r.status IN ('New', 'In progress', 'Waiting parts')";
            }

            // Add specific status filter if specified
            if ($status !== null && $status !== 'All') {
                $whereConditions[] = "r.status = :status";
                $params[':status'] = $status;
            }

            if ($boatId !== null) {
                $whereConditions[] = "r.boat_id = :boat_id";
                $params[':boat_id'] = $boatId;
            }

            if (!empty($whereConditions)) {
                $query .= " WHERE " . implode(" AND ", $whereConditions);
            }

            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            return (int)$result['total'];
        } catch (\Exception $e) {
            error_log("Error in getReportsCount: " . $e->getMessage());
            return 0;
        }
    }
}