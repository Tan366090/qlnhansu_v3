<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set content type to HTML for better readability
header('Content-Type: text/html; charset=utf-8');

echo "<h1>Chat Engine Test</h1>";

try {
    // Test database configuration file
    $dbConfigPath = '../../../src/config/database.php';
    if (!file_exists($dbConfigPath)) {
        throw new Exception("Database configuration file not found at: " . $dbConfigPath);
    }
    echo "<p>Database config file found at: " . $dbConfigPath . "</p>";

    // Include database configuration
    require_once $dbConfigPath;
    echo "<p>Database configuration loaded successfully</p>";

    // Test if Database class exists
    if (!class_exists('Database')) {
        throw new Exception("Database class not found in configuration file");
    }
    echo "<p>Database class found</p>";

    // Test database connection
    try {
        $db = Database::getConnection();
        echo "<p style='color: green;'>Database connection successful</p>";
    } catch (Exception $e) {
        throw new Exception("Failed to connect to database: " . $e->getMessage());
    }

    // Include chat engine
    require_once 'chat_engine.php';
    echo "<p>Chat engine loaded successfully</p>";

    // Create instance of ChatEngine
    $chatEngine = new ChatEngine();

    // Test questions array
    $testQuestions = [
        // Basic questions
        "xin chào",
        "có bao nhiêu nhân viên",
        "danh sách phòng ban",
        
        // Employee questions
        "thông tin nhân viên",
        "lương trung bình",
        "nhân viên mới",
        
        // Department questions
        "phòng ban nào có nhiều nhân viên nhất",
        "thống kê theo phòng ban",
        
        // Salary questions
        "lương cao nhất",
        "so sánh lương giữa các phòng",
        
        // Leave questions
        "thống kê nghỉ phép",
        "nghỉ phép theo tháng"
    ];

    // Function to format output
    function formatOutput($question, $response) {
        echo "<div style='margin: 20px 0; padding: 15px; border: 1px solid #ccc; border-radius: 5px;'>";
        echo "<h3 style='color: #2196F3;'>Câu hỏi: " . htmlspecialchars($question) . "</h3>";
        
        if ($response['success']) {
            echo "<div style='margin: 10px 0; padding: 10px; background: #f5f5f5; border-radius: 3px;'>";
            echo "<strong>Kết quả:</strong><br>";
            echo nl2br(htmlspecialchars($response['response']));
            echo "</div>";
            
            if (!empty($response['relevant_data'])) {
                echo "<div style='margin: 10px 0;'>";
                echo "<strong>Dữ liệu liên quan:</strong><br>";
                foreach ($response['relevant_data'] as $table => $data) {
                    echo "Bảng $table: " . count($data) . " kết quả<br>";
                }
                echo "</div>";
            }
        } else {
            echo "<div style='color: red; margin: 10px 0;'>";
            echo "Lỗi: " . htmlspecialchars($response['error']);
            echo "</div>";
        }
        echo "</div>";
    }

    // Run tests
    echo "<h2>Bắt đầu test các câu hỏi...</h2>";
    
    foreach ($testQuestions as $question) {
        $response = $chatEngine->processQuery($question);
        formatOutput($question, $response);
    }

    echo "<h2>Hoàn thành test!</h2>";

} catch (Exception $e) {
    echo "<div style='color: red; padding: 10px; border: 1px solid red; margin: 10px 0;'>";
    echo "<h3>Error Details:</h3>";
    echo "<p><strong>Message:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>File:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
    if ($e->getPrevious()) {
        echo "<p><strong>Previous Error:</strong> " . $e->getPrevious()->getMessage() . "</p>";
    }
    echo "</div>";
}

// Display PHP info
echo "<h2>PHP Information</h2>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>PDO Drivers: " . implode(", ", PDO::getAvailableDrivers()) . "</p>";
?> 