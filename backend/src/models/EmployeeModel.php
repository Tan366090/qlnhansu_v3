<?php

namespace App\Models;

use App\Core\Database;
use PDO;
use PDOException;
use Exception;

class EmployeeModel
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAllActiveEmployees()
    {
        try {
            $query = "SELECT 
                    e.id,
                    e.employee_code,
                    up.full_name
                FROM employees e
                JOIN user_profiles up ON e.user_id = up.user_id
                WHERE e.status = 'active'
                ORDER BY up.full_name ASC";

            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Error getting active employees: " . $e->getMessage());
        }
    }
} 