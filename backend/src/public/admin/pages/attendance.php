<?php
// Kiểm tra nếu request là AJAX
if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    // Chỉ trả về nội dung chính
    ?>
    <div class="container-fluid">
        <h1 class="h3 mb-4 text-gray-800">Chấm công</h1>
        
        <!-- Thanh công cụ -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-4">
                        <div class="input-group">
                            <input type="date" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <select class="form-select">
                            <option value="">Tất cả phòng ban</option>
                            <option value="it">Phòng IT</option>
                            <option value="hr">Phòng Nhân sự</option>
                            <option value="marketing">Phòng Marketing</option>
                        </select>
                    </div>
                    <div class="col-md-4 text-end">
                        <button class="btn btn-primary">
                            <i class="fas fa-download"></i> Xuất báo cáo
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bảng chấm công -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Họ tên</th>
                                <th>Phòng ban</th>
                                <th>Giờ vào</th>
                                <th>Giờ ra</th>
                                <th>Trạng thái</th>
                                <th>Ghi chú</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>NV001</td>
                                <td>Nguyễn Văn A</td>
                                <td>Phòng IT</td>
                                <td>08:00</td>
                                <td>17:30</td>
                                <td><span class="badge bg-success">Đúng giờ</span></td>
                                <td>
                                    <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#noteModal">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </td>
                            </tr>
                            <!-- Thêm các hàng khác tương tự -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal ghi chú -->
    <div class="modal fade" id="noteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Thêm ghi chú</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <textarea class="form-control" rows="3" placeholder="Nhập ghi chú..."></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="button" class="btn btn-primary">Lưu</button>
                </div>
            </div>
        </div>
    </div>
    <?php
} else {
    // Nếu không phải AJAX request, chuyển hướng về trang chủ
    header('Location: /admin/dashboard_admin_V1.php');
    exit;
}
?> 