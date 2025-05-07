<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use App\Models\Payroll;
use App\Models\Employee;

class PayrollTest extends TestCase
{
    private $baseUrl = 'http://localhost/QLNhanSu_version1/api/payroll.php';
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

    public function testGeneratePayroll()
    {
        $payrollData = [
            'employee_id' => 1,
            'month' => date('m'),
            'year' => date('Y'),
            'work_days' => 22,
            'basic_salary' => 10000000,
            'allowances' => [
                ['type' => 'transport', 'amount' => 500000],
                ['type' => 'meal', 'amount' => 300000]
            ],
            'deductions' => [
                ['type' => 'tax', 'amount' => 1000000],
                ['type' => 'insurance', 'amount' => 500000]
            ]
        ];

        $response = $this->makeRequest($this->baseUrl . '?action=generate', 'POST', $payrollData);
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->fail('Invalid JSON response: ' . json_last_error_msg() . "\nResponse: " . $response);
        }

        $this->assertTrue($data['success']);
        $this->assertNotEmpty($data['payroll_id']);
    }

    public function testGeneratePayrollWithInvalidData()
    {
        $payrollData = [
            'employee_id' => 999, // Non-existent employee
            'month' => 13, // Invalid month
            'year' => -2024, // Invalid year
            'work_days' => -22, // Invalid work days
            'basic_salary' => -10000000, // Invalid salary
            'allowances' => [
                ['type' => '', 'amount' => -500000] // Invalid allowance
            ],
            'deductions' => [
                ['type' => '', 'amount' => -1000000] // Invalid deduction
            ]
        ];

        $response = $this->makeRequest($this->baseUrl . '?action=generate', 'POST', $payrollData);
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->fail('Invalid JSON response: ' . json_last_error_msg() . "\nResponse: " . $response);
        }

        $this->assertFalse($data['success']);
        $this->assertNotEmpty($data['errors']);
    }

    public function testGetPayrollList()
    {
        $params = [
            'employee_id' => 1,
            'month' => date('m'),
            'year' => date('Y')
        ];

        $response = $this->makeRequest($this->baseUrl . '?action=list&' . http_build_query($params));
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->fail('Invalid JSON response: ' . json_last_error_msg() . "\nResponse: " . $response);
        }

        $this->assertTrue($data['success']);
        $this->assertIsArray($data['payrolls']);
    }

    public function testGetPayrollDetails()
    {
        $response = $this->makeRequest($this->baseUrl . '?action=get&id=1');
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->fail('Invalid JSON response: ' . json_last_error_msg() . "\nResponse: " . $response);
        }

        $this->assertTrue($data['success']);
        $this->assertIsArray($data['payroll']);
        $this->assertEquals(1, $data['payroll']['id']);
    }

    public function testGetNonExistentPayroll()
    {
        $response = $this->makeRequest($this->baseUrl . '?action=get&id=999');
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->fail('Invalid JSON response: ' . json_last_error_msg() . "\nResponse: " . $response);
        }

        $this->assertFalse($data['success']);
        $this->assertNotEmpty($data['message']);
    }

    public function testUpdatePayroll()
    {
        $updateData = [
            'id' => 1,
            'work_days' => 23,
            'basic_salary' => 11000000,
            'allowances' => [
                ['type' => 'transport', 'amount' => 600000],
                ['type' => 'meal', 'amount' => 400000]
            ],
            'deductions' => [
                ['type' => 'tax', 'amount' => 1100000],
                ['type' => 'insurance', 'amount' => 600000]
            ]
        ];

        $response = $this->makeRequest($this->baseUrl . '?action=update', 'PUT', $updateData);
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->fail('Invalid JSON response: ' . json_last_error_msg() . "\nResponse: " . $response);
        }

        $this->assertTrue($data['success']);
        $this->assertNotEmpty($data['message']);
    }

    public function testUpdateNonExistentPayroll()
    {
        $updateData = [
            'id' => 999,
            'work_days' => 23,
            'basic_salary' => 11000000
        ];

        $response = $this->makeRequest($this->baseUrl . '?action=update', 'PUT', $updateData);
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->fail('Invalid JSON response: ' . json_last_error_msg() . "\nResponse: " . $response);
        }

        $this->assertFalse($data['success']);
        $this->assertNotEmpty($data['message']);
    }

    public function testApprovePayroll()
    {
        $response = $this->makeRequest($this->baseUrl . '?action=approve&id=1', 'POST');
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->fail('Invalid JSON response: ' . json_last_error_msg() . "\nResponse: " . $response);
        }

        $this->assertTrue($data['success']);
        $this->assertNotEmpty($data['message']);
    }

    public function testApproveNonExistentPayroll()
    {
        $response = $this->makeRequest($this->baseUrl . '?action=approve&id=999', 'POST');
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->fail('Invalid JSON response: ' . json_last_error_msg() . "\nResponse: " . $response);
        }

        $this->assertFalse($data['success']);
        $this->assertNotEmpty($data['message']);
    }

    public function testRejectPayroll()
    {
        $rejectData = [
            'id' => 1,
            'reason' => 'Incorrect calculation'
        ];

        $response = $this->makeRequest($this->baseUrl . '?action=reject', 'POST', $rejectData);
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->fail('Invalid JSON response: ' . json_last_error_msg() . "\nResponse: " . $response);
        }

        $this->assertTrue($data['success']);
        $this->assertNotEmpty($data['message']);
    }

    public function testRejectNonExistentPayroll()
    {
        $rejectData = [
            'id' => 999,
            'reason' => 'Incorrect calculation'
        ];

        $response = $this->makeRequest($this->baseUrl . '?action=reject', 'POST', $rejectData);
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->fail('Invalid JSON response: ' . json_last_error_msg() . "\nResponse: " . $response);
        }

        $this->assertFalse($data['success']);
        $this->assertNotEmpty($data['message']);
    }

    public function testGetPayrollReport()
    {
        $params = [
            'department_id' => 1,
            'month' => date('m'),
            'year' => date('Y')
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
        $this->assertArrayHasKey('total_salary', $data['report']);
        $this->assertArrayHasKey('total_allowances', $data['report']);
        $this->assertArrayHasKey('total_deductions', $data['report']);
    }

    public function testGetSalaryHistory()
    {
        $params = [
            'employee_id' => 1,
            'start_date' => date('Y-m-d', strtotime('-12 months')),
            'end_date' => date('Y-m-d')
        ];

        $response = $this->makeRequest($this->baseUrl . '?action=history&' . http_build_query($params));
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->fail('Invalid JSON response: ' . json_last_error_msg() . "\nResponse: " . $response);
        }

        $this->assertTrue($data['success']);
        $this->assertIsArray($data['history']);
    }
} 