<?php

use App\Middleware\AuthMiddleware;
use App\Middleware\PermissionMiddleware;
use App\Middleware\ScopeMiddleware;

// Public routes
$router->post('/auth/login', 'AuthController@login');
$router->post('/auth/register', 'AuthController@register');

// Protected routes
$router->group(['middleware' => [AuthMiddleware::class]], function($router) {
    // Dashboard routes
    $router->get('/dashboard/admin', 'DashboardController@admin', [PermissionMiddleware::class, 'check', 'admin.dashboard']);
    $router->get('/dashboard/manager', 'DashboardController@manager', [PermissionMiddleware::class, 'check', 'manager.dashboard']);
    $router->get('/dashboard/hr', 'DashboardController@hr', [PermissionMiddleware::class, 'check', 'hr.dashboard']);
    $router->get('/dashboard/employee', 'DashboardController@employee', [PermissionMiddleware::class, 'check', 'employee.dashboard']);

    // User routes
    $router->group(['middleware' => [ScopeMiddleware::class, 'hrNoDepartment']], function($router) {
        $router->get('/users', 'UserController@index', [PermissionMiddleware::class, 'check', 'user.view']);
        $router->post('/users', 'UserController@store', [PermissionMiddleware::class, 'check', 'user.create']);
        $router->get('/users/{id}', 'UserController@show', [PermissionMiddleware::class, 'check', 'user.view']);
        $router->put('/users/{id}', 'UserController@update', [PermissionMiddleware::class, 'check', 'user.edit']);
        $router->delete('/users/{id}', 'UserController@delete', [PermissionMiddleware::class, 'check', 'user.delete']);
    });

    // Attendance routes
    $router->group(['middleware' => [ScopeMiddleware::class, 'employeeSelf']], function($router) {
        $router->get('/attendance', 'AttendanceController@index', [PermissionMiddleware::class, 'check', 'attendance.view']);
        $router->post('/attendance', 'AttendanceController@store', [PermissionMiddleware::class, 'check', 'attendance.create']);
        $router->get('/attendance/{id}', 'AttendanceController@show', [PermissionMiddleware::class, 'check', 'attendance.view']);
        $router->put('/attendance/{id}', 'AttendanceController@update', [PermissionMiddleware::class, 'check', 'attendance.edit']);
        $router->delete('/attendance/{id}', 'AttendanceController@delete', [PermissionMiddleware::class, 'check', 'attendance.delete']);
    });

    // Salary routes
    $router->group(['middleware' => [ScopeMiddleware::class, 'hrNoDepartment']], function($router) {
        $router->get('/salary', 'SalaryController@index', [PermissionMiddleware::class, 'check', 'salary.view']);
        $router->post('/salary', 'SalaryController@store', [PermissionMiddleware::class, 'check', 'salary.create']);
        $router->get('/salary/{id}', 'SalaryController@show', [PermissionMiddleware::class, 'check', 'salary.view']);
        $router->put('/salary/{id}', 'SalaryController@update', [PermissionMiddleware::class, 'check', 'salary.edit']);
        $router->delete('/salary/{id}', 'SalaryController@delete', [PermissionMiddleware::class, 'check', 'salary.delete']);
    });

    // Department routes
    $router->group(['middleware' => [ScopeMiddleware::class, 'hrNoDepartment']], function($router) {
        $router->get('/departments', 'DepartmentController@index', [PermissionMiddleware::class, 'check', 'department.view']);
        $router->post('/departments', 'DepartmentController@store', [PermissionMiddleware::class, 'check', 'department.create']);
        $router->get('/departments/{id}', 'DepartmentController@show', [PermissionMiddleware::class, 'check', 'department.view']);
        $router->put('/departments/{id}', 'DepartmentController@update', [PermissionMiddleware::class, 'check', 'department.edit']);
        $router->delete('/departments/{id}', 'DepartmentController@delete', [PermissionMiddleware::class, 'check', 'department.delete']);
    });

    // Leave routes
    $router->group(['middleware' => [ScopeMiddleware::class, 'employeeSelf']], function($router) {
        $router->get('/leaves', 'LeaveController@index', [PermissionMiddleware::class, 'check', 'leave.view']);
        $router->post('/leaves', 'LeaveController@store', [PermissionMiddleware::class, 'check', 'leave.create']);
        $router->get('/leaves/{id}', 'LeaveController@show', [PermissionMiddleware::class, 'check', 'leave.view']);
        $router->put('/leaves/{id}', 'LeaveController@update', [PermissionMiddleware::class, 'check', 'leave.edit']);
        $router->delete('/leaves/{id}', 'LeaveController@delete', [PermissionMiddleware::class, 'check', 'leave.delete']);
    });

    // Training routes
    $router->group(['middleware' => [ScopeMiddleware::class, 'managerDepartment']], function($router) {
        $router->get('/trainings', 'TrainingController@index', [PermissionMiddleware::class, 'check', 'training.view']);
        $router->post('/trainings', 'TrainingController@store', [PermissionMiddleware::class, 'check', 'training.create']);
        $router->get('/trainings/{id}', 'TrainingController@show', [PermissionMiddleware::class, 'check', 'training.view']);
        $router->put('/trainings/{id}', 'TrainingController@update', [PermissionMiddleware::class, 'check', 'training.edit']);
        $router->delete('/trainings/{id}', 'TrainingController@delete', [PermissionMiddleware::class, 'check', 'training.delete']);
    });

    // Certificate routes
    $router->group(['middleware' => [ScopeMiddleware::class, 'hrNoDepartment']], function($router) {
        $router->get('/certificates', 'CertificateController@index', [PermissionMiddleware::class, 'check', 'certificate.view']);
        $router->post('/certificates', 'CertificateController@store', [PermissionMiddleware::class, 'check', 'certificate.create']);
        $router->get('/certificates/{id}', 'CertificateController@show', [PermissionMiddleware::class, 'check', 'certificate.view']);
        $router->put('/certificates/{id}', 'CertificateController@update', [PermissionMiddleware::class, 'check', 'certificate.edit']);
        $router->delete('/certificates/{id}', 'CertificateController@delete', [PermissionMiddleware::class, 'check', 'certificate.delete']);
    });

    // Document routes
    $router->group(['middleware' => [ScopeMiddleware::class, 'hrNoDepartment']], function($router) {
        $router->get('/documents', 'DocumentController@index', [PermissionMiddleware::class, 'check', 'document.view']);
        $router->post('/documents', 'DocumentController@store', [PermissionMiddleware::class, 'check', 'document.create']);
        $router->get('/documents/{id}', 'DocumentController@show', [PermissionMiddleware::class, 'check', 'document.view']);
        $router->put('/documents/{id}', 'DocumentController@update', [PermissionMiddleware::class, 'check', 'document.edit']);
        $router->delete('/documents/{id}', 'DocumentController@delete', [PermissionMiddleware::class, 'check', 'document.delete']);
        $router->post('/documents/upload', 'DocumentController@upload', [PermissionMiddleware::class, 'check', 'document.create']);
    });

    // Equipment routes
    $router->group(['middleware' => [ScopeMiddleware::class, 'managerDepartment']], function($router) {
        $router->get('/equipment', 'EquipmentController@index', [PermissionMiddleware::class, 'check', 'equipment.view']);
        $router->post('/equipment', 'EquipmentController@store', [PermissionMiddleware::class, 'check', 'equipment.create']);
        $router->get('/equipment/{id}', 'EquipmentController@show', [PermissionMiddleware::class, 'check', 'equipment.view']);
        $router->put('/equipment/{id}', 'EquipmentController@update', [PermissionMiddleware::class, 'check', 'equipment.edit']);
        $router->delete('/equipment/{id}', 'EquipmentController@delete', [PermissionMiddleware::class, 'check', 'equipment.delete']);
        $router->post('/equipment/{id}/assign', 'EquipmentController@assignEquipment', [PermissionMiddleware::class, 'check', 'equipment.edit']);
        $router->post('/equipment/{id}/return', 'EquipmentController@returnEquipment', [PermissionMiddleware::class, 'check', 'equipment.edit']);
    });
});

