<?php
namespace App\Middleware;

use App\Services\JwtService;

class AuthMiddleware {
    private $jwtService;

    public function __construct() {
        $this->jwtService = new JwtService();
    }

    public function handle() {
        $token = $this->getBearerToken();
        
        if (!$token) {
            return false;
        }

        try {
            $decoded = $this->jwtService->validateToken($token);
            return $decoded;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function getBearerToken() {
        $headers = getallheaders();
        
        if (!isset($headers['Authorization'])) {
            return null;
        }

        if (preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $matches)) {
            return $matches[1];
        }

        return null;
    }

    public function requireRole($requiredRole) {
        if (!$this->handle()) {
            return false;
        }

        $user = $this->sessionManager->getCurrentUser();
        return $user && $user['role'] === $requiredRole;
    }
} 