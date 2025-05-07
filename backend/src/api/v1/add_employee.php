<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Allow from any origin
if (isset($_SERVER['HTTP_ORIGIN'])) {
    // Should do a check here to match $_SERVER['HTTP_ORIGIN'] to a
    // whitelist of safe domains
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');    // cache for 1 day
}

// Access-Control headers are received during OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");         

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

    exit(0);
}

header('Content-Type: application/json; charset=UTF-8');

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Get raw POST data
$rawData = file_get_contents("php://input");
$data = json_decode($rawData);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid JSON data received",
        "error" => json_last_error_msg(),
        "raw_data" => $rawData
    ]);
    exit;
}

// Validate required fields
$required_fields = [
    'fullName' => 'Họ và tên',
    'employeeId' => 'Mã nhân viên',
    'email' => 'Email',
    'phone' => 'Số điện thoại',
    'department' => 'Phòng ban',
    'position' => 'Chức vụ',
    'birthDate' => 'Ngày sinh',
    'gender' => 'Giới tính',
    'address' => 'Địa chỉ',
    'idNumber' => 'CMND/CCCD',
    'startDate' => 'Ngày bắt đầu làm việc',
    'password' => 'Mật khẩu'
];

$missing_fields = [];
foreach ($required_fields as $field => $label) {
    if (!isset($data->$field) || empty($data->$field)) {
        $missing_fields[] = $label;
    }
}

if (!empty($missing_fields)) {
    echo json_encode([
        "success" => false,
        "message" => "Vui lòng điền đầy đủ thông tin: " . implode(", ", $missing_fields)
    ]);
    exit;
}

try {
    $db->beginTransaction();

    // Check if email already exists
    $stmt = $db->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->execute([$data->email]);
    if ($stmt->rowCount() > 0) {
        throw new Exception("Email đã tồn tại trong hệ thống");
    }

    // Check if employee ID already exists
    $stmt = $db->prepare("SELECT user_id FROM users WHERE employee_code = ?");
    $stmt->execute([$data->employeeId]);
    if ($stmt->rowCount() > 0) {
        throw new Exception("Mã nhân viên đã tồn tại trong hệ thống");
    }

    // Generate password hash and salt
    $salt = bin2hex(random_bytes(32));
    $password_hash = md5($data->password . $salt);

    // Insert into users table
    $stmt = $db->prepare("
        INSERT INTO users (
            username, email, password_hash, password_salt, role_id, 
            department_id, position_id, hire_date, employee_code
        ) VALUES (
            ?, ?, ?, ?, 3, ?, ?, ?, ?
        )
    ");

    $username = strtolower(str_replace(' ', '.', $data->fullName));
    $stmt->execute([
        $username,
        $data->email,
        $password_hash,
        $salt,
        $data->department,
        $data->position,
        $data->startDate,
        $data->employeeId
    ]);

    $user_id = $db->lastInsertId();

    // Insert into user_profiles table
    $stmt = $db->prepare("
        INSERT INTO user_profiles (
            user_id, full_name, date_of_birth, gender,
            phone_number, permanent_address, id_card_number
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?
        )
    ");

    $stmt->execute([
        $user_id,
        $data->fullName,
        $data->birthDate,
        $data->gender,
        $data->phone,
        $data->address,
        $data->idNumber
    ]);

    // Handle avatar upload if provided
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = "../uploads/avatars/";
        $file_extension = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
        $file_name = $user_id . "_" . time() . "." . $file_extension;
        $target_file = $upload_dir . $file_name;

        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $target_file)) {
            $stmt = $db->prepare("UPDATE user_profiles SET avatar_url = ? WHERE user_id = ?");
            $stmt->execute([$file_name, $user_id]);
        }
    }

    $db->commit();

    echo json_encode([
        "success" => true,
        "message" => "Thêm nhân viên thành công",
        "user_id" => $user_id
    ]);

} catch (Exception $e) {
    $db->rollBack();
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
?> 