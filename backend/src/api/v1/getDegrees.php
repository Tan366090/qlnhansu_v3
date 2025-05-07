<?php
require_once 'config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

try {
    $query = "SELECT * FROM degrees ORDER BY issue_date DESC";
    $result = $conn->query($query);

    if (!$result) {
        throw new Exception('Failed to fetch degrees');
    }

    $degrees = [];
    while ($row = $result->fetch_assoc()) {
        $degrees[] = [
            'id' => $row['id'],
            'degree_name' => $row['degree_name'],
            'issue_date' => $row['issue_date'],
            'attachment_url' => $row['attachment_url']
        ];
    }

    echo json_encode([
        'success' => true,
        'data' => $degrees
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
