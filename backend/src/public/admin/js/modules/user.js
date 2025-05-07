// API URL
const API_URL = "http://localhost/QLNhanSu_version1";

// Handle form submission
document.getElementById("userForm").addEventListener("submit", async (e) => {
    e.preventDefault();

    // Get form data
    const formData = {
        username: document.getElementById("username").value.trim(),
        email: document.getElementById("email").value.trim(),
        password: document.getElementById("password").value,
        confirmPassword: document.getElementById("confirmPassword").value,
        role: document.getElementById("role").value,
        status: document.getElementById("status").value
    };

    // Validate form data
    if (!formData.username || !formData.email || !formData.password || !formData.confirmPassword || !formData.role) {
        showToast("Vui lòng điền đầy đủ thông tin", "error");
        return;
    }

    if (formData.password !== formData.confirmPassword) {
        showToast("Mật khẩu không khớp", "error");
        return;
    }

    try {
        const response = await fetch(`${API_URL}/api/auth/register.php`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "Accept": "application/json"
            },
            body: JSON.stringify(formData)
        });

        const result = await response.json();

        if (result.success) {
            showToast("Thêm user thành công", "success");
            setTimeout(() => {
                window.location.href = "list_user.html";
            }, 1500);
        } else {
            showToast(result.message || "Có lỗi xảy ra", "error");
        }
    } catch (error) {
        console.error("Error:", error);
        showToast("Lỗi kết nối server", "error");
    }
});

// Show toast message
function showToast(message, type = "success") {
    const toast = document.createElement("div");
    toast.className = `toast align-items-center text-white bg-${type === "success" ? "success" : "danger"} border-0`;
    toast.setAttribute("role", "alert");
    toast.setAttribute("aria-live", "assertive");
    toast.setAttribute("aria-atomic", "true");

    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    `;

    const toastContainer = document.createElement("div");
    toastContainer.className = "toast-container position-fixed bottom-0 end-0 p-3";
    toastContainer.appendChild(toast);
    document.body.appendChild(toastContainer);

    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();

    // Remove toast after it's hidden
    toast.addEventListener("hidden.bs.toast", () => {
        toastContainer.remove();
    });
} 