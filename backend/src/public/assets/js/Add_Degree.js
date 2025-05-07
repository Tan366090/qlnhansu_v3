fetch("sidebar.html")
    .then((response) => response.text())
    .then((data) => {
        document.getElementById("sidebar-container").innerHTML = data;
    });

document.addEventListener("DOMContentLoaded", function () {
    const saveButton = document.getElementById("saveButton");
    const certificateNameInput = document.getElementById("certificate-name");
    const certificateFileInput = document.getElementById("certificate-file");
    const issueDateInput = document.getElementById("issue-date");
    const validityInput = document.getElementById("validity");

    // Show "Lưu" button when any input changes
    const inputs = [
        certificateNameInput,
        certificateFileInput,
        issueDateInput,
        validityInput,
    ];
    inputs.forEach((input) => {
        input.addEventListener("input", () => {
            saveButton.style.display = "inline-block";
        });
    });

    // Handle "Lưu" button click
    saveButton.addEventListener("click", async () => {
        const formData = new FormData();
        formData.append("certificate_name", certificateNameInput.value);
        formData.append("certificate_file", certificateFileInput.files[0]);
        formData.append("issue_date", issueDateInput.value);
        formData.append("validity", validityInput.value);

        try {
            const response = await fetch("http://localhost:4000/api/degrees", {
                method: "POST",
                body: formData,
            });

            if (!response.ok) {
                throw new Error("Failed to save degree data");
            }

            alert("Dữ liệu đã được lưu thành công!");
            saveButton.style.display = "none"; // Hide "Lưu" button after saving
            loadDegrees(); // Reload data after saving
        } catch (error) {
            console.error("Error saving degree data:", error.message);
            alert("Có lỗi xảy ra khi lưu dữ liệu!");
        }
    });

    // Function to load degrees (optional, if you want to display them)
    async function loadDegrees() {
        try {
            const response = await fetch("http://localhost:4000/api/degrees", {
                method: "GET",
            });

            if (!response.ok) {
                throw new Error("Failed to load degree data");
            }

            const data = await response.json();
            console.log("Loaded degrees:", data);
            // Optional: Render data into a table or list
        } catch (error) {
            console.error("Error loading degree data:", error.message);
        }
    }

    // Initial load (optional)
    loadDegrees();
});

document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("addDegreeForm");
    const cancelButton = document.getElementById("cancelButton");

    // Handle form submission
    form.addEventListener("submit", async function (event) {
        event.preventDefault();

        const degreeName = document.getElementById("degreeName").value.trim();
        const issueDate = document.getElementById("issueDate").value;
        const validity = document.getElementById("validity").value.trim();
        const attachment = document.getElementById("attachment").files[0];

        // Validate inputs
        if (!degreeName || !issueDate) {
            alert("Vui lòng nhập đầy đủ thông tin bắt buộc.");
            return;
        }

        const formData = new FormData();
        formData.append("degreeName", degreeName);
        formData.append("issueDate", issueDate);
        formData.append("validity", validity);
        if (attachment) {
            formData.append("attachment", attachment);
        }

        try {
            const response = await fetch(
                "http://localhost/qlnhansu/api/addDegree.php",
                {
                    method: "POST",
                    body: formData,
                }
            );

            const result = await response.json();
            if (result.error) {
                alert(result.error);
            } else {
                alert("Thêm bằng cấp thành công!");
                window.location.href = "Degree.html"; // Redirect to Degree page
            }
        } catch (error) {
            console.error("Error adding degree:", error.message);
            alert("Có lỗi xảy ra khi thêm bằng cấp. Vui lòng thử lại.");
        }
    });

    // Handle cancel button
    cancelButton.addEventListener("click", function () {
        window.location.href = "Degree.html"; // Redirect to Degree page
    });
});
