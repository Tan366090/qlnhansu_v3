<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

try {
    // Lấy 5 nhân viên mới nhất
    $query = "SELECT 
        e.id,
        CONCAT(e.first_name, ' ', e.last_name) as name,
        p.name as position,
        DATE_FORMAT(e.join_date, '%d/%m/%Y') as join_date
    FROM employees e
    LEFT JOIN positions p ON e.position_id = p.id
    WHERE e.status = 'active'
    ORDER BY e.join_date DESC
    LIMIT 5";

    $stmt = $db->prepare($query);
    $stmt->execute();
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($employees);

} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(["message" => "Lỗi khi lấy danh sách nhân viên: " . $e->getMessage()]);
}
?> 