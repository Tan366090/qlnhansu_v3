<?php
// Test file for chat_widget.php

// Start output buffering to capture any errors
ob_start();

// Include the chat widget file
include 'chat_widget.php';

// Get the output
$output = ob_get_clean();

// Test cases
$tests = array();

// Test 1: Check if file exists
$tests[] = array(
    'name' => 'File Existence',
    'result' => file_exists('chat_widget.php'),
    'expected' => true
);

// Test 2: Check if output contains essential HTML elements
$tests[] = array(
    'name' => 'Essential HTML Elements',
    'result' => (
        strpos($output, 'chat-widget-box') !== false &&
        strpos($output, 'chat-widget-header') !== false &&
        strpos($output, 'chat-widget-messages') !== false &&
        strpos($output, 'chat-widget-input') !== false
    ),
    'expected' => true
);

// Test 3: Check if required CSS is present
$tests[] = array(
    'name' => 'Required CSS',
    'result' => (
        strpos($output, 'font-awesome') !== false &&
        strpos($output, 'bootstrap') !== false
    ),
    'expected' => true
);

// Test 4: Check if required JavaScript is present
$tests[] = array(
    'name' => 'Required JavaScript',
    'result' => (
        strpos($output, 'jquery') !== false &&
        strpos($output, 'process_chat.php') !== false
    ),
    'expected' => true
);

// Test 5: Check if initial message is present
$tests[] = array(
    'name' => 'Initial Message',
    'result' => strpos($output, 'Xin chào! Tôi có thể giúp gì cho bạn về hệ thống quản lý nhân sự?') !== false,
    'expected' => true
);

// Test 6: Check if suggestion chips are present
$tests[] = array(
    'name' => 'Suggestion Chips',
    'result' => (
        strpos($output, 'Tổng số nhân viên') !== false &&
        strpos($output, 'Thông tin phòng ban') !== false &&
        strpos($output, 'Thống kê lương') !== false
    ),
    'expected' => true
);

// Test 7: Check if emoji picker is present
$tests[] = array(
    'name' => 'Emoji Picker',
    'result' => strpos($output, 'emoji-picker') !== false,
    'expected' => true
);

// Test 8: Check if file upload functionality is present
$tests[] = array(
    'name' => 'File Upload',
    'result' => (
        strpos($output, 'file-upload') !== false &&
        strpos($output, 'file-preview') !== false
    ),
    'expected' => true
);

// Test 9: Check if message actions are present
$tests[] = array(
    'name' => 'Message Actions',
    'result' => strpos($output, 'message-actions') !== false,
    'expected' => true
);

// Add styling for better presentation
echo '<style>
    body { 
        font-family: Arial, sans-serif; 
        margin: 20px;
        background: #f0f2f5;
    }
    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    h1 { 
        color: #333;
        text-align: center;
        margin-bottom: 30px;
    }
    table { 
        width: 100%; 
        margin: 20px 0;
        border-collapse: collapse;
        background: white;
    }
    th { 
        background: #f8f9fa;
        padding: 12px;
        text-align: left;
        border-bottom: 2px solid #dee2e6;
    }
    td { 
        padding: 12px;
        border-bottom: 1px solid #dee2e6;
    }
    .preview-container { 
        background: white;
        padding: 20px;
        border-radius: 10px;
        margin: 20px 0;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .preview-container iframe {
        width: 100%;
        height: 600px;
        border: 1px solid #dee2e6;
        border-radius: 5px;
    }
    .summary-box {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 10px;
        margin: 20px 0;
        text-align: center;
    }
    .summary-box h2 {
        color: #333;
        margin-bottom: 15px;
    }
    .summary-box p {
        margin: 10px 0;
        font-size: 1.1em;
    }
    .pass-rate {
        font-size: 1.5em;
        font-weight: bold;
        color: #28a745;
    }
</style>';

echo '<div class="container">';

// Display test results
echo "<h1>Chat Widget Test Results</h1>";
echo "<table>";
echo "<tr><th>Test Name</th><th>Result</th><th>Status</th></tr>";

foreach ($tests as $test) {
    $status = $test['result'] === $test['expected'] ? 'PASS' : 'FAIL';
    $color = $status === 'PASS' ? '#28a745' : '#dc3545';
    
    echo "<tr>";
    echo "<td>{$test['name']}</td>";
    echo "<td>" . ($test['result'] ? 'true' : 'false') . "</td>";
    echo "<td style='color: {$color}; font-weight: bold;'>{$status}</td>";
    echo "</tr>";
}

echo "</table>";

// Display any errors that occurred
$errors = error_get_last();
if ($errors !== null) {
    echo "<div class='preview-container'>";
    echo "<h2>Errors:</h2>";
    echo "<pre>";
    print_r($errors);
    echo "</pre>";
    echo "</div>";
}

// Display the actual widget in an iframe
echo "<div class='preview-container'>";
echo "<h2>Widget Preview</h2>";
echo "<iframe src='chat_widget.php'></iframe>";
echo "</div>";

// Display the raw output for debugging
echo "<div class='preview-container'>";
echo "<h2>Raw Output Preview</h2>";
echo "<pre style='max-height: 300px; overflow-y: auto;'>";
echo htmlspecialchars(substr($output, 0, 2000)) . "..."; // Show first 2000 characters
echo "</pre>";
echo "</div>";

// Add summary
$totalTests = count($tests);
$passedTests = count(array_filter($tests, function($test) { return $test['result'] === $test['expected']; }));
$passRate = ($passedTests / $totalTests) * 100;

echo "<div class='summary-box'>";
echo "<h2>Test Summary</h2>";
echo "<p>Total Tests: {$totalTests}</p>";
echo "<p>Passed Tests: {$passedTests}</p>";
echo "<p class='pass-rate'>Pass Rate: {$passRate}%</p>";
echo "</div>";

echo '</div>'; // Close container
?> 