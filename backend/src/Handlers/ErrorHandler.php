<?php
namespace App\Handlers;

class ErrorHandler {
    public static function handle($error, $statusCode = 500) {
        $response = [
            'success' => false,
            'message' => 'An error occurred',
            'errors' => []
        ];

        if ($error instanceof \Exception) {
            $response['message'] = $error->getMessage();
            $response['errors'] = [
                'code' => $error->getCode(),
                'file' => $error->getFile(),
                'line' => $error->getLine()
            ];
        } else if (is_array($error)) {
            $response['errors'] = $error;
        } else {
            $response['errors'] = ['message' => $error];
        }

        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

    public static function handleValidationError($errors) {
        self::handle([
            'message' => 'Validation failed',
            'errors' => $errors
        ], 422);
    }

    public static function handleNotFound($message = 'Resource not found') {
        self::handle($message, 404);
    }

    public static function handleUnauthorized($message = 'Unauthorized access') {
        self::handle($message, 401);
    }

    public static function handleForbidden($message = 'Forbidden access') {
        self::handle($message, 403);
    }
}
?> 