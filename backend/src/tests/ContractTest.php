<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Tests\Traits\AuthTrait;
use App\Models\Contract;
use App\Models\Employee;

class ContractTest extends TestCase
{
    use AuthTrait;

    private $baseUrl = 'http://localhost/QLNhanSu_version1/api/contract.php';
    private $authToken;

    protected function setUp(): void
    {
        parent::setUp();
        // Ensure error reporting is enabled
        error_reporting(E_ALL);
        ini_set('display_errors', 1);

        // Login to get auth token
        try {
            $loginData = $this->login('admin@example.com', '123456');
            $this->authToken = $loginData['token'];
        } catch (\Exception $e) {
            $this->fail('Login failed: ' . $e->getMessage());
        }
    }

    private function makeRequest($url, $method = 'GET', $data = null)
    {
        $response = $this->makeApiRequest($url, $method, $data, $this->authToken);
        
        if ($response['code'] !== 200) {
            $this->fail("API request failed with status code: " . $response['code']);
        }

        return $response['data'];
    }

    public function testCreateContract()
    {
        $contractData = [
            'employee_id' => 1,
            'contract_type' => 'full_time',
            'start_date' => date('Y-m-d'),
            'end_date' => date('Y-m-d', strtotime('+1 year')),
            'salary' => 10000000,
            'position_id' => 1,
            'department_id' => 1,
            'status' => 'active'
        ];

        $data = $this->makeRequest($this->baseUrl . '?action=create', 'POST', $contractData);
        $this->assertTrue($data['success']);
        $this->assertNotEmpty($data['contract_id']);
    }

    public function testCreateContractWithInvalidData()
    {
        $contractData = [
            'employee_id' => 999, // Non-existent employee
            'contract_type' => 'invalid', // Invalid contract type
            'start_date' => 'invalid_date', // Invalid date
            'end_date' => 'invalid_date', // Invalid date
            'salary' => -1000, // Invalid salary
            'position_id' => 999, // Non-existent position
            'department_id' => 999, // Non-existent department
            'status' => 'invalid' // Invalid status
        ];

        $data = $this->makeRequest($this->baseUrl . '?action=create', 'POST', $contractData);
        $this->assertFalse($data['success']);
        $this->assertNotEmpty($data['errors']);
    }

    public function testGetContractList()
    {
        $params = [
            'employee_id' => 1,
            'status' => 'active'
        ];

        $data = $this->makeRequest($this->baseUrl . '?action=list&' . http_build_query($params));
        $this->assertTrue($data['success']);
        $this->assertIsArray($data['contracts']);
    }

    public function testGetContractDetails()
    {
        $data = $this->makeRequest($this->baseUrl . '?action=get&id=1');
        $this->assertTrue($data['success']);
        $this->assertIsArray($data['contract']);
        $this->assertEquals(1, $data['contract']['id']);
    }

    public function testGetNonExistentContract()
    {
        $data = $this->makeRequest($this->baseUrl . '?action=get&id=999');
        $this->assertFalse($data['success']);
        $this->assertNotEmpty($data['message']);
    }

    public function testUpdateContract()
    {
        $updateData = [
            'id' => 1,
            'contract_type' => 'part_time',
            'salary' => 12000000,
            'status' => 'active'
        ];

        $data = $this->makeRequest($this->baseUrl . '?action=update', 'PUT', $updateData);
        $this->assertTrue($data['success']);
        $this->assertNotEmpty($data['message']);
    }

    public function testUpdateNonExistentContract()
    {
        $updateData = [
            'id' => 999,
            'contract_type' => 'full_time',
            'salary' => 10000000
        ];

        $data = $this->makeRequest($this->baseUrl . '?action=update', 'PUT', $updateData);
        $this->assertFalse($data['success']);
        $this->assertNotEmpty($data['message']);
    }

    public function testTerminateContract()
    {
        $data = $this->makeRequest($this->baseUrl . '?action=terminate&id=1');
        $this->assertTrue($data['success']);
        $this->assertNotEmpty($data['message']);
    }

    public function testTerminateNonExistentContract()
    {
        $data = $this->makeRequest($this->baseUrl . '?action=terminate&id=999');
        $this->assertFalse($data['success']);
        $this->assertNotEmpty($data['message']);
    }

    public function testGetContractHistory()
    {
        $data = $this->makeRequest($this->baseUrl . '?action=history&employee_id=1');
        $this->assertTrue($data['success']);
        $this->assertIsArray($data['history']);
    }

    public function testGetContractReport()
    {
        $data = $this->makeRequest($this->baseUrl . '?action=report&department_id=1');
        $this->assertTrue($data['success']);
        $this->assertIsArray($data['report']);
    }
} 