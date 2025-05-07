<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use App\Models\Performance;
use App\Models\Employee;

class PerformanceTest extends TestCase
{
    private $baseUrl = 'http://localhost/QLNhanSu_version1/api/performance.php';
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

    public function testCreateEvaluation()
    {
        $evaluationData = [
            'employee_id' => 1,
            'evaluator_id' => 2,
            'evaluation_period' => 'Q1 2024',
            'evaluation_date' => date('Y-m-d'),
            'criteria' => [
                'work_quality' => 4,
                'productivity' => 4,
                'teamwork' => 5,
                'communication' => 4,
                'initiative' => 3
            ],
            'strengths' => 'Excellent team player, strong communication skills',
            'areas_for_improvement' => 'Could take more initiative in projects',
            'overall_rating' => 4,
            'status' => 'pending'
        ];

        $response = $this->makeRequest($this->baseUrl . '?action=create', 'POST', $evaluationData);
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->fail('Invalid JSON response: ' . json_last_error_msg() . "\nResponse: " . $response);
        }

        $this->assertTrue($data['success']);
        $this->assertNotEmpty($data['evaluation_id']);
    }

    public function testCreateEvaluationWithInvalidData()
    {
        $evaluationData = [
            'employee_id' => 999, // Non-existent employee
            'evaluator_id' => 999, // Non-existent evaluator
            'evaluation_period' => '', // Empty period
            'evaluation_date' => 'invalid-date', // Invalid date
            'criteria' => [], // Empty criteria
            'strengths' => '', // Empty strengths
            'areas_for_improvement' => '', // Empty areas for improvement
            'overall_rating' => 6, // Invalid rating (should be 1-5)
            'status' => 'invalid' // Invalid status
        ];

        $response = $this->makeRequest($this->baseUrl . '?action=create', 'POST', $evaluationData);
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->fail('Invalid JSON response: ' . json_last_error_msg() . "\nResponse: " . $response);
        }

        $this->assertFalse($data['success']);
        $this->assertNotEmpty($data['errors']);
    }

    public function testGetEvaluationList()
    {
        $params = [
            'employee_id' => 1,
            'evaluator_id' => 2,
            'evaluation_period' => 'Q1 2024',
            'status' => 'pending'
        ];

        $response = $this->makeRequest($this->baseUrl . '?action=list&' . http_build_query($params));
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->fail('Invalid JSON response: ' . json_last_error_msg() . "\nResponse: " . $response);
        }

        $this->assertTrue($data['success']);
        $this->assertIsArray($data['evaluations']);
    }

    public function testGetEvaluationDetails()
    {
        $response = $this->makeRequest($this->baseUrl . '?action=get&id=1');
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->fail('Invalid JSON response: ' . json_last_error_msg() . "\nResponse: " . $response);
        }

        $this->assertTrue($data['success']);
        $this->assertIsArray($data['evaluation']);
        $this->assertEquals(1, $data['evaluation']['id']);
    }

    public function testGetNonExistentEvaluation()
    {
        $response = $this->makeRequest($this->baseUrl . '?action=get&id=999');
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->fail('Invalid JSON response: ' . json_last_error_msg() . "\nResponse: " . $response);
        }

        $this->assertFalse($data['success']);
        $this->assertNotEmpty($data['message']);
    }

    public function testUpdateEvaluation()
    {
        $updateData = [
            'id' => 1,
            'criteria' => [
                'work_quality' => 5,
                'productivity' => 4,
                'teamwork' => 5,
                'communication' => 5,
                'initiative' => 4
            ],
            'strengths' => 'Outstanding performance in all areas',
            'areas_for_improvement' => 'Continue to develop leadership skills',
            'overall_rating' => 5,
            'status' => 'completed'
        ];

        $response = $this->makeRequest($this->baseUrl . '?action=update', 'PUT', $updateData);
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->fail('Invalid JSON response: ' . json_last_error_msg() . "\nResponse: " . $response);
        }

        $this->assertTrue($data['success']);
        $this->assertNotEmpty($data['message']);
    }

    public function testUpdateNonExistentEvaluation()
    {
        $updateData = [
            'id' => 999,
            'overall_rating' => 5,
            'status' => 'completed'
        ];

        $response = $this->makeRequest($this->baseUrl . '?action=update', 'PUT', $updateData);
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->fail('Invalid JSON response: ' . json_last_error_msg() . "\nResponse: " . $response);
        }

        $this->assertFalse($data['success']);
        $this->assertNotEmpty($data['message']);
    }

    public function testSubmitEvaluation()
    {
        $submissionData = [
            'id' => 1,
            'submitted_by' => 2,
            'submission_date' => date('Y-m-d'),
            'notes' => 'Evaluation submitted for review'
        ];

        $response = $this->makeRequest($this->baseUrl . '?action=submit', 'POST', $submissionData);
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->fail('Invalid JSON response: ' . json_last_error_msg() . "\nResponse: " . $response);
        }

        $this->assertTrue($data['success']);
        $this->assertNotEmpty($data['message']);
    }

    public function testSubmitNonExistentEvaluation()
    {
        $submissionData = [
            'id' => 999,
            'submitted_by' => 2,
            'submission_date' => date('Y-m-d'),
            'notes' => 'Evaluation submitted for review'
        ];

        $response = $this->makeRequest($this->baseUrl . '?action=submit', 'POST', $submissionData);
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->fail('Invalid JSON response: ' . json_last_error_msg() . "\nResponse: " . $response);
        }

        $this->assertFalse($data['success']);
        $this->assertNotEmpty($data['message']);
    }

    public function testGetEmployeePerformanceHistory()
    {
        $params = [
            'employee_id' => 1,
            'start_date' => date('Y-m-d', strtotime('-1 year')),
            'end_date' => date('Y-m-d')
        ];

        $response = $this->makeRequest($this->baseUrl . '?action=history&' . http_build_query($params));
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->fail('Invalid JSON response: ' . json_last_error_msg() . "\nResponse: " . $response);
        }

        $this->assertTrue($data['success']);
        $this->assertIsArray($data['history']);
        $this->assertArrayHasKey('evaluations', $data['history']);
        $this->assertArrayHasKey('average_ratings', $data['history']);
        $this->assertArrayHasKey('improvement_areas', $data['history']);
    }

    public function testGetDepartmentPerformanceReport()
    {
        $params = [
            'department_id' => 1,
            'evaluation_period' => 'Q1 2024'
        ];

        $response = $this->makeRequest($this->baseUrl . '?action=department_report&' . http_build_query($params));
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->fail('Invalid JSON response: ' . json_last_error_msg() . "\nResponse: " . $response);
        }

        $this->assertTrue($data['success']);
        $this->assertIsArray($data['report']);
        $this->assertArrayHasKey('total_employees', $data['report']);
        $this->assertArrayHasKey('average_ratings', $data['report']);
        $this->assertArrayHasKey('performance_distribution', $data['report']);
        $this->assertArrayHasKey('top_performers', $data['report']);
    }
} 