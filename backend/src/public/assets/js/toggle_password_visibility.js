document.addEventListener("DOMContentLoaded", function () {
    const toggleIcons = document.querySelectorAll(".toggle-password");

    toggleIcons.forEach((icon) => {
        icon.addEventListener("click", function () {
            const input = document.getElementById(this.dataset.target);
            const imgElement = this.querySelector("img");
            if (input.type === "password") {
                input.type = "text";
                imgElement.src = "./img/Eye.png";
            } else {
                input.type = "password";
                imgElement.src = "./img/Eye.png";
            }
        });
    });
});
