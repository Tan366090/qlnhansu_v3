-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 30, 2025 at 10:49 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `qlnhansu`
--

-- --------------------------------------------------------

--
-- Table structure for table `activities`
--

CREATE TABLE `activities` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL COMMENT 'User performing action, NULL if system',
  `type` varchar(100) NOT NULL COMMENT 'e.g., LOGIN, LOGOUT, UPDATE_PROFILE, CREATE_LEAVE',
  `description` text NOT NULL,
  `target_entity` varchar(100) DEFAULT NULL COMMENT 'e.g., Employee, LeaveRequest',
  `target_entity_id` int(11) DEFAULT NULL,
  `status` enum('success','warning','error','info') DEFAULT 'info',
  `user_agent` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activities`
--

INSERT INTO `activities` (`id`, `user_id`, `type`, `description`, `target_entity`, `target_entity_id`, `status`, `user_agent`, `ip_address`, `created_at`) VALUES
(1, 1, 'LOGIN', 'Đăng nhập thành công', 'User', 1, 'info', NULL, NULL, '2025-04-30 05:00:00'),
(2, 2, 'UPDATE_PROFILE', 'Cập nhật thông tin cá nhân', 'UserProfile', 2, 'info', NULL, NULL, '2025-04-30 05:00:00'),
(3, 3, 'CREATE_LEAVE', 'Tạo đơn nghỉ phép', 'Leave', 3, 'info', NULL, NULL, '2025-04-30 05:00:00'),
(4, 4, 'UPLOAD_DOCUMENT', 'Tải lên tài liệu mới', 'Document', 4, 'info', NULL, NULL, '2025-04-30 05:00:00'),
(5, 5, 'APPROVE_LEAVE', 'Duyệt đơn nghỉ phép', 'Leave', 5, 'info', NULL, NULL, '2025-04-30 05:00:00'),
(6, 6, 'ASSIGN_ASSET', 'Phân bổ tài sản', 'AssetAssignment', 6, 'info', NULL, NULL, '2025-04-30 05:00:00'),
(7, 7, 'GENERATE_REPORT', 'Xuất báo cáo lương', 'Payroll', 7, 'info', NULL, NULL, '2025-04-30 05:00:00'),
(8, 8, 'CREATE_PROJECT', 'Tạo dự án mới', 'Project', 8, 'info', NULL, NULL, '2025-04-30 05:00:00'),
(9, 9, 'COMPLETE_TASK', 'Hoàn thành công việc', 'Task', 9, 'info', NULL, NULL, '2025-04-30 05:00:00'),
(10, 10, 'LOGOUT', 'Đăng xuất hệ thống', 'User', 10, 'info', NULL, NULL, '2025-04-30 05:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `assets`
--

CREATE TABLE `assets` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `asset_code` varchar(50) NOT NULL,
  `category` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `serial_number` varchar(100) DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `purchase_cost` decimal(10,2) DEFAULT NULL,
  `current_value` decimal(10,2) DEFAULT NULL,
  `status` enum('available','assigned','maintenance','disposed','lost','damaged') NOT NULL DEFAULT 'available',
  `location` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assets`
--

INSERT INTO `assets` (`id`, `name`, `asset_code`, `category`, `description`, `serial_number`, `purchase_date`, `purchase_cost`, `current_value`, `status`, `location`, `created_at`, `updated_at`) VALUES
(1, 'Laptop Dell XPS 15', 'ASSET-001', 'Thiết bị IT', NULL, NULL, NULL, NULL, NULL, 'available', NULL, '2025-04-30 11:54:31', '2025-04-30 11:54:31'),
(2, 'Máy in HP LaserJet', 'ASSET-002', 'Văn phòng', NULL, NULL, NULL, NULL, NULL, 'assigned', NULL, '2025-04-30 11:54:31', '2025-04-30 11:54:31'),
(3, 'Máy chiếu Epson', 'ASSET-003', 'Thiết bị trình chiếu', NULL, NULL, NULL, NULL, NULL, 'available', NULL, '2025-04-30 11:54:31', '2025-04-30 11:54:31'),
(4, 'Điện thoại Samsung Galaxy', 'ASSET-004', 'Thiết bị di động', NULL, NULL, NULL, NULL, NULL, 'maintenance', NULL, '2025-04-30 11:54:31', '2025-04-30 11:54:31'),
(5, 'Máy tính bảng iPad Pro', 'ASSET-005', 'Thiết bị di động', NULL, NULL, NULL, NULL, NULL, 'available', NULL, '2025-04-30 11:54:31', '2025-04-30 11:54:31'),
(6, 'Máy quét Canon', 'ASSET-006', 'Văn phòng', NULL, NULL, NULL, NULL, NULL, 'available', NULL, '2025-04-30 11:54:31', '2025-04-30 11:54:31'),
(7, 'Máy ảnh Sony', 'ASSET-007', 'Thiết bị chụp ảnh', NULL, NULL, NULL, NULL, NULL, 'assigned', NULL, '2025-04-30 11:54:31', '2025-04-30 11:54:31'),
(8, 'Ổ cứng di động 1TB', 'ASSET-008', 'Lưu trữ', NULL, NULL, NULL, NULL, NULL, 'available', NULL, '2025-04-30 11:54:31', '2025-04-30 11:54:31'),
(9, 'Màn hình LG 24 inch', 'ASSET-009', 'Thiết bị IT', NULL, NULL, NULL, NULL, NULL, 'available', NULL, '2025-04-30 11:54:31', '2025-04-30 11:54:31'),
(10, 'Bàn phím cơ Logitech', 'ASSET-010', 'Phụ kiện', NULL, NULL, NULL, NULL, NULL, 'available', NULL, '2025-04-30 11:54:31', '2025-04-30 11:54:31');

-- --------------------------------------------------------

--
-- Table structure for table `asset_assignments`
--

