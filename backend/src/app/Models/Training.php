<?php
namespace App\Models;

use PDO;
use PDOException;
use App\Core\Model;

class Training extends Model {
    protected $table = 'trainings';
    protected $primaryKey = 'training_id';

    protected $fillable = [
        'title',
        'description',
        'start_date',
        'end_date',
        'location',
        'trainer',
        'max_participants',
        'status',
        'created_by',
        'created_at',
        'updated_at'
    ];

    public function createTraining($employeeId, $trainingName, $trainingType, $startDate, $endDate, $provider, $location, $cost = 0, $status = 'planned', $notes = null) {
        try {
            $data = [
                'employee_id' => $employeeId,
                'training_name' => $trainingName,
                'training_type' => $trainingType,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'provider' => $provider,
                'location' => $location,
                'cost' => $cost,
                'status' => $status,
                'notes' => $notes,
                'created_at' => date('Y-m-d H:i:s')
            ];

            $trainingId = $this->create($data);
            return [
                'success' => true,
                'training_id' => $trainingId
            ];
        } catch (PDOException $e) {
            error_log("Create Training Error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Lỗi hệ thống'
            ];
        }
    }

    public function updateTraining($trainingId, $trainingName, $trainingType, $startDate, $endDate, $provider, $location, $cost = null, $notes = null) {
        try {
            $data = [
                'training_name' => $trainingName,
                'training_type' => $trainingType,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'provider' => $provider,
                'location' => $location,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            if ($cost !== null) {
                $data['cost'] = $cost;
            }

            if ($notes !== null) {
                $data['notes'] = $notes;
            }

            return $this->update($trainingId, $data);
        } catch (PDOException $e) {
            error_log("Update Training Error: " . $e->getMessage());
            return false;
        }
    }

    public function updateTrainingStatus($trainingId, $status, $notes = null) {
        try {
            $data = [
                'status' => $status,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            if ($notes !== null) {
                $data['notes'] = $notes;
            }

            return $this->update($trainingId, $data);
        } catch (PDOException $e) {
            error_log("Update Training Status Error: " . $e->getMessage());
            return false;
        }
    }

    public function getTrainingDetails($trainingId) {
        try {
            $query = "SELECT t.*, e.full_name as employee_name, e.employee_code, p.position_name, d.department_name 
                     FROM {$this->table} t
                     JOIN employees e ON t.employee_id = e.employee_id
                     JOIN positions p ON e.position_id = p.position_id
                     JOIN departments d ON e.department_id = d.department_id
                     WHERE t.training_id = ?";
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute([$trainingId]);

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get Training Details Error: " . $e->getMessage());
            return false;
        }
    }

    public function getEmployeeTrainings($employeeId, $status = null) {
        try {
            $query = "SELECT t.* 
                     FROM {$this->table} t
                     WHERE t.employee_id = ?";
            $params = [$employeeId];

            if ($status) {
                $query .= " AND t.status = ?";
                $params[] = $status;
            }

            $query .= " ORDER BY t.start_date DESC";
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute($params);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get Employee Trainings Error: " . $e->getMessage());
            return [];
        }
    }

    public function getDepartmentTrainings($departmentId, $status = null) {
        try {
            $query = "SELECT t.*, e.full_name as employee_name, e.employee_code 
                     FROM {$this->table} t
                     JOIN employees e ON t.employee_id = e.employee_id
                     WHERE e.department_id = ?";
            $params = [$departmentId];

            if ($status) {
                $query .= " AND t.status = ?";
                $params[] = $status;
            }

            $query .= " ORDER BY e.full_name ASC, t.start_date DESC";
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute($params);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get Department Trainings Error: " . $e->getMessage());
            return [];
        }
    }

    public function getUpcomingTrainings($limit = 10) {
        return $this->where('start_date', '>=', date('Y-m-d'))
                   ->where('status', 'active')
                   ->orderBy('start_date', 'ASC')
                   ->limit($limit)
                   ->get();
    }

    public function getTrainingStats($departmentId = null) {
        try {
            $query = "SELECT 
                        COUNT(DISTINCT t.training_id) as total_trainings,
                        COUNT(DISTINCT t.employee_id) as employees_with_trainings,
                        COUNT(DISTINCT CASE WHEN t.status = 'completed' THEN t.training_id END) as completed_trainings,
                        COUNT(DISTINCT CASE WHEN t.status = 'planned' THEN t.training_id END) as planned_trainings,
                        COUNT(DISTINCT t.training_type) as training_types,
                        SUM(t.cost) as total_cost
                     FROM {$this->table} t";
            
            $params = [];
            if ($departmentId) {
                $query .= " JOIN employees e ON t.employee_id = e.employee_id
                          WHERE e.department_id = ?";
                $params[] = $departmentId;
            }

            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute($params);

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get Training Stats Error: " . $e->getMessage());
            return false;
        }
    }

    public function searchTrainings($keyword, $departmentId = null, $trainingType = null) {
        try {
            $query = "SELECT t.*, e.full_name as employee_name, e.employee_code, d.department_name 
                     FROM {$this->table} t
                     JOIN employees e ON t.employee_id = e.employee_id
                     JOIN departments d ON e.department_id = d.department_id
                     WHERE (e.full_name LIKE ? OR t.training_name LIKE ? OR t.training_type LIKE ? OR t.provider LIKE ? OR t.notes LIKE ?)";
            $params = ["%$keyword%", "%$keyword%", "%$keyword%", "%$keyword%", "%$keyword%"];

            if ($departmentId) {
                $query .= " AND e.department_id = ?";
                $params[] = $departmentId;
            }

            if ($trainingType) {
                $query .= " AND t.training_type = ?";
                $params[] = $trainingType;
            }

            $query .= " ORDER BY e.full_name ASC, t.start_date DESC";
            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute($params);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Search Trainings Error: " . $e->getMessage());
            return [];
        }
    }

    public function registrations()
    {
        return $this->hasMany(TrainingRegistration::class, 'training_id');
    }

    public function getPastTrainings($limit = 10)
    {
        return $this->where('end_date', '<', date('Y-m-d'))
                   ->orderBy('end_date', 'DESC')
                   ->limit($limit)
                   ->get();
    }

    public function getTrainingByDepartment($departmentId)
    {
        return $this->whereHas('registrations', function($query) use ($departmentId) {
            $query->whereHas('user', function($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        })->get();
    }

    public function getTrainingParticipants($trainingId)
    {
        return $this->find($trainingId)->registrations()
                   ->with('user')
                   ->get();
    }

    public function registerUser($trainingId, $userId)
    {
        $training = $this->find($trainingId);
        if (!$training) {
            return ['success' => false, 'message' => 'Training not found'];
        }

        if ($training->status !== 'active') {
            return ['success' => false, 'message' => 'Training is not active'];
        }

        $registration = new TrainingRegistration();
        $registration->training_id = $trainingId;
        $registration->user_id = $userId;
        $registration->status = 'registered';
        $registration->registered_at = date('Y-m-d H:i:s');

        if ($registration->save()) {
            return ['success' => true, 'message' => 'Registration successful'];
        }

        return ['success' => false, 'message' => 'Registration failed'];
    }

    public function cancelRegistration($trainingId, $userId)
    {
        $registration = TrainingRegistration::where('training_id', $trainingId)
                                          ->where('user_id', $userId)
                                          ->first();

        if (!$registration) {
            return ['success' => false, 'message' => 'Registration not found'];
        }

        if ($registration->delete()) {
            return ['success' => true, 'message' => 'Registration cancelled'];
        }

        return ['success' => false, 'message' => 'Failed to cancel registration'];
    }

    public function getTrainingStatistics()
    {
        return [
            'total_trainings' => $this->count(),
            'active_trainings' => $this->where('status', 'active')->count(),
            'completed_trainings' => $this->where('status', 'completed')->count(),
            'upcoming_trainings' => $this->where('start_date', '>=', date('Y-m-d'))->count(),
            'total_participants' => TrainingRegistration::count()
        ];
    }
} 