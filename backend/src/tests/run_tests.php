<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start output buffering
ob_start();

require_once __DIR__ . '/auth_test.php';
require_once __DIR__ . '/../config/database.php';

class TestRunner {
    private $testResults = [];
    private $totalTests = 0;
    private $passedTests = 0;
    private $failedTests = 0;
    private $output = '';

    private function debug($message) {
        $this->output .= "Debug: $message\n";
    }

    public function runTests() {
        $this->debug("Starting test execution...");
        
        try {
            $database = Database::getInstance();
            $db = $database->getConnection();
            $this->debug("Database connection successful");
        } catch (Exception $e) {
            $this->debug("Database connection failed: " . $e->getMessage());
            return;
        }
        
        $this->debug("Running tests...");
        
        // Run PHP tests
        $this->runPHPTests();
        
        // Run JavaScript tests
        $this->runJSTests();

        // Print summary
        $this->printSummary();
        
        // Output all debug messages and test results
        echo $this->output;
    }

    private function runPHPTests() {
        echo "=== Starting Tests ===\n\n";
        
        $authTest = new AuthTest();
        $results = $authTest->runTests();
        
        // Capture PHP test results
        $this->testResults['php'] = [
            'total' => 8, // Number of tests in AuthTest
            'passed' => $results['passed'] ?? 0,
            'failed' => $results['failed'] ?? 0,
            'details' => $results['details'] ?? []
        ];

        // Update total counters
        $this->totalTests += $this->testResults['php']['total'];
        $this->passedTests += $this->testResults['php']['passed'];
        $this->failedTests += $this->testResults['php']['failed'];
        
        echo "\nPHP Tests Complete.\n";
        echo "Passed: " . $this->testResults['php']['passed'] . "\n";
        echo "Failed: " . $this->testResults['php']['failed'] . "\n\n";
    }

    private function runJSTests() {
        echo "Running JavaScript Tests...\n";
        echo "-------------------------\n";
        
        // Create a test user for JavaScript tests
        $this->createTestUser();

        // Run JavaScript tests using cURL
        $this->runJSTest('CORS Test', 'testCORS');
        $this->runJSTest('Login Test', 'testLogin', [
            'username' => 'test_user',
            'password' => 'test_password'
        ]);
        $this->runJSTest('Session Test', 'testSession');
        $this->runJSTest('Error Handling Test', 'testErrorHandling');

        // Cleanup test user
        $this->cleanupTestUser();
        
        echo "\nJavaScript Tests Complete.\n";
        echo "Passed: " . ($this->passedTests - $this->testResults['php']['passed']) . "\n";
        echo "Failed: " . ($this->failedTests - $this->testResults['php']['failed']) . "\n\n";
    }

    private function runJSTest($name, $testFunction, $params = []) {
        echo "Running test: $name... ";
        
        $url = 'http://localhost/QLNhanSu_version1/tests/auth_test.html';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        
        $response = curl_exec($ch);
        curl_close($ch);

        // Parse response and check results
        if (strpos($response, 'class="pass"') !== false) {
            echo "PASS\n";
            $this->passedTests++;
        } else {
            echo "FAIL\n";
            $this->failedTests++;
        }

        $this->totalTests++;
    }

    private function createTestUser() {
        echo "Creating test user... ";
        
        try {
            $database = Database::getInstance();
            $db = $database->getConnection();

            $query = "INSERT INTO users (username, password_hash, email, role_name) 
                     VALUES (?, ?, ?, ?)";
            $stmt = $db->prepare($query);
            $stmt->execute([
                'test_user',
                md5('test_password'),
                'test@example.com',
                'employee'
            ]);
            
            echo "OK\n";
        } catch (Exception $e) {
            echo "FAIL (" . $e->getMessage() . ")\n";
        }
    }

    private function cleanupTestUser() {
        echo "Cleaning up test user... ";
        
        try {
            $database = Database::getInstance();
            $db = $database->getConnection();

            $query = "DELETE FROM users WHERE username = ?";
            $stmt = $db->prepare($query);
            $stmt->execute(['test_user']);
            
            echo "OK\n";
        } catch (Exception $e) {
            echo "FAIL (" . $e->getMessage() . ")\n";
        }
    }

    private function printSummary() {
        echo "\n=== Test Summary ===\n\n";
        
        echo "PHP Tests:\n";
        echo "----------\n";
        echo "Total: " . $this->testResults['php']['total'] . "\n";
        echo "Passed: " . $this->testResults['php']['passed'] . "\n";
        echo "Failed: " . $this->testResults['php']['failed'] . "\n\n";
        
        if ($this->testResults['php']['failed'] > 0) {
            echo "Failed Tests:\n";
            foreach ($this->testResults['php']['details'] as $test => $status) {
                if ($status === 'fail') {
                    echo "- $test\n";
                }
            }
            echo "\n";
        }

        echo "JavaScript Tests:\n";
        echo "----------------\n";
        echo "Total: " . ($this->totalTests - $this->testResults['php']['total']) . "\n";
        echo "Passed: " . ($this->passedTests - $this->testResults['php']['passed']) . "\n";
        echo "Failed: " . ($this->failedTests - $this->testResults['php']['failed']) . "\n\n";

        echo "Overall Results:\n";
        echo "---------------\n";
        echo "Total Tests: " . $this->totalTests . "\n";
        echo "Passed: " . $this->passedTests . "\n";
        echo "Failed: " . $this->failedTests . "\n";
        echo "Success Rate: " . round(($this->passedTests / $this->totalTests) * 100, 2) . "%\n\n";
        
        if ($this->failedTests > 0) {
            echo "❌ Some tests failed\n\n";
        } else {
            echo "✅ All tests passed\n\n";
        }
    }
}

// Run the tests
$runner = new TestRunner();
$runner->runTests();

// End output buffering and flush
ob_end_flush();
?> 