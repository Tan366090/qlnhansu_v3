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
    }
    
    private function testSimpleMessage() {
        echo "Test 1: Tin nhắn đơn giản\n";
        echo "Input: 'Xin chào'\n";
        
        $response = $this->chatEngine->processQuery('Xin chào');
        $this->printResponse($response);
        echo "----------------------------------------\n\n";
    }
    
    private function testEmployeeQuery() {
        echo "Test 2: Câu hỏi về nhân viên\n";
        echo "Input: 'Có bao nhiêu nhân viên trong công ty?'\n";
        
        $response = $this->chatEngine->processQuery('Có bao nhiêu nhân viên trong công ty?');
        $this->printResponse($response);
        echo "----------------------------------------\n\n";
    }
    
    private function testDepartmentQuery() {
        echo "Test 3: Câu hỏi về phòng ban\n";
        echo "Input: 'Thống kê số lượng nhân viên theo phòng ban'\n";
        
        $response = $this->chatEngine->processQuery('Thống kê số lượng nhân viên theo phòng ban');
        $this->printResponse($response);
        echo "----------------------------------------\n\n";
    }
    
    private function testSalaryQuery() {
        echo "Test 4: Câu hỏi về lương\n";
        echo "Input: 'Lương trung bình của nhân viên là bao nhiêu?'\n";
        
        $response = $this->chatEngine->processQuery('Lương trung bình của nhân viên là bao nhiêu?');
        $this->printResponse($response);
        echo "----------------------------------------\n\n";
    }
    
    private function testComplexQuery() {
        echo "Test 5: Câu hỏi phức tạp\n";
        echo "Input: 'So sánh lương trung bình giữa các phòng ban trong năm nay'\n";
        
        $response = $this->chatEngine->processQuery('So sánh lương trung bình giữa các phòng ban trong năm nay');
        $this->printResponse($response);
        echo "----------------------------------------\n\n";
    }
    
    private function testErrorHandling() {
        echo "Test 6: Xử lý lỗi\n";
        echo "Input: '' (tin nhắn rỗng)\n";
        
        $response = $this->chatEngine->processQuery('');
        $this->printResponse($response);
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
}

// Chạy test
$test = new ChatWidgetTest();
$test->runTests();
?> 