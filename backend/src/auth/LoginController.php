<?php

class LoginController {
    public function showLoginForm() {
        // Kiểm tra nếu đã đăng nhập
        if (isset($_SESSION['user_id'])) {
            NotificationHelper::notifyInfo('auth', 'already_logged_in');
            header('Location: /dashboard');
            exit;
        }
        
        require_once __DIR__ . '/../views/auth/login.php';
    }
    
    public function login() {
        header('Content-Type: application/json');
        try {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            
            if (empty($username) || empty($password)) {
                throw new Exception('Vui lòng nhập đầy đủ thông tin');
            }
            
            $user = User::findByUsername($username);
            
            if (!$user) {
                throw new Exception('Tài khoản không tồn tại');
            }
            
            // Kiểm tra mật khẩu
            $isValid = false;
            
            // Kiểm tra nếu là bcrypt hash
            if (password_get_info($user->password_hash)['algo'] !== null) {
                $isValid = password_verify($password, $user->password_hash);
            } 
            // Kiểm tra nếu là MD5 hash
            else if (strlen($user->password_hash) === 32) {
                $isValid = (md5($password) === $user->password_hash);
            }
            // Kiểm tra plain text (cho admin mặc định)
            else if ($user->password_hash === 'admin123' && $username === 'admin') {
                $isValid = ($password === 'admin123');
            }
            
            if (!$isValid) {
                throw new Exception('Mật khẩu không chính xác');
            }
            
            $_SESSION['user_id'] = $user->id;
            $_SESSION['username'] = $user->username;
            $_SESSION['role'] = $user->role_id;
            
            echo json_encode([
                'success' => true,
                'message' => 'Đăng nhập thành công',
                'redirect' => '/dashboard'
            ]);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    public function logout() {
        try {
            // Bắt đầu loading
            LoadingHelper::startLoading('Đang đăng xuất...');
            
            // Ghi log đăng xuất
            if (isset($_SESSION['user_id'])) {
                ActivityLog::log('logout', [
                    'user_id' => $_SESSION['user_id'],
                    'ip' => $_SERVER['REMOTE_ADDR']
                ]);
            }
            
            // Xóa session
            session_destroy();
            
            // Thông báo thành công
            NotificationHelper::notifyLogoutSuccess();
            
            // Kết thúc loading
            LoadingHelper::endLoading();
            
            // Chuyển hướng
            header('Location: /login');
            exit;
            
        } catch (Exception $e) {
            // Kết thúc loading
            LoadingHelper::endLoading();
            
            // Xử lý lỗi
            ErrorHelper::handleError(500, 'Lỗi đăng xuất', $e->getMessage());
        }
    }
} 