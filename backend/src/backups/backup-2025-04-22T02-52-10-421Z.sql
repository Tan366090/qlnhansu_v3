

-- Table structure for activities
CREATE TABLE `activities` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `status` enum('success','warning','error','active') DEFAULT 'success',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `activities_user_id_foreign` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for activities
INSERT INTO activities VALUES (1, 1, 'login', 'User logged in', NULL, NULL, 'success', Mon Apr 21 2025 09:23:14 GMT+0700 (Indochina Time));
INSERT INTO activities VALUES (2, 1, 'login', 'Admin logged into the system', 'Mozilla/5.0 (Windows NT 10.0)', '192.168.1.100', 'success', Mon Apr 21 2025 09:24:14 GMT+0700 (Indochina Time));
INSERT INTO activities VALUES (3, 2, 'update_profile', 'Updated personal information', 'Mozilla/5.0 (Windows NT 10.0)', '192.168.1.101', 'success', Mon Apr 21 2025 09:24:24 GMT+0700 (Indochina Time));
INSERT INTO activities VALUES (4, 3, 'view_document', 'Accessed employee handbook', 'Mozilla/5.0 (Windows NT 10.0)', '192.168.1.102', 'success', Mon Apr 21 2025 09:24:40 GMT+0700 (Indochina Time));
INSERT INTO activities VALUES (5, 1, 'approve_leave', 'Approved leave request for employee ID 2', 'Mozilla/5.0 (Windows NT 10.0)', '192.168.1.100', 'success', Mon Apr 21 2025 09:24:51 GMT+0700 (Indochina Time));
INSERT INTO activities VALUES (6, 6, 'login', 'Operations Manager logged in', 'Mozilla/5.0 (Windows NT 10.0)', '192.168.1.106', 'success', Mon Apr 21 2025 09:25:14 GMT+0700 (Indochina Time));
INSERT INTO activities VALUES (7, 7, 'update_profile', 'Updated HR information', 'Mozilla/5.0 (Windows NT 10.0)', '192.168.1.107', 'success', Mon Apr 21 2025 09:25:24 GMT+0700 (Indochina Time));
INSERT INTO activities VALUES (8, 8, 'view_document', 'Accessed development guidelines', 'Mozilla/5.0 (Windows NT 10.0)', '192.168.1.108', 'success', Mon Apr 21 2025 09:25:40 GMT+0700 (Indochina Time));
INSERT INTO activities VALUES (9, 9, 'approve_leave', 'Approved leave request for employee ID 3', 'Mozilla/5.0 (Windows NT 10.0)', '192.168.1.109', 'success', Mon Apr 21 2025 09:25:51 GMT+0700 (Indochina Time));
INSERT INTO activities VALUES (10, 10, 'login', 'Marketing Specialist logged in', 'Mozilla/5.0 (Windows NT 10.0)', '192.168.1.110', 'success', Mon Apr 21 2025 09:26:14 GMT+0700 (Indochina Time));


