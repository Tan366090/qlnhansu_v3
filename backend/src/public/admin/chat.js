// Thêm hàm xử lý chat
async function handleChat() {
    const chatInput = document.getElementById('chat-input');
    const chatMessages = document.getElementById('chat-messages');
    const query = chatInput.value.trim();
    
    if (!query) return;
    
    // Hiển thị tin nhắn người dùng
    appendMessage('user', query);
    chatInput.value = '';
    
    try {
        // Gọi API chat engine
        const response = await fetch('chat_engine.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ query })
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Hiển thị câu trả lời
            appendMessage('bot', result.response);
            
            // Nếu có dữ liệu liên quan, hiển thị thêm
            if (result.relevant_data) {
                displayRelevantData(result.relevant_data);
            }
        } else {
            appendMessage('error', 'Có lỗi xảy ra: ' + result.error);
        }
    } catch (error) {
        console.error('Lỗi:', error);
        appendMessage('error', 'Có lỗi xảy ra khi xử lý yêu cầu');
    }
}

function appendMessage(type, content) {
    const chatMessages = document.getElementById('chat-messages');
    const messageDiv = document.createElement('div');
    messageDiv.className = `message ${type}-message`;
    
    const messageContent = document.createElement('div');
    messageContent.className = 'message-content';
    messageContent.textContent = content;
    
    messageDiv.appendChild(messageContent);
    chatMessages.appendChild(messageDiv);
    
    // Cuộn xuống tin nhắn mới nhất
    chatMessages.scrollTop = chatMessages.scrollHeight;
}

function displayRelevantData(data) {
    const chatMessages = document.getElementById('chat-messages');
    const dataDiv = document.createElement('div');
    dataDiv.className = 'relevant-data';
    
    if (data.employees && data.employees.length > 0) {
        const employeesDiv = document.createElement('div');
        employeesDiv.className = 'data-section';
        employeesDiv.innerHTML = '<h4>Nhân viên liên quan:</h4>';
        
        const employeeList = document.createElement('ul');
        data.employees.forEach(emp => {
            const li = document.createElement('li');
            li.textContent = `${emp.name} (${emp.position_name})`;
            employeeList.appendChild(li);
        });
        
        employeesDiv.appendChild(employeeList);
        dataDiv.appendChild(employeesDiv);
    }
    
    if (data.departments && data.departments.length > 0) {
        const deptsDiv = document.createElement('div');
        deptsDiv.className = 'data-section';
        deptsDiv.innerHTML = '<h4>Phòng ban liên quan:</h4>';
        
        const deptList = document.createElement('ul');
        data.departments.forEach(dept => {
            const li = document.createElement('li');
            li.textContent = dept.name;
            deptList.appendChild(li);
        });
        
        deptsDiv.appendChild(deptList);
        dataDiv.appendChild(deptsDiv);
    }
    
    chatMessages.appendChild(dataDiv);
}

// Thêm event listener cho nút gửi
document.getElementById('send-button').addEventListener('click', handleChat);

// Thêm event listener cho phím Enter
document.getElementById('chat-input').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        handleChat();
    }
}); 