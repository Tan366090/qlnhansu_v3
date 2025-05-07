<?php
require_once __DIR__ . '/../services/DataService.php';
require_once __DIR__ . '/../services/DataStore.php';

use App\Services\DataService;
use App\Services\DataStore;

class DataLoadingTest {
    private $dataService;
    private $dataStore;
    private $testResults = [];

    public function __construct() {
        $this->dataService = DataService::getInstance();
        $this->dataStore = DataStore::getInstance();
    }

    public function runTests() {
        $this->testBasicDataLoading();
        $this->testFilteredDataLoading();
        $this->testRelatedDataLoading();
        $this->testCacheFunctionality();
        $this->printResults();
    }

    private function testBasicDataLoading() {
        try {
            $tables = ['employees', 'departments', 'positions'];
            foreach ($tables as $table) {
                $data = $this->dataService->getData($table);
                $this->testResults[] = [
                    'test' => "Basic data loading for $table",
                    'status' => !empty($data) ? 'PASS' : 'FAIL',
                    'message' => !empty($data) ? "Successfully loaded data from $table" : "Failed to load data from $table"
                ];
            }
        } catch (Exception $e) {
            $this->testResults[] = [
                'test' => 'Basic data loading',
                'status' => 'FAIL',
                'message' => 'Exception: ' . $e->getMessage()
            ];
        }
    }

    private function testFilteredDataLoading() {
        try {
            $conditions = ['status' => 'active'];
            $data = $this->dataService->getFilteredData('employees', $conditions);
            $this->testResults[] = [
                'test' => 'Filtered data loading',
                'status' => !empty($data) ? 'PASS' : 'FAIL',
                'message' => !empty($data) ? 'Successfully loaded filtered data' : 'Failed to load filtered data'
            ];
        } catch (Exception $e) {
            $this->testResults[] = [
                'test' => 'Filtered data loading',
                'status' => 'FAIL',
                'message' => 'Exception: ' . $e->getMessage()
            ];
        }
    }

    private function testRelatedDataLoading() {
        try {
            $data = $this->dataService->getRelatedData('employees', 'departments', 'department_id', 1);
            $this->testResults[] = [
                'test' => 'Related data loading',
                'status' => !empty($data) ? 'PASS' : 'FAIL',
                'message' => !empty($data) ? 'Successfully loaded related data' : 'Failed to load related data'
            ];
        } catch (Exception $e) {
            $this->testResults[] = [
                'test' => 'Related data loading',
                'status' => 'FAIL',
                'message' => 'Exception: ' . $e->getMessage()
            ];
        }
    }

    private function testCacheFunctionality() {
        try {
            // First load
            $this->dataService->getData('employees');
            $firstLoadTime = microtime(true);

            // Second load (should use cache)
            $this->dataService->getData('employees');
            $secondLoadTime = microtime(true);

            $timeDifference = $secondLoadTime - $firstLoadTime;
            $this->testResults[] = [
                'test' => 'Cache functionality',
                'status' => $timeDifference < 0.1 ? 'PASS' : 'FAIL',
                'message' => $timeDifference < 0.1 ? 'Cache working properly' : 'Cache not working as expected'
            ];
        } catch (Exception $e) {
            $this->testResults[] = [
                'test' => 'Cache functionality',
                'status' => 'FAIL',
                'message' => 'Exception: ' . $e->getMessage()
            ];
        }
    }

    private function printResults() {
        echo "<h2>Data Loading Test Results</h2>";
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Test</th><th>Status</th><th>Message</th></tr>";
        
        foreach ($this->testResults as $result) {
            $color = $result['status'] === 'PASS' ? 'green' : 'red';
            echo "<tr>";
            echo "<td>{$result['test']}</td>";
            echo "<td style='color: {$color}'>{$result['status']}</td>";
            echo "<td>{$result['message']}</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    }
}

// Run the tests
$test = new DataLoadingTest();
$test->runTests(); 