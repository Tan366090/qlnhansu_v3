<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Models\User;
use App\Models\Department;
use App\Models\Attendance;
use App\Models\Leave;
use App\Models\Training;
use App\Models\Certificate;
use App\Models\Document;
use App\Models\Equipment;

class DashboardController extends Controller
{
    public function admin(Request $request, Response $response)
    {
        $user = $request->getUser();
        if (!$user || $user['role'] !== 'admin') {
            return $response->status(403)->json([
                'success' => false,
                'message' => 'Access denied'
            ]);
        }

        $data = [
            'overview' => [
                'total_employees' => User::count(),
                'total_departments' => Department::count(),
                'total_equipment' => Equipment::count(),
                'total_documents' => Document::count()
            ],
            'attendance' => [
                'today' => Attendance::whereDate('date', date('Y-m-d'))->count(),
                'late' => Attendance::whereDate('date', date('Y-m-d'))
                    ->where('status', 'late')
                    ->count()
            ],
            'leaves' => [
                'pending' => Leave::where('status', 'pending')->count(),
                'approved' => Leave::where('status', 'approved')
                    ->whereMonth('start_date', date('m'))
                    ->count()
            ],
            'certificates' => [
                'expiring' => Certificate::where('expiry_date', '<=', date('Y-m-d', strtotime('+30 days')))
                    ->where('expiry_date', '>', date('Y-m-d'))
                    ->count(),
                'expired' => Certificate::where('expiry_date', '<', date('Y-m-d'))->count()
            ],
            'equipment' => [
                'maintenance' => Equipment::where('status', 'maintenance')->count(),
                'assigned' => Equipment::where('status', 'assigned')->count()
            ],
            'documents' => [
                'by_type' => Document::select('file_type', \DB::raw('count(*) as total'))
                    ->groupBy('file_type')
                    ->get()
            ]
        ];

        return $response->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function manager(Request $request, Response $response)
    {
        $user = $request->getUser();
        if (!$user || $user['role'] !== 'manager') {
            return $response->status(403)->json([
                'success' => false,
                'message' => 'Access denied'
            ]);
        }

        $departmentId = $user['department_id'];
        $data = [
            'department' => [
                'total_employees' => User::where('department_id', $departmentId)->count(),
                'attendance_today' => Attendance::whereHas('user', function($query) use ($departmentId) {
                    $query->where('department_id', $departmentId);
                })->whereDate('date', date('Y-m-d'))->count()
            ],
            'leaves' => [
                'pending' => Leave::whereHas('user', function($query) use ($departmentId) {
                    $query->where('department_id', $departmentId);
                })->where('status', 'pending')->count()
            ],
            'trainings' => [
                'upcoming' => Training::where('department_id', $departmentId)
                    ->where('start_date', '>', date('Y-m-d'))
                    ->count(),
                'ongoing' => Training::where('department_id', $departmentId)
                    ->where('start_date', '<=', date('Y-m-d'))
                    ->where('end_date', '>=', date('Y-m-d'))
                    ->count()
            ],
            'equipment' => [
                'total' => Equipment::where('location', $departmentId)->count(),
                'maintenance' => Equipment::where('location', $departmentId)
                    ->where('status', 'maintenance')
                    ->count()
            ]
        ];

        return $response->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function hr(Request $request, Response $response)
    {
        $user = $request->getUser();
        if (!$user || $user['role'] !== 'hr') {
            return $response->status(403)->json([
                'success' => false,
                'message' => 'Access denied'
            ]);
        }

        $data = [
            'employees' => [
                'total' => User::count(),
                'by_department' => User::select('department_id', \DB::raw('count(*) as total'))
                    ->groupBy('department_id')
                    ->get()
            ],
            'attendance' => [
                'today' => Attendance::whereDate('date', date('Y-m-d'))->count(),
                'late' => Attendance::whereDate('date', date('Y-m-d'))
                    ->where('status', 'late')
                    ->count()
            ],
            'leaves' => [
                'pending' => Leave::where('status', 'pending')->count(),
                'approved' => Leave::where('status', 'approved')
                    ->whereMonth('start_date', date('m'))
                    ->count()
            ],
            'contracts' => [
                'expiring' => User::where('contract_end_date', '<=', date('Y-m-d', strtotime('+30 days')))
                    ->where('contract_end_date', '>', date('Y-m-d'))
                    ->count()
            ],
            'trainings' => [
                'upcoming' => Training::where('start_date', '>', date('Y-m-d'))->count(),
                'ongoing' => Training::where('start_date', '<=', date('Y-m-d'))
                    ->where('end_date', '>=', date('Y-m-d'))
                    ->count()
            ],
            'documents' => [
                'hr_documents' => Document::where('category', 'hr')->count()
            ]
        ];

        return $response->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function employee(Request $request, Response $response)
    {
        $user = $request->getUser();
        if (!$user) {
            return $response->status(401)->json([
                'success' => false,
                'message' => 'Unauthorized'
            ]);
        }

        $data = [
            'profile' => [
                'name' => $user['name'],
                'department' => $user['department']['name'],
                'position' => $user['position']
            ],
            'attendance' => [
                'today' => Attendance::where('user_id', $user['id'])
                    ->whereDate('date', date('Y-m-d'))
                    ->first(),
                'monthly' => Attendance::where('user_id', $user['id'])
                    ->whereMonth('date', date('m'))
                    ->count()
            ],
            'leaves' => [
                'balance' => $user['leave_balance'],
                'pending' => Leave::where('user_id', $user['id'])
                    ->where('status', 'pending')
                    ->count()
            ],
            'trainings' => [
                'upcoming' => Training::whereHas('participants', function($query) use ($user) {
                    $query->where('user_id', $user['id']);
                })->where('start_date', '>', date('Y-m-d'))->count(),
                'completed' => Training::whereHas('participants', function($query) use ($user) {
                    $query->where('user_id', $user['id']);
                })->where('end_date', '<', date('Y-m-d'))->count()
            ],
            'certificates' => [
                'total' => Certificate::where('user_id', $user['id'])->count(),
                'expiring' => Certificate::where('user_id', $user['id'])
                    ->where('expiry_date', '<=', date('Y-m-d', strtotime('+30 days')))
                    ->where('expiry_date', '>', date('Y-m-d'))
                    ->count()
            ],
            'equipment' => [
                'assigned' => Equipment::where('assigned_to', $user['id'])->count()
            ]
        ];

        return $response->json([
            'success' => true,
            'data' => $data
        ]);
    }
} 