<?php

use PHPUnit\Framework\TestCase;
use App\Config\SessionManager;

class SharedScriptsTest extends TestCase
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

    public function testSessionCheckScript()
    {
        // Test session check script functionality
        $userData = [
            'user_id' => 1,
            'username' => 'test',
            'role_name' => 'admin',
            'email' => 'test@example.com',
            'full_name' => 'Test User'
        ];

        $this->sessionManager->setUserData($userData);
        
        // Verify session check script can access session data
        $this->assertTrue($this->sessionManager->isAuthenticated());
        $this->assertEquals($userData['user_id'], $this->sessionManager->get('user_id'));
        $this->assertEquals($userData['username'], $this->sessionManager->get('username'));
        $this->assertEquals($userData['role_name'], $this->sessionManager->get('role'));
    }

    public function testUserInfoScript()
    {
        // Test user info script functionality
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
            
            // Verify user info script can access and format user data
            $currentUser = $this->sessionManager->getCurrentUser();
            
            $this->assertEquals($userData['username'], $currentUser['username']);
            $this->assertEquals($userData['role_name'], $currentUser['role']);
            $this->assertEquals($userData['email'], $currentUser['email']);
            $this->assertEquals($userData['full_name'], $currentUser['full_name']);
        }
    }

    public function testSessionTimeoutScript()
    {
        // Test session timeout script functionality
        $userData = [
            'user_id' => 1,
            'username' => 'test',
            'role_name' => 'admin',
            'email' => 'test@example.com',
            'full_name' => 'Test User'
        ];

        $this->sessionManager->setUserData($userData);
        
        // Verify session timeout script can detect expired sessions
        $_SESSION['last_activity'] = time() - (24 * 60 * 60 + 1);
        $this->assertFalse($this->sessionManager->isAuthenticated());
    }

    public function testRoleCheckScript()
    {
        // Test role check script functionality
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
            
            // Verify role check script can validate user roles
            $this->assertEquals($role, $this->sessionManager->get('role'));
            
            // Test invalid role
            $this->sessionManager->set('role', 'invalid_role');
            $this->assertNotEquals($role, $this->sessionManager->get('role'));
        }
    }

    public function testSecurityCheckScript()
    {
        // Test security check script functionality
        $userData = [
            'user_id' => 1,
            'username' => 'test',
            'role_name' => 'admin',
            'email' => 'test@example.com',
            'full_name' => 'Test User'
        ];

        $this->sessionManager->setUserData($userData);
        
        // Store original values
        $originalIp = $_SERVER['REMOTE_ADDR'];
        $originalUserAgent = $_SERVER['HTTP_USER_AGENT'];
        
        // Test IP change detection
        $_SERVER['REMOTE_ADDR'] = '192.168.1.2';
        $this->assertFalse($this->sessionManager->isAuthenticated());
        
        // Test user agent change detection
        $_SERVER['REMOTE_ADDR'] = $originalIp;
        $_SERVER['HTTP_USER_AGENT'] = 'Different Browser';
        $this->assertFalse($this->sessionManager->isAuthenticated());
        
        // Restore original values
        $_SERVER['HTTP_USER_AGENT'] = $originalUserAgent;
    }
} 