// Employee Form Handler
class EmployeeFormHandler {
    constructor() {
        this.departmentSelect = document.getElementById('departmentId');
        this.positionSelect = document.getElementById('positionId');
        this.contractTypeSelect = document.getElementById('contractType');
        this.addEmployeeForm = document.getElementById('addEmployeeForm');
        this.saveButton = document.getElementById('saveEmployeeBtn');
        this.formInteracted = false;
        this.retryCount = 0;
        this.maxRetries = 3;
        this.autoSaveTimeout = null;
        this.lastSavedData = null;
        this.modalService = ModalService;
        
        if (!this.addEmployeeForm) {
            console.warn('Employee form not found');
            return;
        }

        this.initializeForm();
        this.initializeEventListeners();
        this.loadInitialData();
        this.setupAutoSave();
    }

    initializeForm() {
        // Ngăn chặn submit form mặc định
        this.addEmployeeForm.addEventListener('submit', (e) => {
            e.preventDefault();
        });

        // Thêm event listener cho nút save
        if (this.saveButton) {
            this.saveButton.addEventListener('click', () => this.validateAndSaveEmployee());
        }

        // Thêm event listener cho toàn bộ form
        this.addEmployeeForm.addEventListener('input', () => {
            this.formInteracted = true;
            this.handleAutoSave();
        });

        // Thêm validation real-time
        this.addEmployeeForm.querySelectorAll('input, select').forEach(input => {
            input.addEventListener('focus', () => {
                input.classList.remove('invalid', 'valid', 'pending');
            });
            input.addEventListener('blur', () => {
                if (input.value.trim() !== '') {
                    this.validateField(input);
                }
            });
            input.addEventListener('input', () => {
                if (input.value.trim() !== '') {
                    this.validateField(input);
                    this.syncNameFields(input);
                }
            });
        });
    }

    setupAutoSave() {
        // Thiết lập auto-save mỗi 30 giây nếu có thay đổi
        setInterval(() => {
            if (this.formInteracted) {
                this.handleAutoSave();
            }
        }, 30000);
    }

    handleAutoSave() {
        if (this.autoSaveTimeout) {
            clearTimeout(this.autoSaveTimeout);
        }

        this.autoSaveTimeout = setTimeout(async () => {
            try {
                const formData = this.getFormData();
                if (this.hasFormDataChanged(formData)) {
                    await this.autoSaveDraft(formData);
                }
            } catch (error) {
                console.warn('Auto-save failed:', error);
            }
        }, 5000); // Đợi 5 giây sau khi người dùng dừng nhập
    }

    hasFormDataChanged(newData) {
        return JSON.stringify(newData) !== JSON.stringify(this.lastSavedData);
    }