-- Table structure for attendance
CREATE TABLE `attendance` (
  `attendance_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `attendance_date` date NOT NULL,
  `recorded_at` datetime DEFAULT current_timestamp(),
  `notes` text DEFAULT NULL,
  `attendance_symbol` varchar(10) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`attendance_id`),
  KEY `user_id` (`user_id`),
  KEY `idx_attendance_date_user` (`attendance_date`,`user_id`),
  CONSTRAINT `fk_attendance_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=55 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data for attendance
INSERT INTO attendance VALUES (1, 1, Fri Mar 01 2024 00:00:00 GMT+0700 (Indochina Time), Fri Mar 01 2024 08:00:00 GMT+0700 (Indochina Time), 'On time', 'P', Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO attendance VALUES (2, 2, Fri Mar 01 2024 00:00:00 GMT+0700 (Indochina Time), Fri Mar 01 2024 08:05:00 GMT+0700 (Indochina Time), 'On time', 'P', Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO attendance VALUES (3, 3, Fri Mar 01 2024 00:00:00 GMT+0700 (Indochina Time), Fri Mar 01 2024 08:10:00 GMT+0700 (Indochina Time), 'On time', 'P', Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO attendance VALUES (4, 4, Fri Mar 01 2024 00:00:00 GMT+0700 (Indochina Time), Fri Mar 01 2024 08:00:00 GMT+0700 (Indochina Time), 'On time', 'P', Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO attendance VALUES (5, 5, Fri Mar 01 2024 00:00:00 GMT+0700 (Indochina Time), Fri Mar 01 2024 08:15:00 GMT+0700 (Indochina Time), 'On time', 'P', Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO attendance VALUES (6, 6, Fri Mar 01 2024 00:00:00 GMT+0700 (Indochina Time), Fri Mar 01 2024 08:00:00 GMT+0700 (Indochina Time), 'On time', 'P', Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO attendance VALUES (7, 7, Fri Mar 01 2024 00:00:00 GMT+0700 (Indochina Time), Fri Mar 01 2024 08:20:00 GMT+0700 (Indochina Time), 'On time', 'P', Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO attendance VALUES (8, 8, Fri Mar 01 2024 00:00:00 GMT+0700 (Indochina Time), Fri Mar 01 2024 08:00:00 GMT+0700 (Indochina Time), 'On time', 'P', Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO attendance VALUES (9, 9, Fri Mar 01 2024 00:00:00 GMT+0700 (Indochina Time), Fri Mar 01 2024 08:25:00 GMT+0700 (Indochina Time), 'On time', 'P', Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO attendance VALUES (10, 10, Fri Mar 01 2024 00:00:00 GMT+0700 (Indochina Time), Fri Mar 01 2024 08:00:00 GMT+0700 (Indochina Time), 'On time', 'P', Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO attendance VALUES (11, 1, Mon Apr 15 2024 00:00:00 GMT+0700 (Indochina Time), Tue Apr 22 2025 03:20:24 GMT+0700 (Indochina Time), 'Check-in Ä‘Ãºng giá»', 'P', Tue Apr 22 2025 03:20:24 GMT+0700 (Indochina Time));
INSERT INTO attendance VALUES (12, 2, Mon Apr 15 2024 00:00:00 GMT+0700 (Indochina Time), Tue Apr 22 2025 03:20:24 GMT+0700 (Indochina Time), 'Check-in Ä‘Ãºng giá»', 'P', Tue Apr 22 2025 03:20:24 GMT+0700 (Indochina Time));
INSERT INTO attendance VALUES (13, 3, Mon Apr 15 2024 00:00:00 GMT+0700 (Indochina Time), Tue Apr 22 2025 03:20:24 GMT+0700 (Indochina Time), 'Check-in Ä‘Ãºng giá»', 'P', Tue Apr 22 2025 03:20:24 GMT+0700 (Indochina Time));
INSERT INTO attendance VALUES (14, 4, Mon Apr 15 2024 00:00:00 GMT+0700 (Indochina Time), Tue Apr 22 2025 03:20:24 GMT+0700 (Indochina Time), 'Check-in Ä‘Ãºng giá»', 'P', Tue Apr 22 2025 03:20:24 GMT+0700 (Indochina Time));
INSERT INTO attendance VALUES (15, 5, Mon Apr 15 2024 00:00:00 GMT+0700 (Indochina Time), Tue Apr 22 2025 03:20:24 GMT+0700 (Indochina Time), 'Check-in Ä‘Ãºng giá»', 'P', Tue Apr 22 2025 03:20:24 GMT+0700 (Indochina Time));
INSERT INTO attendance VALUES (16, 1, Tue Apr 16 2024 00:00:00 GMT+0700 (Indochina Time), Tue Apr 22 2025 03:20:24 GMT+0700 (Indochina Time), 'Check-in Ä‘Ãºng giá»', 'P', Tue Apr 22 2025 03:20:24 GMT+0700 (Indochina Time));
INSERT INTO attendance VALUES (17, 2, Tue Apr 16 2024 00:00:00 GMT+0700 (Indochina Time), Tue Apr 22 2025 03:20:24 GMT+0700 (Indochina Time), 'Check-in Ä‘Ãºng giá»', 'P', Tue Apr 22 2025 03:20:24 GMT+0700 (Indochina Time));
INSERT INTO attendance VALUES (18, 3, Tue Apr 16 2024 00:00:00 GMT+0700 (Indochina Time), Tue Apr 22 2025 03:20:24 GMT+0700 (Indochina Time), 'Check-in Ä‘Ãºng giá»', 'P', Tue Apr 22 2025 03:20:24 GMT+0700 (Indochina Time));
INSERT INTO attendance VALUES (19, 4, Tue Apr 16 2024 00:00:00 GMT+0700 (Indochina Time), Tue Apr 22 2025 03:20:24 GMT+0700 (Indochina Time), 'Check-in Ä‘Ãºng giá»', 'P', Tue Apr 22 2025 03:20:24 GMT+0700 (Indochina Time));
INSERT INTO attendance VALUES (20, 5, Tue Apr 16 2024 00:00:00 GMT+0700 (Indochina Time), Tue Apr 22 2025 03:20:24 GMT+0700 (Indochina Time), 'Check-in Ä‘Ãºng giá»', 'P', Tue Apr 22 2025 03:20:24 GMT+0700 (Indochina Time));
INSERT INTO attendance VALUES (21, 1, Wed Apr 17 2024 00:00:00 GMT+0700 (Indochina Time), Tue Apr 22 2025 03:20:24 GMT+0700 (Indochina Time), 'Check-in Ä‘Ãºng giá»', 'P', Tue Apr 22 2025 03:20:24 GMT+0700 (Indochina Time));
INSERT INTO attendance VALUES (22, 2, Wed Apr 17 2024 00:00:00 GMT+0700 (Indochina Time), Tue Apr 22 2025 03:20:24 GMT+0700 (Indochina Time), 'Check-in Ä‘Ãºng giá»', 'P', Tue Apr 22 2025 03:20:24 GMT+0700 (Indochina Time));
INSERT INTO attendance VALUES (23, 3, Wed Apr 17 2024 00:00:00 GMT+0700 (Indochina Time), Tue Apr 22 2025 03:20:24 GMT+0700 (Indochina Time), 'Check-in Ä‘Ãºng giá»', 'P', Tue Apr 22 2025 03:20:24 GMT+0700 (Indochina Time));
INSERT INTO attendance VALUES (24, 4, Wed Apr 17 2024 00:00:00 GMT+0700 (Indochina Time), Tue Apr 22 2025 03:20:24 GMT+0700 (Indochina Time), 'Check-in Ä‘Ãºng giá»', 'P', Tue Apr 22 2025 03:20:24 GMT+0700 (Indochina Time));
INSERT INTO attendance VALUES (25, 5, Wed Apr 17 2024 00:00:00 GMT+0700 (Indochina Time), Tue Apr 22 2025 03:20:24 GMT+0700 (Indochina Time), 'Check-in Ä‘Ãºng giá»', 'P', Tue Apr 22 2025 03:20:24 GMT+0700 (Indochina Time));
INSERT INTO attendance VALUES (26, 1, Thu Apr 18 2024 00:00:00 GMT+0700 (Indochina Time), Tue Apr 22 2025 03:20:24 GMT+0700 (Indochina Time), 'Check-in Ä‘Ãºng giá»', 'P', Tue Apr 22 2025 03:20:24 GMT+0700 (Indochina Time));
INSERT INTO attendance VALUES (27, 2, Thu Apr 18 2024 00:00:00 GMT+0700 (Indochina Time), Tue Apr 22 2025 03:20:24 GMT+0700 (Indochina Time), 'Check-in Ä‘Ãºng giá»', 'P', Tue Apr 22 2025 03:20:24 GMT+0700 (Indochina Time));
INSERT INTO attendance VALUES (28, 3, Thu Apr 18 2024 00:00:00 GMT+0700 (Indochina Time), Tue Apr 22 2025 03:20:24 GMT+0700 (Indochina Time), 'Check-in Ä‘Ãºng giá»', 'P', Tue Apr 22 2025 03:20:24 GMT+0700 (Indochina Time));
INSERT INTO attendance VALUES (29, 4, Thu Apr 18 2024 00:00:00 GMT+0700 (Indochina Time), Tue Apr 22 2025 03:20:24 GMT+0700 (Indochina Time), 'Check-in Ä‘Ãºng giá»', 'P', Tue Apr 22 2025 03:20:24 GMT+0700 (Indochina Time));
INSERT INTO attendance VALUES (30, 5, Thu Apr 18 2024 00:00:00 GMT+0700 (Indochina Time), Tue Apr 22 2025 03:20:24 GMT+0700 (Indochina Time), 'Check-in Ä‘Ãºng giá»', 'P', Tue Apr 22 2025 03:20:24 GMT+0700 (Indochina Time));
INSERT INTO attendance VALUES (31, 1, Fri Apr 19 2024 00:00:00 GMT+0700 (Indochina Time), Tue Apr 22 2025 03:20:24 GMT+0700 (Indochina Time), 'Check-in Ä‘Ãºng giá»', 'P', Tue Apr 22 2025 03:20:24 GMT+0700 (Indochina Time));
INSERT INTO attendance VALUES (32, 2, Fri Apr 19 2024 00:00:00 GMT+0700 (Indochina Time), Tue Apr 22 2025 03:20:24 GMT+0700 (Indochina Time), 'Check-in Ä‘Ãºng giá»', 'P', Tue Apr 22 2025 03:20:24 GMT+0700 (Indochina Time));
INSERT INTO attendance VALUES (33, 3, Fri Apr 19 2024 00:00:00 GMT+0700 (Indochina Time), Tue Apr 22 2025 03:20:24 GMT+0700 (Indochina Time), 'Check-in Ä‘Ãºng giá»', 'P', Tue Apr 22 2025 03:20:24 GMT+0700 (Indochina Time));
INSERT INTO attendance VALUES (34, 4, Fri Apr 19 2024 00:00:00 GMT+0700 (Indochina Time), Tue Apr 22 2025 03:20:24 GMT+0700 (Indochina Time), 'Check-in Ä‘Ãºng giá»', 'P', Tue Apr 22 2025 03:20:24 GMT+0700 (Indochina Time));
INSERT INTO attendance VALUES (35, 5, Fri Apr 19 2024 00:00:00 GMT+0700 (Indochina Time), Tue Apr 22 2025 03:20:24 GMT+0700 (Indochina Time), 'Check-in Ä‘Ãºng giá»', 'P', Tue Apr 22 2025 03:20:24 GMT+0700 (Indochina Time));
INSERT INTO attendance VALUES (36, 1, Sat Apr 20 2024 00:00:00 GMT+0700 (Indochina Time), Tue Apr 22 2025 03:20:24 GMT+0700 (Indochina Time), 'Check-in Ä‘Ãºng giá»', 'P', Tue Apr 22 2025 03:20:24 GMT+0700 (Indochina Time));
INSERT INTO attendance VALUES (37, 2, Sat Apr 20 2024 00:00:00 GMT+0700 (Indochina Time), Tue Apr 22 2025 03:20:24 GMT+0700 (Indochina Time), 'Check-in Ä‘Ãºng giá»', 'P', Tue Apr 22 2025 03:20:24 GMT+0700 (Indochina Time));
INSERT INTO attendance VALUES (38, 3, Sat Apr 20 2024 00:00:00 GMT+0700 (Indochina Time), Tue Apr 22 2025 03:20:24 GMT+0700 (Indochina Time), 'Check-in Ä‘Ãºng giá»', 'P', Tue Apr 22 2025 03:20:24 GMT+0700 (Indochina Time));
INSERT INTO attendance VALUES (39, 4, Sat Apr 20 2024 00:00:00 GMT+0700 (Indochina Time), Tue Apr 22 2025 03:20:24 GMT+0700 (Indochina Time), 'Check-in Ä‘Ãºng giá»', 'P', Tue Apr 22 2025 03:20:24 GMT+0700 (Indochina Time));
INSERT INTO attendance VALUES (40, 5, Sat Apr 20 2024 00:00:00 GMT+0700 (Indochina Time), Tue Apr 22 2025 03:20:24 GMT+0700 (Indochina Time), 'Check-in Ä‘Ãºng giá»', 'P', Tue Apr 22 2025 03:20:24 GMT+0700 (Indochina Time));
INSERT INTO attendance VALUES (41, 1, Sun Apr 21 2024 00:00:00 GMT+0700 (Indochina Time), Tue Apr 22 2025 03:20:24 GMT+0700 (Indochina Time), 'Check-in Ä‘Ãºng giá»', 'P', Tue Apr 22 2025 03:20:24 GMT+0700 (Indochina Time));
INSERT INTO attendance VALUES (42, 2, Sun Apr 21 2024 00:00:00 GMT+0700 (Indochina Time), Tue Apr 22 2025 03:20:24 GMT+0700 (Indochina Time), 'Check-in Ä‘Ãºng giá»', 'P', Tue Apr 22 2025 03:20:24 GMT+0700 (Indochina Time));
INSERT INTO attendance VALUES (43, 3, Sun Apr 21 2024 00:00:00 GMT+0700 (Indochina Time), Tue Apr 22 2025 03:20:24 GMT+0700 (Indochina Time), 'Check-in Ä‘Ãºng giá»', 'P', Tue Apr 22 2025 03:20:24 GMT+0700 (Indochina Time));
INSERT INTO attendance VALUES (44, 4, Sun Apr 21 2024 00:00:00 GMT+0700 (Indochina Time), Tue Apr 22 2025 03:20:24 GMT+0700 (Indochina Time), 'Check-in Ä‘Ãºng giá»', 'P', Tue Apr 22 2025 03:20:24 GMT+0700 (Indochina Time));
INSERT INTO attendance VALUES (45, 5, Sun Apr 21 2024 00:00:00 GMT+0700 (Indochina Time), Tue Apr 22 2025 03:20:24 GMT+0700 (Indochina Time), 'Check-in Ä‘Ãºng giá»', 'P', Tue Apr 22 2025 03:20:24 GMT+0700 (Indochina Time));
INSERT INTO attendance VALUES (46, 2, Mon Apr 21 2025 00:00:00 GMT+0700 (Indochina Time), Mon Apr 21 2025 22:24:39 GMT+0700 (Indochina Time), 'Check-in Ä‘Ãºng giá»', 'P', Mon Apr 21 2025 22:24:39 GMT+0700 (Indochina Time));
INSERT INTO attendance VALUES (47, 3, Mon Apr 21 2025 00:00:00 GMT+0700 (Indochina Time), Mon Apr 21 2025 22:24:39 GMT+0700 (Indochina Time), 'Check-in Ä‘Ãºng giá»', 'P', Mon Apr 21 2025 22:24:39 GMT+0700 (Indochina Time));
INSERT INTO attendance VALUES (48, 4, Mon Apr 21 2025 00:00:00 GMT+0700 (Indochina Time), Mon Apr 21 2025 22:24:39 GMT+0700 (Indochina Time), 'Check-in Ä‘Ãºng giá»', 'P', Mon Apr 21 2025 22:24:39 GMT+0700 (Indochina Time));
INSERT INTO attendance VALUES (49, 5, Mon Apr 21 2025 00:00:00 GMT+0700 (Indochina Time), Mon Apr 21 2025 22:24:39 GMT+0700 (Indochina Time), 'Check-in Ä‘Ãºng giá»', 'P', Mon Apr 21 2025 22:24:39 GMT+0700 (Indochina Time));
INSERT INTO attendance VALUES (50, 6, Mon Apr 21 2025 00:00:00 GMT+0700 (Indochina Time), Mon Apr 21 2025 22:24:39 GMT+0700 (Indochina Time), 'Check-in Ä‘Ãºng giá»', 'P', Mon Apr 21 2025 22:24:39 GMT+0700 (Indochina Time));
INSERT INTO attendance VALUES (51, 7, Mon Apr 21 2025 00:00:00 GMT+0700 (Indochina Time), Mon Apr 21 2025 22:24:39 GMT+0700 (Indochina Time), 'Check-in Ä‘Ãºng giá»', 'P', Mon Apr 21 2025 22:24:39 GMT+0700 (Indochina Time));
INSERT INTO attendance VALUES (52, 8, Mon Apr 21 2025 00:00:00 GMT+0700 (Indochina Time), Mon Apr 21 2025 22:24:39 GMT+0700 (Indochina Time), 'Check-in Ä‘Ãºng giá»', 'P', Mon Apr 21 2025 22:24:39 GMT+0700 (Indochina Time));
INSERT INTO attendance VALUES (53, 9, Mon Apr 21 2025 00:00:00 GMT+0700 (Indochina Time), Mon Apr 21 2025 22:24:39 GMT+0700 (Indochina Time), 'Check-in Ä‘Ãºng giá»', 'P', Mon Apr 21 2025 22:24:39 GMT+0700 (Indochina Time));
INSERT INTO attendance VALUES (54, 10, Mon Apr 21 2025 00:00:00 GMT+0700 (Indochina Time), Mon Apr 21 2025 22:24:39 GMT+0700 (Indochina Time), 'Check-in Ä‘Ãºng giá»', 'P', Mon Apr 21 2025 22:24:39 GMT+0700 (Indochina Time));


-- Table structure for audit_logs
CREATE TABLE `audit_logs` (
  `log_id` bigint(20) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action_type` varchar(100) NOT NULL,
  `target_entity` varchar(100) DEFAULT NULL,
  `target_entity_id` int(11) DEFAULT NULL,
  `timestamp` datetime DEFAULT current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`details`)),
  PRIMARY KEY (`log_id`),
  KEY `idx_audit_logs_user_id` (`user_id`),
  KEY `idx_audit_logs_timestamp` (`timestamp`),
  KEY `idx_audit_logs_action_type` (`action_type`),
  KEY `idx_audit_logs_target` (`target_entity`,`target_entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



-- Table structure for bonuses
CREATE TABLE `bonuses` (
  `bonus_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `bonus_type` varchar(20) NOT NULL,
  `amount` decimal(15,2) DEFAULT NULL,
  `days_off` decimal(4,1) DEFAULT NULL,
  `reason` text NOT NULL,
  `effective_date` date NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `added_by_user_id` int(11) NOT NULL,
  PRIMARY KEY (`bonus_id`),
  KEY `user_id` (`user_id`),
  KEY `added_by_user_id` (`added_by_user_id`),
  CONSTRAINT `fk_bonuses_added_by` FOREIGN KEY (`added_by_user_id`) REFERENCES `users` (`user_id`),
  CONSTRAINT `fk_bonuses_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data for bonuses
INSERT INTO bonuses VALUES (1, 1, 'performance', '1000000.00', NULL, 'Outstanding performance Q1 2024', Sun Mar 31 2024 00:00:00 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), 1);
INSERT INTO bonuses VALUES (2, 2, 'performance', '800000.00', NULL, 'Good performance Q1 2024', Sun Mar 31 2024 00:00:00 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), 1);
INSERT INTO bonuses VALUES (3, 3, 'performance', '800000.00', NULL, 'Good performance Q1 2024', Sun Mar 31 2024 00:00:00 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), 1);
INSERT INTO bonuses VALUES (4, 4, 'performance', '800000.00', NULL, 'Good performance Q1 2024', Sun Mar 31 2024 00:00:00 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), 1);
INSERT INTO bonuses VALUES (5, 5, 'performance', '800000.00', NULL, 'Good performance Q1 2024', Sun Mar 31 2024 00:00:00 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), 1);
INSERT INTO bonuses VALUES (6, 6, 'performance', '800000.00', NULL, 'Good performance Q1 2024', Sun Mar 31 2024 00:00:00 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), 1);
INSERT INTO bonuses VALUES (7, 7, 'performance', '500000.00', NULL, 'Good performance Q1 2024', Sun Mar 31 2024 00:00:00 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), 1);
INSERT INTO bonuses VALUES (8, 8, 'performance', '600000.00', NULL, 'Good performance Q1 2024', Sun Mar 31 2024 00:00:00 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), 1);
INSERT INTO bonuses VALUES (9, 9, 'performance', '500000.00', NULL, 'Good performance Q1 2024', Sun Mar 31 2024 00:00:00 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), 1);
INSERT INTO bonuses VALUES (10, 10, 'performance', '500000.00', NULL, 'Good performance Q1 2024', Sun Mar 31 2024 00:00:00 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), 1);


-- Table structure for degrees
CREATE TABLE `degrees` (
  `degree_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `degree_name` varchar(255) NOT NULL,
  `issue_date` date NOT NULL,
  `expiry_date` date DEFAULT NULL,
  `validity` varchar(50) DEFAULT NULL,
  `attachment_url` varchar(512) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`degree_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for degrees
INSERT INTO degrees VALUES (1, 1, 'Bachelor of Computer Science', Mon Jun 15 2015 00:00:00 GMT+0700 (Indochina Time), NULL, 'Permanent', NULL, Sat Apr 19 2025 23:00:19 GMT+0700 (Indochina Time), Sat Apr 19 2025 23:00:19 GMT+0700 (Indochina Time), 1);
INSERT INTO degrees VALUES (2, 2, 'Master of Business Administration', Sun May 20 2018 00:00:00 GMT+0700 (Indochina Time), NULL, 'Permanent', NULL, Sat Apr 19 2025 23:00:19 GMT+0700 (Indochina Time), Sat Apr 19 2025 23:00:19 GMT+0700 (Indochina Time), 1);
INSERT INTO degrees VALUES (3, 3, 'Bachelor of Information Technology', Sun Jul 10 2016 00:00:00 GMT+0700 (Indochina Time), NULL, 'Permanent', NULL, Sat Apr 19 2025 23:00:19 GMT+0700 (Indochina Time), Sat Apr 19 2025 23:00:19 GMT+0700 (Indochina Time), 1);
INSERT INTO degrees VALUES (4, 1, 'Bachelor of Business Administration', Mon Jun 15 2015 00:00:00 GMT+0700 (Indochina Time), NULL, 'Permanent', NULL, Sat Apr 19 2025 23:06:24 GMT+0700 (Indochina Time), Sat Apr 19 2025 23:06:24 GMT+0700 (Indochina Time), 1);
INSERT INTO degrees VALUES (5, 2, 'Master of Computer Science', Sun May 20 2018 00:00:00 GMT+0700 (Indochina Time), NULL, 'Permanent', NULL, Sat Apr 19 2025 23:06:24 GMT+0700 (Indochina Time), Sat Apr 19 2025 23:06:24 GMT+0700 (Indochina Time), 1);
INSERT INTO degrees VALUES (6, 3, 'Bachelor of Information Technology', Sun Jul 10 2016 00:00:00 GMT+0700 (Indochina Time), NULL, 'Permanent', NULL, Sat Apr 19 2025 23:06:24 GMT+0700 (Indochina Time), Sat Apr 19 2025 23:06:24 GMT+0700 (Indochina Time), 1);
INSERT INTO degrees VALUES (7, 7, 'Bachelor of Human Resources', Thu Jun 15 2017 00:00:00 GMT+0700 (Indochina Time), NULL, 'Permanent', NULL, Sat Apr 19 2025 23:00:19 GMT+0700 (Indochina Time), Sat Apr 19 2025 23:00:19 GMT+0700 (Indochina Time), 1);
INSERT INTO degrees VALUES (8, 8, 'Bachelor of Computer Science', Sun May 20 2018 00:00:00 GMT+0700 (Indochina Time), NULL, 'Permanent', NULL, Sat Apr 19 2025 23:00:19 GMT+0700 (Indochina Time), Sat Apr 19 2025 23:00:19 GMT+0700 (Indochina Time), 1);
INSERT INTO degrees VALUES (9, 9, 'Bachelor of Accounting', Wed Jul 10 2019 00:00:00 GMT+0700 (Indochina Time), NULL, 'Permanent', NULL, Sat Apr 19 2025 23:00:19 GMT+0700 (Indochina Time), Sat Apr 19 2025 23:00:19 GMT+0700 (Indochina Time), 1);
INSERT INTO degrees VALUES (10, 10, 'Bachelor of Marketing', Fri Jun 15 2018 00:00:00 GMT+0700 (Indochina Time), NULL, 'Permanent', NULL, Sat Apr 19 2025 23:00:19 GMT+0700 (Indochina Time), Sat Apr 19 2025 23:00:19 GMT+0700 (Indochina Time), 1);


-- Table structure for departments
CREATE TABLE `departments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `manager_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `parent_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `manager_id` (`manager_id`),
  CONSTRAINT `fk_departments_manager` FOREIGN KEY (`manager_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for departments
INSERT INTO departments VALUES (1, 'Human Resources', 'HR Department', NULL, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), NULL);
INSERT INTO departments VALUES (2, 'Information Technology', 'IT Department', NULL, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), NULL);
INSERT INTO departments VALUES (3, 'Finance', 'Finance Department', NULL, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), NULL);
INSERT INTO departments VALUES (4, 'Marketing', 'Marketing Department', NULL, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), NULL);
INSERT INTO departments VALUES (5, 'Operations', 'Operations Department', NULL, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), NULL);


