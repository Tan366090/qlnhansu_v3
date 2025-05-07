<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';

// Kiểm tra quyền admin
if (!isAdmin()) {
    die('Unauthorized access');
}

// Hàm kiểm tra cột có tồn tại không
function columnExists($db, $table, $column) {
    $stmt = $db->prepare("SHOW COLUMNS FROM `$table` LIKE ?");
    $stmt->execute([$column]);
    return $stmt->rowCount() > 0;
}

try {
    // Kết nối database
    $db = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS
    );
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Bắt đầu transaction
    $db->beginTransaction();
    $transactionActive = true;
    
    echo "<h1>Thêm cột còn thiếu vào cơ sở dữ liệu</h1>";
    echo "<pre>";
    
    // 1. Thêm cột vào bảng performances
    echo "1. Kiểm tra và thêm cột vào bảng performances...\n";
    
    // Kiểm tra và thêm evaluation_date
    if (!columnExists($db, 'performances', 'evaluation_date')) {
        $db->exec("ALTER TABLE `performances`
            ADD COLUMN `evaluation_date` date DEFAULT NULL AFTER `review_date`");
        echo "✓ Thêm cột evaluation_date thành công\n";
    } else {
        echo "✓ Cột evaluation_date đã tồn tại\n";
    }
    
    // Kiểm tra và thêm score
    if (!columnExists($db, 'performances', 'score')) {
        $db->exec("ALTER TABLE `performances`
            ADD COLUMN `score` decimal(4,2) DEFAULT NULL AFTER `performance_score`");
        echo "✓ Thêm cột score thành công\n";
    } else {
        echo "✓ Cột score đã tồn tại\n";
    }
    
    // Cập nhật dữ liệu cho performances
    echo "Cập nhật dữ liệu cho performances...\n";
    $db->exec("UPDATE `performances` 
        SET `evaluation_date` = `review_date`,
            `score` = `performance_score`");
    echo "✓ Cập nhật dữ liệu thành công\n";
    
    // 2. Thêm cột vào bảng payroll
    echo "\n2. Kiểm tra và thêm cột vào bảng payroll...\n";
    if (!columnExists($db, 'payroll', 'amount')) {
        $db->exec("ALTER TABLE `payroll`
            ADD COLUMN `amount` decimal(15,2) DEFAULT NULL AFTER `net_salary`");
        echo "✓ Thêm cột amount thành công\n";
    } else {
        echo "✓ Cột amount đã tồn tại\n";
    }
    
    // Cập nhật dữ liệu cho payroll
    echo "Cập nhật dữ liệu cho payroll...\n";
    $db->exec("UPDATE `payroll` 
        SET `amount` = `gross_salary`");
    echo "✓ Cập nhật dữ liệu thành công\n";
    
    // 3. Thêm cột vào bảng leaves
    echo "\n3. Kiểm tra và thêm cột vào bảng leaves...\n";
    if (!columnExists($db, 'leaves', 'type')) {
        $db->exec("ALTER TABLE `leaves`
            ADD COLUMN `type` varchar(50) DEFAULT NULL AFTER `leave_type`");
        echo "✓ Thêm cột type thành công\n";
    } else {
        echo "✓ Cột type đã tồn tại\n";
    }
    
    // Cập nhật dữ liệu cho leaves
    echo "Cập nhật dữ liệu cho leaves...\n";
    $db->exec("UPDATE `leaves` 
        SET `type` = `leave_type`");
    echo "✓ Cập nhật dữ liệu thành công\n";
    
    // 4. Thêm các ràng buộc
    echo "\n4. Thêm các ràng buộc...\n";
    
    // Ràng buộc cho performances
    echo "Thêm ràng buộc cho performances...\n";
    try {
        $db->exec("ALTER TABLE `performances`
            ADD CONSTRAINT `chk_evaluation_date` CHECK (`evaluation_date` IS NULL OR `evaluation_date` >= `review_period_start` AND `evaluation_date` <= `review_period_end`),
            ADD CONSTRAINT `chk_score` CHECK (`score` IS NULL OR `score` >= 0 AND `score` <= 5)");
        echo "✓ Thêm ràng buộc thành công\n";
    } catch (PDOException $e) {
        echo "⚠ Ràng buộc đã tồn tại hoặc không thể thêm\n";
    }
    
    // Ràng buộc cho payroll
    echo "Thêm ràng buộc cho payroll...\n";
    try {
        $db->exec("ALTER TABLE `payroll`
            ADD CONSTRAINT `chk_amount` CHECK (`amount` IS NULL OR `amount` >= 0)");
        echo "✓ Thêm ràng buộc thành công\n";
    } catch (PDOException $e) {
        echo "⚠ Ràng buộc đã tồn tại hoặc không thể thêm\n";
    }
    
    // Ràng buộc cho leaves
    echo "Thêm ràng buộc cho leaves...\n";
    try {
        $db->exec("ALTER TABLE `leaves`
            ADD CONSTRAINT `chk_type` CHECK (`type` IN ('Annual', 'Sick', 'Unpaid', 'Maternity', 'Paternity', 'Bereavement', 'Other'))");
        echo "✓ Thêm ràng buộc thành công\n";
    } catch (PDOException $e) {
        echo "⚠ Ràng buộc đã tồn tại hoặc không thể thêm\n";
    }
    
    // Commit transaction
    $db->commit();
    $transactionActive = false;
    echo "\n✓ Tất cả thay đổi đã được thực hiện thành công!\n";
    
} catch (PDOException $e) {
    // Rollback transaction nếu có lỗi và transaction vẫn đang hoạt động
    if (isset($db) && $transactionActive) {
        try {
            $db->rollBack();
            echo "\n❌ Đã rollback các thay đổi do có lỗi xảy ra.\n";
        } catch (PDOException $rollbackError) {
            echo "\n❌ Lỗi khi rollback: " . $rollbackError->getMessage() . "\n";
        }
    }
    echo "\n❌ Lỗi: " . $e->getMessage() . "\n";
    echo "Chi tiết lỗi: " . $e->getTraceAsString() . "\n";
}

echo "</pre>";
?> 