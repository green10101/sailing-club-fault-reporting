<?php

namespace src\Models;

use PDO;

class BoatCheckin
{
    private $db;
    private $table = 'boat_checkins';
    private $hasReportsBoatCheckinColumn = null;

    public function __construct()
    {
        $this->db = $GLOBALS['pdo'];
    }

    public function create(array $payload): int|false
    {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO {$this->table}
                (boat_id, user_name, user_email, checked_in_at, put_away_ok, safe_for_next_user, has_faults_to_rectify, damage_during_checkout, checkin_notes)
                VALUES
                (:boat_id, :user_name, :user_email, NOW(), :put_away_ok, :safe_for_next_user, :has_faults_to_rectify, :damage_during_checkout, :checkin_notes)"
            );

            $stmt->bindValue(':boat_id', (int) $payload['boat_id'], PDO::PARAM_INT);
            $stmt->bindValue(':user_name', (string) $payload['user_name']);
            $stmt->bindValue(':user_email', (string) $payload['user_email']);
            $stmt->bindValue(':put_away_ok', $payload['put_away_ok'] ? 1 : 0, PDO::PARAM_INT);
            $stmt->bindValue(':safe_for_next_user', $payload['safe_for_next_user'] ? 1 : 0, PDO::PARAM_INT);
            $stmt->bindValue(':has_faults_to_rectify', $payload['has_faults_to_rectify'] ? 1 : 0, PDO::PARAM_INT);

            if ($payload['damage_during_checkout'] === null) {
                $stmt->bindValue(':damage_during_checkout', null, PDO::PARAM_NULL);
            } else {
                $stmt->bindValue(':damage_during_checkout', $payload['damage_during_checkout'] ? 1 : 0, PDO::PARAM_INT);
            }

            $notes = trim((string) ($payload['checkin_notes'] ?? ''));
            if ($notes === '') {
                $stmt->bindValue(':checkin_notes', null, PDO::PARAM_NULL);
            } else {
                $stmt->bindValue(':checkin_notes', $notes);
            }

            if (!$stmt->execute()) {
                return false;
            }

            return (int) $this->db->lastInsertId();
        } catch (\Throwable $e) {
            error_log('Boat check-in create failed: ' . $e->getMessage());
            return false;
        }
    }

    public function updateFaultReportId(int $checkinId, int $reportId): bool
    {
        try {
            $stmt = $this->db->prepare("UPDATE {$this->table} SET fault_report_id = :fault_report_id WHERE id = :id");
            $stmt->bindValue(':fault_report_id', $reportId, PDO::PARAM_INT);
            $stmt->bindValue(':id', $checkinId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (\Throwable $e) {
            error_log('Boat check-in fault linkage failed: ' . $e->getMessage());
            return false;
        }
    }

    public function getCheckins($boatId = null, $faultFilter = 'all', $page = 1, $perPage = 50): array
    {
        try {
            $query = "SELECT c.*, b.boat_name, r.status AS fault_report_status
                      FROM {$this->table} c
                      INNER JOIN boats b ON b.id = c.boat_id
                      LEFT JOIN reports r ON r.id = c.fault_report_id";

            $where = [];
            $params = [];

            if ($boatId !== null) {
                $where[] = 'c.boat_id = :boat_id';
                $params[':boat_id'] = (int) $boatId;
            }

            if ($faultFilter === 'with_fault') {
                $where[] = 'c.fault_report_id IS NOT NULL';
            } elseif ($faultFilter === 'without_fault') {
                $where[] = 'c.fault_report_id IS NULL';
            }

            if (!empty($where)) {
                $query .= ' WHERE ' . implode(' AND ', $where);
            }

            $query .= ' ORDER BY c.checked_in_at DESC, c.id DESC LIMIT :limit OFFSET :offset';

            $offset = ($page - 1) * $perPage;
            $stmt = $this->db->prepare($query);

            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            }

            $stmt->bindValue(':limit', (int) $perPage, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            error_log('Boat check-in list failed: ' . $e->getMessage());
            return [];
        }
    }

    public function getCheckinsCount($boatId = null, $faultFilter = 'all'): int
    {
        try {
            $query = "SELECT COUNT(*) AS total FROM {$this->table} c";
            $where = [];
            $params = [];

            if ($boatId !== null) {
                $where[] = 'c.boat_id = :boat_id';
                $params[':boat_id'] = (int) $boatId;
            }

            if ($faultFilter === 'with_fault') {
                $where[] = 'c.fault_report_id IS NOT NULL';
            } elseif ($faultFilter === 'without_fault') {
                $where[] = 'c.fault_report_id IS NULL';
            }

            if (!empty($where)) {
                $query .= ' WHERE ' . implode(' AND ', $where);
            }

            $stmt = $this->db->prepare($query);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            }
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            return (int) ($row['total'] ?? 0);
        } catch (\Throwable $e) {
            error_log('Boat check-in count failed: ' . $e->getMessage());
            return 0;
        }
    }

    public function deleteCheckin(int $checkinId): bool
    {
        try {
            $this->db->beginTransaction();

            if ($this->hasReportsBoatCheckinColumn()) {
                $unlinkReportsStmt = $this->db->prepare('UPDATE reports SET boat_checkin_id = NULL WHERE boat_checkin_id = :checkin_id');
                $unlinkReportsStmt->bindValue(':checkin_id', $checkinId, PDO::PARAM_INT);
                $unlinkReportsStmt->execute();
            }

            $deleteStmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = :id");
            $deleteStmt->bindValue(':id', $checkinId, PDO::PARAM_INT);
            $deleted = $deleteStmt->execute();

            $this->db->commit();
            return $deleted;
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log('Boat check-in delete failed for #' . $checkinId . ': ' . $e->getMessage());
            return false;
        }
    }

    private function hasReportsBoatCheckinColumn(): bool
    {
        if ($this->hasReportsBoatCheckinColumn !== null) {
            return $this->hasReportsBoatCheckinColumn;
        }

        try {
            $stmt = $this->db->query("SHOW COLUMNS FROM reports LIKE 'boat_checkin_id'");
            $this->hasReportsBoatCheckinColumn = (bool) ($stmt && $stmt->fetch());
        } catch (\Throwable $e) {
            $this->hasReportsBoatCheckinColumn = false;
        }

        return $this->hasReportsBoatCheckinColumn;
    }
}