-- Table structure for documents
CREATE TABLE `documents` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `file_url` varchar(512) NOT NULL,
  `document_type` varchar(50) NOT NULL,
  `uploaded_by` int(11) NOT NULL,
  `department_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `uploaded_by` (`uploaded_by`),
  KEY `department_id` (`department_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for documents
INSERT INTO documents VALUES (1, 'Employee Handbook', 'Company policies and procedures', '/documents/handbook.pdf', 'policy', 1, 1, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO documents VALUES (2, 'IT Security Guidelines', 'IT security best practices', '/documents/security.pdf', 'guideline', 2, 2, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO documents VALUES (3, 'Financial Procedures', 'Financial management procedures', '/documents/finance.pdf', 'procedure', 3, 3, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO documents VALUES (4, 'Marketing Strategy', 'Company marketing strategy', '/documents/marketing.pdf', 'strategy', 4, 4, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO documents VALUES (5, 'Operations Manual', 'Operations procedures manual', '/documents/operations.pdf', 'manual', 5, 5, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));


-- Table structure for email_verification_tokens
CREATE TABLE `email_verification_tokens` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



-- Table structure for employee_positions
CREATE TABLE `employee_positions` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `position_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `is_current` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`),
  KEY `position_id` (`position_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for employee_positions
INSERT INTO employee_positions VALUES (1, 1, 1, Sun Jan 01 2023 00:00:00 GMT+0700 (Indochina Time), NULL, 1, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO employee_positions VALUES (2, 2, 1, Sun Jan 01 2023 00:00:00 GMT+0700 (Indochina Time), NULL, 1, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO employee_positions VALUES (3, 3, 3, Sun Jan 01 2023 00:00:00 GMT+0700 (Indochina Time), NULL, 1, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO employee_positions VALUES (4, 4, 5, Sun Jan 01 2023 00:00:00 GMT+0700 (Indochina Time), NULL, 1, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO employee_positions VALUES (5, 5, 7, Sun Jan 01 2023 00:00:00 GMT+0700 (Indochina Time), NULL, 1, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO employee_positions VALUES (6, 6, 9, Sun Jan 01 2023 00:00:00 GMT+0700 (Indochina Time), NULL, 1, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO employee_positions VALUES (7, 7, 2, Sun Jan 01 2023 00:00:00 GMT+0700 (Indochina Time), NULL, 1, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO employee_positions VALUES (8, 8, 4, Sun Jan 01 2023 00:00:00 GMT+0700 (Indochina Time), NULL, 1, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO employee_positions VALUES (9, 9, 6, Sun Jan 01 2023 00:00:00 GMT+0700 (Indochina Time), NULL, 1, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO employee_positions VALUES (10, 10, 8, Sun Jan 01 2023 00:00:00 GMT+0700 (Indochina Time), NULL, 1, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));


-- Table structure for employee_trainings
CREATE TABLE `employee_trainings` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `training_id` int(11) NOT NULL,
  `status` varchar(20) DEFAULT 'registered',
  `result` varchar(20) DEFAULT NULL,
  `feedback` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`),
  KEY `training_id` (`training_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for employee_trainings
INSERT INTO employee_trainings VALUES (1, 1, 1, 'registered', NULL, NULL, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO employee_trainings VALUES (2, 2, 1, 'registered', NULL, NULL, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO employee_trainings VALUES (3, 3, 2, 'registered', NULL, NULL, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO employee_trainings VALUES (4, 4, 3, 'registered', NULL, NULL, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO employee_trainings VALUES (5, 5, 4, 'registered', NULL, NULL, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO employee_trainings VALUES (6, 6, 5, 'registered', NULL, NULL, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO employee_trainings VALUES (7, 7, 1, 'registered', NULL, NULL, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO employee_trainings VALUES (8, 8, 2, 'registered', NULL, NULL, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO employee_trainings VALUES (9, 9, 3, 'registered', NULL, NULL, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO employee_trainings VALUES (10, 10, 4, 'registered', NULL, NULL, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));


-- Table structure for employees
CREATE TABLE `employees` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `employee_code` varchar(20) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `position_id` int(11) DEFAULT NULL,
  `hire_date` date DEFAULT NULL,
  `contract_type` varchar(50) DEFAULT NULL,
  `contract_start_date` date DEFAULT NULL,
  `contract_end_date` date DEFAULT NULL,
  `status` varchar(20) DEFAULT 'active',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `employee_code` (`employee_code`),
  KEY `user_id` (`user_id`),
  KEY `department_id` (`department_id`),
  KEY `position_id` (`position_id`),
  CONSTRAINT `employees_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `employees_ibfk_2` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employees_ibfk_3` FOREIGN KEY (`position_id`) REFERENCES `positions` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data for employees
INSERT INTO employees VALUES (1, 1, 'EMP001', 1, 1, Sun Jan 01 2023 00:00:00 GMT+0700 (Indochina Time), 'full_time', Sun Jan 01 2023 00:00:00 GMT+0700 (Indochina Time), Wed Dec 31 2025 00:00:00 GMT+0700 (Indochina Time), 'active', Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO employees VALUES (2, 2, 'EMP002', 1, 1, Sun Jan 01 2023 00:00:00 GMT+0700 (Indochina Time), 'full_time', Sun Jan 01 2023 00:00:00 GMT+0700 (Indochina Time), Wed Dec 31 2025 00:00:00 GMT+0700 (Indochina Time), 'active', Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO employees VALUES (3, 3, 'EMP003', 2, 3, Sun Jan 01 2023 00:00:00 GMT+0700 (Indochina Time), 'full_time', Sun Jan 01 2023 00:00:00 GMT+0700 (Indochina Time), Wed Dec 31 2025 00:00:00 GMT+0700 (Indochina Time), 'active', Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO employees VALUES (4, 4, 'EMP004', 3, 5, Sun Jan 01 2023 00:00:00 GMT+0700 (Indochina Time), 'full_time', Sun Jan 01 2023 00:00:00 GMT+0700 (Indochina Time), Wed Dec 31 2025 00:00:00 GMT+0700 (Indochina Time), 'active', Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO employees VALUES (5, 5, 'EMP005', 4, 7, Sun Jan 01 2023 00:00:00 GMT+0700 (Indochina Time), 'full_time', Sun Jan 01 2023 00:00:00 GMT+0700 (Indochina Time), Wed Dec 31 2025 00:00:00 GMT+0700 (Indochina Time), 'active', Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO employees VALUES (6, 6, 'EMP006', 5, 9, Sun Jan 01 2023 00:00:00 GMT+0700 (Indochina Time), 'full_time', Sun Jan 01 2023 00:00:00 GMT+0700 (Indochina Time), Wed Dec 31 2025 00:00:00 GMT+0700 (Indochina Time), 'active', Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO employees VALUES (7, 7, 'EMP007', 1, 2, Sun Jan 01 2023 00:00:00 GMT+0700 (Indochina Time), 'full_time', Sun Jan 01 2023 00:00:00 GMT+0700 (Indochina Time), Wed Dec 31 2025 00:00:00 GMT+0700 (Indochina Time), 'active', Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO employees VALUES (8, 8, 'EMP008', 2, 4, Sun Jan 01 2023 00:00:00 GMT+0700 (Indochina Time), 'full_time', Sun Jan 01 2023 00:00:00 GMT+0700 (Indochina Time), Wed Dec 31 2025 00:00:00 GMT+0700 (Indochina Time), 'active', Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO employees VALUES (9, 9, 'EMP009', 3, 6, Sun Jan 01 2023 00:00:00 GMT+0700 (Indochina Time), 'full_time', Sun Jan 01 2023 00:00:00 GMT+0700 (Indochina Time), Wed Dec 31 2025 00:00:00 GMT+0700 (Indochina Time), 'active', Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO employees VALUES (10, 10, 'EMP010', 4, 8, Sun Jan 01 2023 00:00:00 GMT+0700 (Indochina Time), 'full_time', Sun Jan 01 2023 00:00:00 GMT+0700 (Indochina Time), Wed Dec 31 2025 00:00:00 GMT+0700 (Indochina Time), 'active', Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));


-- Table structure for equipment_assignments
CREATE TABLE `equipment_assignments` (
  `id` int(11) NOT NULL,
  `equipment_name` varchar(255) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `assigned_date` date NOT NULL,
  `return_date` date DEFAULT NULL,
  `status` varchar(20) DEFAULT 'assigned',
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`),
  CONSTRAINT `fk_equipment_assignments_employee` FOREIGN KEY (`employee_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data for equipment_assignments
INSERT INTO equipment_assignments VALUES (1, 'Laptop Dell XPS', 1, Sun Jan 01 2023 00:00:00 GMT+0700 (Indochina Time), NULL, 'assigned', 'Primary work laptop', Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO equipment_assignments VALUES (2, 'MacBook Pro', 2, Sun Jan 01 2023 00:00:00 GMT+0700 (Indochina Time), NULL, 'assigned', 'Development machine', Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO equipment_assignments VALUES (3, 'Desktop PC', 3, Sun Jan 01 2023 00:00:00 GMT+0700 (Indochina Time), NULL, 'assigned', 'Workstation', Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO equipment_assignments VALUES (4, 'Laptop HP EliteBook', 4, Sun Jan 01 2023 00:00:00 GMT+0700 (Indochina Time), NULL, 'assigned', 'Finance work laptop', Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO equipment_assignments VALUES (5, 'MacBook Air', 5, Sun Jan 01 2023 00:00:00 GMT+0700 (Indochina Time), NULL, 'assigned', 'Marketing work laptop', Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO equipment_assignments VALUES (6, 'Desktop PC', 6, Sun Jan 01 2023 00:00:00 GMT+0700 (Indochina Time), NULL, 'assigned', 'Operations workstation', Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO equipment_assignments VALUES (7, 'Laptop Lenovo ThinkPad', 7, Sun Jan 01 2023 00:00:00 GMT+0700 (Indochina Time), NULL, 'assigned', 'HR work laptop', Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO equipment_assignments VALUES (8, 'MacBook Pro', 8, Sun Jan 01 2023 00:00:00 GMT+0700 (Indochina Time), NULL, 'assigned', 'Development machine', Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO equipment_assignments VALUES (9, 'Laptop Dell Latitude', 9, Sun Jan 01 2023 00:00:00 GMT+0700 (Indochina Time), NULL, 'assigned', 'Finance work laptop', Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO equipment_assignments VALUES (10, 'MacBook Air', 10, Sun Jan 01 2023 00:00:00 GMT+0700 (Indochina Time), NULL, 'assigned', 'Marketing work laptop', Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));


-- Table structure for family_members
CREATE TABLE `family_members` (
  `family_member_id` int(11) NOT NULL,
  `profile_id` int(11) NOT NULL,
  `member_name` varchar(255) NOT NULL,
  `relationship` varchar(100) NOT NULL,
  `year_of_birth` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`family_member_id`),
  KEY `profile_id` (`profile_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for family_members
INSERT INTO family_members VALUES (1, 1, 'Jane Doe', 'Spouse', 1992, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO family_members VALUES (2, 2, 'John Smith', 'Spouse', 1990, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO family_members VALUES (3, 3, 'Mary Wilson', 'Spouse', 1989, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO family_members VALUES (4, 4, 'Sarah Brown', 'Spouse', 1991, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO family_members VALUES (5, 5, 'David Lee', 'Spouse', 1988, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO family_members VALUES (6, 6, 'Lisa Chen', 'Spouse', 1993, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO family_members VALUES (7, 7, 'Mike Johnson', 'Spouse', 1994, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO family_members VALUES (8, 8, 'Anna Davis', 'Spouse', 1992, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO family_members VALUES (9, 9, 'Tom Wilson', 'Spouse', 1991, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO family_members VALUES (10, 10, 'Peter Brown', 'Spouse', 1990, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));


-- Table structure for holidays
CREATE TABLE `holidays` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `description` text DEFAULT NULL,
  `is_recurring` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for holidays
INSERT INTO holidays VALUES (1, 'New Year', Mon Jan 01 2024 00:00:00 GMT+0700 (Indochina Time), 'New Year Day', 1, Sat Apr 19 2025 23:06:24 GMT+0700 (Indochina Time), Sat Apr 19 2025 23:06:24 GMT+0700 (Indochina Time));
INSERT INTO holidays VALUES (2, 'Tet Holiday', Sat Feb 10 2024 00:00:00 GMT+0700 (Indochina Time), 'Lunar New Year', 1, Sat Apr 19 2025 23:06:24 GMT+0700 (Indochina Time), Sat Apr 19 2025 23:06:24 GMT+0700 (Indochina Time));
INSERT INTO holidays VALUES (3, 'Reunification Day', Tue Apr 30 2024 00:00:00 GMT+0700 (Indochina Time), 'National Holiday', 1, Sat Apr 19 2025 23:06:24 GMT+0700 (Indochina Time), Sat Apr 19 2025 23:06:24 GMT+0700 (Indochina Time));
INSERT INTO holidays VALUES (4, 'Labor Day', Wed May 01 2024 00:00:00 GMT+0700 (Indochina Time), 'International Workers Day', 1, Sat Apr 19 2025 23:06:24 GMT+0700 (Indochina Time), Sat Apr 19 2025 23:06:24 GMT+0700 (Indochina Time));


-- Table structure for leaves
CREATE TABLE `leaves` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `leave_type` varchar(50) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `reason` text NOT NULL,
  `status` varchar(20) DEFAULT 'pending',
  `approved_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`),
  KEY `approved_by` (`approved_by`),
  KEY `idx_leave_status` (`status`,`employee_id`),
  CONSTRAINT `fk_leaves_approved_by` FOREIGN KEY (`approved_by`) REFERENCES `users` (`user_id`),
  CONSTRAINT `fk_leaves_employee` FOREIGN KEY (`employee_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data for leaves
INSERT INTO leaves VALUES (1, 1, 'annual', Mon Apr 01 2024 00:00:00 GMT+0700 (Indochina Time), Wed Apr 03 2024 00:00:00 GMT+0700 (Indochina Time), 'Annual vacation', 'approved', 1, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO leaves VALUES (2, 2, 'sick', Mon Mar 25 2024 00:00:00 GMT+0700 (Indochina Time), Tue Mar 26 2024 00:00:00 GMT+0700 (Indochina Time), 'Medical appointment', 'approved', 1, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO leaves VALUES (3, 3, 'annual', Mon Apr 15 2024 00:00:00 GMT+0700 (Indochina Time), Tue Apr 16 2024 00:00:00 GMT+0700 (Indochina Time), 'Personal matters', 'pending', NULL, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO leaves VALUES (4, 4, 'annual', Wed Apr 10 2024 00:00:00 GMT+0700 (Indochina Time), Fri Apr 12 2024 00:00:00 GMT+0700 (Indochina Time), 'Annual vacation', 'approved', 1, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO leaves VALUES (5, 5, 'sick', Mon Apr 15 2024 00:00:00 GMT+0700 (Indochina Time), Mon Apr 15 2024 00:00:00 GMT+0700 (Indochina Time), 'Medical appointment', 'pending', NULL, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO leaves VALUES (6, 6, 'annual', Sat Apr 20 2024 00:00:00 GMT+0700 (Indochina Time), Mon Apr 22 2024 00:00:00 GMT+0700 (Indochina Time), 'Personal matters', 'approved', 1, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO leaves VALUES (7, 7, 'annual', Fri Apr 05 2024 00:00:00 GMT+0700 (Indochina Time), Sun Apr 07 2024 00:00:00 GMT+0700 (Indochina Time), 'Annual vacation', 'approved', 1, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO leaves VALUES (8, 8, 'sick', Thu Mar 28 2024 00:00:00 GMT+0700 (Indochina Time), Thu Mar 28 2024 00:00:00 GMT+0700 (Indochina Time), 'Medical appointment', 'approved', 1, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO leaves VALUES (9, 9, 'annual', Mon Apr 08 2024 00:00:00 GMT+0700 (Indochina Time), Wed Apr 10 2024 00:00:00 GMT+0700 (Indochina Time), 'Annual vacation', 'approved', 1, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO leaves VALUES (10, 10, 'sick', Thu Apr 18 2024 00:00:00 GMT+0700 (Indochina Time), Thu Apr 18 2024 00:00:00 GMT+0700 (Indochina Time), 'Medical appointment', 'pending', NULL, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));


-- Table structure for login_attempts
CREATE TABLE `login_attempts` (
  `id` int(11) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `attempt_time` datetime NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `ip_address` (`ip_address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



-- Table structure for notifications
CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` varchar(50) NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data for notifications
INSERT INTO notifications VALUES (1, 1, 'New Task Assigned', 'You have been assigned a new task: Complete HR Report', 'task', 0, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO notifications VALUES (2, 2, 'System Update', 'IT system maintenance scheduled for next week', 'system', 0, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO notifications VALUES (3, 3, 'Meeting Reminder', 'Team meeting tomorrow at 10:00 AM', 'meeting', 0, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO notifications VALUES (4, 4, 'Document Review', 'Please review the new financial procedures', 'document', 0, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO notifications VALUES (5, 5, 'Training Registration', 'New training session available for registration', 'training', 0, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO notifications VALUES (6, 6, 'System Update', 'Operations system maintenance scheduled', 'system', 0, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO notifications VALUES (7, 7, 'Task Assignment', 'New HR task assigned: Employee onboarding', 'task', 0, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO notifications VALUES (8, 8, 'Training Reminder', 'Software development training tomorrow', 'training', 0, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO notifications VALUES (9, 9, 'Document Review', 'Please review Q1 financial reports', 'document', 0, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO notifications VALUES (10, 10, 'Meeting Reminder', 'Marketing team meeting at 2 PM', 'meeting', 0, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));


-- Table structure for password_reset_tokens
CREATE TABLE `password_reset_tokens` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



-- Table structure for payroll
CREATE TABLE `payroll` (
  `payroll_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `payroll_month` int(11) NOT NULL CHECK (`payroll_month` between 1 and 12),
  `payroll_year` int(11) NOT NULL,
  `work_days_actual` decimal(4,1) NOT NULL,
  `base_salary_at_time` decimal(15,2) NOT NULL,
  `bonuses_total` decimal(15,2) DEFAULT 0.00,
  `social_insurance_deduction` decimal(15,2) NOT NULL,
  `other_deductions` decimal(15,2) DEFAULT 0.00,
  `total_salary` decimal(15,2) NOT NULL,
  `generated_at` datetime DEFAULT current_timestamp(),
  `generated_by_user_id` int(11) NOT NULL,
  `notes` text DEFAULT NULL,
  PRIMARY KEY (`payroll_id`),
  UNIQUE KEY `uq_payroll_user_month_year` (`user_id`,`payroll_month`,`payroll_year`),
  KEY `generated_by_user_id` (`generated_by_user_id`),
  KEY `idx_payroll_month_year` (`payroll_month`,`payroll_year`),
  CONSTRAINT `fk_payroll_generated_by` FOREIGN KEY (`generated_by_user_id`) REFERENCES `users` (`user_id`),
  CONSTRAINT `fk_payroll_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data for payroll
INSERT INTO payroll VALUES (1, 1, 3, 2024, '22.0', '30000000.00', '1000000.00', '1500000.00', '0.00', '29500000.00', Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), 1, NULL);
INSERT INTO payroll VALUES (2, 2, 3, 2024, '22.0', '25000000.00', '800000.00', '1250000.00', '0.00', '24550000.00', Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), 1, NULL);
INSERT INTO payroll VALUES (3, 3, 3, 2024, '22.0', '25000000.00', '800000.00', '1250000.00', '0.00', '24550000.00', Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), 1, NULL);
INSERT INTO payroll VALUES (4, 4, 3, 2024, '22.0', '25000000.00', '800000.00', '1250000.00', '0.00', '24550000.00', Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), 1, NULL);
INSERT INTO payroll VALUES (5, 5, 3, 2024, '22.0', '25000000.00', '800000.00', '1250000.00', '0.00', '24550000.00', Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), 1, NULL);
INSERT INTO payroll VALUES (6, 6, 3, 2024, '22.0', '25000000.00', '800000.00', '1250000.00', '0.00', '24550000.00', Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), 1, NULL);
INSERT INTO payroll VALUES (7, 7, 3, 2024, '22.0', '15000000.00', '500000.00', '750000.00', '0.00', '14750000.00', Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), 1, NULL);
INSERT INTO payroll VALUES (8, 8, 3, 2024, '22.0', '18000000.00', '600000.00', '900000.00', '0.00', '17700000.00', Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), 1, NULL);
INSERT INTO payroll VALUES (9, 9, 3, 2024, '22.0', '15000000.00', '500000.00', '750000.00', '0.00', '14750000.00', Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), 1, NULL);
INSERT INTO payroll VALUES (10, 10, 3, 2024, '22.0', '15000000.00', '500000.00', '750000.00', '0.00', '14750000.00', Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), 1, NULL);
INSERT INTO payroll VALUES (11, 1, 4, 2024, '22.0', '15000000.00', '2000000.00', '1500000.00', '500000.00', '15000000.00', Tue Apr 22 2025 03:20:24 GMT+0700 (Indochina Time), 1, 'LÆ°Æ¡ng thÃ¡ng 4');
INSERT INTO payroll VALUES (12, 2, 4, 2024, '22.0', '12000000.00', '1500000.00', '1200000.00', '400000.00', '12000000.00', Tue Apr 22 2025 03:20:24 GMT+0700 (Indochina Time), 1, 'LÆ°Æ¡ng thÃ¡ng 4');
INSERT INTO payroll VALUES (13, 3, 4, 2024, '22.0', '10000000.00', '1000000.00', '1000000.00', '300000.00', '10000000.00', Tue Apr 22 2025 03:20:24 GMT+0700 (Indochina Time), 1, 'LÆ°Æ¡ng thÃ¡ng 4');
INSERT INTO payroll VALUES (14, 4, 4, 2024, '22.0', '8000000.00', '800000.00', '800000.00', '200000.00', '8000000.00', Tue Apr 22 2025 03:20:24 GMT+0700 (Indochina Time), 1, 'LÆ°Æ¡ng thÃ¡ng 4');
INSERT INTO payroll VALUES (15, 5, 4, 2024, '22.0', '7000000.00', '700000.00', '700000.00', '100000.00', '7000000.00', Tue Apr 22 2025 03:20:24 GMT+0700 (Indochina Time), 1, 'LÆ°Æ¡ng thÃ¡ng 4');
INSERT INTO payroll VALUES (16, 6, 4, 2024, '22.0', '9000000.00', '900000.00', '900000.00', '300000.00', '9000000.00', Tue Apr 22 2025 03:20:24 GMT+0700 (Indochina Time), 1, 'LÆ°Æ¡ng thÃ¡ng 4');
INSERT INTO payroll VALUES (17, 7, 4, 2024, '22.0', '11000000.00', '1100000.00', '1100000.00', '400000.00', '11000000.00', Tue Apr 22 2025 03:20:24 GMT+0700 (Indochina Time), 1, 'LÆ°Æ¡ng thÃ¡ng 4');
INSERT INTO payroll VALUES (18, 8, 4, 2024, '22.0', '13000000.00', '1300000.00', '1300000.00', '500000.00', '13000000.00', Tue Apr 22 2025 03:20:24 GMT+0700 (Indochina Time), 1, 'LÆ°Æ¡ng thÃ¡ng 4');
INSERT INTO payroll VALUES (19, 9, 4, 2024, '22.0', '14000000.00', '1400000.00', '1400000.00', '600000.00', '14000000.00', Tue Apr 22 2025 03:20:24 GMT+0700 (Indochina Time), 1, 'LÆ°Æ¡ng thÃ¡ng 4');
INSERT INTO payroll VALUES (20, 10, 4, 2024, '22.0', '16000000.00', '1600000.00', '1600000.00', '700000.00', '16000000.00', Tue Apr 22 2025 03:20:24 GMT+0700 (Indochina Time), 1, 'LÆ°Æ¡ng thÃ¡ng 4');


-- Table structure for payrolls
CREATE TABLE `payrolls` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `month` int(11) NOT NULL,
  `year` int(11) NOT NULL,
  `basic_salary` decimal(10,2) NOT NULL,
  `allowances` decimal(10,2) DEFAULT 0.00,
  `deductions` decimal(10,2) DEFAULT 0.00,
  `net_salary` decimal(10,2) NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`),
  CONSTRAINT `payrolls_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data for payrolls
INSERT INTO payrolls VALUES (1, 1, 3, 2024, '30000000.00', '2000000.00', '1500000.00', '30500000.00', 'approved', Mon Apr 21 2025 21:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 21:57:56 GMT+0700 (Indochina Time));
INSERT INTO payrolls VALUES (2, 2, 3, 2024, '25000000.00', '1500000.00', '1250000.00', '25250000.00', 'approved', Mon Apr 21 2025 21:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 21:57:56 GMT+0700 (Indochina Time));
INSERT INTO payrolls VALUES (3, 3, 3, 2024, '25000000.00', '1500000.00', '1250000.00', '25250000.00', 'approved', Mon Apr 21 2025 21:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 21:57:56 GMT+0700 (Indochina Time));
INSERT INTO payrolls VALUES (4, 4, 3, 2024, '25000000.00', '1500000.00', '1250000.00', '25250000.00', 'approved', Mon Apr 21 2025 21:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 21:57:56 GMT+0700 (Indochina Time));
INSERT INTO payrolls VALUES (5, 5, 3, 2024, '25000000.00', '1500000.00', '1250000.00', '25250000.00', 'approved', Mon Apr 21 2025 21:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 21:57:56 GMT+0700 (Indochina Time));
INSERT INTO payrolls VALUES (6, 6, 3, 2024, '25000000.00', '1500000.00', '1250000.00', '25250000.00', 'approved', Mon Apr 21 2025 21:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 21:57:56 GMT+0700 (Indochina Time));
INSERT INTO payrolls VALUES (7, 7, 3, 2024, '15000000.00', '1000000.00', '750000.00', '15250000.00', 'approved', Mon Apr 21 2025 21:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 21:57:56 GMT+0700 (Indochina Time));
INSERT INTO payrolls VALUES (8, 8, 3, 2024, '18000000.00', '1200000.00', '900000.00', '18300000.00', 'approved', Mon Apr 21 2025 21:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 21:57:56 GMT+0700 (Indochina Time));
INSERT INTO payrolls VALUES (9, 9, 3, 2024, '15000000.00', '1000000.00', '750000.00', '15250000.00', 'approved', Mon Apr 21 2025 21:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 21:57:56 GMT+0700 (Indochina Time));
INSERT INTO payrolls VALUES (10, 10, 3, 2024, '15000000.00', '1000000.00', '750000.00', '15250000.00', 'approved', Mon Apr 21 2025 21:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 21:57:56 GMT+0700 (Indochina Time));


-- Table structure for performances
CREATE TABLE `performances` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `reviewer_id` int(11) NOT NULL,
  `review_date` date NOT NULL,
  `performance_score` decimal(4,2) NOT NULL,
  `strengths` text DEFAULT NULL,
  `weaknesses` text DEFAULT NULL,
  `goals` text DEFAULT NULL,
  `comments` text DEFAULT NULL,
  `status` varchar(20) DEFAULT 'draft',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`),
  KEY `reviewer_id` (`reviewer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



-- Table structure for positions
CREATE TABLE `positions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `department_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `salary_grade` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `department_id` (`department_id`),
  CONSTRAINT `fk_positions_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for positions
INSERT INTO positions VALUES (1, 'HR Manager', 'Human Resources Manager', 1, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), 'M1');
INSERT INTO positions VALUES (2, 'HR Specialist', 'Human Resources Specialist', 1, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), 'S1');
INSERT INTO positions VALUES (3, 'IT Manager', 'Information Technology Manager', 2, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), 'M1');
INSERT INTO positions VALUES (4, 'Software Developer', 'Software Developer', 2, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), 'S1');
INSERT INTO positions VALUES (5, 'Finance Manager', 'Finance Manager', 3, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), 'M1');
INSERT INTO positions VALUES (6, 'Accountant', 'Accountant', 3, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), 'S1');
INSERT INTO positions VALUES (7, 'Marketing Manager', 'Marketing Manager', 4, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), 'M1');
INSERT INTO positions VALUES (8, 'Marketing Specialist', 'Marketing Specialist', 4, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), 'S1');
INSERT INTO positions VALUES (9, 'Operations Manager', 'Operations Manager', 5, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), 'M1');
INSERT INTO positions VALUES (10, 'Operations Coordinator', 'Operations Coordinator', 5, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), 'S1');


