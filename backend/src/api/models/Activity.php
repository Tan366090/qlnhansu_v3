<?php
namespace App\Models;

class Activity extends BaseModel {
    protected $table = 'activities';
    
    protected $fillable = [
        'name',
        'description',
        'start_date',
        'end_date',
        'location',
        'organizer_id',
        'department_id',
        'status',
        'budget',
        'participant_limit',
        'registration_deadline',
        'is_public'
    ];
    
    public function getWithDetails($id = null) {
        $conn = $this->db->getConnection();
        
        $sql = "
            SELECT a.*, 
                   e.employee_code,
                   up.full_name as organizer_name,
                   d.name as department_name,
                   COUNT(ap.id) as participant_count
            FROM activities a
            JOIN employees e ON a.organizer_id = e.id
            JOIN user_profiles up ON e.user_id = up.user_id
            LEFT JOIN departments d ON a.department_id = d.id
            LEFT JOIN activity_participants ap ON a.id = ap.activity_id
            WHERE 1=1
        ";
        
        if ($id) {
            $sql .= " AND a.id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch();
        }
        
        $sql .= " GROUP BY a.id ORDER BY a.start_date DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getByDepartment($departmentId) {
        $conn = $this->db->getConnection();
        
        $sql = "
            SELECT a.*, 
                   e.employee_code,
                   up.full_name as organizer_name,
                   d.name as department_name,
                   COUNT(ap.id) as participant_count
            FROM activities a
            JOIN employees e ON a.organizer_id = e.id
            JOIN user_profiles up ON e.user_id = up.user_id
            LEFT JOIN departments d ON a.department_id = d.id
            LEFT JOIN activity_participants ap ON a.id = ap.activity_id
            WHERE a.department_id = ?
            GROUP BY a.id
            ORDER BY a.start_date DESC
        ";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$departmentId]);
        return $stmt->fetchAll();
    }
    
    public function getByOrganizer($employeeId) {
        $conn = $this->db->getConnection();
        
        $sql = "
            SELECT a.*, 
                   e.employee_code,
                   up.full_name as organizer_name,
                   d.name as department_name,
                   COUNT(ap.id) as participant_count
            FROM activities a
            JOIN employees e ON a.organizer_id = e.id
            JOIN user_profiles up ON e.user_id = up.user_id
            LEFT JOIN departments d ON a.department_id = d.id
            LEFT JOIN activity_participants ap ON a.id = ap.activity_id
            WHERE a.organizer_id = ?
            GROUP BY a.id
            ORDER BY a.start_date DESC
        ";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$employeeId]);
        return $stmt->fetchAll();
    }
    
