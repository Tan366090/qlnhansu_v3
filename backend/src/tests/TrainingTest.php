<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use App\Models\Training;
use App\Models\Employee;

class TrainingTest extends TestCase
{
    private $baseUrl = 'http://localhost/QLNhanSu_version1/api/training.php';
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

    public function testCreateTraining()
    {
        $trainingData = [
            'title' => 'PHP Advanced Training',
            'description' => 'Advanced PHP programming techniques',
            'start_date' => date('Y-m-d', strtotime('+1 week')),
            'end_date' => date('Y-m-d', strtotime('+2 weeks')),
            'trainer' => 'John Doe',
            'location' => 'Training Room 1',
            'max_participants' => 20,
            'cost' => 5000000
        ];

        $response = $this->makeRequest($this->baseUrl . '?action=create', 'POST', $trainingData);
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->fail('Invalid JSON response: ' . json_last_error_msg() . "\nResponse: " . $response);
        }

        $this->assertTrue($data['success']);
        $this->assertNotEmpty($data['training_id']);
    }

    public function testCreateTrainingWithInvalidData()
    {
        $trainingData = [
            'title' => '', // Empty title
            'description' => '', // Empty description
            'start_date' => 'invalid-date', // Invalid date
            'end_date' => date('Y-m-d', strtotime('-1 day')), // End date before start date
            'trainer' => '', // Empty trainer
            'location' => '', // Empty location
            'max_participants' => -10, // Invalid number
            'cost' => -5000000 // Invalid cost
        ];

        $response = $this->makeRequest($this->baseUrl . '?action=create', 'POST', $trainingData);
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->fail('Invalid JSON response: ' . json_last_error_msg() . "\nResponse: " . $response);
        }

        $this->assertFalse($data['success']);
        $this->assertNotEmpty($data['errors']);
    }

    public function testGetTrainingList()
    {
        $params = [
            'status' => 'upcoming',
            'start_date' => date('Y-m-d'),
            'end_date' => date('Y-m-d', strtotime('+1 month'))
        ];

        $response = $this->makeRequest($this->baseUrl . '?action=list&' . http_build_query($params));
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->fail('Invalid JSON response: ' . json_last_error_msg() . "\nResponse: " . $response);
        }

        $this->assertTrue($data['success']);
        $this->assertIsArray($data['trainings']);
    }

    public function testGetTrainingDetails()
    {
        $response = $this->makeRequest($this->baseUrl . '?action=get&id=1');
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->fail('Invalid JSON response: ' . json_last_error_msg() . "\nResponse: " . $response);
        }

        $this->assertTrue($data['success']);
        $this->assertIsArray($data['training']);
        $this->assertEquals(1, $data['training']['id']);
    }

    public function testGetNonExistentTraining()
    {
        $response = $this->makeRequest($this->baseUrl . '?action=get&id=999');
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->fail('Invalid JSON response: ' . json_last_error_msg() . "\nResponse: " . $response);
        }

        $this->assertFalse($data['success']);
        $this->assertNotEmpty($data['message']);
    }

    public function testUpdateTraining()
    {
        $updateData = [
            'id' => 1,
            'title' => 'Updated PHP Training',
            'description' => 'Updated PHP programming techniques',
            'start_date' => date('Y-m-d', strtotime('+2 weeks')),
            'end_date' => date('Y-m-d', strtotime('+3 weeks')),
            'trainer' => 'Jane Smith',
            'location' => 'Training Room 2',
            'max_participants' => 25,
            'cost' => 6000000
        ];

        $response = $this->makeRequest($this->baseUrl . '?action=update', 'PUT', $updateData);
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->fail('Invalid JSON response: ' . json_last_error_msg() . "\nResponse: " . $response);
        }

        $this->assertTrue($data['success']);
        $this->assertNotEmpty($data['message']);
    }

    public function testUpdateNonExistentTraining()
    {
        $updateData = [
            'id' => 999,
            'title' => 'Updated Training',
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

    public function testRegisterForTraining()
    {
        $registrationData = [
            'training_id' => 1,
            'employee_id' => 1,
            'notes' => 'Interested in advanced PHP'
        ];

        $response = $this->makeRequest($this->baseUrl . '?action=register', 'POST', $registrationData);
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->fail('Invalid JSON response: ' . json_last_error_msg() . "\nResponse: " . $response);
        }

        $this->assertTrue($data['success']);
        $this->assertNotEmpty($data['registration_id']);
    }

    public function testRegisterForNonExistentTraining()
    {
        $registrationData = [
            'training_id' => 999,
            'employee_id' => 1,
            'notes' => 'Interested in training'
        ];

        $response = $this->makeRequest($this->baseUrl . '?action=register', 'POST', $registrationData);
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->fail('Invalid JSON response: ' . json_last_error_msg() . "\nResponse: " . $response);
        }

        $this->assertFalse($data['success']);
        $this->assertNotEmpty($data['message']);
    }

    public function testGetTrainingParticipants()
    {
        $response = $this->makeRequest($this->baseUrl . '?action=participants&id=1');
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->fail('Invalid JSON response: ' . json_last_error_msg() . "\nResponse: " . $response);
        }

        $this->assertTrue($data['success']);
        $this->assertIsArray($data['participants']);
    }

    public function testGetTrainingEvaluation()
    {
        $response = $this->makeRequest($this->baseUrl . '?action=evaluation&id=1');
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->fail('Invalid JSON response: ' . json_last_error_msg() . "\nResponse: " . $response);
        }

        $this->assertTrue($data['success']);
        $this->assertIsArray($data['evaluation']);
        $this->assertArrayHasKey('average_rating', $data['evaluation']);
        $this->assertArrayHasKey('feedback', $data['evaluation']);
    }

    public function testSubmitTrainingEvaluation()
    {
        $evaluationData = [
            'training_id' => 1,
            'employee_id' => 1,
            'rating' => 4,
            'feedback' => 'Great training session',
            'suggestions' => 'More hands-on exercises'
        ];

        $response = $this->makeRequest($this->baseUrl . '?action=submit_evaluation', 'POST', $evaluationData);
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->fail('Invalid JSON response: ' . json_last_error_msg() . "\nResponse: " . $response);
        }

        $this->assertTrue($data['success']);
        $this->assertNotEmpty($data['message']);
    }

    public function testGetTrainingReport()
    {
        $params = [
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
        $this->assertArrayHasKey('total_trainings', $data['report']);
        $this->assertArrayHasKey('total_participants', $data['report']);
        $this->assertArrayHasKey('average_rating', $data['report']);
        $this->assertArrayHasKey('total_cost', $data['report']);
    }
} 