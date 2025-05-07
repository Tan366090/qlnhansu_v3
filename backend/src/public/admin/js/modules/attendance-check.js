// Global variables
let currentPage = 1;
let totalPages = 1;
let selectedEmployees = new Set();
let currentDate = new Date();

// Check authentication first
if (!auth.checkAuth()) {
    window.location.href = "/login.html";
}

class AttendanceCheck {
    constructor() {
        this.currentDate = new Date();
        this.init();
    }

    async init() {
        await this.loadTodayAttendance();
        this.setupEventListeners();
    }

    async loadTodayAttendance() {
        try {
            common.showLoading();
            
            const today = this.currentDate.toISOString().split('T')[0];
            const params = {
                attendance_date: today
            };

            const response = await api.attendance.getAll(params);
            
            // Update table
            const tbody = document.querySelector("#attendanceTable tbody");
            tbody.innerHTML = "";
            
            response.forEach(record => {
                const tr = document.createElement("tr");
                tr.innerHTML = `
                    <td>${record.user_name}</td>
                    <td>${record.department_name}</td>
                    <td>${record.position_name}</td>
                    <td>${record.attendance_date}</td>
                    <td>${record.recorded_at}</td>
                    <td>
                        <span class="attendance-symbol ${record.attendance_symbol.toLowerCase()}">
                            ${record.attendance_symbol}
                        </span>
                    </td>
                    <td>${record.notes || '-'}</td>
                    <td>
                        <div class="action-buttons">
                            <button onclick="window.attendanceCheck.editAttendance(${record.attendance_id})" class="btn btn-warning">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="window.attendanceCheck.deleteAttendance(${record.attendance_id})" class="btn btn-danger">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                `;
                tbody.appendChild(tr);
            });
            
            common.hideLoading();
        } catch (error) {
            common.hideLoading();
            common.showError("Không thể tải dữ liệu chấm công: " + error.message);
        }
    }

    setupEventListeners() {
        // Date navigation
        document.getElementById("prevDate").addEventListener("click", () => {
            this.currentDate.setDate(this.currentDate.getDate() - 1);
            this.updateDateDisplay();
            this.loadTodayAttendance();
        });

        document.getElementById("nextDate").addEventListener("click", () => {
            this.currentDate.setDate(this.currentDate.getDate() + 1);
            this.updateDateDisplay();
            this.loadTodayAttendance();
        });

        // Add attendance
        document.getElementById("addAttendanceBtn").addEventListener("click", () => {
            this.showAddAttendanceModal();
        });

        // Export
        document.getElementById("exportBtn").addEventListener("click", () => {
            this.exportAttendance();
        });
    }

    updateDateDisplay() {
        const dateDisplay = document.getElementById("dateDisplay");
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        dateDisplay.textContent = this.currentDate.toLocaleDateString('vi-VN', options);
    }

    async showAddAttendanceModal() {
        try {
            // Load users for dropdown
            const users = await api.users.getAll();
            
            const modal = document.createElement("div");
            modal.className = "modal";
            modal.innerHTML = `
                <div class="modal-content">
                    <h2>Thêm chấm công</h2>
                    <form id="addAttendanceForm">
                        <div class="form-group">
                            <label>Nhân viên</label>
                            <select name="user_id" required>
                                ${users.map(user => `
                                    <option value="${user.user_id}">${user.username} - ${user.department_name}</option>
                                `).join('')}
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Ngày</label>
                            <input type="date" name="attendance_date" value="${this.currentDate.toISOString().split('T')[0]}" required>
                        </div>
                        <div class="form-group">
                            <label>Ký hiệu</label>
                            <select name="attendance_symbol" required>
                                <option value="P">P - Có mặt</option>
                                <option value="A">A - Vắng mặt</option>
                                <option value="L">L - Đi muộn</option>
                                <option value="E">E - Về sớm</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Ghi chú</label>
                            <textarea name="notes"></textarea>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Lưu</button>
                            <button type="button" class="btn btn-secondary" onclick="this.closest('.modal').remove()">Hủy</button>
                        </div>
                    </form>
                </div>
            `;
            
            document.body.appendChild(modal);
            
            // Handle form submission
            document.getElementById("addAttendanceForm").addEventListener("submit", async (e) => {
                e.preventDefault();
                await this.addAttendance(new FormData(e.target));
                modal.remove();
            });
        } catch (error) {
            common.showError("Không thể tải danh sách nhân viên: " + error.message);
        }
    }

