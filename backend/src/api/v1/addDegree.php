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
    try {
        $degreeName = $_POST['name'] ?? null; // Match frontend field name
        $issueDate = $_POST['issue_date'] ?? null; // Match frontend field name
        $expiryDate = $_POST['expiry_date'] ?? null; // Match frontend field name

        if (!$degreeName || !$issueDate) {
            echo json_encode(['error' => 'Tên bằng cấp và ngày cấp là bắt buộc.']);
            exit;
        }

        $attachmentUrl = null;
        if (isset($_FILES['minhChung']) && $_FILES['minhChung']['error'] === UPLOAD_ERR_OK) { // Match frontend field name
            $uploadDir = __DIR__ . '/../files/uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $fileName = uniqid() . '_' . basename($_FILES['minhChung']['name']);
            $filePath = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['minhChung']['tmp_name'], $filePath)) {
                $attachmentUrl = '/files/uploads/' . $fileName;
            } else {
                echo json_encode(['error' => 'Không thể tải lên tệp đính kèm.']);
                exit;
            }
        }

        $stmt = $conn->prepare("INSERT INTO degrees (degree_name, issue_date, expiry_date, attachment_url) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $degreeName, $issueDate, $expiryDate, $attachmentUrl);

        if ($stmt->execute()) {
            echo json_encode(['message' => 'Degree added successfully']);
        } else {
            echo json_encode(['error' => 'Failed to add degree']);
        }

        $stmt->close();
    } catch (Exception $e) {
        echo json_encode(['error' => 'Unexpected error: ' . $e->getMessage()]);
    }
}

$conn->close();
?>
