<?php

use PHPUnit\Framework\TestCase;
use App\Config\SessionManager;

class DashboardSessionTest extends TestCase
{
    private $sessionManager;

    protected function setUp(): void
    {
        $this->sessionManager = SessionManager::getInstance();
        $this->sessionManager->init();
    }

    protected function tearDown(): void
    {
        $this->sessionManager->destroy();
    }

    public function testAdminDashboardAccess()
    {
        // Test admin access
        $adminData = [
            'user_id' => 1,
            'username' => 'admin',
            'role_name' => 'admin',
            'email' => 'admin@example.com',
            'full_name' => 'Admin User'
        ];

        $this->sessionManager->setUserData($adminData);
        
        // Admin should have access
        $this->assertTrue($this->sessionManager->isAuthenticated());
        $this->assertEquals('admin', $this->sessionManager->get('role'));
        
        // Test non-admin access attempt
        $this->sessionManager->set('role', 'user');
        $this->assertFalse($this->sessionManager->get('role') === 'admin');
    }

    public function testHRDashboardAccess()
    {
        // Test HR access
        $hrData = [
            'user_id' => 2,
            'username' => 'hr',
            'role_name' => 'hr',
            'email' => 'hr@example.com',
            'full_name' => 'HR Staff'
        ];

        $this->sessionManager->setUserData($hrData);
        
        // HR should have access
        $this->assertTrue($this->sessionManager->isAuthenticated());
        $this->assertEquals('hr', $this->sessionManager->get('role'));
        
        // Test non-HR access attempt
        $this->sessionManager->set('role', 'user');
        $this->assertFalse($this->sessionManager->get('role') === 'hr');
    }

    public function testManagerDashboardAccess()
    {
        // Test manager access
        $managerData = [
            'user_id' => 3,
            'username' => 'manager',
            'role_name' => 'manager',
            'email' => 'manager@example.com',
            'full_name' => 'Manager User'
        ];

        $this->sessionManager->setUserData($managerData);
        
        // Manager should have access
        $this->assertTrue($this->sessionManager->isAuthenticated());
        $this->assertEquals('manager', $this->sessionManager->get('role'));
        
        // Test non-manager access attempt
        $this->sessionManager->set('role', 'user');
        $this->assertFalse($this->sessionManager->get('role') === 'manager');
    }

    public function testSessionCheckInterval()
    {
        // Test session check interval (5 minutes)
        $userData = [
            'user_id' => 1,
            'username' => 'test',
            'role_name' => 'admin',
            'email' => 'test@example.com',
            'full_name' => 'Test User'
        ];

        $this->sessionManager->setUserData($userData);
        
        // Initial check
        $this->assertTrue($this->sessionManager->isAuthenticated());
        
        // Simulate 4 minutes passing
        $_SESSION['last_activity'] = time() - (4 * 60);
        $this->assertTrue($this->sessionManager->isAuthenticated());
        
        // Simulate 6 minutes passing (should still be valid)
        $_SESSION['last_activity'] = time() - (6 * 60);
        $this->assertTrue($this->sessionManager->isAuthenticated());
        
        // Simulate 24 hours passing (should be invalid)
        $_SESSION['last_activity'] = time() - (24 * 60 * 60 + 1);
        $this->assertFalse($this->sessionManager->isAuthenticated());
    }

    public function testSessionRedirect()
    {
        // Test session redirect behavior
        $userData = [
            'user_id' => 1,
            'username' => 'test',
            'role_name' => 'admin',
            'email' => 'test@example.com',
            'full_name' => 'Test User'
        ];

        $this->sessionManager->setUserData($userData);
        
        // Valid session should not redirect
        $this->assertTrue($this->sessionManager->isAuthenticated());
        
        // Invalid session should trigger redirect
        $this->sessionManager->destroy();
        $this->assertFalse($this->sessionManager->isAuthenticated());
    }

    public function testUserInfoDisplay()
    {
        // Test user info display consistency
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
            
            // Verify user info is consistently available
            $this->assertEquals($userData['username'], $this->sessionManager->get('username'));
            $this->assertEquals($userData['role_name'], $this->sessionManager->get('role'));
            $this->assertEquals($userData['email'], $this->sessionManager->get('email'));
            $this->assertEquals($userData['full_name'], $this->sessionManager->get('full_name'));
        }
    }
} 