// Training routes
$router->group('/trainings', function($router) {
    $router->get('/', 'TrainingController@index');
    $router->post('/', 'TrainingController@store');
    $router->get('/{id}', 'TrainingController@show');
    $router->put('/{id}', 'TrainingController@update');
    $router->delete('/{id}', 'TrainingController@delete');
    $router->post('/{id}/register', 'TrainingController@register');
    $router->get('/{id}/participants', 'TrainingController@getParticipants');
    $router->get('/user', 'TrainingController@getUserTrainings');
    $router->get('/department/{departmentId}', 'TrainingController@getDepartmentTrainings');
    $router->put('/registrations/{registrationId}/status', 'TrainingController@updateRegistrationStatus');
    $router->post('/registrations/{registrationId}/feedback', 'TrainingController@addFeedback');
});

// Certificate routes
$router->group('/certificates', function($router) {
    $router->get('/', 'CertificateController@index');
    $router->post('/', 'CertificateController@store');
    $router->get('/{id}', 'CertificateController@show');
    $router->put('/{id}', 'CertificateController@update');
    $router->delete('/{id}', 'CertificateController@delete');
    $router->get('/user/{userId}', 'CertificateController@getUserCertificates');
    $router->get('/expired', 'CertificateController@getExpiredCertificates');
    $router->get('/expiring', 'CertificateController@getExpiringCertificates');
    $router->get('/department/{departmentId}', 'CertificateController@getDepartmentCertificates');
    $router->get('/search', 'CertificateController@search');
});

