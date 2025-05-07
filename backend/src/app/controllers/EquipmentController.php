<?php

namespace App\Controllers;

use App\Models\Equipment;
use App\Models\User;
use App\Models\Department;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;

class EquipmentController extends Controller
{
    private $equipmentModel;
    private $userModel;
    private $departmentModel;

    public function __construct()
    {
        $this->equipmentModel = new Equipment();
        $this->userModel = new User();
        $this->departmentModel = new Department();
    }

    public function index(Request $request, Response $response)
    {
        $equipment = $this->equipmentModel->all();
        return $response->json($equipment);
    }

    public function show(Request $request, Response $response, $id)
    {
        $equipment = $this->equipmentModel->find($id);
        if (!$equipment) {
            return $response->status(404)->json(['message' => 'Equipment not found']);
        }
        return $response->json($equipment);
    }

    public function store(Request $request, Response $response)
    {
        $data = $request->getBody();
        
        // Validate required fields
        if (empty($data['name']) || empty($data['serial_number']) || empty($data['purchase_date'])) {
            return $response->status(400)->json(['message' => 'Missing required fields']);
        }

        // Create new equipment
        $equipment = $this->equipmentModel->create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'serial_number' => $data['serial_number'],
            'purchase_date' => $data['purchase_date'],
            'purchase_cost' => $data['purchase_cost'] ?? 0,
            'warranty_period' => $data['warranty_period'] ?? null,
            'status' => 'available',
            'location' => $data['location'] ?? null
        ]);

        return $response->status(201)->json($equipment);
    }

    public function update(Request $request, Response $response, $id)
    {
        $equipment = $this->equipmentModel->find($id);
        if (!$equipment) {
            return $response->status(404)->json(['message' => 'Equipment not found']);
        }

        $data = $request->getBody();
        $equipment->update($data);

        return $response->json($equipment);
    }

    public function delete(Request $request, Response $response, $id)
    {
        $equipment = $this->equipmentModel->find($id);
        if (!$equipment) {
            return $response->status(404)->json(['message' => 'Equipment not found']);
        }

        $equipment->delete();
        return $response->json(['message' => 'Equipment deleted successfully']);
    }

    public function getAvailableEquipment(Request $request, Response $response)
    {
        $equipment = $this->equipmentModel->getAvailableEquipment();
        return $response->json($equipment);
    }

    public function getAssignedEquipment(Request $request, Response $response)
    {
        $equipment = $this->equipmentModel->getAssignedEquipment();
        return $response->json($equipment);
    }

    public function getDepartmentEquipment(Request $request, Response $response, $departmentId)
    {
        $equipment = $this->equipmentModel->getDepartmentEquipment($departmentId);
        return $response->json($equipment);
    }

    public function getUserEquipment(Request $request, Response $response, $userId)
    {
        $equipment = $this->equipmentModel->getUserEquipment($userId);
        return $response->json($equipment);
    }

    public function assignEquipment(Request $request, Response $response, $equipmentId)
    {
        $data = $request->getBody();
        if (empty($data['user_id']) || empty($data['department_id'])) {
            return $response->status(400)->json(['message' => 'Missing required fields']);
        }

        $result = $this->equipmentModel->assignEquipment($equipmentId, $data['user_id'], $data['department_id']);
        
        if (!$result['success']) {
            return $response->status(400)->json(['message' => $result['message']]);
        }

        return $response->json(['message' => $result['message']]);
    }

    public function returnEquipment(Request $request, Response $response, $equipmentId)
    {
        $result = $this->equipmentModel->returnEquipment($equipmentId);
        
        if (!$result['success']) {
            return $response->status(400)->json(['message' => $result['message']]);
        }

        return $response->json(['message' => $result['message']]);
    }

    public function updateStatus(Request $request, Response $response, $equipmentId)
    {
        $data = $request->getBody();
        if (empty($data['status'])) {
            return $response->status(400)->json(['message' => 'Status is required']);
        }

        $result = $this->equipmentModel->updateStatus($equipmentId, $data['status']);
        
        if (!$result['success']) {
            return $response->status(400)->json(['message' => $result['message']]);
        }

        return $response->json(['message' => $result['message']]);
    }

    public function getStatistics(Request $request, Response $response)
    {
        $statistics = $this->equipmentModel->getEquipmentStatistics();
        return $response->json($statistics);
    }
} 