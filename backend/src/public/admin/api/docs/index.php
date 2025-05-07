<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Database configuration
$db_host = 'localhost';
$db_name = 'qlnhansu';
$db_user = 'root';
$db_pass = '';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path_parts = explode('/', trim($path, '/'));

// Handle different HTTP methods
switch($method) {
    case 'GET':
        if (isset($path_parts[5]) && $path_parts[5] === 'tables') {
            // Get all tables structure
            $tables = [
                'activities',
                'attendance',
                'benefits',
                'candidates',
                'certificates',
                'contracts',
                'departments',
                'documents',
                'leave_requests',
                'positions',
                'salaries',
                'users'
            ];
            
            $structures = [];
            foreach ($tables as $table) {
                $stmt = $pdo->query("SHOW CREATE TABLE $table");
                $create_table = $stmt->fetch(PDO::FETCH_ASSOC);
                $structures[$table] = $create_table['Create Table'];
            }
            
            echo json_encode($structures);
        } else if (isset($path_parts[5]) && $path_parts[5] === 'endpoints') {
            // Get all API endpoints documentation
            $endpoints = [
                'activities' => [
                    'GET /activities' => 'Get all activities with pagination',
                    'GET /activities/{id}' => 'Get activity by ID',
                    'GET /activities?user_id={user_id}' => 'Get activities by user ID',
                    'GET /activities?type={type}' => 'Get activities by type',
                    'GET /activities?start_date={start_date}&end_date={end_date}' => 'Get activities by date range',
                    'POST /activities' => 'Create new activity'
                ],
                'attendance' => [
                    'GET /attendance' => 'Get all attendance records with pagination',
                    'GET /attendance/{id}' => 'Get attendance record by ID',
                    'GET /attendance?user_id={user_id}' => 'Get attendance records by user ID',
                    'GET /attendance?date={date}' => 'Get attendance records by date',
                    'GET /attendance?month={month}&year={year}' => 'Get attendance records by month and year',
                    'POST /attendance' => 'Create new attendance record',
                    'PUT /attendance/{id}' => 'Update attendance record'
                ],
                'benefits' => [
                    'GET /benefits' => 'Get all benefits with pagination',
                    'GET /benefits/{id}' => 'Get benefit by ID',
                    'GET /benefits?user_id={user_id}' => 'Get benefits by user ID',
                    'GET /benefits?type={type}' => 'Get benefits by type',
                    'POST /benefits' => 'Create new benefit',
                    'PUT /benefits/{id}' => 'Update benefit',
                    'DELETE /benefits/{id}' => 'Delete benefit'
                ],
                'candidates' => [
                    'GET /candidates' => 'Get all candidates with pagination',
                    'GET /candidates/{id}' => 'Get candidate by ID',
                    'GET /candidates?status={status}' => 'Get candidates by status',
                    'GET /candidates?position={position}' => 'Get candidates by position',
                    'POST /candidates' => 'Create new candidate',
                    'PUT /candidates/{id}' => 'Update candidate',
                    'DELETE /candidates/{id}' => 'Delete candidate'
                ],
                'certificates' => [
                    'GET /certificates' => 'Get all certificates with pagination',
                    'GET /certificates/{id}' => 'Get certificate by ID',
                    'GET /certificates?user_id={user_id}' => 'Get certificates by user ID',
                    'GET /certificates?type={type}' => 'Get certificates by type',
                    'POST /certificates' => 'Create new certificate',
                    'PUT /certificates/{id}' => 'Update certificate',
                    'DELETE /certificates/{id}' => 'Delete certificate'
                ],
                'contracts' => [
                    'GET /contracts' => 'Get all contracts with pagination',
                    'GET /contracts/{id}' => 'Get contract by ID',
                    'GET /contracts?user_id={user_id}' => 'Get contracts by user ID',
                    'GET /contracts?type={type}' => 'Get contracts by type',
                    'GET /contracts?status={status}' => 'Get contracts by status',
                    'POST /contracts' => 'Create new contract',
                    'PUT /contracts/{id}' => 'Update contract',
                    'DELETE /contracts/{id}' => 'Delete contract'
                ],
                'departments' => [
                    'GET /departments' => 'Get all departments with pagination',
                    'GET /departments/{id}' => 'Get department by ID',
                    'POST /departments' => 'Create new department',
                    'PUT /departments/{id}' => 'Update department',
                    'DELETE /departments/{id}' => 'Delete department'
                ],
                'documents' => [
                    'GET /documents' => 'Get all documents with pagination',
                    'GET /documents/{id}' => 'Get document by ID',
                    'GET /documents?type={type}' => 'Get documents by type',
                    'GET /documents?department_id={department_id}' => 'Get documents by department',
                    'POST /documents' => 'Create new document',
                    'PUT /documents/{id}' => 'Update document',
                    'DELETE /documents/{id}' => 'Delete document'
                ],
                'leave_requests' => [
                    'GET /leave-requests' => 'Get all leave requests with pagination',
                    'GET /leave-requests/{id}' => 'Get leave request by ID',
                    'GET /leave-requests?user_id={user_id}' => 'Get leave requests by user ID',
                    'GET /leave-requests?status={status}' => 'Get leave requests by status',
                    'GET /leave-requests?type={type}' => 'Get leave requests by type',
                    'POST /leave-requests' => 'Create new leave request',
                    'PUT /leave-requests/{id}' => 'Update leave request',
                    'DELETE /leave-requests/{id}' => 'Delete leave request'
                ],
                'positions' => [
                    'GET /positions' => 'Get all positions with pagination',
                    'GET /positions/{id}' => 'Get position by ID',
                    'POST /positions' => 'Create new position',
                    'PUT /positions/{id}' => 'Update position',
                    'DELETE /positions/{id}' => 'Delete position'
                ],
                'salaries' => [
                    'GET /salaries' => 'Get all salaries with pagination',
                    'GET /salaries/{id}' => 'Get salary by ID',
                    'GET /salaries?user_id={user_id}' => 'Get salaries by user ID',
                    'GET /salaries?month={month}&year={year}' => 'Get salaries by month and year',
                    'POST /salaries' => 'Create new salary',
                    'PUT /salaries/{id}' => 'Update salary',
                    'DELETE /salaries/{id}' => 'Delete salary'
                ],
                'users' => [
                    'GET /users' => 'Get all users with pagination',
                    'GET /users/{id}' => 'Get user by ID',
                    'GET /users?department_id={department_id}' => 'Get users by department',
                    'GET /users?position={position}' => 'Get users by position',
                    'POST /users' => 'Create new user',
                    'PUT /users/{id}' => 'Update user',
                    'DELETE /users/{id}' => 'Delete user'
                ],
                'backup' => [
                    'GET /backup' => 'Get backup status',
                    'GET /backup/list' => 'List all backups',
                    'GET /backup/download' => 'Download backup file',
                    'POST /backup/create' => 'Create new backup',
                    'POST /backup/restore' => 'Restore from backup'
                ],
                'files' => [
                    'GET /files' => 'Get all files with pagination',
                    'GET /files/{id}' => 'Get file by ID',
                    'GET /files?department_id={department_id}' => 'Get files by department',
                    'GET /files?type={type}' => 'Get files by type',
                    'POST /files' => 'Upload new file',
                    'DELETE /files/{id}' => 'Delete file'
                ],
                'email-templates' => [
                    'GET /email-templates' => 'Get all email templates with pagination',
                    'GET /email-templates/{id}' => 'Get email template by ID',
                    'GET /email-templates?type={type}' => 'Get email templates by type',
                    'GET /email-templates?status={status}' => 'Get email templates by status',
                    'POST /email-templates' => 'Create new email template',
                    'PUT /email-templates/{id}' => 'Update email template',
                    'DELETE /email-templates/{id}' => 'Delete email template'
                ],
                'dashboard' => [
                    'GET /dashboard' => 'Get all dashboard data',
                    'GET /dashboard/overview' => 'Get overview statistics',
                    'GET /dashboard/attendance' => 'Get attendance statistics',
                    'GET /dashboard/leave' => 'Get leave statistics by department',
                    'GET /dashboard/salary' => 'Get salary statistics',
                    'GET /dashboard/employee' => 'Get employee statistics by department'
                ]
            ];
            
            echo json_encode($endpoints);
        } else {
            // Get API documentation overview
            $overview = [
                'title' => 'QLNhanSu API Documentation',
                'version' => '1.0.0',
                'description' => 'API documentation for QLNhanSu Human Resource Management System',
                'base_url' => '/admin/api',
                'authentication' => [
                    'type' => 'Bearer Token',
                    'header' => 'Authorization: Bearer {token}'
                ],
                'modules' => [
                    'activities' => 'System activities and audit logs',
                    'attendance' => 'Employee attendance management',
                    'benefits' => 'Employee benefits management',
                    'candidates' => 'Job candidates management',
                    'certificates' => 'Employee certificates management',
                    'contracts' => 'Employee contracts management',
                    'departments' => 'Department management',
                    'documents' => 'Document management',
                    'leave_requests' => 'Leave requests management',
                    'positions' => 'Job positions management',
                    'salaries' => 'Salary management',
                    'users' => 'User management',
                    'backup' => 'Database backup and restore',
                    'files' => 'File upload and management',
                    'email_templates' => 'Email templates management',
                    'dashboard' => 'Dashboard and statistics'
                ]
            ];
            
            echo json_encode($overview);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}
?> 