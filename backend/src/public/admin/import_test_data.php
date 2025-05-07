<?php
// Kết nối database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "qlnhansu";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h1>Import Test Data</h1>";
    
    // Đọc file SQL
    $sql = file_get_contents('test_data.sql');
    
    // Chia các câu lệnh SQL
    $statements = explode(';', $sql);
    
    // Thực thi từng câu lệnh
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement)) {
            try {
                $conn->exec($statement);
                echo "<div style='color: green;'>✓ Thực thi thành công: " . substr($statement, 0, 50) . "...</div>";
            } catch(PDOException $e) {
                echo "<div style='color: red;'>✗ Lỗi: " . $e->getMessage() . "</div>";
            }
        }
    }
    
    echo "<h2>Import hoàn tất!</h2>";
    echo "<p>Dữ liệu test đã được thêm vào database.</p>";
    echo "<p><a href='test_api.php'>Kiểm tra API</a></p>";
    
} catch(PDOException $e) {
    echo "<div style='color: red;'>✗ Lỗi kết nối: " . $e->getMessage() . "</div>";
}
?> 