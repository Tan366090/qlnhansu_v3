class PositionList {
    constructor() {
        this.currentPage = 1;
        this.pageSize = 10;
        this.totalPages = 1;
        this.positions = [];
        this.departments = [];
        this.searchTerm = '';
        this.selectedDepartment = '';
        this.selectedStatus = '';

        this.initializeElements();
        this.loadDepartments();
        this.loadPositions();
        this.setupEventListeners();
    }

    initializeElements() {
        // Table elements
        this.positionTable = document.getElementById('positionTable');
        this.positionTableBody = this.positionTable.querySelector('tbody');
        
        // Filter elements
        this.searchInput = document.getElementById('searchInput');
        this.departmentFilter = document.getElementById('departmentFilter');
        this.statusFilter = document.getElementById('statusFilter');
        
        // Pagination elements
        this.prevPageBtn = document.getElementById('prevPage');
        this.nextPageBtn = document.getElementById('nextPage');
        this.pageNumbers = document.getElementById('pageNumbers');
        
        // Modal elements
        this.positionModal = new bootstrap.Modal(document.getElementById('positionModal'));
        this.positionForm = document.getElementById('positionForm');
        this.savePositionBtn = document.getElementById('savePositionBtn');
        
        // Loading and error elements
        this.loadingSpinner = document.getElementById('loadingSpinner');
        this.errorMessage = document.getElementById('errorMessage');
    }

    async loadDepartments() {
        try {
            const response = await fetch('/api/departments');
            if (!response.ok) throw new Error('Failed to load departments');
            
            const data = await response.json();
            this.departments = data.data;
            
            // Populate department filter
            this.departmentFilter.innerHTML = `
                <option value="">Tất cả phòng ban</option>
                ${this.departments.map(dept => `
                    <option value="${dept.id}">${dept.name}</option>
                `).join('')}
            `;
        } catch (error) {
            this.showError('Lỗi khi tải danh sách phòng ban');
            console.error(error);
        }
    }

    async loadPositions() {
        this.showLoading();
        try {
            let url = `/api/positions?page=${this.currentPage}&pageSize=${this.pageSize}`;
            
            if (this.searchTerm) {
                url += `&search=${encodeURIComponent(this.searchTerm)}`;
            }
            if (this.selectedDepartment) {
                url += `&department=${this.selectedDepartment}`;
            }
            if (this.selectedStatus) {
                url += `&status=${this.selectedStatus}`;
            }

            const response = await fetch(url);
            if (!response.ok) throw new Error('Failed to load positions');
            
            const data = await response.json();
            this.positions = data.data.positions;
            this.totalPages = data.data.totalPages;
            
            this.renderPositions();
            this.renderPagination();
        } catch (error) {
            this.showError('Lỗi khi tải danh sách vị trí');
            console.error(error);
        } finally {
            this.hideLoading();
        }
    }

    renderPositions() {
        this.positionTableBody.innerHTML = this.positions.map(position => `
            <tr>
                <td>${position.id}</td>
                <td>${position.name}</td>
                <td>${this.getDepartmentName(position.department_id)}</td>
                <td>${position.description || '-'}</td>
                <td>${position.employee_count || 0}</td>
                <td>
                    <span class="status-badge ${position.status === 'active' ? 'active' : 'inactive'}">
                        ${position.status === 'active' ? 'Đang hoạt động' : 'Không hoạt động'}
                    </span>
                </td>
                <td>
                    <div class="action-buttons">
                        <button class="btn btn-info" onclick="positionList.editPosition(${position.id})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-danger" onclick="positionList.deletePosition(${position.id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    }

    renderPagination() {
        this.pageNumbers.innerHTML = '';
        
        for (let i = 1; i <= this.totalPages; i++) {
            const pageBtn = document.createElement('button');
            pageBtn.className = `page-number ${i === this.currentPage ? 'active' : ''}`;
            pageBtn.textContent = i;
            pageBtn.onclick = () => this.goToPage(i);
            this.pageNumbers.appendChild(pageBtn);
        }
        
        this.prevPageBtn.disabled = this.currentPage === 1;
        this.nextPageBtn.disabled = this.currentPage === this.totalPages;
    }

    getDepartmentName(departmentId) {
        const department = this.departments.find(d => d.id === departmentId);
        return department ? department.name : '-';
    }

    setupEventListeners() {
        // Search input
        this.searchInput.addEventListener('input', this.debounce(() => {
            this.searchTerm = this.searchInput.value;
            this.currentPage = 1;
            this.loadPositions();
        }, 300));

        // Department filter
        this.departmentFilter.addEventListener('change', () => {
            this.selectedDepartment = this.departmentFilter.value;
            this.currentPage = 1;
            this.loadPositions();
        });

        // Status filter
        this.statusFilter.addEventListener('change', () => {
            this.selectedStatus = this.statusFilter.value;
            this.currentPage = 1;
            this.loadPositions();
        });

        // Pagination buttons
        this.prevPageBtn.addEventListener('click', () => {
            if (this.currentPage > 1) {
                this.goToPage(this.currentPage - 1);
            }
        });

        this.nextPageBtn.addEventListener('click', () => {
            if (this.currentPage < this.totalPages) {
                this.goToPage(this.currentPage + 1);
            }
        });

        // Save position button
        this.savePositionBtn.addEventListener('click', () => this.savePosition());
    }

    goToPage(page) {
        this.currentPage = page;
        this.loadPositions();
    }

    async editPosition(id) {
        try {
            const response = await fetch(`/api/positions/${id}`);
            if (!response.ok) throw new Error('Failed to load position');
            
            const data = await response.json();
            const position = data.data;
            
            // Populate form
            this.positionForm.elements['id'].value = position.id;
            this.positionForm.elements['name'].value = position.name;
            this.positionForm.elements['department_id'].value = position.department_id;
            this.positionForm.elements['description'].value = position.description || '';
            this.positionForm.elements['status'].value = position.status;
            
            // Show modal
            document.getElementById('modalTitle').textContent = 'Chỉnh sửa vị trí';
            this.positionModal.show();
        } catch (error) {
            this.showError('Lỗi khi tải thông tin vị trí');
            console.error(error);
        }
    }

    async deletePosition(id) {
        if (!confirm('Bạn có chắc chắn muốn xóa vị trí này?')) return;

        try {
            const response = await fetch(`/api/positions/${id}`, {
                method: 'DELETE'
            });
            
            if (!response.ok) throw new Error('Failed to delete position');
            
            this.loadPositions();
            this.showSuccess('Xóa vị trí thành công');
        } catch (error) {
            this.showError('Lỗi khi xóa vị trí');
            console.error(error);
        }
    }

    async savePosition() {
        const formData = new FormData(this.positionForm);
        const data = Object.fromEntries(formData.entries());
        
        try {
            const method = data.id ? 'PUT' : 'POST';
            const url = data.id ? `/api/positions/${data.id}` : '/api/positions';
            
            const response = await fetch(url, {
                method,
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });
            
            if (!response.ok) throw new Error('Failed to save position');
            
            this.positionModal.hide();
            this.loadPositions();
            this.showSuccess(data.id ? 'Cập nhật vị trí thành công' : 'Thêm vị trí thành công');
        } catch (error) {
            this.showError('Lỗi khi lưu vị trí');
            console.error(error);
        }
    }

    showLoading() {
        this.loadingSpinner.style.display = 'flex';
    }

    hideLoading() {
        this.loadingSpinner.style.display = 'none';
    }

    showError(message) {
        this.errorMessage.querySelector('span').textContent = message;
        this.errorMessage.style.display = 'block';
        setTimeout(() => {
            this.errorMessage.style.display = 'none';
        }, 5000);
    }

    showSuccess(message) {
        // You can implement a success message display here
        console.log(message);
    }

    debounce(func, wait) {
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
}

// Initialize position list when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.positionList = new PositionList();
}); 