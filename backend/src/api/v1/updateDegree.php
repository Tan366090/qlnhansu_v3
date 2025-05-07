<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "QLNhanSu";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $degreeId = $data['id'] ?? null;
    $degreeName = $data['name'] ?? null;
    $issueDate = $data['issue_date'] ?? null;

    if (!$degreeId || !$degreeName || !$issueDate) {
        echo json_encode(['error' => 'Missing required fields']);
        exit;
    }

    $stmt = $conn->prepare("UPDATE degrees SET degree_name = ?, issue_date = ? WHERE degree_id = ?");
    $stmt->bind_param("ssi", $degreeName, $issueDate, $degreeId);

    if ($stmt->execute()) {
        echo json_encode(['message' => 'Degree updated successfully']);
    } else {
        echo json_encode(['error' => 'Failed to update degree']);
    }

    $stmt->close();
}

$conn->close();
?>
