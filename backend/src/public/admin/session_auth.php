<?php
session_start();

class SessionAuth {
    public static function init() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function login($userData) {
        self::init();
        $_SESSION['user_id'] = $userData['id'];
        $_SESSION['username'] = $userData['username'];
        $_SESSION['email'] = $userData['email'];
        $_SESSION['role'] = $userData['role'];
        $_SESSION['last_activity'] = time();
        $_SESSION['is_logged_in'] = true;
    }

    public static function logout() {
        self::init();
        session_unset();
        session_destroy();
    }

    public static function isLoggedIn() {
        self::init();
        if (!isset($_SESSION['is_logged_in']) || !$_SESSION['is_logged_in']) {
            return false;
        }

        // Kiểm tra thời gian hoạt động cuối cùng (30 phút)
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
            self::logout();
            return false;
        }

        // Cập nhật thời gian hoạt động
        $_SESSION['last_activity'] = time();
        return true;
    }

    public static function getCurrentUser() {
        self::init();
        if (!self::isLoggedIn()) {
            return null;
        }
        return [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'email' => $_SESSION['email'],
            'role' => $_SESSION['role']
        ];
    }

    public static function requireLogin() {
        if (!self::isLoggedIn()) {
            header('Location: login.php');
            exit();
        }
    }

    public static function requireRole($role) {
        self::requireLogin();
        if ($_SESSION['role'] !== $role) {
            header('Location: unauthorized.php');
            exit();
        }
    }
}
?> 