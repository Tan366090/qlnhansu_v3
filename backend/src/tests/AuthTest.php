<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use App\Models\User;

class AuthTest extends TestCase
{
    private $baseUrl = 'http://localhost/QLNhanSu_version1/api/auth.php';
    private $authToken;
    private $sessionCookie;

    protected function setUp(): void
    {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    private function makeRequest($url, $method, $data = null, $headers = [])
    {
        $options = [
            'http' => [
                'method' => $method,
                'header' => array_merge([
                    'Content-Type: application/json',
                    'Cookie: ' . $this->sessionCookie
                ], $headers),
                'ignore_errors' => true
            ]
        ];

        if ($this->authToken) {
            $options['http']['header'][] = 'Authorization: Bearer ' . $this->authToken;
        }

        if ($data !== null) {
            $options['http']['content'] = json_encode($data);
        }

        $context = stream_context_create($options);
        $response = @file_get_contents($url, false, $context);
        
        if ($response === false) {
            $error = error_get_last();
            throw new \RuntimeException("Failed to connect to API endpoint: " . ($error['message'] ?? 'Unknown error'));
        }

        // Get response headers
        $responseHeaders = $http_response_header ?? [];
        
        // Update session cookie if provided
        foreach ($responseHeaders as $header) {
            if (stripos($header, 'Set-Cookie:') === 0) {
                $this->sessionCookie = substr($header, 12);
                break;
            }
        }
        
        $data = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException("Invalid JSON response: " . json_last_error_msg() . "\nResponse: " . $response);
        }
        
        return $data;
    }

    public function testLoginWithValidCredentials()
    {
        $response = $this->makeRequest($this->baseUrl . '?action=login', 'POST', [
            'username' => 'admin',
            'password' => 'admin123'
        ]);
        
        $this->assertEquals(200, $response['status']);
        $this->assertTrue($response['success']);
        $this->assertNotEmpty($response['user']);
    }

    public function testLoginWithMissingCredentials()
    {
        $response = $this->makeRequest($this->baseUrl . '?action=login', 'POST', [
            'username' => '',
            'password' => ''
        ]);
        
        $this->assertEquals(400, $response['status']);
        $this->assertFalse($response['success']);
    }

    public function testLoginWithLockedAccount()
    {
        $response = $this->makeRequest($this->baseUrl . '?action=login', 'POST', [
            'username' => 'locked_user',
            'password' => 'wrong_password'
        ]);
        
        $this->assertEquals(401, $response['status']);
        $this->assertFalse($response['success']);
    }

    public function testLoginWithInactiveAccount()
    {
        $response = $this->makeRequest($this->baseUrl . '?action=login', 'POST', [
            'username' => 'inactive_user',
            'password' => 'wrong_password'
        ]);

        $this->assertFalse($response['success']);
        $this->assertNotEmpty($response['message']);
        $this->assertEquals('Account is inactive', $response['message']);
    }

    public function testPasswordResetRequest()
    {
        $response = $this->makeRequest($this->baseUrl . '?action=password_reset_request', 'POST', [
            'email' => 'admin@example.com'
        ]);

        $this->assertTrue($response['success']);
        $this->assertNotEmpty($response['message']);
    }

    public function testPasswordResetRequestWithInvalidEmail()
    {
        $response = $this->makeRequest($this->baseUrl . '?action=password_reset_request', 'POST', [
            'email' => 'invalid@example.com'
        ]);

        $this->assertFalse($response['success']);
        $this->assertNotEmpty($response['message']);
    }

    public function testPasswordReset()
    {
        $response = $this->makeRequest($this->baseUrl . '?action=password_reset', 'POST', [
            'token' => 'valid_reset_token',
            'password' => 'new_password',
            'password_confirm' => 'new_password'
        ]);

        $this->assertTrue($response['success']);
        $this->assertNotEmpty($response['message']);
    }

    public function testPasswordResetWithInvalidToken()
    {
        $response = $this->makeRequest($this->baseUrl . '?action=password_reset', 'POST', [
            'token' => 'invalid_reset_token',
            'password' => 'new_password',
            'password_confirm' => 'new_password'
        ]);

        $this->assertFalse($response['success']);
        $this->assertNotEmpty($response['message']);
    }

    public function testPasswordResetWithMismatchedPasswords()
    {
        $response = $this->makeRequest($this->baseUrl . '?action=password_reset', 'POST', [
            'token' => 'valid_reset_token',
            'password' => 'new_password',
            'password_confirm' => 'different_password'
        ]);

        $this->assertFalse($response['success']);
        $this->assertNotEmpty($response['message']);
        $this->assertEquals('Passwords do not match', $response['message']);
    }
}