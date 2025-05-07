<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use App\Models\Leave;
use App\Models\Employee;

class LeaveTest extends TestCase
{
    private $baseUrl = 'http://localhost/QLNhanSu_version1/api/leave.php';
    private $authToken;

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
        $options = [
            'http' => [
                'method' => 'POST',
                'header' => [
                    'Content-Type: application/json',
                ],
                'content' => json_encode([
                    'email' => 'admin@example.com',
                    'password' => '123456'
                ])
            ]
        ];

        $context = stream_context_create($options);
        $response = file_get_contents('http://localhost/QLNhanSu_version1/api/auth.php?action=login', false, $context);
        $data = json_decode($response, true);
        $this->authToken = $data['token'];
    }

    private function makeRequest($url, $method = 'GET', $data = null)
    {
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->authToken
        ];

        $options = [
            'http' => [
                'method' => $method,
                'header' => $headers,
                'ignore_errors' => true
            ]
        ];

        if ($data !== null) {
            $options['http']['content'] = json_encode($data);
        }

        $context = stream_context_create($options);
        $response = @file_get_contents($url, false, $context);
        
        if ($response === false) {
            $error = error_get_last();
            $this->fail("Failed to connect to API endpoint: " . ($error['message'] ?? 'Unknown error'));
        }

        return $response;
    }

    public function testRequestLeave()
    {
        $leaveData = [
            'employee_id' => 1,
            'leave_type' => 'annual',
            'start_date' => date('Y-m-d', strtotime('+1 week')),
            'end_date' => date('Y-m-d', strtotime('+2 weeks')),
            'reason' => 'Annual vacation',
            'contact_info' => '0123456789',
            'status' => 'pending'
        ];

        $response = $this->makeRequest($this->baseUrl . '?action=request', 'POST', $leaveData);
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->fail('Invalid JSON response: ' . json_last_error_msg() . "\nResponse: " . $response);
        }

        $this->assertTrue($data['success']);
        $this->assertNotEmpty($data['leave_id']);
    }

    public function testRequestLeaveWithInvalidData()
    {
        $leaveData = [
            'employee_id' => 999, // Non-existent employee
            'leave_type' => 'invalid', // Invalid leave type
            'start_date' => 'invalid-date', // Invalid date
            'end_date' => date('Y-m-d', strtotime('-1 day')), // End date before start date
            'reason' => '', // Empty reason
            'contact_info' => '', // Empty contact info
            'status' => 'invalid' // Invalid status
        ];

        $response = $this->makeRequest($this->baseUrl . '?action=request', 'POST', $leaveData);
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->fail('Invalid JSON response: ' . json_last_error_msg() . "\nResponse: " . $response);
        }

        $this->assertFalse($data['success']);
        $this->assertNotEmpty($data['errors']);
    }

    public function testGetLeaveList()
    {
        $params = [
            'employee_id' => 1,
            'status' => 'pending',
            'start_date' => date('Y-m-d'),
            'end_date' => date('Y-m-d', strtotime('+1 month'))
        ];

        $response = $this->makeRequest($this->baseUrl . '?action=list&' . http_build_query($params));
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->fail('Invalid JSON response: ' . json_last_error_msg() . "\nResponse: " . $response);
        }

        $this->assertTrue($data['success']);
        $this->assertIsArray($data['leaves']);
    }

    public function testGetLeaveDetails()
    {
        $response = $this->makeRequest($this->baseUrl . '?action=get&id=1');
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->fail('Invalid JSON response: ' . json_last_error_msg() . "\nResponse: " . $response);
        }

        $this->assertTrue($data['success']);
        $this->assertIsArray($data['leave']);
        $this->assertEquals(1, $data['leave']['id']);
    }

    public function testGetNonExistentLeave()
    {
        $response = $this->makeRequest($this->baseUrl . '?action=get&id=999');
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->fail('Invalid JSON response: ' . json_last_error_msg() . "\nResponse: " . $response);
        }

        $this->assertFalse($data['success']);
        $this->assertNotEmpty($data['message']);
    }

    public function testUpdateLeave()
    {
        $updateData = [
            'id' => 1,
            'leave_type' => 'sick',
            'start_date' => date('Y-m-d', strtotime('+2 weeks')),
            'end_date' => date('Y-m-d', strtotime('+3 weeks')),
            'reason' => 'Medical treatment',
            'contact_info' => '0987654321',
            'status' => 'pending'
        ];

        $response = $this->makeRequest($this->baseUrl . '?action=update', 'PUT', $updateData);
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->fail('Invalid JSON response: ' . json_last_error_msg() . "\nResponse: " . $response);
        }

        $this->assertTrue($data['success']);
        $this->assertNotEmpty($data['message']);
    }

    public function testUpdateNonExistentLeave()
    {
        $updateData = [
            'id' => 999,
            'reason' => 'Updated reason',
            'contact_info' => 'Updated contact'
        ];

        $response = $this->makeRequest($this->baseUrl . '?action=update', 'PUT', $updateData);
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->fail('Invalid JSON response: ' . json_last_error_msg() . "\nResponse: " . $response);
        }

        $this->assertFalse($data['success']);
        $this->assertNotEmpty($data['message']);
    }

    public function testApproveLeave()
    {
        $approvalData = [
            'id' => 1,
            'approved_by' => 2,
            'approval_date' => date('Y-m-d'),
            'notes' => 'Approved as per policy'
        ];

        $response = $this->makeRequest($this->baseUrl . '?action=approve', 'POST', $approvalData);
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->fail('Invalid JSON response: ' . json_last_error_msg() . "\nResponse: " . $response);
        }

        $this->assertTrue($data['success']);
        $this->assertNotEmpty($data['message']);
    }

    public function testApproveNonExistentLeave()
    {
        $approvalData = [
            'id' => 999,
            'approved_by' => 2,
            'approval_date' => date('Y-m-d'),
            'notes' => 'Approved as per policy'
        ];

        $response = $this->makeRequest($this->baseUrl . '?action=approve', 'POST', $approvalData);
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->fail('Invalid JSON response: ' . json_last_error_msg() . "\nResponse: " . $response);
        }

        $this->assertFalse($data['success']);
        $this->assertNotEmpty($data['message']);
    }

    public function testRejectLeave()
    {
        $rejectionData = [
            'id' => 1,
            'rejected_by' => 2,
            'rejection_date' => date('Y-m-d'),
            'reason' => 'Insufficient leave balance'
        ];

        $response = $this->makeRequest($this->baseUrl . '?action=reject', 'POST', $rejectionData);
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->fail('Invalid JSON response: ' . json_last_error_msg() . "\nResponse: " . $response);
        }

        $this->assertTrue($data['success']);
        $this->assertNotEmpty($data['message']);
    }

    public function testRejectNonExistentLeave()
    {
        $rejectionData = [
            'id' => 999,
            'rejected_by' => 2,
            'rejection_date' => date('Y-m-d'),
            'reason' => 'Insufficient leave balance'
        ];

        $response = $this->makeRequest($this->baseUrl . '?action=reject', 'POST', $rejectionData);
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->fail('Invalid JSON response: ' . json_last_error_msg() . "\nResponse: " . $response);
        }

        $this->assertFalse($data['success']);
        $this->assertNotEmpty($data['message']);
    }

    public function testGetLeaveBalance()
    {
        $response = $this->makeRequest($this->baseUrl . '?action=balance&employee_id=1');
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->fail('Invalid JSON response: ' . json_last_error_msg() . "\nResponse: " . $response);
        }

        $this->assertTrue($data['success']);
        $this->assertIsArray($data['balance']);
        $this->assertArrayHasKey('annual', $data['balance']);
        $this->assertArrayHasKey('sick', $data['balance']);
        $this->assertArrayHasKey('unpaid', $data['balance']);
    }

    public function testGetLeaveReport()
    {
        $params = [
            'department_id' => 1,
            'start_date' => date('Y-m-d', strtotime('-6 months')),
            'end_date' => date('Y-m-d')
        ];

        $response = $this->makeRequest($this->baseUrl . '?action=report&' . http_build_query($params));
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->fail('Invalid JSON response: ' . json_last_error_msg() . "\nResponse: " . $response);
        }

        $this->assertTrue($data['success']);
        $this->assertIsArray($data['report']);
        $this->assertArrayHasKey('total_leaves', $data['report']);
        $this->assertArrayHasKey('leaves_by_type', $data['report']);
        $this->assertArrayHasKey('leaves_by_status', $data['report']);
    }
} 