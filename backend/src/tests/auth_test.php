<?php
// Start output buffering
ob_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../api/middleware/CORSMiddleware.php';

class AuthTest {
    private $db;
    private $testUser = [
        'username' => 'test_user',
        'password' => 'test_password',
        'email' => 'test@example.com',
        'role_name' => 'employee'
    ];
    private $output = '';
    private $cors;
    private $passedTests = 0;
    private $failedTests = 0;
    private $testDetails = [];

    public function __construct() {
        $database = Database::getInstance();
        $this->db = $database->getConnection();
        $this->cors = new CORSMiddleware();
    }

    public function runTests() {
        // Buffer all output
        ob_start();
        
        // Run all tests
        $this->testCORSHeaders();
        $this->testPreflightRequest();
        $this->testInvalidMethod();
        $this->testInvalidJson();
        $this->testMissingCredentials();
        $this->testInvalidCredentials();
        $this->testValidLogin();
        $this->testSessionCreation();
        $this->cleanup();
        
        // Get buffered output and clear buffer
        $testOutput = ob_get_clean();
        
        // Format and return results
        $this->output .= "\n=== Auth Test Results ===\n\n";
        $this->output .= $testOutput;
        
        return [
            'passed' => $this->passedTests,
            'failed' => $this->failedTests,
            'details' => $this->testDetails,
            'output' => $this->output
        ];
    }

    private function log($message, $testName = null, $status = null) {
        $this->output .= $message . "\n";
        
        if ($testName && $status) {
            $this->testDetails[$testName] = $status;
            if ($status === 'pass') {
                $this->passedTests++;
            } else {
                $this->failedTests++;
            }
        }
    }

    private function testCORSHeaders() {
        $this->log("Testing CORS Headers...");
        
        // Clear any existing headers
        header_remove();
        
        // Call CORS middleware
        $this->cors->handleCORS();
        
        // Get all headers
        $headers = headers_list();
        $requiredHeaders = [
            'Access-Control-Allow-Origin: http://localhost:3000',
            'Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS',
            'Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With',
            'Access-Control-Allow-Credentials: true'
        ];

        $allPresent = true;
        foreach ($requiredHeaders as $header) {
            $found = false;
            foreach ($headers as $h) {
                if (strcasecmp($h, $header) === 0) {
                    $found = true;
                    break;
                }
            }
            
            if ($found) {
                $this->log("PASS: Header present: $header");
            } else {
                $this->log("FAIL: Missing header: $header");
                $allPresent = false;
            }
        }

        if ($allPresent) {
            $this->log("PASS: All CORS headers present", "CORS Headers", "pass");
        } else {
            $this->log("FAIL: Some CORS headers missing", "CORS Headers", "fail");
        }
    }

    private function testPreflightRequest() {
        $this->log("\nTesting Preflight Request...");
        
        // Clear any existing headers and output buffers
        while (ob_get_level()) ob_end_clean();
        header_remove();
        
        $_SERVER['REQUEST_METHOD'] = 'OPTIONS';
        
        // Call CORS middleware
        $this->cors->handleCORS();
        
        if (http_response_code() === 200) {
            $this->log("PASS: Preflight request handled correctly", "Preflight Request", "pass");
        } else {
            $this->log("FAIL: Preflight request not handled correctly", "Preflight Request", "fail");
        }
    }

    private function testInvalidMethod() {
        $this->log("\nTesting Invalid Method...");
        
        // Clear any existing headers and output buffers
        while (ob_get_level()) ob_end_clean();
        header_remove();
        
        $_SERVER['REQUEST_METHOD'] = 'GET';
        
        // Call CORS middleware
        $this->cors->handleCORS();
        
        ob_start();
        require __DIR__ . '/../api/auth/login.php';
        $output = ob_get_clean();
        $response = json_decode($output, true);
        
        if (http_response_code() === 405 && 
            isset($response['success']) && 
            $response['success'] === false) {
            $this->log("PASS: Invalid method handled correctly", "Invalid Method", "pass");
        } else {
            $this->log("FAIL: Invalid method not handled correctly", "Invalid Method", "fail");
        }
    }

