// Test data mẫu
const testCases = [
    {
        name: "Test Case 1: Dữ liệu hợp lệ đầy đủ",
        data: {
            employeeName: "Đạt",
            employeeFullName: "Trần Tấn Đạt",
            employeeEmail: "dat.tran@example.com",
            employeePhone: "0987654321",
            employeeBirthday: "1995-05-15",
            employeeAddress: "123 Đường ABC, Quận 1, TP.HCM",
            departmentId: "1",
            positionId: "1",
            contractType: "1",
            baseSalary: "10000000",
            contractStartDate: "2024-03-20",
            contractEndDate: "2025-03-20",
            familyMembers: [{
                name: "Trần Văn A",
                relationship: "father",
                birthday: "1970-01-01",
                occupation: "Kỹ sư"
            }]
        }
    },
    {
        name: "Test Case 2: Thiếu trường bắt buộc",
        data: {
            employeeName: "",
            employeeFullName: "",
            employeeEmail: "",
            employeePhone: "",
            employeeBirthday: "",
            employeeAddress: "",
            departmentId: "",
            positionId: "",
            contractType: "",
            baseSalary: "",
            contractStartDate: "",
            contractEndDate: "",
            familyMembers: []
        }
    },
    {
        name: "Test Case 3: Email không hợp lệ",
        data: {
            employeeName: "Đạt",
            employeeFullName: "Trần Tấn Đạt",
            employeeEmail: "dat.tran",
            employeePhone: "0987654321",
            employeeBirthday: "1995-05-15",
            employeeAddress: "123 Đường ABC, Quận 1, TP.HCM",
            departmentId: "1",
            positionId: "1",
            contractType: "1",
            baseSalary: "10000000",
            contractStartDate: "2024-03-20",
            contractEndDate: "2025-03-20",
            familyMembers: []
        }
    },
    {
        name: "Test Case 4: Số điện thoại không hợp lệ",
        data: {
            employeeName: "Đạt",
            employeeFullName: "Trần Tấn Đạt",
            employeeEmail: "dat.tran@example.com",
            employeePhone: "123456",
            employeeBirthday: "1995-05-15",
            employeeAddress: "123 Đường ABC, Quận 1, TP.HCM",
            departmentId: "1",
            positionId: "1",
            contractType: "1",
            baseSalary: "10000000",
            contractStartDate: "2024-03-20",
            contractEndDate: "2025-03-20",
            familyMembers: []
        }
    },
    {
        name: "Test Case 5: Lương không hợp lệ",
        data: {
            employeeName: "Đạt",
            employeeFullName: "Trần Tấn Đạt",
            employeeEmail: "dat.tran@example.com",
            employeePhone: "0987654321",
            employeeBirthday: "1995-05-15",
            employeeAddress: "123 Đường ABC, Quận 1, TP.HCM",
            departmentId: "1",
            positionId: "1",
            contractType: "1",
            baseSalary: "100",
            contractStartDate: "2024-03-20",
            contractEndDate: "2025-03-20",
            familyMembers: []
        }
    },
    {
        name: "Test Case 6: Ngày sinh không hợp lệ",
        data: {
            employeeName: "Đạt",
            employeeFullName: "Trần Tấn Đạt",
            employeeEmail: "dat.tran@example.com",
            employeePhone: "0987654321",
            employeeBirthday: "2025-05-15", // Ngày sinh trong tương lai
            employeeAddress: "123 Đường ABC, Quận 1, TP.HCM",
            departmentId: "1",
            positionId: "1",
            contractType: "1",
            baseSalary: "10000000",
            contractStartDate: "2024-03-20",
            contractEndDate: "2025-03-20",
            familyMembers: []
        }
    },
    {
        name: "Test Case 7: Ngày kết thúc hợp đồng trước ngày bắt đầu",
        data: {
            employeeName: "Đạt",
            employeeFullName: "Trần Tấn Đạt",
            employeeEmail: "dat.tran@example.com",
            employeePhone: "0987654321",
            employeeBirthday: "1995-05-15",
            employeeAddress: "123 Đường ABC, Quận 1, TP.HCM",
            departmentId: "1",
            positionId: "1",
            contractType: "1",
            baseSalary: "10000000",
            contractStartDate: "2024-03-20",
            contractEndDate: "2023-03-20", // Ngày kết thúc trước ngày bắt đầu
            familyMembers: []
        }
    },
    {
        name: "Test Case 8: Thông tin gia đình không hợp lệ",
        data: {
            employeeName: "Đạt",
            employeeFullName: "Trần Tấn Đạt",
            employeeEmail: "dat.tran@example.com",
            employeePhone: "0987654321",
            employeeBirthday: "1995-05-15",
            employeeAddress: "123 Đường ABC, Quận 1, TP.HCM",
            departmentId: "1",
            positionId: "1",
            contractType: "1",
            baseSalary: "10000000",
            contractStartDate: "2024-03-20",
            contractEndDate: "2025-03-20",
            familyMembers: [{
                name: "A", // Tên quá ngắn
                relationship: "father",
                birthday: "2025-01-01", // Ngày sinh trong tương lai
                occupation: "K" // Nghề nghiệp quá ngắn
            }]
        }
    }
];

