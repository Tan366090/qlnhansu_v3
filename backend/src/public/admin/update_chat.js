// Hàm để cập nhật dữ liệu chat
function updateChatData() {
    const currentTime = new Date().toLocaleTimeString();
    console.log(`[${currentTime}] Đang cập nhật dữ liệu...`);
    
    fetch('update_chat_data.php')
        .then(response => response.json())
        .then(data => {
            // Hiển thị thời gian cập nhật thành công
            console.log(`[${new Date().toLocaleTimeString()}] Cập nhật thành công!`);
            console.log('Dữ liệu mới:', data);
            
            // Hiển thị thông báo trên trang web (nếu có element với id="updateStatus")
            const statusElement = document.getElementById('updateStatus');
            if (statusElement) {
                statusElement.innerHTML = `Cập nhật lần cuối: ${currentTime}`;
                statusElement.style.color = 'green';
            }
        })
        .catch(error => {
            console.error(`[${new Date().toLocaleTimeString()}] Lỗi khi cập nhật dữ liệu:`, error);
            
            // Hiển thị lỗi trên trang web (nếu có element với id="updateStatus")
            const statusElement = document.getElementById('updateStatus');
            if (statusElement) {
                statusElement.innerHTML = `Lỗi cập nhật: ${error.message}`;
                statusElement.style.color = 'red';
            }
        });
}

// Cập nhật dữ liệu ngay lập tức khi trang được tải
updateChatData();

// Cập nhật dữ liệu sau mỗi 3 giây
setInterval(updateChatData, 3000);

// Hiển thị thông báo khi script được tải
console.log('Script cập nhật dữ liệu đã được khởi động!'); 