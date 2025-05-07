<?php
namespace App\Handlers;

class Response {
    public static function success($data = [], $message = 'Success', $statusCode = 200) {
        $response = [
            'success' => true,
            'message' => $message,
            'data' => $data
        ];

        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

    public static function created($data = [], $message = 'Resource created successfully') {
        self::success($data, $message, 201);
    }

    public static function updated($data = [], $message = 'Resource updated successfully') {
        self::success($data, $message, 200);
    }

    public static function deleted($message = 'Resource deleted successfully') {
        self::success([], $message, 200);
    }

    public static function paginated($data, $total, $page, $perPage, $message = 'Success') {
        $response = [
            'success' => true,
            'message' => $message,
            'data' => $data,
            'pagination' => [
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => ceil($total / $perPage)
            ]
        ];

        http_response_code(200);
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
}
?> 