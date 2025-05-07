-- Thêm dữ liệu mẫu cho bảng performances
INSERT INTO performances (employee_id, evaluation_date, score, comments, created_at, updated_at)
SELECT 
    e.id as employee_id,
    DATE_ADD('2025-01-01', INTERVAL FLOOR(RAND() * 365) DAY) as evaluation_date,
    ROUND(RAND() * 5 + 5, 2) as score,
    CONCAT('Đánh giá hiệu suất quý ', QUARTER(evaluation_date)) as comments,
    NOW() as created_at,
    NOW() as updated_at
FROM employees e
CROSS JOIN (SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4) as quarters
WHERE e.id IN (SELECT id FROM employees ORDER BY RAND() LIMIT 20);

-- Thêm dữ liệu mẫu cho bảng payroll
INSERT INTO payroll (employee_id, payment_date, basic_salary, allowances, deductions, net_salary, status, created_at, updated_at)
SELECT 
    e.id as employee_id,
    DATE_ADD('2025-01-01', INTERVAL FLOOR(RAND() * 12) MONTH) as payment_date,
    ROUND(RAND() * 10000000 + 5000000, -3) as basic_salary,
    ROUND(basic_salary * 0.2, -3) as allowances,
    ROUND(basic_salary * 0.1, -3) as deductions,
    basic_salary + allowances - deductions as net_salary,
    'paid' as status,
    NOW() as created_at,
    NOW() as updated_at
FROM employees e
WHERE e.id IN (SELECT id FROM employees ORDER BY RAND() LIMIT 15);

-- Thêm dữ liệu mẫu cho bảng leaves
INSERT INTO leaves (employee_id, leave_type, start_date, end_date, reason, status, created_at, updated_at)
SELECT 
    e.id as employee_id,
    ELT(FLOOR(RAND() * 4) + 1, 'annual', 'sick', 'maternity', 'unpaid') as leave_type,
    DATE_ADD('2025-01-01', INTERVAL FLOOR(RAND() * 365) DAY) as start_date,
    DATE_ADD(start_date, INTERVAL FLOOR(RAND() * 5) + 1 DAY) as end_date,
    CONCAT('Lý do nghỉ ', leave_type) as reason,
    'approved' as status,
    NOW() as created_at,
    NOW() as updated_at
FROM employees e
WHERE e.id IN (SELECT id FROM employees ORDER BY RAND() LIMIT 10);

-- Thêm dữ liệu mẫu cho bảng job_applications
INSERT INTO job_applications (position_id, candidate_name, email, phone, application_date, status, created_at, updated_at)
SELECT 
    p.id as position_id,
    CONCAT('Ứng viên ', FLOOR(RAND() * 1000)) as candidate_name,
    CONCAT('candidate', FLOOR(RAND() * 1000), '@example.com') as email,
    CONCAT('09', FLOOR(RAND() * 100000000)) as phone,
    DATE_ADD('2025-01-01', INTERVAL FLOOR(RAND() * 365) DAY) as application_date,
    ELT(FLOOR(RAND() * 4) + 1, 'pending', 'interviewing', 'accepted', 'rejected') as status,
    NOW() as created_at,
    NOW() as updated_at
FROM job_positions p
CROSS JOIN (SELECT 1 UNION SELECT 2 UNION SELECT 3) as dummy
WHERE p.id IN (SELECT id FROM job_positions ORDER BY RAND() LIMIT 5);

-- Thêm dữ liệu mẫu cho bảng training_courses
INSERT INTO training_courses (course_name, course_type, start_date, end_date, location, max_participants, created_at, updated_at)
VALUES 
('Kỹ năng lãnh đạo', 'leadership', '2025-01-15', '2025-01-17', 'Phòng họp A', 20, NOW(), NOW()),
('Quản lý dự án', 'project_management', '2025-02-01', '2025-02-03', 'Phòng họp B', 15, NOW(), NOW()),
('Kỹ năng giao tiếp', 'communication', '2025-03-10', '2025-03-12', 'Phòng họp C', 25, NOW(), NOW()),
('Đào tạo kỹ thuật', 'technical', '2025-04-05', '2025-04-07', 'Phòng họp D', 30, NOW(), NOW());

-- Thêm dữ liệu mẫu cho bảng training_registrations
INSERT INTO training_registrations (course_id, employee_id, registration_date, status, created_at, updated_at)
SELECT 
    tc.id as course_id,
    e.id as employee_id,
    DATE_SUB(tc.start_date, INTERVAL FLOOR(RAND() * 7) DAY) as registration_date,
    'registered' as status,
    NOW() as created_at,
    NOW() as updated_at
FROM training_courses tc
CROSS JOIN employees e
WHERE e.id IN (SELECT id FROM employees ORDER BY RAND() LIMIT 10);

-- Thêm dữ liệu mẫu cho bảng assets
INSERT INTO assets (asset_name, asset_type, purchase_date, status, created_at, updated_at)
VALUES 
('Máy tính xách tay Dell', 'laptop', '2025-01-10', 'available', NOW(), NOW()),
('Máy tính để bàn HP', 'desktop', '2025-01-15', 'assigned', NOW(), NOW()),
('Máy in Canon', 'printer', '2025-02-01', 'maintenance', NOW(), NOW()),
('Máy chiếu Epson', 'projector', '2025-02-10', 'available', NOW(), NOW()),
('Điện thoại Samsung', 'phone', '2025-03-01', 'assigned', NOW(), NOW()),
('Máy quét Fujitsu', 'scanner', '2025-03-15', 'available', NOW(), NOW()),
('Máy fax Brother', 'fax', '2025-04-01', 'maintenance', NOW(), NOW()),
('Máy chủ Dell', 'server', '2025-04-10', 'available', NOW(), NOW()),
('Switch Cisco', 'network', '2025-04-15', 'assigned', NOW(), NOW()),
('Router TP-Link', 'network', '2025-04-20', 'available', NOW(), NOW()); 