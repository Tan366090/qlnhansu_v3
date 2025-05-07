// Hiển thị form thêm nhân viên
function showFormOption() {
    document.getElementById('formOption').classList.remove('hidden');
    document.getElementById('fileOption').classList.add('hidden');
}

// Hiển thị form upload file
function showFileOption() {
    document.getElementById('fileOption').classList.remove('hidden');
    document.getElementById('formOption').classList.add('hidden');
}

let previewedEmployees = [];

// Thêm biến toàn cục để lưu dữ liệu phòng ban và chức vụ
let departments = [];
let positions = [];

// Hàm load dữ liệu phòng ban và chức vụ
async function loadDepartmentsAndPositions() {
    try {
        // Load phòng ban
        const deptResponse = await fetch('/qlnhansu_V2/backend/src/api/departments.php');
        if (!deptResponse.ok) {
            throw new Error('Không thể tải danh sách phòng ban');
        }
        const deptData = await deptResponse.json();
        departments = Array.isArray(deptData) ? deptData : deptData.data || [];

        // Load chức vụ
        const posResponse = await fetch('/qlnhansu_V2/backend/src/api/positions.php');
        if (!posResponse.ok) {
            throw new Error('Không thể tải danh sách chức vụ');
        }
        const posData = await posResponse.json();
        positions = Array.isArray(posData) ? posData : posData.data || [];

        return true;
    } catch (error) {
        console.error('Error loading departments and positions:', error);
        throw error;
    }
}

// Sửa lại hàm getDepartmentIdByName
function getDepartmentIdByName(departmentName) {
    const department = departments.find(d => d.name.toLowerCase() === departmentName.toLowerCase());
    return department ? department.id : null;
}

// Sửa lại hàm getPositionIdByName
function getPositionIdByName(positionName, departmentId) {
    const position = positions.find(p => 
        p.name.toLowerCase() === positionName.toLowerCase() && 
        p.department_id == departmentId
    );
    return position ? position.id : null;
}
// đúng
function parseFileContent(content) {
    try {
        const lines = content.split('\n').map(line => line.trim()).filter(line => line !== '');
        const employees = [];
        let currentEmployee = null;

        for (const line of lines) {
            const parts = line.split('|').map(p => p.trim());

            if (parts[0] === 'EMP') {
                // Nếu đang có nhân viên đang được parse, push vào mảng
                if (currentEmployee) {
                    employees.push(currentEmployee);
                }

                // Kiểm tra số lượng trường dữ liệu
                if (parts.length < 14) {
                    throw new Error(`Dòng EMP thiếu dữ liệu. Cần 14 trường, nhưng chỉ có ${parts.length} trường`);
                }

                // Parse dữ liệu nhân viên từ dòng EMP theo đúng thứ tự
                currentEmployee = {
                    employee_code: parts[1] || '',      // MNV
                    name: parts[2] || '',              // Tên
                    full_name: parts[3] || '',         // Họ và tên đầy đủ
                    email: parts[4] || '',             // Email
                    phone: parts[5] || '',             // Số điện thoại
                    birthday: parts[6] || '',          // Ngày sinh
                    address: parts[7] || '',           // Địa chỉ
                    department_name: parts[8] || '',   // Phòng ban
                    position_name: parts[9] || '',     // Chức vụ
                    contract_type: parts[10] || '',    // Loại hợp đồng
                    base_salary: parts[11] || '',      // Lương
                    contract_start_date: parts[12] || '', // Ngày bắt đầu
                    contract_end_date: parts[13] || '',  // Ngày kết thúc
                    family_members: []                  // Thành viên gia đình
                };

                // Kiểm tra các trường bắt buộc
                if (!currentEmployee.employee_code) {
                    throw new Error('Thiếu mã nhân viên');
                }
            } else if (parts[0] === 'FAM' && currentEmployee) {
                // Kiểm tra số lượng trường dữ liệu người thân
                if (parts.length < 6) {
                    throw new Error(`Dòng FAM thiếu dữ liệu. Cần 6 trường, nhưng chỉ có ${parts.length} trường`);
                }

                // Parse dữ liệu người thân theo đúng thứ tự
                const fam = {
                    name: parts[1] || '',          // Tên
                    relationship: parts[2] || '',   // Quan hệ
                    birthday: parts[3] || '',       // Ngày sinh
                    occupation: parts[4] || '',     // Nghề nghiệp
                    is_dependent: parts[5] === '1'  // Người phụ thuộc
                };

                // Kiểm tra các trường bắt buộc của người thân
                if (!fam.name || !fam.relationship) {
                    throw new Error('Thiếu thông tin bắt buộc của người thân: tên hoặc mối quan hệ');
                }

                currentEmployee.family_members.push(fam);
            } else if (parts[0] !== '') {
                throw new Error(`Dòng không hợp lệ: ${line}`);
            }
        }

        // Đừng quên push nhân viên cuối cùng
        if (currentEmployee) {
            employees.push(currentEmployee);
        }

        if (employees.length === 0) {
            throw new Error('Không tìm thấy dữ liệu nhân viên nào trong file');
        }

        return employees;
    } catch (error) {
        console.error('Lỗi khi parse file:', error);
        throw error;
    }
}

