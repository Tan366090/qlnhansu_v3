<?php
return [
    // Auth routes
    [
        'method' => 'POST',
        'path' => '/auth/login',
        'controller' => 'App\\Controllers\\AuthController',
        'method' => 'login'
    ],
    [
        'method' => 'POST',
        'path' => '/auth/logout',
        'controller' => 'App\\Controllers\\AuthController',
        'method' => 'logout'
    ],
    
    // Employee routes
    [
        'method' => 'GET',
        'path' => '/employees',
        'controller' => 'App\\Controllers\\EmployeeController',
        'method' => 'index'
    ],
    [
        'method' => 'POST',
        'path' => '/employees',
        'controller' => 'App\\Controllers\\EmployeeController',
        'method' => 'store'
    ],
    
    // Department routes
    [
        'method' => 'GET',
        'path' => '/departments',
        'controller' => 'App\\Controllers\\DepartmentController',
        'method' => 'index'
    ],
    
    // Position routes
    [
        'method' => 'GET',
        'path' => '/positions',
        'controller' => 'App\\Controllers\\PositionController',
        'method' => 'index'
    ],
    
    // Attendance routes
    [
        'method' => 'POST',
        'path' => '/attendance/check-in',
        'controller' => 'App\\Controllers\\AttendanceController',
        'method' => 'checkIn'
    ],
    [
        'method' => 'POST',
        'path' => '/attendance/check-out',
        'controller' => 'App\\Controllers\\AttendanceController',
        'method' => 'checkOut'
    ],
    
    // Salary routes
    [
        'method' => 'GET',
        'path' => '/salaries',
        'controller' => 'App\\Controllers\\SalaryController',
        'method' => 'index'
    ],
    
    // Document routes
    [
        'method' => 'GET',
        'path' => '/documents',
        'controller' => 'App\\Controllers\\DocumentController',
        'method' => 'index'
    ]
]; 