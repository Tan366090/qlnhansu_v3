<?php
require_once __DIR__ . '/../backend/src/config/Database.php';

class TestDataSetup {
    private $db;
    private $testUsers = [
        [
            'user_id' => 1,
            'username' => 'admin',
            'password' => 'admin123',
            'role_id' => 1, // 1 = admin
            'email' => 'admin@example.com',
            'is_active' => 1
        ],
        [
            'user_id' => 2,
            'username' => 'manager',
            'password' => 'manager123',
            'role_id' => 2, // 2 = manager
            'email' => 'manager@example.com',
            'is_active' => 1
        ],
        [
            'user_id' => 3,
            'username' => 'employee',
            'password' => 'employee123',
            'role_id' => 3, // 3 = employee
            'email' => 'employee@example.com',
            'is_active' => 1
        ],
        [
            'user_id' => 4,
            'username' => 'hr',
            'password' => 'hr123',
            'role_id' => 4, // 4 = hr
            'email' => 'hr@example.com',
            'is_active' => 1
        ]
    ];

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function setup() {
        try {
            // Bắt đầu transaction
            $this->db->beginTransaction();

            // Tắt kiểm tra khóa ngoại tạm thời
            $this->db->exec("SET FOREIGN_KEY_CHECKS = 0");

            // Xóa dữ liệu cũ từ các bảng liên quan
            $this->db->exec("DELETE FROM attendance WHERE user_id IN (1, 2, 3, 4)");
            $this->db->exec("DELETE FROM users WHERE user_id IN (1, 2, 3, 4)");

            // Bật lại kiểm tra khóa ngoại
            $this->db->exec("SET FOREIGN_KEY_CHECKS = 1");

            // Thêm dữ liệu test
            foreach ($this->testUsers as $user) {
                $stmt = $this->db->prepare("
                    INSERT INTO users (user_id, username, password_hash, role_id, email, is_active)
                    VALUES (:user_id, :username, :password, :role_id, :email, :is_active)
                ");

                $stmt->execute([
                    ':user_id' => $user['user_id'],
                    ':username' => $user['username'],
                    ':password' => password_hash($user['password'], PASSWORD_DEFAULT), // Hash mật khẩu
                    ':role_id' => $user['role_id'],
                    ':email' => $user['email'],
                    ':is_active' => $user['is_active']
                ]);
            }

            // Commit transaction
            $this->db->commit();
            echo "<div style='color: green;'>✓ Dữ liệu test đã được thêm thành công</div>";

        } catch (Exception $e) {
            // Rollback nếu có lỗi
            $this->db->rollBack();
            echo "<div style='color: red;'>✗ Lỗi: " . $e->getMessage() . "</div>";
        }
    }
}

// Chạy setup
echo "<h2>Thiết lập dữ liệu test</h2>";
$setup = new TestDataSetup();
$setup->setup();

// Thêm link để chạy test
echo "<br><a href='test_session_timeout.php' style='display: inline-block; padding: 10px 20px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 5px;'>Chạy Test Session</a>";
?> 