    private function testInvalidJson() {
        $this->log("\nTesting Invalid JSON...");
        
        // Clear any existing headers and output buffers
        while (ob_get_level()) ob_end_clean();
        header_remove();
        
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['invalid'] = 'data';
        
        // Call CORS middleware
        $this->cors->handleCORS();
        
        ob_start();
        require __DIR__ . '/../api/auth/login.php';
        $output = ob_get_clean();
        $response = json_decode($output, true);
        
        if (http_response_code() === 400 && 
            isset($response['success']) && 
            $response['success'] === false) {
            $this->log("PASS: Invalid JSON handled correctly", "Invalid JSON", "pass");
        } else {
            $this->log("FAIL: Invalid JSON not handled correctly", "Invalid JSON", "fail");
        }
    }

    private function testMissingCredentials() {
        $this->log("\nTesting Missing Credentials...");
        
        // Clear any existing headers and output buffers
        while (ob_get_level()) ob_end_clean();
        header_remove();
        
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [];
        
        // Call CORS middleware
        $this->cors->handleCORS();
        
        ob_start();
        require __DIR__ . '/../api/auth/login.php';
        $output = ob_get_clean();
        $response = json_decode($output, true);
        
        if (http_response_code() === 400 && 
            isset($response['success']) && 
            $response['success'] === false) {
            $this->log("PASS: Missing credentials handled correctly", "Missing Credentials", "pass");
        } else {
            $this->log("FAIL: Missing credentials not handled correctly", "Missing Credentials", "fail");
        }
    }

    private function testInvalidCredentials() {
        $this->log("\nTesting Invalid Credentials...");
        
        // Clear any existing headers and output buffers
        while (ob_get_level()) ob_end_clean();
        header_remove();
        
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'username' => 'invalid_user',
            'password' => 'invalid_pass'
        ];
        
        // Call CORS middleware
        $this->cors->handleCORS();
        
        ob_start();
        require __DIR__ . '/../api/auth/login.php';
        $output = ob_get_clean();
        $response = json_decode($output, true);
        
        if (http_response_code() === 200 && 
            isset($response['success']) && 
            $response['success'] === false) {
            $this->log("PASS: Invalid credentials handled correctly", "Invalid Credentials", "pass");
        } else {
            $this->log("FAIL: Invalid credentials not handled correctly", "Invalid Credentials", "fail");
        }
    }

    private function testValidLogin() {
        $this->log("\nTesting Valid Login...");
        
        // Clear any existing headers and output buffers
        while (ob_get_level()) ob_end_clean();
        header_remove();
        
        // Create test user
        $this->createTestUser();
        
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'username' => $this->testUser['username'],
            'password' => $this->testUser['password']
        ];
        
        // Call CORS middleware
        $this->cors->handleCORS();
        
        ob_start();
        require __DIR__ . '/../api/auth/login.php';
        $output = ob_get_clean();
        $response = json_decode($output, true);
        
        if (http_response_code() === 200 && 
            isset($response['success']) && 
            $response['success'] === true &&
            isset($response['user']) &&
            isset($response['session_id'])) {
            $this->log("PASS: Valid login handled correctly", "Valid Login", "pass");
        } else {
            $this->log("FAIL: Valid login not handled correctly", "Valid Login", "fail");
        }
    }

    private function testSessionCreation() {
        $this->log("\nTesting Session Creation...");
        
        if (isset($_SESSION['user']) && 
            isset($_SESSION['logged_in']) && 
            $_SESSION['logged_in'] === true) {
            $this->log("PASS: Session created correctly", "Session Creation", "pass");
        } else {
            $this->log("FAIL: Session not created correctly", "Session Creation", "fail");
        }
    }

    private function createTestUser() {
        $query = "INSERT INTO users (username, password_hash, email, role_name) 
                 VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            $this->testUser['username'],
            md5($this->testUser['password']),
            $this->testUser['email'],
            $this->testUser['role_name']
        ]);
    }

    private function cleanup() {
        $this->log("\nCleaning up test data...");
        
        $query = "DELETE FROM users WHERE username = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$this->testUser['username']]);
        
        $this->log("Cleanup completed");
    }
}

// Run tests
$test = new AuthTest();
$test->runTests();

// End output buffering
ob_end_flush();
?> 