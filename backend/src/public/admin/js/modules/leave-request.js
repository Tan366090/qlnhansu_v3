// Check authentication first
if (!auth.checkAuth()) {
    window.location.href = "/login.html";
}

class LeaveRequestManager {
    constructor() {
        this.init();
    }

    async init() {
        await this.loadLeaveTypes();
        this.setupEventListeners();
    }

    async loadLeaveTypes() {
        try {
            common.showLoading();
            
            const response = await api.leaves.getTypes();
            const leaveTypeSelect = document.getElementById("leaveType");
            leaveTypeSelect.innerHTML = "";
            
            response.data.forEach(type => {
                const option = document.createElement("option");
                option.value = type.leave_type_id;
                option.textContent = type.name;
                leaveTypeSelect.appendChild(option);
            });
            
            common.hideLoading();
        } catch (error) {
            common.hideLoading();
            common.showError("Không thể tải danh sách loại nghỉ phép: " + error.message);
        }
    }

    setupEventListeners() {
        // Calculate days when dates change
        document.getElementById("startDate").addEventListener("change", () => {
            this.calculateDays();
        });

        document.getElementById("endDate").addEventListener("change", () => {
            this.calculateDays();
        });

        // Handle form submission
        document.getElementById("leaveRequestForm").addEventListener("submit", async (e) => {
            e.preventDefault();
            await this.submitLeaveRequest(new FormData(e.target));
        });

        // Back button
        document.getElementById("backBtn").addEventListener("click", () => {
            window.location.href = "dashboard-employee.html";
        });
    }

    calculateDays() {
        const startDate = new Date(document.getElementById("startDate").value);
        const endDate = new Date(document.getElementById("endDate").value);
        
        if (startDate && endDate && startDate <= endDate) {
            const diffTime = Math.abs(endDate - startDate);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
            document.getElementById("totalDays").textContent = diffDays;
        } else {
            document.getElementById("totalDays").textContent = "0";
        }
    }

    async submitLeaveRequest(formData) {
        try {
            if (!this.validateForm(formData)) return;

            common.showLoading();

            const data = {
                leave_type_id: formData.get("leaveType"),
                start_date: formData.get("startDate"),
                end_date: formData.get("endDate"),
                reason: formData.get("reason"),
                days: parseInt(document.getElementById("totalDays").textContent)
            };

            await api.leaves.create(data);
            common.showSuccess("Gửi đơn xin nghỉ thành công");
            window.location.href = "leaves.html";
        } catch (error) {
            common.showError("Không thể gửi đơn xin nghỉ: " + error.message);
        } finally {
            common.hideLoading();
        }
    }

    validateForm(formData) {
        const requiredFields = [
            "leaveType",
            "startDate",
            "endDate",
            "reason"
        ];

        for (const field of requiredFields) {
            if (!formData.get(field)) {
                common.showError(`Vui lòng nhập ${this.getFieldLabel(field)}`);
                return false;
            }
        }

        // Validate dates
        const startDate = new Date(formData.get("startDate"));
        const endDate = new Date(formData.get("endDate"));
        
        if (startDate > endDate) {
            common.showError("Ngày kết thúc phải sau ngày bắt đầu");
            return false;
        }

        // Validate reason length
        const reason = formData.get("reason");
        if (reason.length < 10) {
            common.showError("Lý do nghỉ phải có ít nhất 10 ký tự");
            return false;
        }

        return true;
    }

    getFieldLabel(field) {
        const labels = {
            leaveType: "loại nghỉ phép",
            startDate: "ngày bắt đầu",
            endDate: "ngày kết thúc",
            reason: "lý do nghỉ"
        };
        return labels[field] || field;
    }
}

// Initialize LeaveRequestManager
window.leaveRequestManager = new LeaveRequestManager(); 