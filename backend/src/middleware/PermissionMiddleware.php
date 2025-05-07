<?php

namespace App\Middleware;

use App\Config\Permissions;
use App\Core\Response;

class PermissionMiddleware
{
    public static function check($permission)
    {
        return function ($request, $response, $next) use ($permission) {
            $user = $request->getUser();
            
            if (!$user) {
                return $response->status(401)->json([
                    'success' => false,
                    'message' => 'Authentication required'
                ]);
            }

            // Admin có quyền truy cập tất cả
            if ($user['role'] === 'admin') {
                return $next($request, $response);
            }

            // Kiểm tra quyền của user
            $permissions = Permissions::getRolePermissions($user['role']);
            if (!in_array($permission, $permissions)) {
                return $response->status(403)->json([
                    'success' => false,
                    'message' => 'Insufficient permissions'
                ]);
            }

            return $next($request, $response);
        };
    }

    public static function checkAny($permissions)
    {
        return function ($request, $response, $next) use ($permissions) {
            $user = $request->getUser();
            
            if (!$user) {
                return $response->status(401)->json([
                    'success' => false,
                    'message' => 'Authentication required'
                ]);
            }

            // Admin có quyền truy cập tất cả
            if ($user['role'] === 'admin') {
                return $next($request, $response);
            }

            // Kiểm tra quyền của user
            $userPermissions = Permissions::getRolePermissions($user['role']);
            $hasPermission = false;
            
            foreach ($permissions as $permission) {
                if (in_array($permission, $userPermissions)) {
                    $hasPermission = true;
                    break;
                }
            }

            if (!$hasPermission) {
                return $response->status(403)->json([
                    'success' => false,
                    'message' => 'Insufficient permissions'
                ]);
            }

            return $next($request, $response);
        };
    }
} 