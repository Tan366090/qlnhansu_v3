<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use App\Models\Attendance;
use App\Models\Employee;

class AttendanceTest extends TestCase
{
    private $baseUrl = 'http://localhost/QLNhanSu_version1/api/attendance.php';
    private $authToken;
    private $sessionCookie;

    protected function setUp(): void
    {
        parent::setUp();
        // Ensure error reporting is enabled
        error_reporting(E_ALL);
        ini_set('display_errors', 1);

        // Login to get auth token
        $this->login();
    }

    private function login()
    {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Get CSRF token
        $options = [
            'http' => [
                'method' => 'GET',
                'header' => [
                    'Content-Type: application/json'
                ],
                'ignore_errors' => true
            ]
        ];

        $context = stream_context_create($options);
        $csrfResponse = file_get_contents('http://localhost/QLNhanSu_version1/api/auth.php?action=get_csrf_token', false, $context);
        $csrfData = json_decode($csrfResponse, true);
        
        if (!isset($csrfData['csrf_token'])) {
            throw new \RuntimeException('Failed to get CSRF token: ' . $csrfResponse);
        }
        
        $csrfToken = $csrfData['csrf_token'];

        // Store session cookie if provided
        foreach ($http_response_header as $header) {
            if (stripos($header, 'Set-Cookie:') === 0) {
                $this->sessionCookie = substr($header, 12);
                break;
            }
        }

        // Login request
        $options = [
            'http' => [
                'method' => 'POST',
                'header' => [
                    'Content-Type: application/json',
                    'X-CSRF-TOKEN: ' . $csrfToken,
                    'Cookie: ' . $this->sessionCookie
                ],
                'content' => json_encode([
                    'email' => 'admin@example.com',
                    'password' => '123456'
                ]),
                'ignore_errors' => true
            ]
        ];

        $context = stream_context_create($options);
        $response = file_get_contents('http://localhost/QLNhanSu_version1/api/auth.php?action=login', false, $context);
        $data = json_decode($response, true);
        
        if (!isset($data['token'])) {
            throw new \RuntimeException('Failed to login: ' . $response);
        }
        
        $this->authToken = $data['token'];

        // Update session cookie if provided in login response
        foreach ($http_response_header as $header) {
            if (stripos($header, 'Set-Cookie:') === 0) {
                $this->sessionCookie = substr($header, 12);
                break;
            }
        }
    }

    private function makeRequest($url, $method, $data = null)
    {
        $options = [
            'http' => [
                'method' => $method,
                'header' => [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $this->authToken,
                    'Cookie: ' . $this->sessionCookie
                ],
                'ignore_errors' => true
            ]
        ];

        if ($data !== null) {
            $options['http']['content'] = json_encode($data);
        }

        $context = stream_context_create($options);
        $response = file_get_contents($url, false, $context);
        
        // Update session cookie if provided
        foreach ($http_response_header as $header) {
            if (stripos($header, 'Set-Cookie:') === 0) {
                $this->sessionCookie = substr($header, 12);
                break;
            }
        }
        
        return json_decode($response, true);
    }

    public function testCheckIn()
    {
        $checkInData = [
            'employee_id' => 1,
            'check_in_time' => date('Y-m-d H:i:s'),
            'location' => 'Office',
            'notes' => 'Regular check-in'
        ];

        $response = $this->makeRequest($this->baseUrl . '?action=check_in', 'POST', $checkInData);
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->fail('Invalid JSON response: ' . json_last_error_msg() . "\nResponse: " . $response);
        }

