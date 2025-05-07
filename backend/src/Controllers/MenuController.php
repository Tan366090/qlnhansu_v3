<?php

class MenuController {
    private $db;
    private $userRole;

    public function __construct($db, $userRole) {
        $this->db = $db;
        $this->userRole = $userRole;
    }

    public function getMenuItems() {
        try {
            // Get user permissions
            $permissions = $this->getUserPermissions();
            
            // Base menu structure
            $menu = [
                [
                    'id' => 'dashboard',
                    'title' => 'Dashboard',
                    'icon' => 'fas fa-tachometer-alt',
                    'url' => '/admin/dashboard.html',
                    'submenu' => []
                ],
                [
                    'id' => 'employees',
                    'title' => 'Nhân viên',
                    'icon' => 'fas fa-users',
                    'url' => '#',
                    'submenu' => [
                        [
                            'title' => 'Danh sách nhân viên',
                            'icon' => 'fas fa-list',
                            'url' => '/admin/employees/list.html'
                        ],
                        [
                            'title' => 'Thêm nhân viên',
                            'icon' => 'fas fa-plus',
                            'url' => '/admin/employees/add.html'
                        ],
                        [
                            'title' => 'Chỉnh sửa hồ sơ',
                            'icon' => 'fas fa-edit',
                            'url' => '/admin/employees/edit.html'
                        ]
                    ]
                ],
                [
                    'id' => 'attendance',
                    'title' => 'Chấm công',
                    'icon' => 'fas fa-clock',
                    'url' => '#',
                    'submenu' => [
                        [
                            'title' => 'Lịch sử chấm công',
                            'icon' => 'fas fa-history',
                            'url' => '/admin/attendance/history.html'
                        ],
                        [
                            'title' => 'Chấm công',
                            'icon' => 'fas fa-check',
                            'url' => '/admin/attendance/check.html'
                        ]
                    ]
                ],
                [
                    'id' => 'salary',
                    'title' => 'Lương',
                    'icon' => 'fas fa-money-bill-wave',
                    'url' => '#',
                    'submenu' => [
                        [
                            'title' => 'Bảng lương',
                            'icon' => 'fas fa-list',
                            'url' => '/admin/salary/list.html'
                        ],
                        [
                            'title' => 'Lịch sử lương',
                            'icon' => 'fas fa-history',
                            'url' => '/admin/salary/history.html'
                        ]
                    ]
                ],
                [
                    'id' => 'departments',
                    'title' => 'Phòng ban',
                    'icon' => 'fas fa-building',
                    'url' => '#',
                    'submenu' => [
                        [
                            'title' => 'Danh sách phòng ban',
                            'icon' => 'fas fa-list',
                            'url' => '/admin/departments/list.html'
                        ],
                        [
                            'title' => 'Vị trí công việc',
                            'icon' => 'fas fa-briefcase',
                            'url' => '/admin/positions/list.html'
                        ]
                    ]
                ],
                [
                    'id' => 'leave',
                    'title' => 'Nghỉ phép',
                    'icon' => 'fas fa-calendar-alt',
                    'url' => '#',
                    'submenu' => [
                        [
                            'title' => 'Đăng ký nghỉ phép',
                            'icon' => 'fas fa-plus',
                            'url' => '/admin/leave/register.html'
                        ],
                        [
                            'title' => 'Danh sách nghỉ phép',
                            'icon' => 'fas fa-list',
                            'url' => '/admin/leave/list.html'
                        ]
                    ]
                ],
                [
                    'id' => 'training',
                    'title' => 'Đào tạo',
                    'icon' => 'fas fa-graduation-cap',
                    'url' => '#',
                    'submenu' => [
                        [
                            'title' => 'Khóa đào tạo',
                            'icon' => 'fas fa-list',
                            'url' => '/admin/training/courses.html'
                        ],
                        [
                            'title' => 'Đăng ký đào tạo',
                            'icon' => 'fas fa-plus',
                            'url' => '/admin/training/register.html'
                        ]
                    ]
                ],
                [
                    'id' => 'certificates',
                    'title' => 'Bằng cấp',
                    'icon' => 'fas fa-certificate',
                    'url' => '#',
                    'submenu' => [
                        [
                            'title' => 'Danh sách bằng cấp',
                            'icon' => 'fas fa-list',
                            'url' => '/admin/certificates/list.html'
                        ],
                        [
                            'title' => 'Thêm bằng cấp',
                            'icon' => 'fas fa-plus',
                            'url' => '/admin/certificates/add.html'
                        ]
                    ]
                ],
                [
                    'id' => 'documents',
                    'title' => 'Tài liệu',
                    'icon' => 'fas fa-file-alt',
                    'url' => '#',
                    'submenu' => [
                        [
                            'title' => 'Danh sách tài liệu',
                            'icon' => 'fas fa-list',
                            'url' => '/admin/documents/list.html'
                        ],
                        [
                            'title' => 'Upload tài liệu',
                            'icon' => 'fas fa-upload',
                            'url' => '/admin/documents/upload.html'
                        ]
                    ]
                ],
                [
                    'id' => 'equipment',
                    'title' => 'Thiết bị',
                    'icon' => 'fas fa-tools',
                    'url' => '#',
                    'submenu' => [
                        [
                            'title' => 'Danh sách thiết bị',
                            'icon' => 'fas fa-list',
                            'url' => '/admin/equipment/list.html'
                        ],
                        [
                            'title' => 'Cấp phát thiết bị',
                            'icon' => 'fas fa-share',
                            'url' => '/admin/equipment/assign.html'
                        ]
                    ]
                ],
                [
                    'id' => 'performance',
                    'title' => 'Hiệu suất & KPI',
                    'icon' => 'fas fa-chart-line',
                    'url' => '#',
                    'submenu' => [
                        [
                            'title' => 'Đánh giá hiệu suất',
                            'icon' => 'fas fa-list',
                            'url' => '/admin/performance/list.html'
                        ],
                        [
                            'title' => 'Theo dõi KPI',
                            'icon' => 'fas fa-bullseye',
                            'url' => '/admin/kpi/tracking.html'
                        ],
                        [
                            'title' => 'Quản lý mục tiêu',
                            'icon' => 'fas fa-flag',
                            'url' => '/admin/goals/manage.html'
                        ]
                    ]
                ],
                [
                    'id' => 'recruitment',
                    'title' => 'Tuyển dụng',
                    'icon' => 'fas fa-user-plus',
                    'url' => '#',
                    'submenu' => [
                        [
                            'title' => 'Vị trí tuyển dụng',
                            'icon' => 'fas fa-briefcase',
                            'url' => '/admin/recruitment/positions.html'
                        ],
                        [
                            'title' => 'Quản lý ứng viên',
                            'icon' => 'fas fa-users',
                            'url' => '/admin/recruitment/candidates.html'
                        ],
                        [
                            'title' => 'Lịch phỏng vấn',
                            'icon' => 'fas fa-comments',
                            'url' => '/admin/recruitment/interviews.html'
                        ],
                        [
                            'title' => 'Onboarding',
                            'icon' => 'fas fa-user-check',
                            'url' => '/admin/recruitment/onboarding.html'
                        ]
                    ]
                ],
                [
                    'id' => 'benefits',
                    'title' => 'Phúc lợi',
                    'icon' => 'fas fa-gift',
                    'url' => '#',
                    'submenu' => [
                        [
                            'title' => 'Bảo hiểm',
                            'icon' => 'fas fa-shield-alt',
                            'url' => '/admin/benefits/insurance.html'
                        ],
                        [
                            'title' => 'Chính sách phúc lợi',
                            'icon' => 'fas fa-file-contract',
                            'url' => '/admin/benefits/policies.html'
                        ]
                    ]
                ],
                [
                    'id' => 'projects',
                    'title' => 'Dự án',
                    'icon' => 'fas fa-project-diagram',
                    'url' => '#',
                    'submenu' => [
                        [
                            'title' => 'Danh sách dự án',
                            'icon' => 'fas fa-list',
                            'url' => '/admin/projects/list.html'
                        ],
                        [
                            'title' => 'Quản lý công việc',
                            'icon' => 'fas fa-tasks',
                            'url' => '/admin/projects/tasks.html'
                        ],
                        [
                            'title' => 'Quản lý tài nguyên',
                            'icon' => 'fas fa-cubes',
                            'url' => '/admin/projects/resources.html'
                        ]
                    ]
                ],
                [
                    'id' => 'settings',
                    'title' => 'Cài đặt',
                    'icon' => 'fas fa-cogs',
                    'url' => '#',
                    'submenu' => [
                        [
                            'title' => 'Bảo mật',
                            'icon' => 'fas fa-shield-alt',
                            'url' => '/admin/settings/security.html'
                        ],
                        [
                            'title' => 'Tích hợp',
                            'icon' => 'fas fa-plug',
                            'url' => '/admin/settings/integrations.html'
                        ],
                        [
                            'title' => 'Sao lưu',
                            'icon' => 'fas fa-database',
                            'url' => '/admin/settings/backup.html'
                        ]
                    ]
                ]
            ];

            // Filter menu based on permissions
            $filteredMenu = $this->filterMenuByPermissions($menu, $permissions);

            return [
                'success' => true,
                'data' => $filteredMenu
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    private function getUserPermissions() {
        try {
            $stmt = $this->db->prepare("
                SELECT p.permission_name 
                FROM role_permissions rp
                JOIN permissions p ON rp.permission_id = p.id
                WHERE rp.role_id = ?
            ");
            
            $stmt->execute([$this->userRole]);
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (Exception $e) {
            return [];
        }
    }

    private function filterMenuByPermissions($menu, $permissions) {
        return array_filter($menu, function($item) use ($permissions) {
            // Check if user has permission for this menu item
            $hasPermission = in_array('view_' . $item['id'], $permissions);
            
            if (isset($item['submenu']) && !empty($item['submenu'])) {
                // Filter submenu items
                $item['submenu'] = array_filter($item['submenu'], function($subitem) use ($permissions) {
                    $submenuPermission = 'view_' . str_replace('/', '_', trim($subitem['url'], '/'));
                    return in_array($submenuPermission, $permissions);
                });
                
                // Only keep menu item if it has visible submenu items
                return !empty($item['submenu']);
            }
            
            return $hasPermission;
        });
    }
} 