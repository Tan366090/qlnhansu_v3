<?php
require_once __DIR__ . '/../config/database.php';

class ContractTypeController {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getContractTypes() {
        try {
            $query = "SELECT id, name, description FROM contract_types WHERE status = 'active' ORDER BY name";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
} 