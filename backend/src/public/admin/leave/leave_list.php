<?php
session_start();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý nghỉ phép - Hệ thống quản lý nhân sự</title>
    
    <!-- CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="leave.css" rel="stylesheet">
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js" defer></script>
</head>
<style>
    .header-text h2 {
    color: #222;
    font-size: 2.2rem;
    font-weight: 900;
    margin: 0 0 0.4rem 0;
    letter-spacing: -1.5px;
    line-height: 1.1;
   color: #222;
    background: linear-gradient(90deg, #fafafb 20%, #dce8a9 80%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.header-text p {
    color: #222;
    font-size: 1.08rem;
    font-weight: 400;
    letter-spacing: 0.5px;
    margin: 0;
    opacity: 0.85;
    line-height: 1.5;
    text-shadow: 0 1px 8px rgba(76,0,252,0.06);
}

/* Dashboard Cards Styles */
.dashboard-cards {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
    margin: 1.5rem 0;
}

.dashboard-card {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    padding: 1rem;
    transition: transform 0.3s ease;
    position: relative;
    cursor: pointer;
}

.dashboard-card:hover {
    transform: translateY(-5px);
}

/* Reset CSS cho tooltip */
.leave-tooltip {
    display: none;
    position: absolute;
    top: 100%;
    left: 50%;
    transform: translateX(-50%);
    background: white;
    padding: 1rem;
    border-radius: 8px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    width: 300px;
    z-index: 9999;
    margin-top: 10px;
}

.dashboard-card:hover .leave-tooltip {
    display: block;
}

.leave-tooltip::before {
    content: '';
    position: absolute;
    top: -8px;
    left: 50%;
    transform: translateX(-50%);
    border-left: 8px solid transparent;
    border-right: 8px solid transparent;
    border-bottom: 8px solid white;
}

.leave-tooltip-title {
    font-weight: 600;
    margin-bottom: 12px;
    color: #333;
    font-size: 1rem;
    padding-bottom: 8px;
    border-bottom: 1px solid #eee;
}

.leave-tooltip-content {
    font-size: 0.9rem;
}

.leave-tooltip-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 6px 0;
    border-bottom: 1px solid #eee;
}

.leave-tooltip-item:last-child {
    border-bottom: none;
}

.leave-tooltip-item i {
    color: #f1c40f;
    font-size: 0.9rem;
}

/* Thêm style cho card */
.dashboard-card {
    position: relative;
    cursor: pointer;
    transition: all 0.3s ease;
}

.dashboard-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.15);
}

