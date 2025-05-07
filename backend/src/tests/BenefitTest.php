<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use App\Models\Benefit;
use App\Models\Employee;

class BenefitTest extends TestCase
{
    private $baseUrl = 'http://localhost/QLNhanSu_version1/api/benefit.php';
    private $authToken;
    private $sessionCookie;

    protected function setUp(): void
    {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $this->login();
    }

    private function login()
    {
        $authTest = new AuthTest();
        $authTest->testLogin();
        $this->authToken = $authTest->getAuthToken();
        $this->sessionCookie = $authTest->getSessionCookie();
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

    public function testCreateBenefit()
    {
        $benefitData = [
            'name' => 'Health Insurance',
            'description' => 'Company health insurance plan',
            'type' => 'insurance',
            'amount' => 5000000,
            'start_date' => date('Y-m-d'),
            'end_date' => date('Y-m-d', strtotime('+1 year')),
            'eligibility_criteria' => 'All full-time employees',
            'documentation_required' => ['ID card', 'Health check report']
        ];

        $response = $this->makeRequest($this->baseUrl . '?action=create', 'POST', $benefitData);
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->fail('Invalid JSON response: ' . json_last_error_msg() . "\nResponse: " . $response);
        }

        $this->assertTrue($data['success']);
        $this->assertNotEmpty($data['benefit_id']);
    }

    public function testCreateBenefitWithInvalidData()
    {
        $benefitData = [
            'name' => '', // Empty name
            'description' => '', // Empty description
            'type' => 'invalid', // Invalid type
            'amount' => -5000000, // Invalid amount
            'start_date' => 'invalid-date', // Invalid date
            'end_date' => date('Y-m-d', strtotime('-1 day')), // End date before start date
            'eligibility_criteria' => '', // Empty criteria
            'documentation_required' => [] // Empty documentation
        ];

        $response = $this->makeRequest($this->baseUrl . '?action=create', 'POST', $benefitData);
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->fail('Invalid JSON response: ' . json_last_error_msg() . "\nResponse: " . $response);
        }

