<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../../config/database.php';

// Kết nối database
$database = new Database();
$db = $database->getConnection();

// Xử lý request
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        try {
            // Lấy thông tin user profile từ database
            $query = "SELECT * FROM user_profiles WHERE user_id = 1"; // Tạm thời lấy user_id = 1
            $stmt = $db->prepare($query);
            $stmt->execute();
            
            $profile = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Nếu không có dữ liệu, trả về dữ liệu mặc định
            if (!$profile) {
                $profile = [
                    'profile_id' => 1,
                    'user_id' => 1,
                    'full_name' => 'Admin User',
                    'avatar_url' => null,
                    'date_of_birth' => '1990-01-01',
                    'permanent_address' => '123 Main St, Hanoi',
                    'current_workplace' => null,
                    'gender' => 'Male',
                    'phone_number' => '0123456789',
                    'emergency_contact' => 'Jane Doe',
                    'bank_account' => '1234567890',
                    'tax_code' => '123456789',
                    'nationality' => 'Vietnamese',
                    'ethnicity' => 'Kinh',
                    'religion' => 'None',
                    'marital_status' => 'Single',
                    'id_card_number' => '123456789',
                    'id_card_issue_date' => '2010-01-01',
                    'id_card_issue_place' => 'Hanoi',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
            }
            
            echo json_encode($profile);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        }
        break;
        
    case 'PUT':
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['full_name']) || !isset($data['phone_number'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing required fields']);
                exit;
            }
            
            $query = "UPDATE user_profiles SET 
                full_name = :full_name,
                avatar_url = :avatar_url,
                date_of_birth = :date_of_birth,
                permanent_address = :permanent_address,
                current_workplace = :current_workplace,
                gender = :gender,
                phone_number = :phone_number,
                emergency_contact = :emergency_contact,
                bank_account = :bank_account,
                tax_code = :tax_code,
                nationality = :nationality,
                ethnicity = :ethnicity,
                religion = :religion,
                marital_status = :marital_status,
                id_card_number = :id_card_number,
                id_card_issue_date = :id_card_issue_date,
                id_card_issue_place = :id_card_issue_place,
                updated_at = NOW()
                WHERE user_id = :user_id";
                
            $stmt = $db->prepare($query);
            $stmt->bindParam(':full_name', $data['full_name']);
            $stmt->bindParam(':avatar_url', $data['avatar_url']);
            $stmt->bindParam(':date_of_birth', $data['date_of_birth']);
            $stmt->bindParam(':permanent_address', $data['permanent_address']);
            $stmt->bindParam(':current_workplace', $data['current_workplace']);
            $stmt->bindParam(':gender', $data['gender']);
            $stmt->bindParam(':phone_number', $data['phone_number']);
            $stmt->bindParam(':emergency_contact', $data['emergency_contact']);
            $stmt->bindParam(':bank_account', $data['bank_account']);
            $stmt->bindParam(':tax_code', $data['tax_code']);
            $stmt->bindParam(':nationality', $data['nationality']);
            $stmt->bindParam(':ethnicity', $data['ethnicity']);
            $stmt->bindParam(':religion', $data['religion']);
            $stmt->bindParam(':marital_status', $data['marital_status']);
            $stmt->bindParam(':id_card_number', $data['id_card_number']);
            $stmt->bindParam(':id_card_issue_date', $data['id_card_issue_date']);
            $stmt->bindParam(':id_card_issue_place', $data['id_card_issue_place']);
            $stmt->bindParam(':user_id', $data['user_id']);
            $stmt->execute();
            
            echo json_encode(['message' => 'Profile updated successfully']);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}
?> 