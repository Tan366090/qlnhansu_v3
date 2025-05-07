<?php
// Test file for export API
header('Content-Type: application/json; charset=utf-8');

// Test parameters
$params = [
    'action' => 'export',
    'search' => '',
    'type' => '',
    'status' => ''
];

// Build query string
$queryString = http_build_query($params);

// API URL
$apiUrl = "http://localhost/qlnhansu_V3/backend/src/public/admin/api/degrees.php?{$queryString}";

// Make request
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, false);

// Execute request
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

// Check for errors
if (curl_errno($ch)) {
    echo json_encode([
        'error' => 'Curl error: ' . curl_error($ch)
    ]);
} else {
    // Output response details
    echo "HTTP Status Code: " . $httpCode . "\n\n";
    echo "Response:\n";
    echo $response;
}

curl_close($ch);
?> 