-- Table structure for rate_limits
CREATE TABLE `rate_limits` (
  `id` int(11) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `endpoint` varchar(255) NOT NULL,
  `request_count` int(11) NOT NULL DEFAULT 1,
  `window_start` datetime NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_ip_endpoint` (`ip_address`,`endpoint`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



-- Table structure for roles
CREATE TABLE `roles` (
  `role_id` int(11) NOT NULL,
  `role_name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`role_id`),
  UNIQUE KEY `role_name` (`role_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for roles
INSERT INTO roles VALUES (1, 'admin', 'System administrator with full access');
INSERT INTO roles VALUES (2, 'manager', 'Department manager with elevated privileges');
INSERT INTO roles VALUES (3, 'hr', 'Human resources staff with HR-related access');
INSERT INTO roles VALUES (4, 'employee', 'Regular employee with basic access');


-- Table structure for salary_history
CREATE TABLE `salary_history` (
  `salary_history_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `effective_date` date NOT NULL,
  `job_position` varchar(255) DEFAULT NULL,
  `department` varchar(255) DEFAULT NULL,
  `salary_coefficient` decimal(10,2) NOT NULL,
  `salary_level` varchar(50) NOT NULL,
  `decision_attachment_url` varchar(512) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `recorded_by_user_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`salary_history_id`),
  KEY `user_id` (`user_id`),
  KEY `recorded_by_user_id` (`recorded_by_user_id`),
  CONSTRAINT `fk_salary_history_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data for salary_history
INSERT INTO salary_history VALUES (1, 1, Sun Jan 01 2023 00:00:00 GMT+0700 (Indochina Time), 'HR Manager', 'Human Resources', '3.00', 'Senior', NULL, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), NULL);
INSERT INTO salary_history VALUES (2, 2, Sun Jan 01 2023 00:00:00 GMT+0700 (Indochina Time), 'HR Manager', 'Human Resources', '2.50', 'Mid', NULL, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), NULL);
INSERT INTO salary_history VALUES (3, 3, Sun Jan 01 2023 00:00:00 GMT+0700 (Indochina Time), 'IT Manager', 'Information Technology', '3.00', 'Senior', NULL, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), NULL);
INSERT INTO salary_history VALUES (4, 4, Sun Jan 01 2023 00:00:00 GMT+0700 (Indochina Time), 'Finance Manager', 'Finance', '3.00', 'Senior', NULL, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), NULL);
INSERT INTO salary_history VALUES (5, 5, Sun Jan 01 2023 00:00:00 GMT+0700 (Indochina Time), 'Marketing Manager', 'Marketing', '3.00', 'Senior', NULL, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), NULL);
INSERT INTO salary_history VALUES (6, 6, Sun Jan 01 2023 00:00:00 GMT+0700 (Indochina Time), 'Operations Manager', 'Operations', '3.00', 'Senior', NULL, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), NULL);
INSERT INTO salary_history VALUES (7, 7, Sun Jan 01 2023 00:00:00 GMT+0700 (Indochina Time), 'HR Specialist', 'Human Resources', '2.00', 'Junior', NULL, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), NULL);
INSERT INTO salary_history VALUES (8, 8, Sun Jan 01 2023 00:00:00 GMT+0700 (Indochina Time), 'Software Developer', 'Information Technology', '2.00', 'Junior', NULL, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), NULL);
INSERT INTO salary_history VALUES (9, 9, Sun Jan 01 2023 00:00:00 GMT+0700 (Indochina Time), 'Accountant', 'Finance', '2.00', 'Junior', NULL, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), NULL);
INSERT INTO salary_history VALUES (10, 10, Sun Jan 01 2023 00:00:00 GMT+0700 (Indochina Time), 'Marketing Specialist', 'Marketing', '2.00', 'Junior', NULL, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), NULL);