.card-header {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.card-icon {
    width: 45px;
    height: 45px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.card-icon i {
    color: white;
    font-size: 1.2rem;
    transition: transform 0.3s ease;
}

.dashboard-card:hover .card-icon i {
    transform: scale(1.2) rotate(10deg);
}

.card-title {
    font-size: 0.9rem;
    color: #666;
    margin: 0;
    text-transform: lowercase;
}

.card-title::first-letter {
    text-transform: uppercase;
}

.card-value {
    font-size: 1.5rem;
    font-weight: 600;
    margin: 0.2rem 0 0;
    color: #333;
}

/* Add animation for circle-decoration */
.circle-decoration {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    position: relative;
    animation: float 6s ease-in-out infinite;
}

@keyframes float {
    0% {
        transform: translateY(0px) rotate(0deg);
    }
    50% {
        transform: translateY(-20px) rotate(180deg);
    }
    100% {
        transform: translateY(0px) rotate(360deg);
    }
}

.icon-btn {
    border: none;
    background: none;
    outline: none;
    border-radius: 50%;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.2s;
    font-size: 1.2rem;
    padding: 0;
}
.icon-btn:focus {
    outline: none;
}
.info-btn i {
    color: #2196f3;
}
.info-btn:hover {
    background: #e3f2fd;
}
.edit-btn i {
    color: #ffc107;
}
.edit-btn:hover {
    background: #fff8e1;
}
.delete-btn i {
    color: #f44336;
}
.delete-btn:hover {
    background: #ffebee;
}
.action-btns {
    gap: 12px !important;
}

/* Status Badges */
.badge {
    padding: 0.25em 0.5em;
    font-weight: 500;
    border-radius: 4px;
    font-size: 0.7rem;
    letter-spacing: 0.1px;
    text-transform: none;
    display: inline-block;
    line-height: 1;
}

.badge-approved {
    background-color: #98cda9;
    color: #055619;
}

.badge-rejected {
    background-color: #fdeaea;
    color: #d63031;
}

.badge-pending {
    background-color: #fffbe6;
    color: #bfa100;
}

.badge-cancelled {
    background-color: #f2f2f2;
    color: #636e72;
}

.badge-status {
    padding: 0.2em 0.45em;
    font-size: 0.7rem;
    border-radius: 4px;
    font-weight: 500;
    letter-spacing: 0.1px;
    text-transform: none;
}

.search-filter {
    background: #fff;
    padding: 1rem;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 1.5rem;
}

.filter-group {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.search-box {
    flex: 1;
    min-width: 300px;
}

.search-suggestions {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #ddd;
    border-radius: 0 0 8px 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    z-index: 1000;
    display: none;
    max-height: 300px;
    overflow-y: auto;
}

.suggestion-item {
    padding: 0.75rem 1rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    border-bottom: 1px solid #eee;
}

.suggestion-item:hover {
    background: #f8f9fa;
}

.suggestion-item i {
    color: #6c757d;
    font-size: 0.9rem;
}

.suggestion-item .suggestion-text {
    flex: 1;
}

.suggestion-item .suggestion-type {
    font-size: 0.8rem;
    color: #6c757d;
    background: #e9ecef;
    padding: 0.2rem 0.5rem;
    border-radius: 4px;
}

.filter-controls {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.filter-select {
    min-width: 200px;
}

.date-range-filter {
    display: flex;
    gap: 0.5rem;
}

.active-filters {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.filter-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 0.75rem;
    background: #e9ecef;
    border-radius: 20px;
    font-size: 0.9rem;
}

.filter-badge .remove-filter {
    cursor: pointer;
    color: #6c757d;
}

.filter-badge .remove-filter:hover {
    color: #dc3545;
}

/* Thêm style cho datepicker */
.date-range-filter .input-group {
    min-width: 200px;
}

.date-range-filter .input-group-text {
    background-color: #fff;
    border-left: none;
    cursor: pointer;
}

.date-range-filter .form-control {
    border-right: none;
}

.date-range-filter .form-control:focus {
    border-color: #ced4da;
    box-shadow: none;
}

.date-range-filter .input-group:focus-within {
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
}

.flatpickr-calendar {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    border-radius: 8px;
}

.flatpickr-day.selected {
    background: #007bff;
    border-color: #007bff;
}

.flatpickr-day:hover {
    background: #e9ecef;
}

/* Thêm style cho no results */
.no-results {
    color: #6c757d;
    padding: 2rem;
    text-align: center;
}

.no-results i {
    color: #dee2e6;
    margin-bottom: 1rem;
}

.no-results p {
    font-size: 1.1rem;
    font-weight: 500;
}

/* Enhanced Button Styles */
.btn {
    padding: 0.5rem 1rem;
    font-weight: 500;
    border-radius: 8px;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.btn:active {
    transform: translateY(0);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

/* Success Button */
.btn-success {
    background: linear-gradient(135deg, #2ecc71, #27ae60);
    border: none;
    color: white;
}

.btn-success:hover {
    background: linear-gradient(135deg, #27ae60, #219a52);
}

/* Danger Button */
.btn-danger {
    background: linear-gradient(135deg, #e74c3c, #c0392b);
    border: none;
    color: white;
}

.btn-danger:hover {
    background: linear-gradient(135deg, #c0392b, #a93226);
}

/* Info Button */
.btn-info {
    background: linear-gradient(135deg, #3498db, #2980b9);
    border: none;
    color: white;
}

.btn-info:hover {
    background: linear-gradient(135deg, #2980b9, #2472a4);
}

/* Secondary Button */
.btn-secondary {
    background: linear-gradient(135deg, #95a5a6, #7f8c8d);
    border: none;
    color: white;
}

.btn-secondary:hover {
    background: linear-gradient(135deg, #7f8c8d, #6c7a7d);
}

/* Small Buttons */
.btn-sm {
    padding: 0.35rem 0.75rem;
    font-size: 0.875rem;
    border-radius: 6px;
}

/* Action Buttons Container */
.action-btns {
    display: flex;
    gap: 0.5rem;
    justify-content: center;
    align-items: center;
}

/* Icon Buttons */
.icon-btn {
    width: 32px;
    height: 32px;
    padding: 0;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    border: none;
    background: transparent;
    color: #666;
}

.icon-btn:hover {
    transform: scale(1.1);
    background: rgba(0,0,0,0.05);
}

.icon-btn i {
    font-size: 1rem;
    transition: all 0.3s ease;
}

/* Specific Icon Button Colors */
.info-btn:hover {
    color: #3498db;
    background: rgba(52, 152, 219, 0.1);
}

.edit-btn:hover {
    color: #f1c40f;
    background: rgba(241, 196, 15, 0.1);
}

.delete-btn:hover {
    color: #e74c3c;
    background: rgba(231, 76, 60, 0.1);
}

/* Button Loading State */
.btn.loading {
    position: relative;
    pointer-events: none;
    opacity: 0.8;
}

.btn.loading::after {
    content: '';
    position: absolute;
    width: 20px;
    height: 20px;
    border: 2px solid #fff;
    border-radius: 50%;
    border-top-color: transparent;
    animation: button-loading 0.8s linear infinite;
}

@keyframes button-loading {
    to {
        transform: rotate(360deg);
    }
}

/* Button with Icon */
.btn i {
    font-size: 1rem;
    transition: transform 0.3s ease;
}

.btn:hover i {
    transform: scale(1.1);
}

/* Modal Footer Buttons */
.modal-footer .btn {
    min-width: 100px;
}

/* Responsive Button Adjustments */
@media (max-width: 768px) {
    .btn {
        padding: 0.4rem 0.8rem;
        font-size: 0.9rem;
    }
    
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.8rem;
    }
    
    .action-btns {
        flex-wrap: wrap;
    }
}

/* Button Focus States */
.btn:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(0,123,255,0.25);
}

/* Button Disabled State */
.btn:disabled {
    opacity: 0.65;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

/* Button Group Styles */
.btn-group {
    display: inline-flex;
    gap: 0.5rem;
}

/* Custom Button Variants */
.btn-outline-success {
    border: 2px solid #2ecc71;
    color: #2ecc71;
    background: transparent;
}

.btn-outline-success:hover {
    background: #2ecc71;
    color: white;
}

.btn-outline-danger {
    border: 2px solid #e74c3c;
    color: #e74c3c;
    background: transparent;
}

.btn-outline-danger:hover {
    background: #e74c3c;
    color: white;
}

/* Button with Badge */
.btn-with-badge {
    position: relative;
}

.btn-badge {
    position: absolute;
    top: -8px;
    right: -8px;
    background: #e74c3c;
    color: white;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    font-size: 0.75rem;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Button with Tooltip */
.btn[data-tooltip] {
    position: relative;
}

.btn[data-tooltip]::before {
    content: attr(data-tooltip);
    position: absolute;
    bottom: 100%;
    left: 50%;
    transform: translateX(-50%);
    padding: 0.5rem;
    background: rgba(0,0,0,0.8);
    color: white;
    border-radius: 4px;
    font-size: 0.875rem;
    white-space: nowrap;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
}

.btn[data-tooltip]:hover::before {
    opacity: 1;
    visibility: visible;
    bottom: calc(100% + 5px);
}
</style>
<body>
    <!-- Toast Container -->
    <div class="toast-container position-fixed top-0 end-0 p-3"></div>

    <div class="dashboard-container">
        <!-- Nút quay lại Dashboard -->
        <a href="../dashboard_admin_V1.php" class="btn btn-outline-primary mb-3 d-inline-flex align-items-center" style="font-weight:600;font-size:1rem;gap:8px;">
            <i class="fa fa-arrow-left"></i> Quay lại Dashboard
        </a>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Page Header -->
            <header style="background: linear-gradient(to right, #ff7e5f, #feb47b);" class="page-header">
                <div class="header-content">
                    <div class="header-text">
                        <h2>Quản lý nghỉ phép</h2>
                        <p>Quản lý và theo dõi đơn xin nghỉ phép của nhân viên</p>
                    </div>
                    <div class="header-decoration">
                        <div style="background: linear-gradient(to right, #ff7e5f, #feb47b);" class="circle-decoration"></div>
                    </div>
                </div>
            </header>

            <!-- Leave Details Section -->
            <div id="leaveDetailsSection" class="leave-details-section mb-4" style="display: none;">
                <div class="card">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-file-alt text-primary me-2"></i>
                            Chi tiết đơn nghỉ phép
                        </h5>
                        <button type="button" class="btn-close" onclick="hideLeaveDetails()"></button>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-group mb-3">
                                    <label class="text-muted">Mã đơn:</label>
                                    <p class="mb-0" id="detailLeaveCode"></p>
                                </div>
                                <div class="info-group mb-3">
                                    <label class="text-muted">Nhân viên:</label>
                                    <p class="mb-0" id="detailEmployee"></p>
                                </div>
                                <div class="info-group mb-3">
                                    <label class="text-muted">Loại nghỉ phép:</label>
                                    <p class="mb-0" id="detailLeaveType"></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-group mb-3">
                                    <label class="text-muted">Ngày bắt đầu:</label>
                                    <p class="mb-0" id="detailStartDate"></p>
                                </div>
                                <div class="info-group mb-3">
                                    <label class="text-muted">Ngày kết thúc:</label>
                                    <p class="mb-0" id="detailEndDate"></p>
                                </div>
                                <div class="info-group mb-3">
                                    <label class="text-muted">Số ngày nghỉ:</label>
                                    <p class="mb-0" id="detailDuration"></p>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="info-group mb-3">
                                    <label class="text-muted">Lý do:</label>
                                    <p class="mb-0" id="detailReason"></p>
                                </div>
                            </div>
                        </div>
                        <div class="action-buttons mt-3">
                            <button class="btn btn-success me-2" id="approveButton">
                                <i class="fas fa-check"></i> Duyệt
                            </button>
                            <button class="btn btn-danger me-2" id="rejectButton">
                                <i class="fas fa-times"></i> Từ chối
                            </button>
                            <button class="btn btn-secondary" onclick="hideLeaveDetails()">
                                <i class="fas fa-times"></i> Đóng
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dashboard Cards -->
            <div class="dashboard-cards">
                <div class="dashboard-card" id="totalLeavesCard">
                    <div class="card-header">
                        <div class="card-icon" style="background: linear-gradient(135deg, #3498db, #2980b9);">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div>
                            <h6 class="card-title">Tổng số đơn nghỉ phép</h6>
                            <h3 class="card-value" id="totalLeaves">0</h3>
                        </div>
                    </div>
                    <div class="leave-tooltip">
                        <div class="leave-tooltip-title">Thống kê tổng quan</div>
                        <div class="leave-tooltip-content">
                            <div class="leave-tooltip-item">
                                <i class="fas fa-calendar-check"></i>
                                <span id="totalApproved">0 đơn đã duyệt</span>
                            </div>
                            <div class="leave-tooltip-item">
                                <i class="fas fa-calendar-times"></i>
                                <span id="totalRejected">0 đơn đã từ chối</span>
                            </div>
                            <div class="leave-tooltip-item">
                                <i class="fas fa-calendar-minus"></i>
                                <span id="totalCancelled">0 đơn đã hủy</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="dashboard-card" id="approvedLeavesCard">
                    <div class="card-header">
                        <div class="card-icon" style="background: linear-gradient(135deg, #2ecc71, #27ae60);">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div>
                            <h6 class="card-title">Đơn đã duyệt</h6>
                            <h3 class="card-value" id="approvedLeaves">0</h3>
                        </div>
                    </div>
                    <div class="leave-tooltip">
                        <div class="leave-tooltip-title">Chi tiết đơn đã duyệt</div>
                        <div class="leave-tooltip-content">
                            <div class="leave-tooltip-item">
                                <i class="fas fa-calendar-day"></i>
                                <span id="approvedToday">0 đơn hôm nay</span>
                            </div>
                            <div class="leave-tooltip-item">
                                <i class="fas fa-calendar-week"></i>
                                <span id="approvedThisWeek">0 đơn tuần này</span>
                            </div>
                            <div class="leave-tooltip-item">
                                <i class="fas fa-calendar-alt"></i>
                                <span id="approvedThisMonth">0 đơn tháng này</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="dashboard-card" id="pendingLeavesCard">
                    <div class="card-header">
                        <div class="card-icon" style="background: linear-gradient(135deg, #f1c40f, #f39c12);">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div>
                            <h6 class="card-title">Đơn đang chờ duyệt</h6>
                            <h3 class="card-value" id="pendingLeaves">0</h3>
                        </div>
                    </div>
                    <div class="leave-tooltip">
                        <div class="leave-tooltip-title">Chi tiết đơn chờ duyệt</div>
                        <div class="leave-tooltip-content">
                            <div class="leave-tooltip-item">
                                <i class="fas fa-user"></i>
                                <span id="pendingEmployeeCount">0 nhân viên</span>
                            </div>
                            <div class="leave-tooltip-item">
                                <i class="fas fa-calendar-day"></i>
                                <span id="pendingTodayCount">0 đơn hôm nay</span>
                            </div>
                            <div class="leave-tooltip-item">
                                <i class="fas fa-clock"></i>
                                <span id="pendingAvgTime">0 giờ chờ trung bình</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search and Filter -->
            <div class="search-filter">
                <div class="filter-group">
                    <div class="search-box position-relative">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-search"></i>
                            </span>
                            <input type="text" class="form-control" id="searchInput" placeholder="Tìm kiếm theo mã đơn, tên nhân viên, lý do...">
                            <!-- <button class="btn btn-outline-secondary" type="button" id="clearSearch">
                                <i class="fas fa-times"></i>
                            </button> -->
                        </div>
                        <div id="searchSuggestions" class="search-suggestions"></div>
                    </div>
                    <div class="filter-controls">
                        <select class="form-select filter-select" id="statusFilter">
                            <option value="">Tất cả trạng thái</option>
                            <option value="pending">Đang chờ duyệt</option>
                            <option value="approved">Đã duyệt</option>
                            <option value="rejected">Đã từ chối</option>
                            <option value="cancelled">Đã hủy</option>
                        </select>
                        <select class="form-select filter-select" id="leaveTypeFilter">
                            <option value="">Tất cả loại nghỉ phép</option>
                            <option value="Annual">Nghỉ phép năm</option>
                            <option value="Sick">Nghỉ ốm</option>
                            <option value="Unpaid">Nghỉ không lương</option>
                            <option value="Maternity">Nghỉ thai sản</option>
                        </select>
                        <div class="date-range-filter">
                            <div class="input-group">
                                <input type="text" class="form-control datepicker" id="startDateFilter" placeholder="Từ ngày" autocomplete="off">
                                <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                            </div>
                            <div class="input-group">
                                <input type="text" class="form-control datepicker" id="endDateFilter" placeholder="Đến ngày" autocomplete="off">
                                <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="activeFilters" class="active-filters mt-2"></div>
            </div>

            <!-- Leave Table -->
            <div class="leave-table">
                <div class="table-header">
                    <h5 class="table-title">Danh sách đơn nghỉ phép</h5>
                    <div class="table-actions">
                        <button class="btn btn-success me-2" id="exportBtn">
                            <i class="fas fa-file-export"></i> Xuất Excel
                        </button>
                        <button class="btn btn-primary" id="addLeaveBtn">
                            <i class="fas fa-plus"></i> Thêm đơn nghỉ phép
                        </button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>STT</th>
                                <th>Mã đơn</th>
                                <th>Nhân viên</th>
                                <th>Loại nghỉ phép</th>
                                <th>Ngày bắt đầu</th>
                                <th>Ngày kết thúc</th>
                                <th>Số ngày</th>
                                <th>Lý do</th>
                                <th>Trạng thái</th>
                                <th>Người duyệt</th>
                                <th>Ngày tạo</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody id="leaveTableBody">
                            <!-- Data will be loaded here -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <nav aria-label="Page navigation" class="mt-4">
                <div id="customPagination" class="d-flex align-items-center justify-content-center gap-3">
                    <!-- Pagination sẽ được render bởi JS -->
                </div>
            </nav>

            <!-- Leave Info Modal -->
            <div class="modal fade" id="leaveInfoModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Thông tin chi tiết đơn nghỉ phép</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="leave-info-grid">
                                <div class="info-section">
                                    <h6><i class="fas fa-info-circle"></i> Thông tin cơ bản</h6>
                                    <div class="info-content">
                                        <div class="info-item">
                                            <span class="info-label">Mã đơn:</span>
                                            <span class="info-value" id="infoLeaveCode"></span>
                                        </div>
                                        <div class="info-item">
                                            <span class="info-label">Nhân viên:</span>
                                            <span class="info-value" id="infoEmployee"></span>
                                        </div>
                                        <div class="info-item">
                                            <span class="info-label">Loại nghỉ phép:</span>
                                            <span class="info-value" id="infoLeaveType"></span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="info-section">
                                    <h6><i class="fas fa-calendar"></i> Thông tin thời gian</h6>
                                    <div class="info-content">
                                        <div class="info-item">
                                            <span class="info-label">Ngày bắt đầu:</span>
                                            <span class="info-value" id="infoStartDate"></span>
                                        </div>
                                        <div class="info-item">
                                            <span class="info-label">Ngày kết thúc:</span>
                                            <span class="info-value" id="infoEndDate"></span>
                                        </div>
                                        <div class="info-item">
                                            <span class="info-label">Số ngày nghỉ:</span>
                                            <span class="info-value" id="infoDuration"></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="info-section">
                                    <h6><i class="fas fa-file-alt"></i> Thông tin bổ sung</h6>
                                    <div class="info-content">
                                        <div class="info-item">
                                            <span class="info-label">Lý do:</span>
                                            <span class="info-value" id="infoReason"></span>
                                        </div>
                                        <div class="info-item">
                                            <span class="info-label">File đính kèm:</span>
                                            <span class="info-value" id="infoAttachment"></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="info-section">
                                    <h6><i class="fas fa-history"></i> Thông tin phê duyệt</h6>
                                    <div class="info-content">
                                        <div class="info-item">
                                            <span class="info-label">Trạng thái:</span>
                                            <span class="info-value" id="infoStatus"></span>
                                        </div>
                                        <div class="info-item">
                                            <span class="info-label">Người duyệt:</span>
                                            <span class="info-value" id="infoApprover"></span>
                                        </div>
                                        <div class="info-item">
                                            <span class="info-label">Ý kiến phê duyệt:</span>
                                            <span class="info-value" id="infoApproverComments"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer" id="leaveActionButtons">
                            <!-- Buttons will be added dynamically based on leave status -->
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Add/Edit Leave Modal -->
    <div class="modal fade" id="leaveModal" tabindex="-1" aria-labelledby="leaveModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" style="border-radius:18px;">
                <div class="modal-header" style="border-bottom: 1px solid #eee;">
                    <h2 class="modal-title" id="leaveModalLabel" style="font-weight:700; font-size:2rem; margin:0;">Thêm đơn nghỉ phép</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="padding: 2rem 2.5rem;">
                    <form id="leaveForm">
                        <div style="margin-bottom: 1.5rem;">
                            <h5 style="font-weight:600; margin-bottom:1rem; color:#222;"><input type="checkbox" checked disabled style="margin-right:8px;">Thông tin nghỉ phép</h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label" for="employeeId">Mã nhân viên <span style="color:red">*</span></label>
                                    <select id="employeeId" class="form-control" required>
                                        <option value="">Chọn mã nhân viên</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="employeeName">Tên nhân viên</label>
                                    <input type="text" id="employeeName" class="form-control" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="leaveType">Loại nghỉ phép <span style="color:red">*</span></label>
                                    <select id="leaveType" class="form-control" required>
                                        <option value="">Chọn loại nghỉ phép</option>
                                        <option value="Annual">Nghỉ phép năm</option>
                                        <option value="Sick">Nghỉ ốm</option>
                                        <option value="Unpaid">Nghỉ không lương</option>
                                        <option value="Maternity">Nghỉ thai sản</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="startDate">Ngày bắt đầu <span style="color:red">*</span></label>
                                    <div class="input-group">
                                        <input type="date" id="startDate" class="form-control" required>
                                        <!-- <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span> -->
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="endDate">Ngày kết thúc <span style="color:red">*</span></label>
                                    <div class="input-group">
                                        <input type="date" id="endDate" class="form-control" required>
                                        <!-- <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span> -->
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label" for="reason">Lý do <span style="color:red">*</span></label>
                                    <textarea id="reason" class="form-control" rows="2" required></textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label" for="attachment">File đính kèm</label>
                                    <input type="file" id="attachment" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fas fa-save"></i> Lưu
                            </button>
                            <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">
                                <i class="fas fa-times"></i> Hủy
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Từ chối đơn nghỉ phép</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="rejectReason" class="form-label">Lý do từ chối <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="rejectReason" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-danger" id="confirmReject">Xác nhận từ chối</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Leaves Modal -->
    <div class="modal fade" id="pendingLeavesModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title">
                        <i class="fas fa-clock text-warning me-2"></i>
                        Danh sách đơn đang chờ duyệt
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>STT</th>
                                    <th>Mã đơn</th>
                                    <th>Nhân viên</th>
                                    <th>Loại nghỉ phép</th>
                                    <th>Ngày bắt đầu</th>
                                    <th>Ngày kết thúc</th>
                                    <th>Số ngày</th>
                                    <th>Lý do</th>
                                    <th>Ngày tạo</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody id="pendingLeavesTableBody">
                                <!-- Data will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Leave Details Modal -->
    <div class="modal fade" id="leaveDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title">
                        <i class="fas fa-file-alt text-primary me-2"></i>
                        Chi tiết đơn nghỉ phép
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-group mb-3">
                                <label class="text-muted">Mã đơn:</label>
                                <p class="mb-0" id="modalLeaveCode"></p>
                            </div>
                            <div class="info-group mb-3">
                                <label class="text-muted">Nhân viên:</label>
                                <p class="mb-0" id="modalEmployee"></p>
                            </div>
                            <div class="info-group mb-3">
                                <label class="text-muted">Loại nghỉ phép:</label>
                                <p class="mb-0" id="modalLeaveType"></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-group mb-3">
                                <label class="text-muted">Ngày bắt đầu:</label>
                                <p class="mb-0" id="modalStartDate"></p>
                            </div>
                            <div class="info-group mb-3">
                                <label class="text-muted">Ngày kết thúc:</label>
                                <p class="mb-0" id="modalEndDate"></p>
                            </div>
                            <div class="info-group mb-3">
                                <label class="text-muted">Số ngày nghỉ:</label>
                                <p class="mb-0" id="modalDuration"></p>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="info-group mb-3">
                                <label class="text-muted">Lý do:</label>
                                <p class="mb-0" id="modalReason"></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button class="btn btn-success me-2" id="modalApproveButton">
                        <i class="fas fa-check"></i> Duyệt
                    </button>
                    <button class="btn btn-danger me-2" id="modalRejectButton">
                        <i class="fas fa-times"></i> Từ chối
                    </button>
                    <button class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Đóng
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../js/modules/api_service.js"></script>
    <script src="../js/modules/notifications.js"></script>
    <script src="leave.js"></script>

    <!-- Thêm script để lấy user ID từ session -->
    <script>
        // Lấy user ID từ session và set vào biến global
        window.currentUserId = <?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'null'; ?>;
    </script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Hàm lấy dữ liệu thống kê từ API
        async function fetchLeaveStatistics() {
            try {
                const response = await fetch('../api/leaves.php?action=statistics');
                const result = await response.json();
                
                if (result.success) {
                    const data = result.data;
                    
                    // Cập nhật giá trị chính của các card
                    document.getElementById('totalLeaves').textContent = data.total_leaves || 0;
                    document.getElementById('approvedLeaves').textContent = data.approved_leaves || 0;
                    document.getElementById('pendingLeaves').textContent = data.pending_leaves || 0;

                    // Cập nhật tooltip cho card tổng số đơn
                    document.getElementById('totalApproved').textContent = `${data.approved_leaves || 0} đơn đã duyệt`;
                    document.getElementById('totalRejected').textContent = `${data.rejected_leaves || 0} đơn đã từ chối`;
                    document.getElementById('totalCancelled').textContent = `${data.cancelled_leaves || 0} đơn đã hủy`;

                    // Cập nhật tooltip cho card đơn đã duyệt
                    document.getElementById('approvedToday').textContent = `${data.approved_today || 0} đơn hôm nay`;
                    document.getElementById('approvedThisWeek').textContent = `${data.approved_this_week || 0} đơn tuần này`;
                    document.getElementById('approvedThisMonth').textContent = `${data.approved_this_month || 0} đơn tháng này`;

                    // Cập nhật tooltip cho card đơn chờ duyệt
                    document.getElementById('pendingEmployeeCount').textContent = `${data.pending_employee_count || 0} nhân viên`;
                    document.getElementById('pendingTodayCount').textContent = `${data.pending_today || 0} đơn hôm nay`;
                    document.getElementById('pendingAvgTime').textContent = `${data.pending_avg_time || 0} giờ chờ trung bình`;
                }
            } catch (error) {
                console.error('Lỗi khi lấy dữ liệu thống kê:', error);
            }
        }

        // Thêm sự kiện hover cho tất cả các card
        const cards = document.querySelectorAll('.dashboard-card');
        cards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                const tooltip = this.querySelector('.leave-tooltip');
                if (tooltip) {
                    tooltip.style.display = 'block';
                }
            });

            card.addEventListener('mouseleave', function() {
                const tooltip = this.querySelector('.leave-tooltip');
                if (tooltip) {
                    tooltip.style.display = 'none';
                }
            });
        });

        // Gọi hàm lấy dữ liệu khi trang được tải
        fetchLeaveStatistics();

        // Cập nhật dữ liệu mỗi 5 phút
        setInterval(fetchLeaveStatistics, 300000);
    });
    </script>

    <script>
    // Khai báo các biến toàn cục chỉ một lần duy nhất
    let searchInput = document.getElementById('searchInput');
    let searchSuggestions = document.getElementById('searchSuggestions');
    let clearSearchBtn = document.getElementById('clearSearch');
    let activeFilters = document.getElementById('activeFilters');
    let statusFilter = document.getElementById('statusFilter');
    let leaveTypeFilter = document.getElementById('leaveTypeFilter');
    let startDateFilter = document.getElementById('startDateFilter');
    let endDateFilter = document.getElementById('endDateFilter');

    // Debounce function to limit API calls
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // Search suggestions handling
    const debouncedSearch = debounce(async (query) => {
        if (query.length < 2) {
            searchSuggestions.style.display = 'none';
            return;
        }

        try {
            const response = await fetch(`../api/leaves.php?action=suggestions&search=${encodeURIComponent(query)}`);
            const data = await response.json();
            
            if (data.success && data.suggestions.length > 0) {
                searchSuggestions.innerHTML = data.suggestions.map(suggestion => `
                    <div class="suggestion-item" data-type="${suggestion.type}" data-value="${suggestion.title}">
                        <i class="fas ${getSuggestionIcon(suggestion.type)}"></i>
                        <div class="suggestion-text">${suggestion.title}</div>
                        <span class="suggestion-type">${getSuggestionType(suggestion.type)}</span>
                    </div>
                `).join('');
                searchSuggestions.style.display = 'block';
            } else {
                searchSuggestions.style.display = 'none';
            }
        } catch (error) {
            console.error('Error fetching suggestions:', error);
        }
    }, 300);

    // Event listeners for search
    searchInput.addEventListener('input', (e) => {
        debouncedSearch(e.target.value);
        applyFilters(); // Apply filters when search input changes
    });

    searchInput.addEventListener('focus', () => {
        if (searchInput.value.length >= 2) {
            debouncedSearch(searchInput.value);
        }
    });

    searchSuggestions.addEventListener('click', (e) => {
        const suggestionItem = e.target.closest('.suggestion-item');
        if (suggestionItem) {
            searchInput.value = suggestionItem.dataset.value;
            searchSuggestions.style.display = 'none';
            applyFilters();
        }
    });

    clearSearchBtn.addEventListener('click', () => {
        searchInput.value = '';
        searchSuggestions.style.display = 'none';
        applyFilters();
    });

    // Helper functions for suggestions
    function getSuggestionIcon(type) {
        const icons = {
            'employee': 'fa-user',
            'leave_code': 'fa-file-alt',
            'reason': 'fa-comment',
            'status': 'fa-tag'
        };
        return icons[type] || 'fa-search';
    }

    function getSuggestionType(type) {
        const types = {
            'employee': 'Nhân viên',
            'leave_code': 'Mã đơn',
            'reason': 'Lý do',
            'status': 'Trạng thái'
        };
        return types[type] || type;
    }
    </script>

    <!-- Thêm script để format và parse ngày -->
    <script>
    // Format date function
    function formatDate(date) {
        if (!date) return '';
        const d = new Date(date);
        const day = String(d.getDate()).padStart(2, '0');
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const year = d.getFullYear();
        return `${day}/${month}/${year}`;
    }

    // Parse date from dd/mm/yyyy to yyyy-mm-dd
    function parseDate(dateStr) {
        if (!dateStr) return '';
        const [day, month, year] = dateStr.split('/');
        return `${year}-${month}-${day}`;
    }

    // Initialize datepickers
    document.addEventListener('DOMContentLoaded', function() {
        // Add datepicker CSS
        const datepickerCSS = document.createElement('link');
        datepickerCSS.rel = 'stylesheet';
        datepickerCSS.href = 'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css';
        document.head.appendChild(datepickerCSS);

        // Add datepicker JS
        const datepickerJS = document.createElement('script');
        datepickerJS.src = 'https://cdn.jsdelivr.net/npm/flatpickr';
        datepickerJS.onload = function() {
            // Initialize datepickers
            flatpickr(".datepicker", {
                dateFormat: "d/m/Y",
                locale: {
                    firstDayOfWeek: 1,
                    weekdays: {
                        shorthand: ["CN", "T2", "T3", "T4", "T5", "T6", "T7"],
                        longhand: ["Chủ nhật", "Thứ hai", "Thứ ba", "Thứ tư", "Thứ năm", "Thứ sáu", "Thứ bảy"]
                    },
                    months: {
                        shorthand: ["T1", "T2", "T3", "T4", "T5", "T6", "T7", "T8", "T9", "T10", "T11", "T12"],
                        longhand: ["Tháng 1", "Tháng 2", "Tháng 3", "Tháng 4", "Tháng 5", "Tháng 6", "Tháng 7", "Tháng 8", "Tháng 9", "Tháng 10", "Tháng 11", "Tháng 12"]
                    }
                },
                allowInput: true,
                onChange: function(selectedDates, dateStr) {
                    applyFilters();
                }
            });
        };
        document.head.appendChild(datepickerJS);
    });

    // Update applyFilters function
    function applyFilters() {
        const params = new URLSearchParams();
        
        if (searchInput.value) params.append('search', searchInput.value);
        if (statusFilter.value) params.append('status', statusFilter.value);
        if (leaveTypeFilter.value) params.append('leave_type', leaveTypeFilter.value);
        if (startDateFilter.value) params.append('start_date', startDateFilter.value);
        if (endDateFilter.value) params.append('end_date', endDateFilter.value);
        
        // Update URL with current filters
        const newUrl = `${window.location.pathname}?${params.toString()}`;
        window.history.pushState({}, '', newUrl);
        
        // Update active filters display
        updateActiveFilters();
        
        // Fetch and update table data
        fetchLeaveData();
    }

    // Update updateActiveFilters function
    function updateActiveFilters() {
        const filters = [];
        
        if (searchInput.value) {
            filters.push({
                type: 'search',
                label: `Tìm kiếm: ${searchInput.value}`,
                value: searchInput.value
            });
        }
        
        if (statusFilter.value) {
            filters.push({
                type: 'status',
                label: `Trạng thái: ${statusFilter.options[statusFilter.selectedIndex].text}`,
                value: statusFilter.value
            });
        }
        
        if (leaveTypeFilter.value) {
            filters.push({
                type: 'leaveType',
                label: `Loại nghỉ: ${leaveTypeFilter.options[leaveTypeFilter.selectedIndex].text}`,
                value: leaveTypeFilter.value
            });
        }
        
        if (startDateFilter.value || endDateFilter.value) {
            const dateRange = [];
            if (startDateFilter.value) dateRange.push(startDateFilter.value);
            if (endDateFilter.value) dateRange.push(endDateFilter.value);
            filters.push({
                type: 'dateRange',
                label: `Khoảng thời gian: ${dateRange.join(' - ')}`,
                value: dateRange
            });
        }
        
        activeFilters.innerHTML = filters.map(filter => `
            <div class="filter-badge">
                <span>${filter.label}</span>
                <i class="fas fa-times remove-filter" data-type="${filter.type}"></i>
            </div>
        `).join('');
        
        // Add event listeners to remove buttons
        document.querySelectorAll('.remove-filter').forEach(btn => {
            btn.addEventListener('click', () => {
                const type = btn.dataset.type;
                switch(type) {
                    case 'search':
                        searchInput.value = '';
                        break;
                    case 'status':
                        statusFilter.value = '';
                        break;
                    case 'leaveType':
                        leaveTypeFilter.value = '';
                        break;
                    case 'dateRange':
                        startDateFilter.value = '';
                        endDateFilter.value = '';
                        break;
                }
                applyFilters();
            });
        });
    }

    // Update initializeFiltersFromUrl function
    function initializeFiltersFromUrl() {
        const params = new URLSearchParams(window.location.search);
        
        if (params.has('search')) searchInput.value = params.get('search');
        if (params.has('status')) statusFilter.value = params.get('status');
        if (params.has('leave_type')) leaveTypeFilter.value = params.get('leave_type');
        if (params.has('start_date')) startDateFilter.value = params.get('start_date');
        if (params.has('end_date')) endDateFilter.value = params.get('end_date');
        
        updateActiveFilters();
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', () => {
        initializeFiltersFromUrl();
        applyFilters();
    });
    </script>

    <script>
    // Thêm vào phần script
    function updateTableData(data) {
        const tbody = document.getElementById('leaveTableBody');
        
        if (!data || data.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="12" class="text-center py-4">
                        <div class="no-results">
                            <i class="fas fa-search fa-2x mb-3"></i>
                            <p class="mb-0">Không có kết quả tìm kiếm</p>
                        </div>
                    </td>
                </tr>
            `;
            return;
        }
        
        tbody.innerHTML = data.map((leave, index) => `
            <tr>
                <td>${index + 1}</td>
                <td>${leave.leave_code || '-'}</td>
                <td>${leave.employee_name || '-'}</td>
                <td>${leave.leave_type || '-'}</td>
                <td>${formatDate(leave.start_date) || '-'}</td>
                <td>${formatDate(leave.end_date) || '-'}</td>
                <td>${leave.leave_duration_days || '-'}</td>
                <td>${leave.reason || '-'}</td>
                <td>
                    <span class="badge badge-${getStatusClass(leave.status)}">
                        ${getStatusText(leave.status)}
                    </span>
                </td>
                <td>${leave.approver_name || '-'}</td>
                <td>${formatDate(leave.created_at) || '-'}</td>
                <td>
                    <div class="action-btns d-flex justify-content-center">
                        <button class="icon-btn info-btn" onclick="viewLeave(${leave.id})">
                            <i class="fas fa-info-circle"></i>
                        </button>
                        <button class="icon-btn edit-btn" onclick="editLeave(${leave.id})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="icon-btn delete-btn" onclick="deleteLeave(${leave.id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    }

    // Helper function to get status class
    function getStatusClass(status) {
        const classes = {
            'pending': 'pending',
            'approved': 'approved',
            'rejected': 'rejected',
            'cancelled': 'cancelled'
        };
        return classes[status] || 'pending';
    }

    // Helper function to get status text
    function getStatusText(status) {
        const texts = {
            'pending': 'Đang chờ duyệt',
            'approved': 'Đã duyệt',
            'rejected': 'Đã từ chối',
            'cancelled': 'Đã hủy'
        };
        return texts[status] || status;
    }

    // Update fetchLeaveData function
    async function fetchLeaveData() {
        try {
            const params = new URLSearchParams(window.location.search);
            const response = await fetch(`../api/leaves.php?${params.toString()}`);
            const result = await response.json();
            
            if (result.success) {
                updateTableData(result.data);
                updatePagination(result.total, result.page, result.per_page);
            } else {
                showNotification('error', 'Lỗi khi tải dữ liệu', result.message);
            }
        } catch (error) {
            console.error('Error fetching leave data:', error);
            showNotification('error', 'Lỗi khi tải dữ liệu', 'Không thể kết nối đến server');
        }
    }
    </script>

    <script>
    // Hàm load dữ liệu nhân viên vào select box
    async function loadEmployeeData() {
        try {
            const response = await fetch('../api/employees.php');
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const result = await response.json();
            
            if (result.success) {
                const employeeSelect = document.getElementById('employeeId');
                
                // Xóa các option cũ
                employeeSelect.innerHTML = '<option value="">Chọn nhân viên</option>';
                
                // Thêm các nhân viên vào select box
                if (Array.isArray(result.data)) {
                    result.data.forEach(employee => {
                        const option = document.createElement('option');
                        option.value = employee.id;
                        option.textContent = `${employee.full_name} (${employee.email})`;
                        employeeSelect.appendChild(option);
                    });
                } else {
                    console.error('Dữ liệu nhân viên không đúng định dạng');
                    showNotification('error', 'Lỗi', 'Dữ liệu nhân viên không đúng định dạng');
                }
            } else {
                throw new Error(result.message || 'Không thể tải danh sách nhân viên');
            }
        } catch (error) {
            console.error('Lỗi khi tải dữ liệu nhân viên:', error);
            showNotification('error', 'Lỗi khi tải dữ liệu', error.message || 'Không thể tải danh sách nhân viên');
            
            // Thêm option mặc định khi có lỗi
            const employeeSelect = document.getElementById('employeeId');
            employeeSelect.innerHTML = '<option value="">Không thể tải danh sách nhân viên</option>';
        }
    }

    // Load dữ liệu khi modal được mở
    document.getElementById('addLeaveBtn').addEventListener('click', function() {
        loadEmployeeData();
    });

    // Thêm hàm để format date input
    function formatDateInput(date) {
        if (!date) return '';
        const d = new Date(date);
        return d.toISOString().split('T')[0];
    }

    // Set min date for date inputs
    document.getElementById('startDate').min = formatDateInput(new Date());
    document.getElementById('endDate').min = formatDateInput(new Date());

    // Update end date min when start date changes
    document.getElementById('startDate').addEventListener('change', function() {
        document.getElementById('endDate').min = this.value;
    });
    </script>
</body>
</html>