    public function getUpcomingActivities($limit = 10) {
        $conn = $this->db->getConnection();
        
        $sql = "
            SELECT a.*, 
                   e.employee_code,
                   up.full_name as organizer_name,
                   d.name as department_name,
                   COUNT(ap.id) as participant_count
            FROM activities a
            JOIN employees e ON a.organizer_id = e.id
            JOIN user_profiles up ON e.user_id = up.user_id
            LEFT JOIN departments d ON a.department_id = d.id
            LEFT JOIN activity_participants ap ON a.id = ap.activity_id
            WHERE a.start_date > NOW()
            AND a.status = 'active'
            GROUP BY a.id
            ORDER BY a.start_date ASC
            LIMIT ?
        ";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
    
    public function getPastActivities($limit = 10) {
        $conn = $this->db->getConnection();
        
        $sql = "
            SELECT a.*, 
                   e.employee_code,
                   up.full_name as organizer_name,
                   d.name as department_name,
                   COUNT(ap.id) as participant_count
            FROM activities a
            JOIN employees e ON a.organizer_id = e.id
            JOIN user_profiles up ON e.user_id = up.user_id
            LEFT JOIN departments d ON a.department_id = d.id
            LEFT JOIN activity_participants ap ON a.id = ap.activity_id
            WHERE a.end_date < NOW()
            AND a.status = 'completed'
            GROUP BY a.id
            ORDER BY a.end_date DESC
            LIMIT ?
        ";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
    
    public function getActivityParticipants($activityId) {
        $conn = $this->db->getConnection();
        
        $sql = "
            SELECT ap.*, 
                   e.employee_code,
                   up.full_name as participant_name,
                   d.name as department_name,
                   p.name as position_name
            FROM activity_participants ap
            JOIN employees e ON ap.employee_id = e.id
            JOIN user_profiles up ON e.user_id = up.user_id
            LEFT JOIN departments d ON e.department_id = d.id
            LEFT JOIN positions p ON e.position_id = p.id
            WHERE ap.activity_id = ?
            ORDER BY ap.registered_at DESC
        ";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$activityId]);
        return $stmt->fetchAll();
    }
    
    public function registerParticipant($activityId, $employeeId) {
        $conn = $this->db->getConnection();
        
        // Check if activity exists and is open for registration
        $stmt = $conn->prepare("
            SELECT id, participant_limit, registration_deadline
            FROM activities
            WHERE id = ? AND status = 'active'
            AND registration_deadline > NOW()
        ");
        $stmt->execute([$activityId]);
        $activity = $stmt->fetch();
        
        if (!$activity) {
            throw new \Exception('Activity not found or registration closed');
        }
        
        // Check if participant limit reached
        if ($activity['participant_limit']) {
            $stmt = $conn->prepare("
                SELECT COUNT(*) as count
                FROM activity_participants
                WHERE activity_id = ?
            ");
            $stmt->execute([$activityId]);
            $count = $stmt->fetch()['count'];
            
            if ($count >= $activity['participant_limit']) {
                throw new \Exception('Activity is full');
            }
        }
        
        // Check if already registered
        $stmt = $conn->prepare("
            SELECT id FROM activity_participants
            WHERE activity_id = ? AND employee_id = ?
        ");
        $stmt->execute([$activityId, $employeeId]);
        if ($stmt->fetch()) {
            throw new \Exception('Already registered for this activity');
        }
        
        // Register participant
        $stmt = $conn->prepare("
            INSERT INTO activity_participants
            (activity_id, employee_id, registered_at)
            VALUES (?, ?, NOW())
        ");
        
        return $stmt->execute([$activityId, $employeeId]);
    }
    
    public function cancelRegistration($activityId, $employeeId) {
        $conn = $this->db->getConnection();
        
        $stmt = $conn->prepare("
            DELETE FROM activity_participants
            WHERE activity_id = ? AND employee_id = ?
        ");
        
        return $stmt->execute([$activityId, $employeeId]);
    }
    
    public function updateActivityStatus($activityId, $status) {
        $conn = $this->db->getConnection();
        
        $stmt = $conn->prepare("
            UPDATE activities
            SET status = ?,
                updated_at = NOW()
            WHERE id = ?
        ");
        
        return $stmt->execute([$status, $activityId]);
    }
    
    public function searchActivities($query) {
        $conn = $this->db->getConnection();
        
        $sql = "
            SELECT a.*, 
                   e.employee_code,
                   up.full_name as organizer_name,
                   d.name as department_name,
                   COUNT(ap.id) as participant_count
            FROM activities a
            JOIN employees e ON a.organizer_id = e.id
            JOIN user_profiles up ON e.user_id = up.user_id
            LEFT JOIN departments d ON a.department_id = d.id
            LEFT JOIN activity_participants ap ON a.id = ap.activity_id
            WHERE a.name LIKE ?
            OR a.description LIKE ?
            AND a.status = 'active'
            GROUP BY a.id
            ORDER BY a.start_date DESC
        ";
        
        $searchTerm = "%{$query}%";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$searchTerm, $searchTerm]);
        return $stmt->fetchAll();
    }
} 