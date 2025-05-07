// Function to load employees from API
async function loadEmployees() {
    try {
        const response = await fetch('/qlnhansu_V2/backend/src/api/employees.php');
        const data = await response.json();
        
        if (data.success) {
            displayEmployees(data.data);
            updateStatistics(data.data);
        } else {
            showError('Không thể tải danh sách nhân viên');
        }
    } catch (error) {
        console.error('Error loading employees:', error);
        showError('Có lỗi xảy ra khi tải danh sách nhân viên');
    }
}

// Function to display employees in the table
function displayEmployees(employees) {
    const tbody = document.querySelector('#employeeTable tbody');
    tbody.innerHTML = '';

    employees.forEach(employee => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${employee.employee_id}</td>
            <td>${employee.full_name}</td>
            <td>${employee.position_name}</td>
            <td>${employee.department_name}</td>
            <td>${formatDate(employee.hire_date)}</td>
            <td>${formatDate(employee.birth_date)}</td>
            <td>${employee.phone}</td>
            <td>${employee.email}</td>
            <td>${employee.address}</td>
            <td>
                <span class="badge ${employee.status === 'active' ? 'bg-success' : 'bg-danger'}">
                    ${employee.status === 'active' ? 'Đang làm việc' : 'Đã nghỉ việc'}
                </span>
            </td>
            <td>
                <div class="btn-group">
                    <button class="btn btn-sm btn-info" onclick="viewEmployee(${employee.employee_id})">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-warning" onclick="editEmployee(${employee.employee_id})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="deleteEmployee(${employee.employee_id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        `;
        tbody.appendChild(row);
    });
}

// Function to update statistics
function updateStatistics(employees) {
    const totalEmployees = employees.length;
    const activeEmployees = employees.filter(e => e.status === 'active').length;
    const inactiveEmployees = totalEmployees - activeEmployees;

    document.querySelector('#totalEmployees').textContent = totalEmployees;
    document.querySelector('#activeEmployees').textContent = activeEmployees;
    document.querySelector('#inactiveEmployees').textContent = inactiveEmployees;
}

// Function to format date
function formatDate(dateString) {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('vi-VN');
}

// Function to show error message
function showError(message) {
    const errorDiv = document.createElement('div');
    errorDiv.className = 'alert alert-danger';
    errorDiv.textContent = message;
    
    const container = document.querySelector('.table-container');
    container.insertBefore(errorDiv, container.firstChild);
    
    setTimeout(() => errorDiv.remove(), 5000);
}

// Function to view employee details
function viewEmployee(id) {
    window.location.href = `view.html?id=${id}`;
}

// Function to edit employee
function editEmployee(id) {
    window.location.href = `edit.html?id=${id}`;
}

// Function to delete employee
async function deleteEmployee(id) {
    if (confirm('Bạn có chắc chắn muốn xóa nhân viên này?')) {
        try {
            const response = await fetch(`/qlnhansu_V2/backend/src/api/employees.php?id=${id}`, {
                method: 'DELETE'
            });
            
            const data = await response.json();
            
            if (data.success) {
                showSuccess('Xóa nhân viên thành công');
                loadEmployees(); // Reload the list
            } else {
                showError(data.message || 'Không thể xóa nhân viên');
            }
        } catch (error) {
            console.error('Error deleting employee:', error);
            showError('Có lỗi xảy ra khi xóa nhân viên');
        }
    }
}

// Function to show success message
function showSuccess(message) {
    const successDiv = document.createElement('div');
    successDiv.className = 'alert alert-success';
    successDiv.textContent = message;
    
    const container = document.querySelector('.table-container');
    container.insertBefore(successDiv, container.firstChild);
    
    setTimeout(() => successDiv.remove(), 5000);
}

// Initialize when the page loads
document.addEventListener('DOMContentLoaded', () => {
    loadEmployees();
    
    // Add event listener for search
    document.querySelector('.search-box input').addEventListener('input', (e) => {
        const searchTerm = e.target.value.toLowerCase();
        const rows = document.querySelectorAll('#employeeTable tbody tr');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });
    
    // Add event listener for filter
    document.querySelector('.filter-box select').addEventListener('change', (e) => {
        const filterValue = e.target.value;
        const rows = document.querySelectorAll('#employeeTable tbody tr');
        
        rows.forEach(row => {
            if (filterValue === 'all') {
                row.style.display = '';
            } else {
                const status = row.querySelector('td:nth-child(10)').textContent;
                row.style.display = status.includes(filterValue) ? '' : 'none';
            }
        });
    });
}); 