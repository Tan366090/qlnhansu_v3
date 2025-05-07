document.addEventListener("DOMContentLoaded", () => {
    const mainContent = document.querySelector(".main-content");
    const navLinks = document.querySelectorAll(".nav-link[data-content]");

    // Function to load content dynamically
    async function loadContent(url) {
        try {
            // Show loading overlay
            document.getElementById("loadingOverlay").style.display = "flex";

            const response = await fetch(url);
            if (!response.ok) throw new Error("Failed to load content");

            const content = await response.text();
            mainContent.innerHTML = content;

            // Update browser history
            history.pushState({ url }, "", url);
        } catch (error) {
            console.error("Error loading content:", error);
            mainContent.innerHTML = `<div class="error-message">Không thể tải nội dung. Vui lòng thử lại sau.</div>`;
        } finally {
            // Hide loading overlay
            document.getElementById("loadingOverlay").style.display = "none";
        }
    }

    // Event listener for menu clicks
    navLinks.forEach((link) => {
        link.addEventListener("click", (event) => {
            event.preventDefault();

            // Remove active class from all links
            navLinks.forEach((link) => link.classList.remove("active"));

            // Add active class to the clicked link
            link.classList.add("active");

            // Load the content
            const url = link.getAttribute("data-content");
            if (url) loadContent(url);
        });
    });

    // Handle browser back/forward buttons
    window.addEventListener("popstate", (event) => {
        if (event.state && event.state.url) {
            loadContent(event.state.url);
        }
    });
});
