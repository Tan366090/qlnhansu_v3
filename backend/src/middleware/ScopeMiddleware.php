<?php

namespace App\Middleware;

use App\Core\Response;

class ScopeMiddleware
{
    public static function managerDepartment()
    {
        return function ($request, $response, $next) {
            $user = $request->getUser();
            
            if (!$user || $user['role'] !== 'manager') {
                return $next($request, $response);
            }

            // Lấy department_id từ request hoặc route parameters
            $departmentId = $request->get('department_id') ?? $request->getRouteParam('departmentId');
            
            if ($departmentId && $departmentId != $user['department_id']) {
                return $response->status(403)->json([
                    'success' => false,
                    'message' => 'Access denied: You can only manage resources in your department'
                ]);
            }

            return $next($request, $response);
        };
    }

    public static function hrNoDepartment()
    {
        return function ($request, $response, $next) {
            $user = $request->getUser();
            
            if (!$user || $user['role'] !== 'hr') {
                return $next($request, $response);
            }

            // Kiểm tra nếu request liên quan đến department
            $path = $request->getPath();
            if (strpos($path, '/departments') !== false) {
                return $response->status(403)->json([
                    'success' => false,
                    'message' => 'Access denied: HR staff cannot manage departments'
                ]);
            }

            return $next($request, $response);
        };
    }

    public static function employeeSelf()
    {
        return function ($request, $response, $next) {
            $user = $request->getUser();
            
            if (!$user || $user['role'] !== 'employee') {
                return $next($request, $response);
            }

            // Lấy user_id từ request hoặc route parameters
            $userId = $request->get('user_id') ?? $request->getRouteParam('userId');
            
            if ($userId && $userId != $user['id']) {
                return $response->status(403)->json([
                    'success' => false,
                    'message' => 'Access denied: You can only access your own resources'
                ]);
            }

            return $next($request, $response);
        };
    }
} 