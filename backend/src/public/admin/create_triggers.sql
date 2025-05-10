DELIMITER //

-- Hàm để gọi file PHP
CREATE FUNCTION call_sync_script() 
RETURNS INT
DETERMINISTIC
BEGIN
    -- Đường dẫn đến file sync_chat_data.php
    SET @script_path = 'C:/xampp/htdocs/qlnhansu_V3/backend/src/public/admin/sync_chat_data.php';
    
    -- Gọi file PHP
    SET @result = sys_exec(CONCAT('php "', @script_path, '"'));
    
    RETURN 1;
END //

-- Triggers cho bảng employees
CREATE TRIGGER after_employee_insert
AFTER INSERT ON employees
FOR EACH ROW
BEGIN
    SELECT call_sync_script() INTO @dummy;
END //

CREATE TRIGGER after_employee_update
AFTER UPDATE ON employees
FOR EACH ROW
BEGIN
    SELECT call_sync_script() INTO @dummy;
END //

CREATE TRIGGER after_employee_delete
AFTER DELETE ON employees
FOR EACH ROW
BEGIN
    SELECT call_sync_script() INTO @dummy;
END //

-- Triggers cho bảng departments
CREATE TRIGGER after_department_insert
AFTER INSERT ON departments
FOR EACH ROW
BEGIN
    SELECT call_sync_script() INTO @dummy;
END //

CREATE TRIGGER after_department_update
AFTER UPDATE ON departments
FOR EACH ROW
BEGIN
    SELECT call_sync_script() INTO @dummy;
END //

CREATE TRIGGER after_department_delete
AFTER DELETE ON departments
FOR EACH ROW
BEGIN
    SELECT call_sync_script() INTO @dummy;
END //

-- Triggers cho bảng positions
CREATE TRIGGER after_position_insert
AFTER INSERT ON positions
FOR EACH ROW
BEGIN
    SELECT call_sync_script() INTO @dummy;
END //

CREATE TRIGGER after_position_update
AFTER UPDATE ON positions
FOR EACH ROW
BEGIN
    SELECT call_sync_script() INTO @dummy;
END //

CREATE TRIGGER after_position_delete
AFTER DELETE ON positions
FOR EACH ROW
BEGIN
    SELECT call_sync_script() INTO @dummy;
END //

-- Triggers cho bảng users
CREATE TRIGGER after_user_insert
AFTER INSERT ON users
FOR EACH ROW
BEGIN
    SELECT call_sync_script() INTO @dummy;
END //

CREATE TRIGGER after_user_update
AFTER UPDATE ON users
FOR EACH ROW
BEGIN
    SELECT call_sync_script() INTO @dummy;
END //

CREATE TRIGGER after_user_delete
AFTER DELETE ON users
FOR EACH ROW
BEGIN
    SELECT call_sync_script() INTO @dummy;
END //

-- Triggers cho bảng user_profiles
CREATE TRIGGER after_user_profile_insert
AFTER INSERT ON user_profiles
FOR EACH ROW
BEGIN
    SELECT call_sync_script() INTO @dummy;
END //

CREATE TRIGGER after_user_profile_update
AFTER UPDATE ON user_profiles
FOR EACH ROW
BEGIN
    SELECT call_sync_script() INTO @dummy;
END //

CREATE TRIGGER after_user_profile_delete
AFTER DELETE ON user_profiles
FOR EACH ROW
BEGIN
    SELECT call_sync_script() INTO @dummy;
END //

-- Triggers cho bảng leaves
CREATE TRIGGER after_leave_insert
AFTER INSERT ON leaves
FOR EACH ROW
BEGIN
    SELECT call_sync_script() INTO @dummy;
END //

CREATE TRIGGER after_leave_update
AFTER UPDATE ON leaves
FOR EACH ROW
BEGIN
    SELECT call_sync_script() INTO @dummy;
END //

CREATE TRIGGER after_leave_delete
AFTER DELETE ON leaves
FOR EACH ROW
BEGIN
    SELECT call_sync_script() INTO @dummy;
END //

-- Triggers cho bảng attendance
CREATE TRIGGER after_attendance_insert
AFTER INSERT ON attendance
FOR EACH ROW
BEGIN
    SELECT call_sync_script() INTO @dummy;
END //

CREATE TRIGGER after_attendance_update
AFTER UPDATE ON attendance
FOR EACH ROW
BEGIN
    SELECT call_sync_script() INTO @dummy;
END //

CREATE TRIGGER after_attendance_delete
AFTER DELETE ON attendance
FOR EACH ROW
BEGIN
    SELECT call_sync_script() INTO @dummy;
END //

-- Triggers cho bảng salaries
CREATE TRIGGER after_salary_insert
AFTER INSERT ON salaries
FOR EACH ROW
BEGIN
    SELECT call_sync_script() INTO @dummy;
END //

CREATE TRIGGER after_salary_update
AFTER UPDATE ON salaries
FOR EACH ROW
BEGIN
    SELECT call_sync_script() INTO @dummy;
END //

CREATE TRIGGER after_salary_delete
AFTER DELETE ON salaries
FOR EACH ROW
BEGIN
    SELECT call_sync_script() INTO @dummy;
END //

-- Triggers cho bảng training_courses
CREATE TRIGGER after_training_course_insert
AFTER INSERT ON training_courses
FOR EACH ROW
BEGIN
    SELECT call_sync_script() INTO @dummy;
END //

CREATE TRIGGER after_training_course_update
AFTER UPDATE ON training_courses
FOR EACH ROW
BEGIN
    SELECT call_sync_script() INTO @dummy;
END //

CREATE TRIGGER after_training_course_delete
AFTER DELETE ON training_courses
FOR EACH ROW
BEGIN
    SELECT call_sync_script() INTO @dummy;
END //

-- Triggers cho bảng performances
CREATE TRIGGER after_performance_insert
AFTER INSERT ON performances
FOR EACH ROW
BEGIN
    SELECT call_sync_script() INTO @dummy;
END //

CREATE TRIGGER after_performance_update
AFTER UPDATE ON performances
FOR EACH ROW
BEGIN
    SELECT call_sync_script() INTO @dummy;
END //

CREATE TRIGGER after_performance_delete
AFTER DELETE ON performances
FOR EACH ROW
BEGIN
    SELECT call_sync_script() INTO @dummy;
END //

DELIMITER ; 