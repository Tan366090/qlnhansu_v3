<?php

use PHPUnit\Framework\TestCase;
use App\Config\SessionManager;

class SessionManagerTest extends TestCase
{
    private $sessionManager;
    private $originalIp;
    private $originalUserAgent;

    protected function setUp(): void
    {
        $this->sessionManager = SessionManager::getInstance();
        $this->sessionManager->init();
        
        // Store original values
        $this->originalIp = $_SERVER['REMOTE_ADDR'] ?? null;
        $this->originalUserAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        
        // Set default test values
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['HTTP_USER_AGENT'] = 'PHPUnit Test Browser';
    }

    protected function tearDown(): void
    {
        $this->sessionManager->destroy();
        
        // Restore original values
        if ($this->originalIp !== null) {
            $_SERVER['REMOTE_ADDR'] = $this->originalIp;
        }
        if ($this->originalUserAgent !== null) {
            $_SERVER['HTTP_USER_AGENT'] = $this->originalUserAgent;
        }
    }

    public function testAdminDashboardSession()
    {
        // Test admin session initialization
        $adminData = [
            'user_id' => 1,
            'username' => 'admin',
            'role_name' => 'admin',
            'email' => 'admin@example.com',
            'full_name' => 'Admin User',
            'department_id' => 1,
            'position_id' => 1
        ];

        $this->sessionManager->setUserData($adminData);

        // Verify session data
        $this->assertTrue($this->sessionManager->isAuthenticated());
        $this->assertEquals('admin', $this->sessionManager->get('role'));
        
        $currentUser = $this->sessionManager->getCurrentUser();
        $this->assertEquals($adminData['user_id'], $currentUser['user_id']);
        $this->assertEquals($adminData['username'], $currentUser['username']);
        $this->assertEquals($adminData['role_name'], $currentUser['role']);
    }

    public function testHRDashboardSession()
    {
        // Test HR session initialization
        $hrData = [
            'user_id' => 2,
            'username' => 'hr',
            'role_name' => 'hr',
            'email' => 'hr@example.com',
            'full_name' => 'HR Staff',
            'department_id' => 2,
            'position_id' => 2
        ];

        $this->sessionManager->setUserData($hrData);

        // Verify session data
        $this->assertTrue($this->sessionManager->isAuthenticated());
        $this->assertEquals('hr', $this->sessionManager->get('role'));
        
        $currentUser = $this->sessionManager->getCurrentUser();
        $this->assertEquals($hrData['user_id'], $currentUser['user_id']);
        $this->assertEquals($hrData['username'], $currentUser['username']);
        $this->assertEquals($hrData['role_name'], $currentUser['role']);
    }

    public function testManagerDashboardSession()
    {
        // Test manager session initialization
        $managerData = [
            'user_id' => 3,
            'username' => 'manager',
            'role_name' => 'manager',
            'email' => 'manager@example.com',
            'full_name' => 'Manager User',
            'department_id' => 3,
            'position_id' => 3
        ];

        $this->sessionManager->setUserData($managerData);

        // Verify session data
        $this->assertTrue($this->sessionManager->isAuthenticated());
        $this->assertEquals('manager', $this->sessionManager->get('role'));
        
        $currentUser = $this->sessionManager->getCurrentUser();
        $this->assertEquals($managerData['user_id'], $currentUser['user_id']);
        $this->assertEquals($managerData['username'], $currentUser['username']);
        $this->assertEquals($managerData['role_name'], $currentUser['role']);
    }

    public function testSessionTimeout()
    {
        // Test session timeout after 5 minutes
        $userData = [
            'user_id' => 1,
            'username' => 'test',
            'role_name' => 'admin',
            'email' => 'test@example.com',
            'full_name' => 'Test User'
        ];

        $this->sessionManager->setUserData($userData);
        
        // Simulate 5 minutes passing
        $_SESSION['last_activity'] = time() - (5 * 60 + 1);
        
        // Session should still be valid
        $this->assertTrue($this->sessionManager->isAuthenticated());
        
        // Simulate 24 hours passing (session lifetime)
        $_SESSION['last_activity'] = time() - (24 * 60 * 60 + 1);
        
        // Session should be invalid
        $this->assertFalse($this->sessionManager->isAuthenticated());
    }

    public function testSessionSecurity()
    {
        // Test session security features
        $userData = [
            'user_id' => 1,
            'username' => 'test',
            'role_name' => 'admin',
            'email' => 'test@example.com',
            'full_name' => 'Test User'
        ];

        $this->sessionManager->setUserData($userData);
        
        // Initial check should pass
        $this->assertTrue($this->sessionManager->isAuthenticated());
        
        // Change IP address
        $originalIp = $_SERVER['REMOTE_ADDR'];
        $_SERVER['REMOTE_ADDR'] = '192.168.1.2';
        
        // Session should fail with different IP
        $this->assertFalse($this->sessionManager->isAuthenticated());
        
        // Restore IP and change user agent
        $_SERVER['REMOTE_ADDR'] = $originalIp;
        $originalUserAgent = $_SERVER['HTTP_USER_AGENT'];
        $_SERVER['HTTP_USER_AGENT'] = 'Different Browser';
        
        // Session should fail with different user agent
        $this->assertFalse($this->sessionManager->isAuthenticated());
        
        // Restore original values
        $_SERVER['HTTP_USER_AGENT'] = $originalUserAgent;
        
        // Session should be valid again
        $this->assertTrue($this->sessionManager->isAuthenticated());
    }

    public function testSessionDataConsistency()
    {
        // Test session data consistency across all roles
        $roles = ['admin', 'hr', 'manager'];
        
        foreach ($roles as $role) {
            $userData = [
                'user_id' => 1,
                'username' => $role,
                'role_name' => $role,
                'email' => $role . '@example.com',
                'full_name' => ucfirst($role) . ' User'
            ];
            
            $this->sessionManager->setUserData($userData);
            
            $currentUser = $this->sessionManager->getCurrentUser();
            
            // Verify all required fields are present
            $this->assertArrayHasKey('user_id', $currentUser);
            $this->assertArrayHasKey('username', $currentUser);
            $this->assertArrayHasKey('role', $currentUser);
            $this->assertArrayHasKey('email', $currentUser);
            $this->assertArrayHasKey('full_name', $currentUser);
            
            // Verify data consistency
            $this->assertEquals($userData['user_id'], $currentUser['user_id']);
            $this->assertEquals($userData['username'], $currentUser['username']);
            $this->assertEquals($userData['role_name'], $currentUser['role']);
            $this->assertEquals($userData['email'], $currentUser['email']);
            $this->assertEquals($userData['full_name'], $currentUser['full_name']);
        }
    }
} 