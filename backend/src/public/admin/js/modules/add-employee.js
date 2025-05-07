// Check authentication first
if (!auth.checkAuth()) {
    window.location.href = "/login.html";
}

class AddEmployeeManager {
    constructor() {
        this.init();
    }

    async init() {
        await this.loadFilters();
        this.setupEventListeners();
    }

    async loadFilters() {
        try {
            common.showLoading();
            
            // Load departments
            const deptResponse = await api.departments.getAll();
            const departmentSelect = document.getElementById("department_id");
            departmentSelect.innerHTML = '<option value="">Chọn phòng ban</option>';
            deptResponse.data.forEach(dept => {
                const option = document.createElement("option");
                option.value = dept.department_id;
                option.textContent = dept.name;
                departmentSelect.appendChild(option);
            });

            // Load positions
            const posResponse = await api.positions.getAll();
            const positionSelect = document.getElementById("position_id");
            positionSelect.innerHTML = '<option value="">Chọn chức vụ</option>';
            posResponse.data.forEach(pos => {
                const option = document.createElement("option");
                option.value = pos.position_id;
                option.textContent = pos.name;
                positionSelect.appendChild(option);
            });

            common.hideLoading();
        } catch (error) {
            common.hideLoading();
            common.showError("Không thể tải danh sách phòng ban và chức vụ: " + error.message);
        }
    }

    setupEventListeners() {
        // Form submission
        document.getElementById("employeeForm").addEventListener("submit", async (e) => {
            e.preventDefault();
            await this.addEmployee(new FormData(e.target));
        });

        // Back button
        document.getElementById("backBtn").addEventListener("click", () => {
            window.location.href = "employee-list.html";
        });

        // Department change
        document.getElementById("department_id").addEventListener("change", async (e) => {
            await this.loadPositionsByDepartment(e.target.value);
        });
    }

    async loadPositionsByDepartment(departmentId) {
        try {
            const positionSelect = document.getElementById("position_id");
            positionSelect.innerHTML = '<option value="">Chọn chức vụ</option>';
            
            if (!departmentId) return;

            const response = await api.positions.getAll({ department_id: departmentId });
            response.data.forEach(pos => {
                const option = document.createElement("option");
                option.value = pos.position_id;
                option.textContent = pos.name;
                positionSelect.appendChild(option);
            });
        } catch (error) {
            console.error("Error loading positions:", error);
        }
    }

    async addEmployee(formData) {
        try {
            if (!this.validateForm(formData)) return;

            common.showLoading();

            const data = {
                employee_code: formData.get("employee_code"),
                username: formData.get("username"),
                password: formData.get("password"),
                full_name: formData.get("full_name"),
                email: formData.get("email"),
                phone: formData.get("phone"),
                department_id: formData.get("department_id"),
                position_id: formData.get("position_id"),
                gender: formData.get("gender"),
                birth_date: formData.get("birth_date"),
                address: formData.get("address"),
                is_active: true
            };

            await api.users.create(data);
            common.showSuccess("Thêm nhân viên thành công");
            window.location.href = "employee-list.html";
        } catch (error) {
            common.showError("Không thể thêm nhân viên: " + error.message);
        } finally {
            common.hideLoading();
        }
    }

    validateForm(formData) {
        const requiredFields = [
            "employee_code",
            "username",
            "password",
            "full_name",
            "email",
            "phone",
            "department_id",
            "position_id",
            "gender",
            "birth_date"
        ];

        for (const field of requiredFields) {
            if (!formData.get(field)) {
                common.showError(`Vui lòng nhập ${this.getFieldLabel(field)}`);
                return false;
            }
        }

        // Validate email format
        const email = formData.get("email");
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            common.showError("Email không hợp lệ");
            return false;
        }

        // Validate phone format
        const phone = formData.get("phone");
        if (!/^[0-9]{10,11}$/.test(phone)) {
            common.showError("Số điện thoại không hợp lệ");
            return false;
        }

        return true;
    }

    getFieldLabel(field) {
        const labels = {
            employee_code: "mã nhân viên",
            username: "tên đăng nhập",
            password: "mật khẩu",
            full_name: "họ và tên",
            email: "email",
            phone: "số điện thoại",
            department_id: "phòng ban",
            position_id: "chức vụ",
            gender: "giới tính",
            birth_date: "ngày sinh"
        };
        return labels[field] || field;
    }
}

// Initialize AddEmployeeManager
window.addEmployeeManager = new AddEmployeeManager(); 