<?php
namespace App\Utils;

class ResponseHandler {
    public static function sendSuccess($data = [], $message = 'Success', $statusCode = 200) {
        http_response_code($statusCode);
        echo json_encode([
            'success' => true,
            'message' => $message,
            'data' => $data
        ]);
        exit();
    }
    
    public static function sendError($message, $statusCode = 400, $errors = []) {
        http_response_code($statusCode);
        echo json_encode([
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ]);
        exit();
    }
    
    public static function sendValidationError($errors) {
        self::sendError('Validation failed', 422, $errors);
    }
    
    public static function sendNotFound($message = 'Resource not found') {
        self::sendError($message, 404);
    }
    
    public static function sendUnauthorized($message = 'Unauthorized') {
        self::sendError($message, 401);
    }
    
    public static function sendForbidden($message = 'Forbidden') {
        self::sendError($message, 403);
    }
    
    public static function sendServerError($message = 'Internal server error') {
        self::sendError($message, 500);
    }
} 