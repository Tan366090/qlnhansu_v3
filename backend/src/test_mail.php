<?php
require_once __DIR__ . '/config/mail.php';

// Test data
$testEmail = "test@example.com"; // Thay bằng email của bạn để test
$testName = "Test User";
$testPassword = "test123";

try {
    $mailConfig = new MailConfig();
    $result = $mailConfig->sendWelcomeEmail($testEmail, $testName, $testPassword);
    
    if ($result) {
        echo "Email sent successfully!";
    } else {
        echo "Failed to send email.";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?> 