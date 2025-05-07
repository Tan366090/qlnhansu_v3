// Khởi tạo các biểu đồ
let departmentChart = null;
let positionChart = null;
let hiringTrendChart = null;
let ageDistributionChart = null;

// Hàm lấy dữ liệu thống kê
async function fetchDashboardStats() {
    try {
        const response = await fetch("/api/admin/dashboard/stats");
        if (!response.ok) {
            throw new Error("Lỗi khi lấy dữ liệu thống kê");
        }
        return await response.json();
    } catch (error) {
        console.error("Lỗi:", error);
        alert("Có lỗi xảy ra khi lấy dữ liệu thống kê");
    }
}

// Hàm cập nhật thống kê
function updateStats(data) {
    document.getElementById("totalEmployees").textContent = data.totalEmployees;
    document.getElementById("newEmployees").textContent = data.newEmployees;
    document.getElementById("resignedEmployees").textContent = data.resignedEmployees;
    document.getElementById("resignationRate").textContent = data.resignationRate + "%";
}

// Hàm tạo biểu đồ phân bố nhân viên theo phòng ban
function createDepartmentChart(data) {
    const ctx = document.getElementById("departmentChart").getContext("2d");
    
    if (departmentChart) {
        departmentChart.destroy();
    }
    
    departmentChart = new Chart(ctx, {
        type: "pie",
        data: {
            labels: data.departments.map(dept => dept.name),
            datasets: [{
                data: data.departments.map(dept => dept.count),
                backgroundColor: [
                    "#FF6384",
                    "#36A2EB",
                    "#FFCE56",
                    "#4BC0C0",
                    "#9966FF",
                    "#FF9F40"
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: "right"
                }
            }
        }
    });
}

// Hàm tạo biểu đồ số lượng nhân viên theo chức vụ
function createPositionChart(data) {
    const ctx = document.getElementById("positionChart").getContext("2d");
    
    if (positionChart) {
        positionChart.destroy();
    }
    
    positionChart = new Chart(ctx, {
        type: "bar",
        data: {
            labels: data.positions.map(pos => pos.name),
            datasets: [{
                label: "Số lượng nhân viên",
                data: data.positions.map(pos => pos.count),
                backgroundColor: "#36A2EB"
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

// Hàm tạo biểu đồ xu hướng tuyển dụng
function createHiringTrendChart(data) {
    const ctx = document.getElementById("hiringTrendChart").getContext("2d");
    
    if (hiringTrendChart) {
        hiringTrendChart.destroy();
    }
    
    hiringTrendChart = new Chart(ctx, {
        type: "line",
        data: {
            labels: data.hiringTrend.map(item => item.month),
            datasets: [{
                label: "Số nhân viên mới",
                data: data.hiringTrend.map(item => item.count),
                borderColor: "#FF6384",
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

// Hàm tạo biểu đồ phân bố độ tuổi
function createAgeDistributionChart(data) {
    const ctx = document.getElementById("ageDistributionChart").getContext("2d");
    
    if (ageDistributionChart) {
        ageDistributionChart.destroy();
    }
    
    ageDistributionChart = new Chart(ctx, {
        type: "line",
        data: {
            labels: data.ageDistribution.map(item => item.range),
            datasets: [{
                label: "Số nhân viên",
                data: data.ageDistribution.map(item => item.count),
                backgroundColor: "rgba(75, 192, 192, 0.2)",
                borderColor: "rgba(75, 192, 192, 1)",
                fill: true
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

// Khởi tạo dashboard
document.addEventListener("DOMContentLoaded", async () => {
    try {
        const data = await fetchDashboardStats();
        if (data) {
            updateStats(data);
            createDepartmentChart(data);
            createPositionChart(data);
            createHiringTrendChart(data);
            createAgeDistributionChart(data);
        }
    } catch (error) {
        console.error("Lỗi khi khởi tạo dashboard:", error);
    }
}); 

