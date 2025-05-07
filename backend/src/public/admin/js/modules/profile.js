// Check authentication first
if (!auth.checkAuth()) {
    window.location.href = "/login.html";
}

class ProfileManager {
    constructor() {
        this.init();
    }

    init() {
        this.loadProfile();
        this.setupEventListeners();
    }

    setupEventListeners() {
        // Edit profile button
        document.getElementById("editProfileBtn").addEventListener("click", () => {
            this.showEditProfileModal();
        });

        // Change password button
        document.getElementById("changePasswordBtn").addEventListener("click", () => {
            window.location.href = "change-password.html";
        });

        // Back to dashboard button
        document.getElementById("backToDashboardBtn").addEventListener("click", () => {
            window.location.href = "dashboard-employee.html";
        });
    }

    async loadProfile() {
        try {
            common.showLoading();
            const response = await api.users.getProfile();
            this.displayProfile(response.data);
        } catch (error) {
            common.showError("Không thể tải thông tin hồ sơ: " + error.message);
        } finally {
            common.hideLoading();
        }
    }

    displayProfile(profile) {
        document.getElementById("employeeCode").textContent = profile.employeeCode;
        document.getElementById("fullName").textContent = profile.fullName;
        document.getElementById("email").textContent = profile.email;
        document.getElementById("phone").textContent = profile.phone;
        document.getElementById("department").textContent = profile.department.name;
        document.getElementById("position").textContent = profile.position.name;
        document.getElementById("joinDate").textContent = this.formatDate(profile.joinDate);
        document.getElementById("status").textContent = profile.status === "ACTIVE" ? "Đang làm việc" : "Đã nghỉ việc";
        
        // Set avatar
        const avatar = document.getElementById("avatar");
        if (profile.avatar) {
            avatar.src = profile.avatar;
        } else {
            avatar.src = "assets/images/default-avatar.png";
        }
    }

    showEditProfileModal() {
        const modal = document.getElementById("editProfileModal");
        modal.style.display = "block";

        // Load current profile data into form
        this.loadProfileDataIntoForm();

        // Close modal when clicking outside
        window.onclick = (event) => {
            if (event.target === modal) {
                modal.style.display = "none";
            }
        };

        // Handle form submission
        document.getElementById("editProfileForm").addEventListener("submit", async (e) => {
            e.preventDefault();
            await this.updateProfile(new FormData(e.target));
        });
    }

    async loadProfileDataIntoForm() {
        try {
            const response = await api.users.getProfile();
            const profile = response.data;

            document.getElementById("editFullName").value = profile.fullName;
            document.getElementById("editPhone").value = profile.phone;
            document.getElementById("editEmail").value = profile.email;
        } catch (error) {
            common.showError("Không thể tải thông tin hồ sơ: " + error.message);
        }
    }

    async updateProfile(formData) {
        try {
            if (!this.validateForm(formData)) return;

            common.showLoading();

            const data = {
                fullName: formData.get("fullName"),
                phone: formData.get("phone"),
                email: formData.get("email")
            };

            await api.users.updateProfile(data);
            common.showSuccess("Cập nhật hồ sơ thành công");
            
            // Reload profile
            await this.loadProfile();
            
            // Close modal
            document.getElementById("editProfileModal").style.display = "none";
        } catch (error) {
            common.showError("Không thể cập nhật hồ sơ: " + error.message);
        } finally {
            common.hideLoading();
        }
    }

    validateForm(formData) {
        const fullName = formData.get("fullName");
        const email = formData.get("email");
        const phone = formData.get("phone");

        if (!fullName) {
            common.showError("Vui lòng nhập họ tên");
            return false;
        }

        if (!email) {
            common.showError("Vui lòng nhập email");
            return false;
        }

        if (!this.isValidEmail(email)) {
            common.showError("Email không hợp lệ");
            return false;
        }

        if (!phone) {
            common.showError("Vui lòng nhập số điện thoại");
            return false;
        }

        if (!this.isValidPhone(phone)) {
            common.showError("Số điện thoại không hợp lệ");
            return false;
        }

        return true;
    }

    isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    isValidPhone(phone) {
        const phoneRegex = /^[0-9]{10,11}$/;
        return phoneRegex.test(phone);
    }

    formatDate(date) {
        return new Date(date).toLocaleDateString("vi-VN");
    }
}

// Initialize ProfileManager
window.profileManager = new ProfileManager(); 