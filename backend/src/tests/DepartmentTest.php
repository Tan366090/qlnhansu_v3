<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use App\Models\Department;
use App\Models\Employee;

class DepartmentTest extends TestCase
{
    private $baseUrl = 'http://localhost/QLNhanSu_version1/api/department.php';
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

    public function testCreateDepartment()
    {
        $departmentData = [
            'name' => 'IT Department',
            'code' => 'IT',
            'description' => 'Information Technology Department',
            'manager_id' => 1,
            'parent_id' => null,
            'status' => 'active'
        ];

        $response = $this->makeRequest($this->baseUrl . '?action=create', 'POST', $departmentData);
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->fail('Invalid JSON response: ' . json_last_error_msg() . "\nResponse: " . $response);
        }

        $this->assertTrue($data['success']);
        $this->assertNotEmpty($data['department_id']);
    }

    public function testCreateDepartmentWithInvalidData()
    {
        $departmentData = [
            'name' => '', // Empty name
            'code' => '', // Empty code
            'description' => '', // Empty description
            'manager_id' => 999, // Non-existent manager
            'parent_id' => 999, // Non-existent parent
            'status' => 'invalid' // Invalid status
        ];

        $response = $this->makeRequest($this->baseUrl . '?action=create', 'POST', $departmentData);
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->fail('Invalid JSON response: ' . json_last_error_msg() . "\nResponse: " . $response);
        }

        $this->assertFalse($data['success']);
        $this->assertNotEmpty($data['errors']);
    }

    public function testGetDepartmentList()
    {
        $params = [
            'status' => 'active',
            'manager_id' => 1
        ];

        $response = $this->makeRequest($this->baseUrl . '?action=list&' . http_build_query($params));
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->fail('Invalid JSON response: ' . json_last_error_msg() . "\nResponse: " . $response);
        }

        $this->assertTrue($data['success']);
        $this->assertIsArray($data['departments']);
    }

    public function testGetDepartmentDetails()
    {
        $response = $this->makeRequest($this->baseUrl . '?action=get&id=1');
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->fail('Invalid JSON response: ' . json_last_error_msg() . "\nResponse: " . $response);
        }

        $this->assertTrue($data['success']);
        $this->assertIsArray($data['department']);
        $this->assertEquals(1, $data['department']['id']);
    }

    public function testGetNonExistentDepartment()
    {
        $response = $this->makeRequest($this->baseUrl . '?action=get&id=999');
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->fail('Invalid JSON response: ' . json_last_error_msg() . "\nResponse: " . $response);
        }

        $this->assertFalse($data['success']);
        $this->assertNotEmpty($data['message']);
    }

    public function testUpdateDepartment()
    {
        $updateData = [
            'id' => 1,
            'name' => 'Updated IT Department',
            'code' => 'ITD',
            'description' => 'Updated Information Technology Department',
            'manager_id' => 2,
            'parent_id' => null,
            'status' => 'active'
        ];

        $response = $this->makeRequest($this->baseUrl . '?action=update', 'PUT', $updateData);
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->fail('Invalid JSON response: ' . json_last_error_msg() . "\nResponse: " . $response);
        }

        $this->assertTrue($data['success']);
        $this->assertNotEmpty($data['message']);
    }

    public function testUpdateNonExistentDepartment()
    {
        $updateData = [
            'id' => 999,
            'name' => 'Updated Department',
            'code' => 'UD'
        ];

        $response = $this->makeRequest($this->baseUrl . '?action=update', 'PUT', $updateData);
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->fail('Invalid JSON response: ' . json_last_error_msg() . "\nResponse: " . $response);
        }

        $this->assertFalse($data['success']);
        $this->assertNotEmpty($data['message']);
    }

    public function testAssignEmployeeToDepartment()
    {
        $assignmentData = [
            'department_id' => 1,
            'employee_id' => 1,
            'position' => 'Senior Developer',
            'start_date' => date('Y-m-d'),
            'end_date' => null,
            'status' => 'active'
        ];

        $response = $this->makeRequest($this->baseUrl . '?action=assign_employee', 'POST', $assignmentData);
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->fail('Invalid JSON response: ' . json_last_error_msg() . "\nResponse: " . $response);
        }

        $this->assertTrue($data['success']);
        $this->assertNotEmpty($data['assignment_id']);
    }

    public function testAssignEmployeeToNonExistentDepartment()
    {
        $assignmentData = [
            'department_id' => 999,
            'employee_id' => 1,
            'position' => 'Senior Developer',
            'start_date' => date('Y-m-d'),
            'end_date' => null,
            'status' => 'active'
        ];

        $response = $this->makeRequest($this->baseUrl . '?action=assign_employee', 'POST', $assignmentData);
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->fail('Invalid JSON response: ' . json_last_error_msg() . "\nResponse: " . $response);
        }

        $this->assertFalse($data['success']);
        $this->assertNotEmpty($data['message']);
    }

    public function testGetDepartmentEmployees()
    {
        $response = $this->makeRequest($this->baseUrl . '?action=employees&id=1');
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->fail('Invalid JSON response: ' . json_last_error_msg() . "\nResponse: " . $response);
        }

        $this->assertTrue($data['success']);
        $this->assertIsArray($data['employees']);
    }

    public function testGetDepartmentStructure()
    {
        $response = $this->makeRequest($this->baseUrl . '?action=structure');
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->fail('Invalid JSON response: ' . json_last_error_msg() . "\nResponse: " . $response);
        }

        $this->assertTrue($data['success']);
        $this->assertIsArray($data['structure']);
        $this->assertArrayHasKey('departments', $data['structure']);
        $this->assertArrayHasKey('hierarchy', $data['structure']);
    }

    public function testGetDepartmentReport()
    {
        $params = [
            'start_date' => date('Y-m-d', strtotime('-1 year')),
            'end_date' => date('Y-m-d')
        ];

        $response = $this->makeRequest($this->baseUrl . '?action=report&' . http_build_query($params));
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->fail('Invalid JSON response: ' . json_last_error_msg() . "\nResponse: " . $response);
        }

        $this->assertTrue($data['success']);
        $this->assertIsArray($data['report']);
        $this->assertArrayHasKey('total_employees', $data['report']);
        $this->assertArrayHasKey('employees_by_department', $data['report']);
        $this->assertArrayHasKey('turnover_rate', $data['report']);
    }
} 