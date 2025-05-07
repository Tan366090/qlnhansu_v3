<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set environment variables directly (replace these with your actual Gmail credentials)
$smtpPassword = 'ugciacxtpbaicdmr'; // Your App Password
putenv('SMTP_HOST=smtp.gmail.com');
putenv('SMTP_PORT=587');
putenv('SMTP_USERNAME=tan366090@gmail.com');
putenv('SMTP_PASSWORD=' . $smtpPassword);
putenv('SMTP_FROM_EMAIL=tan366090@gmail.com');
putenv('SMTP_FROM_NAME=HR System');

// Debug: Print environment variables
echo "Checking environment variables:\n";
$requiredVars = ['SMTP_HOST', 'SMTP_USERNAME', 'SMTP_PASSWORD', 'SMTP_FROM_EMAIL'];
foreach ($requiredVars as $var) {
    $value = getenv($var);
    echo "$var: " . ($value ? "Set" : "Not set") . "\n";
    if ($var === 'SMTP_PASSWORD') {
        echo "Password length: " . strlen($value) . " characters\n";
        echo "Password value matches: " . ($value === $smtpPassword ? "Yes" : "No") . "\n";
    }
}

require_once __DIR__ . '/../backend/src/config/mail.php';

// Test data
$testEmail = "tan366090@gmail.com";
$testName = "TanDat";
$testPassword = "test123";

try {
    // Create MailConfig with debugging enabled
    $mailConfig = new MailConfig(true);
    
    // Test connection first
    echo "\nTesting SMTP connection...\n";
    $mailConfig->testConnection();
    
    // Try to send email
    echo "\nAttempting to send email...\n";
    $result = $mailConfig->sendWelcomeEmail($testEmail, $testName, $testPassword);
    
    if ($result) {
        echo "Email sent successfully!";
    } else {
        echo "Failed to send email.";
    }
} catch (Exception $e) {
    echo "\nError Details:\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    
    if (isset($mailConfig->mail)) {
        echo "\nSMTP Error Info: " . $mailConfig->mail->ErrorInfo . "\n";
    }
}
?> 