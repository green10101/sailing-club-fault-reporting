<?php

namespace src\Models;

use PDO;

class Boat
{
    private $db;
    private $table = 'boats';

    public function __construct()
    {
        $this->db = $GLOBALS['pdo'];
    }

    public function getAllBoats()
    {
        return $this->getBoatsFilteredSorted();
    }

    public function getBoatsFilteredSorted($filter = 'current', $sortBy = 'boat_name', $sortOrder = 'ASC')
    {
        $query = "SELECT * FROM " . $this->table;
        $params = [];

        if ($filter === 'current') {
            $query .= " WHERE status != 'Retired'";
        } elseif ($filter === 'ok_or_minor') {
            $query .= " WHERE status IN ('OK','Minor Faults')";
        } elseif ($filter === 'not_operational') {
            $query .= " WHERE status = 'Out of Operation'";
        }
        // 'all' filter shows everything including retired boats

        $allowedSortColumns = ['boat_name','boat_type','status','created_at','updated_at'];
        if (!in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'boat_name';
        }

        $sortOrder = strtoupper($sortOrder);
        if (!in_array($sortOrder, ['ASC','DESC'])) {
            $sortOrder = 'ASC';
        }

        $query .= " ORDER BY $sortBy $sortOrder";

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createBoat($boatName, $boatType, $serialNumber, $status)
    {
        $stmt = $this->db->prepare("INSERT INTO " . $this->table . " (boat_name, boat_type, serial_number, status, created_at) VALUES (:boat_name, :boat_type, :serial_number, :status, NOW())");
        $stmt->bindValue(':boat_name', $boatName);
        $stmt->bindValue(':boat_type', $boatType);
        // Treat empty serial numbers as NULL to avoid unique '' collisions
        if ($serialNumber === '' || $serialNumber === null) {
            $stmt->bindValue(':serial_number', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindValue(':serial_number', $serialNumber, PDO::PARAM_STR);
        }
        $stmt->bindValue(':status', $status);
        return $stmt->execute();
    }

    public function getBoatById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM " . $this->table . " WHERE id = :id");
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateStatus($id, $status)
    {
        $stmt = $this->db->prepare("UPDATE " . $this->table . " SET status = :status, updated_at = NOW() WHERE id = :id");
        $stmt->bindValue(':status', $status);
        $stmt->bindValue(':id', $id);
        return $stmt->execute();
    }

    public function updateDetails($id, $boatName, $boatType, $serialNumber, $status)
    {
        $stmt = $this->db->prepare("UPDATE " . $this->table . " SET boat_name = :boat_name, boat_type = :boat_type, serial_number = :serial_number, status = :status, updated_at = NOW() WHERE id = :id");
        $stmt->bindValue(':boat_name', $boatName);
        $stmt->bindValue(':boat_type', $boatType);
        // Treat empty serial numbers as NULL
        if ($serialNumber === '' || $serialNumber === null) {
            $stmt->bindValue(':serial_number', null, PDO::PARAM_NULL);
        } else {
            $stmt->bindValue(':serial_number', $serialNumber, PDO::PARAM_STR);
        }
        $stmt->bindValue(':status', $status);
        $stmt->bindValue(':id', $id);
        return $stmt->execute();
    }

    public function getActiveFaultCount($boatId)
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM reports WHERE boat_id = :boat_id AND status != 'Complete'");
        $stmt->bindValue(':boat_id', $boatId);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    }
}