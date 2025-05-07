<?php

return [
    'roles' => [
        'admin' => [
            'name' => 'Administrator',
            'permissions' => [
                'user.view',
                'user.create',
                'user.edit',
                'user.delete',
                'attendance.view',
                'attendance.create',
                'attendance.edit',
                'attendance.delete',
                'salary.view',
                'salary.create',
                'salary.edit',
                'salary.delete',
                'department.view',
                'department.create',
                'department.edit',
                'department.delete',
                'leave.view',
                'leave.create',
                'leave.edit',
                'leave.delete',
                'training.view',
                'training.create',
                'training.edit',
                'training.delete',
                'certificate.view',
                'certificate.create',
                'certificate.edit',
                'certificate.delete',
                'document.view',
                'document.create',
                'document.edit',
                'document.delete',
                'equipment.view',
                'equipment.create',
                'equipment.edit',
                'equipment.delete'
            ]
        ],
        'manager' => [
            'name' => 'Manager',
            'permissions' => [
                'leave.view',
                'leave.create',
                'leave.edit',
                'training.view',
                'training.create',
                'equipment.view',
                'equipment.create',
                'equipment.edit'
            ]
        ],
        'hr' => [
            'name' => 'HR Staff',
            'permissions' => [
                'user.view',
                'user.create',
                'user.edit',
                'attendance.view',
                'attendance.create',
                'attendance.edit',
                'salary.view',
                'salary.create',
                'salary.edit',
                'leave.view',
                'leave.create',
                'leave.edit',
                'training.view',
                'training.create',
                'training.edit',
                'certificate.view',
                'certificate.create',
                'certificate.edit',
                'document.view',
                'document.create',
                'document.edit'
            ]
        ],
        'employee' => [
            'name' => 'Employee',
            'permissions' => [
                'attendance.view',
                'attendance.create',
                'leave.view',
                'leave.create',
                'training.view',
                'training.create'
            ]
        ]
    ]
]; 