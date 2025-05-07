<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Tests\Traits\AuthTrait;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Position;

class EmployeeTest extends TestCase
{
    use AuthTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->login();
    }

    public function testCreateEmployee()
    {
        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'phone' => '0123456789',
            'birth_date' => '1990-01-01',
            'gender' => 'male',
            'national_id' => '123456789',
            'tax_code' => '1234567890',
            'address' => '123 Main St',
            'bank_account' => '1234567890',
            'bank_name' => 'Vietcombank',
            'department_id' => 1,
            'position_id' => 1,
            'emergency_contact' => [
                'name' => 'Jane Doe',
                'relationship' => 'Spouse',
                'phone' => '0987654321'
            ],
            'education' => [
                [
                    'degree' => 'Bachelor',
                    'major' => 'Computer Science',
                    'school' => 'University of Technology',
                    'graduation_year' => 2012
                ]
            ],
            'work_experience' => [
                [
                    'company' => 'ABC Company',
                    'position' => 'Software Engineer',
                    'start_date' => '2012-01-01',
                    'end_date' => '2015-12-31',
                    'description' => 'Developed web applications'
                ]
            ]
        ];

        $response = $this->makeRequest(
            'http://localhost/QLNhanSu_version1/api/employees.php?action=create',
            'POST',
            $data
        );

        $this->assertTrue($response['success']);
        $this->assertArrayHasKey('employee_id', $response['data']);
    }

    public function testCreateEmployeeWithInvalidData()
    {
        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'invalid-email',
            'phone' => '0123456789',
            'birth_date' => '1990-01-01',
            'gender' => 'male',
            'national_id' => '123456789',
            'tax_code' => '1234567890',
            'address' => '123 Main St',
            'bank_account' => '1234567890',
            'bank_name' => 'Vietcombank',
            'department_id' => 1,
            'position_id' => 1
        ];

        $response = $this->makeRequest(
            'http://localhost/QLNhanSu_version1/api/employees.php?action=create',
            'POST',
            $data
        );

        $this->assertFalse($response['success']);
        $this->assertArrayHasKey('errors', $response);
    }

    public function testGetEmployeeList()
    {
        $response = $this->makeRequest(
            'http://localhost/QLNhanSu_version1/api/employees.php?action=list'
        );

        $this->assertTrue($response['success']);
        $this->assertArrayHasKey('employees', $response['data']);
    }

    public function testGetEmployeeDetails()
    {
        $response = $this->makeRequest(
            'http://localhost/QLNhanSu_version1/api/employees.php?action=get&id=1'
        );

        $this->assertTrue($response['success']);
        $this->assertArrayHasKey('employee', $response['data']);
    }

    public function testGetNonExistentEmployee()
    {
        $response = $this->makeRequest(
            'http://localhost/QLNhanSu_version1/api/employees.php?action=get&id=999'
        );

        $this->assertFalse($response['success']);
        $this->assertArrayHasKey('errors', $response);
    }

    public function testUpdateEmployee()
    {
        $data = [
            'id' => 1,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'phone' => '0123456789',
            'birth_date' => '1990-01-01',
            'gender' => 'male',
            'national_id' => '123456789',
            'tax_code' => '1234567890',
            'address' => '123 Main St',
            'bank_account' => '1234567890',
            'bank_name' => 'Vietcombank',
            'department_id' => 1,
            'position_id' => 1,
            'status' => 'active',
            'emergency_contact' => [
                'name' => 'Jane Doe',
                'relationship' => 'Spouse',
                'phone' => '0987654321'
            ]
        ];

        $response = $this->makeRequest(
            'http://localhost/QLNhanSu_version1/api/employees.php?action=update',
            'PUT',
            $data
        );

        $this->assertTrue($response['success']);
    }

    public function testUpdateNonExistentEmployee()
    {
        $data = [
            'id' => 999,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'phone' => '0123456789',
            'birth_date' => '1990-01-01',
            'gender' => 'male',
            'national_id' => '123456789',
            'tax_code' => '1234567890',
            'address' => '123 Main St',
            'bank_account' => '1234567890',
            'bank_name' => 'Vietcombank',
            'department_id' => 1,
            'position_id' => 1,
            'status' => 'active'
        ];

        $response = $this->makeRequest(
            'http://localhost/QLNhanSu_version1/api/employees.php?action=update',
            'PUT',
            $data
        );

        $this->assertFalse($response['success']);
        $this->assertArrayHasKey('errors', $response);
    }

    public function testDeactivateEmployee()
    {
        $response = $this->makeRequest(
            'http://localhost/QLNhanSu_version1/api/employees.php?action=deactivate&id=1'
        );

        $this->assertTrue($response['success']);
    }

    public function testDeactivateNonExistentEmployee()
    {
        $response = $this->makeRequest(
            'http://localhost/QLNhanSu_version1/api/employees.php?action=deactivate&id=999'
        );

        $this->assertFalse($response['success']);
        $this->assertArrayHasKey('errors', $response);
    }

    public function testGetEmployeeDocuments()
    {
        $response = $this->makeRequest(
            'http://localhost/QLNhanSu_version1/api/employees.php?action=documents&id=1'
        );

        $this->assertTrue($response['success']);
        $this->assertArrayHasKey('documents', $response['data']);
    }

    public function testGetEmployeeReport()
    {
        $response = $this->makeRequest(
            'http://localhost/QLNhanSu_version1/api/employees.php?action=report&id=1'
        );

        $this->assertTrue($response['success']);
        $this->assertArrayHasKey('report', $response['data']);
    }
} 