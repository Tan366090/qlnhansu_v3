// Contract management functionality
document.addEventListener("DOMContentLoaded", () => {
    loadContracts();
    setupEventListeners();
});

async function loadContracts() {
    try {
        const response = await fetch("/api/contracts");
        if (!response.ok) throw new Error("Failed to fetch contracts");
        const contracts = await response.json();
        displayContracts(contracts);
    } catch (error) {
        console.error("Error loading contracts:", error);
        showNotification("error", "Failed to load contracts");
    }
}

function displayContracts(contracts) {
    const tbody = document.querySelector("#contractTable tbody");
    if (!tbody) return;

    tbody.innerHTML = contracts.map(contract => `
        <tr>
            <td>${contract.id}</td>
            <td>${contract.employeeName}</td>
            <td>${contract.position}</td>
            <td>${contract.startDate}</td>
            <td>${contract.endDate}</td>
            <td>${contract.salary}</td>
            <td>
                <button class="btn btn-primary btn-sm edit-contract" data-id="${contract.id}">Edit</button>
                <button class="btn btn-danger btn-sm delete-contract" data-id="${contract.id}">Delete</button>
            </td>
        </tr>
    `).join("");
}

function setupEventListeners() {
    // Add Contract Form Submit
    const addForm = document.getElementById("addContractForm");
    if (addForm) {
        addForm.addEventListener("submit", async (e) => {
            e.preventDefault();
            const formData = new FormData(addForm);
            try {
                const response = await fetch("/api/contracts", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                    },
                    body: JSON.stringify(Object.fromEntries(formData))
                });
                
                if (!response.ok) throw new Error("Failed to add contract");
                
                showNotification("success", "Contract added successfully");
                addForm.reset();
                loadContracts();
            } catch (error) {
                console.error("Error adding contract:", error);
                showNotification("error", "Failed to add contract");
            }
        });
    }

    // Delete Contract
    document.addEventListener("click", async (e) => {
        if (e.target.classList.contains("delete-contract")) {
            const id = e.target.dataset.id;
            if (confirm("Are you sure you want to delete this contract?")) {
                try {
                    const response = await fetch(`/api/contracts/${id}`, {
                        method: "DELETE"
                    });
                    
                    if (!response.ok) throw new Error("Failed to delete contract");
                    
                    showNotification("success", "Contract deleted successfully");
                    loadContracts();
                } catch (error) {
                    console.error("Error deleting contract:", error);
                    showNotification("error", "Failed to delete contract");
                }
            }
        }
    });

    // Edit Contract
    document.addEventListener("click", async (e) => {
        if (e.target.classList.contains("edit-contract")) {
            const id = e.target.dataset.id;
            try {
                const response = await fetch(`/api/contracts/${id}`);
                if (!response.ok) throw new Error("Failed to fetch contract details");
                
                const contract = await response.json();
                // Populate edit form with contract details
                const editForm = document.getElementById("editContractForm");
                if (editForm) {
                    Object.keys(contract).forEach(key => {
                        const input = editForm.querySelector(`[name="${key}"]`);
                        if (input) input.value = contract[key];
                    });
                    // Show edit modal
                    const editModal = new bootstrap.Modal(document.getElementById("editContractModal"));
                    editModal.show();
                }
            } catch (error) {
                console.error("Error fetching contract details:", error);
                showNotification("error", "Failed to load contract details");
            }
        }
    });

    // Edit Form Submit
    const editForm = document.getElementById("editContractForm");
    if (editForm) {
        editForm.addEventListener("submit", async (e) => {
            e.preventDefault();
            const formData = new FormData(editForm);
            const id = formData.get("id");
            
            try {
                const response = await fetch(`/api/contracts/${id}`, {
                    method: "PUT",
                    headers: {
                        "Content-Type": "application/json",
                    },
                    body: JSON.stringify(Object.fromEntries(formData))
                });
                
                if (!response.ok) throw new Error("Failed to update contract");
                
                showNotification("success", "Contract updated successfully");
                bootstrap.Modal.getInstance(document.getElementById("editContractModal")).hide();
                loadContracts();
            } catch (error) {
                console.error("Error updating contract:", error);
                showNotification("error", "Failed to update contract");
            }
        });
    }
}

// Notification function
function showNotification(type, message) {
    const toast = document.createElement("div");
    toast.className = `toast align-items-center text-white bg-${type === "success" ? "success" : "danger"} border-0`;
    toast.setAttribute("role", "alert");
    toast.setAttribute("aria-live", "assertive");
    toast.setAttribute("aria-atomic", "true");
    
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    `;
    
    document.body.appendChild(toast);
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
    
    toast.addEventListener("hidden.bs.toast", () => {
        toast.remove();
    });
} 