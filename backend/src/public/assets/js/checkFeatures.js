// Danh sách các chức năng đang phát triển
const developingFeatures = {
    "forgot-password": "Chức năng Quên mật khẩu đang phát triển",
    "attendance": "Chức năng Chấm công đang phát triển",
    "reset-password": "Chức năng Reset mật khẩu mặc định đang phát triển",
    "bonus": "Chức năng Thêm thưởng đang phát triển",
    "export-salary": "Chức năng Xuất bảng lương đang phát triển",
    "salary-complaint": "Chức năng Khiếu nại lương đang phát triển"
};

// Hàm kiểm tra và vô hiệu hóa các chức năng đang phát triển
function checkDevelopingFeatures() {
    // Tìm tất cả các elements có data-feature attribute
    document.querySelectorAll("[data-feature]").forEach(element => {
        const featureKey = element.getAttribute("data-feature");
        if (developingFeatures[featureKey]) {
            // Vô hiệu hóa element
            element.disabled = true;
            element.classList.add("developing");
            
            // Thêm tooltip hoặc thông báo
            element.title = developingFeatures[featureKey];
            
            // Nếu là button hoặc link, thêm xử lý click
            if (element.tagName === "BUTTON" || element.tagName === "A") {
                element.addEventListener("click", (e) => {
                    e.preventDefault();
                    showDevelopingMessage(featureKey);
                });
            }
        }
    });
}

// Hàm hiển thị thông báo
function showDevelopingMessage(featureKey) {
    const message = developingFeatures[featureKey];
    // Kiểm tra xem đã có toast container chưa
    let toastContainer = document.getElementById("toast-container");
    if (!toastContainer) {
        toastContainer = document.createElement("div");
        toastContainer.id = "toast-container";
        document.body.appendChild(toastContainer);
    }

    // Tạo toast message
    const toast = document.createElement("div");
    toast.className = "toast";
    toast.innerHTML = `
        <div class="toast-content">
            <i class="fas fa-tools"></i>
            <div class="toast-message">${message}</div>
        </div>
    `;

    // Thêm toast vào container
    toastContainer.appendChild(toast);

    // Xóa toast sau 3 giây
    setTimeout(() => {
        toast.remove();
    }, 3000);
}

// CSS cho toast messages
const style = document.createElement("style");
style.textContent = `
    #toast-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 1000;
    }

    .toast {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        margin-bottom: 10px;
        padding: 15px;
        min-width: 280px;
        animation: slideIn 0.3s ease-in-out;
    }

    .toast-content {
        display: flex;
        align-items: center;
    }

    .toast i {
        margin-right: 10px;
        color: #f39c12;
    }

    .toast-message {
        color: #333;
        font-size: 14px;
    }

    .developing {
        opacity: 0.7;
        cursor: not-allowed !important;
    }

    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
`;

document.head.appendChild(style);

// Chạy kiểm tra khi trang đã load
document.addEventListener("DOMContentLoaded", checkDevelopingFeatures); 