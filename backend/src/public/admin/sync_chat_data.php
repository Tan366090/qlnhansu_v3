<?php
// Bật báo lỗi
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Kết nối database
$conn = new mysqli("localhost", "root", "", "qlnhansu");
$conn->set_charset("utf8");

if ($conn->connect_error) {
    error_log("Kết nối database thất bại: " . $conn->connect_error);
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Hàm để lấy dữ liệu từ database
function getDataFromDatabase($conn) {
    $data = [];
    
    try {
        // 1. Thông tin nhân viên
        $employees_query = "SELECT 
            e.*,
            d.name as department_name,
            p.name as position_name,
            u.username,
            up.full_name,
            up.phone_number,
            up.date_of_birth,
            up.gender,
            up.current_address
        FROM employees e
        LEFT JOIN departments d ON e.department_id = d.id
        LEFT JOIN positions p ON e.position_id = p.id
        LEFT JOIN users u ON e.user_id = u.user_id
        LEFT JOIN user_profiles up ON u.user_id = up.user_id";
        
        $result = $conn->query($employees_query);
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $data['employees'][] = $row;
            }
            error_log("Đã lấy " . count($data['employees']) . " nhân viên từ database");
        }

        // 2. Thông tin phòng ban
        $departments_query = "SELECT * FROM departments";
        $result = $conn->query($departments_query);
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $data['departments'][] = $row;
            }
            error_log("Đã lấy " . count($data['departments']) . " phòng ban từ database");
        }

        // 3. Thông tin chức vụ
        $positions_query = "SELECT * FROM positions";
        $result = $conn->query($positions_query);
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $data['positions'][] = $row;
            }
            error_log("Đã lấy " . count($data['positions']) . " chức vụ từ database");
        }

        // 4. Thông tin người dùng
        $users_query = "SELECT * FROM users";
        $result = $conn->query($users_query);
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $data['users'][] = $row;
            }
            error_log("Đã lấy " . count($data['users']) . " người dùng từ database");
        }

        // 5. Thông tin hồ sơ người dùng
        $profiles_query = "SELECT * FROM user_profiles";
        $result = $conn->query($profiles_query);
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $data['user_profiles'][] = $row;
            }
            error_log("Đã lấy " . count($data['user_profiles']) . " hồ sơ người dùng từ database");
        }

        // 6. Thông tin nghỉ phép
        $leaves_query = "SELECT * FROM leaves";
        $result = $conn->query($leaves_query);
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $data['leaves'][] = $row;
            }
            error_log("Đã lấy " . count($data['leaves']) . " đơn nghỉ phép từ database");
        }

        // 7. Thông tin chấm công
        $attendance_query = "SELECT * FROM attendance";
        $result = $conn->query($attendance_query);
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $data['attendance'][] = $row;
            }
            error_log("Đã lấy " . count($data['attendance']) . " bản ghi chấm công từ database");
        }

        // 8. Thông tin lương
        $salary_query = "SELECT * FROM salaries";
        $result = $conn->query($salary_query);
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $data['salaries'][] = $row;
            }
            error_log("Đã lấy " . count($data['salaries']) . " bản ghi lương từ database");
        }

        // 9. Thông tin đào tạo
        $training_query = "SELECT * FROM training_courses";
        $result = $conn->query($training_query);
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $data['training_courses'][] = $row;
            }
            error_log("Đã lấy " . count($data['training_courses']) . " khóa đào tạo từ database");
        }

        // 10. Thông tin đánh giá
        $performance_query = "SELECT * FROM performances";
        $result = $conn->query($performance_query);
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $data['performances'][] = $row;
            }
            error_log("Đã lấy " . count($data['performances']) . " đánh giá từ database");
        }

    } catch (Exception $e) {
        error_log("Lỗi khi lấy dữ liệu: " . $e->getMessage());
    }
    
    return $data;
}

// Hàm để cập nhật file chat_data.txt
function updateChatDataFile($data) {
    try {
        $jsonData = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $filePath = __DIR__ . '/chat_data.txt';
        
        if (file_put_contents($filePath, $jsonData) === false) {
            error_log("Không thể ghi dữ liệu vào file chat_data.txt");
            return false;
        }
        error_log("Đã cập nhật file chat_data.txt thành công");
        return true;
    } catch (Exception $e) {
        error_log("Lỗi khi cập nhật file: " . $e->getMessage());
        return false;
    }
}

// Lấy dữ liệu từ database
$data = getDataFromDatabase($conn);

// Cập nhật file chat_data.txt
if (updateChatDataFile($data)) {
    echo json_encode(['success' => true, 'message' => 'Đã cập nhật dữ liệu thành công']);
} else {
    echo json_encode(['success' => false, 'message' => 'Không thể cập nhật dữ liệu']);
}

$conn->close();
?> 