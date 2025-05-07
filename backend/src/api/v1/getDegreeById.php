<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
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

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $degreeId = $_GET['id'] ?? null;

    if (!$degreeId) {
        echo json_encode(['error' => 'Missing degree ID']);
        exit;
    }

    $stmt = $conn->prepare("SELECT * FROM degrees WHERE degree_id = ?");
    $stmt->bind_param("i", $degreeId);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $degree = $result->fetch_assoc();
        echo json_encode($degree);
    } else {
        echo json_encode(['error' => 'Failed to fetch degree']);
    }

    $stmt->close();
}

$conn->close();
?>
