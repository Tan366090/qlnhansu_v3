<?php
namespace App\Controllers;

use App\Services\StorageService;

class DataController {
    private $storageService;

    public function __construct() {
        $this->storageService = StorageService::getInstance();
    }

    public function getData($request) {
        try {
            $table = $request['table'] ?? null;
            $conditions = $request['conditions'] ?? [];
            $forceRefresh = $request['force_refresh'] ?? false;

            if (!$table) {
                throw new \Exception('Table name is required');
            }

            $data = $this->storageService->getData($table, $conditions, $forceRefresh);
            
            return [
                'success' => true,
                'data' => $data
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function getRelatedData($request) {
        try {
            $table = $request['table'] ?? null;
            $relatedTable = $request['related_table'] ?? null;
            $foreignKey = $request['foreign_key'] ?? null;
            $id = $request['id'] ?? null;

            if (!$table || !$relatedTable || !$foreignKey || !$id) {
                throw new \Exception('Missing required parameters');
            }

            $data = $this->storageService->getRelatedData($table, $relatedTable, $foreignKey, $id);
            
            return [
                'success' => true,
                'data' => $data
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function insertData($request) {
        try {
            $table = $request['table'] ?? null;
            $data = $request['data'] ?? null;

            if (!$table || !$data) {
                throw new \Exception('Missing required parameters');
            }

            $id = $this->storageService->insertData($table, $data);
            
            return [
                'success' => true,
                'id' => $id,
                'message' => 'Data inserted successfully'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function updateData($request) {
        try {
            $table = $request['table'] ?? null;
            $id = $request['id'] ?? null;
            $data = $request['data'] ?? null;

            if (!$table || !$id || !$data) {
                throw new \Exception('Missing required parameters');
            }

            $this->storageService->updateData($table, $id, $data);
            
            return [
                'success' => true,
                'message' => 'Data updated successfully'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function deleteData($request) {
        try {
            $table = $request['table'] ?? null;
            $id = $request['id'] ?? null;

            if (!$table || !$id) {
                throw new \Exception('Missing required parameters');
            }

            $this->storageService->deleteData($table, $id);
            
            return [
                'success' => true,
                'message' => 'Data deleted successfully'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function clearCache($request) {
        try {
            $table = $request['table'] ?? null;
            $this->storageService->clearCache($table);
            
            return [
                'success' => true,
                'message' => 'Cache cleared successfully'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
?> 