        $this->assertTrue($data['success']);
        $this->assertNotEmpty($data['attendance_id']);
    }

    public function testCheckInWithInvalidData()
    {
        $checkInData = [
            'employee_id' => 999, // Non-existent employee
            'check_in_time' => 'invalid-date', // Invalid date format
            'location' => '', // Empty location
            'notes' => '' // Empty notes
        ];

        $response = $this->makeRequest($this->baseUrl . '?action=check_in', 'POST', $checkInData);
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->fail('Invalid JSON response: ' . json_last_error_msg() . "\nResponse: " . $response);
        }

        $this->assertFalse($data['success']);
        $this->assertNotEmpty($data['errors']);
    }

    public function testCheckOut()
    {
        $checkOutData = [
            'attendance_id' => 1,
            'check_out_time' => date('Y-m-d H:i:s'),
            'notes' => 'Regular check-out'
        ];

        $response = $this->makeRequest($this->baseUrl . '?action=check_out', 'POST', $checkOutData);
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->fail('Invalid JSON response: ' . json_last_error_msg() . "\nResponse: " . $response);
        }

        $this->assertTrue($data['success']);
        $this->assertNotEmpty($data['message']);
    }

    public function testCheckOutWithInvalidData()
    {
        $checkOutData = [
            'attendance_id' => 999, // Non-existent attendance
            'check_out_time' => 'invalid-date', // Invalid date format
            'notes' => '' // Empty notes
        ];

        $response = $this->makeRequest($this->baseUrl . '?action=check_out', 'POST', $checkOutData);
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->fail('Invalid JSON response: ' . json_last_error_msg() . "\nResponse: " . $response);
        }

        $this->assertFalse($data['success']);
        $this->assertNotEmpty($data['errors']);
    }

    public function testGetAttendanceList()
    {
        $params = [
            'employee_id' => 1,
            'start_date' => date('Y-m-d', strtotime('-7 days')),
            'end_date' => date('Y-m-d')
        ];

        $response = $this->makeRequest($this->baseUrl . '?action=list&' . http_build_query($params));
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->fail('Invalid JSON response: ' . json_last_error_msg() . "\nResponse: " . $response);
        }

        $this->assertTrue($data['success']);
        $this->assertIsArray($data['attendance_records']);
    }

    public function testGetAttendanceDetails()
    {
        $response = $this->makeRequest($this->baseUrl . '?action=get&id=1');
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->fail('Invalid JSON response: ' . json_last_error_msg() . "\nResponse: " . $response);
        }

        $this->assertTrue($data['success']);
        $this->assertIsArray($data['attendance']);
        $this->assertEquals(1, $data['attendance']['id']);
    }

    public function testGetNonExistentAttendance()
    {
        $response = $this->makeRequest($this->baseUrl . '?action=get&id=999');
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->fail('Invalid JSON response: ' . json_last_error_msg() . "\nResponse: " . $response);
        }

        $this->assertFalse($data['success']);
        $this->assertNotEmpty($data['message']);
    }

    public function testUpdateAttendance()
    {
        $updateData = [
            'id' => 1,
            'check_in_time' => date('Y-m-d 08:00:00'),
            'check_out_time' => date('Y-m-d 17:00:00'),
            'notes' => 'Updated attendance record'
        ];

        $response = $this->makeRequest($this->baseUrl . '?action=update', 'PUT', $updateData);
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->fail('Invalid JSON response: ' . json_last_error_msg() . "\nResponse: " . $response);
        }

        $this->assertTrue($data['success']);
        $this->assertNotEmpty($data['message']);
    }

    public function testUpdateNonExistentAttendance()
    {
        $updateData = [
            'id' => 999,
            'check_in_time' => date('Y-m-d 08:00:00'),
            'check_out_time' => date('Y-m-d 17:00:00'),
            'notes' => 'Updated attendance record'
        ];

        $response = $this->makeRequest($this->baseUrl . '?action=update', 'PUT', $updateData);
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->fail('Invalid JSON response: ' . json_last_error_msg() . "\nResponse: " . $response);
        }

        $this->assertFalse($data['success']);
        $this->assertNotEmpty($data['message']);
    }

    public function testGetAttendanceSummary()
    {
        $params = [
            'employee_id' => 1,
            'month' => date('m'),
            'year' => date('Y')
        ];

        $response = $this->makeRequest($this->baseUrl . '?action=summary&' . http_build_query($params));
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->fail('Invalid JSON response: ' . json_last_error_msg() . "\nResponse: " . $response);
        }

        $this->assertTrue($data['success']);
        $this->assertIsArray($data['summary']);
        $this->assertArrayHasKey('total_days', $data['summary']);
        $this->assertArrayHasKey('present_days', $data['summary']);
        $this->assertArrayHasKey('absent_days', $data['summary']);
        $this->assertArrayHasKey('late_days', $data['summary']);
        $this->assertArrayHasKey('early_leaves', $data['summary']);
    }

    public function testGetAttendanceReport()
    {
        $params = [
            'department_id' => 1,
            'start_date' => date('Y-m-d', strtotime('-30 days')),
            'end_date' => date('Y-m-d')
        ];

        $response = $this->makeRequest($this->baseUrl . '?action=report&' . http_build_query($params));
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->fail('Invalid JSON response: ' . json_last_error_msg() . "\nResponse: " . $response);
        }

        $this->assertTrue($data['success']);
        $this->assertIsArray($data['report']);
        $this->assertArrayHasKey('department_name', $data['report']);
        $this->assertArrayHasKey('total_employees', $data['report']);
        $this->assertArrayHasKey('attendance_stats', $data['report']);
    }
} 