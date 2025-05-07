-- Tạm thời bỏ trigger để sử dụng plain text password
-- Sẽ cập nhật lại sau khi implement proper password hashing

DELIMITER //

CREATE TRIGGER before_user_insert
BEFORE INSERT ON users
FOR EACH ROW
BEGIN
    IF NEW.password_hash IS NOT NULL THEN
        SET NEW.password_hash = PASSWORD(NEW.password_hash);
    END IF;
END//

CREATE TRIGGER before_user_update
BEFORE UPDATE ON users
FOR EACH ROW
BEGIN
    IF NEW.password_hash IS NOT NULL AND NEW.password_hash != OLD.password_hash THEN
        SET NEW.password_hash = PASSWORD(NEW.password_hash);
    END IF;
END//

DELIMITER ; 