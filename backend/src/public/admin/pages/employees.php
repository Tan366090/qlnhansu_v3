<?php
// Kiểm tra nếu request là AJAX
if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    // Chỉ trả về nội dung chính
    ?>
    <div class="container-fluid">
        <h1 class="h3 mb-4 text-gray-800">Quản lý nhân viên</h1>
        
        <!-- Thanh công cụ -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="Tìm kiếm nhân viên...">
                            <button class="btn btn-primary" type="button">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-6 text-end">
                        <button class="btn btn-success">
                            <i class="fas fa-plus"></i> Thêm nhân viên
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Danh sách nhân viên -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Họ tên</th>
                                <th>Email</th>
                                <th>Phòng ban</th>
                                <th>Chức vụ</th>
                                <th>Trạng thái</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>NV001</td>
                                <td>Nguyễn Văn A</td>
                                <td>nguyenvana@example.com</td>
                                <td>Phòng Kỹ thuật</td>
                                <td>Nhân viên</td>
                                <td><span class="badge bg-success">Đang làm việc</span></td>
                                <td>
                                    <button class="btn btn-sm btn-info"><i class="fas fa-edit"></i></button>
                                    <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                            <!-- Thêm các hàng khác tương tự -->
                        </tbody>
                    </table>
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