<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

class PositionTest extends TestCase
{
    private $baseUrl = 'http://localhost/QLNhanSu_version1/api/position.php';
    private $authToken;

    protected function setUp(): void
    {
        // Đăng nhập để lấy token
        $loginResponse = file_get_contents('http://localhost/QLNhanSu_version1/api/auth.php?action=login', false, stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/json',
                'content' => json_encode([
                    'username' => 'admin',
                    'password' => 'admin123'
                ])
            ]
        ]));

        $loginData = json_decode($loginResponse, true);
        $this->authToken = $loginData['token'];
    }

    public function testGetPositions()
    {
        $response = $this->makeRequest($this->baseUrl . '?action=list', 'GET');
        $this->assertTrue($response['success']);
        $this->assertIsArray($response['positions']);
    }

    private function makeRequest($url, $method, $data = null)
    {
        $options = [
            'http' => [
                'method' => $method,
                'header' => [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $this->authToken
                ],
                'ignore_errors' => true
            ]
        ];

        if ($data !== null) {
            $options['http']['content'] = json_encode($data);
        }

        $context = stream_context_create($options);
        $response = file_get_contents($url, false, $context);
        
        return json_decode($response, true);
    }

    public function testCreatePosition()
    {
        $positionData = [
            'title' => 'Senior Developer',
            'code' => 'SD',
            'department_id' => 1,
            'description' => 'Senior Software Developer position',
            'requirements' => [
                'education' => 'Bachelor\'s degree in Computer Science',
                'experience' => '5+ years of experience',
                'skills' => ['PHP', 'JavaScript', 'MySQL']
            ],
            'responsibilities' => [
                'Develop and maintain web applications',
                'Lead development projects',
                'Mentor junior developers'
            ],
            'salary_range' => [
                'min' => 20000000,
                'max' => 30000000
            ],
            'status' => 'active'
        ];

        $response = $this->makeRequest($this->baseUrl . '?action=create', 'POST', $positionData);
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->fail('Invalid JSON response: ' . json_last_error_msg() . "\nResponse: " . $response);
        }

        $this->assertTrue($data['success']);
        $this->assertNotEmpty($data['position_id']);
    }

    public function testCreatePositionWithInvalidData()
    {
        $positionData = [
            'title' => '', // Empty title
            'code' => '', // Empty code
            'department_id' => 999, // Non-existent department
            'description' => '', // Empty description
            'requirements' => [], // Empty requirements
            'responsibilities' => [], // Empty responsibilities
            'salary_range' => [
                'min' => 0,
                'max' => 0
            ],
            'status' => 'invalid' // Invalid status
        ];

        $response = $this->makeRequest($this->baseUrl . '?action=create', 'POST', $positionData);
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->fail('Invalid JSON response: ' . json_last_error_msg() . "\nResponse: " . $response);
        }

        $this->assertFalse($data['success']);
        $this->assertNotEmpty($data['errors']);
    }

    public function testGetPositionDetails()
    {
        $response = $this->makeRequest($this->baseUrl . '?action=get&id=1');
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->fail('Invalid JSON response: ' . json_last_error_msg() . "\nResponse: " . $response);
        }

        $this->assertTrue($data['success']);
        $this->assertIsArray($data['position']);
        $this->assertEquals(1, $data['position']['id']);
    }

    public function testGetNonExistentPosition()
    {
        $response = $this->makeRequest($this->baseUrl . '?action=get&id=999');
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->fail('Invalid JSON response: ' . json_last_error_msg() . "\nResponse: " . $response);
        }

        $this->assertFalse($data['success']);
        $this->assertNotEmpty($data['message']);
    }

    public function testUpdatePosition()
    {
        $updateData = [
            'id' => 1,
            'title' => 'Lead Developer',
            'code' => 'LD',
            'department_id' => 1,
            'description' => 'Lead Software Developer position',
            'requirements' => [
                'education' => 'Bachelor\'s degree in Computer Science',
                'experience' => '7+ years of experience',
                'skills' => ['PHP', 'JavaScript', 'MySQL', 'Leadership']
            ],
            'responsibilities' => [
                'Lead development team',
                'Architect software solutions',
                'Mentor team members'
            ],
            'salary_range' => [
                'min' => 25000000,
                'max' => 35000000
            ],
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

    public function testUpdateNonExistentPosition()
    {
        $updateData = [
            'id' => 999,
            'title' => 'Updated Position',
            'code' => 'UP'
        ];

        $response = $this->makeRequest($this->baseUrl . '?action=update', 'PUT', $updateData);
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->fail('Invalid JSON response: ' . json_last_error_msg() . "\nResponse: " . $response);
        }

        $this->assertFalse($data['success']);
        $this->assertNotEmpty($data['message']);
    }

    public function testAssignEmployeeToPosition()
    {
        $assignmentData = [
            'position_id' => 1,
            'employee_id' => 1,
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

    public function testAssignEmployeeToNonExistentPosition()
    {
        $assignmentData = [
            'position_id' => 999,
            'employee_id' => 1,
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

    public function testGetPositionEmployees()
    {
        $response = $this->makeRequest($this->baseUrl . '?action=employees&id=1');
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->fail('Invalid JSON response: ' . json_last_error_msg() . "\nResponse: " . $response);
        }

        $this->assertTrue($data['success']);
        $this->assertIsArray($data['employees']);
    }

    public function testGetPositionRequirements()
    {
        $response = $this->makeRequest($this->baseUrl . '?action=requirements&id=1');
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->fail('Invalid JSON response: ' . json_last_error_msg() . "\nResponse: " . $response);
        }

        $this->assertTrue($data['success']);
        $this->assertIsArray($data['requirements']);
    }

    public function testGetPositionReport()
    {
        $params = [
            'department_id' => 1,
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
        $this->assertArrayHasKey('total_positions', $data['report']);
        $this->assertArrayHasKey('positions_by_department', $data['report']);
        $this->assertArrayHasKey('vacant_positions', $data['report']);
    }
} 