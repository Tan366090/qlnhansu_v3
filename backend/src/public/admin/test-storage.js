// Test script to verify data fetching from storage
const StorageTest = {
    async testDataFetching() {
        console.log('Starting storage test...');
        // Xóa overlay cũ nếu có
        let oldOverlay = document.getElementById('storageTestOverlay');
        if (oldOverlay) oldOverlay.remove();
        // Tạo overlay và modal
        const overlay = document.createElement('div');
        overlay.id = 'storageTestOverlay';
        overlay.onclick = function(e) {
            if (e.target === overlay) overlay.remove();
        };
        const resultDiv = document.createElement('div');
        resultDiv.id = 'storageTestResult';
        resultDiv.innerHTML = '<div class="alert alert-info">Đang kiểm tra dữ liệu...</div>';
        // Nút đóng
        const closeBtn = document.createElement('button');
        closeBtn.className = 'close-btn';
        closeBtn.innerHTML = '&times;';
        closeBtn.onclick = () => overlay.remove();
        resultDiv.appendChild(closeBtn);
        overlay.appendChild(resultDiv);
        document.body.appendChild(overlay);
        try {
            const res = await fetch('/qlnhansu_V2/backend/src/public/admin/check_storage.php');
            const data = await res.json();
            if (!data.success) throw new Error(data.error || 'Lỗi không xác định');
            // Hiển thị dữ liệu
            let html = '';
            for (const [table, info] of Object.entries(data.data)) {
                html += `<div class=\"card mb-3\"><div class=\"card-header bg-warning\"><b>Bảng:</b> ${table} <span class='badge bg-secondary ms-2'>${info.count ?? 0} bản ghi</span></div>`;
                if (info.error) {
                    html += `<div class='card-body'><span class='text-danger'>${info.error}</span></div></div>`;
                    continue;
                }
                if (info.sample && info.sample.length > 0) {
                    html += '<div class=\"table-responsive\"><table class=\"table table-sm table-bordered mb-0\"><thead><tr>';
                    Object.keys(info.sample[0]).forEach(col => html += `<th>${col}</th>`);
                    html += '</tr></thead><tbody>';
                    info.sample.forEach(row => {
                        html += '<tr>';
                        Object.values(row).forEach(val => html += `<td>${val ?? ''}</td>`);
                        html += '</tr>';
                    });
                    html += '</tbody></table></div>';
                } else {
                    html += '<div class=\"card-body\"><i>Không có dữ liệu mẫu.</i></div>';
                }
                html += '</div>';
            }
            resultDiv.innerHTML += html || '<div class=\"alert alert-warning\">Không có dữ liệu để hiển thị.</div>';
            resultDiv.appendChild(closeBtn); // Đảm bảo nút đóng luôn trên cùng
        } catch (error) {
            resultDiv.innerHTML = `<div class='alert alert-danger'>Lỗi: ${error.message}</div>`;
            resultDiv.appendChild(closeBtn);
            console.error('Storage test failed:', error);
        }
    }
};

// Run test when page loads
document.addEventListener('DOMContentLoaded', () => {
    // Add test button to header
    const headerControls = document.querySelector('.header-controls');
    if (headerControls) {
        const testButton = document.createElement('button');
        testButton.className = 'btn btn-warning';
        testButton.innerHTML = '<i class="fas fa-vial"></i> Test Storage';
        testButton.onclick = () => StorageTest.testDataFetching();
        headerControls.appendChild(testButton);
    }
}); 