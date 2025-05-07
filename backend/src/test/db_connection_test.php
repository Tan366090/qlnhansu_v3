<?php
// Thông tin kết nối cơ sở dữ liệu
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'qlnhansu';

try {
    // Tạo kết nối
    $conn = new mysqli($host, $username, $password, $database);
    
    // Kiểm tra kết nối
    if ($conn->connect_error) {
        throw new Exception("Kết nối thất bại: " . $conn->connect_error);
    }
    
    echo "<h2>Kết nối thành công đến cơ sở dữ liệu!</h2>";
    echo "<h3>Thông tin kết nối:</h3>";
    echo "<ul>";
    echo "<li>Host: " . $host . "</li>";
    echo "<li>Database: " . $database . "</li>";
    echo "<li>Username: " . $username . "</li>";
    echo "<li>MySQL Version: " . $conn->server_info . "</li>";
    echo "<li>MySQL Protocol Version: " . $conn->protocol_version . "</li>";
    echo "<li>Character Set: " . $conn->character_set_name() . "</li>";
    echo "</ul>";

    // Kiểm tra danh sách bảng trong database
    echo "<h3>Danh sách các bảng trong database:</h3>";
    $result = $conn->query("SHOW TABLES");
    if ($result->num_rows > 0) {
        echo "<ul>";
        while($row = $result->fetch_array()) {
            echo "<li>" . $row[0] . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>Không có bảng nào trong database</p>";
    }
    
    // Đóng kết nối
    $conn->close();
    
} catch (Exception $e) {
    echo "<div style='color: red; padding: 10px; border: 1px solid red;'>";
    echo "<h3>Lỗi kết nối:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}
?> 