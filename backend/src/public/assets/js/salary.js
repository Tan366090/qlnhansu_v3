document.addEventListener("DOMContentLoaded", function () {
    // Sample Salary Data (Replace with actual data source/API call)
    let salaryData = [
        { employeeId: "NV001", employeeName: "Nguyễn Văn A", salaryMonth: "2024-07", position: "Kỹ sư phần mềm", salaryCoefficient: 3.5, baseSalary: "10,000,000 VNĐ", bonus: "2,000,000 VNĐ", netSalary: "15,500,000 VNĐ" },
        { employeeId: "NV002", employeeName: "Trần Thị B", salaryMonth: "2024-07", position: "Chuyên viên kinh doanh", salaryCoefficient: 3.0, baseSalary: "8,000,000 VNĐ", bonus: "1,500,000 VNĐ", netSalary: "12,800,000 VNĐ" },
        { employeeId: "NV003", employeeName: "Lê Văn C", salaryMonth: "2024-07", position: "Nhân viên hành chính", salaryCoefficient: 2.5, baseSalary: "6,000,000 VNĐ", bonus: "500,000 VNĐ", netSalary: "8,500,000 VNĐ" },
        { employeeId: "NV001", employeeName: "Nguyễn Văn A", salaryMonth: "2024-06", position: "Kỹ sư phần mềm", salaryCoefficient: 3.5, baseSalary: "10,000,000 VNĐ", bonus: "1,000,000 VNĐ", netSalary: "14,800,000 VNĐ" },
        { employeeId: "NV004", employeeName: "Phạm Thị D", salaryMonth: "2024-07", position: "Trưởng phòng kỹ thuật", salaryCoefficient: 4.5, baseSalary: "15,000,000 VNĐ", bonus: "3,000,000 VNĐ", netSalary: "21,500,000 VNĐ" },
    ];

    const salaryTableBody = document.getElementById("salaryTableBody");
    const filterSelects = document.querySelectorAll(".filter-select");
    const btnExportExcel = document.getElementById("btnExportExcel");
    const btnExportPdf = document.getElementById("btnExportPdf"); // Get PDF button

    // Variable to hold currently displayed/filtered data
    let currentFilteredData = [];

    // --- Populate Filter Dropdowns ---
    function populateFilters() {
        const uniqueValues = {
            employeeId: new Set(), employeeName: new Set(), salaryMonth: new Set(),
            position: new Set(), salaryCoefficient: new Set(),
        };
        salaryData.forEach(salary => {
            Object.keys(uniqueValues).forEach(key => {
                if (salary[key] !== undefined && salary[key] !== null) {
                    uniqueValues[key].add(salary[key]);
                }
            });
        });
        filterSelects.forEach(select => {
            const column = select.dataset.column;
            if (uniqueValues[column]) {
                const currentSelectedValue = select.value;
                while (select.options.length > 1) { select.remove(1); }
                const sortedValues = Array.from(uniqueValues[column]).sort((a, b) => String(a).localeCompare(String(b))); // Ensure proper sorting
                sortedValues.forEach(value => {
                    const option = document.createElement("option");
                    option.value = value; option.textContent = value;
                    select.appendChild(option);
                });
                if (select.querySelector(`option[value="${CSS.escape(currentSelectedValue)}"]`)) { // Use CSS.escape for safety
                    select.value = currentSelectedValue;
                } else {
                    select.value = "";
                }
            }
        });
    }

    // --- Render Salary Table ---
    function renderSalaryTable(dataToRender) {
        salaryTableBody.innerHTML = "";
        if (!dataToRender || dataToRender.length === 0) {
            const tr = document.createElement("tr");
            const cellCount = document.querySelector("#salaryTableBody").closest("table").querySelector("thead tr:first-child").cells.length;
            tr.innerHTML = `<td colspan="${cellCount}" style="text-align:center; padding: 20px;">Không có dữ liệu phù hợp.</td>`;
            salaryTableBody.appendChild(tr);
            return;
        }
        dataToRender.forEach((salary) => {
            const tr = document.createElement("tr");
            tr.innerHTML = `
                <td>${salary.employeeId || ""}</td>
                <td>${salary.employeeName || ""}</td>
                <td>${salary.salaryMonth || ""}</td>
                <td>${salary.position || ""}</td>
                <td>${salary.salaryCoefficient || ""}</td>
                <td>${salary.baseSalary || ""}</td>
                <td>${salary.bonus || ""}</td>
                <td>${salary.netSalary || ""}</td>
                <td>
                    <button class="action-btn complaint" onclick="handleComplaint('${salary.employeeId}', '${salary.salaryMonth}')">
                        Khiếu nại
                    </button>
                </td>
            `;
            salaryTableBody.appendChild(tr);
        });
    }

    // --- Filter Logic ---
    function filterTable() {
        let filteredData = [...salaryData];
        filterSelects.forEach(select => {
            const column = select.dataset.column;
            const selectedValue = select.value;
            if (selectedValue) {
                filteredData = filteredData.filter(salary => {
                    const salaryValue = salary[column] === null || salary[column] === undefined ? "" : String(salary[column]);
                    return salaryValue === selectedValue;
                });
            }
        });
        currentFilteredData = filteredData;
        renderSalaryTable(currentFilteredData);
    }

    // --- Export to Excel Function ---
    function exportToExcel() {
        if (!currentFilteredData || currentFilteredData.length === 0) {
            alert("Không có dữ liệu để xuất Excel.");
            return;
        }
        const dataForExport = currentFilteredData.map(salary => ({
            "Mã NV": salary.employeeId, "Tên nhân viên": salary.employeeName,
            "Tháng lương": salary.salaryMonth, "Chức vụ": salary.position,
            "Hệ số lương": salary.salaryCoefficient, "Lương cơ sở": salary.baseSalary,
            "Thưởng": salary.bonus, "Thực lãnh": salary.netSalary
        }));
        const ws = XLSX.utils.json_to_sheet(dataForExport);
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, "Bảng lương");
        XLSX.writeFile(wb, "BangLuongNhanVien.xlsx");
    }

    // --- Export to PDF Function ---
    function exportToPdf() {
        if (!currentFilteredData || currentFilteredData.length === 0) {
            alert("Không có dữ liệu để xuất PDF.");
            return;
        }

        const { jsPDF } = window.jspdf; // Get jsPDF constructor
        const doc = new jsPDF();        // Create new PDF document

        // --- FONT HANDLING FOR VIETNAMESE (IMPORTANT) ---
        // 1. You need a Unicode TTF font file (e.g., Roboto-Regular.ttf).
        // 2. Convert the TTF file to a Base64 string or a format jsPDF understands.
        //    (Search online for "ttf to jsPDF font converter" or use tools/scripts).
        // 3. Create a JS file (e.g., roboto-font.js) containing the VFS data like:
        //    window.myFontData = 'AAEAAAASAQAABAAgR0RFRgAAAA....'; // Base64 or similar
        // 4. Include that script *before* Salary.js in Salary.html.
        // 5. Uncomment and use the lines below:
        // try {
        //     // doc.addFileToVFS('Roboto-Regular.ttf', window.myFontData); // Add font data
        //     // doc.addFont('Roboto-Regular.ttf', 'Roboto', 'normal');    // Register font
        //     doc.setFont('Roboto', 'normal'); // *** USE THE REGISTERED FONT ***
        // } catch (e) {
        //     console.error("Font loading/setting error:", e);
        //     // Fallback or warning - Default fonts likely won't show Vietnamese correctly
        //     alert("Lỗi tải font tiếng Việt. PDF có thể không hiển thị đúng.");
        //     doc.setFont('helvetica', 'normal'); // Fallback (likely poor for Vietnamese)
        // }
         // --- If NOT using custom font, set a default (may break Vietnamese) ---
         doc.setFont("helvetica", "normal"); // Remove this line if using custom font above

        // --- PDF Content ---
        doc.setFontSize(16);
        doc.text("Bảng lương nhân viên", 14, 15); // Title

        // Define columns for AutoTable (Match the order in `body`)
        const tableColumn = ["Mã NV", "Tên NV", "Tháng lương", "Chức vụ", "Hệ số", "Lương cơ sở", "Thưởng", "Thực lãnh"];
        // Define rows for AutoTable
        const tableRows = currentFilteredData.map(salary => [
            salary.employeeId || "",
            salary.employeeName || "",
            salary.salaryMonth || "",
            salary.position || "",
            salary.salaryCoefficient || "",
            salary.baseSalary || "", // Keep strings as they are formatted
            salary.bonus || "",
            salary.netSalary || ""
        ]);

        // Add table using AutoTable plugin
        doc.autoTable({
            head: [tableColumn],
            body: tableRows,
            startY: 25, // Start table below the title
            theme: "grid", // 'striped', 'grid', 'plain'
            styles: {
                fontSize: 8,
                cellPadding: 2,
                 font: "Roboto" // *** IMPORTANT: Match the font name registered above ***
                // font: 'helvetica' // Use this if NOT using a custom font
            },
            headStyles: {
                fillColor: [22, 160, 133], // Example header color
                textColor: 255,
                fontSize: 9,
                fontStyle: "bold",
                 font: "Roboto" // *** IMPORTANT: Match the font name registered above ***
                // font: 'helvetica' // Use this if NOT using a custom font
            },
            didParseCell: function (data) {
                // --- Potential additional font handling per cell ---
                // This might be needed if the global setFont doesn't suffice
                // if (data.cell.section === 'head' || data.cell.section === 'body') {
                //    data.cell.styles.font = 'Roboto'; // Ensure font for every cell
                // }
            }
        });

        // Save the PDF
        doc.save("BangLuongNhanVien.pdf");
    }


    // --- Complaint Handler Function ---
    function handleComplaint(employeeId, salaryMonth) {
        alert(`Yêu cầu khiếu nại lương tháng ${salaryMonth} cho nhân viên có mã ${employeeId}.`);
        // Implement actual complaint logic here (e.g., redirect, modal, API call)
    }

    // --- Event Listeners ---
    btnExportExcel.addEventListener("click", exportToExcel);
    btnExportPdf.addEventListener("click", exportToPdf); // Add listener for PDF export

    filterSelects.forEach(select => {
        select.addEventListener("change", filterTable);
    });

    // --- Initial Setup ---
    populateFilters();
    filterTable();

    // --- Make functions globally accessible for inline onclick handlers ---
    window.handleComplaint = handleComplaint;

});