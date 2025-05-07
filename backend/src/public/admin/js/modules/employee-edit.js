class EmployeeEdit {
    constructor() {
        this.form = document.getElementById('employeeForm');
        this.departmentSelect = document.getElementById('department');
        this.positionSelect = document.getElementById('position');
        this.profilePictureInput = document.getElementById('profilePicture');
        this.profilePicturePreview = document.getElementById('profilePicturePreview');
        this.loadingSpinner = document.getElementById('loadingSpinner');
        this.errorMessage = document.getElementById('errorMessage');
        this.successMessage = document.getElementById('successMessage');
        this.changeHistoryBody = document.getElementById('changeHistoryBody');

        this.employeeId = new URLSearchParams(window.location.search).get('id');
        this.originalData = null;
        this.changes = [];

        this.initializeElements();
        this.loadEmployeeData();
        this.loadDepartments();
        this.setupEventListeners();
    }

    initializeElements() {
        // Initialize form elements
        this.formElements = {
            fullName: this.form.elements['full_name'],
            gender: this.form.elements['gender'],
            birthDate: this.form.elements['birth_date'],
            phone: this.form.elements['phone'],
            email: this.form.elements['email'],
            address: this.form.elements['address'],
            department: this.form.elements['department_id'],
            position: this.form.elements['position_id'],
            status: this.form.elements['status'],
            joinDate: this.form.elements['join_date'],
            username: this.form.elements['username'],
            password: this.form.elements['password']
        };
    }

    async loadEmployeeData() {
        if (!this.employeeId) {
            this.showError('Không tìm thấy ID nhân viên');
            return;
        }

        this.showLoading();
        try {
            const response = await fetch(`/api/employees/${this.employeeId}`);
            if (!response.ok) throw new Error('Failed to load employee data');
            
            const data = await response.json();
            this.originalData = data.data;
            
            // Populate form with employee data
            this.populateForm(data.data);
            
            // Load change history
            this.loadChangeHistory();
        } catch (error) {
            this.showError('Lỗi khi tải thông tin nhân viên');
            console.error(error);
        } finally {
            this.hideLoading();
        }
    }

    populateForm(data) {
        // Set form values
        this.formElements.fullName.value = data.full_name;
        this.formElements.gender.value = data.gender;
        this.formElements.birthDate.value = data.birth_date;
        this.formElements.phone.value = data.phone;
        this.formElements.email.value = data.email;
        this.formElements.address.value = data.address;
        this.formElements.department.value = data.department_id;
        this.formElements.status.value = data.status;
        this.formElements.joinDate.value = data.join_date;
        this.formElements.username.value = data.username;
        
        // Set profile picture
        if (data.profile_picture) {
            this.profilePicturePreview.src = data.profile_picture;
        }

        // Load positions for the selected department
        if (data.department_id) {
            this.loadPositions(data.department_id, data.position_id);
        }
    }

    async loadDepartments() {
        try {
            const response = await fetch('/api/departments');
            if (!response.ok) throw new Error('Failed to load departments');
            
            const data = await response.json();
            this.departments = data.data;
            
            // Populate department select
            this.departmentSelect.innerHTML = `
                <option value="">Chọn phòng ban</option>
                ${this.departments.map(dept => `
                    <option value="${dept.id}">${dept.name}</option>
                `).join('')}
            `;
        } catch (error) {
            this.showError('Lỗi khi tải danh sách phòng ban');
            console.error(error);
        }
    }

    async loadPositions(departmentId, selectedPositionId = null) {
        try {
            const response = await fetch(`/api/positions?department_id=${departmentId}`);
            if (!response.ok) throw new Error('Failed to load positions');
            
            const data = await response.json();
            this.positions = data.data;
            
            // Populate position select
            this.positionSelect.innerHTML = `
                <option value="">Chọn vị trí</option>
                ${this.positions.map(pos => `
                    <option value="${pos.id}" ${selectedPositionId === pos.id ? 'selected' : ''}>
                        ${pos.name}
                    </option>
                `).join('')}
            `;
        } catch (error) {
            this.showError('Lỗi khi tải danh sách vị trí');
            console.error(error);
        }
    }

    async loadChangeHistory() {
        try {
            const response = await fetch(`/api/employees/${this.employeeId}/history`);
            if (!response.ok) throw new Error('Failed to load change history');
            
            const data = await response.json();
            this.renderChangeHistory(data.data);
        } catch (error) {
            this.showError('Lỗi khi tải lịch sử thay đổi');
            console.error(error);
        }
    }

    renderChangeHistory(history) {
        this.changeHistoryBody.innerHTML = history.map(change => `
            <tr>
                <td>${new Date(change.created_at).toLocaleString()}</td>
                <td>${this.getFieldLabel(change.field)}</td>
                <td>${change.old_value || '-'}</td>
                <td>${change.new_value || '-'}</td>
                <td>${change.changed_by}</td>
            </tr>
        `).join('');
    }

    getFieldLabel(field) {
        const labels = {
            'full_name': 'Họ và tên',
            'gender': 'Giới tính',
            'birth_date': 'Ngày sinh',
            'phone': 'Số điện thoại',
            'email': 'Email',
            'address': 'Địa chỉ',
            'department_id': 'Phòng ban',
            'position_id': 'Vị trí',
            'status': 'Trạng thái',
            'profile_picture': 'Ảnh đại diện'
        };
        return labels[field] || field;
    }

    setupEventListeners() {
        // Department change event
        this.departmentSelect.addEventListener('change', () => {
            const departmentId = this.departmentSelect.value;
            if (departmentId) {
                this.loadPositions(departmentId);
            } else {
                this.positionSelect.innerHTML = '<option value="">Chọn vị trí</option>';
            }
        });

        // Profile picture change event
        this.profilePictureInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                if (this.validateImage(file)) {
                    this.previewImage(file);
                } else {
                    this.showError('Vui lòng chọn file ảnh hợp lệ (JPG, PNG, GIF)');
                    this.profilePictureInput.value = '';
                }
            }
        });

        // Form submit event
        this.form.addEventListener('submit', (e) => {
            e.preventDefault();
            if (this.validateForm()) {
                this.submitForm();
            }
        });

        // Form reset event
        this.form.addEventListener('reset', () => {
            this.profilePicturePreview.src = this.originalData.profile_picture || '/assets/images/default-avatar.png';
            this.clearValidation();
        });

        // Track changes
        Object.entries(this.formElements).forEach(([key, element]) => {
            element.addEventListener('change', () => {
                this.trackChange(key, element.value);
            });
        });
    }

    trackChange(field, newValue) {
        if (!this.originalData) return;

        const oldValue = this.originalData[field];
        if (oldValue !== newValue) {
            this.changes.push({
                field,
                old_value: oldValue,
                new_value: newValue
            });
        }
    }

    validateForm() {
        let isValid = true;
        this.clearValidation();

        // Validate required fields
        for (const [key, element] of Object.entries(this.formElements)) {
            if (element.required && !element.value.trim()) {
                this.setInvalid(element, 'Vui lòng nhập thông tin này');
                isValid = false;
            }
        }

        // Validate email format
        if (this.formElements.email.value && !this.isValidEmail(this.formElements.email.value)) {
            this.setInvalid(this.formElements.email, 'Email không hợp lệ');
            isValid = false;
        }

        // Validate phone format
        if (this.formElements.phone.value && !this.isValidPhone(this.formElements.phone.value)) {
            this.setInvalid(this.formElements.phone, 'Số điện thoại không hợp lệ');
            isValid = false;
        }

        // Validate birth date
        if (this.formElements.birthDate.value) {
            const birthDate = new Date(this.formElements.birthDate.value);
            const today = new Date();
            const age = today.getFullYear() - birthDate.getFullYear();
            
            if (age < 18) {
                this.setInvalid(this.formElements.birthDate, 'Nhân viên phải từ 18 tuổi trở lên');
                isValid = false;
            }
        }

        return isValid;
    }

    validateImage(file) {
        const validTypes = ['image/jpeg', 'image/png', 'image/gif'];
        const maxSize = 5 * 1024 * 1024; // 5MB

        if (!validTypes.includes(file.type)) {
            return false;
        }

        if (file.size > maxSize) {
            this.showError('Kích thước ảnh không được vượt quá 5MB');
            return false;
        }

        return true;
    }

    previewImage(file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            this.profilePicturePreview.src = e.target.result;
        };
        reader.readAsDataURL(file);
    }

    async submitForm() {
        if (this.changes.length === 0) {
            this.showError('Không có thay đổi nào để lưu');
            return;
        }

        this.showLoading();
        try {
            const formData = new FormData(this.form);
            
            const response = await fetch(`/api/employees/${this.employeeId}`, {
                method: 'PUT',
                body: formData
            });

            if (!response.ok) {
                const error = await response.json();
                throw new Error(error.message || 'Lỗi khi cập nhật thông tin nhân viên');
            }

            this.showSuccess('Cập nhật thông tin thành công');
            setTimeout(() => {
                window.location.href = '/admin/employees/list.html';
            }, 2000);
        } catch (error) {
            this.showError(error.message);
            console.error(error);
        } finally {
            this.hideLoading();
        }
    }

    setInvalid(element, message) {
        element.classList.add('is-invalid');
        const feedback = element.nextElementSibling;
        if (feedback && feedback.classList.contains('invalid-feedback')) {
            feedback.textContent = message;
        }
    }

    clearValidation() {
        for (const element of Object.values(this.formElements)) {
            element.classList.remove('is-invalid');
        }
    }

    isValidEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }

    isValidPhone(phone) {
        const re = /^[0-9]{10,11}$/;
        return re.test(phone);
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
        this.successMessage.querySelector('span').textContent = message;
        this.successMessage.style.display = 'block';
        setTimeout(() => {
            this.successMessage.style.display = 'none';
        }, 5000);
    }
}

// Initialize form when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.employeeEdit = new EmployeeEdit();
}); 