-- Table structure for tasks
CREATE TABLE `tasks` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `assigned_to` int(11) NOT NULL,
  `assigned_by` int(11) NOT NULL,
  `due_date` date DEFAULT NULL,
  `priority` varchar(20) DEFAULT 'medium',
  `status` varchar(20) DEFAULT 'pending',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `assigned_to` (`assigned_to`),
  KEY `assigned_by` (`assigned_by`),
  CONSTRAINT `fk_tasks_assigned_by` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`user_id`),
  CONSTRAINT `fk_tasks_assigned_to` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data for tasks
INSERT INTO tasks VALUES (1, 'Complete HR Report', 'Monthly HR performance report', 1, 1, Mon Apr 15 2024 00:00:00 GMT+0700 (Indochina Time), 'high', 'pending', Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO tasks VALUES (2, 'Update IT System', 'System maintenance and updates', 2, 1, Sat Apr 20 2024 00:00:00 GMT+0700 (Indochina Time), 'medium', 'pending', Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO tasks VALUES (3, 'Review Financial Statements', 'Q1 financial review', 3, 1, Thu Apr 25 2024 00:00:00 GMT+0700 (Indochina Time), 'high', 'pending', Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO tasks VALUES (4, 'Marketing Campaign', 'New product launch campaign', 4, 1, Tue Apr 30 2024 00:00:00 GMT+0700 (Indochina Time), 'high', 'pending', Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO tasks VALUES (5, 'Operations Review', 'Monthly operations review', 5, 1, Sun Apr 28 2024 00:00:00 GMT+0700 (Indochina Time), 'medium', 'pending', Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO tasks VALUES (6, 'Employee Training', 'New employee orientation', 6, 1, Mon Apr 22 2024 00:00:00 GMT+0700 (Indochina Time), 'high', 'pending', Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO tasks VALUES (7, 'System Development', 'New feature development', 7, 1, Thu Apr 18 2024 00:00:00 GMT+0700 (Indochina Time), 'high', 'pending', Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO tasks VALUES (8, 'Budget Planning', 'Q2 budget planning', 8, 1, Wed Apr 17 2024 00:00:00 GMT+0700 (Indochina Time), 'high', 'pending', Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO tasks VALUES (9, 'Social Media Campaign', 'Social media marketing campaign', 9, 1, Fri Apr 19 2024 00:00:00 GMT+0700 (Indochina Time), 'medium', 'pending', Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO tasks VALUES (10, 'Process Improvement', 'Operations process improvement', 10, 1, Sun Apr 21 2024 00:00:00 GMT+0700 (Indochina Time), 'high', 'pending', Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));


-- Table structure for trainings
CREATE TABLE `trainings` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `trainer` varchar(255) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'planned',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for trainings
INSERT INTO trainings VALUES (1, 'New HR Policies', 'Training on updated HR policies and procedures', Wed Apr 10 2024 00:00:00 GMT+0700 (Indochina Time), Thu Apr 11 2024 00:00:00 GMT+0700 (Indochina Time), 'Training Room A', 'External Trainer', 'planned', Tue Apr 15 2025 13:53:46 GMT+0700 (Indochina Time), Tue Apr 15 2025 13:53:46 GMT+0700 (Indochina Time));
INSERT INTO trainings VALUES (2, 'IT Security', 'Basic IT security training', Mon Apr 15 2024 00:00:00 GMT+0700 (Indochina Time), Tue Apr 16 2024 00:00:00 GMT+0700 (Indochina Time), 'Online', 'Internal IT Team', 'planned', Tue Apr 15 2025 13:53:46 GMT+0700 (Indochina Time), Tue Apr 15 2025 13:53:46 GMT+0700 (Indochina Time));


-- Table structure for user_profiles
CREATE TABLE `user_profiles` (
  `profile_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `avatar_url` varchar(512) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `permanent_address` text DEFAULT NULL,
  `current_workplace` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `gender` varchar(10) DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `emergency_contact` varchar(255) DEFAULT NULL,
  `bank_account` varchar(50) DEFAULT NULL,
  `tax_code` varchar(20) DEFAULT NULL,
  `nationality` varchar(100) DEFAULT NULL,
  `ethnicity` varchar(100) DEFAULT NULL,
  `religion` varchar(100) DEFAULT NULL,
  `marital_status` varchar(20) DEFAULT NULL,
  `id_card_number` varchar(20) DEFAULT NULL,
  `id_card_issue_date` date DEFAULT NULL,
  `id_card_issue_place` varchar(255) DEFAULT NULL,
  KEY `fk_user_profiles_user` (`user_id`),
  CONSTRAINT `fk_user_profiles_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data for user_profiles
INSERT INTO user_profiles VALUES (1, 1, 'Admin User', NULL, Mon Jan 01 1990 00:00:00 GMT+0700 (Indochina Time), '123 Main St, Hanoi', NULL, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), 'Male', '0123456789', 'Jane Doe', '1234567890', '123456789', 'Vietnamese', 'Kinh', 'None', 'Single', '123456789', Fri Jan 01 2010 00:00:00 GMT+0700 (Indochina Time), 'Hanoi');
INSERT INTO user_profiles VALUES (2, 2, 'HR Manager', NULL, Wed May 15 1985 00:00:00 GMT+0700 (Indochina Time), '456 Park Ave, Hanoi', NULL, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), 'Female', '0123456788', 'John Smith', '0987654321', '987654321', 'Vietnamese', 'Kinh', 'None', 'Married', '987654321', Tue May 15 2012 00:00:00 GMT+0700 (Indochina Time), 'Hanoi');
INSERT INTO user_profiles VALUES (3, 3, 'IT Manager', NULL, Sat Aug 20 1988 00:00:00 GMT+0700 (Indochina Time), '789 Oak St, Hanoi', NULL, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), 'Male', '0123456787', 'Mary Wilson', '5678901234', '567890123', 'Vietnamese', 'Kinh', 'None', 'Married', '567890123', Wed Aug 20 2008 00:00:00 GMT+0700 (Indochina Time), 'Hanoi');
INSERT INTO user_profiles VALUES (4, 4, 'Finance Manager', NULL, Tue Mar 10 1987 00:00:00 GMT+0700 (Indochina Time), '321 Pine St, Hanoi', NULL, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), 'Male', '0123456786', 'Sarah Brown', '4321098765', '432109876', 'Vietnamese', 'Kinh', 'None', 'Single', '432109876', Sat Mar 10 2007 00:00:00 GMT+0700 (Indochina Time), 'Hanoi');
INSERT INTO user_profiles VALUES (5, 5, 'Marketing Manager', NULL, Tue Nov 25 1986 00:00:00 GMT+0700 (Indochina Time), '654 Elm St, Hanoi', NULL, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), 'Female', '0123456785', 'David Lee', '8765432109', '876543210', 'Vietnamese', 'Kinh', 'None', 'Married', '876543210', Sat Nov 25 2006 00:00:00 GMT+0700 (Indochina Time), 'Hanoi');
INSERT INTO user_profiles VALUES (6, 6, 'Operations Manager', NULL, Sun Jul 30 1989 00:00:00 GMT+0700 (Indochina Time), '987 Maple St, Hanoi', NULL, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), 'Male', '0123456784', 'Lisa Chen', '2345678901', '234567890', 'Vietnamese', 'Kinh', 'None', 'Single', '234567890', Thu Jul 30 2009 00:00:00 GMT+0700 (Indochina Time), 'Hanoi');
INSERT INTO user_profiles VALUES (7, 7, 'HR Specialist', NULL, Fri Feb 14 1992 00:00:00 GMT+0700 (Indochina Time), '147 Cedar St, Hanoi', NULL, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), 'Female', '0123456783', 'Mike Johnson', '3456789012', '345678901', 'Vietnamese', 'Kinh', 'None', 'Single', '345678901', Tue Feb 14 2012 00:00:00 GMT+0700 (Indochina Time), 'Hanoi');
INSERT INTO user_profiles VALUES (8, 8, 'Software Developer', NULL, Thu Sep 05 1991 00:00:00 GMT+0700 (Indochina Time), '258 Birch St, Hanoi', NULL, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), 'Male', '0123456782', 'Anna Davis', '4567890123', '456789012', 'Vietnamese', 'Kinh', 'None', 'Married', '456789012', Mon Sep 05 2011 00:00:00 GMT+0700 (Indochina Time), 'Hanoi');
INSERT INTO user_profiles VALUES (9, 9, 'Accountant', NULL, Tue Apr 20 1993 00:00:00 GMT+0700 (Indochina Time), '369 Spruce St, Hanoi', NULL, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), 'Female', '0123456781', 'Tom Wilson', '5678901234', '567890123', 'Vietnamese', 'Kinh', 'None', 'Single', '567890123', Sat Apr 20 2013 00:00:00 GMT+0700 (Indochina Time), 'Hanoi');
INSERT INTO user_profiles VALUES (10, 10, 'Marketing Specialist', NULL, Sat Dec 15 1990 00:00:00 GMT+0700 (Indochina Time), '741 Walnut St, Hanoi', NULL, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), 'Female', '0123456780', 'Peter Brown', '6789012345', '678901234', 'Vietnamese', 'Kinh', 'None', 'Married', '678901234', Wed Dec 15 2010 00:00:00 GMT+0700 (Indochina Time), 'Hanoi');
INSERT INTO user_profiles VALUES (1, 1, 'Admin User', NULL, Mon Jan 01 1990 00:00:00 GMT+0700 (Indochina Time), '123 Main St, Hanoi', NULL, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), 'Male', '0123456789', 'Jane Doe', '1234567890', '123456789', 'Vietnamese', 'Kinh', 'None', 'Single', '123456789', Fri Jan 01 2010 00:00:00 GMT+0700 (Indochina Time), 'Hanoi');
INSERT INTO user_profiles VALUES (2, 2, 'HR Manager', NULL, Wed May 15 1985 00:00:00 GMT+0700 (Indochina Time), '456 Park Ave, Hanoi', NULL, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), 'Female', '0123456788', 'John Smith', '0987654321', '987654321', 'Vietnamese', 'Kinh', 'None', 'Married', '987654321', Tue May 15 2012 00:00:00 GMT+0700 (Indochina Time), 'Hanoi');
INSERT INTO user_profiles VALUES (3, 3, 'IT Manager', NULL, Sat Aug 20 1988 00:00:00 GMT+0700 (Indochina Time), '789 Oak St, Hanoi', NULL, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), 'Male', '0123456787', 'Mary Wilson', '5678901234', '567890123', 'Vietnamese', 'Kinh', 'None', 'Married', '567890123', Wed Aug 20 2008 00:00:00 GMT+0700 (Indochina Time), 'Hanoi');
INSERT INTO user_profiles VALUES (4, 4, 'Finance Manager', NULL, Tue Mar 10 1987 00:00:00 GMT+0700 (Indochina Time), '321 Pine St, Hanoi', NULL, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), 'Male', '0123456786', 'Sarah Brown', '4321098765', '432109876', 'Vietnamese', 'Kinh', 'None', 'Single', '432109876', Sat Mar 10 2007 00:00:00 GMT+0700 (Indochina Time), 'Hanoi');
INSERT INTO user_profiles VALUES (5, 5, 'Marketing Manager', NULL, Tue Nov 25 1986 00:00:00 GMT+0700 (Indochina Time), '654 Elm St, Hanoi', NULL, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), 'Female', '0123456785', 'David Lee', '8765432109', '876543210', 'Vietnamese', 'Kinh', 'None', 'Married', '876543210', Sat Nov 25 2006 00:00:00 GMT+0700 (Indochina Time), 'Hanoi');
INSERT INTO user_profiles VALUES (6, 6, 'Operations Manager', NULL, Sun Jul 30 1989 00:00:00 GMT+0700 (Indochina Time), '987 Maple St, Hanoi', NULL, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), 'Male', '0123456784', 'Lisa Chen', '2345678901', '234567890', 'Vietnamese', 'Kinh', 'None', 'Single', '234567890', Thu Jul 30 2009 00:00:00 GMT+0700 (Indochina Time), 'Hanoi');
INSERT INTO user_profiles VALUES (7, 7, 'HR Specialist', NULL, Fri Feb 14 1992 00:00:00 GMT+0700 (Indochina Time), '147 Cedar St, Hanoi', NULL, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), 'Female', '0123456783', 'Mike Johnson', '3456789012', '345678901', 'Vietnamese', 'Kinh', 'None', 'Single', '345678901', Tue Feb 14 2012 00:00:00 GMT+0700 (Indochina Time), 'Hanoi');
INSERT INTO user_profiles VALUES (8, 8, 'Software Developer', NULL, Thu Sep 05 1991 00:00:00 GMT+0700 (Indochina Time), '258 Birch St, Hanoi', NULL, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), 'Male', '0123456782', 'Anna Davis', '4567890123', '456789012', 'Vietnamese', 'Kinh', 'None', 'Married', '456789012', Mon Sep 05 2011 00:00:00 GMT+0700 (Indochina Time), 'Hanoi');
INSERT INTO user_profiles VALUES (9, 9, 'Accountant', NULL, Tue Apr 20 1993 00:00:00 GMT+0700 (Indochina Time), '369 Spruce St, Hanoi', NULL, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), 'Female', '0123456781', 'Tom Wilson', '5678901234', '567890123', 'Vietnamese', 'Kinh', 'None', 'Single', '567890123', Sat Apr 20 2013 00:00:00 GMT+0700 (Indochina Time), 'Hanoi');
INSERT INTO user_profiles VALUES (10, 10, 'Marketing Specialist', NULL, Sat Dec 15 1990 00:00:00 GMT+0700 (Indochina Time), '741 Walnut St, Hanoi', NULL, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), 'Female', '0123456780', 'Peter Brown', '6789012345', '678901234', 'Vietnamese', 'Kinh', 'None', 'Married', '678901234', Wed Dec 15 2010 00:00:00 GMT+0700 (Indochina Time), 'Hanoi');


