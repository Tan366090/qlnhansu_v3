class EmployeeForm {
    constructor() {
        this.form = document.getElementById('employeeForm');
        this.departmentSelect = document.getElementById('department');
        this.positionSelect = document.getElementById('position');
        this.profilePictureInput = document.getElementById('profilePicture');
        this.profilePicturePreview = document.getElementById('profilePicturePreview');
        this.loadingSpinner = document.getElementById('loadingSpinner');
        this.errorMessage = document.getElementById('errorMessage');
        this.successMessage = document.getElementById('successMessage');

        this.initializeElements();
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
            username: this.form.elements['username'],
            password: this.form.elements['password']
        };
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

    async loadPositions(departmentId) {
        try {
            const response = await fetch(`/api/positions?department_id=${departmentId}`);
            if (!response.ok) throw new Error('Failed to load positions');
            
            const data = await response.json();
            this.positions = data.data;
            
            // Populate position select
            this.positionSelect.innerHTML = `
                <option value="">Chọn vị trí</option>
                ${this.positions.map(pos => `
                    <option value="${pos.id}">${pos.name}</option>
                `).join('')}
            `;
        } catch (error) {
            this.showError('Lỗi khi tải danh sách vị trí');
            console.error(error);
        }
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
            this.profilePicturePreview.src = '/assets/images/default-avatar.png';
            this.clearValidation();
        });
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
        this.showLoading();
        try {
            const formData = new FormData(this.form);
            
            // Convert FormData to JSON
            const jsonData = {};
            formData.forEach((value, key) => {
                jsonData[key] = value;
            });

            const response = await fetch('/api/employees', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(jsonData)
            });

            if (!response.ok) {
                const error = await response.json();
                throw new Error(error.message || 'Lỗi khi thêm nhân viên');
            }

            this.showSuccess('Thêm nhân viên thành công');
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
    window.employeeForm = new EmployeeForm();
}); 