CREATE TABLE `asset_assignments` (
  `id` int(11) NOT NULL,
  `asset_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `assigned_date` date NOT NULL,
  `expected_return_date` date DEFAULT NULL,
  `actual_return_date` date DEFAULT NULL,
  `condition_out` text DEFAULT NULL,
  `condition_in` text DEFAULT NULL,
  `status` enum('active','returned','lost','damaged') NOT NULL DEFAULT 'active',
  `notes` text DEFAULT NULL,
  `assigned_by_user_id` int(11) DEFAULT NULL,
  `returned_to_user_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `asset_assignments`
--

INSERT INTO `asset_assignments` (`id`, `asset_id`, `employee_id`, `assigned_date`, `expected_return_date`, `actual_return_date`, `condition_out`, `condition_in`, `status`, `notes`, `assigned_by_user_id`, `returned_to_user_id`, `created_at`, `updated_at`) VALUES
(1, 1, 1, '2023-01-01', NULL, NULL, NULL, NULL, 'active', NULL, NULL, NULL, '2025-04-30 11:54:41', '2025-04-30 11:54:41'),
(2, 2, 2, '2023-02-01', NULL, NULL, NULL, NULL, 'active', NULL, NULL, NULL, '2025-04-30 11:54:41', '2025-04-30 11:54:41'),
(3, 3, 3, '2023-03-01', NULL, NULL, NULL, NULL, 'returned', NULL, NULL, NULL, '2025-04-30 11:54:41', '2025-04-30 11:54:41'),
(4, 4, 4, '2023-04-01', NULL, NULL, NULL, NULL, 'active', NULL, NULL, NULL, '2025-04-30 11:54:41', '2025-04-30 11:54:41'),
(5, 5, 5, '2023-05-01', NULL, NULL, NULL, NULL, 'active', NULL, NULL, NULL, '2025-04-30 11:54:41', '2025-04-30 11:54:41'),
(6, 6, 6, '2023-06-01', NULL, NULL, NULL, NULL, 'returned', NULL, NULL, NULL, '2025-04-30 11:54:41', '2025-04-30 11:54:41'),
(7, 7, 7, '2023-07-01', NULL, NULL, NULL, NULL, 'active', NULL, NULL, NULL, '2025-04-30 11:54:41', '2025-04-30 11:54:41'),
(8, 8, 8, '2023-08-01', NULL, NULL, NULL, NULL, 'active', NULL, NULL, NULL, '2025-04-30 11:54:41', '2025-04-30 11:54:41'),
(9, 9, 9, '2023-09-01', NULL, NULL, NULL, NULL, 'returned', NULL, NULL, NULL, '2025-04-30 11:54:41', '2025-04-30 11:54:41'),
(10, 10, 10, '2023-10-01', NULL, NULL, NULL, NULL, 'active', NULL, NULL, NULL, '2025-04-30 11:54:41', '2025-04-30 11:54:41');

-- --------------------------------------------------------

--
-- Table structure for table `asset_maintenance`
--

CREATE TABLE `asset_maintenance` (
  `id` int(11) NOT NULL,
  `asset_id` int(11) NOT NULL,
  `maintenance_type` enum('preventive','corrective','inspection','upgrade') NOT NULL,
  `description` text DEFAULT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime DEFAULT NULL,
  `cost` decimal(10,2) DEFAULT NULL,
  `vendor` varchar(255) DEFAULT NULL,
  `status` enum('scheduled','in_progress','completed','cancelled','failed') NOT NULL DEFAULT 'scheduled',
  `created_by_user_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `attendance_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `attendance_date` date NOT NULL,
  `check_in_time` time DEFAULT NULL,
  `check_out_time` time DEFAULT NULL,
  `work_duration_hours` decimal(4,2) DEFAULT NULL,
  `attendance_symbol` varchar(10) DEFAULT NULL COMMENT 'e.g., P (Present), A (Absent), L (Leave), WFH',
  `notes` text DEFAULT NULL,
  `recorded_at` datetime DEFAULT current_timestamp(),
  `source` varchar(50) DEFAULT 'manual' COMMENT 'e.g., manual, biometric, system'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`attendance_id`, `employee_id`, `attendance_date`, `check_in_time`, `check_out_time`, `work_duration_hours`, `attendance_symbol`, `notes`, `recorded_at`, `source`) VALUES
(1, 1, '2023-10-01', '08:00:00', '17:00:00', NULL, 'P', NULL, '2025-04-30 11:54:01', 'manual'),
(2, 1, '2023-10-02', '08:15:00', '17:30:00', NULL, 'P', NULL, '2025-04-30 11:54:01', 'manual'),
(3, 1, '2023-10-03', NULL, NULL, NULL, 'A', NULL, '2025-04-30 11:54:01', 'manual'),
(4, 2, '2023-10-01', '08:05:00', '17:10:00', NULL, 'P', NULL, '2025-04-30 11:54:01', 'manual'),
(5, 2, '2023-10-02', '08:20:00', '17:25:00', NULL, 'WFH', NULL, '2025-04-30 11:54:01', 'manual'),
(6, 3, '2023-10-01', '08:10:00', '17:15:00', NULL, 'L', NULL, '2025-04-30 11:54:01', 'manual'),
(7, 4, '2023-10-01', '08:00:00', '17:00:00', NULL, 'P', NULL, '2025-04-30 11:54:01', 'manual'),
(8, 5, '2023-10-01', '08:30:00', '17:45:00', NULL, 'P', NULL, '2025-04-30 11:54:01', 'manual'),
(9, 6, '2023-10-01', NULL, NULL, NULL, 'A', NULL, '2025-04-30 11:54:01', 'manual'),
(10, 7, '2023-10-01', '08:00:00', '17:00:00', NULL, 'P', NULL, '2025-04-30 11:54:01', 'manual'),
(27, 2, '2025-04-30', '07:50:00', NULL, NULL, 'P', NULL, '2025-04-30 15:33:05', 'manual'),
(28, 3, '2025-04-30', '07:50:00', NULL, NULL, 'P', NULL, '2025-04-30 15:33:05', 'manual'),
(29, 4, '2025-04-30', '07:50:00', NULL, NULL, 'P', NULL, '2025-04-30 15:33:05', 'manual'),
(30, 5, '2025-04-30', '08:15:00', NULL, NULL, 'P', NULL, '2025-04-30 15:33:05', 'manual'),
(31, 6, '2025-04-30', '08:15:00', NULL, NULL, 'P', NULL, '2025-04-30 15:33:05', 'manual'),
(32, 7, '2025-04-30', '08:15:00', NULL, NULL, 'P', NULL, '2025-04-30 15:33:05', 'manual'),
(33, 8, '2025-04-30', NULL, NULL, NULL, 'A', NULL, '2025-04-30 15:33:05', 'manual'),
(34, 9, '2025-04-30', NULL, NULL, NULL, 'A', NULL, '2025-04-30 15:33:05', 'manual'),
(35, 10, '2025-04-30', NULL, NULL, NULL, 'A', NULL, '2025-04-30 15:33:05', 'manual');

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `log_id` bigint(20) NOT NULL,
  `user_id` int(11) DEFAULT NULL COMMENT 'User making the change, NULL if system',
  `action_type` enum('CREATE','UPDATE','DELETE') NOT NULL,
  `target_entity` varchar(100) NOT NULL,
  `target_entity_id` int(11) NOT NULL,
  `timestamp` datetime DEFAULT current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'JSON containing old and new values' CHECK (json_valid(`details`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`log_id`, `user_id`, `action_type`, `target_entity`, `target_entity_id`, `timestamp`, `ip_address`, `user_agent`, `details`) VALUES
(1, 1, 'CREATE', 'Employee', 1, '2025-04-30 11:55:39', '192.168.1.100', NULL, '{\"old\": null, \"new\": {\"name\": \"Nguyễn Văn Admin\"}}'),
(2, 2, 'UPDATE', 'Employee', 2, '2025-04-30 11:55:39', '192.168.1.101', NULL, '{\"old\": {\"salary\": 20000000}, \"new\": {\"salary\": 25000000}}'),
(3, 3, 'DELETE', 'Document', 3, '2025-04-30 11:55:39', '192.168.1.102', NULL, '{\"old\": {\"title\": \"Mẫu hợp đồng cũ\"}}'),
(4, 1, 'CREATE', 'Asset', 1, '2025-04-30 11:55:39', '192.168.1.100', NULL, '{\"old\": null, \"new\": {\"name\": \"Laptop Dell\"}}'),
(5, 4, 'UPDATE', 'Contract', 4, '2025-04-30 11:55:39', '192.168.1.103', NULL, '{\"old\": {\"end_date\": null}, \"new\": {\"end_date\": \"2023-04-01\"}}'),
(6, 5, 'CREATE', 'Training', 5, '2025-04-30 11:55:39', '192.168.1.104', NULL, '{\"old\": null, \"new\": {\"name\": \"Marketing Digital\"}}'),
(7, 6, 'DELETE', 'Leave', 6, '2025-04-30 11:55:39', '192.168.1.105', NULL, '{\"old\": {\"reason\": \"Khám sức khỏe\"}}'),
(8, 7, 'UPDATE', 'Salary', 7, '2025-04-30 11:55:39', '192.168.1.106', NULL, '{\"old\": 15000000, \"new\": 18000000}'),
(9, 8, 'CREATE', 'Project', 1, '2025-04-30 11:55:39', '192.168.1.107', NULL, '{\"old\": null, \"new\": {\"name\": \"Dự án CRM\"}}'),
(10, 9, 'DELETE', 'User', 9, '2025-04-30 11:55:39', '192.168.1.108', NULL, '{\"old\": {\"username\": \"old_user\"}}');

-- --------------------------------------------------------

--
-- Table structure for table `backup_logs`
--

CREATE TABLE `backup_logs` (
  `id` int(11) NOT NULL,
  `backup_type` enum('full','incremental','differential') NOT NULL,
  `file_path` varchar(512) NOT NULL,
  `file_size_bytes` bigint(20) NOT NULL,
  `status` enum('success','failed','in_progress') NOT NULL,
  `error_message` text DEFAULT NULL,
  `duration_seconds` int(11) DEFAULT NULL,
  `created_by_user_id` int(11) DEFAULT NULL COMMENT 'User initiating backup, NULL if automated',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `backup_logs`
--

INSERT INTO `backup_logs` (`id`, `backup_type`, `file_path`, `file_size_bytes`, `status`, `error_message`, `duration_seconds`, `created_by_user_id`, `created_at`) VALUES
(1, 'full', '/backups/full_20231001.zip', 1024000000, 'success', NULL, NULL, NULL, '2025-04-30 12:00:32'),
(2, 'incremental', '/backups/inc_20231002.zip', 204800000, 'success', NULL, NULL, NULL, '2025-04-30 12:00:32'),
(3, 'differential', '/backups/diff_20231003.zip', 512000000, 'failed', 'Lỗi đĩa cứng', NULL, NULL, '2025-04-30 12:00:32'),
(4, 'full', '/backups/full_20231004.zip', 1536000000, 'success', NULL, NULL, NULL, '2025-04-30 12:00:32'),
(5, 'incremental', '/backups/inc_20231005.zip', 256000000, 'success', NULL, NULL, NULL, '2025-04-30 12:00:32'),
(6, 'differential', '/backups/diff_20231006.zip', 768000000, 'success', NULL, NULL, NULL, '2025-04-30 12:00:32'),
(7, 'full', '/backups/full_20231007.zip', 2048000000, 'in_progress', NULL, NULL, NULL, '2025-04-30 12:00:32'),
(8, 'incremental', '/backups/inc_20231008.zip', 307200000, 'failed', 'Mất kết nối mạng', NULL, NULL, '2025-04-30 12:00:32'),
(9, 'differential', '/backups/diff_20231009.zip', 1024000000, 'success', NULL, NULL, NULL, '2025-04-30 12:00:32'),
(10, 'full', '/backups/full_20231010.zip', 2560000000, 'success', NULL, NULL, NULL, '2025-04-30 12:00:32');

-- --------------------------------------------------------

--
-- Table structure for table `benefits`
--

CREATE TABLE `benefits` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `type` varchar(50) NOT NULL COMMENT 'e.g., Health, Retirement, Allowance',
  `amount` decimal(10,2) DEFAULT NULL COMMENT 'Fixed amount, if applicable',
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `benefits`
--

INSERT INTO `benefits` (`id`, `name`, `description`, `type`, `amount`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Bảo hiểm y tế', 'Bảo hiểm cho nhân viên và gia đình', 'Health', 1000000.00, 'active', '2025-04-30 04:56:20', '2025-04-30 04:56:20'),
(2, 'Bảo hiểm thất nghiệp', 'Hỗ trợ thất nghiệp', 'Retirement', 500000.00, 'active', '2025-04-30 04:56:20', '2025-04-30 04:56:20'),
(3, 'Phụ cấp đi lại', 'Hỗ trợ xăng xe', 'Allowance', 500000.00, 'active', '2025-04-30 04:56:20', '2025-04-30 04:56:20'),
(4, 'Phụ cấp ăn trưa', 'Tiền ăn trưa hàng tháng', 'Allowance', 300000.00, 'active', '2025-04-30 04:56:20', '2025-04-30 04:56:20'),
(5, 'Thưởng cuối năm', 'Thưởng theo hiệu suất', 'Bonus', NULL, 'active', '2025-04-30 04:56:20', '2025-04-30 04:56:20'),
(6, 'Du lịch công ty', 'Tour du lịch hàng năm', 'Other', NULL, 'active', '2025-04-30 04:56:20', '2025-04-30 04:56:20'),
(7, 'Hỗ trợ học phí', 'Hỗ trợ khóa học chuyên môn', 'Education', 2000000.00, 'active', '2025-04-30 04:56:20', '2025-04-30 04:56:20'),
(8, 'Phòng gym', 'Thẻ thành viên phòng gym', 'Health', 500000.00, 'active', '2025-04-30 04:56:20', '2025-04-30 04:56:20'),
(9, 'Bảo hiểm nhân thọ', 'Bảo hiểm tử kỳ', 'Insurance', 800000.00, 'active', '2025-04-30 04:56:20', '2025-04-30 04:56:20'),
(10, 'Quà sinh nhật', 'Voucher mua sắm', 'Other', 200000.00, 'active', '2025-04-30 04:56:20', '2025-04-30 04:56:20');

-- --------------------------------------------------------

--
-- Table structure for table `bonuses`
--

CREATE TABLE `bonuses` (
  `bonus_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `bonus_type` varchar(50) NOT NULL COMMENT 'e.g., Performance, Referral, Spot',
  `amount` decimal(15,2) DEFAULT NULL,
  `effective_date` date NOT NULL COMMENT 'Date bonus applies',
  `payroll_id` int(11) DEFAULT NULL COMMENT 'FK to payroll where this bonus was included',
  `reason` text NOT NULL,
  `status` enum('pending','approved','paid','rejected') DEFAULT 'pending',
  `approved_by_user_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bonuses`
--

INSERT INTO `bonuses` (`bonus_id`, `employee_id`, `bonus_type`, `amount`, `effective_date`, `payroll_id`, `reason`, `status`, `approved_by_user_id`, `created_at`) VALUES
(1, 1, 'Performance', 5000000.00, '2023-10-01', NULL, 'Hoàn thành dự án xuất sắc', 'paid', NULL, '2025-04-30 11:57:33'),
(2, 2, 'Referral', 3000000.00, '2023-10-01', NULL, 'Giới thiệu nhân viên mới', 'paid', NULL, '2025-04-30 11:57:33'),
(3, 3, 'Spot', 2000000.00, '2023-10-01', NULL, 'Giải quyết vấn đề nhanh', 'paid', NULL, '2025-04-30 11:57:33'),
(4, 4, 'Performance', 4000000.00, '2023-10-01', NULL, 'Đạt KPI cao', 'paid', NULL, '2025-04-30 11:57:33'),
(5, 5, 'Annual', 3000000.00, '2023-10-01', NULL, 'Thưởng cuối năm', 'paid', NULL, '2025-04-30 11:57:33'),
(6, 6, 'Spot', 1000000.00, '2023-10-01', NULL, 'Hỗ trợ đồng nghiệp', 'paid', NULL, '2025-04-30 11:57:33'),
(7, 7, 'Performance', 3500000.00, '2023-10-01', NULL, 'Hoàn thành deadline', 'paid', NULL, '2025-04-30 11:57:33'),
(8, 8, 'Referral', 2500000.00, '2023-10-01', NULL, 'Giới thiệu khách hàng', 'paid', NULL, '2025-04-30 11:57:33'),
(9, 9, 'Spot', 1500000.00, '2023-10-01', NULL, 'Sáng kiến cải tiến', 'paid', NULL, '2025-04-30 11:57:33'),
(10, 10, 'Annual', 2000000.00, '2023-10-01', NULL, 'Thưởng cuối năm', 'paid', NULL, '2025-04-30 11:57:33');

-- --------------------------------------------------------

--
-- Table structure for table `certificates`
--

CREATE TABLE `certificates` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `issuing_organization` varchar(255) NOT NULL,
  `issue_date` date NOT NULL,
  `expiry_date` date DEFAULT NULL,
  `credential_id` varchar(100) DEFAULT NULL,
  `file_url` varchar(512) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `certificates`
--

INSERT INTO `certificates` (`id`, `employee_id`, `name`, `issuing_organization`, `issue_date`, `expiry_date`, `credential_id`, `file_url`, `created_at`, `updated_at`) VALUES
(1, 1, 'AWS Certified', 'Amazon', '2022-01-01', '2025-01-01', 'AWS-123', NULL, '2025-04-30 04:56:59', '2025-04-30 04:56:59'),
(2, 2, 'PMP', 'PMI', '2021-02-01', '2026-02-01', 'PMP-456', NULL, '2025-04-30 04:56:59', '2025-04-30 04:56:59'),
(3, 3, 'HR Certification', 'HRCI', '2020-03-01', '2024-03-01', 'HRC-789', NULL, '2025-04-30 04:56:59', '2025-04-30 04:56:59'),
(4, 4, 'Google Analytics', 'Google', '2023-04-01', '2026-04-01', 'GA-101', NULL, '2025-04-30 04:56:59', '2025-04-30 04:56:59'),
(5, 5, 'Scrum Master', 'Scrum.org', '2022-05-01', NULL, 'SM-202', NULL, '2025-04-30 04:56:59', '2025-04-30 04:56:59'),
(6, 6, 'CEH', 'EC-Council', '2021-06-01', '2024-06-01', 'CEH-303', NULL, '2025-04-30 04:56:59', '2025-04-30 04:56:59'),
(7, 7, 'CPA', 'AICPA', '2020-07-01', '2025-07-01', 'CPA-404', NULL, '2025-04-30 04:56:59', '2025-04-30 04:56:59'),
(8, 8, 'Adobe Certified', 'Adobe', '2023-08-01', '2026-08-01', 'ADB-505', NULL, '2025-04-30 04:56:59', '2025-04-30 04:56:59'),
(9, 9, 'Salesforce Admin', 'Salesforce', '2022-09-01', NULL, 'SF-606', NULL, '2025-04-30 04:56:59', '2025-04-30 04:56:59'),
(10, 10, 'TOEIC 950', 'ETS', '2021-10-01', NULL, 'TOEIC-707', NULL, '2025-04-30 04:56:59', '2025-04-30 04:56:59');

-- --------------------------------------------------------

--
-- Table structure for table `contracts`
--

CREATE TABLE `contracts` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `contract_code` varchar(50) DEFAULT NULL,
  `contract_type` varchar(50) NOT NULL COMMENT 'e.g., Permanent, Fixed-Term, Intern',
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL COMMENT 'NULL for permanent contracts',
  `salary` decimal(15,2) NOT NULL COMMENT 'Base salary defined in contract',
  `salary_currency` varchar(3) NOT NULL DEFAULT 'VND',
  `status` enum('draft','active','expired','terminated') NOT NULL DEFAULT 'draft',
  `file_url` varchar(512) DEFAULT NULL COMMENT 'Link to scanned contract PDF',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contracts`
--

INSERT INTO `contracts` (`id`, `employee_id`, `contract_code`, `contract_type`, `start_date`, `end_date`, `salary`, `salary_currency`, `status`, `file_url`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, 'Permanent', '2020-01-01', NULL, 30000000.00, 'VND', 'draft', NULL, '2025-04-30 04:54:09', '2025-04-30 04:54:09'),
(2, 2, NULL, 'Permanent', '2020-02-01', NULL, 25000000.00, 'VND', 'draft', NULL, '2025-04-30 04:54:09', '2025-04-30 04:54:09'),
(3, 3, NULL, 'Permanent', '2020-03-01', NULL, 22000000.00, 'VND', 'draft', NULL, '2025-04-30 04:54:09', '2025-04-30 04:54:09'),
(4, 4, NULL, 'Fixed-Term', '2020-04-01', '2023-04-01', 15000000.00, 'VND', 'draft', NULL, '2025-04-30 04:54:09', '2025-04-30 04:54:09'),
(5, 5, NULL, 'Fixed-Term', '2020-05-01', '2023-05-01', 15000000.00, 'VND', 'draft', NULL, '2025-04-30 04:54:09', '2025-04-30 04:54:09'),
(6, 6, NULL, 'Intern', '2020-06-01', '2020-12-01', 5000000.00, 'VND', 'draft', NULL, '2025-04-30 04:54:09', '2025-04-30 04:54:09'),
(7, 7, NULL, 'Permanent', '2020-07-01', NULL, 18000000.00, 'VND', 'draft', NULL, '2025-04-30 04:54:09', '2025-04-30 04:54:09'),
(8, 8, NULL, 'Permanent', '2020-08-01', NULL, 20000000.00, 'VND', 'draft', NULL, '2025-04-30 04:54:09', '2025-04-30 04:54:09'),
(9, 9, NULL, 'Fixed-Term', '2020-09-01', '2023-09-01', 16000000.00, 'VND', 'draft', NULL, '2025-04-30 04:54:09', '2025-04-30 04:54:09'),
(10, 10, NULL, 'Permanent', '2020-10-01', NULL, 17000000.00, 'VND', 'draft', NULL, '2025-04-30 04:54:09', '2025-04-30 04:54:09');

-- --------------------------------------------------------

--
-- Table structure for table `degrees`
--

CREATE TABLE `degrees` (
  `degree_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `degree_name` varchar(255) NOT NULL COMMENT 'e.g., Bachelor of Science',
  `major` varchar(255) DEFAULT NULL,
  `institution` varchar(255) NOT NULL,
  `graduation_date` date NOT NULL,
  `gpa` decimal(4,2) DEFAULT NULL,
  `attachment_url` varchar(512) DEFAULT NULL COMMENT 'Link to scanned degree',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `degrees`
--

INSERT INTO `degrees` (`degree_id`, `employee_id`, `degree_name`, `major`, `institution`, `graduation_date`, `gpa`, `attachment_url`, `created_at`, `updated_at`) VALUES
(1, 1, 'Cử nhân CNTT', 'Khoa học máy tính', 'Đại học Bách Khoa', '2010-06-01', 3.60, NULL, '2025-04-30 11:56:51', '2025-04-30 11:56:51'),
(2, 2, 'Thạc sĩ Quản trị', 'Quản trị kinh doanh', 'Đại học Kinh tế', '2015-05-01', 3.80, NULL, '2025-04-30 11:56:51', '2025-04-30 11:56:51'),
(3, 3, 'Cử nhân Luật', 'Luật dân sự', 'Đại học Luật', '2012-07-01', 3.50, NULL, '2025-04-30 11:56:51', '2025-04-30 11:56:51'),
(4, 4, 'Kỹ sư phần mềm', 'Công nghệ phần mềm', 'Đại học Công nghệ', '2018-08-01', 3.70, NULL, '2025-04-30 11:56:51', '2025-04-30 11:56:51'),
(5, 5, 'Cử nhân Marketing', 'Tiếp thị số', 'Đại học Kinh tế', '2019-09-01', 3.40, NULL, '2025-04-30 11:56:51', '2025-04-30 11:56:51'),
(6, 6, 'Cử nhân Tài chính', 'Tài chính doanh nghiệp', 'Đại học Ngân hàng', '2017-10-01', 3.90, NULL, '2025-04-30 11:56:51', '2025-04-30 11:56:51'),
(7, 7, 'Thạc sĩ Kế toán', 'Kế toán quốc tế', 'Đại học Tài chính', '2020-11-01', 3.80, NULL, '2025-04-30 11:56:51', '2025-04-30 11:56:51'),
(8, 8, 'Cử nhân Thiết kế', 'Thiết kế đồ họa', 'Đại học Mỹ thuật', '2021-12-01', 3.50, NULL, '2025-04-30 11:56:51', '2025-04-30 11:56:51'),
(9, 9, 'Kỹ sư xây dựng', 'Xây dựng dân dụng', 'Đại học Xây dựng', '2016-04-01', 3.60, NULL, '2025-04-30 11:56:51', '2025-04-30 11:56:51'),
(10, 10, 'Cử nhân Ngôn ngữ', 'Tiếng Anh thương mại', 'Đại học Ngoại ngữ', '2019-03-01', 3.70, NULL, '2025-04-30 11:56:51', '2025-04-30 11:56:51');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `manager_id` int(11) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `name`, `description`, `manager_id`, `parent_id`, `created_at`, `updated_at`) VALUES
(1, 'IT', 'Phòng Công nghệ Thông tin', NULL, NULL, '2025-04-30 11:53:16', '2025-04-30 11:53:16'),
(2, 'HR', 'Phòng Nhân sự', NULL, NULL, '2025-04-30 11:53:16', '2025-04-30 11:53:16'),
(3, 'Finance', 'Phòng Tài chính', NULL, NULL, '2025-04-30 11:53:16', '2025-04-30 11:53:16'),
(4, 'Marketing', 'Phòng Tiếp thị', NULL, NULL, '2025-04-30 11:53:16', '2025-04-30 11:53:16'),
(5, 'Sales', 'Phòng Kinh doanh', NULL, NULL, '2025-04-30 11:53:16', '2025-04-30 11:53:16'),
(6, 'Operations', 'Phòng Vận hành', NULL, NULL, '2025-04-30 11:53:16', '2025-04-30 11:53:16'),
(7, 'Legal', 'Phòng Pháp lý', NULL, NULL, '2025-04-30 11:53:16', '2025-04-30 11:53:16'),
(8, 'R&D', 'Phòng Nghiên cứu và Phát triển', NULL, NULL, '2025-04-30 11:53:16', '2025-04-30 11:53:16'),
(9, 'Customer Service', 'Phòng Dịch vụ Khách hàng', NULL, NULL, '2025-04-30 11:53:16', '2025-04-30 11:53:16'),
(10, 'Logistics', 'Phòng Hậu cần', NULL, NULL, '2025-04-30 11:53:16', '2025-04-30 11:53:16');

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

CREATE TABLE `documents` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `file_url` varchar(512) NOT NULL COMMENT 'URL to the primary/latest version',
  `document_type` varchar(50) NOT NULL COMMENT 'e.g., Policy, Guideline, Template, Report',
  `uploaded_by_user_id` int(11) NOT NULL,
  `department_id` int(11) DEFAULT NULL COMMENT 'Department this document belongs to (optional)',
  `access_level` enum('public','internal','restricted') DEFAULT 'internal',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `documents`
--

INSERT INTO `documents` (`id`, `title`, `description`, `file_url`, `document_type`, `uploaded_by_user_id`, `department_id`, `access_level`, `created_at`, `updated_at`) VALUES
(1, 'Chính sách công ty', 'Quy định nội bộ', 'http://example.com/policy.pdf', 'Policy', 1, 1, 'internal', '2025-04-30 11:55:15', '2025-04-30 11:55:15'),
(2, 'Hướng dẫn sử dụng hệ thống', 'Tài liệu IT', 'http://example.com/guide.pdf', 'Guideline', 2, 1, 'internal', '2025-04-30 11:55:15', '2025-04-30 11:55:15'),
(3, 'Mẫu hợp đồng lao động', 'Dành cho HR', 'http://example.com/contract.docx', 'Template', 3, 2, 'restricted', '2025-04-30 11:55:15', '2025-04-30 11:55:15'),
(4, 'Báo cáo tài chính Q1', 'Báo cáo nội bộ', 'http://example.com/finance_q1.xlsx', 'Report', 7, 3, 'restricted', '2025-04-30 11:55:15', '2025-04-30 11:55:15'),
(5, 'Chiến lược Marketing 2023', 'Kế hoạch tiếp thị', 'http://example.com/marketing_plan.pdf', 'Plan', 8, 4, 'internal', '2025-04-30 11:55:15', '2025-04-30 11:55:15'),
(6, 'Biên bản họp', 'Họp triển khai dự án', 'http://example.com/meeting_minutes.docx', 'Minutes', 2, 1, 'internal', '2025-04-30 11:55:15', '2025-04-30 11:55:15'),
(7, 'Danh sách nhân viên', 'Cập nhật tháng 10', 'http://example.com/employee_list.xlsx', 'List', 3, 2, 'restricted', '2025-04-30 11:55:15', '2025-04-30 11:55:15'),
(8, 'Quy trình tuyển dụng', 'HR Process', 'http://example.com/recruitment_process.pdf', 'Process', 3, 2, 'internal', '2025-04-30 11:55:15', '2025-04-30 11:55:15'),
(9, 'Đề án R&D', 'Nghiên cứu sản phẩm mới', 'http://example.com/rnd_proposal.pdf', 'Proposal', 10, 8, 'restricted', '2025-04-30 11:55:15', '2025-04-30 11:55:15'),
(10, 'Hướng dẫn an toàn', 'Quy định an toàn lao động', 'http://example.com/safety_guide.pdf', 'Guideline', 1, 6, 'public', '2025-04-30 11:55:15', '2025-04-30 11:55:15');

-- --------------------------------------------------------

--
-- Table structure for table `document_versions`
--

CREATE TABLE `document_versions` (
  `id` int(11) NOT NULL,
  `document_id` int(11) NOT NULL,
  `version_number` varchar(20) NOT NULL,
  `file_url` varchar(512) NOT NULL COMMENT 'URL to this specific version',
  `changes_description` text DEFAULT NULL,
  `created_by_user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `email_verification_tokens`
--

CREATE TABLE `email_verification_tokens` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `email_verification_tokens`
--

INSERT INTO `email_verification_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES
(1, 1, 'token123', '2023-11-01 00:00:00', '2025-04-30 11:59:41'),
(2, 2, 'token456', '2023-11-01 00:00:00', '2025-04-30 11:59:41'),
(3, 3, 'token789', '2023-11-01 00:00:00', '2025-04-30 11:59:41'),
(4, 4, 'token101', '2023-11-01 00:00:00', '2025-04-30 11:59:41'),
(5, 5, 'token112', '2023-11-01 00:00:00', '2025-04-30 11:59:41'),
(6, 6, 'token131', '2023-11-01 00:00:00', '2025-04-30 11:59:41'),
(7, 7, 'token415', '2023-11-01 00:00:00', '2025-04-30 11:59:41'),
(8, 8, 'token161', '2023-11-01 00:00:00', '2025-04-30 11:59:41'),
(9, 9, 'token718', '2023-11-01 00:00:00', '2025-04-30 11:59:41'),
(10, 10, 'token191', '2023-11-01 00:00:00', '2025-04-30 11:59:41');

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL COMMENT 'Link to the user account',
  `employee_code` varchar(20) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `position_id` int(11) DEFAULT NULL,
  `hire_date` date DEFAULT NULL,
  `termination_date` date DEFAULT NULL,
  `status` enum('active','inactive','terminated','on_leave') DEFAULT 'active',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `user_id`, `employee_code`, `department_id`, `position_id`, `hire_date`, `termination_date`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'EMP-ADM-001', 1, 1, '2020-01-01', NULL, 'active', '2025-04-30 11:53:44', '2025-04-30 11:53:44'),
(2, 2, 'EMP-MGR-IT-002', 1, 2, '2020-02-01', NULL, 'active', '2025-04-30 11:53:44', '2025-04-30 11:53:44'),
(3, 3, 'EMP-HR-003', 2, 3, '2020-03-01', NULL, 'active', '2025-04-30 11:53:44', '2025-04-30 11:53:44'),
(4, 4, 'EMP-IT-004', 1, 4, '2020-04-01', NULL, 'active', '2025-04-30 11:53:44', '2025-04-30 11:53:44'),
(5, 5, 'EMP-IT-005', 1, 4, '2020-05-01', NULL, 'active', '2025-04-30 11:53:44', '2025-04-30 11:53:44'),
(6, 6, 'EMP-HR-006', 2, 4, '2020-06-01', NULL, 'active', '2025-04-30 11:53:44', '2025-04-30 11:53:44'),
(7, 7, 'EMP-FIN-007', 3, 5, '2020-07-01', NULL, 'active', '2025-04-30 11:53:44', '2025-04-30 11:53:44'),
(8, 8, 'EMP-MKT-008', 4, 7, '2020-08-01', NULL, 'active', '2025-04-30 11:53:44', '2025-04-30 11:53:44'),
(9, 9, 'EMP-SLS-009', 5, 8, '2020-09-01', NULL, 'active', '2025-04-30 11:53:44', '2025-04-30 11:53:44'),
(10, 10, 'EMP-OPT-010', 6, 9, '2020-10-01', NULL, 'active', '2025-04-30 11:53:44', '2025-04-30 11:53:44');

-- --------------------------------------------------------

--
-- Table structure for table `employee_positions`
--

CREATE TABLE `employee_positions` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `position_id` int(11) NOT NULL,
  `department_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL COMMENT 'NULL if current position',
  `is_current` tinyint(1) GENERATED ALWAYS AS (if(`end_date` is null,1,0)) STORED,
  `reason_for_change` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee_positions`
--

INSERT INTO `employee_positions` (`id`, `employee_id`, `position_id`, `department_id`, `start_date`, `end_date`, `reason_for_change`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, '2020-01-01', NULL, 'Bổ nhiệm ban đầu', '2025-04-30 11:57:13', '2025-04-30 11:57:13'),
(2, 2, 2, 1, '2020-02-01', '2022-02-01', 'Thăng chức lên Manager', '2025-04-30 11:57:13', '2025-04-30 11:57:13'),
(3, 2, 1, 1, '2022-02-01', NULL, 'Điều chỉnh vai trò', '2025-04-30 11:57:13', '2025-04-30 11:57:13'),
(4, 3, 3, 2, '2020-03-01', NULL, 'Bổ nhiệm HR Manager', '2025-04-30 11:57:13', '2025-04-30 11:57:13'),
(5, 4, 4, 1, '2020-04-01', '2023-04-01', 'Hết hạn hợp đồng', '2025-04-30 11:57:13', '2025-04-30 11:57:13'),
(6, 4, 5, 1, '2023-04-01', NULL, 'Gia hạn hợp đồng', '2025-04-30 11:57:13', '2025-04-30 11:57:13'),
(7, 5, 4, 1, '2020-05-01', NULL, 'Bổ nhiệm nhân viên', '2025-04-30 11:57:13', '2025-04-30 11:57:13'),
(8, 6, 6, 2, '2020-06-01', '2023-06-01', 'Chuyển phòng ban', '2025-04-30 11:57:13', '2025-04-30 11:57:13'),
(9, 7, 7, 3, '2020-07-01', NULL, 'Bổ nhiệm kế toán', '2025-04-30 11:57:13', '2025-04-30 11:57:13'),
(10, 8, 8, 4, '2020-08-01', NULL, 'Nhân viên Marketing', '2025-04-30 11:57:13', '2025-04-30 11:57:13');

-- --------------------------------------------------------

--
-- Table structure for table `family_members`
--

CREATE TABLE `family_members` (
  `family_member_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL COMMENT 'Link to the Employee record',
  `member_name` varchar(255) NOT NULL,
  `relationship` varchar(100) NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `occupation` varchar(100) DEFAULT NULL,
  `is_dependent` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `family_members`
--

INSERT INTO `family_members` (`family_member_id`, `employee_id`, `member_name`, `relationship`, `date_of_birth`, `occupation`, `is_dependent`, `created_at`, `updated_at`) VALUES
(1, 1, 'Nguyễn Thị A', 'Vợ', '1987-05-05', 'Giáo viên', 1, '2025-04-30 11:56:44', '2025-04-30 11:56:44'),
(2, 2, 'Trần Văn B', 'Chồng', '1988-06-06', 'Kỹ sư', 0, '2025-04-30 11:56:44', '2025-04-30 11:56:44'),
(3, 3, 'Lê Thị C', 'Con gái', '2015-07-07', 'Học sinh', 1, '2025-04-30 11:56:44', '2025-04-30 11:56:44'),
(4, 4, 'Phạm Văn D', 'Cha', '1960-08-08', 'Nghỉ hưu', 1, '2025-04-30 11:56:44', '2025-04-30 11:56:44'),
(5, 5, 'Hoàng Thị E', 'Mẹ', '1962-09-09', 'Nội trợ', 1, '2025-04-30 11:56:44', '2025-04-30 11:56:44'),
(6, 6, 'Võ Văn F', 'Anh trai', '1990-10-10', 'Bác sĩ', 0, '2025-04-30 11:56:44', '2025-04-30 11:56:44'),
(7, 7, 'Đặng Thị G', 'Em gái', '1998-11-11', 'Sinh viên', 1, '2025-04-30 11:56:44', '2025-04-30 11:56:44'),
(8, 8, 'Bùi Văn H', 'Con trai', '2018-12-12', 'Học sinh', 1, '2025-04-30 11:56:44', '2025-04-30 11:56:44'),
(9, 9, 'Mai Thị I', 'Vợ', '1995-01-01', 'Kế toán', 1, '2025-04-30 11:56:44', '2025-04-30 11:56:44'),
(10, 10, 'Lý Văn K', 'Chồng', '1994-02-02', 'Luật sư', 0, '2025-04-30 11:56:44', '2025-04-30 11:56:44');

-- --------------------------------------------------------

--
-- Table structure for table `holidays`
--

CREATE TABLE `holidays` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `description` text DEFAULT NULL,
  `is_recurring` tinyint(1) DEFAULT 0 COMMENT '1 if repeats annually on same date',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `holidays`
--

INSERT INTO `holidays` (`id`, `name`, `date`, `description`, `is_recurring`, `created_at`, `updated_at`) VALUES
(1, 'Tết Dương lịch', '2024-01-01', 'Nghỉ Tết Dương lịch', 1, '2025-04-30 11:56:36', '2025-04-30 11:56:36'),
(2, 'Tết Nguyên đán', '2024-02-10', 'Nghỉ Tết Âm lịch', 1, '2025-04-30 11:56:36', '2025-04-30 11:56:36'),
(3, 'Giỗ Tổ Hùng Vương', '2024-04-18', 'Quốc giỗ', 1, '2025-04-30 11:56:36', '2025-04-30 11:56:36'),
(4, 'Ngày Giải phóng', '2024-04-30', 'Thống nhất đất nước', 1, '2025-04-30 11:56:36', '2025-04-30 11:56:36'),
(5, 'Quốc tế Lao động', '2024-05-01', 'Ngày Lao động', 1, '2025-04-30 11:56:36', '2025-04-30 11:56:36'),
(6, 'Quốc khánh', '2024-09-02', 'Ngày Quốc khánh', 1, '2025-04-30 11:56:36', '2025-04-30 11:56:36'),
(7, 'Trung thu', '2024-09-17', 'Tết Trung thu', 1, '2025-04-30 11:56:36', '2025-04-30 11:56:36'),
(8, 'Halloween', '2024-10-31', 'Lễ hội hóa trang', 0, '2025-04-30 11:56:36', '2025-04-30 11:56:36'),
(9, 'Noel', '2024-12-25', 'Giáng sinh', 1, '2025-04-30 11:56:36', '2025-04-30 11:56:36'),
(10, 'Tết Tây', '2025-01-01', 'Năm mới', 1, '2025-04-30 11:56:36', '2025-04-30 11:56:36');

-- --------------------------------------------------------

--
-- Table structure for table `insurance`
--

CREATE TABLE `insurance` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `insurance_type` varchar(50) NOT NULL COMMENT 'e.g., Social, Health, Unemployment',
  `policy_number` varchar(100) DEFAULT NULL,
  `provider` varchar(255) DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `employee_contribution` decimal(15,2) DEFAULT NULL,
  `employer_contribution` decimal(15,2) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `insurance`
--

INSERT INTO `insurance` (`id`, `employee_id`, `insurance_type`, `policy_number`, `provider`, `start_date`, `end_date`, `employee_contribution`, `employer_contribution`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'Social', 'SOC-001', 'Bảo hiểm Xã hội', '2020-01-01', NULL, 800000.00, 1600000.00, 'active', '2025-04-30 04:57:06', '2025-04-30 04:57:06'),
(2, 2, 'Health', 'HLT-002', 'Bảo hiểm Y tế', '2020-02-01', NULL, 500000.00, 1000000.00, 'active', '2025-04-30 04:57:06', '2025-04-30 04:57:06'),
(3, 3, 'Unemployment', 'UNE-003', 'Bảo hiểm Thất nghiệp', '2020-03-01', NULL, 200000.00, 400000.00, 'active', '2025-04-30 04:57:06', '2025-04-30 04:57:06'),
(4, 4, 'Social', 'SOC-004', 'Bảo hiểm Xã hội', '2020-04-01', NULL, 800000.00, 1600000.00, 'active', '2025-04-30 04:57:06', '2025-04-30 04:57:06'),
(5, 5, 'Health', 'HLT-005', 'Bảo hiểm Y tế', '2020-05-01', NULL, 500000.00, 1000000.00, 'active', '2025-04-30 04:57:06', '2025-04-30 04:57:06'),
(6, 6, 'Social', 'SOC-006', 'Bảo hiểm Xã hội', '2020-06-01', NULL, 800000.00, 1600000.00, 'active', '2025-04-30 04:57:06', '2025-04-30 04:57:06'),
(7, 7, 'Health', 'HLT-007', 'Bảo hiểm Y tế', '2020-07-01', NULL, 500000.00, 1000000.00, 'active', '2025-04-30 04:57:06', '2025-04-30 04:57:06'),
(8, 8, 'Social', 'SOC-008', 'Bảo hiểm Xã hội', '2020-08-01', NULL, 800000.00, 1600000.00, 'active', '2025-04-30 04:57:06', '2025-04-30 04:57:06'),
(9, 9, 'Health', 'HLT-009', 'Bảo hiểm Y tế', '2020-09-01', NULL, 500000.00, 1000000.00, 'active', '2025-04-30 04:57:06', '2025-04-30 04:57:06'),
(10, 10, 'Social', 'SOC-010', 'Bảo hiểm Xã hội', '2020-10-01', NULL, 800000.00, 1600000.00, 'active', '2025-04-30 04:57:06', '2025-04-30 04:57:06');

-- --------------------------------------------------------

--
-- Table structure for table `interviews`
--

CREATE TABLE `interviews` (
  `id` int(11) NOT NULL,
  `job_application_id` int(11) NOT NULL,
  `interviewer_employee_id` int(11) NOT NULL,
  `interview_datetime` datetime NOT NULL,
  `duration_minutes` int(11) DEFAULT 60,
  `location` varchar(255) DEFAULT NULL,
  `type` varchar(50) NOT NULL COMMENT 'e.g., Screening, Technical, HR, Panel',
  `status` enum('scheduled','completed','cancelled','rescheduled') NOT NULL DEFAULT 'scheduled',
  `interviewer_feedback` text DEFAULT NULL,
  `candidate_feedback` text DEFAULT NULL,
  `score` decimal(4,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `interviews`
--

INSERT INTO `interviews` (`id`, `job_application_id`, `interviewer_employee_id`, `interview_datetime`, `duration_minutes`, `location`, `type`, `status`, `interviewer_feedback`, `candidate_feedback`, `score`, `created_at`, `updated_at`) VALUES
(1, 1, 3, '2023-10-05 09:00:00', 60, NULL, 'Technical', 'completed', NULL, NULL, NULL, '2025-04-30 04:58:31', '2025-04-30 04:58:31'),
(2, 2, 3, '2023-10-06 10:00:00', 60, NULL, 'HR', 'scheduled', NULL, NULL, NULL, '2025-04-30 04:58:31', '2025-04-30 04:58:31'),
(3, 3, 3, '2023-10-07 11:00:00', 60, NULL, 'Panel', 'completed', NULL, NULL, NULL, '2025-04-30 04:58:31', '2025-04-30 04:58:31'),
(4, 4, 3, '2023-10-08 14:00:00', 60, NULL, 'Technical', 'scheduled', NULL, NULL, NULL, '2025-04-30 04:58:31', '2025-04-30 04:58:31'),
(5, 5, 3, '2023-10-09 15:00:00', 60, NULL, 'HR', 'cancelled', NULL, NULL, NULL, '2025-04-30 04:58:31', '2025-04-30 04:58:31'),
(6, 6, 3, '2023-10-10 16:00:00', 60, NULL, 'Technical', 'completed', NULL, NULL, NULL, '2025-04-30 04:58:31', '2025-04-30 04:58:31'),
(7, 7, 3, '2023-10-11 09:00:00', 60, NULL, 'HR', 'scheduled', NULL, NULL, NULL, '2025-04-30 04:58:31', '2025-04-30 04:58:31'),
(8, 8, 3, '2023-10-12 10:00:00', 60, NULL, 'Panel', 'completed', NULL, NULL, NULL, '2025-04-30 04:58:31', '2025-04-30 04:58:31'),
(9, 9, 3, '2023-10-13 11:00:00', 60, NULL, 'Technical', 'scheduled', NULL, NULL, NULL, '2025-04-30 04:58:31', '2025-04-30 04:58:31'),
(10, 10, 3, '2023-10-14 14:00:00', 60, NULL, 'HR', 'completed', NULL, NULL, NULL, '2025-04-30 04:58:31', '2025-04-30 04:58:31');

-- --------------------------------------------------------

--
-- Table structure for table `job_applications`
--

CREATE TABLE `job_applications` (
  `id` int(11) NOT NULL,
  `job_position_id` int(11) NOT NULL COMMENT 'FK to the specific job opening',
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `resume_url` varchar(512) DEFAULT NULL,
  `cover_letter_url` varchar(512) DEFAULT NULL,
  `source` varchar(100) DEFAULT NULL COMMENT 'e.g., LinkedIn, Website, Referral',
  `status` enum('new','reviewing','shortlisted','interviewing','assessment','offered','hired','rejected','withdrawn') NOT NULL DEFAULT 'new',
  `applied_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `job_applications`
--

INSERT INTO `job_applications` (`id`, `job_position_id`, `first_name`, `last_name`, `email`, `phone`, `resume_url`, `cover_letter_url`, `source`, `status`, `applied_at`, `updated_at`) VALUES
(1, 1, 'Nguyễn', 'A', 'nguyena@gmail.com', '0911111111', 'http://example.com/resume1.pdf', NULL, NULL, 'hired', '2025-04-30 11:58:25', '2025-04-30 11:58:25'),
(2, 2, 'Trần', 'B', 'tranb@gmail.com', '0912222222', 'http://example.com/resume2.pdf', NULL, NULL, 'interviewing', '2025-04-30 11:58:25', '2025-04-30 11:58:25'),
(3, 3, 'Lê', 'C', 'lec@gmail.com', '0913333333', 'http://example.com/resume3.pdf', NULL, NULL, 'shortlisted', '2025-04-30 11:58:25', '2025-04-30 11:58:25'),
(4, 4, 'Phạm', 'D', 'phamd@gmail.com', '0914444444', 'http://example.com/resume4.pdf', NULL, NULL, 'new', '2025-04-30 11:58:25', '2025-04-30 11:58:25'),
(5, 5, 'Hoàng', 'E', 'hoange@gmail.com', '0915555555', 'http://example.com/resume5.pdf', NULL, NULL, 'rejected', '2025-04-30 11:58:25', '2025-04-30 11:58:25'),
(6, 6, 'Võ', 'F', 'vof@gmail.com', '0916666666', 'http://example.com/resume6.pdf', NULL, NULL, 'assessment', '2025-04-30 11:58:25', '2025-04-30 11:58:25'),
(7, 7, 'Đặng', 'G', 'dangg@gmail.com', '0917777777', 'http://example.com/resume7.pdf', NULL, NULL, 'offered', '2025-04-30 11:58:25', '2025-04-30 11:58:25'),
(8, 8, 'Bùi', 'H', 'buih@gmail.com', '0918888888', 'http://example.com/resume8.pdf', NULL, NULL, 'withdrawn', '2025-04-30 11:58:25', '2025-04-30 11:58:25'),
(9, 9, 'Mai', 'I', 'maii@gmail.com', '0919999999', 'http://example.com/resume9.pdf', NULL, NULL, 'hired', '2025-04-30 11:58:25', '2025-04-30 11:58:25'),
(10, 10, 'Lý', 'K', 'lyk@gmail.com', '0910000000', 'http://example.com/resume10.pdf', NULL, NULL, 'reviewing', '2025-04-30 11:58:25', '2025-04-30 11:58:25');

-- --------------------------------------------------------

--
-- Table structure for table `job_positions`
--

CREATE TABLE `job_positions` (
  `id` int(11) NOT NULL,
  `campaign_id` int(11) DEFAULT NULL,
  `position_id` int(11) NOT NULL,
  `title_override` varchar(255) DEFAULT NULL COMMENT 'Specific title for this opening, if different',
  `department_id` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `requirements` text DEFAULT NULL,
  `responsibilities` text DEFAULT NULL,
  `salary_range_min` decimal(15,2) DEFAULT NULL,
  `salary_range_max` decimal(15,2) DEFAULT NULL,
  `status` enum('draft','open','closed','on_hold') NOT NULL DEFAULT 'draft',
  `posting_date` date DEFAULT NULL,
  `closing_date` date DEFAULT NULL,
  `hiring_manager_user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `job_positions`
--

INSERT INTO `job_positions` (`id`, `campaign_id`, `position_id`, `title_override`, `department_id`, `description`, `requirements`, `responsibilities`, `salary_range_min`, `salary_range_max`, `status`, `posting_date`, `closing_date`, `hiring_manager_user_id`, `created_at`, `updated_at`) VALUES
(1, NULL, 5, 'Lập trình viên Java', 1, NULL, NULL, NULL, 15000000.00, 25000000.00, 'open', NULL, NULL, NULL, '2025-04-30 04:58:18', '2025-04-30 04:58:18'),
(2, NULL, 6, 'Chuyên viên HR', 2, NULL, NULL, NULL, 10000000.00, 15000000.00, 'open', NULL, NULL, NULL, '2025-04-30 04:58:18', '2025-04-30 04:58:18'),
(3, NULL, 7, 'Kế toán tổng hợp', 3, NULL, NULL, NULL, 12000000.00, 18000000.00, 'open', NULL, NULL, NULL, '2025-04-30 04:58:18', '2025-04-30 04:58:18'),
(4, NULL, 8, 'Chuyên viên Marketing', 4, NULL, NULL, NULL, 10000000.00, 15000000.00, 'open', NULL, NULL, NULL, '2025-04-30 04:58:18', '2025-04-30 04:58:18'),
(5, NULL, 9, 'Nhân viên Sales', 5, NULL, NULL, NULL, 8000000.00, 12000000.00, 'open', NULL, NULL, NULL, '2025-04-30 04:58:18', '2025-04-30 04:58:18'),
(6, NULL, 10, 'Quản lý vận hành', 6, NULL, NULL, NULL, 15000000.00, 20000000.00, 'open', NULL, NULL, NULL, '2025-04-30 04:58:18', '2025-04-30 04:58:18'),
(7, NULL, 1, 'Admin', 1, NULL, NULL, NULL, 10000000.00, 15000000.00, 'closed', NULL, NULL, NULL, '2025-04-30 04:58:18', '2025-04-30 04:58:18'),
(8, NULL, 2, 'IT Manager', 1, NULL, NULL, NULL, 20000000.00, 30000000.00, 'closed', NULL, NULL, NULL, '2025-04-30 04:58:18', '2025-04-30 04:58:18'),
(9, NULL, 3, 'HR Manager', 2, NULL, NULL, NULL, 18000000.00, 25000000.00, 'closed', NULL, NULL, NULL, '2025-04-30 04:58:18', '2025-04-30 04:58:18'),
(10, NULL, 4, 'Senior Developer', 1, NULL, NULL, NULL, 20000000.00, 30000000.00, 'closed', NULL, NULL, NULL, '2025-04-30 04:58:18', '2025-04-30 04:58:18');

-- --------------------------------------------------------

--
-- Table structure for table `kpi`
--

CREATE TABLE `kpi` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `metric_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `target_value` decimal(15,2) DEFAULT NULL,
  `actual_value` decimal(15,2) DEFAULT NULL,
  `unit` varchar(20) DEFAULT NULL,
  `period_start` date NOT NULL,
  `period_end` date NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kpi`
--

INSERT INTO `kpi` (`id`, `employee_id`, `metric_name`, `description`, `target_value`, `actual_value`, `unit`, `period_start`, `period_end`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'Doanh số', NULL, 1000000000.00, 950000000.00, 'VND', '2023-01-01', '2023-12-31', 'active', '2025-04-30 04:57:55', '2025-04-30 04:57:55'),
(2, 2, 'Số dự án hoàn thành', NULL, 5.00, 4.00, 'projects', '2023-01-01', '2023-12-31', 'active', '2025-04-30 04:57:55', '2025-04-30 04:57:55'),
(3, 3, 'Tuyển dụng thành công', NULL, 20.00, 18.00, 'người', '2023-01-01', '2023-12-31', 'active', '2025-04-30 04:57:55', '2025-04-30 04:57:55'),
(4, 4, 'Số giờ làm việc', NULL, 2000.00, 1950.00, 'giờ', '2023-01-01', '2023-12-31', 'active', '2025-04-30 04:57:55', '2025-04-30 04:57:55'),
(5, 5, 'Tỉ lệ khách hàng hài lòng', NULL, 90.00, 85.00, '%', '2023-01-01', '2023-12-31', 'active', '2025-04-30 04:57:55', '2025-04-30 04:57:55'),
(6, 6, 'Số lỗi phát sinh', NULL, 0.00, 2.00, 'lỗi', '2023-01-01', '2023-12-31', 'active', '2025-04-30 04:57:55', '2025-04-30 04:57:55'),
(7, 7, 'Hoàn thành deadline', NULL, 100.00, 98.00, '%', '2023-01-01', '2023-12-31', 'active', '2025-04-30 04:57:55', '2025-04-30 04:57:55'),
(8, 8, 'Chiến dịch thành công', NULL, 3.00, 3.00, 'chiến dịch', '2023-01-01', '2023-12-31', 'active', '2025-04-30 04:57:55', '2025-04-30 04:57:55'),
(9, 9, 'Tăng trưởng doanh số', NULL, 15.00, 12.00, '%', '2023-01-01', '2023-12-31', 'active', '2025-04-30 04:57:55', '2025-04-30 04:57:55'),
(10, 10, 'Giảm chi phí', NULL, 10.00, 8.00, '%', '2023-01-01', '2023-12-31', 'active', '2025-04-30 04:57:55', '2025-04-30 04:57:55');

-- --------------------------------------------------------

--
-- Table structure for table `leaves`
--

CREATE TABLE `leaves` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `leave_type` varchar(50) NOT NULL COMMENT 'e.g., Annual, Sick, Unpaid, Maternity',
  `start_date` datetime NOT NULL COMMENT 'Include time for partial days',
  `end_date` datetime NOT NULL COMMENT 'Include time for partial days',
  `leave_duration_days` decimal(4,1) NOT NULL,
  `reason` text NOT NULL,
  `status` enum('pending','approved','rejected','cancelled') DEFAULT 'pending',
  `approved_by_user_id` int(11) DEFAULT NULL COMMENT 'User who approved/rejected',
  `approver_comments` text DEFAULT NULL,
  `attachment_url` varchar(512) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `leaves`
--

INSERT INTO `leaves` (`id`, `employee_id`, `leave_type`, `start_date`, `end_date`, `leave_duration_days`, `reason`, `status`, `approved_by_user_id`, `approver_comments`, `attachment_url`, `created_at`, `updated_at`) VALUES
(1, 1, 'Annual', '2023-10-05 00:00:00', '2023-10-07 00:00:00', 3.0, 'Nghỉ phép cá nhân', 'approved', NULL, NULL, NULL, '2025-04-30 11:54:23', '2025-04-30 11:54:23'),
(2, 2, 'Sick', '2023-10-10 00:00:00', '2023-10-11 00:00:00', 2.0, 'Ốm', 'approved', NULL, NULL, NULL, '2025-04-30 11:54:23', '2025-04-30 11:54:23'),
(3, 3, 'Unpaid', '2023-10-15 00:00:00', '2023-10-16 00:00:00', 2.0, 'Việc gia đình', 'pending', NULL, NULL, NULL, '2025-04-30 11:54:23', '2025-04-30 11:54:23'),
(4, 4, 'Maternity', '2023-11-01 00:00:00', '2024-02-01 00:00:00', 90.0, 'Nghỉ thai sản', 'approved', NULL, NULL, NULL, '2025-04-30 11:54:23', '2025-04-30 11:54:23'),
(5, 5, 'Annual', '2023-10-20 00:00:00', '2023-10-22 00:00:00', 3.0, 'Du lịch', 'approved', NULL, NULL, NULL, '2025-04-30 11:54:23', '2025-04-30 11:54:23'),
(6, 6, 'Sick', '2023-10-25 00:00:00', '2023-10-26 00:00:00', 2.0, 'Khám sức khỏe', 'approved', NULL, NULL, NULL, '2025-04-30 11:54:23', '2025-04-30 11:54:23'),
(7, 7, 'Unpaid', '2023-11-05 00:00:00', '2023-11-07 00:00:00', 3.0, 'Học tập', 'pending', NULL, NULL, NULL, '2025-04-30 11:54:23', '2025-04-30 11:54:23'),
(8, 8, 'Annual', '2023-12-01 00:00:00', '2023-12-05 00:00:00', 5.0, 'Nghỉ lễ', 'approved', NULL, NULL, NULL, '2025-04-30 11:54:23', '2025-04-30 11:54:23'),
(9, 9, 'Sick', '2023-10-18 00:00:00', '2023-10-19 00:00:00', 2.0, 'Cảm cúm', 'approved', NULL, NULL, NULL, '2025-04-30 11:54:23', '2025-04-30 11:54:23'),
(10, 10, 'Unpaid', '2023-10-30 00:00:00', '2023-10-31 00:00:00', 2.0, 'Việc riêng', 'pending', NULL, NULL, NULL, '2025-04-30 11:54:23', '2025-04-30 11:54:23');

-- --------------------------------------------------------

--
-- Table structure for table `login_attempts`
--

CREATE TABLE `login_attempts` (
  `id` int(11) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `username_attempted` varchar(100) DEFAULT NULL,
  `attempt_time` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `login_attempts`
--

INSERT INTO `login_attempts` (`id`, `ip_address`, `username_attempted`, `attempt_time`) VALUES
(1, '192.168.1.100', 'admin', '2025-04-30 12:00:15'),
(2, '192.168.1.101', 'employee1', '2025-04-30 12:00:15'),
(3, '192.168.1.102', 'unknown_user', '2025-04-30 12:00:15'),
(4, '192.168.1.103', 'manager_it', '2025-04-30 12:00:15'),
(5, '192.168.1.104', 'hr_manager', '2025-04-30 12:00:15'),
(6, '192.168.1.105', 'test_user', '2025-04-30 12:00:15'),
(7, '192.168.1.106', 'employee2', '2025-04-30 12:00:15'),
(8, '192.168.1.107', 'employee3', '2025-04-30 12:00:15'),
(9, '192.168.1.108', 'employee4', '2025-04-30 12:00:15'),
(10, '192.168.1.109', 'employee5', '2025-04-30 12:00:15');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL COMMENT 'Recipient user',
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` varchar(50) NOT NULL DEFAULT 'info' COMMENT 'e.g., info, warning, success, reminder',
  `related_entity_type` varchar(50) DEFAULT NULL,
  `related_entity_id` int(11) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `read_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `type`, `related_entity_type`, `related_entity_id`, `is_read`, `read_at`, `created_at`) VALUES
(1, 1, 'Chào mừng', 'Chào mừng bạn đến với hệ thống!', 'info', NULL, NULL, 1, NULL, '2025-04-30 11:56:08'),
(2, 2, 'Nhắc nhở', 'Vui lòng cập nhật hợp đồng cho nhân viên IT004', 'warning', NULL, NULL, 0, NULL, '2025-04-30 11:56:08'),
(3, 3, 'Duyệt đơn nghỉ phép', 'Đơn nghỉ phép của employee6 đang chờ duyệt', 'reminder', NULL, NULL, 0, NULL, '2025-04-30 11:56:08'),
(4, 4, 'Thông báo lương', 'Lương tháng 10 đã được chuyển', 'success', NULL, NULL, 1, NULL, '2025-04-30 11:56:08'),
(5, 5, 'Lỗi hệ thống', 'Máy chủ IT sẽ bảo trì vào 20/10', 'warning', NULL, NULL, 0, NULL, '2025-04-30 11:56:08'),
(6, 6, 'Đào tạo', 'Đăng ký khóa học Python trước 30/10', 'info', NULL, NULL, 0, NULL, '2025-04-30 11:56:08'),
(7, 7, 'Cảnh báo', 'Tài sản ASSET-004 cần bảo trì', 'warning', NULL, NULL, 0, NULL, '2025-04-30 11:56:08'),
(8, 8, 'Cập nhật', 'Chính sách mới đã được thêm', 'info', NULL, NULL, 1, NULL, '2025-04-30 11:56:08'),
(9, 9, 'Nhắc nhở', 'Hợp đồng của bạn sắp hết hạn', 'reminder', NULL, NULL, 0, NULL, '2025-04-30 11:56:08'),
(10, 10, 'Thông báo', 'Dự án CRM đã hoàn thành', 'success', NULL, NULL, 1, NULL, '2025-04-30 11:56:08');

-- --------------------------------------------------------

--
-- Table structure for table `onboarding`
--

CREATE TABLE `onboarding` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('pending','in_progress','completed','cancelled') NOT NULL DEFAULT 'pending',
  `buddy_employee_id` int(11) DEFAULT NULL COMMENT 'Assigned buddy',
  `checklist_items_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'JSON array of tasks/items' CHECK (json_valid(`checklist_items_json`)),
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `onboarding`
--

INSERT INTO `onboarding` (`id`, `employee_id`, `start_date`, `end_date`, `status`, `buddy_employee_id`, `checklist_items_json`, `notes`, `created_at`, `updated_at`) VALUES
(1, 1, '2020-01-01', '2020-01-07', 'completed', 2, NULL, NULL, '2025-04-30 04:58:40', '2025-04-30 04:58:40'),
(2, 2, '2020-02-01', '2020-02-07', 'completed', 1, NULL, NULL, '2025-04-30 04:58:40', '2025-04-30 04:58:40'),
(3, 3, '2020-03-01', '2020-03-07', 'completed', 2, NULL, NULL, '2025-04-30 04:58:40', '2025-04-30 04:58:40'),
(4, 4, '2020-04-01', '2020-04-07', 'completed', 5, NULL, NULL, '2025-04-30 04:58:40', '2025-04-30 04:58:40'),
(5, 5, '2020-05-01', '2020-05-07', 'completed', 6, NULL, NULL, '2025-04-30 04:58:40', '2025-04-30 04:58:40'),
(6, 6, '2020-06-01', '2020-06-07', 'completed', 7, NULL, NULL, '2025-04-30 04:58:40', '2025-04-30 04:58:40'),
(7, 7, '2020-07-01', '2020-07-07', 'completed', 8, NULL, NULL, '2025-04-30 04:58:40', '2025-04-30 04:58:40'),
(8, 8, '2020-08-01', '2020-08-07', 'completed', 9, NULL, NULL, '2025-04-30 04:58:40', '2025-04-30 04:58:40'),
(9, 9, '2020-09-01', '2020-09-07', 'completed', 10, NULL, NULL, '2025-04-30 04:58:40', '2025-04-30 04:58:40'),
(10, 10, '2020-10-01', '2020-10-07', 'completed', 1, NULL, NULL, '2025-04-30 04:58:40', '2025-04-30 04:58:40');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_reset_tokens`
--

INSERT INTO `password_reset_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES
(1, 1, 'reset123', '2023-11-01 00:00:00', '2025-04-30 11:59:52'),
(2, 2, 'reset456', '2023-11-01 00:00:00', '2025-04-30 11:59:52'),
(3, 3, 'reset789', '2023-11-01 00:00:00', '2025-04-30 11:59:52'),
(4, 4, 'reset101', '2023-11-01 00:00:00', '2025-04-30 11:59:52'),
(5, 5, 'reset112', '2023-11-01 00:00:00', '2025-04-30 11:59:52'),
(6, 6, 'reset131', '2023-11-01 00:00:00', '2025-04-30 11:59:52'),
(7, 7, 'reset415', '2023-11-01 00:00:00', '2025-04-30 11:59:52'),
(8, 8, 'reset161', '2023-11-01 00:00:00', '2025-04-30 11:59:52'),
(9, 9, 'reset718', '2023-11-01 00:00:00', '2025-04-30 11:59:52'),
(10, 10, 'reset191', '2023-11-01 00:00:00', '2025-04-30 11:59:52');

-- --------------------------------------------------------

--
-- Table structure for table `payroll`
--

CREATE TABLE `payroll` (
  `payroll_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `pay_period_start` date NOT NULL,
  `pay_period_end` date NOT NULL,
  `work_days_payable` decimal(4,1) NOT NULL COMMENT 'Days eligible for payment in period',
  `base_salary_period` decimal(15,2) NOT NULL COMMENT 'Base salary for the pay period',
  `allowances_total` decimal(15,2) DEFAULT 0.00,
  `bonuses_total` decimal(15,2) DEFAULT 0.00,
  `deductions_total` decimal(15,2) DEFAULT 0.00,
  `gross_salary` decimal(15,2) NOT NULL,
  `tax_deduction` decimal(15,2) DEFAULT 0.00,
  `insurance_deduction` decimal(15,2) DEFAULT 0.00,
  `net_salary` decimal(15,2) NOT NULL COMMENT 'Take-home pay',
  `payment_date` date DEFAULT NULL,
  `status` enum('pending','calculated','approved','paid','rejected') DEFAULT 'pending',
  `generated_at` datetime DEFAULT current_timestamp(),
  `generated_by_user_id` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payroll`
--

INSERT INTO `payroll` (`payroll_id`, `employee_id`, `pay_period_start`, `pay_period_end`, `work_days_payable`, `base_salary_period`, `allowances_total`, `bonuses_total`, `deductions_total`, `gross_salary`, `tax_deduction`, `insurance_deduction`, `net_salary`, `payment_date`, `status`, `generated_at`, `generated_by_user_id`, `notes`) VALUES
(1, 1, '2023-10-01', '2023-10-31', 22.0, 25000000.00, 0.00, 0.00, 0.00, 25000000.00, 0.00, 0.00, 22000000.00, NULL, 'paid', '2025-04-30 11:57:25', NULL, NULL),
(2, 2, '2023-10-01', '2023-10-31', 22.0, 20000000.00, 0.00, 0.00, 0.00, 20000000.00, 0.00, 0.00, 17600000.00, NULL, 'paid', '2025-04-30 11:57:25', NULL, NULL),
(3, 3, '2023-10-01', '2023-10-31', 20.0, 18000000.00, 0.00, 0.00, 0.00, 18000000.00, 0.00, 0.00, 15840000.00, NULL, 'paid', '2025-04-30 11:57:25', NULL, NULL),
(4, 4, '2023-10-01', '2023-10-31', 22.0, 15000000.00, 0.00, 0.00, 0.00, 15000000.00, 0.00, 0.00, 13200000.00, NULL, 'paid', '2025-04-30 11:57:25', NULL, NULL),
(5, 5, '2023-10-01', '2023-10-31', 21.0, 15000000.00, 0.00, 0.00, 0.00, 15000000.00, 0.00, 0.00, 13200000.00, NULL, 'paid', '2025-04-30 11:57:25', NULL, NULL),
(6, 6, '2023-10-01', '2023-10-31', 18.0, 5000000.00, 0.00, 0.00, 0.00, 5000000.00, 0.00, 0.00, 4400000.00, NULL, 'paid', '2025-04-30 11:57:25', NULL, NULL),
(7, 7, '2023-10-01', '2023-10-31', 22.0, 18000000.00, 0.00, 0.00, 0.00, 18000000.00, 0.00, 0.00, 15840000.00, NULL, 'paid', '2025-04-30 11:57:25', NULL, NULL),
(8, 8, '2023-10-01', '2023-10-31', 22.0, 20000000.00, 0.00, 0.00, 0.00, 20000000.00, 0.00, 0.00, 17600000.00, NULL, 'paid', '2025-04-30 11:57:25', NULL, NULL),
(9, 9, '2023-10-01', '2023-10-31', 22.0, 16000000.00, 0.00, 0.00, 0.00, 16000000.00, 0.00, 0.00, 14080000.00, NULL, 'paid', '2025-04-30 11:57:25', NULL, NULL),
(10, 10, '2023-10-01', '2023-10-31', 22.0, 17000000.00, 0.00, 0.00, 0.00, 17000000.00, 0.00, 0.00, 14960000.00, NULL, 'paid', '2025-04-30 11:57:25', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `performances`
--

CREATE TABLE `performances` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `reviewer_user_id` int(11) NOT NULL COMMENT 'User performing the review',
  `review_period_start` date NOT NULL,
  `review_period_end` date NOT NULL,
  `review_date` date NOT NULL,
  `performance_score` decimal(4,2) DEFAULT NULL,
  `strengths` text DEFAULT NULL,
  `areas_for_improvement` text DEFAULT NULL,
  `employee_comments` text DEFAULT NULL,
  `reviewer_comments` text DEFAULT NULL,
  `goals_for_next_period` text DEFAULT NULL,
  `status` enum('draft','submitted','acknowledged','completed') DEFAULT 'draft',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `performances`
--

INSERT INTO `performances` (`id`, `employee_id`, `reviewer_user_id`, `review_period_start`, `review_period_end`, `review_date`, `performance_score`, `strengths`, `areas_for_improvement`, `employee_comments`, `reviewer_comments`, `goals_for_next_period`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 2, '2023-01-01', '2023-12-31', '2023-12-15', 4.50, NULL, NULL, NULL, NULL, NULL, 'draft', '2025-04-30 11:57:49', '2025-04-30 11:57:49'),
(2, 2, 1, '2023-01-01', '2023-12-31', '2023-12-15', 4.20, NULL, NULL, NULL, NULL, NULL, 'draft', '2025-04-30 11:57:49', '2025-04-30 11:57:49'),
(3, 3, 1, '2023-01-01', '2023-12-31', '2023-12-15', 4.00, NULL, NULL, NULL, NULL, NULL, 'draft', '2025-04-30 11:57:49', '2025-04-30 11:57:49'),
(4, 4, 2, '2023-01-01', '2023-12-31', '2023-12-15', 3.80, NULL, NULL, NULL, NULL, NULL, 'draft', '2025-04-30 11:57:49', '2025-04-30 11:57:49'),
(5, 5, 2, '2023-01-01', '2023-12-31', '2023-12-15', 4.10, NULL, NULL, NULL, NULL, NULL, 'draft', '2025-04-30 11:57:49', '2025-04-30 11:57:49'),
(6, 6, 3, '2023-01-01', '2023-12-31', '2023-12-15', 3.90, NULL, NULL, NULL, NULL, NULL, 'draft', '2025-04-30 11:57:49', '2025-04-30 11:57:49'),
(7, 7, 3, '2023-01-01', '2023-12-31', '2023-12-15', 4.30, NULL, NULL, NULL, NULL, NULL, 'draft', '2025-04-30 11:57:49', '2025-04-30 11:57:49'),
(8, 8, 1, '2023-01-01', '2023-12-31', '2023-12-15', 4.00, NULL, NULL, NULL, NULL, NULL, 'draft', '2025-04-30 11:57:49', '2025-04-30 11:57:49'),
(9, 9, 2, '2023-01-01', '2023-12-31', '2023-12-15', 3.70, NULL, NULL, NULL, NULL, NULL, 'draft', '2025-04-30 11:57:49', '2025-04-30 11:57:49'),
(10, 10, 3, '2023-01-01', '2023-12-31', '2023-12-15', 4.20, NULL, NULL, NULL, NULL, NULL, 'draft', '2025-04-30 11:57:49', '2025-04-30 11:57:49');

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `code` varchar(50) NOT NULL COMMENT 'Unique code for permission checks',
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `code`, `description`, `created_at`) VALUES
(1, 'Xem nhân viên', 'view_employees', 'Xem thông tin nhân viên', '2025-04-30 04:55:23'),
(2, 'Thêm nhân viên', 'add_employees', 'Thêm nhân viên mới', '2025-04-30 04:55:23'),
(3, 'Sửa nhân viên', 'edit_employees', 'Cập nhật thông tin nhân viên', '2025-04-30 04:55:23'),
(4, 'Xóa nhân viên', 'delete_employees', 'Xóa nhân viên', '2025-04-30 04:55:23'),
(5, 'Duyệt nghỉ phép', 'approve_leaves', 'Phê duyệt đơn nghỉ phép', '2025-04-30 04:55:23'),
(6, 'Quản lý lương', 'manage_payroll', 'Tính và quản lý lương', '2025-04-30 04:55:23'),
(7, 'Quản lý tài sản', 'manage_assets', 'Theo dõi và phân bổ tài sản', '2025-04-30 04:55:23'),
(8, 'Tạo báo cáo', 'generate_reports', 'Xuất báo cáo hệ thống', '2025-04-30 04:55:23'),
(9, 'Quản lý đào tạo', 'manage_training', 'Tổ chức khóa đào tạo', '2025-04-30 04:55:23'),
(10, 'Cấu hình hệ thống', 'configure_system', 'Thay đổi cài đặt hệ thống', '2025-04-30 04:55:23');

-- --------------------------------------------------------

--
-- Table structure for table `policies`
--

CREATE TABLE `policies` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(50) NOT NULL,
  `effective_date` date NOT NULL,
  `status` varchar(20) NOT NULL COMMENT 'e.g., draft, active, archived',
  `file_url` varchar(512) DEFAULT NULL COMMENT 'Optional link to policy document',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `policies`
--

INSERT INTO `policies` (`id`, `title`, `description`, `category`, `effective_date`, `status`, `file_url`, `created_at`, `updated_at`) VALUES
(1, 'Chính sách nghỉ phép', 'Quy định về nghỉ phép năm', 'HR', '2023-01-01', 'active', 'http://example.com/leave_policy.pdf', '2025-04-30 04:56:30', '2025-04-30 04:56:30'),
(2, 'Chính sách bảo mật', 'Quy định bảo mật thông tin', 'IT', '2023-01-01', 'active', 'http://example.com/security_policy.pdf', '2025-04-30 04:56:30', '2025-04-30 04:56:30'),
(3, 'Chính sách chống quấy rối', 'Quy định ứng xử tại nơi làm việc', 'HR', '2023-01-01', 'active', 'http://example.com/harassment_policy.pdf', '2025-04-30 04:56:30', '2025-04-30 04:56:30'),
(4, 'Chính sách tăng lương', 'Nguyên tắc tăng lương hàng năm', 'Finance', '2023-01-01', 'active', 'http://example.com/salary_policy.pdf', '2025-04-30 04:56:30', '2025-04-30 04:56:30'),
(5, 'Chính sách mua sắm', 'Quy trình mua sắm nội bộ', 'Finance', '2023-01-01', 'active', 'http://example.com/procurement_policy.pdf', '2025-04-30 04:56:30', '2025-04-30 04:56:30'),
(6, 'Chính sách đào tạo', 'Hỗ trợ đào tạo nhân viên', 'HR', '2023-01-01', 'active', 'http://example.com/training_policy.pdf', '2025-04-30 04:56:30', '2025-04-30 04:56:30'),
(7, 'Chính sách làm việc từ xa', 'Quy định WFH', 'HR', '2023-01-01', 'active', 'http://example.com/wfh_policy.pdf', '2025-04-30 04:56:30', '2025-04-30 04:56:30'),
(8, 'Chính sách sử dụng tài sản', 'Quản lý thiết bị công ty', 'Admin', '2023-01-01', 'active', 'http://example.com/assets_policy.pdf', '2025-04-30 04:56:30', '2025-04-30 04:56:30'),
(9, 'Chính sách an toàn lao động', 'Quy định PCCC', 'Legal', '2023-01-01', 'active', 'http://example.com/safety_policy.pdf', '2025-04-30 04:56:30', '2025-04-30 04:56:30'),
(10, 'Chính sách môi trường', 'Giảm thiểu rác thải', 'Admin', '2023-01-01', 'active', 'http://example.com/environment_policy.pdf', '2025-04-30 04:56:30', '2025-04-30 04:56:30');

-- --------------------------------------------------------

--
-- Table structure for table `positions`
--

CREATE TABLE `positions` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `department_id` int(11) NOT NULL,
  `salary_grade` varchar(50) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `positions`
--

INSERT INTO `positions` (`id`, `name`, `description`, `department_id`, `salary_grade`, `created_at`, `updated_at`) VALUES
(1, 'IT Manager', NULL, 1, 'M1', '2025-04-30 11:53:26', '2025-04-30 11:53:26'),
(2, 'Senior Developer', NULL, 1, 'E4', '2025-04-30 11:53:26', '2025-04-30 11:53:26'),
(3, 'HR Manager', NULL, 2, 'M1', '2025-04-30 11:53:26', '2025-04-30 11:53:26'),
(4, 'Recruiter', NULL, 2, 'E2', '2025-04-30 11:53:26', '2025-04-30 11:53:26'),
(5, 'Finance Manager', NULL, 3, 'M1', '2025-04-30 11:53:26', '2025-04-30 11:53:26'),
(6, 'Accountant', NULL, 3, 'E3', '2025-04-30 11:53:26', '2025-04-30 11:53:26'),
(7, 'Marketing Director', NULL, 4, 'M2', '2025-04-30 11:53:26', '2025-04-30 11:53:26'),
(8, 'Sales Executive', NULL, 5, 'E2', '2025-04-30 11:53:26', '2025-04-30 11:53:26'),
(9, 'Operations Lead', NULL, 6, 'M1', '2025-04-30 11:53:26', '2025-04-30 11:53:26'),
(10, 'Legal Advisor', NULL, 7, 'E4', '2025-04-30 11:53:26', '2025-04-30 11:53:26');

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `project_code` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('planning','active','completed','on_hold','cancelled') NOT NULL DEFAULT 'planning',
  `manager_employee_id` int(11) DEFAULT NULL,
  `budget` decimal(15,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`id`, `name`, `project_code`, `description`, `start_date`, `end_date`, `status`, `manager_employee_id`, `budget`, `created_at`, `updated_at`) VALUES
(1, 'Dự án CRM', NULL, NULL, '2023-01-01', '2023-12-31', 'active', 2, NULL, '2025-04-30 04:58:48', '2025-04-30 04:58:48'),
(2, 'Nâng cấp hệ thống', NULL, NULL, '2023-02-01', '2023-11-30', 'active', 2, NULL, '2025-04-30 04:58:48', '2025-04-30 04:58:48'),
(3, 'Triển khai ERP', NULL, NULL, '2023-03-01', '2024-03-01', 'planning', 2, NULL, '2025-04-30 04:58:48', '2025-04-30 04:58:48'),
(4, 'Phát triển App Mobile', NULL, NULL, '2023-04-01', '2023-10-31', 'completed', 4, NULL, '2025-04-30 04:58:48', '2025-04-30 04:58:48'),
(5, 'Chiến dịch Marketing Q4', NULL, NULL, '2023-09-01', '2023-12-31', 'active', 8, NULL, '2025-04-30 04:58:48', '2025-04-30 04:58:48'),
(6, 'Cải tiến quy trình', NULL, NULL, '2023-05-01', '2023-08-31', 'completed', 6, NULL, '2025-04-30 04:58:48', '2025-04-30 04:58:48'),
(7, 'Đào tạo nội bộ', NULL, NULL, '2023-06-01', '2023-12-31', 'active', 3, NULL, '2025-04-30 04:58:48', '2025-04-30 04:58:48'),
(8, 'Nghiên cứu AI', NULL, NULL, '2023-07-01', '2024-07-01', 'planning', 2, NULL, '2025-04-30 04:58:48', '2025-04-30 04:58:48'),
(9, 'Tối ưu hóa Database', NULL, NULL, '2023-08-01', '2023-09-30', 'completed', 4, NULL, '2025-04-30 04:58:48', '2025-04-30 04:58:48'),
(10, 'Xây dựng Website', NULL, NULL, '2023-10-01', '2023-12-31', 'active', 5, NULL, '2025-04-30 04:58:48', '2025-04-30 04:58:48');

-- --------------------------------------------------------

--
-- Table structure for table `project_resources`
--

CREATE TABLE `project_resources` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `resource_type` enum('employee','asset','other') NOT NULL,
  `resource_id` int(11) NOT NULL COMMENT 'FK to employees.id or assets.id based on type',
  `role` varchar(100) DEFAULT NULL COMMENT 'Role if employee resource',
  `allocation_percentage` int(11) DEFAULT NULL COMMENT 'Percentage of time/resource allocated',
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `project_resources`
--

INSERT INTO `project_resources` (`id`, `project_id`, `resource_type`, `resource_id`, `role`, `allocation_percentage`, `start_date`, `end_date`, `created_at`, `updated_at`) VALUES
(1, 1, 'employee', 4, 'Developer', 80, '0000-00-00', NULL, '2025-04-30 04:59:03', '2025-04-30 04:59:03'),
(2, 1, 'employee', 5, 'Developer', 70, '0000-00-00', NULL, '2025-04-30 04:59:03', '2025-04-30 04:59:03'),
(3, 2, 'employee', 4, 'DevOps', 100, '0000-00-00', NULL, '2025-04-30 04:59:03', '2025-04-30 04:59:03'),
(4, 3, 'employee', 2, 'Project Manager', 50, '0000-00-00', NULL, '2025-04-30 04:59:03', '2025-04-30 04:59:03'),
(5, 4, 'employee', 8, 'Designer', 60, '0000-00-00', NULL, '2025-04-30 04:59:03', '2025-04-30 04:59:03'),
(6, 5, 'employee', 8, 'Marketer', 90, '0000-00-00', NULL, '2025-04-30 04:59:03', '2025-04-30 04:59:03'),
(7, 6, 'employee', 6, 'Analyst', 70, '0000-00-00', NULL, '2025-04-30 04:59:03', '2025-04-30 04:59:03'),
(8, 7, 'employee', 3, 'Trainer', 80, '0000-00-00', NULL, '2025-04-30 04:59:03', '2025-04-30 04:59:03'),
(9, 8, 'employee', 4, 'Researcher', 60, '0000-00-00', NULL, '2025-04-30 04:59:03', '2025-04-30 04:59:03'),
(10, 9, 'employee', 4, 'DBA', 75, '0000-00-00', NULL, '2025-04-30 04:59:03', '2025-04-30 04:59:03');

-- --------------------------------------------------------

--
-- Table structure for table `project_tasks`
--

CREATE TABLE `project_tasks` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `assigned_to_employee_id` int(11) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `estimated_hours` decimal(5,2) DEFAULT NULL,
  `actual_hours` decimal(5,2) DEFAULT NULL,
  `priority` enum('low','medium','high','critical') DEFAULT 'medium',
  `status` enum('pending','in_progress','completed','blocked','cancelled') NOT NULL DEFAULT 'pending',
  `parent_task_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `project_tasks`
--

INSERT INTO `project_tasks` (`id`, `project_id`, `title`, `description`, `assigned_to_employee_id`, `start_date`, `due_date`, `estimated_hours`, `actual_hours`, `priority`, `status`, `parent_task_id`, `created_at`, `updated_at`) VALUES
(1, 1, 'Thiết kế database', NULL, 4, NULL, '2023-02-01', NULL, NULL, 'medium', 'completed', NULL, '2025-04-30 04:58:56', '2025-04-30 04:58:56'),
(2, 1, 'Phát triển API', NULL, 5, NULL, '2023-03-01', NULL, NULL, 'medium', 'completed', NULL, '2025-04-30 04:58:56', '2025-04-30 04:58:56'),
(3, 2, 'Nâng cấp server', NULL, 4, NULL, '2023-04-01', NULL, NULL, 'medium', 'in_progress', NULL, '2025-04-30 04:58:56', '2025-04-30 04:58:56'),
(4, 3, 'Lập kế hoạch ERP', NULL, 2, NULL, '2023-05-01', NULL, NULL, 'medium', 'pending', NULL, '2025-04-30 04:58:56', '2025-04-30 04:58:56'),
(5, 4, 'Thiết kế UI/UX', NULL, 8, NULL, '2023-06-01', NULL, NULL, 'medium', 'completed', NULL, '2025-04-30 04:58:56', '2025-04-30 04:58:56'),
(6, 5, 'Chạy quảng cáo', NULL, 8, NULL, '2023-07-01', NULL, NULL, 'medium', 'in_progress', NULL, '2025-04-30 04:58:56', '2025-04-30 04:58:56'),
(7, 6, 'Phân tích quy trình', NULL, 6, NULL, '2023-08-01', NULL, NULL, 'medium', 'completed', NULL, '2025-04-30 04:58:56', '2025-04-30 04:58:56'),
(8, 7, 'Tổ chức training', NULL, 3, NULL, '2023-09-01', NULL, NULL, 'medium', 'in_progress', NULL, '2025-04-30 04:58:56', '2025-04-30 04:58:56'),
(9, 8, 'Nghiên cứu AI', NULL, 4, NULL, '2023-10-01', NULL, NULL, 'medium', 'pending', NULL, '2025-04-30 04:58:56', '2025-04-30 04:58:56'),
(10, 9, 'Tối ưu query', NULL, 4, NULL, '2023-11-01', NULL, NULL, 'medium', 'completed', NULL, '2025-04-30 04:58:56', '2025-04-30 04:58:56');

-- --------------------------------------------------------

--
-- Table structure for table `rate_limits`
--

CREATE TABLE `rate_limits` (
  `id` int(11) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `endpoint` varchar(255) NOT NULL,
  `request_count` int(11) NOT NULL DEFAULT 1,
  `window_start` datetime NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rate_limits`
--

INSERT INTO `rate_limits` (`id`, `ip_address`, `endpoint`, `request_count`, `window_start`, `created_at`) VALUES
(1, '192.168.1.100', '/api/login', 3, '2023-10-01 09:00:00', '2025-04-30 12:00:23'),
(2, '192.168.1.101', '/api/users', 5, '2023-10-01 10:00:00', '2025-04-30 12:00:23'),
(3, '192.168.1.102', '/api/employees', 2, '2023-10-01 11:00:00', '2025-04-30 12:00:23'),
(4, '192.168.1.103', '/api/payroll', 4, '2023-10-01 12:00:00', '2025-04-30 12:00:23'),
(5, '192.168.1.104', '/api/documents', 1, '2023-10-01 13:00:00', '2025-04-30 12:00:23'),
(6, '192.168.1.105', '/api/assets', 6, '2023-10-01 14:00:00', '2025-04-30 12:00:23'),
(7, '192.168.1.106', '/api/leaves', 3, '2023-10-01 15:00:00', '2025-04-30 12:00:23'),
(8, '192.168.1.107', '/api/projects', 2, '2023-10-01 16:00:00', '2025-04-30 12:00:23'),
(9, '192.168.1.108', '/api/training', 4, '2023-10-01 17:00:00', '2025-04-30 12:00:23'),
(10, '192.168.1.109', '/api/settings', 5, '2023-10-01 18:00:00', '2025-04-30 12:00:23');

-- --------------------------------------------------------

--
-- Table structure for table `recruitment_campaigns`
--

CREATE TABLE `recruitment_campaigns` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('draft','active','closed','cancelled') NOT NULL DEFAULT 'draft',
  `created_by_user_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `recruitment_campaigns`
--

INSERT INTO `recruitment_campaigns` (`id`, `title`, `description`, `start_date`, `end_date`, `status`, `created_by_user_id`, `created_at`, `updated_at`) VALUES
(1, 'Tuyển dụng IT 2023', NULL, '2023-01-01', '2023-12-31', 'closed', 3, '2025-04-30 11:58:11', '2025-04-30 11:58:11'),
(2, 'Tuyển dụng HR Q4', NULL, '2023-10-01', '2023-12-31', 'active', 3, '2025-04-30 11:58:11', '2025-04-30 11:58:11'),
(3, 'Tuyển dụng Sales 2023', NULL, '2023-06-01', '2023-09-30', 'closed', 3, '2025-04-30 11:58:11', '2025-04-30 11:58:11'),
(4, 'Tuyển dụng Marketing', NULL, '2023-07-01', '2023-08-31', 'closed', 3, '2025-04-30 11:58:11', '2025-04-30 11:58:11'),
(5, 'Tuyển dụng Kế toán', NULL, '2023-09-01', '2023-11-30', 'active', 3, '2025-04-30 11:58:11', '2025-04-30 11:58:11'),
(6, 'Tuyển dụng Intern', NULL, '2023-05-01', '2023-05-31', 'closed', 3, '2025-04-30 11:58:11', '2025-04-30 11:58:11'),
(7, 'Tuyển dụng R&D', NULL, '2023-04-01', '2023-06-30', 'closed', 3, '2025-04-30 11:58:11', '2025-04-30 11:58:11'),
(8, 'Tuyển dụng Customer Service', NULL, '2023-08-01', '2023-10-31', 'active', 3, '2025-04-30 11:58:11', '2025-04-30 11:58:11'),
(9, 'Tuyển dụng Legal', NULL, '2023-03-01', '2023-04-30', 'closed', 3, '2025-04-30 11:58:11', '2025-04-30 11:58:11'),
(10, 'Tuyển dụng Logistics', NULL, '2023-02-01', '2023-03-31', 'closed', 3, '2025-04-30 11:58:11', '2025-04-30 11:58:11');

-- --------------------------------------------------------

--
-- Table structure for table `report_executions`
--

CREATE TABLE `report_executions` (
  `id` bigint(20) NOT NULL,
  `template_id` int(11) NOT NULL,
  `schedule_id` int(11) DEFAULT NULL COMMENT 'Null if run manually',
  `parameters_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Parameters used for this execution' CHECK (json_valid(`parameters_json`)),
  `status` enum('pending','running','completed','failed','cancelled') NOT NULL DEFAULT 'pending',
  `start_time` datetime DEFAULT NULL,
  `end_time` datetime DEFAULT NULL,
  `result_url` varchar(512) DEFAULT NULL COMMENT 'URL to the generated report file',
  `result_metadata_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'e.g., row count, file size' CHECK (json_valid(`result_metadata_json`)),
  `error_message` text DEFAULT NULL,
  `executed_by_user_id` int(11) DEFAULT NULL COMMENT 'User who triggered manual run',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `report_schedules`
--

CREATE TABLE `report_schedules` (
  `id` int(11) NOT NULL,
  `template_id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `schedule_type` enum('daily','weekly','monthly','quarterly','yearly','on_demand') NOT NULL,
  `schedule_time` time DEFAULT NULL COMMENT 'Time of day for scheduled runs',
  `schedule_day_of_week` tinyint(1) DEFAULT NULL COMMENT '1=Sun, 7=Sat (for weekly)',
  `schedule_day_of_month` tinyint(2) DEFAULT NULL COMMENT '1-31 (for monthly)',
  `recipients_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'JSON array of email addresses or user IDs' CHECK (json_valid(`recipients_json`)),
  `parameters_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'JSON of parameters to run with' CHECK (json_valid(`parameters_json`)),
  `status` enum('active','inactive','error') NOT NULL DEFAULT 'active',
  `last_run_at` datetime DEFAULT NULL,
  `next_run_at` datetime DEFAULT NULL,
  `created_by_user_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `report_templates`
--

CREATE TABLE `report_templates` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `template_type` varchar(50) NOT NULL COMMENT 'e.g., SQL, Predefined',
  `query_or_definition` text NOT NULL COMMENT 'SQL query or definition for predefined reports',
  `parameters` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'JSON defining required parameters' CHECK (json_valid(`parameters`)),
  `output_format` enum('csv','pdf','html','json') DEFAULT 'csv',
  `created_by_user_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `description`, `created_at`) VALUES
(1, 'Admin', 'Quản trị hệ thống', '2025-04-30 04:53:07'),
(2, 'Manager', 'Quản lý phòng ban', '2025-04-30 04:53:07'),
(3, 'HR', 'Nhân sự', '2025-04-30 04:53:07'),
(4, 'Employee', 'Nhân viên', '2025-04-30 04:53:07'),
(5, 'Auditor', 'Kiểm toán nội bộ', '2025-04-30 04:53:07'),
(6, 'Developer', 'Lập trình viên', '2025-04-30 04:53:07'),
(7, 'Accountant', 'Kế toán', '2025-04-30 04:53:07'),
(8, 'Designer', 'Thiết kế đồ họa', '2025-04-30 04:53:07'),
(9, 'Support', 'Hỗ trợ khách hàng', '2025-04-30 04:53:07'),
(10, 'Intern', 'Thực tập sinh', '2025-04-30 04:53:07');

-- --------------------------------------------------------

--
-- Table structure for table `role_permissions`
--

CREATE TABLE `role_permissions` (
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_permissions`
--

INSERT INTO `role_permissions` (`role_id`, `permission_id`, `created_at`) VALUES
(1, 1, '2025-04-30 04:55:32'),
(1, 2, '2025-04-30 04:55:32'),
(1, 3, '2025-04-30 04:55:32'),
(1, 4, '2025-04-30 04:55:32'),
(1, 5, '2025-04-30 04:55:32'),
(1, 6, '2025-04-30 04:55:32'),
(1, 7, '2025-04-30 04:55:32'),
(1, 8, '2025-04-30 04:55:32'),
(1, 9, '2025-04-30 04:55:32'),
(1, 10, '2025-04-30 04:55:32'),
(2, 1, '2025-04-30 04:55:32'),
(2, 5, '2025-04-30 04:55:32'),
(2, 6, '2025-04-30 04:55:32'),
(2, 8, '2025-04-30 04:55:32'),
(3, 1, '2025-04-30 04:55:32'),
(3, 2, '2025-04-30 04:55:32'),
(3, 5, '2025-04-30 04:55:32'),
(3, 9, '2025-04-30 04:55:32'),
(4, 1, '2025-04-30 04:55:32');

-- --------------------------------------------------------

--
-- Table structure for table `salary_history`
--

CREATE TABLE `salary_history` (
  `salary_history_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `effective_date` date NOT NULL,
  `previous_salary` decimal(15,2) DEFAULT NULL,
  `new_salary` decimal(15,2) NOT NULL,
  `salary_currency` varchar(3) NOT NULL DEFAULT 'VND',
  `reason` text DEFAULT NULL COMMENT 'Reason for change (e.g., Promotion, Annual Review)',
  `decision_attachment_url` varchar(512) DEFAULT NULL,
  `recorded_by_user_id` int(11) DEFAULT NULL COMMENT 'User who recorded the change',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `salary_history`
--

INSERT INTO `salary_history` (`salary_history_id`, `employee_id`, `effective_date`, `previous_salary`, `new_salary`, `salary_currency`, `reason`, `decision_attachment_url`, `recorded_by_user_id`, `created_at`) VALUES
(1, 1, '2021-01-01', 25000000.00, 30000000.00, 'VND', 'Thăng chức', NULL, NULL, '2025-04-30 11:54:16'),
(2, 2, '2021-02-01', 20000000.00, 25000000.00, 'VND', 'Tăng lương định kỳ', NULL, NULL, '2025-04-30 11:54:16'),
(3, 3, '2021-03-01', 18000000.00, 22000000.00, 'VND', 'Hoàn thành dự án', NULL, NULL, '2025-04-30 11:54:16'),
(4, 4, '2021-04-01', 12000000.00, 15000000.00, 'VND', 'Ký hợp đồng mới', NULL, NULL, '2025-04-30 11:54:16'),
(5, 5, '2021-05-01', 12000000.00, 15000000.00, 'VND', 'Ký hợp đồng mới', NULL, NULL, '2025-04-30 11:54:16'),
(6, 6, '2021-06-01', NULL, 5000000.00, 'VND', 'Bắt đầu thực tập', NULL, NULL, '2025-04-30 11:54:16'),
(7, 7, '2021-07-01', 15000000.00, 18000000.00, 'VND', 'Tăng lương', NULL, NULL, '2025-04-30 11:54:16'),
(8, 8, '2021-08-01', 18000000.00, 20000000.00, 'VND', 'Thăng chức', NULL, NULL, '2025-04-30 11:54:16'),
(9, 9, '2021-09-01', 14000000.00, 16000000.00, 'VND', 'Điều chỉnh lương', NULL, NULL, '2025-04-30 11:54:16'),
(10, 10, '2021-10-01', 15000000.00, 17000000.00, 'VND', 'Tăng lương', NULL, NULL, '2025-04-30 11:54:16');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `session_id` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `expires` int(11) UNSIGNED NOT NULL,
  `data` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `system_logs`
--

CREATE TABLE `system_logs` (
  `id` bigint(20) NOT NULL,
  `log_type` varchar(50) NOT NULL COMMENT 'e.g., Application, Database, Security',
  `log_level` enum('debug','info','notice','warning','error','critical','alert','emergency') NOT NULL,
  `message` text NOT NULL,
  `context` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Additional context as JSON' CHECK (json_valid(`context`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL COMMENT 'User associated with the event, if applicable',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_logs`
--

INSERT INTO `system_logs` (`id`, `log_type`, `log_level`, `message`, `context`, `ip_address`, `user_agent`, `user_id`, `created_at`) VALUES
(1, 'Application', 'info', 'Khởi động hệ thống', NULL, NULL, NULL, NULL, '2025-04-30 12:00:07'),
(2, 'Database', 'warning', 'Truy vấn chậm', NULL, NULL, NULL, NULL, '2025-04-30 12:00:07'),
(3, 'Security', 'error', 'Đăng nhập thất bại', NULL, NULL, NULL, 1, '2025-04-30 12:00:07'),
(4, 'Application', 'info', 'Cập nhật phiên bản mới', NULL, NULL, NULL, NULL, '2025-04-30 12:00:07'),
(5, 'Database', 'error', 'Lỗi kết nối', NULL, NULL, NULL, NULL, '2025-04-30 12:00:07'),
(6, 'Security', 'warning', 'Truy cập trái phép', NULL, NULL, NULL, 2, '2025-04-30 12:00:07'),
(7, 'Application', 'debug', 'Kiểm tra API', NULL, NULL, NULL, 3, '2025-04-30 12:00:07'),
(8, 'Database', 'info', 'Sao lưu thành công', NULL, NULL, NULL, NULL, '2025-04-30 12:00:07'),
(9, 'Security', 'info', 'Đổi mật khẩu thành công', NULL, NULL, NULL, 4, '2025-04-30 12:00:07'),
(10, 'Application', 'error', 'Lỗi xử lý đơn nghỉ phép', NULL, NULL, NULL, 5, '2025-04-30 12:00:07');

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text NOT NULL,
  `setting_type` enum('string','integer','boolean','json','array') NOT NULL DEFAULT 'string',
  `description` text DEFAULT NULL,
  `is_public` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1 if readable by non-admins (e.g., site name)',
  `created_by_user_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `description`, `is_public`, `created_by_user_id`, `created_at`, `updated_at`) VALUES
(1, 'company_name', 'Công ty ABC', 'string', 'Tên công ty', 1, NULL, '2025-04-30 11:55:53', '2025-04-30 11:55:53'),
(2, 'default_currency', 'VND', 'string', 'Đơn vị tiền tệ mặc định', 1, NULL, '2025-04-30 11:55:53', '2025-04-30 11:55:53'),
(3, 'max_login_attempts', '5', 'integer', 'Số lần đăng nhập sai tối đa', 0, NULL, '2025-04-30 11:55:53', '2025-04-30 11:55:53'),
(4, 'password_expiry_days', '90', 'integer', 'Thời hạn đổi mật khẩu (ngày)', 0, NULL, '2025-04-30 11:55:53', '2025-04-30 11:55:53'),
(5, 'smtp_host', 'smtp.company.com', 'string', 'Máy chủ SMTP', 0, NULL, '2025-04-30 11:55:53', '2025-04-30 11:55:53'),
(6, 'smtp_port', '587', 'integer', 'Cổng SMTP', 0, NULL, '2025-04-30 11:55:53', '2025-04-30 11:55:53'),
(7, 'annual_leave_days', '12', 'integer', 'Số ngày nghỉ phép năm', 1, NULL, '2025-04-30 11:55:53', '2025-04-30 11:55:53'),
(8, 'tax_rate', '0.1', '', 'Thuế thu nhập cá nhân', 1, NULL, '2025-04-30 11:55:53', '2025-04-30 11:55:53'),
(9, 'overtime_rate', '1.5', '', 'Hệ số tăng ca', 1, NULL, '2025-04-30 11:55:53', '2025-04-30 11:55:53'),
(10, 'theme_color', 'blue', 'string', 'Màu sắc giao diện', 1, NULL, '2025-04-30 11:55:53', '2025-04-30 11:55:53');

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `assigned_to_employee_id` int(11) DEFAULT NULL,
  `assigned_by_user_id` int(11) NOT NULL,
  `due_date` date DEFAULT NULL,
  `priority` enum('low','medium','high','critical') DEFAULT 'medium',
  `status` enum('pending','in_progress','completed','blocked','cancelled') DEFAULT 'pending',
  `related_entity_type` varchar(50) DEFAULT NULL COMMENT 'e.g., employee, onboarding, performance',
  `related_entity_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tasks`
--

INSERT INTO `tasks` (`id`, `title`, `description`, `assigned_to_employee_id`, `assigned_by_user_id`, `due_date`, `priority`, `status`, `related_entity_type`, `related_entity_id`, `created_at`, `updated_at`) VALUES
(1, 'Kiểm tra hợp đồng', NULL, 3, 1, '2023-10-05', 'medium', 'completed', NULL, NULL, '2025-04-30 11:59:10', '2025-04-30 11:59:10'),
(2, 'Cập nhật thông tin nhân viên', NULL, 3, 1, '2023-10-06', 'medium', 'in_progress', NULL, NULL, '2025-04-30 11:59:10', '2025-04-30 11:59:10'),
(3, 'Duyệt đơn nghỉ phép', NULL, 3, 1, '2023-10-07', 'medium', 'pending', NULL, NULL, '2025-04-30 11:59:10', '2025-04-30 11:59:10'),
(4, 'Bảo trì server', NULL, 4, 2, '2023-10-08', 'medium', 'completed', NULL, NULL, '2025-04-30 11:59:10', '2025-04-30 11:59:10'),
(5, 'Thiết kế poster', NULL, 8, 8, '2023-10-09', 'medium', 'in_progress', NULL, NULL, '2025-04-30 11:59:10', '2025-04-30 11:59:10'),
(6, 'Chuẩn bị báo cáo tài chính', NULL, 7, 7, '2023-10-10', 'medium', 'pending', NULL, NULL, '2025-04-30 11:59:10', '2025-04-30 11:59:10'),
(7, 'Tuyển dụng nhân sự', NULL, 3, 3, '2023-10-11', 'medium', 'completed', NULL, NULL, '2025-04-30 11:59:10', '2025-04-30 11:59:10'),
(8, 'Đào tạo nhân viên mới', NULL, 3, 3, '2023-10-12', 'medium', 'in_progress', NULL, NULL, '2025-04-30 11:59:10', '2025-04-30 11:59:10'),
(9, 'Kiểm kho thiết bị', NULL, 4, 2, '2023-10-13', 'medium', 'pending', NULL, NULL, '2025-04-30 11:59:10', '2025-04-30 11:59:10'),
(10, 'Gửi thông báo lương', NULL, 1, 1, '2023-10-14', 'medium', 'completed', NULL, NULL, '2025-04-30 11:59:10', '2025-04-30 11:59:10');

-- --------------------------------------------------------

--
-- Table structure for table `training_courses`
--

CREATE TABLE `training_courses` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `duration` int(11) DEFAULT NULL COMMENT 'e.g., in hours or days',
  `cost` decimal(10,2) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'active' COMMENT 'e.g., active, inactive, draft',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `training_courses`
--

INSERT INTO `training_courses` (`id`, `name`, `description`, `duration`, `cost`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Qu?n lý d? án Agile', 'Khóa h?c Agile c? b?n', 16, 5000000.00, 'active', '2025-04-30 04:54:50', '2025-04-30 04:54:50'),
(2, 'L?p trình Python', 'Khóa h?c Python nâng cao', 24, 7000000.00, 'active', '2025-04-30 04:54:50', '2025-04-30 04:54:50'),
(3, 'K? n?ng giao ti?p', '?ào t?o k? n?ng m?m', 8, 3000000.00, 'active', '2025-04-30 04:54:50', '2025-04-30 04:54:50'),
(4, 'Qu?n lý tài chính', 'Khóa h?c cho qu?n lý', 12, 6000000.00, 'active', '2025-04-30 04:54:50', '2025-04-30 04:54:50'),
(5, 'Marketing Digital', 'Chi?n l??c ti?p th? s?', 20, 8000000.00, 'active', '2025-04-30 04:54:50', '2025-04-30 04:54:50'),
(6, 'An toàn thông tin', 'B?o m?t h? th?ng', 10, 4000000.00, 'active', '2025-04-30 04:54:50', '2025-04-30 04:54:50'),
(7, 'Excel chuyên nghi?p', 'K? n?ng Excel nâng cao', 8, 2000000.00, 'active', '2025-04-30 04:54:50', '2025-04-30 04:54:50'),
(8, 'Thi?t k? UI/UX', 'Nguyên t?c thi?t k?', 15, 5500000.00, 'active', '2025-04-30 04:54:50', '2025-04-30 04:54:50'),
(9, 'Qu?n tr? c? s? d? li?u', 'SQL và NoSQL', 18, 6500000.00, 'active', '2025-04-30 04:54:50', '2025-04-30 04:54:50'),
(10, 'K? n?ng lãnh ??o', 'Dành cho qu?n lý', 10, 4500000.00, 'active', '2025-04-30 04:54:50', '2025-04-30 04:54:50');

-- --------------------------------------------------------

--
-- Table structure for table `training_evaluations`
--

CREATE TABLE `training_evaluations` (
  `id` int(11) NOT NULL,
  `registration_id` int(11) NOT NULL COMMENT 'Link to the specific registration being evaluated',
  `evaluator_employee_id` int(11) NOT NULL COMMENT 'Employee providing the evaluation',
  `evaluation_date` date NOT NULL,
  `rating_content` int(11) DEFAULT NULL COMMENT 'Scale (e.g., 1-5)',
  `rating_instructor` int(11) DEFAULT NULL COMMENT 'Scale (e.g., 1-5)',
  `rating_materials` int(11) DEFAULT NULL COMMENT 'Scale (e.g., 1-5)',
  `comments` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `training_evaluations`
--

INSERT INTO `training_evaluations` (`id`, `registration_id`, `evaluator_employee_id`, `evaluation_date`, `rating_content`, `rating_instructor`, `rating_materials`, `comments`, `created_at`, `updated_at`) VALUES
(1, 1, 2, '2023-10-01', 5, 4, NULL, NULL, '2025-04-30 04:58:03', '2025-04-30 04:58:03'),
(2, 2, 1, '2023-10-01', 4, 4, NULL, NULL, '2025-04-30 04:58:03', '2025-04-30 04:58:03'),
(3, 3, 3, '2023-10-01', 3, 5, NULL, NULL, '2025-04-30 04:58:03', '2025-04-30 04:58:03'),
(4, 4, 2, '2023-10-01', 5, 5, NULL, NULL, '2025-04-30 04:58:03', '2025-04-30 04:58:03'),
(5, 5, 1, '2023-10-01', 4, 3, NULL, NULL, '2025-04-30 04:58:03', '2025-04-30 04:58:03'),
(6, 6, 3, '2023-10-01', 4, 4, NULL, NULL, '2025-04-30 04:58:03', '2025-04-30 04:58:03'),
(7, 7, 2, '2023-10-01', 5, 5, NULL, NULL, '2025-04-30 04:58:03', '2025-04-30 04:58:03'),
(8, 8, 1, '2023-10-01', 3, 4, NULL, NULL, '2025-04-30 04:58:03', '2025-04-30 04:58:03'),
(9, 9, 3, '2023-10-01', 4, 4, NULL, NULL, '2025-04-30 04:58:03', '2025-04-30 04:58:03'),
(10, 10, 2, '2023-10-01', 5, 5, NULL, NULL, '2025-04-30 04:58:03', '2025-04-30 04:58:03');

-- --------------------------------------------------------

--
-- Table structure for table `training_registrations`
--

CREATE TABLE `training_registrations` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `registration_date` date NOT NULL,
  `status` enum('registered','attended','completed','failed','cancelled') NOT NULL DEFAULT 'registered',
  `completion_date` date DEFAULT NULL,
  `score` decimal(5,2) DEFAULT NULL,
  `feedback` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `training_registrations`
--

INSERT INTO `training_registrations` (`id`, `employee_id`, `course_id`, `registration_date`, `status`, `completion_date`, `score`, `feedback`, `created_at`, `updated_at`) VALUES
(1, 1, 1, '2023-09-01', 'completed', NULL, NULL, NULL, '2025-04-30 04:55:06', '2025-04-30 04:55:06'),
(2, 2, 2, '2023-09-05', 'attended', NULL, NULL, NULL, '2025-04-30 04:55:06', '2025-04-30 04:55:06'),
(3, 3, 3, '2023-09-10', 'registered', NULL, NULL, NULL, '2025-04-30 04:55:06', '2025-04-30 04:55:06'),
(4, 4, 4, '2023-09-15', 'completed', NULL, NULL, NULL, '2025-04-30 04:55:06', '2025-04-30 04:55:06'),
(5, 5, 5, '2023-09-20', 'attended', NULL, NULL, NULL, '2025-04-30 04:55:06', '2025-04-30 04:55:06'),
(6, 6, 6, '2023-09-25', 'registered', NULL, NULL, NULL, '2025-04-30 04:55:06', '2025-04-30 04:55:06'),
(7, 7, 7, '2023-10-01', 'completed', NULL, NULL, NULL, '2025-04-30 04:55:06', '2025-04-30 04:55:06'),
(8, 8, 8, '2023-10-05', 'attended', NULL, NULL, NULL, '2025-04-30 04:55:06', '2025-04-30 04:55:06'),
(9, 9, 9, '2023-10-10', 'registered', NULL, NULL, NULL, '2025-04-30 04:55:06', '2025-04-30 04:55:06'),
(10, 10, 10, '2023-10-15', 'completed', NULL, NULL, NULL, '2025-04-30 04:55:06', '2025-04-30 04:55:06');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `password_salt` varchar(64) DEFAULT NULL,
  `role_id` int(11) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1 COMMENT '0=Inactive, 1=Active',
  `requires_password_change` tinyint(1) DEFAULT 0 COMMENT '1=Must change password on next login',
  `last_login` datetime DEFAULT NULL,
  `login_attempts` int(11) DEFAULT 0,
  `last_attempt` datetime DEFAULT NULL,
  `remember_token` varchar(64) DEFAULT NULL,
  `remember_token_expiry` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `password_hash`, `password_salt`, `role_id`, `is_active`, `requires_password_change`, `last_login`, `login_attempts`, `last_attempt`, `remember_token`, `remember_token_expiry`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@company.com', 'hashed_password', NULL, 1, 1, 0, NULL, 0, NULL, NULL, NULL, '2025-04-30 11:53:34', '2025-04-30 11:53:34'),
(2, 'manager_it', 'manager.it@company.com', 'hashed_password', NULL, 2, 1, 0, NULL, 0, NULL, NULL, NULL, '2025-04-30 11:53:34', '2025-04-30 11:53:34'),
(3, 'hr_manager', 'hr@company.com', 'hashed_password', NULL, 3, 1, 0, NULL, 0, NULL, NULL, NULL, '2025-04-30 11:53:34', '2025-04-30 11:53:34'),
(4, 'employee1', 'employee1@company.com', 'hashed_password', NULL, 4, 1, 0, NULL, 0, NULL, NULL, NULL, '2025-04-30 11:53:34', '2025-04-30 11:53:34'),
(5, 'employee2', 'employee2@company.com', 'hashed_password', NULL, 4, 1, 0, NULL, 0, NULL, NULL, NULL, '2025-04-30 11:53:34', '2025-04-30 11:53:34'),
(6, 'employee3', 'employee3@company.com', 'hashed_password', NULL, 4, 1, 0, NULL, 0, NULL, NULL, NULL, '2025-04-30 11:53:34', '2025-04-30 11:53:34'),
(7, 'employee4', 'employee4@company.com', 'hashed_password', NULL, 4, 1, 0, NULL, 0, NULL, NULL, NULL, '2025-04-30 11:53:34', '2025-04-30 11:53:34'),
(8, 'employee5', 'employee5@company.com', 'hashed_password', NULL, 4, 1, 0, NULL, 0, NULL, NULL, NULL, '2025-04-30 11:53:34', '2025-04-30 11:53:34'),
(9, 'employee6', 'employee6@company.com', 'hashed_password', NULL, 4, 1, 0, NULL, 0, NULL, NULL, NULL, '2025-04-30 11:53:34', '2025-04-30 11:53:34'),
(10, 'employee7', 'employee7@company.com', 'hashed_password', NULL, 4, 1, 0, NULL, 0, NULL, NULL, NULL, '2025-04-30 11:53:34', '2025-04-30 11:53:34');

-- --------------------------------------------------------

--
-- Table structure for table `user_profiles`
--

CREATE TABLE `user_profiles` (
  `profile_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `avatar_url` varchar(512) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('Male','Female','Other','Prefer not to say') DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `permanent_address` text DEFAULT NULL,
  `current_address` text DEFAULT NULL,
  `emergency_contact_name` varchar(255) DEFAULT NULL,
  `emergency_contact_phone` varchar(20) DEFAULT NULL,
  `bank_account_number` varchar(50) DEFAULT NULL,
  `bank_name` varchar(100) DEFAULT NULL,
  `tax_code` varchar(20) DEFAULT NULL,
  `nationality` varchar(100) DEFAULT NULL,
  `ethnicity` varchar(100) DEFAULT NULL,
  `religion` varchar(100) DEFAULT NULL,
  `marital_status` enum('Single','Married','Divorced','Widowed') DEFAULT NULL,
  `id_card_number` varchar(20) DEFAULT NULL,
  `id_card_issue_date` date DEFAULT NULL,
  `id_card_issue_place` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_profiles`
--

INSERT INTO `user_profiles` (`profile_id`, `user_id`, `full_name`, `avatar_url`, `date_of_birth`, `gender`, `phone_number`, `permanent_address`, `current_address`, `emergency_contact_name`, `emergency_contact_phone`, `bank_account_number`, `bank_name`, `tax_code`, `nationality`, `ethnicity`, `religion`, `marital_status`, `id_card_number`, `id_card_issue_date`, `id_card_issue_place`, `created_at`, `updated_at`) VALUES
(1, 1, 'Nguyễn Văn Admin', NULL, '1985-01-01', 'Male', '0912345678', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-04-30 11:53:53', '2025-04-30 11:53:53'),
(2, 2, 'Trần Quản Lý IT', NULL, '1990-05-15', 'Male', '0912345679', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-04-30 11:53:53', '2025-04-30 11:53:53'),
(3, 3, 'Lê Thị HR', NULL, '1992-08-20', 'Female', '0912345680', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-04-30 11:53:53', '2025-04-30 11:53:53'),
(4, 4, 'Phạm Nhân Viên 1', NULL, '1995-03-10', 'Male', '0912345681', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-04-30 11:53:53', '2025-04-30 11:53:53'),
(5, 5, 'Hoàng Nhân Viên 2', NULL, '1996-07-25', 'Male', '0912345682', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-04-30 11:53:53', '2025-04-30 11:53:53'),
(6, 6, 'Võ Thị Nhân Viên 3', NULL, '1994-11-05', 'Female', '0912345683', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-04-30 11:53:53', '2025-04-30 11:53:53'),
(7, 7, 'Đặng Nhân Viên 4', NULL, '1993-09-12', 'Female', '0912345684', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-04-30 11:53:53', '2025-04-30 11:53:53'),
(8, 8, 'Bùi Kế Toán 1', NULL, '1997-04-18', 'Male', '0912345685', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-04-30 11:53:53', '2025-04-30 11:53:53'),
(9, 9, 'Mai Kế Toán 2', NULL, '1998-02-22', 'Female', '0912345686', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-04-30 11:53:53', '2025-04-30 11:53:53'),
(10, 10, 'Lý Kế Toán 3', NULL, '1999-06-30', 'Female', '0912345687', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-04-30 11:53:53', '2025-04-30 11:53:53');

-- --------------------------------------------------------

--
-- Table structure for table `work_schedules`
--

CREATE TABLE `work_schedules` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `work_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `break_duration_minutes` int(11) DEFAULT 0,
  `schedule_type` enum('normal','overtime','shift','flexible') DEFAULT 'normal',
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `work_schedules`
--

INSERT INTO `work_schedules` (`id`, `employee_id`, `work_date`, `start_time`, `end_time`, `break_duration_minutes`, `schedule_type`, `notes`, `created_at`, `updated_at`) VALUES
(1, 1, '2023-10-01', '08:00:00', '17:00:00', 0, 'normal', NULL, '2025-04-30 11:57:41', '2025-04-30 11:57:41'),
(2, 2, '2023-10-01', '08:00:00', '17:00:00', 0, 'normal', NULL, '2025-04-30 11:57:41', '2025-04-30 11:57:41'),
(3, 3, '2023-10-01', '09:00:00', '18:00:00', 0, 'shift', NULL, '2025-04-30 11:57:41', '2025-04-30 11:57:41'),
(4, 4, '2023-10-01', '08:30:00', '17:30:00', 0, 'flexible', NULL, '2025-04-30 11:57:41', '2025-04-30 11:57:41'),
(5, 5, '2023-10-01', '08:00:00', '17:00:00', 0, 'normal', NULL, '2025-04-30 11:57:41', '2025-04-30 11:57:41'),
(6, 6, '2023-10-01', '10:00:00', '19:00:00', 0, 'overtime', NULL, '2025-04-30 11:57:41', '2025-04-30 11:57:41'),
(7, 7, '2023-10-01', '08:00:00', '17:00:00', 0, 'normal', NULL, '2025-04-30 11:57:41', '2025-04-30 11:57:41'),
(8, 8, '2023-10-01', '08:00:00', '17:00:00', 0, 'normal', NULL, '2025-04-30 11:57:41', '2025-04-30 11:57:41'),
(9, 9, '2023-10-01', '08:00:00', '17:00:00', 0, 'normal', NULL, '2025-04-30 11:57:41', '2025-04-30 11:57:41'),
(10, 10, '2023-10-01', '08:00:00', '17:00:00', 0, 'normal', NULL, '2025-04-30 11:57:41', '2025-04-30 11:57:41');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activities`
--
ALTER TABLE `activities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_activities_user_idx` (`user_id`),
  ADD KEY `idx_activities_type` (`type`),
  ADD KEY `idx_activities_target` (`target_entity`,`target_entity_id`),
  ADD KEY `idx_activities_created_at` (`created_at`);

--
-- Indexes for table `assets`
--
ALTER TABLE `assets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_asset_code` (`asset_code`),
  ADD KEY `idx_asset_status` (`status`),
  ADD KEY `idx_asset_category` (`category`);

--
-- Indexes for table `asset_assignments`
--
ALTER TABLE `asset_assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_assetassign_asset_idx` (`asset_id`),
  ADD KEY `fk_assetassign_employee_idx` (`employee_id`),
  ADD KEY `fk_assetassign_assigner_idx` (`assigned_by_user_id`),
  ADD KEY `fk_assetassign_receiver_idx` (`returned_to_user_id`),
  ADD KEY `idx_assetassign_status` (`status`);

--
-- Indexes for table `asset_maintenance`
--
ALTER TABLE `asset_maintenance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_assetmaint_asset_idx` (`asset_id`),
  ADD KEY `fk_assetmaint_creator_idx` (`created_by_user_id`);

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`attendance_id`),
  ADD UNIQUE KEY `uq_attendance_employee_date` (`employee_id`,`attendance_date`),
  ADD KEY `fk_attendance_employee_idx` (`employee_id`),
  ADD KEY `idx_attendance_date` (`attendance_date`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `idx_audit_user_id` (`user_id`),
  ADD KEY `idx_audit_timestamp` (`timestamp`),
  ADD KEY `idx_audit_action_type` (`action_type`),
  ADD KEY `idx_audit_target` (`target_entity`,`target_entity_id`);

--
-- Indexes for table `backup_logs`
--
ALTER TABLE `backup_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_backuplog_creator_idx` (`created_by_user_id`),
  ADD KEY `idx_backuplog_status` (`status`),
  ADD KEY `idx_backuplog_created_at` (`created_at`);

--
-- Indexes for table `benefits`
--
ALTER TABLE `benefits`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `bonuses`
--
ALTER TABLE `bonuses`
  ADD PRIMARY KEY (`bonus_id`),
  ADD KEY `fk_bonuses_employee_idx` (`employee_id`),
  ADD KEY `fk_bonuses_approver_idx` (`approved_by_user_id`),
  ADD KEY `fk_bonuses_payroll_idx` (`payroll_id`);

--
-- Indexes for table `certificates`
--
ALTER TABLE `certificates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_certificates_employee_idx` (`employee_id`);

--
-- Indexes for table `contracts`
--
ALTER TABLE `contracts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_contracts_employee_idx` (`employee_id`),
  ADD KEY `idx_contract_status` (`status`),
  ADD KEY `idx_contract_end_date` (`end_date`);

--
-- Indexes for table `degrees`
--
ALTER TABLE `degrees`
  ADD PRIMARY KEY (`degree_id`),
  ADD KEY `fk_degrees_employee_idx` (`employee_id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_department_name` (`name`),
  ADD KEY `idx_dept_manager_id` (`manager_id`),
  ADD KEY `idx_dept_parent_id` (`parent_id`);

--
-- Indexes for table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_docs_uploader_idx` (`uploaded_by_user_id`),
  ADD KEY `fk_docs_department_idx` (`department_id`);

--
-- Indexes for table `document_versions`
--
ALTER TABLE `document_versions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_docver_doc_version` (`document_id`,`version_number`),
  ADD KEY `fk_docver_document_idx` (`document_id`),
  ADD KEY `fk_docver_creator_idx` (`created_by_user_id`);

--
-- Indexes for table `email_verification_tokens`
--
ALTER TABLE `email_verification_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_emailver_token` (`token`),
  ADD KEY `fk_emailver_user_idx` (`user_id`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_employee_user_id` (`user_id`),
  ADD UNIQUE KEY `uq_employee_code` (`employee_code`),
  ADD KEY `fk_employees_department_idx` (`department_id`),
  ADD KEY `fk_employees_position_idx` (`position_id`);

--
-- Indexes for table `employee_positions`
--
ALTER TABLE `employee_positions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_emppos_employee_idx` (`employee_id`),
  ADD KEY `fk_emppos_position_idx` (`position_id`),
  ADD KEY `fk_emppos_department_idx` (`department_id`),
  ADD KEY `idx_emppos_current_employee` (`employee_id`,`is_current`);

--
-- Indexes for table `family_members`
--
ALTER TABLE `family_members`
  ADD PRIMARY KEY (`family_member_id`),
  ADD KEY `fk_family_employee_idx` (`employee_id`);

--
-- Indexes for table `holidays`
--
ALTER TABLE `holidays`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `insurance`
--
ALTER TABLE `insurance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_insurance_employee_idx` (`employee_id`);

--
-- Indexes for table `interviews`
--
ALTER TABLE `interviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_interviews_application_idx` (`job_application_id`),
  ADD KEY `fk_interviews_interviewer_idx` (`interviewer_employee_id`);

--
-- Indexes for table `job_applications`
--
ALTER TABLE `job_applications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_application_email_job` (`email`,`job_position_id`),
  ADD KEY `fk_jobapp_jobpos_idx` (`job_position_id`),
  ADD KEY `idx_jobapp_status` (`status`);

--
-- Indexes for table `job_positions`
--
ALTER TABLE `job_positions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_jobpos_campaign_idx` (`campaign_id`),
  ADD KEY `fk_jobpos_position_idx` (`position_id`),
  ADD KEY `fk_jobpos_department_idx` (`department_id`),
  ADD KEY `fk_jobpos_manager_idx` (`hiring_manager_user_id`),
  ADD KEY `idx_jobpos_status` (`status`);

--
-- Indexes for table `kpi`
--
ALTER TABLE `kpi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_kpi_employee_idx` (`employee_id`),
  ADD KEY `idx_kpi_period` (`period_start`,`period_end`);

--
-- Indexes for table `leaves`
--
ALTER TABLE `leaves`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_leaves_employee_idx` (`employee_id`),
  ADD KEY `fk_leaves_approver_idx` (`approved_by_user_id`),
  ADD KEY `idx_leave_status_employee` (`employee_id`,`status`),
  ADD KEY `idx_leave_dates` (`start_date`,`end_date`);

--
-- Indexes for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_loginatt_ip_time` (`ip_address`,`attempt_time`),
  ADD KEY `idx_loginatt_user_time` (`username_attempted`,`attempt_time`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_notifications_user_idx` (`user_id`),
  ADD KEY `idx_notifications_read_user` (`user_id`,`is_read`);

--
-- Indexes for table `onboarding`
--
ALTER TABLE `onboarding`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_onboarding_employee` (`employee_id`),
  ADD KEY `fk_onboarding_employee_idx` (`employee_id`),
  ADD KEY `fk_onboarding_buddy_idx` (`buddy_employee_id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_pwdreset_token` (`token`),
  ADD KEY `fk_pwdreset_user_idx` (`user_id`);

--
-- Indexes for table `payroll`
--
ALTER TABLE `payroll`
  ADD PRIMARY KEY (`payroll_id`),
  ADD UNIQUE KEY `uq_payroll_employee_period` (`employee_id`,`pay_period_start`,`pay_period_end`),
  ADD KEY `fk_payroll_employee_idx` (`employee_id`),
  ADD KEY `fk_payroll_generator_idx` (`generated_by_user_id`),
  ADD KEY `idx_payroll_period` (`pay_period_start`,`pay_period_end`),
  ADD KEY `idx_payroll_status` (`status`);

--
-- Indexes for table `performances`
--
ALTER TABLE `performances`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_perf_employee_idx` (`employee_id`),
  ADD KEY `fk_perf_reviewer_idx` (`reviewer_user_id`),
  ADD KEY `idx_perf_period` (`review_period_start`,`review_period_end`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_permission_code` (`code`),
  ADD UNIQUE KEY `uq_permission_name` (`name`);

--
-- Indexes for table `policies`
--
ALTER TABLE `policies`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `positions`
--
ALTER TABLE `positions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_position_name_dept` (`name`,`department_id`),
  ADD KEY `fk_positions_department_idx` (`department_id`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_project_code` (`project_code`),
  ADD KEY `fk_projects_manager_idx` (`manager_employee_id`);

--
-- Indexes for table `project_resources`
--
ALTER TABLE `project_resources`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_projres_project_res` (`project_id`,`resource_type`,`resource_id`),
  ADD KEY `fk_projres_project_idx` (`project_id`),
  ADD KEY `idx_projres_resource` (`resource_type`,`resource_id`);

--
-- Indexes for table `project_tasks`
--
ALTER TABLE `project_tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_projtasks_project_idx` (`project_id`),
  ADD KEY `fk_projtasks_assignee_idx` (`assigned_to_employee_id`),
  ADD KEY `fk_projtasks_parent_idx` (`parent_task_id`),
  ADD KEY `idx_projtasks_status` (`status`),
  ADD KEY `idx_projtasks_due_date` (`due_date`);

--
-- Indexes for table `rate_limits`
--
ALTER TABLE `rate_limits`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_ratelimit_ip_endpoint_window` (`ip_address`,`endpoint`,`window_start`);

--
-- Indexes for table `recruitment_campaigns`
--
ALTER TABLE `recruitment_campaigns`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_reccamp_creator_idx` (`created_by_user_id`),
  ADD KEY `idx_reccamp_status` (`status`),
  ADD KEY `idx_reccamp_dates` (`start_date`,`end_date`);

--
-- Indexes for table `report_executions`
--
ALTER TABLE `report_executions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_repexec_template_idx` (`template_id`),
  ADD KEY `fk_repexec_schedule_idx` (`schedule_id`),
  ADD KEY `fk_repexec_executor_idx` (`executed_by_user_id`),
  ADD KEY `idx_repexec_status` (`status`),
  ADD KEY `idx_repexec_created_at` (`created_at`);

--
-- Indexes for table `report_schedules`
--
ALTER TABLE `report_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_repsched_template_idx` (`template_id`),
  ADD KEY `fk_repsched_creator_idx` (`created_by_user_id`),
  ADD KEY `idx_repsched_next_run` (`next_run_at`),
  ADD KEY `idx_repsched_status` (`status`);

--
-- Indexes for table `report_templates`
--
ALTER TABLE `report_templates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_reptemp_creator_idx` (`created_by_user_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_role_name` (`name`);

--
-- Indexes for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`role_id`,`permission_id`),
  ADD KEY `fk_roleperm_permission_idx` (`permission_id`);

--
-- Indexes for table `salary_history`
--
ALTER TABLE `salary_history`
  ADD PRIMARY KEY (`salary_history_id`),
  ADD KEY `fk_salaryhist_employee_idx` (`employee_id`),
  ADD KEY `fk_salaryhist_recorder_idx` (`recorded_by_user_id`),
  ADD KEY `idx_salaryhist_effective_date` (`effective_date`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`session_id`),
  ADD KEY `idx_sessions_expires` (`expires`);

--
-- Indexes for table `system_logs`
--
ALTER TABLE `system_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_syslog_type` (`log_type`),
  ADD KEY `idx_syslog_level` (`log_level`),
  ADD KEY `fk_syslog_user_idx` (`user_id`),
  ADD KEY `idx_syslog_created_at` (`created_at`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_setting_key` (`setting_key`),
  ADD KEY `fk_sysset_creator_idx` (`created_by_user_id`);

--
-- Indexes for table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_tasks_assignee_idx` (`assigned_to_employee_id`),
  ADD KEY `fk_tasks_assigner_idx` (`assigned_by_user_id`),
  ADD KEY `idx_tasks_status` (`status`),
  ADD KEY `idx_tasks_due_date` (`due_date`),
  ADD KEY `idx_tasks_related_entity` (`related_entity_type`,`related_entity_id`);

--
-- Indexes for table `training_courses`
--
ALTER TABLE `training_courses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `training_evaluations`
--
ALTER TABLE `training_evaluations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_eval_registration_evaluator` (`registration_id`,`evaluator_employee_id`),
  ADD KEY `fk_eval_registration_idx` (`registration_id`),
  ADD KEY `fk_eval_evaluator_idx` (`evaluator_employee_id`);

--
-- Indexes for table `training_registrations`
--
ALTER TABLE `training_registrations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_reg_employee_course` (`employee_id`,`course_id`),
  ADD KEY `fk_reg_employee_idx` (`employee_id`),
  ADD KEY `fk_reg_course_idx` (`course_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `uq_username` (`username`),
  ADD UNIQUE KEY `uq_email` (`email`),
  ADD KEY `fk_users_role_idx` (`role_id`);

--
-- Indexes for table `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD PRIMARY KEY (`profile_id`),
  ADD UNIQUE KEY `uq_user_profiles_user_id` (`user_id`);

--
-- Indexes for table `work_schedules`
--
ALTER TABLE `work_schedules`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_schedule_employee_date` (`employee_id`,`work_date`),
  ADD KEY `fk_worksch_employee_idx` (`employee_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activities`
--
ALTER TABLE `activities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `assets`
--
ALTER TABLE `assets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `asset_assignments`
--
ALTER TABLE `asset_assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `asset_maintenance`
--
ALTER TABLE `asset_maintenance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `attendance_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `log_id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `backup_logs`
--
ALTER TABLE `backup_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `benefits`
--
ALTER TABLE `benefits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `bonuses`
--
ALTER TABLE `bonuses`
  MODIFY `bonus_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `certificates`
--
ALTER TABLE `certificates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `contracts`
--
ALTER TABLE `contracts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `degrees`
--
ALTER TABLE `degrees`
  MODIFY `degree_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `document_versions`
--
ALTER TABLE `document_versions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `email_verification_tokens`
--
ALTER TABLE `email_verification_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `employee_positions`
--
ALTER TABLE `employee_positions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `family_members`
--
ALTER TABLE `family_members`
  MODIFY `family_member_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `holidays`
--
ALTER TABLE `holidays`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `insurance`
--
ALTER TABLE `insurance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `interviews`
--
ALTER TABLE `interviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `job_applications`
--
ALTER TABLE `job_applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `job_positions`
--
ALTER TABLE `job_positions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `kpi`
--
ALTER TABLE `kpi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `leaves`
--
ALTER TABLE `leaves`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `onboarding`
--
ALTER TABLE `onboarding`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `payroll`
--
ALTER TABLE `payroll`
  MODIFY `payroll_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `performances`
--
ALTER TABLE `performances`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `policies`
--
ALTER TABLE `policies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `positions`
--
ALTER TABLE `positions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `project_resources`
--
ALTER TABLE `project_resources`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `project_tasks`
--
ALTER TABLE `project_tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `rate_limits`
--
ALTER TABLE `rate_limits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `recruitment_campaigns`
--
ALTER TABLE `recruitment_campaigns`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `report_executions`
--
ALTER TABLE `report_executions`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `report_schedules`
--
ALTER TABLE `report_schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `report_templates`
--
ALTER TABLE `report_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `salary_history`
--
ALTER TABLE `salary_history`
  MODIFY `salary_history_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `system_logs`
--
ALTER TABLE `system_logs`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `training_courses`
--
ALTER TABLE `training_courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `training_evaluations`
--
ALTER TABLE `training_evaluations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `training_registrations`
--
ALTER TABLE `training_registrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `user_profiles`
--
ALTER TABLE `user_profiles`
  MODIFY `profile_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `work_schedules`
--
ALTER TABLE `work_schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activities`
--
ALTER TABLE `activities`
  ADD CONSTRAINT `fk_activities_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `asset_assignments`
--
ALTER TABLE `asset_assignments`
  ADD CONSTRAINT `fk_assetassign_asset` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_assetassign_assigner` FOREIGN KEY (`assigned_by_user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_assetassign_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_assetassign_receiver` FOREIGN KEY (`returned_to_user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `asset_maintenance`
--
ALTER TABLE `asset_maintenance`
  ADD CONSTRAINT `fk_assetmaint_asset` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_assetmaint_creator` FOREIGN KEY (`created_by_user_id`) REFERENCES `users` (`user_id`) ON UPDATE CASCADE;

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `fk_attendance_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `fk_audit_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `backup_logs`
--
ALTER TABLE `backup_logs`
  ADD CONSTRAINT `fk_backuplog_creator` FOREIGN KEY (`created_by_user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `bonuses`
--
ALTER TABLE `bonuses`
  ADD CONSTRAINT `fk_bonuses_approver` FOREIGN KEY (`approved_by_user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_bonuses_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_bonuses_payroll` FOREIGN KEY (`payroll_id`) REFERENCES `payroll` (`payroll_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `certificates`
--
ALTER TABLE `certificates`
  ADD CONSTRAINT `fk_certificates_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `contracts`
--
ALTER TABLE `contracts`
  ADD CONSTRAINT `fk_contracts_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `degrees`
--
ALTER TABLE `degrees`
  ADD CONSTRAINT `fk_degrees_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `departments`
--
ALTER TABLE `departments`
  ADD CONSTRAINT `fk_departments_manager` FOREIGN KEY (`manager_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_departments_parent` FOREIGN KEY (`parent_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `documents`
--
ALTER TABLE `documents`
  ADD CONSTRAINT `fk_docs_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_docs_uploader` FOREIGN KEY (`uploaded_by_user_id`) REFERENCES `users` (`user_id`) ON UPDATE CASCADE;

--
-- Constraints for table `document_versions`
--
ALTER TABLE `document_versions`
  ADD CONSTRAINT `fk_docver_creator` FOREIGN KEY (`created_by_user_id`) REFERENCES `users` (`user_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_docver_document` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `email_verification_tokens`
--
ALTER TABLE `email_verification_tokens`
  ADD CONSTRAINT `fk_emailver_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `employees`
--
ALTER TABLE `employees`
  ADD CONSTRAINT `fk_employees_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_employees_position` FOREIGN KEY (`position_id`) REFERENCES `positions` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_employees_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `employee_positions`
--
ALTER TABLE `employee_positions`
  ADD CONSTRAINT `fk_emppos_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_emppos_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_emppos_position` FOREIGN KEY (`position_id`) REFERENCES `positions` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `family_members`
--
ALTER TABLE `family_members`
  ADD CONSTRAINT `fk_family_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `insurance`
--
ALTER TABLE `insurance`
  ADD CONSTRAINT `fk_insurance_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `interviews`
--
ALTER TABLE `interviews`
  ADD CONSTRAINT `fk_interviews_application` FOREIGN KEY (`job_application_id`) REFERENCES `job_applications` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_interviews_interviewer` FOREIGN KEY (`interviewer_employee_id`) REFERENCES `employees` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `job_applications`
--
ALTER TABLE `job_applications`
  ADD CONSTRAINT `fk_jobapp_jobpos` FOREIGN KEY (`job_position_id`) REFERENCES `job_positions` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `job_positions`
--
ALTER TABLE `job_positions`
  ADD CONSTRAINT `fk_jobpos_campaign` FOREIGN KEY (`campaign_id`) REFERENCES `recruitment_campaigns` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_jobpos_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_jobpos_manager` FOREIGN KEY (`hiring_manager_user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_jobpos_position` FOREIGN KEY (`position_id`) REFERENCES `positions` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `kpi`
--
ALTER TABLE `kpi`
  ADD CONSTRAINT `fk_kpi_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `leaves`
--
ALTER TABLE `leaves`
  ADD CONSTRAINT `fk_leaves_approver` FOREIGN KEY (`approved_by_user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_leaves_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `onboarding`
--
ALTER TABLE `onboarding`
  ADD CONSTRAINT `fk_onboarding_buddy` FOREIGN KEY (`buddy_employee_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_onboarding_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD CONSTRAINT `fk_pwdreset_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `payroll`
--
ALTER TABLE `payroll`
  ADD CONSTRAINT `fk_payroll_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_payroll_generator` FOREIGN KEY (`generated_by_user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `performances`
--
ALTER TABLE `performances`
  ADD CONSTRAINT `fk_perf_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_perf_reviewer` FOREIGN KEY (`reviewer_user_id`) REFERENCES `users` (`user_id`) ON UPDATE CASCADE;

--
-- Constraints for table `positions`
--
ALTER TABLE `positions`
  ADD CONSTRAINT `fk_positions_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `projects`
--
ALTER TABLE `projects`
  ADD CONSTRAINT `fk_projects_manager` FOREIGN KEY (`manager_employee_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `project_resources`
--
ALTER TABLE `project_resources`
  ADD CONSTRAINT `fk_projres_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `project_tasks`
--
ALTER TABLE `project_tasks`
  ADD CONSTRAINT `fk_projtasks_assignee` FOREIGN KEY (`assigned_to_employee_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_projtasks_parent` FOREIGN KEY (`parent_task_id`) REFERENCES `project_tasks` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_projtasks_project` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `recruitment_campaigns`
--
ALTER TABLE `recruitment_campaigns`
  ADD CONSTRAINT `fk_reccamp_creator` FOREIGN KEY (`created_by_user_id`) REFERENCES `users` (`user_id`) ON UPDATE CASCADE;

--
-- Constraints for table `report_executions`
--
ALTER TABLE `report_executions`
  ADD CONSTRAINT `fk_repexec_executor` FOREIGN KEY (`executed_by_user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_repexec_schedule` FOREIGN KEY (`schedule_id`) REFERENCES `report_schedules` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_repexec_template` FOREIGN KEY (`template_id`) REFERENCES `report_templates` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `report_schedules`
--
ALTER TABLE `report_schedules`
  ADD CONSTRAINT `fk_repsched_creator` FOREIGN KEY (`created_by_user_id`) REFERENCES `users` (`user_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_repsched_template` FOREIGN KEY (`template_id`) REFERENCES `report_templates` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `report_templates`
--
ALTER TABLE `report_templates`
  ADD CONSTRAINT `fk_reptemp_creator` FOREIGN KEY (`created_by_user_id`) REFERENCES `users` (`user_id`) ON UPDATE CASCADE;

--
-- Constraints for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `fk_roleperm_permission` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_roleperm_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `salary_history`
--
ALTER TABLE `salary_history`
  ADD CONSTRAINT `fk_salaryhist_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_salaryhist_recorder` FOREIGN KEY (`recorded_by_user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `system_logs`
--
ALTER TABLE `system_logs`
  ADD CONSTRAINT `fk_syslog_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD CONSTRAINT `fk_sysset_creator` FOREIGN KEY (`created_by_user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `tasks`
--
ALTER TABLE `tasks`
  ADD CONSTRAINT `fk_tasks_assignee` FOREIGN KEY (`assigned_to_employee_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_tasks_assigner` FOREIGN KEY (`assigned_by_user_id`) REFERENCES `users` (`user_id`) ON UPDATE CASCADE;

--
-- Constraints for table `training_evaluations`
--
ALTER TABLE `training_evaluations`
  ADD CONSTRAINT `fk_eval_evaluator` FOREIGN KEY (`evaluator_employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_eval_registration` FOREIGN KEY (`registration_id`) REFERENCES `training_registrations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `training_registrations`
--
ALTER TABLE `training_registrations`
  ADD CONSTRAINT `fk_reg_course` FOREIGN KEY (`course_id`) REFERENCES `training_courses` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_reg_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `user_profiles`
--
ALTER TABLE `user_profiles`
  ADD CONSTRAINT `fk_user_profiles_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `work_schedules`
--
ALTER TABLE `work_schedules`
  ADD CONSTRAINT `fk_worksch_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