    async addAttendance(formData) {
        try {
            common.showLoading();
            
            const data = {
                user_id: formData.get("user_id"),
                attendance_date: formData.get("attendance_date"),
                attendance_symbol: formData.get("attendance_symbol"),
                notes: formData.get("notes")
            };

            await api.attendance.create(data);
            common.showSuccess("Thêm chấm công thành công");
            this.loadTodayAttendance();
        } catch (error) {
            common.showError("Không thể thêm chấm công: " + error.message);
        } finally {
            common.hideLoading();
        }
    }

    async editAttendance(id) {
        try {
            const record = await api.attendance.getById(id);
            
            const modal = document.createElement("div");
            modal.className = "modal";
            modal.innerHTML = `
                <div class="modal-content">
                    <h2>Sửa chấm công</h2>
                    <form id="editAttendanceForm">
                        <input type="hidden" name="attendance_id" value="${id}">
                        <div class="form-group">
                            <label>Nhân viên</label>
                            <input type="text" value="${record.user_name}" disabled>
                        </div>
                        <div class="form-group">
                            <label>Ngày</label>
                            <input type="date" name="attendance_date" value="${record.attendance_date}" required>
                        </div>
                        <div class="form-group">
                            <label>Ký hiệu</label>
                            <select name="attendance_symbol" required>
                                <option value="P" ${record.attendance_symbol === 'P' ? 'selected' : ''}>P - Có mặt</option>
                                <option value="A" ${record.attendance_symbol === 'A' ? 'selected' : ''}>A - Vắng mặt</option>
                                <option value="L" ${record.attendance_symbol === 'L' ? 'selected' : ''}>L - Đi muộn</option>
                                <option value="E" ${record.attendance_symbol === 'E' ? 'selected' : ''}>E - Về sớm</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Ghi chú</label>
                            <textarea name="notes">${record.notes || ''}</textarea>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Lưu</button>
                            <button type="button" class="btn btn-secondary" onclick="this.closest('.modal').remove()">Hủy</button>
                        </div>
                    </form>
                </div>
            `;
            
            document.body.appendChild(modal);
            
            // Handle form submission
            document.getElementById("editAttendanceForm").addEventListener("submit", async (e) => {
                e.preventDefault();
                await this.updateAttendance(new FormData(e.target));
                modal.remove();
            });
        } catch (error) {
            common.showError("Không thể tải thông tin chấm công: " + error.message);
        }
    }

    async updateAttendance(formData) {
        try {
            common.showLoading();
            
            const id = formData.get("attendance_id");
            const data = {
                attendance_date: formData.get("attendance_date"),
                attendance_symbol: formData.get("attendance_symbol"),
                notes: formData.get("notes")
            };

            await api.attendance.update(id, data);
            common.showSuccess("Cập nhật chấm công thành công");
            this.loadTodayAttendance();
        } catch (error) {
            common.showError("Không thể cập nhật chấm công: " + error.message);
        } finally {
            common.hideLoading();
        }
    }

    async deleteAttendance(id) {
        if (confirm("Bạn có chắc chắn muốn xóa bản ghi chấm công này?")) {
            try {
                await api.attendance.delete(id);
                common.showSuccess("Xóa chấm công thành công");
                this.loadTodayAttendance();
            } catch (error) {
                common.showError("Không thể xóa chấm công: " + error.message);
            }
        }
    }

    async exportAttendance() {
        try {
            common.showLoading();
            
            const today = this.currentDate.toISOString().split('T')[0];
            const params = {
                attendance_date: today
            };

            const response = await api.attendance.getAll(params);
            
            // Create CSV content
            let csvContent = "Nhân viên,Phòng ban,Chức vụ,Ngày,Thời gian,Ký hiệu,Ghi chú\n";
            
            response.forEach(record => {
                csvContent += `"${record.user_name}","${record.department_name}","${record.position_name}",`;
                csvContent += `"${record.attendance_date}","${record.recorded_at}","${record.attendance_symbol}","${record.notes || ''}"\n`;
            });
            
            // Create download link
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement("a");
            link.href = URL.createObjectURL(blob);
            link.download = `attendance_${today}.csv`;
            link.click();
            
            common.hideLoading();
        } catch (error) {
            common.hideLoading();
            common.showError("Không thể xuất dữ liệu: " + error.message);
        }
    }
}

