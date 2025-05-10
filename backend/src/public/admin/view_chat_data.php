<?php
include 'headers.php';

// Đọc dữ liệu từ file
$json_data = file_get_contents('chat_data.txt');
if ($json_data === false) {
    die("Không thể đọc file chat_data.txt");
}

// Chuyển đổi JSON thành mảng
$data = json_decode($json_data, true);
if ($data === null) {
    die("Lỗi khi phân tích JSON: " . json_last_error_msg());
}

// Hiển thị dữ liệu
echo "<h2>Dữ liệu cập nhật lần cuối: " . $data['last_updated'] . "</h2>";

// Hiển thị thông tin nhân viên
echo "<h3>Thông tin nhân viên (" . count($data['employees']) . " nhân viên)</h3>";
echo "<table border='1'>";
echo "<tr><th>ID</th><th>Tên</th><th>Email</th><th>Phòng ban</th><th>Vị trí</th><th>Trạng thái</th></tr>";
foreach ($data['employees'] as $employee) {
    echo "<tr>";
    echo "<td>" . $employee['id'] . "</td>";
    echo "<td>" . $employee['name'] . "</td>";
    echo "<td>" . $employee['email'] . "</td>";
    echo "<td>" . $employee['department_name'] . "</td>";
    echo "<td>" . $employee['position_name'] . "</td>";
    echo "<td>" . $employee['status'] . "</td>";
    echo "</tr>";
}
echo "</table>";

// Hiển thị thông tin phòng ban
echo "<h3>Thông tin phòng ban (" . count($data['departments']) . " phòng ban)</h3>";
echo "<table border='1'>";
echo "<tr><th>ID</th><th>Tên phòng ban</th><th>Người quản lý</th><th>Số nhân viên</th></tr>";
foreach ($data['departments'] as $department) {
    echo "<tr>";
    echo "<td>" . $department['id'] . "</td>";
    echo "<td>" . $department['name'] . "</td>";
    echo "<td>" . $department['manager_name'] . "</td>";
    echo "<td>" . $department['employee_count'] . "</td>";
    echo "</tr>";
}
echo "</table>";

// Hiển thị thông tin nghỉ phép
echo "<h3>Thông tin nghỉ phép (" . count($data['leaves']) . " đơn)</h3>";
echo "<table border='1'>";
echo "<tr><th>ID</th><th>Nhân viên</th><th>Loại nghỉ</th><th>Ngày bắt đầu</th><th>Ngày kết thúc</th><th>Trạng thái</th></tr>";
foreach ($data['leaves'] as $leave) {
    echo "<tr>";
    echo "<td>" . $leave['id'] . "</td>";
    echo "<td>" . $leave['employee_name'] . "</td>";
    echo "<td>" . $leave['leave_type'] . "</td>";
    echo "<td>" . $leave['start_date'] . "</td>";
    echo "<td>" . $leave['end_date'] . "</td>";
    echo "<td>" . $leave['status'] . "</td>";
    echo "</tr>";
}
echo "</table>";

// Hiển thị thông tin đào tạo
echo "<h3>Thông tin đào tạo (" . count($data['training']) . " khóa học)</h3>";
echo "<table border='1'>";
echo "<tr><th>ID</th><th>Tên khóa học</th><th>Thời lượng</th><th>Chi phí</th><th>Số người đăng ký</th><th>Trạng thái</th></tr>";
foreach ($data['training'] as $course) {
    echo "<tr>";
    echo "<td>" . $course['id'] . "</td>";
    echo "<td>" . $course['course_name'] . "</td>";
    echo "<td>" . $course['duration'] . "</td>";
    echo "<td>" . $course['cost'] . "</td>";
    echo "<td>" . $course['registered_count'] . "</td>";
    echo "<td>" . $course['status'] . "</td>";
    echo "</tr>";
}
echo "</table>";
?> 