<?php

class Notification {
    private static $instance = null;
    private $notifications = [];
    private $logger;

    private function __construct() {
        $this->logger = new Logger();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Notification();
        }
        return self::$instance;
    }

    public function success($message, $context = []) {
        $this->add('success', $message, $context);
    }

    public function error($message, $context = []) {
        $this->add('error', $message, $context);
    }

    public function warning($message, $context = []) {
        $this->add('warning', $message, $context);
    }

    public function info($message, $context = []) {
        $this->add('info', $message, $context);
    }

    private function add($type, $message, $context = []) {
        $notification = [
            'type' => $type,
            'message' => $message,
            'context' => $context,
            'timestamp' => time()
        ];

        $this->notifications[] = $notification;
        $this->logger->info("Notification: [$type] $message", $context);
    }

    public function getAll() {
        return $this->notifications;
    }

    public function clear() {
        $this->notifications = [];
    }

    public function hasNotifications() {
        return !empty($this->notifications);
    }

    public function getLast() {
        return end($this->notifications) ?: null;
    }

    public function getByType($type) {
        return array_filter($this->notifications, function($notification) use ($type) {
            return $notification['type'] === $type;
        });
    }
} 