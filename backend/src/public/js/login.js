document.addEventListener("DOMContentLoaded", () => {
    const loginForm = document.getElementById("loginForm");
    const submitButton = document.getElementById("submitButton");
    const loading = document.getElementById("loading");
    const errorDiv = document.getElementById("errorDiv");

    // Display error message with optional timeout
    function showError(message, timeout = 5000) {
        console.error("Login Error:", message);
        errorDiv.textContent = message;
        errorDiv.style.display = "block";
        if (timeout) {
            setTimeout(() => {
                errorDiv.style.display = "none";
            }, timeout);
        }
    }

    // Handle form submission
    loginForm.addEventListener("submit", async (e) => {
        e.preventDefault();
        
        // Show loading state
        submitButton.disabled = true;
        loading.style.display = "block";
        errorDiv.style.display = "none";

        try {
            const formData = new FormData(loginForm);
            const data = Object.fromEntries(formData.entries());
            
            // Debug log
            console.log("Sending data:", data);

            // Send login request
            const response = await fetch("login_simple.php", {
                method: "POST",
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(data)
            });

            // Debug log
            console.log("Response status:", response.status);
            const responseText = await response.text();
            console.log("Raw response:", responseText);

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}, response: ${responseText}`);
            }

            // Parse the response as JSON
            const result = JSON.parse(responseText);
            
            if (result.success) {
                // Redirect to the dashboard
                window.location.href = result.redirectUrl;
            } else {
                showError(result.error || "Đăng nhập thất bại");
            }
        } catch (error) {
            console.error("Login error:", error);
            showError("Có lỗi xảy ra khi đăng nhập. Vui lòng thử lại.");
        } finally {
            // Reset loading state
            submitButton.disabled = false;
            loading.style.display = "none";
        }
    });
});