    async autoSaveDraft(formData) {
        try {
            const response = await fetch('/qlnhansu_V2/backend/src/api/drafts.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    type: 'employee_form',
                    data: formData
                })
            });

            if (response.ok) {
                this.lastSavedData = formData;
                this.showNotification('Đã tự động lưu bản nháp', 'info');
            }
        } catch (error) {
            console.warn('Failed to auto-save draft:', error);
        }
    }

    async validateAndSaveEmployee() {
        this.formInteracted = true;
        
        try {
            // Validate dữ liệu
            const validationResult = await this.validateFormData();
            if (!validationResult.isValid) {
                this.showValidationErrors(validationResult.errors);
                return;
            }

            // Xử lý dữ liệu thông minh
            const processedData = await this.processFormData(validationResult.data);
            
            // Thử lưu với retry
            const result = await this.saveWithRetry(processedData);
            
            if (result.success) {
                this.handleSuccess(result);
            } else {
                this.handleError(result);
            }
        } catch (error) {
            this.handleError(error);
        }
    }

    async validateFormData() {
        const formData = this.getFormData();
        const errors = [];
        let isValid = true;

        // Validate các trường bắt buộc
        const requiredFields = ['employeeName', 'employeeEmail', 'employeePhone', 'departmentId', 'positionId'];
        requiredFields.forEach(field => {
            if (!formData[field]?.trim()) {
                errors.push(`${this.getFieldLabel(field)} không được để trống`);
                isValid = false;
            }
        });

        // Kiểm tra riêng cho positionId
        if (formData.positionId === 'new') {
            const newPositionInput = document.getElementById('newPosition');
            const newPositionName = newPositionInput ? newPositionInput.value.trim() : '';
            if (!newPositionName) {
                errors.push('Vui lòng nhập tên chức vụ mới');
                isValid = false;
            }
        }

        // Validate định dạng
        if (formData.employeeEmail && !this.validateEmail(formData.employeeEmail)) {
            errors.push('Email không hợp lệ');
            isValid = false;
        }

        if (formData.employeePhone && !this.validatePhone(formData.employeePhone)) {
            errors.push('Số điện thoại không hợp lệ');
            isValid = false;
        }

        // Validate ngày tháng
        if (formData.employeeBirthday && !this.validateDate(formData.employeeBirthday)) {
            errors.push('Ngày sinh không hợp lệ');
            isValid = false;
        }

        return { isValid, errors, data: formData };
    }

    async processFormData(formData) {
        // Nếu chọn thêm chức vụ mới, tạo chức vụ mới trước khi gửi
        let positionId = formData.positionId;
        if (positionId === 'new') {
            const newPositionInput = document.getElementById('newPosition');
            const newPositionName = newPositionInput ? newPositionInput.value.trim() : '';
            const departmentId = formData.departmentId;
            if (newPositionName && departmentId) {
                // Gọi API tạo chức vụ mới
                try {
                    const response = await fetch('/qlnhansu_V2/backend/src/api/positions.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ name: newPositionName, department_id: departmentId })
                    });
                    const data = await response.json();
                    if (data.success && data.data && data.data.id) {
                        positionId = data.data.id;
                    } else {
                        throw new Error('Không thể tạo chức vụ mới: ' + (data.message || 'Lỗi không xác định'));
                    }
                } catch (err) {
                    throw new Error('Lỗi khi tạo chức vụ mới: ' + err.message);
                }
            } else {
                throw new Error('Vui lòng nhập tên chức vụ mới và chọn phòng ban');
            }
        }
        if (!positionId || positionId === 'new') {
            throw new Error('Chức vụ không được để trống hoặc chưa hợp lệ');
        }

        // Xử lý tên thông minh
        const nameData = this.processName(formData.employeeName, formData.employeeFullName);
        // Xử lý email thông minh
        const email = this.processEmail(formData.employeeEmail, nameData.name);
        // Xử lý số điện thoại thông minh
        const phone = this.processPhone(formData.employeePhone);
        // Xử lý ngày tháng thông minh
        const dates = this.processDates(formData);
        // Xử lý lương thông minh
        const salary = this.processSalary(formData.baseSalary, positionId);

        return {
            ...formData,
            ...nameData,
            email,
            phone,
            ...dates,
            base_salary: salary,
            positionId: positionId // Đảm bảo luôn là id hợp lệ
        };
    }

    processName(name, fullName) {
        if (!name && fullName) {
            const nameParts = fullName.split(' ');
            return {
                name: nameParts[nameParts.length - 1],
                full_name: fullName
            };
        } else if (!name) {
            const defaultName = `Nhân viên ${new Date().getTime()}`;
            return {
                name: defaultName,
                full_name: defaultName
            };
        }
        return {
            name: name,
            full_name: fullName || name
        };
    }

    processEmail(email, name) {
        if (!email) {
            const cleanName = name.toLowerCase().replace(/[^a-z0-9]/g, '');
            return `${cleanName}@company.com`;
        }
        return email;
    }

    processPhone(phone) {
        if (!phone) return '';
        const cleanPhone = phone.replace(/\D/g, '');
        if (cleanPhone.length === 9 && !cleanPhone.startsWith('0')) {
            return `0${cleanPhone}`;
        }
        return cleanPhone;
    }

    processDates(formData) {
        const today = new Date();
        const oneYearLater = new Date(today);
        oneYearLater.setFullYear(today.getFullYear() + 1);

        return {
            contract_start_date: formData.contractStartDate || this.formatDate(today),
            contract_end_date: formData.contractEndDate || this.formatDate(oneYearLater),
            date_of_birth: formData.employeeBirthday || null
        };
    }

    processSalary(baseSalary, positionId) {
        if (!baseSalary || isNaN(parseFloat(baseSalary))) {
            const position = positions.find(p => p.id === positionId);
            return position?.base_salary || 0;
        }
        return parseFloat(baseSalary);
    }

    async saveWithRetry(employeeData, maxRetries = 3) {
        let retryCount = 0;
        let lastError = null;

        while (retryCount < maxRetries) {
            try {
                console.log(`Attempt ${retryCount + 1} to save employee data`);
                
                const response = await fetch('/qlnhansu_V2/backend/src/api/employees.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(employeeData)
                });

                const result = await response.json();
                
                if (!response.ok) {
                    throw new Error(result.message || 'Lỗi khi lưu nhân viên');
                }

                return result;
            } catch (error) {
                console.error(`Save attempt ${retryCount + 1} failed:`, error);
                lastError = error;
                retryCount++;
                
                if (retryCount < maxRetries) {
                    await new Promise(resolve => setTimeout(resolve, 1000 * retryCount));
                    continue;
                }
                
                throw lastError;
            }
        }
    }

    handleSuccess(result) {
        this.showNotification('Thêm nhân viên thành công', 'success');
        this.closeModal();
        if (typeof loadEmployees === 'function') {
            loadEmployees();
        }
    }

    handleError(error) {
        console.error('Error saving employee:', error);
        this.showNotification(error.message || 'Có lỗi xảy ra khi thêm nhân viên', 'error');
    }

    showValidationErrors(errors) {
        errors.forEach(error => {
            this.showNotification(error, 'error');
        });
    }

    getFormData() {
        const formData = new FormData(this.addEmployeeForm);
        const data = {};
        formData.forEach((value, key) => {
            data[key] = value;
        });
        return data;
    }

    getFieldLabel(fieldId) {
        const labels = {
            employeeName: 'Tên nhân viên',
            employeeEmail: 'Email',
            employeePhone: 'Số điện thoại',
            departmentId: 'Phòng ban',
            positionId: 'Chức vụ'
        };
        return labels[fieldId] || fieldId;
    }

    validateEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    validatePhone(phone) {
        const phoneRegex = /^\d{10,15}$/;
        return phoneRegex.test(phone.replace(/\D/g, ''));
    }

    validateDate(date) {
        if (!date) return true;
        const parsedDate = new Date(date);
        return !isNaN(parsedDate.getTime());
    }

    formatDate(date) {
        if (!date) return null;
        const d = new Date(date);
        return d.toISOString().split('T')[0];
    }

    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }

    showModal() {
        this.modalService.show('addEmployeeModal');
        this.modalService.handleCloseButton('addEmployeeModal', 'closeModalBtn');
    }

    closeModal() {
        this.modalService.hide('addEmployeeModal');
        this.modalService.resetForm('addEmployeeForm');
    }

    initializeEventListeners() {
        // Khi phòng ban thay đổi, load lại chức vụ tương ứng
        this.departmentSelect.addEventListener('change', () => this.loadPositionsByDepartment());
        
        // Thêm validation cho các trường input
        this.addEmployeeForm.querySelectorAll('input, select').forEach(input => {
            input.addEventListener('input', () => {
                this.validateField(input);
                this.syncNameFields(input);
            });
            input.addEventListener('blur', () => this.validateField(input));
        });

        // Gán sự kiện cho nút Hủy và icon X
        const closeBtn = document.getElementById('closeModalBtn');
        if (closeBtn) {
            closeBtn.onclick = null; // Remove any existing click handlers
            closeBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.closeModal();
            });
        }

        // Gán sự kiện cho nút mở modal
        const addEmployeeFormBtn = document.getElementById('addEmployeeFormBtn');
        if (addEmployeeFormBtn) {
            addEmployeeFormBtn.addEventListener('click', () => this.showModal());
        }

        // Add event listener for cancel button
        const cancelBtn = document.getElementById('cancelEmployeeBtn');
        if (cancelBtn) {
            cancelBtn.addEventListener('click', () => {
                this.closeModal();
            });
        }

        // Add event listeners for file upload modal
        const closeFileModalBtn = document.getElementById('closeFileModalBtn');
        if (closeFileModalBtn) {
            closeFileModalBtn.addEventListener('click', () => {
                this.modalService.hide('addEmployeeByFileModal');
            });
        }

        const cancelFileBtn = document.getElementById('cancelFileBtn');
        if (cancelFileBtn) {
            cancelFileBtn.addEventListener('click', () => {
                this.modalService.hide('addEmployeeByFileModal');
            });
        }
    }

    // Hàm đồng bộ giữa trường name và full_name
    syncNameFields(input) {
        const fieldName = input.getAttribute('id');
        if (fieldName === 'employeeName' || fieldName === 'employeeFullName') {
            const nameInput = document.getElementById('employeeName');
            const fullNameInput = document.getElementById('employeeFullName');
            
            if (nameInput && fullNameInput) {
                const nameValue = nameInput.value.trim();
                const fullNameValue = fullNameInput.value.trim();

                if (fieldName === 'employeeName') {
                    // Nếu người dùng đang nhập name
                    if (nameValue && !fullNameValue) {
                        // Nếu name có giá trị và full_name trống, điền full_name
                        fullNameInput.value = nameValue;
                        this.validateField(fullNameInput);
                    }
                } else if (fieldName === 'employeeFullName') {
                    // Nếu người dùng đang nhập full_name
                    if (fullNameValue && !nameValue) {
                        // Nếu full_name có giá trị và name trống, điền name
                        nameInput.value = fullNameValue;
                        this.validateField(nameInput);
                    }
                }
            }
        }
    }

    validateField(input) {
        // Chỉ validate khi form đã được tương tác
        if (!this.formInteracted) {
            return;
        }

        const value = input.value.trim();
        const fieldName = input.getAttribute('id');
        let isValid = true;
        let message = '';

        switch(fieldName) {
            case 'employeeName':
            case 'employeeFullName':
                // Tên chỉ được chứa chữ cái, dấu cách và dấu tiếng Việt
                const nameRegex = /^[a-zA-ZÀ-ỹ\s]+$/;
                if (value.length < 2) {
                    isValid = false;
                    message = 'Tên phải có ít nhất 2 ký tự';
                } else if (!nameRegex.test(value)) {
                    isValid = false;
                    message = 'Tên chỉ được chứa chữ cái và dấu cách';
                } else {
                    message = 'Tên hợp lệ';
                }
                break;
            case 'employeeEmail':
                // Email phải đúng định dạng và có tên miền hợp lệ
                const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
                if (!emailRegex.test(value)) {
                    isValid = false;
                    message = 'Email phải có định dạng: example@domain.com';
                } else {
                    message = 'Email hợp lệ';
                }
                break;
            case 'employeePhone':
                // Số điện thoại phải bắt đầu bằng 0 và có 10 chữ số
                const phoneRegex = /^0[0-9]{9}$/;
                if (!phoneRegex.test(value)) {
                    isValid = false;
                    message = 'Số điện thoại phải bắt đầu bằng 0 và có 10 chữ số';
                } else {
                    message = 'Số điện thoại hợp lệ';
                }
                break;
            case 'employeeBirthday':
                if (value) {
                    const birthDate = new Date(value);
                    const today = new Date();
                    const age = today.getFullYear() - birthDate.getFullYear();
                    if (age < 18) {
                        isValid = false;
                        message = 'Nhân viên phải từ 18 tuổi trở lên';
                    } else if (age > 65) {
                        isValid = false;
                        message = 'Nhân viên không được quá 65 tuổi';
                    } else {
                        message = 'Tuổi hợp lệ';
                    }
                } else {
                    message = '';
                }
                break;
            case 'employeeAddress':
                // Địa chỉ phải có ít nhất 10 ký tự và không chứa ký tự đặc biệt
                const addressRegex = /^[a-zA-Z0-9À-ỹ\s.,-]{10,}$/;
                if (value && !addressRegex.test(value)) {
                    isValid = false;
                    message = 'Địa chỉ phải có ít nhất 10 ký tự và không chứa ký tự đặc biệt';
                } else {
                    message = 'Địa chỉ hợp lệ';
                }
                break;
            case 'departmentId':
                if (value === '') {
                    isValid = false;
                    message = 'Vui lòng chọn phòng ban';
                } else {
                    message = 'Đã chọn phòng ban';
                }
                break;
            case 'positionId':
                if (value === '') {
                    isValid = false;
                    message = 'Vui lòng chọn chức vụ';
                } else if (value === 'new') {
                    const newPosition = document.getElementById('newPosition').value.trim();
                    if (!newPosition) {
                        isValid = false;
                        message = 'Vui lòng nhập tên chức vụ mới';
                    } else if (newPosition.length < 3) {
                        isValid = false;
                        message = 'Tên chức vụ phải có ít nhất 3 ký tự';
                    } else {
                        message = 'Tên chức vụ hợp lệ';
                    }
                } else {
                    message = 'Đã chọn chức vụ';
                }
                break;
            case 'contractType':
                if (value === '') {
                    isValid = false;
                    message = 'Vui lòng chọn loại hợp đồng';
                } else {
                    message = 'Đã chọn loại hợp đồng';
                }
                break;
            case 'baseSalary':
                // Lương phải là số dương, tối thiểu 500 đồng và không quá 100 triệu
                const salary = parseFloat(value);
                if (isNaN(salary) || salary <= 0) {
                    isValid = false;
                    message = 'Lương phải là số dương';
                } else if (salary < 500) {
                    isValid = false;
                    message = 'Lương phải lớn hơn hoặc bằng 500 đồng';
                } else if (salary > 100000000) {
                    isValid = false;
                    message = 'Lương không được vượt quá 100 triệu';
                } else {
                    message = 'Lương hợp lệ';
                }
                break;
            case 'contractStartDate':
                if (value === '') {
                    isValid = false;
                    message = 'Vui lòng chọn ngày bắt đầu hợp đồng';
                } else {
                    const startDate = new Date(value);
                    const today = new Date();
                    if (startDate < today) {
                        isValid = false;
                        message = 'Ngày bắt đầu hợp đồng không được nhỏ hơn ngày hiện tại';
                    } else {
                        message = 'Ngày hợp lệ';
                    }
                }
                break;
            // Kiểm tra các trường của thành viên gia đình
            case 'member-name':
                if (value.length < 2) {
                    isValid = false;
                    message = 'Tên thành viên phải có ít nhất 2 ký tự';
                } else if (!/^[a-zA-ZÀ-ỹ\s]+$/.test(value)) {
                    isValid = false;
                    message = 'Tên thành viên chỉ được chứa chữ cái và dấu cách';
                } else {
                    message = 'Tên thành viên hợp lệ';
                }
                break;
            case 'relationship':
                if (value === '') {
                    isValid = false;
                    message = 'Vui lòng chọn mối quan hệ';
                } else {
                    message = 'Mối quan hệ hợp lệ';
                }
                break;
            case 'member-birthday':
                if (value) {
                    const memberBirthDate = new Date(value);
                    const today = new Date();
                    const age = today.getFullYear() - memberBirthDate.getFullYear();
                    if (age < 0) {
                        isValid = false;
                        message = 'Ngày sinh không được lớn hơn ngày hiện tại';
                    } else if (age > 120) {
                        isValid = false;
                        message = 'Tuổi không hợp lệ';
                    } else {
                        message = 'Ngày sinh hợp lệ';
                    }
                } else {
                    message = '';
                }
                break;
            case 'member-occupation':
                if (value && value.length < 2) {
                    isValid = false;
                    message = 'Nghề nghiệp phải có ít nhất 2 ký tự';
                } else if (value && !/^[a-zA-ZÀ-ỹ\s]+$/.test(value)) {
                    isValid = false;
                    message = 'Nghề nghiệp chỉ được chứa chữ cái và dấu cách';
                } else {
                    message = 'Nghề nghiệp hợp lệ';
                }
                break;
        }

        // Cập nhật trạng thái input
        input.classList.remove('valid', 'invalid', 'pending');
        
        if (value === '') {
            // Nếu trường bắt buộc và chưa nhập
            if (input.hasAttribute('required')) {
                input.classList.add('invalid');
                message = 'Trường này là bắt buộc';
            } else {
                // Nếu trường không bắt buộc và chưa nhập
                input.classList.add('pending');
                message = '';
            }
        } else {
            // Nếu đã nhập giá trị
            input.classList.add(isValid ? 'valid' : 'invalid');
        }

        // Cập nhật thông báo
        let messageElement = input.nextElementSibling;
        if (!messageElement || !messageElement.classList.contains('validation-message')) {
            messageElement = document.createElement('div');
            messageElement.className = 'validation-message';
            input.parentNode.insertBefore(messageElement, input.nextSibling);
        }
        messageElement.textContent = message;
        messageElement.className = 'validation-message ' + (value === '' && !input.hasAttribute('required') ? 'pending' : (isValid ? 'valid' : 'invalid'));
    }

    async loadInitialData() {
        try {
            // Load danh sách phòng ban
            await this.loadDepartments();
            
            // Load danh sách loại hợp đồng
            await this.loadContractTypes();
        } catch (error) {
            console.error('Error loading initial data:', error);
            this.showNotification('Có lỗi xảy ra khi tải dữ liệu', 'error');
        }
    }

    async loadDepartments() {
        try {
            const response = await fetch('/qlnhansu_V2/backend/src/api/departments.php');
            if (!response.ok) throw new Error('Failed to load departments');
            const data = await response.json();
            if (data.success) {
                const departmentSelect = document.getElementById('departmentId');
                departmentSelect.innerHTML = '<option value="">Chọn phòng ban</option>';
                data.data.forEach(dept => {
                    const option = document.createElement('option');
                    option.value = dept.id;
                    option.textContent = dept.name;
                    departmentSelect.appendChild(option);
                });
            }
        } catch (error) {
            console.error('Error loading departments:', error);
            this.showNotification('Không thể tải danh sách phòng ban', 'error');
        }
    }

    async loadPositionsByDepartment() {
        const departmentId = this.departmentSelect.value;
        
        if (!departmentId) {
            // Nếu không chọn phòng ban, reset chức vụ
            this.resetPositionSelect();
            return;
        }

        try {
            const response = await fetch(`/qlnhansu_V2/backend/src/api/positions.php?department_id=${departmentId}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            console.log('Positions Response:', data);

            // Lấy select element cho vị trí
            const positionSelect = document.getElementById('positionId');
            if (!positionSelect) return;

            // Reset options
            positionSelect.innerHTML = '<option value="">Chọn chức vụ</option>';
            
            // Thêm các vị trí vào select
            let positions = [];
            if (Array.isArray(data)) {
                positions = data;
            } else if (data.data && Array.isArray(data.data)) {
                positions = data.data;
            }

            // Nếu không có chức vụ nào, thêm option "Thêm chức vụ mới"
            if (positions.length === 0) {
                const newOption = document.createElement('option');
                newOption.value = 'new';
                newOption.textContent = '+ Thêm chức vụ mới';
                positionSelect.appendChild(newOption);
                
                // Hiển thị ô nhập chức vụ mới
                const newPositionGroup = document.getElementById('newPositionGroup');
                const newPositionInput = document.getElementById('newPosition');
                newPositionGroup.style.display = 'block';
                newPositionInput.required = true;
                newPositionInput.disabled = false;
                newPositionInput.style.backgroundColor = '#fff';
            } else {
                // Thêm các chức vụ hiện có
                positions.forEach(position => {
                    if (position && position.id && position.name) {
                        const option = document.createElement('option');
                        option.value = position.id;
                        option.textContent = position.name;
                        positionSelect.appendChild(option);
                    }
                });

                // Ẩn ô nhập chức vụ mới
                const newPositionGroup = document.getElementById('newPositionGroup');
                const newPositionInput = document.getElementById('newPosition');
                newPositionGroup.style.display = 'none';
                newPositionInput.value = '';
                newPositionInput.required = false;
                newPositionInput.disabled = true;
                newPositionInput.style.backgroundColor = '#f8f9fa';
            }

        } catch (error) {
            console.error('Error loading positions:', error);
            this.showNotification('Không thể tải danh sách chức vụ', 'error');
        }
    }

    async loadContractTypes() {
        try {
            const response = await fetch('/qlnhansu_V2/backend/src/api/contract_types.php');
            if (!response.ok) throw new Error('Failed to load contract types');
            const data = await response.json();
    
            if (data.success) {
                // Xóa các option cũ (giữ lại option mặc định)
                while (this.contractTypeSelect.options.length > 1) {
                    this.contractTypeSelect.remove(1);
                }
                // Thêm các loại hợp đồng mới
                data.data.forEach(contractType => {
                    const option = document.createElement('option');
                    option.value = contractType.name;
                    option.textContent = contractType.name;
                    this.contractTypeSelect.appendChild(option);
                });
            }
        } catch (error) {
            console.error('Error loading contract types:', error);
            this.showNotification('Không thể tải danh sách loại hợp đồng', 'error');
        }
    }

    resetPositionSelect() {
        while (this.positionSelect.options.length > 1) {
            this.positionSelect.remove(1);
        }
    }
}

// Khởi tạo khi DOM đã load xong
document.addEventListener('DOMContentLoaded', () => {
    new EmployeeFormHandler();
}); 
document.addEventListener('DOMContentLoaded', function() {
    var positionSelect = document.getElementById('positionId');
    if (positionSelect) {
        positionSelect.addEventListener('change', handlePositionChange);
    }
});