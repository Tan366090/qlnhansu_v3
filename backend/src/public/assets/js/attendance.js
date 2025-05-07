document.addEventListener("DOMContentLoaded", function () {
    const timekeepingTableBody = document.getElementById(
        "timekeepingTableBody"
    );
    const addAttendanceButton = document.getElementById("addAttendanceButton");
    const historyButton = document.querySelector(".btn.btn-secondary");
    const saveButton = document.getElementById("saveButton");

    if (!timekeepingTableBody) {
        console.error("Table body with ID 'timekeepingTableBody' not found!");
        return;
    }

    // Create overlay for dimming the background
    const overlay = document.createElement("div");
    overlay.id = "modalOverlay";
    document.body.appendChild(overlay);

    // Create modal for adding attendance
    const modal = document.createElement("div");
    modal.id = "attendanceModal";

    modal.innerHTML = `
        <h3>Thêm chấm công</h3>
        <div>
            <label for="attendanceDate">Ngày tháng năm</label>
            <input type="date" id="attendanceDate">
        </div>
        <div>
            <label for="attendanceSymbol">Ký hiệu chấm công</label>
            <select id="attendanceSymbol">
                <option value="P">Đi làm</option>
                <option value="1/2P">Nghỉ nửa ngày phép</option>
                <option value="AL">Nghỉ phép</option>
                <option value="A">Vắng không phép</option>
                <option value="WFH">Làm việc tại nhà</option>
                <option value="L">Đến muộn</option>
                <option value="SL">Ốm</option>
                <option value="Cô">Chăm sóc con ốm</option>
                <option value="TS">Nghỉ thai sản</option>
                <option value="T">Tai nạn lao động</option>
                <option value="CN">Chủ nhật</option>
                <option value="NL">Nghỉ lễ</option>
                <option value="NB">Nghỉ bù</option>
                <option value="1/2K">Nghỉ nửa ngày không lương</option>
                <option value="K">Nghỉ không lương</option>
                <option value="N">Ngừng làm việc</option>
                <option value="NN">Làm nửa ngày có lương</option>
            </select>
        </div>
        <div>
            <label for="attendanceNote">Ghi chú</label>
            <textarea id="attendanceNote" rows="3"></textarea>
        </div>
        <div class="buttons">
            <button class="btn-save">Lưu</button>
            <button class="btn-cancel">Hủy</button>
        </div>
    `;
    document.body.appendChild(modal);

    // Event listener for "Thêm chấm công" button
    addAttendanceButton.addEventListener("click", function () {
        modal.style.display = "block"; // Show modal
        overlay.style.display = "block"; // Show overlay
    });

    // Event listener for "Hủy" button
    modal.querySelector(".btn-cancel").addEventListener("click", function () {
        modal.style.display = "none"; // Hide modal
        overlay.style.display = "none"; // Hide overlay
    });

    // Event listener for "Lưu" button
    modal
        .querySelector(".btn-save")
        .addEventListener("click", async function () {
            const attendanceDate =
                document.getElementById("attendanceDate").value;
            const attendanceSymbol =
                document.getElementById("attendanceSymbol").value;
            const attendanceNote =
                document.getElementById("attendanceNote").value;

            // Validate input
            if (!attendanceDate) {
                showNotification("Vui lòng nhập ngày tháng năm!", "error");
                return;
            }

            const today = new Date();
            const selectedDate = new Date(attendanceDate);
            if (selectedDate > today) {
                showNotification(
                    "Ngày chấm công không được lớn hơn ngày hiện tại!",
                    "error"
                );
                return;
            }

            // Prepare data
            const newAttendance = {
                attendance_date: attendanceDate,
                attendance_symbol: attendanceSymbol,
                notes: attendanceNote,
            };

            try {
                const response = await fetch(
                    "http://localhost:4000/api/attendance",
                    {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                        },
                        body: JSON.stringify([newAttendance]),
                    }
                );

                if (!response.ok) {
                    throw new Error("Không thể lưu dữ liệu chấm công");
                }

                showNotification("Thêm mới chấm công thành công!", "success");
                modal.style.display = "none"; // Hide modal
                overlay.style.display = "none"; // Hide overlay
                fetchAttendanceData(); // Reload data
            } catch (error) {
                console.error("Error saving attendance data:", error.message);
                showNotification("Có lỗi xảy ra khi lưu dữ liệu!", "error");
            }
        });

    // Helper function to show notifications (updated for better user experience)
    function showNotification(message, type = "success") {
        const notification = document.createElement("div");
        notification.className = `notification ${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <span>${message}</span>
                <button class="notification-close">&times;</button>
            </div>
        `;

        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: ${type === "success" ? "#28a745" : "#dc3545"};
            color: white;
            padding: 15px;
            border-radius: 5px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            z-index: 1001;
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        `;

        document.body.appendChild(notification);

        // Close notification on button click
        notification
            .querySelector(".notification-close")
            .addEventListener("click", () => {
                notification.remove();
            });

        // Auto-remove notification after 3 seconds
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }

    // Event listener for "Lịch sử chấm công" button
    historyButton.addEventListener("click", function () {
        window.location.href = "./attendance_History.html"; // Redirect to attendance_History.html
    });

    // Fetch attendance data from the server
    async function fetchAttendanceData() {
        try {
            const response = await fetch(
                "http://localhost/qlnhansu/api/getAttendance.php"
            );

            if (!response.ok) {
                const errorText = await response.text();
                console.error("Server response:", errorText);
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const result = await response.json();

            // Validate the response structure
            if (result.success && Array.isArray(result.data)) {
                console.log("Fetched attendance data:", result.data);
                renderAttendanceTable(result.data);
            } else {
                throw new Error("Invalid data format received from server");
            }
        } catch (error) {
            console.error("Error fetching attendance data:", error);
            showNotification(
                "Có lỗi xảy ra khi tải dữ liệu chấm công!",
                "error"
            );
        }
    }

    // Render attendance data into the table
    function renderAttendanceTable(data) {
        timekeepingTableBody.innerHTML = ""; // Clear existing rows

        if (!Array.isArray(data)) {
            console.error("Invalid data format received:", data);
            showNotification("Dữ liệu không hợp lệ", "error");
            return;
        }

        data.forEach((record, index) => {
            const row = document.createElement("tr");
            row.innerHTML = `
                <td>${index + 1}</td>
                <td>${formatDisplayDate(record.attendance_date)}</td>
                <td>${record.attendance_symbol || "N/A"}</td>
                <td>${record.notes || ""}</td>
                <td>
                    <button class="btn btn-danger delete-button" data-id="${
                        record.attendance_id
                    }">Xóa</button>
                    <button class="btn btn-primary edit-button" data-id="${
                        record.attendance_id
                    }">Sửa</button>
                </td>
            `;
            timekeepingTableBody.appendChild(row);
        });

        // Add event listeners for delete buttons
        document.querySelectorAll(".delete-button").forEach((button) => {
            button.addEventListener("click", function () {
                const recordId = this.getAttribute("data-id");
                if (!recordId || recordId === "undefined") {
                    showNotification("Không thể xóa: ID không hợp lệ", "error");
                    return;
                }
                deleteRecord(recordId);
            });
        });

        // Add event listeners for edit buttons
        document.querySelectorAll(".edit-button").forEach((button) => {
            button.addEventListener("click", function () {
                const recordId = this.getAttribute("data-id");
                if (!recordId) {
                    showNotification("ID bản ghi không hợp lệ", "error");
                    return;
                }

                const row = this.closest("tr");
                const cells = row.querySelectorAll("td");

                // Get current data from the row
                const currentData = {
                    date: cells[1].textContent,
                    symbol: cells[2].textContent,
                    notes: cells[3].textContent,
                };

                // Convert display date (dd/mm/yyyy) to input date format (yyyy-mm-dd)
                const [day, month, year] = currentData.date.split("/");
                const inputDate = `${year}-${month}-${day}`;

                // Create and show modal
                const modal = document.createElement("div");
                modal.id = "editAttendanceModal";
                modal.className = "modal";

                const symbolOptions = Object.entries(ATTENDANCE_SYMBOLS)
                    .map(
                        ([value, label]) =>
                            `<option value="${value}" ${
                                currentData.symbol === value ? "selected" : ""
                            }>${label}</option>`
                    )
                    .join("");

                modal.innerHTML = `
                    <div class="modal-content">
                        <h3>Sửa chấm công</h3>
                        <form id="editAttendanceForm" data-id="${recordId}">
                            <div class="form-group">
                                <label for="editDate">Ngày chấm công</label>
                                <input type="date" id="editDate" value="${inputDate}" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="editSymbol">Ký hiệu chấm công</label>
                                <select id="editSymbol" required>
                                    ${symbolOptions}
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="editNote">Ghi chú</label>
                                <textarea id="editNote" rows="3">${currentData.notes}</textarea>
                            </div>
                            
                            <div class="buttons">
                                <button type="submit" class="btn-save">Lưu</button>
                                <button type="button" class="btn-cancel">Hủy</button>
                            </div>
                        </form>
                    </div>
                `;

                // Create and add overlay
                const overlay = document.createElement("div");
                overlay.id = "modalOverlay";
                document.body.appendChild(overlay);
                document.body.appendChild(modal);

                // Show modal and overlay
                modal.style.display = "block";
                overlay.style.display = "block";

                // Handle form submission
                document.getElementById("editAttendanceForm").onsubmit =
                    async function (e) {
                        e.preventDefault();

                        const formId = this.getAttribute("data-id");
                        if (!formId) {
                            showNotification(
                                "ID bản ghi không hợp lệ",
                                "error"
                            );
                            return;
                        }

                        const updatedData = {
                            attendance_date:
                                document.getElementById("editDate").value,
                            attendance_symbol:
                                document.getElementById("editSymbol").value,
                            notes: document.getElementById("editNote").value,
                        };

                        try {
                            const success = await updateRecord(
                                formId,
                                updatedData
                            );
                            if (success) {
                                closeModal();
                                await fetchAttendanceData(); // Refresh table
                            }
                        } catch (error) {
                            console.error("Error updating record:", error);
                            showNotification(
                                "Có lỗi xảy ra khi cập nhật!",
                                "error"
                            );
                        }
                    };

                // Handle cancel button
                modal.querySelector(".btn-cancel").onclick = function () {
                    closeModal();
                };

                function closeModal() {
                    modal.remove();
                    overlay.remove();
                }
            });
        });
    }

    // Delete a record
    async function deleteRecord(attendanceId) {
        try {
            // Confirm deletion with the user
            const confirmDelete = confirm(
                "Bạn có chắc chắn muốn xóa bản ghi này?"
            );
            if (!confirmDelete) return;

            // Send DELETE request to the server
            const response = await fetch(
                `http://localhost/qlnhansu/api/deleteAttendance.php?id=${attendanceId}`,
                {
                    method: "DELETE",
                    headers: {
                        "Content-Type": "application/json",
                    },
                }
            );

            // Check if the response is OK
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const result = await response.json();
            if (result.success) {
                alert(result.message);

                fetchAttendanceData();
            } else {
                throw new Error(result.error);
            }
        } catch (error) {
            console.error("Error deleting attendance record:", error);
            alert("Có lỗi xảy ra khi xóa bản ghi. Vui lòng thử lại.");
        }
    }

    // Update a record
    async function updateRecord(recordId, data) {
        if (!recordId) {
            console.error("Record ID is undefined. Cannot update record.");
            showNotification("Không thể cập nhật: ID không hợp lệ", "error");
            return false;
        }

        // Validate data
        if (!data.attendance_date || !data.attendance_symbol) {
            showNotification("Vui lòng nhập đầy đủ thông tin!", "error");
            return false;
        }

        try {
            console.log("Sending update request with data:", {
                id: recordId,
                ...data,
            });

            const response = await fetch(
                "http://localhost/qlnhansu/api/updateAttendance.php",
                {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        Accept: "application/json",
                    },
                    body: JSON.stringify({
                        id: recordId,
                        attendance_date: data.attendance_date,
                        attendance_symbol: data.attendance_symbol,
                        notes: data.notes || "",
                    }),
                }
            );

            // Log the raw response for debugging
            const responseText = await response.text();
            console.log("Raw server response:", responseText);

            // Try to parse the response as JSON
            let result;
            try {
                result = JSON.parse(responseText);
            } catch (e) {
                console.error("Failed to parse JSON response:", e);
                throw new Error("Invalid JSON response from server");
            }

            if (result.success) {
                showNotification(
                    result.message || "Cập nhật chấm công thành công!",
                    "success"
                );
                return true;
            } else {
                throw new Error(result.error || "Cập nhật không thành công");
            }
        } catch (error) {
            console.error("Error updating record:", error);
            showNotification(
                error.message || "Có lỗi xảy ra khi cập nhật chấm công!",
                "error"
            );
            return false;
        }
    }

    // Helper function to map attendance symbols to CSS classes
    function getSymbolClass(symbol) {
        const symbolClassMap = {
            P: "status-work", // Đi làm
            AL: "status-leave", // Nghỉ phép
            A: "status-absent", // Vắng không phép
            WFH: "status-wfh", // Làm việc tại nhà
            L: "status-late", // Đến muộn
            SL: "status-sick", // Ốm
        };
        return symbolClassMap[symbol] || "status-unknown"; // Default to "unknown" if symbol is not recognized
    }

    // Helper function to format date as "yyyy-MM-dd"
    function formatDate(dateString) {
        if (!dateString) return "N/A";

        // Handle both ISO format and dd/mm/yyyy format
        let date;
        if (dateString.includes("/")) {
            // Convert dd/mm/yyyy to Date object
            const [day, month, year] = dateString.split("/");
            date = new Date(year, month - 1, day);
        } else {
            date = new Date(dateString);
        }

        if (isNaN(date.getTime())) return "N/A";

        // Adjust for timezone offset
        const timezoneOffset = date.getTimezoneOffset() * 60000;
        const localDate = new Date(date.getTime() - timezoneOffset);

        const year = localDate.getFullYear();
        const month = String(localDate.getMonth() + 1).padStart(2, "0");
        const day = String(localDate.getDate()).padStart(2, "0");
        return `${year}-${month}-${day}`;
    }

    // Helper function to format date for display
    function formatDisplayDate(dateString) {
        if (!dateString) return "N/A";

        // Handle both ISO format and dd/mm/yyyy format
        let date;
        if (dateString.includes("/")) {
            // Convert dd/mm/yyyy to Date object
            const [day, month, year] = dateString.split("/");
            date = new Date(year, month - 1, day);
        } else {
            date = new Date(dateString);
        }

        if (isNaN(date.getTime())) return "N/A";

        // Adjust for timezone offset
        const timezoneOffset = date.getTimezoneOffset() * 60000;
        const localDate = new Date(date.getTime() - timezoneOffset);

        const day = String(localDate.getDate()).padStart(2, "0");
        const month = String(localDate.getMonth() + 1).padStart(2, "0");
        const year = localDate.getFullYear();
        return `${day}/${month}/${year}`;
    }

    // Helper function to format datetime as "dd/mm/yyyy hh:mm:ss"
    function formatDateTime(dateTimeString) {
        const date = new Date(dateTimeString);
        const day = date.getDate().toString().padStart(2, "0");
        const month = (date.getMonth() + 1).toString().padStart(2, "0");
        const year = date.getFullYear();
        const hours = date.getHours().toString().padStart(2, "0");
        const minutes = date.getMinutes().toString().padStart(2, "0");
        const seconds = date.getSeconds().toString().padStart(2, "0");
        return `${day}/${month}/${year} ${hours}:${minutes}:${seconds}`;
    }

    // Initialize
    fetchAttendanceData();
    console.log("attendance.js đã được tải thành công!");

    // Function to upload attendance data
    async function uploadAttendanceData(data) {
        try {
            const response = await fetch(
                "http://localhost/qlnhansu/api/uploadAttendance.php",
                {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                    },
                    body: JSON.stringify(data),
                }
            );

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const result = await response.json();
            if (result.success) {
                showNotification("Tải dữ liệu lên thành công!", "success");
                return true;
            } else {
                throw new Error(
                    result.error || "Có lỗi xảy ra khi tải dữ liệu lên"
                );
            }
        } catch (error) {
            console.error("Error uploading attendance data:", error);
            showNotification(
                error.message || "Có lỗi xảy ra khi tải dữ liệu lên",
                "error"
            );
            return false;
        }
    }

    // Modify the save button event listener
    if (saveButton) {
        saveButton.addEventListener("click", async function () {
            const newRows = timekeepingTableBody.querySelectorAll("tr");
            const newData = [];
            const today = new Date();

            newRows.forEach((row) => {
                const dateInput = row.querySelector(".attendance-date-input");
                const symbolSelect = row.querySelector(
                    ".attendance-symbol-select"
                );
                const noteInput = row.querySelector(".attendance-note-input");

                if (dateInput && symbolSelect && noteInput) {
                    const attendanceDate = new Date(dateInput.value);

                    // Check if the date is valid and not in the future
                    if (isNaN(attendanceDate.getTime())) {
                        showNotification(
                            "Ngày chấm công không hợp lệ!",
                            "error"
                        );
                        return;
                    }
                    if (attendanceDate > today) {
                        showNotification(
                            "Ngày chấm công không được lớn hơn ngày hiện tại!",
                            "error"
                        );
                        return;
                    }

                    newData.push({
                        attendance_date: dateInput.value,
                        attendance_symbol: symbolSelect.value,
                        notes: noteInput.value,
                    });
                }
            });

            if (newData.length === 0) {
                showNotification("Không có dữ liệu hợp lệ để lưu!", "error");
                return;
            }

            const success = await uploadAttendanceData(newData);
            if (success) {
                saveButton.style.display = "none"; // Hide "Lưu" button after saving
                fetchAttendanceData(); // Reload data after saving
            }
        });
    }

    // Định nghĩa các ký hiệu chấm công
    const ATTENDANCE_SYMBOLS = {
        P: "Đi làm",
        "1/2P": "Nghỉ nửa ngày phép",
        AL: "Nghỉ phép",
        A: "Vắng không phép",
        WFH: "Làm việc tại nhà",
        L: "Đến muộn",
        SL: "Ốm",
        Cô: "Chăm sóc con ốm",
        TS: "Nghỉ thai sản",
        T: "Tai nạn lao động",
        CN: "Chủ nhật",
        NL: "Nghỉ lễ",
        NB: "Nghỉ bù",
        "1/2K": "Nghỉ nửa ngày không lương",
        K: "Nghỉ không lương",
        N: "Ngừng làm việc",
        NN: "Làm nửa ngày có lương",
    };
});
