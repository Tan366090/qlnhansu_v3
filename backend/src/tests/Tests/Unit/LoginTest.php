<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class LoginTest extends TestCase
{
    private $baseUrl = 'http://localhost:3000';

    protected function setUp(): void
    {
        require_once __DIR__ . '/../../../utils/jwt.php';
    }

    public function testValidateEmptyCredentials()
    {
        $data = [
            'username' => '',
            'password' => ''
        ];
        
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Tên đăng nhập và mật khẩu không được để trống');
        
        $this->validateLoginData($data);
    }

    public function testValidateEmptyUsername()
    {
        $data = [
            'username' => '',
            'password' => 'somepassword'
        ];
        
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Tên đăng nhập và mật khẩu không được để trống');
        
        $this->validateLoginData($data);
    }

    public function testValidateEmptyPassword()
    {
        $data = [
            'username' => 'someuser',
            'password' => ''
        ];
        
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Tên đăng nhập và mật khẩu không được để trống');
        
        $this->validateLoginData($data);
    }

    public function testInvalidCredentials()
    {
        $data = [
            'username' => 'wronguser',
            'password' => 'wrongpass'
        ];
        
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Tên đăng nhập hoặc mật khẩu không đúng');
        
        $this->authenticate($data);
    }

    public function testSuccessfulLogin()
    {
        $data = [
            'username' => 'admin',
            'password' => 'admin123'
        ];
        
        $result = $this->authenticate($data);
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('token', $result);
        $this->assertArrayHasKey('user', $result);
        $this->assertEquals($data['username'], $result['user']['username']);
    }

    private function validateLoginData($data)
    {
        if (!isset($data['username']) || !isset($data['password']) || 
            empty($data['username']) || empty($data['password'])) {
            throw new \Exception('Tên đăng nhập và mật khẩu không được để trống');
        }
        return true;
    }

    private function authenticate($data)
    {
        $this->validateLoginData($data);
        
        // Mock user data for testing
        $mockUser = null;
        if ($data['username'] === 'admin' && $data['password'] === 'admin123') {
            $mockUser = [
                'user_id' => 1,
                'username' => 'admin',
                'email' => 'admin@example.com',
                'password_hash' => password_hash('admin123', PASSWORD_DEFAULT),
                'role_name' => 'admin',
                'status' => 'active'
            ];
        }

        if (!$mockUser) {
            throw new \Exception('Tên đăng nhập hoặc mật khẩu không đúng');
        }

        // Generate token data
        $tokenData = [
            'user_id' => $mockUser['user_id'],
            'username' => $mockUser['username'],
            'email' => $mockUser['email'],
            'role' => $mockUser['role_name'],
            'exp' => time() + (60 * 60 * 24)
        ];

        return [
            'success' => true,
            'token' => generateJWT($tokenData),
            'user' => [
                'user_id' => $mockUser['user_id'],
                'username' => $mockUser['username'],
                'email' => $mockUser['email'],
                'role' => $mockUser['role_name']
            ]
        ];
    }
} 