// Attendance routes
$router->get('/attendance/today', 'AttendanceController@getTodayAttendance');

// API Routes
$router->post('/api/employees', function ($request, $response) {
    $data = $request->getParsedBody();
    
    // Validate required fields
    $requiredFields = [
        'name', 'email', 'phone', 'employeeCode', 
        'departmentId', 'positionId', 'hireDate',
        'contractType', 'baseSalary', 'contractStartDate'
    ];
    
    foreach ($requiredFields as $field) {
        if (empty($data[$field])) {
            return $response->withJson([
                'success' => false,
                'message' => "Trường $field là bắt buộc"
            ], 400);
        }
    }
    
    // Validate email format
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        return $response->withJson([
            'success' => false,
            'message' => 'Email không hợp lệ'
        ], 400);
    }
    
    // Validate phone number format
    if (!preg_match('/^[0-9]{10,11}$/', $data['phone'])) {
        return $response->withJson([
            'success' => false,
            'message' => 'Số điện thoại không hợp lệ'
        ], 400);
    }
    
    $employeeController = new EmployeeController();
    $result = $employeeController->addEmployee($data);
    
    if ($result['success']) {
        return $response->withJson($result, 201);
    } else {
        return $response->withJson($result, 500);
    }
});

// API để lấy danh sách phòng ban
$router->get('/api/departments', function($request, $response) {
    $departmentController = new DepartmentController();
    $departments = $departmentController->getDepartments();
    return $response->withJson([
        'success' => true,
        'data' => $departments
    ]);
});

// API để lấy danh sách chức vụ
$router->get('/api/positions', function($request, $response) {
    global $db;
    $positionController = new PositionController($db);
    $result = $positionController->getAll();
    return $response->withJson($result);
});

// API để lấy mã nhân viên mới
$router->get('/api/employee-code', function($request, $response) {
    $employeeController = new EmployeeController();
    $code = $employeeController->generateEmployeeCode();
    return $response->withJson(['success' => true, 'data' => $code]);
}); 