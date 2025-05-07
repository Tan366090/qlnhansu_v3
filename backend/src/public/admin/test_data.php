<?php
require_once __DIR__ . '/../config/database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();

    // 1. Test dữ liệu phòng ban
    echo "<h2>1. Kiểm tra dữ liệu phòng ban</h2>";
    
    $query = "SELECT 
                d.id as department_id,
                d.name,
                d.description,
                d.manager_id,
                d.status,
                COUNT(e.id) as employee_count,
                m.full_name as manager_name
            FROM departments d
            LEFT JOIN employees e ON d.id = e.department_id
            LEFT JOIN user_profiles m ON d.manager_id = m.id
            GROUP BY d.id
            ORDER BY d.name";

    $stmt = $conn->prepare($query);
    $stmt->execute();
    $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<h3>Dữ liệu phòng ban:</h3>";
    echo "<pre>";
    print_r($departments);
    echo "</pre>";

    // 2. Test dữ liệu chấm công
    echo "<h2>2. Kiểm tra dữ liệu chấm công</h2>";
    
    $query = "SELECT 
                DATE(e.check_in) as date,
                COUNT(*) as total_employees,
                SUM(CASE WHEN e.status = 'on_time' THEN 1 ELSE 0 END) as on_time_count,
                SUM(CASE WHEN e.status = 'late' THEN 1 ELSE 0 END) as late_count,
                SUM(CASE WHEN e.status = 'absent' THEN 1 ELSE 0 END) as absent_count
            FROM employee_attendance e
            WHERE e.check_in >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            GROUP BY DATE(e.check_in)
            ORDER BY date DESC";

    $stmt = $conn->prepare($query);
    $stmt->execute();
    $attendance = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<h3>Dữ liệu chấm công 30 ngày gần nhất:</h3>";
    echo "<pre>";
    print_r($attendance);
    echo "</pre>";

    // 3. Kiểm tra cấu trúc bảng
    echo "<h2>3. Kiểm tra cấu trúc bảng</h2>";
    
    $tables = ['departments', 'employees', 'employee_attendance', 'user_profiles'];
    foreach ($tables as $table) {
        $query = "DESCRIBE $table";
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $structure = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<h3>Cấu trúc bảng $table:</h3>";
        echo "<pre>";
        print_r($structure);
        echo "</pre>";
    }

    // 4. Tạo dữ liệu mẫu nếu chưa có
    echo "<h2>4. Tạo dữ liệu mẫu</h2>";
    
    // Tạo phòng ban mẫu
    $departments = [
        ['IT Department', 'Phòng Công nghệ thông tin', 1],
        ['HR Department', 'Phòng Nhân sự', 2],
        ['Finance Department', 'Phòng Tài chính', 3],
        ['Marketing Department', 'Phòng Marketing', 4],
        ['Operations Department', 'Phòng Vận hành', 5]
    ];

    foreach ($departments as $dept) {
        $query = "INSERT INTO departments (name, description, manager_id) 
                 VALUES (?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->execute($dept);
    }

    // Tạo nhân viên mẫu
    $employees = [
        [1, 1, 'Software Engineer', '2023-01-01', 5000.00, 'active'],
        [2, 2, 'HR Manager', '2023-02-01', 6000.00, 'active'],
        [3, 3, 'Accountant', '2023-03-01', 4500.00, 'active'],
        [4, 4, 'Marketing Specialist', '2023-04-01', 4800.00, 'active'],
        [5, 5, 'Operations Manager', '2023-05-01', 5500.00, 'active']
    ];

    foreach ($employees as $emp) {
        $query = "INSERT INTO employees (user_id, department_id, position, hire_date, salary, status) 
                 VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->execute($emp);
    }

    // Tạo dữ liệu chấm công mẫu
    $attendance = [
        [1, '2024-01-01 08:00:00', 'on_time'],
        [2, '2024-01-01 08:15:00', 'late'],
        [3, '2024-01-01 08:00:00', 'on_time'],
        [4, '2024-01-01 08:30:00', 'late'],
        [5, '2024-01-01 08:00:00', 'on_time']
    ];

    foreach ($attendance as $att) {
        $query = "INSERT INTO employee_attendance (employee_id, check_in, status) 
                 VALUES (?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->execute($att);
    }

    echo "<p>Đã tạo dữ liệu mẫu thành công!</p>";

} catch (PDOException $e) {
    echo "Lỗi: " . $e->getMessage();
}
?> 