<?php
return [
    'admin' => [
        'dashboard' => '/admin/dashboard.html',
        'permissions' => ['*'],
        'restricted_paths' => [],
        'menu_items' => [
            'dashboard' => 'Dashboard',
            'users' => 'Quản lý người dùng',
            'roles' => 'Quản lý vai trò',
            'departments' => 'Quản lý phòng ban',
            'reports' => 'Báo cáo',
            'system_logs' => 'Nhật ký hệ thống',
            'system_config' => 'Cấu hình hệ thống',
            'settings' => 'Cài đặt hệ thống'
        ]
    ],
    'manager' => [
        'dashboard' => '/manager/dashboard.html',
        'permissions' => [
            'view_employees',
            'manage_department',
            'view_reports',
            'approve_requests',
            'manage_projects',
            'evaluate_employees',
            'manage_kpi',
            'view_team_performance'
        ],
        'restricted_paths' => ['/admin/*'],
        'menu_items' => [
            'dashboard' => 'Dashboard',
            'employees' => 'Nhân viên',
            'departments' => 'Phòng ban',
            'projects' => 'Quản lý dự án',
            'evaluations' => 'Đánh giá nhân viên',
            'kpi' => 'Quản lý KPI',
            'reports' => 'Báo cáo',
            'team_performance' => 'Hiệu suất nhóm'
        ]
    ],
    'hr' => [
        'dashboard' => '/hr/dashboard.html',
        'permissions' => [
            'manage_employees',
            'view_hr_reports',
            'manage_recruitment',
            'manage_training',
            'manage_contracts',
            'manage_benefits',
            'manage_salary',
            'manage_evaluations',
            'manage_attendance'
        ],
        'restricted_paths' => ['/admin/*', '/manager/*'],
        'menu_items' => [
            'dashboard' => 'Dashboard',
            'employees' => 'Quản lý nhân sự',
            'recruitment' => 'Tuyển dụng',
            'training' => 'Đào tạo',
            'contracts' => 'Hợp đồng',
            'benefits' => 'Phúc lợi',
            'salary' => 'Lương thưởng',
            'evaluations' => 'Đánh giá nhân viên',
            'attendance' => 'Chấm công',
            'reports' => 'Báo cáo nhân sự'
        ]
    ],
    'employee' => [
        'dashboard' => '/employee/dashboard.html',
        'permissions' => [
            'view_profile',
            'update_profile',
            'view_payslip',
            'request_leave',
            'view_notifications',
            'register_training',
            'view_schedule',
            'view_performance',
            'view_benefits'
        ],
        'restricted_paths' => ['/admin/*', '/manager/*', '/hr/*'],
        'menu_items' => [
            'dashboard' => 'Dashboard',
            'profile' => 'Hồ sơ cá nhân',
            'payslip' => 'Bảng lương',
            'leave' => 'Nghỉ phép',
            'notifications' => 'Thông báo',
            'training' => 'Đào tạo',
            'schedule' => 'Lịch làm việc',
            'performance' => 'Đánh giá hiệu suất',
            'benefits' => 'Phúc lợi'
        ]
    ]
];
?> 