-- Table structure for users
CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `password_salt` varchar(64) DEFAULT NULL,
  `role_id` int(11) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `requires_password_change` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `department_id` int(11) DEFAULT NULL,
  `position_id` int(11) DEFAULT NULL,
  `hire_date` date DEFAULT NULL,
  `status` varchar(20) DEFAULT 'active',
  `employee_code` varchar(20) DEFAULT NULL,
  `contract_type` varchar(50) DEFAULT NULL,
  `contract_start_date` date DEFAULT NULL,
  `contract_end_date` date DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for users
INSERT INTO users VALUES (0, 'admin', 'admin@example.com', 'admin123', NULL, 1, 1, 0, Tue Apr 22 2025 02:53:52 GMT+0700 (Indochina Time), Tue Apr 22 2025 02:53:52 GMT+0700 (Indochina Time), NULL, NULL, NULL, 'active', NULL, NULL, NULL, NULL, NULL);
INSERT INTO users VALUES (1, 'admin', 'admin@company.com', 'e10adc3949ba59abbe56e057f20f883e', NULL, 1, 1, 0, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 21:04:15 GMT+0700 (Indochina Time), 1, 1, NULL, 'active', 'EMP001', 'full_time', Sun Jan 01 2023 00:00:00 GMT+0700 (Indochina Time), Wed Dec 31 2025 00:00:00 GMT+0700 (Indochina Time), Mon Apr 21 2025 21:04:15 GMT+0700 (Indochina Time));
INSERT INTO users VALUES (2, 'manager', 'manager@company.com', 'e10adc3949ba59abbe56e057f20f883e', NULL, 2, 1, 0, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), 1, 1, NULL, 'active', 'EMP002', 'full_time', Sun Jan 01 2023 00:00:00 GMT+0700 (Indochina Time), Wed Dec 31 2025 00:00:00 GMT+0700 (Indochina Time), NULL);
INSERT INTO users VALUES (3, 'employee1', 'employee1@company.com', 'e10adc3949ba59abbe56e057f20f883e', NULL, 4, 1, 0, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), 2, 3, NULL, 'active', 'EMP003', 'full_time', Sun Jan 01 2023 00:00:00 GMT+0700 (Indochina Time), Wed Dec 31 2025 00:00:00 GMT+0700 (Indochina Time), NULL);
INSERT INTO users VALUES (4, 'employee2', 'employee2@company.com', 'e10adc3949ba59abbe56e057f20f883e', NULL, 4, 1, 0, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), 3, 5, NULL, 'active', 'EMP004', 'full_time', Sun Jan 01 2023 00:00:00 GMT+0700 (Indochina Time), Wed Dec 31 2025 00:00:00 GMT+0700 (Indochina Time), NULL);
INSERT INTO users VALUES (5, 'employee3', 'employee3@company.com', 'e10adc3949ba59abbe56e057f20f883e', NULL, 4, 1, 0, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), 4, 7, NULL, 'active', 'EMP005', 'full_time', Sun Jan 01 2023 00:00:00 GMT+0700 (Indochina Time), Wed Dec 31 2025 00:00:00 GMT+0700 (Indochina Time), NULL);
INSERT INTO users VALUES (6, 'employee4', 'employee4@company.com', 'e10adc3949ba59abbe56e057f20f883e', NULL, 4, 1, 0, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), 5, 9, NULL, 'active', 'EMP006', 'full_time', Sun Jan 01 2023 00:00:00 GMT+0700 (Indochina Time), Wed Dec 31 2025 00:00:00 GMT+0700 (Indochina Time), NULL);
INSERT INTO users VALUES (7, 'employee5', 'employee5@company.com', 'e10adc3949ba59abbe56e057f20f883e', NULL, 4, 1, 0, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), 1, 2, NULL, 'active', 'EMP007', 'full_time', Sun Jan 01 2023 00:00:00 GMT+0700 (Indochina Time), Wed Dec 31 2025 00:00:00 GMT+0700 (Indochina Time), NULL);
INSERT INTO users VALUES (8, 'employee6', 'employee6@company.com', 'e10adc3949ba59abbe56e057f20f883e', NULL, 4, 1, 0, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), 2, 4, NULL, 'active', 'EMP008', 'full_time', Sun Jan 01 2023 00:00:00 GMT+0700 (Indochina Time), Wed Dec 31 2025 00:00:00 GMT+0700 (Indochina Time), NULL);
INSERT INTO users VALUES (9, 'employee7', 'employee7@company.com', 'e10adc3949ba59abbe56e057f20f883e', NULL, 4, 1, 0, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), 3, 6, NULL, 'active', 'EMP009', 'full_time', Sun Jan 01 2023 00:00:00 GMT+0700 (Indochina Time), Wed Dec 31 2025 00:00:00 GMT+0700 (Indochina Time), NULL);
INSERT INTO users VALUES (10, 'employee8', 'employee8@company.com', 'e10adc3949ba59abbe56e057f20f883e', NULL, 4, 1, 0, Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), 4, 8, NULL, 'active', 'EMP010', 'full_time', Sun Jan 01 2023 00:00:00 GMT+0700 (Indochina Time), Wed Dec 31 2025 00:00:00 GMT+0700 (Indochina Time), NULL);


