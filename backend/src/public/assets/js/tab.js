document.addEventListener("DOMContentLoaded", function () {
    const tabs = document.querySelectorAll(".nav-tabs .nav-link");
    tabs.forEach((tab) => {
        tab.addEventListener("click", function (e) {
            e.preventDefault();
            tabs.forEach((t) => t.classList.remove("active"));
            document
                .querySelectorAll(".tab-content .tab-pane")
                .forEach((pane) => {
                    pane.classList.remove("show", "active");
                });
            this.classList.add("active");
            const targetId = this.getAttribute("href");
            document.querySelector(targetId).classList.add("show", "active");
        });
    });
});
