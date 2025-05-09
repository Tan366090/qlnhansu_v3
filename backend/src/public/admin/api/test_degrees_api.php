<?php
// Test file for degrees.php API

// Function to make API requests
function makeRequest($url, $method = 'GET', $data = null) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        'code' => $httpCode,
        'response' => json_decode($response, true)
    ];
}

// Base URL for API
$baseUrl = 'http://localhost/qlnhansu_V3/backend/src/public/admin/api/degrees.php';

// Test cases
$tests = [
    // Test 1: Get all qualifications (default)
    [
        'name' => 'Get all qualifications',
        'url' => $baseUrl . '?action=list',
        'expected' => ['success' => true]
    ],
    
    // Test 2: Get qualifications with pagination
    [
        'name' => 'Get qualifications with pagination',
        'url' => $baseUrl . '?action=list&page=1&limit=5',
        'expected' => ['success' => true]
    ],
    
    // Test 3: Get only degrees
    [
        'name' => 'Get only degrees',
        'url' => $baseUrl . '?action=list&type=degree',
        'expected' => ['success' => true]
    ],
    
    // Test 4: Get only certificates
    [
        'name' => 'Get only certificates',
        'url' => $baseUrl . '?action=list&type=certificate',
        'expected' => ['success' => true]
    ],
    
    // Test 5: Get valid certificates
    [
        'name' => 'Get valid certificates',
        'url' => $baseUrl . '?action=list&type=certificate&status=valid',
        'expected' => ['success' => true]
    ],
    
    // Test 6: Get expired certificates
    [
        'name' => 'Get expired certificates',
        'url' => $baseUrl . '?action=list&type=certificate&status=expired',
        'expected' => ['success' => true]
    ],
    
    // Test 7: Get expiring certificates
    [
        'name' => 'Get expiring certificates',
        'url' => $baseUrl . '?action=list&type=certificate&status=expiring',
        'expected' => ['success' => true]
    ],
    
    // Test 8: Get dashboard stats
    [
        'name' => 'Get dashboard stats',
        'url' => $baseUrl . '?action=dashboard_stats',
        'expected' => ['success' => true]
    ],
    
    // Test 9: Get degree distribution
    [
        'name' => 'Get degree distribution',
        'url' => $baseUrl . '?action=degree_distribution',
        'expected' => ['success' => true]
    ],
    
    // Test 10: Get certificate distribution
    [
        'name' => 'Get certificate distribution',
        'url' => $baseUrl . '?action=certificate_distribution',
        'expected' => ['success' => true]
    ],
    
    // Test 11: Get specific degree
    [
        'name' => 'Get specific degree',
        'url' => $baseUrl . '?action=get&id=1&type=degree',
        'expected' => ['success' => true]
    ],
    
    // Test 12: Get specific certificate
    [
        'name' => 'Get specific certificate',
        'url' => $baseUrl . '?action=get&id=1&type=certificate',
        'expected' => ['success' => true]
    ],
    
    // Test 13: Export qualifications
    [
        'name' => 'Export qualifications',
        'url' => $baseUrl . '?action=export',
        'expected' => ['success' => true]
    ],
    
    // Test 14: Export specific type
    [
        'name' => 'Export specific type',
        'url' => $baseUrl . '?action=export&type=degree',
        'expected' => ['success' => true]
    ]
];

// Run tests
echo "Starting API Tests...\n\n";

foreach ($tests as $test) {
    echo "Running test: {$test['name']}\n";
    echo "URL: {$test['url']}\n";
    
    $result = makeRequest($test['url']);
    
    echo "Status Code: {$result['code']}\n";
    echo "Response: " . json_encode($result['response'], JSON_PRETTY_PRINT) . "\n";
    
    if ($result['code'] === 200 && isset($result['response']['success']) && $result['response']['success'] === true) {
        echo "Test PASSED\n";
    } else {
        echo "Test FAILED\n";
    }
    
    echo "\n----------------------------------------\n\n";
}

// Test POST operations
echo "Testing POST operations...\n\n";

// Test 15: Create new degree
$createDegreeData = [
    'type' => 'degree',
    'employee_id' => 1,
    'name' => 'Bachelor of Science',
    'major' => 'Computer Science',
    'organization' => 'Test University',
    'issue_date' => '2023-01-01',
    'gpa' => 3.5
];

echo "Creating new degree...\n";
$result = makeRequest($baseUrl . '?action=create', 'POST', $createDegreeData);
echo "Status Code: {$result['code']}\n";
echo "Response: " . json_encode($result['response'], JSON_PRETTY_PRINT) . "\n\n";

// Test 16: Create new certificate
$createCertificateData = [
    'type' => 'certificate',
    'employee_id' => 1,
    'name' => 'AWS Certified Solutions Architect',
    'organization' => 'Amazon Web Services',
    'issue_date' => '2023-01-01',
    'expiry_date' => '2026-01-01',
    'credential_id' => 'AWS-123456'
];

echo "Creating new certificate...\n";
$result = makeRequest($baseUrl . '?action=create', 'POST', $createCertificateData);
echo "Status Code: {$result['code']}\n";
echo "Response: " . json_encode($result['response'], JSON_PRETTY_PRINT) . "\n\n";

// Test 17: Update degree
$updateDegreeData = [
    'id' => 1,
    'type' => 'degree',
    'employee_id' => 1,
    'name' => 'Bachelor of Science (Updated)',
    'major' => 'Computer Science',
    'organization' => 'Test University',
    'issue_date' => '2023-01-01',
    'gpa' => 3.8
];

echo "Updating degree...\n";
$result = makeRequest($baseUrl . '?action=update', 'POST', $updateDegreeData);
echo "Status Code: {$result['code']}\n";
echo "Response: " . json_encode($result['response'], JSON_PRETTY_PRINT) . "\n\n";

// Test 18: Delete qualification
$deleteData = [
    'id' => 1,
    'type' => 'degree'
];

echo "Deleting qualification...\n";
$result = makeRequest($baseUrl . '?action=delete', 'POST', $deleteData);
echo "Status Code: {$result['code']}\n";
echo "Response: " . json_encode($result['response'], JSON_PRETTY_PRINT) . "\n\n";

echo "All tests completed!\n";
?> 