        $this->assertFalse($data['success']);
        $this->assertNotEmpty($data['errors']);
    }

    public function testGetBenefitList()
    {
        $params = [
            'type' => 'insurance',
            'status' => 'active',
            'start_date' => date('Y-m-d'),
            'end_date' => date('Y-m-d', strtotime('+1 year'))
        ];

        $response = $this->makeRequest($this->baseUrl . '?action=list&' . http_build_query($params));
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->fail('Invalid JSON response: ' . json_last_error_msg() . "\nResponse: " . $response);
        }

        $this->assertTrue($data['success']);
        $this->assertIsArray($data['benefits']);
    }

    public function testGetBenefitDetails()
    {
        $response = $this->makeRequest($this->baseUrl . '?action=get&id=1');
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->fail('Invalid JSON response: ' . json_last_error_msg() . "\nResponse: " . $response);
        }

        $this->assertTrue($data['success']);
        $this->assertIsArray($data['benefit']);
        $this->assertEquals(1, $data['benefit']['id']);
    }

    public function testGetNonExistentBenefit()
    {
        $response = $this->makeRequest($this->baseUrl . '?action=get&id=999');
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->fail('Invalid JSON response: ' . json_last_error_msg() . "\nResponse: " . $response);
        }

        $this->assertFalse($data['success']);
        $this->assertNotEmpty($data['message']);
    }

    public function testUpdateBenefit()
    {
        $updateData = [
            'id' => 1,
            'name' => 'Updated Health Insurance',
            'description' => 'Updated company health insurance plan',
            'type' => 'insurance',
            'amount' => 6000000,
            'start_date' => date('Y-m-d'),
            'end_date' => date('Y-m-d', strtotime('+2 years')),
            'eligibility_criteria' => 'All full-time employees with 6+ months tenure',
            'documentation_required' => ['ID card', 'Health check report', 'Employment contract']
        ];

        $response = $this->makeRequest($this->baseUrl . '?action=update', 'PUT', $updateData);
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->fail('Invalid JSON response: ' . json_last_error_msg() . "\nResponse: " . $response);
        }

        $this->assertTrue($data['success']);
        $this->assertNotEmpty($data['message']);
    }

    public function testUpdateNonExistentBenefit()
    {
        $updateData = [
            'id' => 999,
            'name' => 'Updated Benefit',
            'description' => 'Updated description'
        ];

        $response = $this->makeRequest($this->baseUrl . '?action=update', 'PUT', $updateData);
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->fail('Invalid JSON response: ' . json_last_error_msg() . "\nResponse: " . $response);
        }

        $this->assertFalse($data['success']);
        $this->assertNotEmpty($data['message']);
    }

    public function testAssignBenefitToEmployee()
    {
        $assignmentData = [
            'benefit_id' => 1,
            'employee_id' => 1,
            'start_date' => date('Y-m-d'),
            'end_date' => date('Y-m-d', strtotime('+1 year')),
            'status' => 'active',
            'notes' => 'Regular benefit assignment'
        ];

        $response = $this->makeRequest($this->baseUrl . '?action=assign', 'POST', $assignmentData);
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->fail('Invalid JSON response: ' . json_last_error_msg() . "\nResponse: " . $response);
        }

        $this->assertTrue($data['success']);
        $this->assertNotEmpty($data['assignment_id']);
    }

    public function testAssignNonExistentBenefit()
    {
        $assignmentData = [
            'benefit_id' => 999,
            'employee_id' => 1,
            'start_date' => date('Y-m-d'),
            'end_date' => date('Y-m-d', strtotime('+1 year')),
            'status' => 'active'
        ];

        $response = $this->makeRequest($this->baseUrl . '?action=assign', 'POST', $assignmentData);
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->fail('Invalid JSON response: ' . json_last_error_msg() . "\nResponse: " . $response);
        }

        $this->assertFalse($data['success']);
        $this->assertNotEmpty($data['message']);
    }

    public function testGetEmployeeBenefits()
    {
        $response = $this->makeRequest($this->baseUrl . '?action=employee_benefits&employee_id=1');
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->fail('Invalid JSON response: ' . json_last_error_msg() . "\nResponse: " . $response);
        }

        $this->assertTrue($data['success']);
        $this->assertIsArray($data['benefits']);
    }

    public function testGetBenefitUsageReport()
    {
        $params = [
            'benefit_id' => 1,
            'start_date' => date('Y-m-d', strtotime('-6 months')),
            'end_date' => date('Y-m-d')
        ];

        $response = $this->makeRequest($this->baseUrl . '?action=usage_report&' . http_build_query($params));
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->fail('Invalid JSON response: ' . json_last_error_msg() . "\nResponse: " . $response);
        }

        $this->assertTrue($data['success']);
        $this->assertIsArray($data['report']);
        $this->assertArrayHasKey('total_employees', $data['report']);
        $this->assertArrayHasKey('total_cost', $data['report']);
        $this->assertArrayHasKey('usage_stats', $data['report']);
    }

    public function testGetBenefitCostReport()
    {
        $params = [
            'start_date' => date('Y-m-d', strtotime('-12 months')),
            'end_date' => date('Y-m-d')
        ];

        $response = $this->makeRequest($this->baseUrl . '?action=cost_report&' . http_build_query($params));
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->fail('Invalid JSON response: ' . json_last_error_msg() . "\nResponse: " . $response);
        }

        $this->assertTrue($data['success']);
        $this->assertIsArray($data['report']);
        $this->assertArrayHasKey('total_cost', $data['report']);
        $this->assertArrayHasKey('cost_by_type', $data['report']);
        $this->assertArrayHasKey('cost_by_department', $data['report']);
    }
} 