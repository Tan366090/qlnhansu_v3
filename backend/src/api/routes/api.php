<?php
function routeRequest($method, $endpoint, $data) {
    // Define routes
    $routes = [
        // Authentication routes
        'auth/login' => [
            'POST' => 'AuthController@login'
        ],
        
        // Employee routes
        'employees/list' => [
            'GET' => 'EmployeeController@list'
        ],
        'employees/add' => [
            'POST' => 'EmployeeController@add'
        ],
        'employees/edit' => [
            'PUT' => 'EmployeeController@edit'
        ],
        'employees/delete' => [
            'DELETE' => 'EmployeeController@delete'
        ],
        'employees/detail' => [
            'GET' => 'EmployeeController@detail'
        ],
        
        // Attendance routes
        'attendance/list' => [
            'GET' => 'AttendanceController@list'
        ],
        'attendance/check' => [
            'POST' => 'AttendanceController@check'
        ],
        'attendance/history' => [
            'GET' => 'AttendanceController@history'
        ],
        'attendance/statistics' => [
            'GET' => 'AttendanceController@statistics'
        ],
        
        // Salary routes
        'salary/list' => [
            'GET' => 'SalaryController@list'
        ],
        'salary/calculate' => [
            'POST' => 'SalaryController@calculate'
        ],
        'salary/history' => [
            'GET' => 'SalaryController@history'
        ],
        'salary/export' => [
            'GET' => 'SalaryController@export'
        ],
        
        // Department routes
        'departments/list' => [
            'GET' => 'DepartmentController@list'
        ],
        'departments/add' => [
            'POST' => 'DepartmentController@add'
        ],
        'departments/edit' => [
            'PUT' => 'DepartmentController@edit'
        ],
        'departments/delete' => [
            'DELETE' => 'DepartmentController@delete'
        ],
        
        // Leave routes
        'leave/list' => [
            'GET' => 'LeaveController@list'
        ],
        'leave/register' => [
            'POST' => 'LeaveController@register'
        ],
        'leave/approve' => [
            'PUT' => 'LeaveController@approve'
        ],
        'leave/reject' => [
            'PUT' => 'LeaveController@reject'
        ],
        'leave/history' => [
            'GET' => 'LeaveController@history'
        ],
        
        // Training routes
        'training/courses' => [
            'GET' => 'TrainingController@courses'
        ],
        'training/register' => [
            'POST' => 'TrainingController@register'
        ],
        'training/attendance' => [
            'POST' => 'TrainingController@attendance'
        ],
        'training/history' => [
            'GET' => 'TrainingController@history'
        ],
        
        // Certificate routes
        'certificates/list' => [
            'GET' => 'CertificateController@list'
        ],
        'certificates/add' => [
            'POST' => 'CertificateController@add'
        ],
        'certificates/edit' => [
            'PUT' => 'CertificateController@edit'
        ],
        'certificates/delete' => [
            'DELETE' => 'CertificateController@delete'
        ],
        
        // Document routes
        'documents/list' => [
            'GET' => 'DocumentController@list'
        ],
        'documents/upload' => [
            'POST' => 'DocumentController@upload'
        ],
        'documents/download' => [
            'GET' => 'DocumentController@download'
        ],
        'documents/delete' => [
            'DELETE' => 'DocumentController@delete'
        ],
        
        // Equipment routes
        'equipment/list' => [
            'GET' => 'EquipmentController@list'
        ],
        'equipment/assign' => [
            'POST' => 'EquipmentController@assign'
        ],
        'equipment/return' => [
            'POST' => 'EquipmentController@return'
        ],
        'equipment/history' => [
            'GET' => 'EquipmentController@history'
        ]
    ];

    // Check if route exists
    if (!isset($routes[$endpoint])) {
        http_response_code(404);
        return [
            'success' => false,
            'message' => 'API endpoint not found'
        ];
    }

    // Check if method is allowed
    if (!isset($routes[$endpoint][$method])) {
        http_response_code(405);
        return [
            'success' => false,
            'message' => 'Method not allowed'
        ];
    }

    // Get controller and method
    $controllerMethod = $routes[$endpoint][$method];
    list($controller, $action) = explode('@', $controllerMethod);

    // Load controller
    $controllerFile = __DIR__ . '/../controllers/' . $controller . '.php';
    if (!file_exists($controllerFile)) {
        http_response_code(500);
        return [
            'success' => false,
            'message' => 'Controller not found'
        ];
    }

    require_once $controllerFile;
    $controllerInstance = new $controller();
    
    // Call controller method
    return $controllerInstance->$action($data);
}
?> 