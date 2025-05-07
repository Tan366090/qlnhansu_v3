<?php
namespace App\Models;

class Project extends BaseModel {
    protected $table = 'projects';
    
    protected $fillable = [
        'name',
        'description',
        'start_date',
        'end_date',
        'status',
        'priority',
        'budget',
        'manager_id',
        'department_id',
        'client_id',
        'progress',
        'notes'
    ];
    
    public function createProject($name, $description, $startDate, $endDate, $managerId, $departmentId, $priority, $budget) {
        $conn = $this->db->getConnection();
        
        $stmt = $conn->prepare("
            INSERT INTO {$this->table}
            (name, description, start_date, end_date, manager_id, 
             department_id, priority, budget, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'planning', NOW())
        ");
        
        return $stmt->execute([
            $name, $description, $startDate, $endDate, 
            $managerId, $departmentId, $priority, $budget
        ]);
    }
    
    public function updateProject($projectId, $name, $description, $startDate, $endDate, $managerId, $departmentId, $priority, $budget) {
        $conn = $this->db->getConnection();
        
        $stmt = $conn->prepare("
            UPDATE {$this->table}
            SET name = ?,
                description = ?,
                start_date = ?,
                end_date = ?,
                manager_id = ?,
                department_id = ?,
                priority = ?,
                budget = ?,
                updated_at = NOW()
            WHERE id = ?
        ");
        
        return $stmt->execute([
            $name, $description, $startDate, $endDate, 
            $managerId, $departmentId, $priority, $budget, $projectId
        ]);
    }
    
    public function updateProjectStatus($projectId, $status) {
        $conn = $this->db->getConnection();
        
        $stmt = $conn->prepare("
            UPDATE {$this->table}
            SET status = ?,
                updated_at = NOW()
            WHERE id = ?
        ");
        
        return $stmt->execute([$status, $projectId]);
    }
    
    public function assignEmployee($projectId, $employeeId, $role) {
        $conn = $this->db->getConnection();
        
        // Check if employee is already assigned
        $stmt = $conn->prepare("
            SELECT COUNT(*) as count
            FROM project_members
            WHERE project_id = ? AND employee_id = ?
        ");
        
        $stmt->execute([$projectId, $employeeId]);
        if ($stmt->fetch()['count'] > 0) {
            throw new \Exception('Employee is already assigned to this project');
        }
        
        // Assign employee
        $stmt = $conn->prepare("
            INSERT INTO project_members
            (project_id, employee_id, role, joined_at)
            VALUES (?, ?, ?, NOW())
        ");
        
        return $stmt->execute([$projectId, $employeeId, $role]);
    }
    
    public function removeEmployee($projectId, $employeeId) {
        $conn = $this->db->getConnection();
        
        $stmt = $conn->prepare("
            DELETE FROM project_members
            WHERE project_id = ? AND employee_id = ?
        ");
        
        return $stmt->execute([$projectId, $employeeId]);
    }
    
    public function getProjectDetails($projectId) {
        $conn = $this->db->getConnection();
        
        $sql = "
            SELECT p.*, e.name as manager_name, 
                   d.name as department_name
            FROM {$this->table} p
            JOIN employees e ON p.manager_id = e.id
            JOIN departments d ON p.department_id = d.id
            WHERE p.id = ?
        ";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$projectId]);
        return $stmt->fetch();
    }
    
    public function getProjectMembers($projectId) {
        $conn = $this->db->getConnection();
        
        $sql = "
            SELECT e.*, 
                   up.full_name,
                   d.name as department_name,
                   p.name as position_name,
                   pm.role as project_role,
                   pm.join_date,
                   pm.leave_date
            FROM project_members pm
            JOIN employees e ON pm.employee_id = e.id
            JOIN user_profiles up ON e.user_id = up.user_id
            JOIN departments d ON e.department_id = d.id
            JOIN positions p ON e.position_id = p.id
            WHERE pm.project_id = ?
            ORDER BY pm.join_date DESC
        ";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$projectId]);
        return $stmt->fetchAll();
    }
    
    public function getEmployeeProjects($employeeId, $status = null) {
        $conn = $this->db->getConnection();
        
        $sql = "
            SELECT p.*, e.name as manager_name, d.name as department_name, pm.role
            FROM {$this->table} p
            JOIN employees e ON p.manager_id = e.id
            JOIN departments d ON p.department_id = d.id
            JOIN project_members pm ON p.id = pm.project_id
            WHERE pm.employee_id = ?
        ";
        
        $params = [$employeeId];
        
        if ($status) {
            $sql .= " AND p.status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY p.start_date DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function getDepartmentProjects($departmentId, $status = null) {
        $conn = $this->db->getConnection();
        
        $sql = "
            SELECT p.*, e.name as manager_name,
                   COUNT(pm.id) as member_count
            FROM {$this->table} p
            JOIN employees e ON p.manager_id = e.id
            LEFT JOIN project_members pm ON p.id = pm.project_id
            WHERE p.department_id = ?
        ";
        
        $params = [$departmentId];
        
        if ($status) {
            $sql .= " AND p.status = ?";
            $params[] = $status;
        }
        
        $sql .= " GROUP BY p.id ORDER BY p.start_date DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function getProjectProgress($projectId) {
        $conn = $this->db->getConnection();
        
        $sql = "
            SELECT 
                COUNT(*) as total_tasks,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_tasks,
                AVG(progress) as avg_progress
            FROM project_tasks
            WHERE project_id = ?
        ";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$projectId]);
        return $stmt->fetch();
    }
    
    public function getProjectTimeline($projectId) {
        $conn = $this->db->getConnection();
        
        $sql = "
            SELECT 
                'project' as type,
                p.created_at as date,
                'Project created' as description,
                NULL as user_id,
                NULL as user_name
            FROM projects p
            WHERE p.id = ?
            
            UNION ALL
            
            SELECT 
                'milestone' as type,
                m.due_date as date,
                CONCAT('Milestone: ', m.name) as description,
                NULL as user_id,
                NULL as user_name
            FROM milestones m
            WHERE m.project_id = ?
            
            UNION ALL
            
            SELECT 
                'task' as type,
                t.created_at as date,
                CONCAT('Task created: ', t.title) as description,
                t.created_by as user_id,
                up.full_name as user_name
            FROM tasks t
            JOIN users u ON t.created_by = u.id
            JOIN user_profiles up ON u.id = up.user_id
            WHERE t.project_id = ?
            
            UNION ALL
            
            SELECT 
                'document' as type,
                d.uploaded_at as date,
                CONCAT('Document uploaded: ', d.name) as description,
                d.uploaded_by as user_id,
                up.full_name as user_name
            FROM documents d
            JOIN employees e ON d.uploaded_by = e.id
            JOIN user_profiles up ON e.user_id = up.user_id
            WHERE d.project_id = ?
            
            ORDER BY date DESC
        ";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$projectId, $projectId, $projectId, $projectId]);
        return $stmt->fetchAll();
    }
    
    public function getWithDetails($id = null) {
        $conn = $this->db->getConnection();
        
        $sql = "
            SELECT p.*, 
                   d.name as department_name,
                   up.full_name as manager_name,
                   c.name as client_name,
                   COUNT(t.id) as total_tasks,
                   COUNT(CASE WHEN t.status = 'completed' THEN 1 END) as completed_tasks,
                   COUNT(CASE WHEN t.status = 'in_progress' THEN 1 END) as in_progress_tasks
            FROM projects p
            JOIN departments d ON p.department_id = d.id
            LEFT JOIN users u ON p.manager_id = u.id
            LEFT JOIN user_profiles up ON u.id = up.user_id
            LEFT JOIN clients c ON p.client_id = c.id
            LEFT JOIN tasks t ON p.id = t.project_id
            WHERE 1=1
        ";
        
        if ($id) {
            $sql .= " AND p.id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch();
        }
        
        $sql .= " GROUP BY p.id ORDER BY p.created_at DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getByDepartment($departmentId) {
        $conn = $this->db->getConnection();
        
        $sql = "
            SELECT p.*, 
                   d.name as department_name,
                   up.full_name as manager_name,
                   c.name as client_name,
                   COUNT(t.id) as total_tasks,
                   COUNT(CASE WHEN t.status = 'completed' THEN 1 END) as completed_tasks,
                   COUNT(CASE WHEN t.status = 'in_progress' THEN 1 END) as in_progress_tasks
            FROM projects p
            JOIN departments d ON p.department_id = d.id
            LEFT JOIN users u ON p.manager_id = u.id
            LEFT JOIN user_profiles up ON u.id = up.user_id
            LEFT JOIN clients c ON p.client_id = c.id
            LEFT JOIN tasks t ON p.id = t.project_id
            WHERE p.department_id = ?
            GROUP BY p.id
            ORDER BY p.created_at DESC
        ";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$departmentId]);
        return $stmt->fetchAll();
    }
    
    public function getByManager($managerId) {
        $conn = $this->db->getConnection();
        
        $sql = "
            SELECT p.*, 
                   d.name as department_name,
                   up.full_name as manager_name,
                   c.name as client_name,
                   COUNT(t.id) as total_tasks,
                   COUNT(CASE WHEN t.status = 'completed' THEN 1 END) as completed_tasks,
                   COUNT(CASE WHEN t.status = 'in_progress' THEN 1 END) as in_progress_tasks
            FROM projects p
            JOIN departments d ON p.department_id = d.id
            LEFT JOIN users u ON p.manager_id = u.id
            LEFT JOIN user_profiles up ON u.id = up.user_id
            LEFT JOIN clients c ON p.client_id = c.id
            LEFT JOIN tasks t ON p.id = t.project_id
            WHERE p.manager_id = ?
            GROUP BY p.id
            ORDER BY p.created_at DESC
        ";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$managerId]);
        return $stmt->fetchAll();
    }
    
    public function getByClient($clientId) {
        $conn = $this->db->getConnection();
        
        $sql = "
            SELECT p.*, 
                   d.name as department_name,
                   up.full_name as manager_name,
                   c.name as client_name,
                   COUNT(t.id) as total_tasks,
                   COUNT(CASE WHEN t.status = 'completed' THEN 1 END) as completed_tasks,
                   COUNT(CASE WHEN t.status = 'in_progress' THEN 1 END) as in_progress_tasks
            FROM projects p
            JOIN departments d ON p.department_id = d.id
            LEFT JOIN users u ON p.manager_id = u.id
            LEFT JOIN user_profiles up ON u.id = up.user_id
            LEFT JOIN clients c ON p.client_id = c.id
            LEFT JOIN tasks t ON p.id = t.project_id
            WHERE p.client_id = ?
            GROUP BY p.id
            ORDER BY p.created_at DESC
        ";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$clientId]);
        return $stmt->fetchAll();
    }
    
    public function addMember($projectId, $employeeId, $role) {
        $conn = $this->db->getConnection();
        
        // Check if already a member
        $stmt = $conn->prepare("
            SELECT id FROM project_members 
            WHERE project_id = ? AND employee_id = ?
        ");
        $stmt->execute([$projectId, $employeeId]);
        if ($stmt->fetch()) {
            throw new \Exception('Employee is already a member of this project');
        }
        
        // Add member
        $stmt = $conn->prepare("
            INSERT INTO project_members 
            (project_id, employee_id, role, join_date)
            VALUES (?, ?, ?, NOW())
        ");
        
        return $stmt->execute([$projectId, $employeeId, $role]);
    }
    
    public function removeMember($projectId, $employeeId) {
        $conn = $this->db->getConnection();
        
        $stmt = $conn->prepare("
            UPDATE project_members 
            SET leave_date = NOW(),
                updated_at = NOW()
            WHERE project_id = ? AND employee_id = ?
            AND leave_date IS NULL
        ");
        
        return $stmt->execute([$projectId, $employeeId]);
    }
    
    public function updateProgress($projectId, $progress) {
        $conn = $this->db->getConnection();
        
        $stmt = $conn->prepare("
            UPDATE projects 
            SET progress = ?,
                updated_at = NOW()
            WHERE id = ?
        ");
        
        return $stmt->execute([$progress, $projectId]);
    }
    
    public function getProjectTasks($projectId) {
        $conn = $this->db->getConnection();
        
        $sql = "
            SELECT t.*, 
                   e.employee_code,
                   up.full_name as assigned_to_name,
                   d.name as department_name,
                   p.name as position_name
            FROM tasks t
            LEFT JOIN employees e ON t.assigned_to = e.id
            LEFT JOIN user_profiles up ON e.user_id = up.user_id
            LEFT JOIN departments d ON e.department_id = d.id
            LEFT JOIN positions p ON e.position_id = p.id
            WHERE t.project_id = ?
            ORDER BY t.due_date ASC
        ";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$projectId]);
        return $stmt->fetchAll();
    }
    
    public function getProjectMilestones($projectId) {
        $conn = $this->db->getConnection();
        
        $sql = "
            SELECT m.*, 
                   COUNT(t.id) as total_tasks,
                   COUNT(CASE WHEN t.status = 'completed' THEN 1 END) as completed_tasks
            FROM milestones m
            LEFT JOIN tasks t ON m.id = t.milestone_id
            WHERE m.project_id = ?
            GROUP BY m.id
            ORDER BY m.due_date ASC
        ";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$projectId]);
        return $stmt->fetchAll();
    }
    
    public function getProjectDocuments($projectId) {
        $conn = $this->db->getConnection();
        
        $sql = "
            SELECT d.*, 
                   e.employee_code,
                   up.full_name as uploaded_by_name,
                   d.name as department_name
            FROM documents d
            JOIN employees e ON d.uploaded_by = e.id
            JOIN user_profiles up ON e.user_id = up.user_id
            JOIN departments d ON e.department_id = d.id
            WHERE d.project_id = ?
            ORDER BY d.uploaded_at DESC
        ";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([$projectId]);
        return $stmt->fetchAll();
    }
} 