// Initialize AttendanceCheck
window.attendanceCheck = new AttendanceCheck();

// Update current date display
function updateCurrentDate() {
    const dateElement = document.getElementById('currentDate');
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    dateElement.textContent = currentDate.toLocaleDateString('vi-VN', options);
}

// Set up event listeners
function setupEventListeners() {
    // Date navigation
    document.getElementById('prevDay').addEventListener('click', () => {
        currentDate.setDate(currentDate.getDate() - 1);
        updateCurrentDate();
        loadAttendanceData();
    });

    document.getElementById('nextDay').addEventListener('click', () => {
        currentDate.setDate(currentDate.getDate() + 1);
        updateCurrentDate();
        loadAttendanceData();
    });

    // Filter changes
    document.getElementById('departmentFilter').addEventListener('change', loadAttendanceData);
    document.getElementById('statusFilter').addEventListener('change', loadAttendanceData);
    document.getElementById('searchInput').addEventListener('input', debounce(loadAttendanceData, 300));

    // Bulk check-in modal
    document.getElementById('bulkCheckInBtn').addEventListener('click', showBulkCheckInModal);
    document.getElementById('confirmBulkCheckIn').addEventListener('click', handleBulkCheckIn);

    // Export button
    document.getElementById('exportBtn').addEventListener('click', exportAttendanceData);

    // Pagination
    document.querySelector('.pagination').addEventListener('click', handlePagination);
}

// Load attendance data
async function loadAttendanceData() {
    showLoading();
    try {
        const department = document.getElementById('departmentFilter').value;
        const status = document.getElementById('statusFilter').value;
        const search = document.getElementById('searchInput').value;
        
        const response = await fetch(`/api/attendance?page=${currentPage}&department=${department}&status=${status}&search=${search}&date=${formatDate(currentDate)}`);
        const data = await response.json();
        
        if (data.success) {
            renderAttendanceTable(data.data);
            updatePagination(data.total, data.per_page);
        } else {
            showError(data.message);
        }
    } catch (error) {
        showError('Có lỗi xảy ra khi tải dữ liệu');
        console.error('Error loading attendance data:', error);
    } finally {
        hideLoading();
    }
}

