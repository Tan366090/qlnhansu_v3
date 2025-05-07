<?php

use App\Models\Employee;
use App\Models\Department;
use App\Models\Position;
use App\Models\Project;
use App\Models\Task;
use App\Models\Performance;
use App\Models\Leave;
use App\Models\Attendance;
use App\Models\Certificate;
use App\Models\Skill;
use App\Models\Experience;
use App\Models\Document;
use App\Models\Training;
use App\Models\Notification;
use App\Models\User;

class ApiController {
    private $db;
    private $request;
    private $response;

    public function __construct() {
        $this->db = new Database();
        $this->request = $_REQUEST;
        $this->response = [
            'success' => false,
            'message' => '',
            'data' => [],
            'total' => 0
        ];
    }

    public function handleRequest() {
        $action = $this->request['action'] ?? '';
        
        switch ($action) {
            case 'get_data':
                $this->getData();
                break;
            case 'create':
                $this->create();
                break;
            case 'update':
                $this->update();
                break;
            case 'delete':
                $this->delete();
                break;
            default:
                $this->response['message'] = 'Invalid action';
                break;
        }

        $this->sendResponse();
    }

    private function getData() {
        try {
            $page = (int)($this->request['page'] ?? 1);
            $pageSize = (int)($this->request['pageSize'] ?? 10);
            $search = $this->request['search'] ?? '';
            $filters = $this->request['filters'] ?? [];

            // Build query
            $query = "SELECT * FROM your_table WHERE 1=1";
            $params = [];

            // Add search condition
            if (!empty($search)) {
                $query .= " AND (name LIKE ? OR description LIKE ?)";
                $params[] = "%$search%";
                $params[] = "%$search%";
            }

            // Add filters
            foreach ($filters as $key => $value) {
                if (!empty($value)) {
                    $query .= " AND $key = ?";
                    $params[] = $value;
                }
            }

            // Get total count
            $countQuery = "SELECT COUNT(*) as total FROM ($query) as count_table";
            $total = $this->db->query($countQuery, $params)->fetch()['total'];

            // Add pagination
            $offset = ($page - 1) * $pageSize;
            $query .= " LIMIT ? OFFSET ?";
            $params[] = $pageSize;
            $params[] = $offset;

            // Execute query
            $data = $this->db->query($query, $params)->fetchAll();

            $this->response['success'] = true;
            $this->response['data'] = $data;
            $this->response['total'] = $total;
        } catch (Exception $e) {
            $this->response['message'] = 'Error getting data: ' . $e->getMessage();
        }
    }

    private function create() {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validate data
            if (!$this->validateData($data)) {
                throw new Exception('Invalid data');
            }

            // Insert data
            $columns = implode(', ', array_keys($data));
            $values = implode(', ', array_fill(0, count($data), '?'));
            $query = "INSERT INTO your_table ($columns) VALUES ($values)";
            
            $this->db->query($query, array_values($data));

            $this->response['success'] = true;
            $this->response['message'] = 'Data created successfully';
        } catch (Exception $e) {
            $this->response['message'] = 'Error creating data: ' . $e->getMessage();
        }
    }

    private function update() {
        try {
            $id = $this->request['id'] ?? null;
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!$id) {
                throw new Exception('ID is required');
            }

            // Validate data
            if (!$this->validateData($data)) {
                throw new Exception('Invalid data');
            }

            // Build update query
            $set = [];
            $params = [];
            foreach ($data as $key => $value) {
                $set[] = "$key = ?";
                $params[] = $value;
            }
            $params[] = $id;

            $query = "UPDATE your_table SET " . implode(', ', $set) . " WHERE id = ?";
            
            $this->db->query($query, $params);

            $this->response['success'] = true;
            $this->response['message'] = 'Data updated successfully';
        } catch (Exception $e) {
            $this->response['message'] = 'Error updating data: ' . $e->getMessage();
        }
    }

    private function delete() {
        try {
            $id = $this->request['id'] ?? null;
            
            if (!$id) {
                throw new Exception('ID is required');
            }

            $query = "DELETE FROM your_table WHERE id = ?";
            $this->db->query($query, [$id]);

            $this->response['success'] = true;
            $this->response['message'] = 'Data deleted successfully';
        } catch (Exception $e) {
            $this->response['message'] = 'Error deleting data: ' . $e->getMessage();
        }
    }

    private function validateData($data) {
        // Add your validation logic here
        return true;
    }

    private function sendResponse() {
        header('Content-Type: application/json');
        echo json_encode($this->response);
    }
} 