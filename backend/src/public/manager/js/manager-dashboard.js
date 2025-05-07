// Chart instances
let teamPerformanceChart;
let resourceChart;
let projectProgressChart;
let budgetChart;

// Chart colors
const chartColors = {
    primary: "#2563eb",
    secondary: "#10b981",
    tertiary: "#f59e0b",
    quaternary: "#ef4444",
    quinary: "#8b5cf6"
};

// Initialize dashboard
document.addEventListener("DOMContentLoaded", async () => {
    await fetchDashboardData();
    createCharts();
    setupChartInteractions();
});

// Fetch dashboard data
async function fetchDashboardData() {
    try {
        const response = await fetch("/api/manager/dashboard/stats");
        if (!response.ok) {
            throw new Error("Failed to fetch dashboard data");
        }
        const data = await response.json();
        updateStats(data.stats);
        updateCharts(data.charts);
    } catch (error) {
        console.error("Error fetching dashboard data:", error);
        showError("Không thể tải dữ liệu dashboard");
    }
}

// Update statistics
function updateStats(stats) {
    document.getElementById("totalEmployees").textContent = stats.totalEmployees;
    document.getElementById("attendanceRate").textContent = `${stats.attendanceRate}%`;
    document.getElementById("totalSalary").textContent = stats.totalSalary;
}

// Create charts
function createCharts() {
    // Team Performance Chart
    const teamCtx = document.getElementById("teamPerformanceChart").getContext("2d");
    teamPerformanceChart = new Chart(teamCtx, {
        type: "bar",
        data: {
            labels: ["Nhóm 1", "Nhóm 2", "Nhóm 3", "Nhóm 4", "Nhóm 5"],
            datasets: [{
                label: "Hiệu suất (%)",
                data: [85, 78, 92, 88, 80],
                backgroundColor: chartColors.primary,
                borderColor: chartColors.primary,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `Hiệu suất: ${context.raw}%`;
                        }
                    }
                }
            }
        }
    });

    // Resource Allocation Chart
    const resourceCtx = document.getElementById("resourceChart").getContext("2d");
    resourceChart = new Chart(resourceCtx, {
        type: "pie",
        data: {
            labels: ["Nhân sự", "Thiết bị", "Tài chính", "Thời gian"],
            datasets: [{
                data: [40, 25, 20, 15],
                backgroundColor: [
                    chartColors.primary,
                    chartColors.secondary,
                    chartColors.tertiary,
                    chartColors.quaternary
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `${context.label}: ${context.raw}%`;
                        }
                    }
                }
            }
        }
    });

    // Project Progress Chart
    const progressCtx = document.getElementById("projectProgressChart").getContext("2d");
    projectProgressChart = new Chart(progressCtx, {
        type: "line",
        data: {
            labels: ["Tuần 1", "Tuần 2", "Tuần 3", "Tuần 4", "Tuần 5"],
            datasets: [{
                label: "Tiến độ (%)",
                data: [20, 40, 60, 80, 100],
                backgroundColor: "rgba(37, 99, 235, 0.2)",
                borderColor: chartColors.primary,
                borderWidth: 2,
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100
                }
            },
            plugins: {
                zoom: {
                    zoom: {
                        wheel: {
                            enabled: true
                        },
                        pinch: {
                            enabled: true
                        },
                        mode: "x"
                    },
                    pan: {
                        enabled: true,
                        mode: "x"
                    }
                }
            }
        }
    });

    // Budget Allocation Chart
    const budgetCtx = document.getElementById("budgetChart").getContext("2d");
    budgetChart = new Chart(budgetCtx, {
        type: "doughnut",
        data: {
            labels: ["Nhân sự", "Thiết bị", "Đào tạo", "Khác"],
            datasets: [{
                data: [50, 25, 15, 10],
                backgroundColor: [
                    chartColors.primary,
                    chartColors.secondary,
                    chartColors.tertiary,
                    chartColors.quaternary
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `${context.label}: ${context.raw}%`;
                        }
                    }
                }
            }
        }
    });
}

// Setup chart interactions
function setupChartInteractions() {
    // Project Progress Chart Zoom Controls
    document.getElementById("zoomInBtn").addEventListener("click", () => {
        projectProgressChart.zoom(1.1);
    });

    document.getElementById("zoomOutBtn").addEventListener("click", () => {
        projectProgressChart.zoom(0.9);
    });

    document.getElementById("resetZoomBtn").addEventListener("click", () => {
        projectProgressChart.resetZoom();
    });

    // Chart Legends
    setupChartLegends();
}

// Setup chart legends
function setupChartLegends() {
    // Team Performance Legend
    const teamLegend = document.getElementById("teamPerformanceLegend");
    teamPerformanceChart.data.datasets[0].data.forEach((value, index) => {
        const legendItem = document.createElement("div");
        legendItem.className = "legend-item";
        legendItem.innerHTML = `
            <div class="legend-color" style="background-color: ${chartColors.primary}"></div>
            <span>${teamPerformanceChart.data.labels[index]}: ${value}%</span>
        `;
        teamLegend.appendChild(legendItem);
    });

    // Resource Allocation Legend
    const resourceLegend = document.getElementById("resourceLegend");
    resourceChart.data.datasets[0].data.forEach((value, index) => {
        const legendItem = document.createElement("div");
        legendItem.className = "legend-item";
        legendItem.innerHTML = `
            <div class="legend-color" style="background-color: ${resourceChart.data.datasets[0].backgroundColor[index]}"></div>
            <span>${resourceChart.data.labels[index]}: ${value}%</span>
        `;
        resourceLegend.appendChild(legendItem);
    });

    // Budget Allocation Legend
    const budgetLegend = document.getElementById("budgetLegend");
    budgetChart.data.datasets[0].data.forEach((value, index) => {
        const legendItem = document.createElement("div");
        legendItem.className = "legend-item";
        legendItem.innerHTML = `
            <div class="legend-color" style="background-color: ${budgetChart.data.datasets[0].backgroundColor[index]}"></div>
            <span>${budgetChart.data.labels[index]}: ${value}%</span>
        `;
        budgetLegend.appendChild(legendItem);
    });
}

// Update charts with new data
function updateCharts(chartData) {
    // Update Team Performance Chart
    teamPerformanceChart.data.datasets[0].data = chartData.teamPerformance;
    teamPerformanceChart.update();

    // Update Resource Allocation Chart
    resourceChart.data.datasets[0].data = chartData.resourceAllocation;
    resourceChart.update();

    // Update Project Progress Chart
    projectProgressChart.data.datasets[0].data = chartData.projectProgress;
    projectProgressChart.update();

    // Update Budget Allocation Chart
    budgetChart.data.datasets[0].data = chartData.budgetAllocation;
    budgetChart.update();
}

// Show error message
function showError(message) {
    const errorDiv = document.createElement("div");
    errorDiv.className = "error-message";
    errorDiv.textContent = message;
    document.querySelector(".main-content").prepend(errorDiv);
    setTimeout(() => errorDiv.remove(), 5000);
} 