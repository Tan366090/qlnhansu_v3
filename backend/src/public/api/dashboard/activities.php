<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

try {
    // Lấy 10 hoạt động gần đây nhất
    $query = "SELECT 
        a.id,
        u.username as user,
        a.type,
        a.description,
        a.device,
        DATE_FORMAT(a.created_at, '%d/%m/%Y %H:%i') as timestamp
    FROM activities a
    LEFT JOIN users u ON a.user_id = u.id
    ORDER BY a.created_at DESC
    LIMIT 10";

    $stmt = $db->prepare($query);
    $stmt->execute();
    $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($activities);

} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(["message" => "Lỗi khi lấy danh sách hoạt động: " . $e->getMessage()]);
}
?> 