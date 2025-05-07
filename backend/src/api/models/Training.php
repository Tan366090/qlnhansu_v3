<?php
namespace App\Models;

class Training extends BaseModel {
    protected $table = 'training_courses';
    
    protected $fillable = [
        'name',
        'description',
        'duration',
        'cost',
        'status'
    ];
    
    public function getWithDetails($id = null) {
        $conn = $this->db->getConnection();
        
        $sql = "
            SELECT t.*, 
                   COUNT(tr.id) as total_registrations,
                   COUNT(CASE WHEN tr.status = 'completed' THEN 1 END) as completed_count,
                   COUNT(CASE WHEN tr.status = 'attended' THEN 1 END) as attended_count
            FROM training_courses t
            LEFT JOIN training_registrations tr ON t.id = tr.course_id
            WHERE 1=1
        ";
        
        if ($id) {
            $sql .= " AND t.id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch();
        }
        
        $sql .= " GROUP BY t.id ORDER BY t.created_at DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getRegistrations($courseId) {
        $conn = $this->db->getConnection();
        
        $sql = "
            SELECT tr.*, 
                   e.employee_code,
                   up.full_name,
                   d.name as department_name,
                   p.name as position_name,
                   te.rating_content,
                   te.rating_instructor,
                   te.rating_materials,
                   te.comments as evaluation_comments
            FROM training_registrations tr
            JOIN employees e ON tr.employee_id = e.id
            JOIN user_profiles up ON e.user_id = up.user_id
            JOIN departments d ON e.department_id = d.id
            JOIN positions p ON e.position_id = p.id
            LEFT JOIN training_evaluations te ON tr.id = te.registration_id
            WHERE tr.course_id = ?
            ORDER BY tr.registration_date DESC
        ";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$courseId]);
        return $stmt->fetchAll();
    }
    
    public function getEmployeeTrainings($employeeId) {
        $conn = $this->db->getConnection();
        
        $sql = "
            SELECT t.*, 
                   tr.registration_date,
                   tr.status as registration_status,
                   tr.completion_date,
                   tr.score,
                   tr.feedback,
                   te.rating_content,
                   te.rating_instructor,
                   te.rating_materials,
                   te.comments as evaluation_comments
            FROM training_courses t
            JOIN training_registrations tr ON t.id = tr.course_id
            LEFT JOIN training_evaluations te ON tr.id = te.registration_id
            WHERE tr.employee_id = ?
            ORDER BY tr.registration_date DESC
        ";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$employeeId]);
        return $stmt->fetchAll();
    }
    
    public function getDepartmentTrainings($departmentId) {
        $conn = $this->db->getConnection();
        
        $sql = "
            SELECT t.*, 
                   COUNT(tr.id) as total_registrations,
                   COUNT(CASE WHEN tr.status = 'completed' THEN 1 END) as completed_count,
                   COUNT(CASE WHEN tr.status = 'attended' THEN 1 END) as attended_count
            FROM training_courses t
            JOIN training_registrations tr ON t.id = tr.course_id
            JOIN employees e ON tr.employee_id = e.id
            WHERE e.department_id = ?
            GROUP BY t.id
            ORDER BY t.created_at DESC
        ";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$departmentId]);
        return $stmt->fetchAll();
    }
    
    public function registerEmployee($courseId, $employeeId) {
        $conn = $this->db->getConnection();
        
        // Check if already registered
        $stmt = $conn->prepare("
            SELECT id FROM training_registrations 
            WHERE course_id = ? AND employee_id = ?
        ");
        $stmt->execute([$courseId, $employeeId]);
        if ($stmt->fetch()) {
            throw new \Exception('Employee already registered for this course');
        }
        
        // Register employee
        $stmt = $conn->prepare("
            INSERT INTO training_registrations 
            (course_id, employee_id, registration_date, status)
            VALUES (?, ?, NOW(), 'registered')
        ");
        
        return $stmt->execute([$courseId, $employeeId]);
    }
    
    public function updateRegistrationStatus($registrationId, $status, $score = null, $feedback = null) {
        $conn = $this->db->getConnection();
        
        $sql = "
            UPDATE training_registrations 
            SET status = ?,
                completion_date = CASE WHEN ? = 'completed' THEN NOW() ELSE NULL END,
                score = ?,
                feedback = ?,
                updated_at = NOW()
            WHERE id = ?
        ";
        
        $stmt = $conn->prepare($sql);
        return $stmt->execute([$status, $status, $score, $feedback, $registrationId]);
    }
    
    public function addEvaluation($registrationId, $employeeId, $ratingContent, $ratingInstructor, $ratingMaterials, $comments) {
        $conn = $this->db->getConnection();
        
        // Check if already evaluated
        $stmt = $conn->prepare("
            SELECT id FROM training_evaluations 
            WHERE registration_id = ? AND evaluator_employee_id = ?
        ");
        $stmt->execute([$registrationId, $employeeId]);
        if ($stmt->fetch()) {
            throw new \Exception('Course already evaluated by this employee');
        }
        
        // Add evaluation
        $stmt = $conn->prepare("
            INSERT INTO training_evaluations 
            (registration_id, evaluator_employee_id, evaluation_date, 
             rating_content, rating_instructor, rating_materials, comments)
            VALUES (?, ?, NOW(), ?, ?, ?, ?)
        ");
        
        return $stmt->execute([
            $registrationId, $employeeId, 
            $ratingContent, $ratingInstructor, $ratingMaterials, $comments
        ]);
    }
    
    public function getCourseEvaluations($courseId) {
        $conn = $this->db->getConnection();
        
        $sql = "
            SELECT te.*, 
                   e.employee_code,
                   up.full_name,
                   d.name as department_name
            FROM training_evaluations te
            JOIN training_registrations tr ON te.registration_id = tr.id
            JOIN employees e ON te.evaluator_employee_id = e.id
            JOIN user_profiles up ON e.user_id = up.user_id
            JOIN departments d ON e.department_id = d.id
            WHERE tr.course_id = ?
            ORDER BY te.evaluation_date DESC
        ";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$courseId]);
        return $stmt->fetchAll();
    }
} 