// Hàm điền dữ liệu vào form
function fillFormData(data) {
    // Reset form
    document.getElementById('addEmployeeForm').reset();
    
    // Điền dữ liệu vào các trường
    Object.keys(data).forEach(key => {
        const element = document.getElementById(key);
        if (element) {
            element.value = data[key];
        }
    });

    // Xử lý thông tin gia đình nếu có
    if (data.familyMembers && data.familyMembers.length > 0) {
        const member = data.familyMembers[0];
        document.querySelector('.member-name').value = member.name;
        document.querySelector('.relationship').value = member.relationship;
        document.querySelector('.member-birthday').value = member.birthday;
        document.querySelector('.member-occupation').value = member.occupation;
    }
}

// Hàm chạy tất cả test case
async function runAllTests() {
    const results = [];
    let passed = 0;
    let failed = 0;
    let total = testCases.length;

    // Tạo container hiển thị kết quả
    const resultContainer = document.createElement('div');
    resultContainer.style.marginTop = '20px';
    resultContainer.style.padding = '20px';
    resultContainer.style.border = '1px solid #ddd';
    resultContainer.style.borderRadius = '5px';
    resultContainer.style.backgroundColor = '#f9f9f9';

    // Thêm tiêu đề
    const title = document.createElement('h3');
    title.textContent = 'Kết quả kiểm thử';
    resultContainer.appendChild(title);

    // Thêm thống kê
    const stats = document.createElement('div');
    stats.style.marginBottom = '20px';
    resultContainer.appendChild(stats);

    // Chạy từng test case
    for (let i = 0; i < testCases.length; i++) {
        const testCase = testCases[i];
        const result = {
            name: testCase.name,
            status: 'Đang chạy...',
            errors: []
        };

        // Hiển thị test case đang chạy
        const testDiv = document.createElement('div');
        testDiv.style.marginBottom = '10px';
        testDiv.style.padding = '10px';
        testDiv.style.border = '1px solid #ddd';
        testDiv.style.borderRadius = '5px';
        testDiv.innerHTML = `<strong>${testCase.name}</strong>: ${result.status}`;
        resultContainer.appendChild(testDiv);

        try {
            // Điền dữ liệu
            fillFormData(testCase.data);

            // Validate form
            const form = document.getElementById('addEmployeeForm');
            const isValid = form.checkValidity();

            // Kiểm tra các trường hợp đặc biệt
            let expectedResult = true;
            let validationErrors = [];

            // Test Case 1: Dữ liệu hợp lệ đầy đủ
            if (i === 0) {
                expectedResult = true;
            }
            // Test Case 2: Thiếu trường bắt buộc
            else if (i === 1) {
                expectedResult = false;
                // Kiểm tra tất cả các trường bắt buộc
                const requiredFields = [
                    'employeeName',
                    'employeeFullName',
                    'employeeEmail',
                    'employeePhone',
                    'employeeBirthday',
                    'employeeAddress',
                    'departmentId',
                    'positionId',
                    'contractType',
                    'baseSalary',
                    'contractStartDate'
                ];

                requiredFields.forEach(field => {
                    const element = document.getElementById(field);
                    if (element && element.value === '') {
                        validationErrors.push(`${field}: Trường này là bắt buộc`);
                    }
                });
            }
            // Test Case 3: Email không hợp lệ
            else if (i === 2) {
                expectedResult = false;
                const email = document.getElementById('employeeEmail').value;
                if (!email.includes('@')) {
                    validationErrors.push('Email không hợp lệ');
                }
            }
            // Test Case 4: Số điện thoại không hợp lệ
            else if (i === 3) {
                expectedResult = false;
                const phone = document.getElementById('employeePhone').value;
                if (phone.length < 10) {
                    validationErrors.push('Số điện thoại không hợp lệ');
                }
            }
            // Test Case 5: Lương không hợp lệ
            else if (i === 4) {
                expectedResult = false;
                const salary = document.getElementById('baseSalary').value;
                if (parseInt(salary) < 500) {
                    validationErrors.push('Lương phải lớn hơn 500 đồng');
                }
            }
            // Test Case 6: Ngày sinh không hợp lệ
            else if (i === 5) {
                expectedResult = false;
                const birthday = new Date(document.getElementById('employeeBirthday').value);
                const today = new Date();
                if (birthday > today) {
                    validationErrors.push('Ngày sinh không được trong tương lai');
                }
            }
            // Test Case 7: Ngày kết thúc hợp đồng trước ngày bắt đầu
            else if (i === 6) {
                expectedResult = false;
                const startDate = new Date(document.getElementById('contractStartDate').value);
                const endDate = new Date(document.getElementById('contractEndDate').value);
                if (endDate < startDate) {
                    validationErrors.push('Ngày kết thúc phải sau ngày bắt đầu');
                }
            }
            // Test Case 8: Thông tin gia đình không hợp lệ
            else if (i === 7) {
                expectedResult = false;
                const memberName = document.querySelector('.member-name').value;
                const memberBirthday = new Date(document.querySelector('.member-birthday').value);
                const memberOccupation = document.querySelector('.member-occupation').value;
                const today = new Date();

                if (memberName.length < 2) {
                    validationErrors.push('Tên thành viên phải có ít nhất 2 ký tự');
                }
                if (memberBirthday > today) {
                    validationErrors.push('Ngày sinh thành viên không được trong tương lai');
                }
                if (memberOccupation.length < 2) {
                    validationErrors.push('Nghề nghiệp phải có ít nhất 2 ký tự');
                }
            }

            // So sánh kết quả thực tế với kết quả mong đợi
            if (isValid === expectedResult && validationErrors.length === 0) {
                result.status = 'PASSED';
                passed++;
            } else {
                result.status = 'FAILED';
                failed++;

                // Thêm lỗi validation vào kết quả
                if (validationErrors.length > 0) {
                    result.errors = validationErrors;
                } else {
                    // Lấy tất cả các trường input
                    const inputs = form.querySelectorAll('input, select');
                    inputs.forEach(input => {
                        if (!input.validity.valid) {
                            let errorMessage = '';
                            if (input.validity.valueMissing) {
                                errorMessage = 'Trường này là bắt buộc';
                            } else if (input.validity.typeMismatch) {
                                errorMessage = 'Định dạng không hợp lệ';
                            } else if (input.validity.patternMismatch) {
                                errorMessage = 'Không đúng định dạng yêu cầu';
                            } else if (input.validity.rangeUnderflow) {
                                errorMessage = 'Giá trị quá nhỏ';
                            } else if (input.validity.rangeOverflow) {
                                errorMessage = 'Giá trị quá lớn';
                            } else if (input.validity.stepMismatch) {
                                errorMessage = 'Giá trị không hợp lệ';
                            } else if (input.validity.badInput) {
                                errorMessage = 'Dữ liệu không hợp lệ';
                            }
                            result.errors.push(`${input.name}: ${errorMessage}`);
                        }
                    });
                }
            }

            // Cập nhật hiển thị kết quả
            testDiv.innerHTML = `
                <strong>${testCase.name}</strong>: 
                <span style="color: ${result.status === 'PASSED' ? 'green' : 'red'}">${result.status}</span>
                ${result.errors.length > 0 ? `<br>Lỗi: ${result.errors.join('<br>')}` : ''}
            `;

        } catch (error) {
            result.status = 'ERROR';
            result.errors.push(`Lỗi khi chạy test: ${error.message}`);
            failed++;

            // Cập nhật hiển thị kết quả
            testDiv.innerHTML = `
                <strong>${testCase.name}</strong>: 
                <span style="color: red">ERROR</span>
                <br>Lỗi: ${error.message}
            `;
        }

        results.push(result);
    }

    // Cập nhật thống kê
    stats.innerHTML = `
        <div style="margin-bottom: 10px;">
            <strong>Tổng số test case:</strong> ${total}
        </div>
        <div style="margin-bottom: 10px;">
            <strong>Passed:</strong> <span style="color: green">${passed}</span>
        </div>
        <div style="margin-bottom: 10px;">
            <strong>Failed:</strong> <span style="color: red">${failed}</span>
        </div>
        <div>
            <strong>Tỷ lệ pass:</strong> ${((passed / total) * 100).toFixed(2)}%
        </div>
    `;

    // Thêm container kết quả vào form
    const form = document.getElementById('addEmployeeForm');
    const existingResult = document.querySelector('.test-results');
    if (existingResult) {
        existingResult.remove();
    }
    resultContainer.className = 'test-results';
    form.appendChild(resultContainer);
}

// Thêm nút chạy tất cả test
document.addEventListener('DOMContentLoaded', () => {
    const testButtons = document.querySelector('.test-buttons');
    const runAllButton = document.createElement('button');
    runAllButton.type = 'button';
    runAllButton.className = 'btn btn-info';
    runAllButton.textContent = 'Chạy tất cả test';
    runAllButton.onclick = runAllTests;
    testButtons.appendChild(runAllButton);
}); 