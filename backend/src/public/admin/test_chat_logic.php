<?php
include 'headers.php';
require_once 'chat_engine.php';

class ChatWidgetTest {
    private $chatEngine;
    
    public function __construct() {
        $this->chatEngine = new ChatEngine();
    }
    
    public function runTests() {
        echo "=== Bắt đầu test Chat Widget ===\n\n";
        
        // Test 1: Tin nhắn đơn giản
        $this->testSimpleMessage();
        
        // Test 2: Câu hỏi về nhân viên
        $this->testEmployeeQuery();
        
        // Test 3: Câu hỏi về phòng ban
        $this->testDepartmentQuery();
        
        // Test 4: Câu hỏi về lương
        $this->testSalaryQuery();
        
        // Test 5: Câu hỏi phức tạp
        $this->testComplexQuery();
        
        // Test 6: Xử lý lỗi
        $this->testErrorHandling();
        
        // Test 7: Câu hỏi về nghỉ phép
        $this->testLeaveQuery();
        
        // Test 8: Câu hỏi kết hợp
        $this->testCombinedQuery();
    }
    
    private function testSimpleMessage() {
        echo "Test 1: Tin nhắn đơn giản\n";
        echo "Input: 'Xin chào'\n";
        
        $response = $this->chatEngine->processQuery('Xin chào');
        $this->printResponse($response);
        $this->validateResponse($response, [
            'should_contain' => ['Xin chào', 'Rất vui được trò chuyện'],
            'should_not_contain' => ['lương', 'nhân viên', 'phòng ban']
        ]);
        echo "----------------------------------------\n\n";
    }
    
    private function testEmployeeQuery() {
        echo "Test 2: Câu hỏi về nhân viên\n";
        echo "Input: 'Có bao nhiêu nhân viên trong công ty?'\n";
        
        $response = $this->chatEngine->processQuery('Có bao nhiêu nhân viên trong công ty?');
        $this->printResponse($response);
        $this->validateResponse($response, [
            'should_contain' => ['nhân viên', 'tổng số'],
            'should_not_contain' => ['Xin chào', 'Rất vui được trò chuyện']
        ]);
        echo "----------------------------------------\n\n";
    }
    
    private function testDepartmentQuery() {
        echo "Test 3: Câu hỏi về phòng ban\n";
        echo "Input: 'Thống kê số lượng nhân viên theo phòng ban'\n";
        
        $response = $this->chatEngine->processQuery('Thống kê số lượng nhân viên theo phòng ban');
        $this->printResponse($response);
        $this->validateResponse($response, [
            'should_contain' => ['phòng ban', 'nhân viên'],
            'should_have_data' => ['departments']
        ]);
        echo "----------------------------------------\n\n";
    }
    
    private function testSalaryQuery() {
        echo "Test 4: Câu hỏi về lương\n";
        echo "Input: 'Lương trung bình của nhân viên là bao nhiêu?'\n";
        
        $response = $this->chatEngine->processQuery('Lương trung bình của nhân viên là bao nhiêu?');
        $this->printResponse($response);
        $this->validateResponse($response, [
            'should_contain' => ['lương', 'trung bình'],
            'should_have_data' => ['payroll'],
            'should_not_contain' => ['Xin chào', 'Rất vui được trò chuyện']
        ]);
        echo "----------------------------------------\n\n";
    }
    
    private function testComplexQuery() {
        echo "Test 5: Câu hỏi phức tạp\n";
        echo "Input: 'So sánh lương trung bình giữa các phòng ban trong năm nay'\n";
        
        $response = $this->chatEngine->processQuery('So sánh lương trung bình giữa các phòng ban trong năm nay');
        $this->printResponse($response);
        $this->validateResponse($response, [
            'should_contain' => ['lương', 'trung bình', 'phòng ban'],
            'should_have_data' => ['payroll', 'departments']
        ]);
        echo "----------------------------------------\n\n";
    }
    
    private function testErrorHandling() {
        echo "Test 6: Xử lý lỗi\n";
        echo "Input: '' (tin nhắn rỗng)\n";
        
        $response = $this->chatEngine->processQuery('');
        $this->printResponse($response);
        $this->validateResponse($response, [
            'should_be_error' => true,
            'should_contain' => ['lỗi', 'tin nhắn rỗng']
        ]);
        echo "----------------------------------------\n\n";
    }
    
    private function testLeaveQuery() {
        echo "Test 7: Câu hỏi về nghỉ phép\n";
        echo "Input: 'Thống kê nghỉ phép của nhân viên'\n";
        
        $response = $this->chatEngine->processQuery('Thống kê nghỉ phép của nhân viên');
        $this->printResponse($response);
        $this->validateResponse($response, [
            'should_contain' => ['nghỉ phép', 'thống kê'],
            'should_have_data' => ['leaves']
        ]);
        echo "----------------------------------------\n\n";
    }
    
    private function testCombinedQuery() {
        echo "Test 8: Câu hỏi kết hợp\n";
        echo "Input: 'So sánh lương và nghỉ phép giữa các phòng ban'\n";
        
        $response = $this->chatEngine->processQuery('So sánh lương và nghỉ phép giữa các phòng ban');
        $this->printResponse($response);
        $this->validateResponse($response, [
            'should_contain' => ['lương', 'nghỉ phép', 'phòng ban'],
            'should_have_data' => ['payroll', 'leaves', 'departments']
        ]);
        echo "----------------------------------------\n\n";
    }
    
    private function printResponse($response) {
        echo "Response:\n";
        if ($response['success']) {
            echo "- Success: true\n";
            echo "- Message: " . $response['response'] . "\n";
            
            if (isset($response['relevant_data'])) {
                echo "- Relevant Data:\n";
                foreach ($response['relevant_data'] as $table => $data) {
                    echo "  + $table: " . count($data) . " records\n";
                }
            }
            
            if (isset($response['context'])) {
                echo "- Context:\n";
                print_r($response['context']);
            }
        } else {
            echo "- Success: false\n";
            echo "- Error: " . $response['error'] . "\n";
        }
    }
    
    private function validateResponse($response, $rules) {
        echo "\nValidation:\n";
        $passed = true;
        
        if (isset($rules['should_be_error'])) {
            if ($rules['should_be_error'] && $response['success']) {
                echo "- ❌ Response should be an error\n";
                $passed = false;
            }
        }
        
        if (isset($rules['should_contain'])) {
            foreach ($rules['should_contain'] as $text) {
                if (stripos($response['response'], $text) === false) {
                    echo "- ❌ Response should contain: '$text'\n";
                    $passed = false;
                }
            }
        }
        
        if (isset($rules['should_not_contain'])) {
            foreach ($rules['should_not_contain'] as $text) {
                if (stripos($response['response'], $text) !== false) {
                    echo "- ❌ Response should not contain: '$text'\n";
                    $passed = false;
                }
            }
        }
        
        if (isset($rules['should_have_data'])) {
            foreach ($rules['should_have_data'] as $table) {
                if (!isset($response['relevant_data'][$table])) {
                    echo "- ❌ Response should have data from: '$table'\n";
                    $passed = false;
                }
            }
        }
        
        if ($passed) {
            echo "- ✅ All validations passed\n";
        }
    }
}

// Chạy test
$test = new ChatWidgetTest();
$test->runTests();
?> 