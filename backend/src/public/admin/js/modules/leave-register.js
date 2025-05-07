// Initialize date pickers
document.addEventListener('DOMContentLoaded', function() {
    // Initialize date pickers with Vietnamese locale
    flatpickr("#startDate", {
        locale: "vn",
        dateFormat: "d/m/Y",
        minDate: "today",
        onChange: function(selectedDates, dateStr, instance) {
            // Update end date minimum date
            endDatePicker.set("minDate", dateStr);
            calculateLeaveDays();
        }
    });

    const endDatePicker = flatpickr("#endDate", {
        locale: "vn",
        dateFormat: "d/m/Y",
        minDate: "today",
        onChange: function() {
            calculateLeaveDays();
        }
    });

    // Set up event listeners
    setupEventListeners();
});

// Set up event listeners
function setupEventListeners() {
    // Leave type change handler
    document.getElementById('leaveType').addEventListener('change', function() {
        updateLeaveInfo();
        loadRemainingDays();
    });

    // Form submission handler
    document.getElementById('leaveForm').addEventListener('submit', handleSubmit);
}

// Update leave information based on selected type
function updateLeaveInfo() {
    const leaveType = document.getElementById('leaveType').value;
    const infoElement = document.getElementById('leaveInfo');
    
    switch(leaveType) {
        case 'annual':
            infoElement.textContent = 'Nghỉ phép có lương, tối đa 12 ngày/năm';
            break;
        case 'sick':
            infoElement.textContent = 'Nghỉ ốm có lương, yêu cầu giấy khám bệnh';
            break;
        case 'unpaid':
            infoElement.textContent = 'Nghỉ không lương, không giới hạn số ngày';
            break;
        default:
            infoElement.textContent = 'Vui lòng chọn loại nghỉ để xem thông tin chi tiết';
    }
}

// Load remaining leave days
async function loadRemainingDays() {
    const leaveType = document.getElementById('leaveType').value;
    if (!leaveType) return;

    showLoading();
    try {
        const response = await fetch(`/api/leave/remaining-days?type=${leaveType}`);
        const data = await response.json();
        
        if (data.success) {
            document.getElementById('remainingDays').textContent = data.remaining_days;
        } else {
            showError(data.message);
        }
    } catch (error) {
        showError('Có lỗi xảy ra khi tải số ngày nghỉ còn lại');
        console.error('Error loading remaining days:', error);
    } finally {
        hideLoading();
    }
}

// Calculate number of leave days
function calculateLeaveDays() {
    const startDate = document.getElementById('startDate').value;
    const endDate = document.getElementById('endDate').value;
    
    if (startDate && endDate) {
        const start = new Date(startDate.split('/').reverse().join('-'));
        const end = new Date(endDate.split('/').reverse().join('-'));
        
        // Calculate working days (excluding weekends)
        let days = 0;
        let current = new Date(start);
        
        while (current <= end) {
            const day = current.getDay();
            if (day !== 0 && day !== 6) { // Not Saturday or Sunday
                days++;
            }
            current.setDate(current.getDate() + 1);
        }
        
        // Update leave info with calculated days
        const infoElement = document.getElementById('leaveInfo');
        const currentText = infoElement.textContent;
        infoElement.textContent = `${currentText} (Số ngày nghỉ: ${days} ngày)`;
    }
}

// Handle form submission
async function handleSubmit(event) {
    event.preventDefault();
    
    const formData = new FormData();
    formData.append('leave_type', document.getElementById('leaveType').value);
    formData.append('start_date', document.getElementById('startDate').value);
    formData.append('end_date', document.getElementById('endDate').value);
    formData.append('reason', document.getElementById('reason').value);
    
    const attachment = document.getElementById('attachment').files[0];
    if (attachment) {
        formData.append('attachment', attachment);
    }

    showLoading();
    try {
        const response = await fetch('/api/leave/register', {
            method: 'POST',
            body: formData
        });

        const data = await response.json();
        if (data.success) {
            showSuccess('Đăng ký nghỉ phép thành công');
            setTimeout(() => {
                window.location.href = '/admin/leave/list';
            }, 2000);
        } else {
            showError(data.message);
        }
    } catch (error) {
        showError('Có lỗi xảy ra khi đăng ký nghỉ phép');
        console.error('Error submitting leave request:', error);
    } finally {
        hideLoading();
    }
}

// Utility functions
function showLoading() {
    document.querySelector('.loading-spinner').style.display = 'flex';
}

function hideLoading() {
    document.querySelector('.loading-spinner').style.display = 'none';
}

function showError(message) {
    const errorElement = document.querySelector('.error-message');
    errorElement.textContent = message;
    errorElement.style.display = 'block';
    setTimeout(() => {
        errorElement.style.display = 'none';
    }, 3000);
}

function showSuccess(message) {
    const successElement = document.querySelector('.success-message');
    successElement.textContent = message;
    successElement.style.display = 'block';
    setTimeout(() => {
        successElement.style.display = 'none';
    }, 3000);
} 