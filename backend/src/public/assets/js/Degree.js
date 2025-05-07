// Centralized state management
const state = {
    degrees: [],
};

// Utility function for API calls
async function fetchAPI(url, options = {}) {
    try {
        const response = await fetch(url, options);
        if (!response.ok) {
            const errorData = await response.json();
            throw new Error(errorData.message || "API error");
        }
        return await response.json();
    } catch (error) {
        console.error("API Error:", error.message);
        showNotification(error.message, "error");
        throw error;
    }
}

// Fetch degrees and update state
async function fetchDegrees() {
    try {
        const result = await fetchAPI("http://localhost/qlnhansu/api/getDegrees.php");
        state.degrees = result.data || [];
        renderDegrees(state.degrees);
    } catch (error) {
        console.error("Error fetching degrees:", error.message);
    }
}

// Render degrees table
function renderDegrees(degrees) {
    const tableBody = document.getElementById("degree-table-body");
    tableBody.innerHTML = "";

    if (degrees.length === 0) {
        tableBody.innerHTML = "<tr><td colspan=\"6\" class=\"no-data\">Không có dữ liệu bằng cấp</td></tr>";
        return;
    }

    degrees.forEach((degree) => {
        const row = document.createElement("tr");
        row.innerHTML = `
            <td>${degree.name}</td>
            <td>${degree.issue_date}</td>
            <td>${degree.expiry_date || "N/A"}</td>
            <td><a href="${degree.attachment_url}" target="_blank">Xem</a></td>
            <td>
                <input type="checkbox" class="active-checkbox" data-id="${degree.degree_id}" ${degree.is_active ? "checked" : ""}>
            </td>
            <td>
                <button class="btn btn-danger delete-button" data-id="${degree.degree_id}">Xóa</button>
                <button class="btn btn-primary edit-button" data-id="${degree.degree_id}">Sửa</button>
            </td>
        `;
        tableBody.appendChild(row);
    });

    attachEventListeners();
}

// Attach event listeners
function attachEventListeners() {
    document.querySelectorAll(".delete-button").forEach((button) => {
        button.addEventListener("click", () => handleDelete(button.dataset.id));
    });

    document.querySelectorAll(".active-checkbox").forEach((checkbox) => {
        checkbox.addEventListener("change", () => handleStatusChange(checkbox.dataset.id, checkbox.checked));
    });

    document.querySelectorAll(".edit-button").forEach((button) => {
        button.addEventListener("click", () => handleEdit(button.dataset.id));
    });
}

// Handle delete degree
async function handleDelete(degreeId) {
    if (!confirm("Bạn có chắc chắn muốn xóa bằng cấp này?")) return;

    try {
        await fetchAPI("http://localhost/qlnhansu/api/deleteDegree.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ degree_id: degreeId }),
        });
        showNotification("Xóa bằng cấp thành công.", "success");
        fetchDegrees();
    } catch (error) {
        console.error("Error deleting degree:", error.message);
    }
}

// Handle status change
async function handleStatusChange(degreeId, isActive) {
    try {
        await fetchAPI("http://localhost/qlnhansu/api/updateDegreeStatus.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ degree_id: degreeId, is_active: isActive }),
        });
        showNotification("Trạng thái bằng cấp đã được cập nhật.", "success");
    } catch (error) {
        console.error("Error updating degree status:", error.message);
    }
}

// Handle edit degree
async function handleEdit(degreeId) {
    try {
        const response = await fetchAPI(`http://localhost/qlnhansu/api/getDegreeById.php?id=${degreeId}`);
        const degree = response;

        if (degree.error) {
            showNotification("Không thể tải dữ liệu bằng cấp.", "error");
            return;
        }

        // Populate the modal with the degree data
        document.getElementById("editTenBangCap").value = degree.degree_name;
        document.getElementById("editNgayCap").value = degree.issue_date;

        // Show the modal
        const editModal = document.getElementById("editDegreeModal");
        const overlay = document.getElementById("modalOverlay");
        editModal.style.display = "block";
        overlay.style.display = "block";

        // Update the form submission to handle editing
        const form = document.getElementById("editDegreeForm");
        form.onsubmit = async function (event) {
            event.preventDefault();

            const updatedDegree = {
                id: degreeId,
                name: document.getElementById("editTenBangCap").value.trim(),
                issue_date: document.getElementById("editNgayCap").value,
            };

            try {
                const updateResponse = await fetchAPI("http://localhost/qlnhansu/api/updateDegree.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify(updatedDegree),
                });

                if (updateResponse.error) {
                    showNotification("Không thể cập nhật bằng cấp.", "error");
                } else {
                    showNotification("Cập nhật bằng cấp thành công.", "success");
                    fetchDegrees(); // Refresh the degree list
                    editModal.style.display = "none"; // Hide the modal
                    overlay.style.display = "none"; // Hide the overlay
                }
            } catch (error) {
                console.error("Error updating degree:", error.message);
                showNotification("Có lỗi xảy ra khi cập nhật bằng cấp.", "error");
            }
        };
    } catch (error) {
        console.error("Error fetching degree:", error.message);
        showNotification("Có lỗi xảy ra khi tải dữ liệu bằng cấp.", "error");
    }
}

// Define showNotification globally with Vietnamese messages
function showNotification(message, type = "success") {
    const notification = document.createElement("div");
    notification.className = `notification ${type}`;
    notification.textContent = message;

    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background-color: ${type === "success" ? "#4CAF50" : "#f44336"};
        color: white;
        padding: 15px;
        border-radius: 5px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        z-index: 1001;
        font-size: 14px;
    `;

    document.body.appendChild(notification);

    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Load degrees on page load
document.addEventListener("DOMContentLoaded", fetchDegrees);
