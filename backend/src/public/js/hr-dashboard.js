// HR Dashboard functionality
document.addEventListener("DOMContentLoaded", () => {
    // Load dashboard data
    loadDashboardData();
    
    // Set up logout button
    const logoutBtn = document.getElementById("logoutBtn");
    if (logoutBtn) {
        logoutBtn.addEventListener("click", handleLogout);
    }
});

async function loadDashboardData() {
    try {
        // Load new candidates
        const candidatesResponse = await fetch("/QLNhanSu_version1/api/hr/candidates/new.php", {
            headers: {
                "Accept": "application/json",
                "Content-Type": "application/json"
            }
        });

        if (!candidatesResponse.ok) {
            throw new Error(`HTTP error! status: ${candidatesResponse.status}`);
        }

        const candidatesData = await candidatesResponse.json();
        if (candidatesData.success) {
            updateCandidatesTable(candidatesData.data);
            document.getElementById("newCandidates").textContent = candidatesData.data.length;
        }

        // Load total employees
        const employeesResponse = await fetch("/QLNhanSu_version1/api/hr/employees/count.php");
        const employeesData = await employeesResponse.json();
        if (employeesData.success) {
            document.getElementById("totalEmployees").textContent = employeesData.count;
        }

        // Load training sessions
        const trainingResponse = await fetch("/QLNhanSu_version1/api/hr/training/sessions.php");
        const trainingData = await trainingResponse.json();
        if (trainingData.success) {
            document.getElementById("trainingSessions").textContent = trainingData.count;
        }

        // Load pending reviews
        const reviewsResponse = await fetch("/QLNhanSu_version1/api/hr/performance/pending.php");
        const reviewsData = await reviewsResponse.json();
        if (reviewsData.success) {
            document.getElementById("pendingReviews").textContent = reviewsData.count;
        }

    } catch (error) {
        console.error("Error loading dashboard data:", error);
        showError("Không thể tải dữ liệu dashboard. Vui lòng thử lại sau.");
    }
}

function updateCandidatesTable(candidates) {
    const tbody = document.getElementById("recentCandidates");
    if (!tbody) return;

    tbody.innerHTML = "";
    candidates.forEach(candidate => {
        const row = document.createElement("tr");
        row.innerHTML = `
            <td>${candidate.id}</td>
            <td>${candidate.name}</td>
            <td>${candidate.position}</td>
            <td>${formatDate(candidate.application_date)}</td>
            <td><span class="status-badge ${candidate.status.toLowerCase()}">${candidate.status}</span></td>
            <td>
                <button class="btn btn-sm btn-primary" onclick="viewCandidate(${candidate.id})">Xem</button>
                <button class="btn btn-sm btn-success" onclick="updateStatus(${candidate.id}, 'interview')">Phỏng vấn</button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString("vi-VN");
}

function showError(message) {
    const errorDiv = document.createElement("div");
    errorDiv.className = "alert alert-danger";
    errorDiv.textContent = message;
    document.querySelector(".main-content").prepend(errorDiv);
    setTimeout(() => errorDiv.remove(), 5000);
}

async function handleLogout() {
    try {
        const response = await fetch("/QLNhanSu_version1/api/auth/logout.php", {
            method: "POST",
            credentials: "include"
        });
        
        if (response.ok) {
            window.location.href = "/QLNhanSu_version1/login.html";
        } else {
            throw new Error("Logout failed");
        }
    } catch (error) {
        console.error("Logout error:", error);
        showError("Đăng xuất thất bại. Vui lòng thử lại.");
    }
} 