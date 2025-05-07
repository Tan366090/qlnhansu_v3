<?php

class CORSMiddleware {
    public static function handleRequest() {
        // Get the origin
        $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
        
        // List of allowed origins
        $allowed_origins = [
            'http://localhost',
            'http://localhost:3000',
            'http://127.0.0.1',
            'http://127.0.0.1:3000'
        ];
        
        // Check if the origin is allowed
        if (in_array($origin, $allowed_origins)) {
            header("Access-Control-Allow-Origin: " . $origin);
        } else {
            // If origin not in allowed list, default to localhost
            header("Access-Control-Allow-Origin: http://localhost");
        }
        
        // Cache the allowed origin for browsers
        header("Vary: Origin");
        
        // Allow credentials
        header("Access-Control-Allow-Credentials: true");
        
        // Allow specific headers
        header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
        
        // Allow specific methods
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
        
        // Cache preflight request for 1 day
        header("Access-Control-Max-Age: 86400");
        
        // Handle preflight requests
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit();
        }
    }
} 