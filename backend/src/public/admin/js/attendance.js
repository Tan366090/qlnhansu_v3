document.addEventListener('DOMContentLoaded', function() {
    loadTodayAttendance();
});

function loadTodayAttendance() {
    fetch('/api/attendance/today')
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                displayAttendanceData(data.data);
            } else {
                console.error('Error loading attendance data:', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
}

function displayAttendanceData(attendanceList) {
    const tbody = document.querySelector('#todayAttendanceTable tbody');
    tbody.innerHTML = '';

    if (attendanceList.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center">Không có dữ liệu chấm công hôm nay</td>
            </tr>
        `;
        return;
    }

    attendanceList.forEach(item => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${item.employee_code || '-'}</td>
            <td>${item.employee_name || '-'}</td>
            <td>${item.department_name || '-'}</td>
            <td>${formatTime(item.check_in_time)}</td>
            <td>${formatTime(item.check_out_time)}</td>
            <td>
                <span class="badge ${getStatusClass(item.attendance_symbol)}">
                    ${getStatusText(item.attendance_symbol)}
                </span>
            </td>
            <td>${item.notes || '-'}</td>
        `;
        tbody.appendChild(row);
    });
}

function formatTime(time) {
    if (!time) return '-';
    return time;
}

function getStatusClass(symbol) {
    const classes = {
        'P': 'bg-success',
        'A': 'bg-danger',
        'L': 'bg-warning',
        'WFH': 'bg-info'
    };
    return classes[symbol] || 'bg-secondary';
}

function getStatusText(symbol) {
    const status = {
        'P': 'Có mặt',
        'A': 'Vắng mặt',
        'L': 'Nghỉ phép',
        'WFH': 'Làm việc từ xa'
    };
    return status[symbol] || 'Không xác định';
} 