-- Table structure for work_schedules
CREATE TABLE `work_schedules` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `work_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `schedule_type` varchar(20) DEFAULT 'normal',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  KEY `fk_work_schedules_employee` (`employee_id`),
  CONSTRAINT `fk_work_schedules_employee` FOREIGN KEY (`employee_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- Data for work_schedules
INSERT INTO work_schedules VALUES (1, 1, Mon Mar 18 2024 00:00:00 GMT+0700 (Indochina Time), '08:00:00', '17:00:00', 'normal', Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO work_schedules VALUES (2, 2, Mon Mar 18 2024 00:00:00 GMT+0700 (Indochina Time), '08:00:00', '17:00:00', 'normal', Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO work_schedules VALUES (3, 3, Mon Mar 18 2024 00:00:00 GMT+0700 (Indochina Time), '08:00:00', '17:00:00', 'normal', Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO work_schedules VALUES (4, 4, Mon Mar 18 2024 00:00:00 GMT+0700 (Indochina Time), '08:00:00', '17:00:00', 'normal', Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO work_schedules VALUES (5, 5, Mon Mar 18 2024 00:00:00 GMT+0700 (Indochina Time), '08:00:00', '17:00:00', 'normal', Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO work_schedules VALUES (6, 6, Mon Mar 18 2024 00:00:00 GMT+0700 (Indochina Time), '08:00:00', '17:00:00', 'normal', Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO work_schedules VALUES (7, 7, Mon Mar 18 2024 00:00:00 GMT+0700 (Indochina Time), '08:00:00', '17:00:00', 'normal', Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO work_schedules VALUES (8, 8, Mon Mar 18 2024 00:00:00 GMT+0700 (Indochina Time), '08:00:00', '17:00:00', 'normal', Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO work_schedules VALUES (9, 9, Mon Mar 18 2024 00:00:00 GMT+0700 (Indochina Time), '08:00:00', '17:00:00', 'normal', Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO work_schedules VALUES (10, 10, Mon Mar 18 2024 00:00:00 GMT+0700 (Indochina Time), '08:00:00', '17:00:00', 'normal', Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO work_schedules VALUES (1, 1, Mon Mar 18 2024 00:00:00 GMT+0700 (Indochina Time), '08:00:00', '17:00:00', 'normal', Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO work_schedules VALUES (2, 2, Mon Mar 18 2024 00:00:00 GMT+0700 (Indochina Time), '08:00:00', '17:00:00', 'normal', Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO work_schedules VALUES (3, 3, Mon Mar 18 2024 00:00:00 GMT+0700 (Indochina Time), '08:00:00', '17:00:00', 'normal', Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO work_schedules VALUES (4, 4, Mon Mar 18 2024 00:00:00 GMT+0700 (Indochina Time), '08:00:00', '17:00:00', 'normal', Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO work_schedules VALUES (5, 5, Mon Mar 18 2024 00:00:00 GMT+0700 (Indochina Time), '08:00:00', '17:00:00', 'normal', Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO work_schedules VALUES (6, 6, Mon Mar 18 2024 00:00:00 GMT+0700 (Indochina Time), '08:00:00', '17:00:00', 'normal', Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO work_schedules VALUES (7, 7, Mon Mar 18 2024 00:00:00 GMT+0700 (Indochina Time), '08:00:00', '17:00:00', 'normal', Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO work_schedules VALUES (8, 8, Mon Mar 18 2024 00:00:00 GMT+0700 (Indochina Time), '08:00:00', '17:00:00', 'normal', Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO work_schedules VALUES (9, 9, Mon Mar 18 2024 00:00:00 GMT+0700 (Indochina Time), '08:00:00', '17:00:00', 'normal', Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
INSERT INTO work_schedules VALUES (10, 10, Mon Mar 18 2024 00:00:00 GMT+0700 (Indochina Time), '08:00:00', '17:00:00', 'normal', Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time), Mon Apr 21 2025 14:57:56 GMT+0700 (Indochina Time));
