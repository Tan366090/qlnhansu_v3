<?php
namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\Config\Database;
use App\Utils\ResponseHandler;

class RecentActivitiesTest extends TestCase {
    private $db;
    private $conn;
    
    protected function setUp(): void {
        $this->db = Database::getInstance();
        $this->conn = $this->db->getConnection();
        
        // Create test data
        $this->createTestData();
    }
    
    protected function tearDown(): void {
        // Clean up test data
        $this->cleanupTestData();
    }
    
    private function createTestData() {
        // Insert test activities
        $sql = "INSERT INTO activities (user_id, type, description, status, created_at) VALUES 
                (1, 'LOGIN', 'Đăng nhập thành công', 'active', NOW()),
                (2, 'UPDATE_PROFILE', 'Cập nhật thông tin cá nhân', 'active', NOW()),
                (3, 'CREATE_LEAVE', 'Tạo đơn nghỉ phép', 'active', NOW()),
                (4, 'UPLOAD_DOCUMENT', 'Tải lên tài liệu mới', 'active', NOW()),
                (5, 'APPROVE_LEAVE', 'Duyệt đơn nghỉ phép', 'active', NOW())";
        
        $this->conn->exec($sql);
    }
    
    private function cleanupTestData() {
        $sql = "DELETE FROM activities WHERE user_id IN (1, 2, 3, 4, 5)";
        $this->conn->exec($sql);
    }
    
    public function testGetRecentActivities() {
        // Test getting recent activities
        $sql = "SELECT a.*, u.name as user_name, u.email as user_email 
                FROM activities a 
                LEFT JOIN users u ON a.user_id = u.id 
                WHERE a.status = 'active' 
                ORDER BY a.created_at DESC 
                LIMIT 10";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $activities = $stmt->fetchAll();
        
        $this->assertNotEmpty($activities);
        $this->assertCount(5, $activities);
        
        // Verify activity structure
        $firstActivity = $activities[0];
        $this->assertArrayHasKey('id', $firstActivity);
        $this->assertArrayHasKey('user_id', $firstActivity);
        $this->assertArrayHasKey('type', $firstActivity);
        $this->assertArrayHasKey('description', $firstActivity);
        $this->assertArrayHasKey('created_at', $firstActivity);
    }
    
    public function testGetRecentActivitiesByType() {
        // Test getting activities by type
        $type = 'LOGIN';
        $sql = "SELECT a.*, u.name as user_name, u.email as user_email 
                FROM activities a 
                LEFT JOIN users u ON a.user_id = u.id 
                WHERE a.type = :type AND a.status = 'active' 
                ORDER BY a.created_at DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':type' => $type]);
        $activities = $stmt->fetchAll();
        
        $this->assertNotEmpty($activities);
        $this->assertEquals('LOGIN', $activities[0]['type']);
    }
    
    public function testGetRecentActivitiesByUser() {
        // Test getting activities by user
        $userId = 1;
        $sql = "SELECT * FROM activities 
                WHERE user_id = :user_id AND status = 'active' 
                ORDER BY created_at DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        $activities = $stmt->fetchAll();
        
        $this->assertNotEmpty($activities);
        $this->assertEquals($userId, $activities[0]['user_id']);
    }
    
    public function testActivityPagination() {
        // Test pagination
        $page = 1;
        $perPage = 2;
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT a.*, u.name as user_name, u.email as user_email 
                FROM activities a 
                LEFT JOIN users u ON a.user_id = u.id 
                WHERE a.status = 'active' 
                ORDER BY a.created_at DESC 
                LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':limit', $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->execute();
        $activities = $stmt->fetchAll();
        
        $this->assertCount($perPage, $activities);
    }
} 