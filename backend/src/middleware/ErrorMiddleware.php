<?php

class ErrorMiddleware {
    private $errorHandler;

    public function __construct() {
        $this->errorHandler = ErrorHandler::getInstance();
    }

    public function __invoke($request, $response, $next) {
        try {
            // Thiết lập error handler
            set_error_handler([$this->errorHandler, 'handleError']);
            set_exception_handler([$this->errorHandler, 'handleException']);
            
            // Thực thi middleware tiếp theo
            $response = $next($request, $response);
            
            // Khôi phục error handler mặc định
            restore_error_handler();
            restore_exception_handler();
            
            return $response;
        } catch (Exception $e) {
            // Xử lý exception
            $this->errorHandler->handleException($e);
            
            // Trả về response lỗi
            return $response->withStatus(500)
                           ->withHeader('Content-Type', 'application/json')
                           ->write(json_encode([
                               'error' => true,
                               'message' => 'An error occurred',
                               'details' => $e->getMessage()
                           ]));
        }
    }
} 