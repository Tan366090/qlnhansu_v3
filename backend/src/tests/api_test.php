<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/auth.php';
require_once __DIR__ . '/../utils/jwt.php';

class ApiTest {
    private $baseUrl = 'http://localhost/qlnhansu_V3/backend/src/api';
    private $token;
    private $testResults = [];

    public function __construct() {
        // Tạo token test
        $this->token = $this->generateTestToken();
    }

    private function generateTestToken() {
        // Tạo JWT token test với user_id = 1
        $userData = [
            'id' => 1,
            'username' => 'admin',
            'role' => 'admin',
            'email' => 'admin@example.com'
        ];
        
        return JWTUtil::generateToken($userData);
    }

    private function makeRequest($endpoint, $method = 'GET', $data = null) {
        $url = $this->baseUrl . $endpoint;
        $ch = curl_init();
        
        $headers = [
            'Authorization: Bearer ' . $this->token,
            'Content-Type: application/json'
        ];

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        } elseif ($method === 'PUT') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        } elseif ($method === 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [
            'status' => $httpCode,
            'response' => json_decode($response, true)
        ];
    }

    private function logTest($endpoint, $method, $expectedStatus, $actualStatus, $response) {
        $this->testResults[] = [
            'endpoint' => $endpoint,
            'method' => $method,
            'expected_status' => $expectedStatus,
            'actual_status' => $actualStatus,
            'success' => $expectedStatus === $actualStatus,
            'response' => $response
        ];
    }

    public function testLeavesApi() {
        echo "Testing Leaves API...\n";

        // Test GET leaves
        $response = $this->makeRequest('/hr/leaves.php');
        $this->logTest('/hr/leaves.php', 'GET', 200, $response['status'], $response['response']);

        // Test GET leaves statistics
        $response = $this->makeRequest('/hr/leaves.php?action=statistics');
        $this->logTest('/hr/leaves.php?action=statistics', 'GET', 200, $response['status'], $response['response']);

        // Test POST new leave
        $leaveData = [
            'employee_id' => 1,
            'leave_type' => 'annual',
            'start_date' => date('Y-m-d'),
            'end_date' => date('Y-m-d', strtotime('+1 day')),
            'reason' => 'Test leave request'
        ];
        $response = $this->makeRequest('/hr/leaves.php', 'POST', $leaveData);
        $this->logTest('/hr/leaves.php', 'POST', 200, $response['status'], $response['response']);

        if (isset($response['response']['leave_id'])) {
            $leaveId = $response['response']['leave_id'];
            
            // Test PUT update leave status
            $updateData = [
                'status' => 'approved',
                'approved_by_user_id' => 1,
                'approver_comments' => 'Test approval'
            ];
            $response = $this->makeRequest("/hr/leaves.php?id={$leaveId}", 'PUT', $updateData);
            $this->logTest("/hr/leaves.php?id={$leaveId}", 'PUT', 200, $response['status'], $response['response']);

            // Test DELETE leave
            $response = $this->makeRequest("/hr/leaves.php?id={$leaveId}", 'DELETE');
            $this->logTest("/hr/leaves.php?id={$leaveId}", 'DELETE', 200, $response['status'], $response['response']);
        }
    }

    public function testNotificationsApi() {
        echo "Testing Notifications API...\n";

        // Test GET notifications
        $response = $this->makeRequest('/notifications.php');
        $this->logTest('/notifications.php', 'GET', 200, $response['status'], $response['response']);

        // Test POST new notification
        $notificationData = [
            'user_id' => 1,
            'title' => 'Test Notification',
            'message' => 'This is a test notification',
            'type' => 'info'
        ];
        $response = $this->makeRequest('/notifications.php', 'POST', $notificationData);
        $this->logTest('/notifications.php', 'POST', 200, $response['status'], $response['response']);

        if (isset($response['response']['notification_id'])) {
            $notificationId = $response['response']['notification_id'];
            
            // Test DELETE notification
            $response = $this->makeRequest("/notifications.php?id={$notificationId}", 'DELETE');
            $this->logTest("/notifications.php?id={$notificationId}", 'DELETE', 200, $response['status'], $response['response']);
        }
    }

    public function testActivitiesApi() {
        echo "Testing Activities API...\n";

        // Test GET activities
        $response = $this->makeRequest('/activities.php');
        $this->logTest('/activities.php', 'GET', 200, $response['status'], $response['response']);

        // Test POST new activity
        $activityData = [
            'title' => 'Test Activity',
            'description' => 'This is a test activity',
            'start_date' => date('Y-m-d'),
            'end_date' => date('Y-m-d', strtotime('+1 day')),
            'location' => 'Test Location',
            'organizer_id' => 1
        ];
        $response = $this->makeRequest('/activities.php', 'POST', $activityData);
        $this->logTest('/activities.php', 'POST', 200, $response['status'], $response['response']);
    }

    public function testHolidaysApi() {
        echo "Testing Holidays API...\n";

        // Test GET holidays
        $response = $this->makeRequest('/holidays.php');
        $this->logTest('/holidays.php', 'GET', 200, $response['status'], $response['response']);

        // Test POST new holiday
        $holidayData = [
            'name' => 'Test Holiday',
            'date' => date('Y-m-d'),
            'description' => 'This is a test holiday'
        ];
        $response = $this->makeRequest('/holidays.php', 'POST', $holidayData);
        $this->logTest('/holidays.php', 'POST', 200, $response['status'], $response['response']);
    }

    public function testAttendanceApi() {
        echo "Testing Attendance API...\n";

        // Test GET attendance
        $response = $this->makeRequest('/attendance.php');
        $this->logTest('/attendance.php', 'GET', 200, $response['status'], $response['response']);

        // Test POST new attendance
        $attendanceData = [
            'employee_id' => 1,
            'check_in' => date('Y-m-d H:i:s'),
            'check_out' => date('Y-m-d H:i:s', strtotime('+8 hours')),
            'status' => 'present'
        ];
        $response = $this->makeRequest('/attendance.php', 'POST', $attendanceData);
        $this->logTest('/attendance.php', 'POST', 200, $response['status'], $response['response']);
    }

    public function runAllTests() {
        $this->testLeavesApi();
        $this->testNotificationsApi();
        $this->testActivitiesApi();
        $this->testHolidaysApi();
        $this->testAttendanceApi();

        $this->printResults();
    }

    public function printResults() {
        echo "\nTest Results Summary:\n";
        echo "====================\n";
        
        $total = count($this->testResults);
        $passed = count(array_filter($this->testResults, function($result) {
            return $result['success'];
        }));
        
        echo "Total Tests: {$total}\n";
        echo "Passed: {$passed}\n";
        echo "Failed: " . ($total - $passed) . "\n\n";
        
        echo "Detailed Results:\n";
        foreach ($this->testResults as $result) {
            $status = $result['success'] ? 'PASS' : 'FAIL';
            echo "[{$status}] {$result['method']} {$result['endpoint']}\n";
            if (!$result['success']) {
                echo "  Expected: {$result['expected_status']}\n";
                echo "  Actual: {$result['actual_status']}\n";
                echo "  Response: " . json_encode($result['response']) . "\n";
            }
        }
    }
}

// Run tests
$tester = new ApiTest();
$tester->runAllTests(); 