// Render attendance table
function renderAttendanceTable(employees) {
    const tbody = document.querySelector('#attendanceTable tbody');
    tbody.innerHTML = '';

    employees.forEach(employee => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${employee.employee_id}</td>
            <td>${employee.name}</td>
            <td>${employee.department}</td>
            <td>${employee.position}</td>
            <td>
                <span class="status-badge status-${employee.status.toLowerCase().replace(' ', '-')}">
                    ${employee.status}
                </span>
            </td>
            <td>${employee.check_in_time || '-'}</td>
            <td>${employee.check_out_time || '-'}</td>
            <td>
                <div class="action-buttons">
                    ${employee.status === 'Chưa điểm danh' ? 
                        `<button class="btn-check-in" onclick="handleCheckIn('${employee.employee_id}')">
                            <i class="fas fa-sign-in-alt"></i> Check-in
                        </button>` : ''}
                    ${employee.status === 'Đã check-in' ? 
                        `<button class="btn-check-out" onclick="handleCheckOut('${employee.employee_id}')">
                            <i class="fas fa-sign-out-alt"></i> Check-out
                        </button>` : ''}
                </div>
            </td>
        `;
        tbody.appendChild(row);
    });
}

// Handle check-in
async function handleCheckIn(employeeId) {
    showLoading();
    try {
        const response = await fetch('/api/attendance/check-in', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                employee_id: employeeId,
                date: formatDate(currentDate)
            })
        });

        const data = await response.json();
        if (data.success) {
            showSuccess('Check-in thành công');
            loadAttendanceData();
        } else {
            showError(data.message);
        }
    } catch (error) {
        showError('Có lỗi xảy ra khi check-in');
        console.error('Error during check-in:', error);
    } finally {
        hideLoading();
    }
}

// Handle check-out
async function handleCheckOut(employeeId) {
    showLoading();
    try {
        const response = await fetch('/api/attendance/check-out', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                employee_id: employeeId,
                date: formatDate(currentDate)
            })
        });

        const data = await response.json();
        if (data.success) {
            showSuccess('Check-out thành công');
            loadAttendanceData();
        } else {
            showError(data.message);
        }
    } catch (error) {
        showError('Có lỗi xảy ra khi check-out');
        console.error('Error during check-out:', error);
    } finally {
        hideLoading();
    }
}

// Show bulk check-in modal
function showBulkCheckInModal() {
    const modal = new bootstrap.Modal(document.getElementById('bulkCheckInModal'));
    modal.show();
}

// Handle bulk check-in
async function handleBulkCheckIn() {
    showLoading();
    try {
        const selectedIds = Array.from(selectedEmployees);
        const response = await fetch('/api/attendance/bulk-check-in', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                employee_ids: selectedIds,
                date: formatDate(currentDate)
            })
        });

        const data = await response.json();
        if (data.success) {
            showSuccess('Check-in hàng loạt thành công');
            loadAttendanceData();
            bootstrap.Modal.getInstance(document.getElementById('bulkCheckInModal')).hide();
        } else {
            showError(data.message);
        }
    } catch (error) {
        showError('Có lỗi xảy ra khi check-in hàng loạt');
        console.error('Error during bulk check-in:', error);
    } finally {
        hideLoading();
    }
}

// Export attendance data
async function exportAttendanceData() {
    showLoading();
    try {
        const department = document.getElementById('departmentFilter').value;
        const status = document.getElementById('statusFilter').value;
        const search = document.getElementById('searchInput').value;
        
        const response = await fetch(`/api/attendance/export?department=${department}&status=${status}&search=${search}&date=${formatDate(currentDate)}`);
        
        if (response.ok) {
            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `attendance_report_${formatDate(currentDate)}.xlsx`;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            a.remove();
        } else {
            showError('Có lỗi xảy ra khi xuất báo cáo');
        }
    } catch (error) {
        showError('Có lỗi xảy ra khi xuất báo cáo');
        console.error('Error exporting data:', error);
    } finally {
        hideLoading();
    }
}

// Handle pagination
function handlePagination(event) {
    if (event.target.classList.contains('page-link')) {
        event.preventDefault();
        const page = event.target.dataset.page;
        if (page) {
            currentPage = parseInt(page);
            loadAttendanceData();
        }
    }
}

// Update pagination UI
function updatePagination(total, perPage) {
    totalPages = Math.ceil(total / perPage);
    const pagination = document.querySelector('.pagination');
    let html = '';

    // Previous button
    html += `
        <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" data-page="${currentPage - 1}">Trước</a>
        </li>
    `;

    // Page numbers
    for (let i = 1; i <= totalPages; i++) {
        html += `
            <li class="page-item ${currentPage === i ? 'active' : ''}">
                <a class="page-link" href="#" data-page="${i}">${i}</a>
            </li>
        `;
    }

    // Next button
    html += `
        <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
            <a class="page-link" href="#" data-page="${currentPage + 1}">Sau</a>
        </li>
    `;

    pagination.innerHTML = html;
}

// Utility functions
function formatDate(date) {
    return date.toISOString().split('T')[0];
}

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

// UI feedback functions
function showLoading() {
    document.querySelector('.loading-spinner').style.display = 'flex';
}

function hideLoading() {
    document.querySelector('.loading-spinner').style.display = 'none';
}

function showError(message) {
    const errorElement = document.querySelector('.error-message');
    errorElement.textContent = message;
    errorElement.style.display = 'block';
    setTimeout(() => {
        errorElement.style.display = 'none';
    }, 3000);
}

function showSuccess(message) {
    const successElement = document.querySelector('.success-message');
    successElement.textContent = message;
    successElement.style.display = 'block';
    setTimeout(() => {
        successElement.style.display = 'none';
    }, 3000);
} 