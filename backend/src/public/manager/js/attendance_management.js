// Attendance Management System
class AttendanceManagement {
    constructor() {
        this.attendances = [];
        this.loadAttendances();
        this.setupAutoAttendance();
    }

    // Load attendances from API
    async loadAttendances() {
        try {
            const response = await fetch("/api/attendances");
            this.attendances = await response.json();
            this.displayAttendances();
        } catch (error) {
            console.error("Error loading attendances:", error);
            notificationSystem.addNotification("Lỗi", "Không thể tải danh sách chấm công", "error");
        }
    }

    // Display attendances in table
    displayAttendances() {
        const tbody = document.getElementById("attendanceList");
        if (!tbody) return;

        tbody.innerHTML = "";
        this.attendances.forEach(attendance => {
            const tr = document.createElement("tr");
            tr.innerHTML = `
                <td>${attendance.employee_name}</td>
                <td>${this.formatDate(attendance.date)}</td>
                <td>${this.formatTime(attendance.check_in)}</td>
                <td>${this.formatTime(attendance.check_out)}</td>
                <td>${this.calculateWorkingHours(attendance.check_in, attendance.check_out)}</td>
                <td>${this.getStatusBadge(attendance.status)}</td>
                <td>
                    <button class="btn btn-sm btn-primary" onclick="attendanceManagement.viewAttendance(${attendance.id})">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-warning" onclick="attendanceManagement.manualCheckIn(${attendance.id})">
                        <i class="fas fa-clock"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    // Setup auto attendance
    setupAutoAttendance() {
        // Check for abnormal attendance patterns
        this.checkAbnormalAttendance();

        // Auto check-in/out based on location
        if ("geolocation" in navigator) {
            navigator.geolocation.watchPosition(
                position => this.handleLocationChange(position),
                error => console.error("Error getting location:", error),
                { enableHighAccuracy: true }
            );
        }
    }

    // Check for abnormal attendance patterns
    checkAbnormalAttendance() {
        this.attendances.forEach(attendance => {
            if (attendance.status === "abnormal") {
                notificationSystem.addNotification(
                    "Cảnh báo",
                    `Điểm danh bất thường của ${attendance.employee_name} vào ngày ${this.formatDate(attendance.date)}`,
                    "warning",
                    `/attendance.html?id=${attendance.id}`
                );
            }
        });
    }

    // Handle location change
    async handleLocationChange(position) {
        const { latitude, longitude } = position.coords;
        const officeLocation = { lat: 10.762622, lng: 106.660172 }; // Example office location

        const distance = this.calculateDistance(
            latitude,
            longitude,
            officeLocation.lat,
            officeLocation.lng
        );

        if (distance <= 0.1) { // Within 100 meters of office
            await this.autoCheckIn();
        } else {
            await this.autoCheckOut();
        }
    }

    // Auto check-in
    async autoCheckIn() {
        try {
            const response = await fetch("/api/attendances/check-in", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                }
            });

            if (response.ok) {
                notificationSystem.addNotification("Thành công", "Đã tự động check-in", "success");
                this.loadAttendances();
            }
        } catch (error) {
            console.error("Error auto check-in:", error);
        }
    }

    // Auto check-out
    async autoCheckOut() {
        try {
            const response = await fetch("/api/attendances/check-out", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                }
            });

            if (response.ok) {
                notificationSystem.addNotification("Thành công", "Đã tự động check-out", "success");
                this.loadAttendances();
            }
        } catch (error) {
            console.error("Error auto check-out:", error);
        }
    }

    // Manual check-in
    async manualCheckIn(id) {
        try {
            const response = await fetch(`/api/attendances/${id}/check-in`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                }
            });

            if (response.ok) {
                notificationSystem.addNotification("Thành công", "Đã check-in thủ công", "success");
                this.loadAttendances();
            } else {
                throw new Error("Failed to check-in");
            }
        } catch (error) {
            console.error("Error manual check-in:", error);
            notificationSystem.addNotification("Lỗi", "Không thể check-in", "error");
        }
    }

    // View attendance details
    async viewAttendance(id) {
        try {
            const response = await fetch(`/api/attendances/${id}`);
            const attendance = await response.json();
            
            // Show attendance details in modal
            this.showAttendanceModal(attendance);
        } catch (error) {
            console.error("Error viewing attendance:", error);
            notificationSystem.addNotification("Lỗi", "Không thể xem chi tiết chấm công", "error");
        }
    }

    // Calculate distance between two points
    calculateDistance(lat1, lon1, lat2, lon2) {
        const R = 6371; // Earth's radius in kilometers
        const dLat = this.toRad(lat2 - lat1);
        const dLon = this.toRad(lon2 - lon1);
        const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                Math.cos(this.toRad(lat1)) * Math.cos(this.toRad(lat2)) *
                Math.sin(dLon/2) * Math.sin(dLon/2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        return R * c;
    }

    // Convert degrees to radians
    toRad(degrees) {
        return degrees * Math.PI / 180;
    }

    // Calculate working hours
    calculateWorkingHours(checkIn, checkOut) {
        if (!checkIn || !checkOut) return "-";
        
        const start = new Date(`2000-01-01T${checkIn}`);
        const end = new Date(`2000-01-01T${checkOut}`);
        const diff = (end - start) / (1000 * 60 * 60);
        
        return diff.toFixed(1) + " giờ";
    }

    // Get status badge
    getStatusBadge(status) {
        const badges = {
            "normal": "<span class=\"badge badge-success\">Bình thường</span>",
            "late": "<span class=\"badge badge-warning\">Đi muộn</span>",
            "early": "<span class=\"badge badge-info\">Về sớm</span>",
            "abnormal": "<span class=\"badge badge-danger\">Bất thường</span>"
        };
        return badges[status] || status;
    }

    // Format date
    formatDate(date) {
        return new Date(date).toLocaleDateString("vi-VN");
    }

    // Format time
    formatTime(time) {
        if (!time) return "-";
        return new Date(`2000-01-01T${time}`).toLocaleTimeString("vi-VN", {
            hour: "2-digit",
            minute: "2-digit"
        });
    }
}

// Initialize attendance management system
const attendanceManagement = new AttendanceManagement();
window.attendanceManagement = attendanceManagement; 