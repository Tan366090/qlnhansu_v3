<?php

class NotificationHelper {
    private static $messages;
    private static $config;

    public static function init() {
        self::$messages = require __DIR__ . '/messages.php';
        self::$config = require __DIR__ . '/config.php';
    }

    public static function getMessage($category, $action, $type = 'success') {
        if (!self::$messages) {
            self::init();
        }

        return self::$messages[$category][$action][$type] ?? 
               self::$messages['general'][$type] ?? 
               'Thông báo không xác định';
    }

    public static function notify($category, $action, $type = 'success', $context = []) {
        $message = self::getMessage($category, $action, $type);
        $notification = Notification::getInstance();

        // Thêm thông tin ngữ cảnh
        $context = array_merge($context, [
            'user_id' => $_SESSION['user_id'] ?? 'guest',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'url' => $_SERVER['REQUEST_URI'] ?? 'unknown',
            'timestamp' => date('Y-m-d H:i:s')
        ]);

        switch ($type) {
            case 'success':
                $notification->success($message, $context);
                break;
            case 'error':
                $notification->error($message, $context);
                break;
            case 'warning':
                $notification->warning($message, $context);
                break;
            case 'info':
                $notification->info($message, $context);
                break;
        }

        // Ghi log thông báo nếu được cấu hình
        if (self::$config['log_notifications']) {
            self::logNotification($message, $type, $context);
        }

        return $notification;
    }

    public static function notifySuccess($category, $action, $context = []) {
        return self::notify($category, $action, 'success', $context);
    }

    public static function notifyError($category, $action, $context = []) {
        return self::notify($category, $action, 'error', $context);
    }

    public static function notifyWarning($category, $action, $context = []) {
        return self::notify($category, $action, 'warning', $context);
    }

    public static function notifyInfo($category, $action, $context = []) {
        return self::notify($category, $action, 'info', $context);
    }

    // Các phương thức thông báo cụ thể
    public static function notifyLoginSuccess($username) {
        return self::notifySuccess('auth', 'login', ['username' => $username]);
    }

    public static function notifyLoginError($username) {
        return self::notifyError('auth', 'login', ['username' => $username]);
    }

    public static function notifyLogoutSuccess() {
        return self::notifySuccess('auth', 'logout');
    }

    public static function notifyCreateSuccess($entity, $id) {
        return self::notifySuccess('crud', 'create', ['entity' => $entity, 'id' => $id]);
    }

    public static function notifyUpdateSuccess($entity, $id) {
        return self::notifySuccess('crud', 'update', ['entity' => $entity, 'id' => $id]);
    }

    public static function notifyDeleteSuccess($entity, $id) {
        return self::notifySuccess('crud', 'delete', ['entity' => $entity, 'id' => $id]);
    }

    public static function notifyFileUploadSuccess($filename) {
        return self::notifySuccess('file', 'upload', ['filename' => $filename]);
    }

    public static function notifyFileUploadError($filename, $error) {
        return self::notifyError('file', 'upload', ['filename' => $filename, 'error' => $error]);
    }

    public static function notifySystemMaintenance($startTime, $endTime) {
        return self::notifyInfo('system', 'maintenance', [
            'start_time' => $startTime,
            'end_time' => $endTime
        ]);
    }

    private static function logNotification($message, $type, $context) {
        $logMessage = sprintf(
            "[%s] %s: %s %s\n",
            date('Y-m-d H:i:s'),
            strtoupper($type),
            $message,
            json_encode($context)
        );
        
        error_log($logMessage, 3, self::$config['log_file']);
    }
} 