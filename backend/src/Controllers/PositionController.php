<?php
require_once __DIR__ . '/../models/Position.php';

class PositionController {
    private $positionModel;
    private $db;

    public function __construct($db) {
        $this->db = $db;
        $this->positionModel = new Position($db);
    }

    public function getAll() {
        try {
            $positions = $this->positionModel->getAll();
            return [
                'success' => true,
                'data' => $positions
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function getById($id) {
        try {
            $position = $this->positionModel->getById($id);
            if (!$position) {
                return [
                    'success' => false,
                    'message' => 'Chức vụ không tồn tại'
                ];
            }
            return [
                'success' => true,
                'data' => $position
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function getByDepartment($departmentId) {
        try {
            $positions = $this->positionModel->getByDepartment($departmentId);
            return [
                'success' => true,
                'data' => $positions
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function create($data) {
        try {
            // Validate input data
            $errors = $this->positionModel->validate($data);
            if (!empty($errors)) {
                return [
                    'success' => false,
                    'message' => 'Dữ liệu không hợp lệ',
                    'errors' => $errors
                ];
            }

            // Create position
            $positionId = $this->positionModel->create($data);
            $position = $this->positionModel->getById($positionId);

            return [
                'success' => true,
                'message' => 'Tạo chức vụ thành công',
                'data' => $position
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function update($id, $data) {
        try {
            // Check if position exists
            $existingPosition = $this->positionModel->getById($id);
            if (!$existingPosition) {
                return [
                    'success' => false,
                    'message' => 'Chức vụ không tồn tại'
                ];
            }

            // Validate input data
            $errors = $this->positionModel->validate($data);
            if (!empty($errors)) {
                return [
                    'success' => false,
                    'message' => 'Dữ liệu không hợp lệ',
                    'errors' => $errors
                ];
            }

            // Update position
            $this->positionModel->update($id, $data);
            $position = $this->positionModel->getById($id);

            return [
                'success' => true,
                'message' => 'Cập nhật chức vụ thành công',
                'data' => $position
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function delete($id) {
        try {
            // Check if position exists
            $existingPosition = $this->positionModel->getById($id);
            if (!$existingPosition) {
                return [
                    'success' => false,
                    'message' => 'Chức vụ không tồn tại'
                ];
            }

            // Delete position
            $this->positionModel->delete($id);

            return [
                'success' => true,
                'message' => 'Xóa chức vụ thành công'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
} 