// Xem trước nội dung file
function previewFile() {
    hideUploadError();
    const fileInput = document.getElementById('employeeFile');
    const file = fileInput.files[0];
    
    if (!file) {
        alert('Vui lòng chọn file txt');
        return;
    }

    const reader = new FileReader();
    reader.onload = function(e) {
        const content = e.target.result;

        console.log("Raw file content:", content); // <-- Di chuyển dòng này vào đây

        const employees = parseFileContent(content);
        previewedEmployees = employees;
        displayPreview(employees);
        document.getElementById('saveToDbBtn').style.display = (employees.length > 0) ? 'inline-block' : 'none';
    };
    reader.readAsText(file);
}

// Hiển thị xem trước dữ liệu
function displayPreview(employees) {
    const tbody = document.getElementById('previewTableBody');
    tbody.innerHTML = '';
    employees.forEach(employee => {
        const famHtml = (employee.family_members && employee.family_members.length > 0)
            ? `<ul style='margin:0;padding-left:18px;'>` + employee.family_members.map(fam =>
                `<li><b>${fam.name}</b> (${fam.relationship})${fam.birthday ? ', ' + fam.birthday : ''}${fam.occupation ? ', ' + fam.occupation : ''}${fam.is_dependent ? ', Người phụ thuộc' : ''}</li>`
            ).join('') + '</ul>'
            : '<i>Không có</i>';
        const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${employee.employee_code}</td>
                <td>${employee.name}</td>
                <td>${employee.full_name}</td>
                <td>${employee.email}</td>
                <td>${employee.phone}</td>
                <td>${employee.birthday}</td>
                <td>${employee.address}</td>
                <td>${employee.department_name}</td>
                <td>${employee.position_name}</td>
                <td>${employee.contract_type}</td>
                <td>${employee.base_salary}</td>
                <td>${employee.contract_start_date}</td>
                <td>${employee.contract_end_date}</td>
                <td>${famHtml}</td>
            `;
        tbody.appendChild(tr);
    });
    document.getElementById('previewSection').classList.remove('hidden');
}

function showUploadError(message) {
    const errDiv = document.getElementById('uploadError');
    errDiv.innerHTML = message;
    errDiv.style.display = 'block';
    // Cuộn lên đầu modal
    const modal = document.getElementById('addEmployeeByFileModal');
    if (modal) modal.scrollTop = 0;
}

function hideUploadError() {
    const errDiv = document.getElementById('uploadError');
    errDiv.innerHTML = '';
    errDiv.style.display = 'none';
}

// Hàm thông minh xử lý dữ liệu nhân viên
function smartProcessEmployeeData(employeeData) {
    console.log("Debug - Bắt đầu xử lý dữ liệu thông minh:", employeeData);
    
    // Tạo bản sao để không ảnh hưởng đến dữ liệu gốc
    const processedData = { ...employeeData };

    // 1. Xử lý tên thông minh
    if (!processedData.name || !processedData.name.trim()) {
        if (processedData.fullName && processedData.fullName.trim()) {
            // Tách tên từ họ và tên đầy đủ
            const nameParts = processedData.fullName.trim().split(' ');
            processedData.name = nameParts[nameParts.length - 1];
            console.log("Debug - Tự động tách tên từ họ và tên:", processedData.name);
        } else {
            // Tạo tên thông minh dựa trên thời gian và mã nhân viên
            const timestamp = new Date().getTime();
            const employeeCode = processedData.employee_code || '';
            processedData.name = `NV${timestamp}${employeeCode.slice(-3)}`;
            processedData.fullName = processedData.name;
            console.log("Debug - Tạo tên thông minh:", processedData.name);
        }
    }

    // 2. Xử lý email thông minh
    if (!processedData.email || !processedData.email.trim()) {
        const cleanName = processedData.name.toLowerCase().replace(/[^a-z0-9]/g, '');
        const domain = '@company.com';
        processedData.email = `${cleanName}${domain}`;
        console.log("Debug - Tạo email tự động:", processedData.email);
    }

    // 3. Xử lý số điện thoại thông minh
    if (processedData.phone) {
        // Loại bỏ tất cả ký tự không phải số
        const cleanPhone = processedData.phone.replace(/\D/g, '');
        
        // Kiểm tra và thêm mã quốc gia nếu cần
        if (cleanPhone.length === 9 && !cleanPhone.startsWith('0')) {
            processedData.phone = `0${cleanPhone}`;
        } else if (cleanPhone.length === 10 && cleanPhone.startsWith('0')) {
            processedData.phone = cleanPhone;
        } else {
            // Tạo số điện thoại mặc định nếu không hợp lệ
            processedData.phone = '0123456789';
        }
        console.log("Debug - Xử lý số điện thoại:", processedData.phone);
    }

    // 4. Xử lý phòng ban và chức vụ thông minh
    if (!processedData.department || !processedData.position) {
        // Tự động tìm phòng ban và chức vụ phù hợp
        const defaultDepartment = departments.find(d => d.name.toLowerCase().includes('default')) || departments[0];
        const defaultPosition = positions.find(p => p.department_id === defaultDepartment.id) || positions[0];
        
        processedData.department = defaultDepartment.id;
        processedData.position = defaultPosition.id;
        console.log("Debug - Gán phòng ban và chức vụ mặc định:", {
            department: defaultDepartment.name,
            position: defaultPosition.name
        });
    }

    // 5. Xử lý ngày tháng thông minh
    const today = new Date();
    if (!processedData.contract_start_date) {
        processedData.contract_start_date = formatDate(today);
    }
    if (!processedData.contract_end_date) {
        // Mặc định hợp đồng 1 năm
        const oneYearLater = new Date(today);
        oneYearLater.setFullYear(today.getFullYear() + 1);
        processedData.contract_end_date = formatDate(oneYearLater);
    }

    // 6. Xử lý lương thông minh
    if (!processedData.base_salary || isNaN(parseFloat(processedData.base_salary))) {
        // Lấy mức lương trung bình của vị trí
        const position = positions.find(p => p.id === processedData.position);
        processedData.base_salary = position?.base_salary || 0;
        console.log("Debug - Gán lương mặc định:", processedData.base_salary);
    }

    console.log("Debug - Dữ liệu sau khi xử lý thông minh:", processedData);
    return processedData;
}

// Hàm tự động thử lại khi gặp lỗi
async function autoRetryWithFix(employeeData, maxRetries = 3) {
    let retryCount = 0;
    let lastError = null;

    console.log("data: ")

    while (retryCount < maxRetries) {
        try {
            console.log(`Debug - Thử lưu dữ liệu lần ${retryCount + 1}`);
            
            // Xử lý dữ liệu thông minh
            const processedData = smartProcessEmployeeData(employeeData);

            // Log dữ liệu trước khi gửi
            console.log("Debug - Dữ liệu chuẩn bị gửi:", processedData);

            // Gửi dữ liệu lên API
            const response = await fetch('/qlnhansu_V2/backend/src/api/employees.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    action: "add_multiple",
                    employees: [processedData]
                })
            });

            // Log response
            console.log("Debug - API Response Status:", response.status);
            const responseText = await response.text();
            console.log("Debug - API Response Body:", responseText);

            if (!response.ok) {
                const errorJson = JSON.parse(responseText);
                lastError = errorJson;

                // Tự động sửa lỗi dựa trên thông báo lỗi
                if (errorJson.message.includes("Tên nhân viên không được để trống")) {
                    processedData.name = "Nhân viên " + (new Date().getTime());
                    processedData.fullName = processedData.name;
                    console.log("Debug - Tạo tên mặc định sau lỗi:", processedData.name);
                }

                retryCount++;
                if (retryCount < maxRetries) {
                    console.log("Debug - Đang thử lại sau khi sửa lỗi...");
                    continue;
                }
            }

            const result = JSON.parse(responseText);
            if (!result.success) {
                throw new Error(result.message || 'Không thể lưu dữ liệu');
            }
            return result;
        } catch (error) {
            console.error("Debug - Lỗi trong quá trình thử lại:", error);
            retryCount++;
            if (retryCount >= maxRetries) {
                throw error;
            }
        }
    }

    throw new Error(`Không thể lưu dữ liệu sau ${maxRetries} lần thử: ${lastError?.message || 'Unknown error'}`);
}

// Sửa lại hàm savePreviewedEmployees để sử dụng autoRetryWithFix
async function savePreviewedEmployees() {
    hideUploadError();

    if (!previewedEmployees || previewedEmployees.length === 0) {
        showUploadError('Không có dữ liệu để lưu');
        return;
    }

    const statusDiv = document.getElementById('uploadStatus');
    statusDiv.innerHTML = '<div class="alert alert-info">Đang tải dữ liệu phòng ban và chức vụ...</div>';

    try {
        // Load dữ liệu phòng ban và chức vụ trước
        await loadDepartmentsAndPositions();
        statusDiv.innerHTML = '<div class="alert alert-info">Đang lưu dữ liệu...</div>';

        console.log("Danh sách nhân viên trước khi lưu:", previewedEmployees);

        const employeesToSave = [];
        const errors = [];

        for (const emp of previewedEmployees) {
            try {
                // Điều chỉnh dữ liệu thông minh
                const adjustedEmployee = smartProcessEmployeeData(emp);
                
                // Kiểm tra và làm sạch dữ liệu
                const employeeCode = adjustedEmployee.employee_code?.trim();
                let name = adjustedEmployee.name?.trim();
                let fullName = adjustedEmployee.full_name?.trim();
                const email = adjustedEmployee.email?.trim();
                const phone = adjustedEmployee.phone?.trim();
                const departmentName = adjustedEmployee.department_name?.trim();
                const positionName = adjustedEmployee.position_name?.trim();

                // Đảm bảo có tên nhân viên
                if (!name) {
                    if (fullName) {
                        const nameParts = fullName.split(' ');
                        name = nameParts[nameParts.length - 1];
                    } else {
                        name = "Nhân viên " + (new Date().getTime());
                        fullName = name;
                    }
                }

                // Lấy ID phòng ban và chức vụ
                const departmentId = getDepartmentIdByName(departmentName);
                if (!departmentId) {
                    errors.push(`Không tìm thấy phòng ban: ${departmentName}`);
                    continue;
                }

                const positionId = getPositionIdByName(positionName, departmentId);
                if (!positionId) {
                    errors.push(`Không tìm thấy chức vụ: ${positionName} trong phòng ban ${departmentName}`);
                    continue;
                }

                // Định dạng dữ liệu nhân viên
                const employeeData = {
                    name: name,
                    fullName: fullName || name,
                    phone: phone || '',
                    email: email || '',
                    department: departmentId,
                    position: positionId,
                    startDate: adjustedEmployee.contract_start_date ? formatDate(adjustedEmployee.contract_start_date) : null,
                    birthDate: adjustedEmployee.birthday ? formatDate(adjustedEmployee.birthday) : null,
                    address: adjustedEmployee.address?.trim() || '',
                    gender: adjustedEmployee.gender || 'other',
                    contract_type: adjustedEmployee.contract_type?.trim() || 'Permanent',
                    base_salary: parseFloat(adjustedEmployee.base_salary) || 0,
                    contract_start_date: adjustedEmployee.contract_start_date ? formatDate(adjustedEmployee.contract_start_date) : null,
                    contract_end_date: adjustedEmployee.contract_end_date ? formatDate(adjustedEmployee.contract_end_date) : null
                };

                // Đảm bảo name không bị trống
                if (!employeeData.name.trim()) {
                    employeeData.name = "Nhân viên " + (new Date().getTime());
                    employeeData.fullName = employeeData.name;
                }

                console.log("Debug - Final employee data to save:", employeeData);

                // Thử lưu dữ liệu với tự động sửa lỗi
                const result = await autoRetryWithFix(employeeData);
                if (result.success) {
                    employeesToSave.push(employeeData);
                } else {
                    errors.push(`Không thể lưu nhân viên ${employeeCode || '[Không có mã]'}: ${result.message}`);
                }
            } catch (err) {
                errors.push(`Lỗi xử lý nhân viên ${emp.employee_code || '[Không có mã]'}: ${err.message}`);
            }
        }

        // Nếu có lỗi, hiển thị
        if (errors.length > 0) {
            console.error("Debug - Validation errors:", errors);
            showUploadError(errors.join('<br>'));
        }

        // Hiển thị kết quả
        if (employeesToSave.length > 0) {
            statusDiv.innerHTML = `
                <div class="alert alert-success">
                    <h5>Thêm nhân viên thành công</h5>
                    <p>Đã thêm ${employeesToSave.length} nhân viên</p>
                    ${errors.length > 0 ? `<p>Có ${errors.length} lỗi trong quá trình xử lý</p>` : ''}
                </div>
            `;
            hideUploadError();

            // Đóng modal sau 2 giây và reload danh sách nhân viên
            setTimeout(() => {
                closeAddEmployeeByFileModal();
                if (typeof loadEmployees === 'function') {
                    loadEmployees();
                }
            }, 2000);
        } else {
            throw new Error('Không thể thêm bất kỳ nhân viên nào');
        }
    } catch (error) {
        console.error('Lỗi khi lưu nhân viên:', error);
        showUploadError(error.message);
        statusDiv.innerHTML = '';
    }
}

// Thêm hàm để gắn sự kiện cho nút lưu
function attachSaveButtonEvent() {
    const saveButton = document.getElementById('saveToDbBtn');
    if (saveButton) {
        saveButton.addEventListener('click', async function(e) {
            e.preventDefault();
            await savePreviewedEmployees();
        });
    }
}

// Thêm hàm để gắn sự kiện cho nút xem trước
function attachPreviewButtonEvent() {
    const previewButton = document.getElementById('previewBtn');
    if (previewButton) {
        previewButton.addEventListener('click', function(e) {
            e.preventDefault();
            previewFile();
        });
    }
}

// Thêm hàm để gắn sự kiện cho nút đóng modal
function attachCloseModalEvent() {
    const closeButton = document.getElementById('closeModalBtn');
    if (closeButton) {
        closeButton.addEventListener('click', function(e) {
            e.preventDefault();
            closeAddEmployeeByFileModal();
        });
    }
}

// Thêm hàm để gắn sự kiện cho nút xuất Excel
function attachExportButtonEvent() {
    const exportButton = document.getElementById('exportBtn');
    if (exportButton) {
        exportButton.addEventListener('click', function(e) {
            e.preventDefault();
            exportEmployeesToExcel();
        });
    }
}

// Thêm hàm để gắn sự kiện cho nút tải lại dữ liệu
function attachReloadButtonEvent() {
    const reloadButton = document.getElementById('reloadBtn');
    if (reloadButton) {
        reloadButton.addEventListener('click', function(e) {
            e.preventDefault();
            reloadEmployeeData();
        });
    }
}

// Thêm hàm để gắn sự kiện cho nút thêm nhân viên bằng form
function attachAddEmployeeFormButtonEvent() {
    const addEmployeeFormBtn = document.getElementById('addEmployeeFormBtn');
    if (addEmployeeFormBtn) {
        addEmployeeFormBtn.addEventListener('click', function(e) {
            e.preventDefault();
            showAddEmployeeModal();
        });
    }
}

// Thêm hàm để gắn sự kiện cho nút thêm nhân viên bằng file
function attachAddEmployeeFileButtonEvent() {
    const addEmployeeFileBtn = document.getElementById('addEmployeeFileBtn');
    if (addEmployeeFileBtn) {
        addEmployeeFileBtn.addEventListener('click', function(e) {
            e.preventDefault();
            showAddEmployeeByFileModal();
        });
    }
}

// Thêm hàm để gắn sự kiện cho nút làm mới
function attachRefreshButtonEvent() {
    const refreshButton = document.getElementById('refreshBtn');
    if (refreshButton) {
        refreshButton.addEventListener('click', function(e) {
            e.preventDefault();
            refreshFileUpload();
        });
    }
}

// Cập nhật hàm DOMContentLoaded để gắn tất cả các sự kiện
document.addEventListener('DOMContentLoaded', function() {
    attachSaveButtonEvent();
    attachPreviewButtonEvent();
    attachCloseModalEvent();
    attachExportButtonEvent();
    attachReloadButtonEvent();
    attachAddEmployeeFormButtonEvent();
    attachAddEmployeeFileButtonEvent();
    attachRefreshButtonEvent();
    attachTestButtonEvent();
  
    const closeBtn = document.getElementById('closeFileModalBtn');
    if (closeBtn) {
        closeBtn.addEventListener('click', closeAddEmployeeByFileModal);
    }
    const cancelBtn = document.getElementById('cancelFileBtn');
    if (cancelBtn) {
        cancelBtn.addEventListener('click', closeAddEmployeeByFileModal);
    }
});

function closeAddEmployeeByFileModal() {
    const modal = document.getElementById('addEmployeeByFileModal');
    if (modal) {
        modal.classList.remove('active');
        // Reset form nếu có
        const form = document.getElementById('addEmployeeByFileForm');
        if (form) form.reset();
    }
}

// 