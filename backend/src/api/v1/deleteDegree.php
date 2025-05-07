<?php
require_once 'config.php';
require_once 'vendor/autoload.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: DELETE');
header('Access-Control-Allow-Headers: Content-Type');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
        throw new Exception('Only DELETE method is allowed');
    }

    if (!isset($_GET['id'])) {
        throw new Exception('Degree ID is required');
    }

    $degreeId = $_GET['id'];

    // Get the file URL before deleting the record
    $stmt = $conn->prepare("SELECT attachment_url FROM degrees WHERE id = ?");
    $stmt->bind_param("i", $degreeId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Degree not found');
    }

    $row = $result->fetch_assoc();
    $fileUrl = $row['attachment_url'];

    // Extract file ID from Google Drive URL
    preg_match('/\/d\/(.+?)\/view/', $fileUrl, $matches);
    if (isset($matches[1])) {
        $fileId = $matches[1];

        // Configure Google Client
        $client = new Google_Client();
        $client->setAuthConfig('c:/xampp/htdocs/QLNhanSu_version1/config/credentials.json'); // Đường dẫn chính xác
        $client->addScope(Google_Service_Drive::DRIVE_FILE);

        // Create Drive service
        $service = new Google_Service_Drive($client);

        // Delete file from Google Drive
        try {
            $service->files->delete($fileId);
        } catch (Exception $e) {
            // Log the error but continue with database deletion
            error_log("Failed to delete file from Google Drive: " . $e->getMessage());
        }
    }

    // Delete from database
    $stmt = $conn->prepare("DELETE FROM degrees WHERE id = ?");
    $stmt->bind_param("i", $degreeId);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to delete degree from database');
    }

    if ($stmt->affected_rows === 0) {
        throw new Exception('No degree was deleted');
    }

    echo json_encode([
        'success' => true,
        'message' => 'Degree deleted successfully'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
