document.addEventListener("DOMContentLoaded", function () {
    // Sample data (in a real app, this would come from a server)
    let salaryBonuses = [
        {
            id: "1",
            employeeName: "Nguyễn Văn A",
            value: "2,000,000 VNĐ",
            reason: "Hoàn thành xuất sắc dự án X",
            effectiveDate: "2024-07-15",
        },
        {
            id: "2",
            employeeName: "Trần Thị B",
            value: "1,500,000 VNĐ",
            reason: "Đạt chỉ tiêu kinh doanh Q2",
            effectiveDate: "2024-07-10",
        },
        {
            id: "3",
            employeeName: "Lê Văn C",
            value: "500,000 VNĐ",
            reason: "Thưởng chuyên cần tháng 6",
            effectiveDate: "2024-07-01",
        },
        {
            id: "4",
            employeeName: "Nguyễn Văn A",
            value: "1,000,000 VNĐ",
            reason: "Sáng kiến cải tiến",
            effectiveDate: "2024-08-01",
        },
    ];

    const bonusTableBody = document.getElementById("bonusTableBody");
    const btnAddBonus = document.getElementById("btnAddBonus");
    const employeeNameInput = document.getElementById("employeeName");
    const bonusValueInput = document.getElementById("bonusValue");
    const bonusReasonInput = document.getElementById("bonusReason");
    const effectiveDateInput = document.getElementById("effectiveDate");
    const filterSelects = document.querySelectorAll(".filter-select");
    const btnExportExcel = document.getElementById("btnExportExcel"); // Get the export button

    // --- Variable to hold currently displayed/filtered data ---
    let currentFilteredData = [];

    // --- Helper Functions ---
    function generateNewId() {
        const lastIdNum =
            salaryBonuses.length > 0
                ? parseInt(
                      salaryBonuses[salaryBonuses.length - 1].id.replace(
                          "TH",
                          ""
                      ),
                      10
                  )
                : 0;
        const newIdNum = lastIdNum + 1;
        return "TH" + String(newIdNum).padStart(3, "0");
    }

    // --- Populate Filter Dropdowns ---
    function populateFilters() {
        // ... (populateFilters function remains the same)
        const uniqueValues = {
            id: new Set(),
            employeeName: new Set(),
            value: new Set(),
            reason: new Set(),
            effectiveDate: new Set(),
        };

        salaryBonuses.forEach((bonus) => {
            Object.keys(uniqueValues).forEach((key) => {
                if (bonus[key] !== undefined && bonus[key] !== null) {
                    uniqueValues[key].add(bonus[key]);
                }
            });
        });

        filterSelects.forEach((select) => {
            const column = select.dataset.column;
            const currentSelectedValue = select.value;
            while (select.options.length > 1) {
                select.remove(1);
            }
            const sortedValues = Array.from(uniqueValues[column]).sort();
            sortedValues.forEach((value) => {
                const option = document.createElement("option");
                option.value = value;
                option.textContent = value;
                select.appendChild(option);
            });
            if (
                select.querySelector(`option[value="${currentSelectedValue}"]`)
            ) {
                select.value = currentSelectedValue;
            } else {
                select.value = "";
            }
        });
    }

    // --- Core Functions ---

    // Render the salary bonus table (MODIFIED to accept data)
    function renderBonusTable(dataToRender) {
        bonusTableBody.innerHTML = "";
        dataToRender.forEach((bonus) => {
            // Find original index using ID for reliable actions even after filtering/sorting
            const originalIndex = salaryBonuses.findIndex(
                (item) => item.id === bonus.id
            );
            if (originalIndex === -1) return; // Should not happen normally

            const tr = document.createElement("tr");
            // IMPORTANT: Ensure onclick handlers use the 'originalIndex'
            if (bonus.isEditing) {
                tr.innerHTML = `
                    <td>${bonus.id}</td>
                    <td><input type="text" value="${bonus.employeeName}" id="editName${originalIndex}" class="edit-input" /></td>
                    <td><input type="text" value="${bonus.value}" id="editValue${originalIndex}" class="edit-input" /></td>
                    <td><input type="text" value="${bonus.reason}" id="editReason${originalIndex}" class="edit-input" /></td>
                    <td><input type="date" value="${bonus.effectiveDate}" id="editDate${originalIndex}" class="edit-input" /></td>
                    <td>
                        <button class="action-btn save" onclick="saveBonus(${originalIndex})">Lưu</button>
                        <button class="action-btn cancel" onclick="cancelEditBonus(${originalIndex})">Hủy</button>
                    </td>
                `;
            } else {
                tr.innerHTML = `
                    <td>${bonus.id}</td>
                    <td>${bonus.employeeName}</td>
                    <td>${bonus.value}</td>
                    <td>${bonus.reason}</td>
                    <td>${bonus.effectiveDate}</td>
                    <td>
                        <button class="action-btn" onclick="editBonus(${originalIndex})">Sửa</button>
                        <button class="action-btn delete" onclick="deleteBonus(${originalIndex})">Xóa</button>
                    </td>
                `;
            }
            bonusTableBody.appendChild(tr);
        });

        document.querySelectorAll(".edit-input").forEach((input) => {
            input.style.width = "95%";
            input.style.padding = "4px";
            input.style.border = "1px solid #ccc";
            input.style.borderRadius = "3px";
        });
    }

    // --- Filter Logic ---
    function filterTable() {
        let filteredData = [...salaryBonuses];

        filterSelects.forEach((select) => {
            const column = select.dataset.column;
            const selectedValue = select.value;

            if (selectedValue) {
                filteredData = filteredData.filter((bonus) => {
                    const bonusValue =
                        bonus[column] === null || bonus[column] === undefined
                            ? ""
                            : String(bonus[column]);
                    return bonusValue === selectedValue;
                });
            }
        });

        // --- UPDATE the global filtered data variable ---
        currentFilteredData = filteredData;
        renderBonusTable(currentFilteredData); // Render the filtered data
    }

    // --- Modified Action Functions ---

    function addBonus() {
        // ... (addBonus function remains the same)
        const name = employeeNameInput.value.trim();
        const value = bonusValueInput.value.trim();
        const reason = bonusReasonInput.value.trim();
        const date = effectiveDateInput.value;

        if (!name || !value || !reason || !date) {
            alert("Vui lòng nhập đầy đủ thông tin thưởng.");
            return;
        }

        const newId = generateNewId();
        const newBonus = {
            id: newId,
            employeeName: name,
            value: value,
            reason: reason,
            effectiveDate: date,
        };

        salaryBonuses.push(newBonus);

        employeeNameInput.value = "";
        bonusValueInput.value = "";
        bonusReasonInput.value = "";
        effectiveDateInput.value = "";

        populateFilters();
        filterTable(); // This will re-render and update currentFilteredData
    }

    function deleteBonus(originalIndex) {
        // Use originalIndex
        if (originalIndex < 0 || originalIndex >= salaryBonuses.length) return;
        const bonusToDelete = salaryBonuses[originalIndex];
        if (
            confirm(
                `Bạn có chắc muốn xóa thưởng [${bonusToDelete.id}] cho ${bonusToDelete.employeeName}?`
            )
        ) {
            salaryBonuses.splice(originalIndex, 1);
            // Need to find and remove from currentFilteredData as well if present
            const filteredIndex = currentFilteredData.findIndex(
                (b) => b.id === bonusToDelete.id
            );
            if (filteredIndex > -1) {
                currentFilteredData.splice(filteredIndex, 1);
            }
            populateFilters();
            filterTable(); // Re-filter and re-render
        }
    }

    function editBonus(originalIndex) {
        // Use originalIndex
        if (originalIndex < 0 || originalIndex >= salaryBonuses.length) return;

        // Find the corresponding item in the currently filtered data by ID
        const filteredItem = currentFilteredData.find(
            (b) => b.id === salaryBonuses[originalIndex].id
        );

        if (filteredItem) {
            // Reset editing state for all items in the current view first
            currentFilteredData.forEach((b) => {
                delete b.isEditing;
            });
            // Set editing state on the correct item in the filtered view
            filteredItem.isEditing = true;
            // Also update the main data source's item (though not strictly needed for render)
            salaryBonuses[originalIndex].isEditing = true;
        } else {
            // If not in filtered view, just update the main source (won't be visible until filter changes)
            salaryBonuses.forEach((b, i) => {
                if (b.isEditing && i !== originalIndex) delete b.isEditing;
            });
            salaryBonuses[originalIndex].isEditing = true;
        }

        renderBonusTable(currentFilteredData); // Re-render the current view
    }

    function cancelEditBonus(originalIndex) {
        // Use originalIndex
        if (originalIndex < 0 || originalIndex >= salaryBonuses.length) return;

        // Find the corresponding item in the currently filtered data by ID
        const filteredItem = currentFilteredData.find(
            (b) => b.id === salaryBonuses[originalIndex].id
        );

        if (filteredItem) {
            delete filteredItem.isEditing;
        }
        // Also remove from the main data source item
        delete salaryBonuses[originalIndex].isEditing;

        renderBonusTable(currentFilteredData); // Re-render the current view
    }

    function saveBonus(originalIndex) {
        // Use originalIndex
        if (originalIndex < 0 || originalIndex >= salaryBonuses.length) return;

        // Use the originalIndex to find the input elements reliably
        const editNameInput = document.getElementById(
            `editName${originalIndex}`
        );
        const editValueInput = document.getElementById(
            `editValue${originalIndex}`
        );
        const editReasonInput = document.getElementById(
            `editReason${originalIndex}`
        );
        const editDateInput = document.getElementById(
            `editDate${originalIndex}`
        );

        if (
            !editNameInput ||
            !editValueInput ||
            !editReasonInput ||
            !editDateInput
        ) {
            console.error(
                "Could not find edit inputs for index:",
                originalIndex
            );
            // Attempt to cancel edit if inputs are missing
            const filteredItem = currentFilteredData.find(
                (b) => b.id === salaryBonuses[originalIndex].id
            );
            if (filteredItem) delete filteredItem.isEditing;
            delete salaryBonuses[originalIndex].isEditing;
            renderBonusTable(currentFilteredData);
            return;
        }

        const updatedName = editNameInput.value.trim();
        const updatedValue = editValueInput.value.trim();
        const updatedReason = editReasonInput.value.trim();
        const updatedDate = editDateInput.value;

        if (!updatedName || !updatedValue || !updatedReason || !updatedDate) {
            alert("Vui lòng nhập đầy đủ thông tin khi chỉnh sửa.");
            return;
        }

        // Update the main data source
        const updatedBonus = {
            ...salaryBonuses[originalIndex], // Keep original id
            employeeName: updatedName,
            value: updatedValue,
            reason: updatedReason,
            effectiveDate: updatedDate,
        };
        delete updatedBonus.isEditing; // Remove editing flag
        salaryBonuses[originalIndex] = updatedBonus;

        // Update the item in currentFilteredData as well
        const filteredIndex = currentFilteredData.findIndex(
            (b) => b.id === updatedBonus.id
        );
        if (filteredIndex > -1) {
            currentFilteredData[filteredIndex] = { ...updatedBonus }; // Update filtered data too
        }

        populateFilters(); // Update filters in case values changed
        renderBonusTable(currentFilteredData); // Re-render the current view
    }

    // --- EXPORT Function ---
    function exportToExcel() {
        if (!currentFilteredData || currentFilteredData.length === 0) {
            alert("Không có dữ liệu để xuất.");
            return;
        }

        // Optional: Prepare data for export (e.g., remove editing flags, select columns)
        const dataForExport = currentFilteredData.map((bonus) => ({
            "ID Thưởng": bonus.id,
            "Tên nhân viên": bonus.employeeName,
            "Giá trị": bonus.value, // Keep as string like "1,000,000 VNĐ"
            "Lý do": bonus.reason,
            "Ngày hiệu lực": bonus.effectiveDate,
            // Exclude 'isEditing' or other internal properties
        }));

        // Define worksheet
        const ws = XLSX.utils.json_to_sheet(dataForExport);

        // Define workbook
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, "Danh sách thưởng"); // Sheet name

        // Trigger download
        XLSX.writeFile(wb, "DanhSachThuong.xlsx"); // File name
    }

    // --- Event Listeners ---
    btnAddBonus.addEventListener("click", addBonus);
    btnExportExcel.addEventListener("click", exportToExcel); // Add listener for export

    filterSelects.forEach((select) => {
        select.addEventListener("change", filterTable);
    });

    // --- Initial Setup ---
    populateFilters();
    filterTable(); // Initial call to filter (shows all), render, and set currentFilteredData

    // --- Make functions globally accessible for inline onclick handlers ---
    window.editBonus = editBonus;
    window.deleteBonus = deleteBonus;
    window.saveBonus = saveBonus;
    window.cancelEditBonus = cancelEditBonus;
});
