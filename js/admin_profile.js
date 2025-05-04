
document.addEventListener("DOMContentLoaded", function() {
    let userBtn = document.querySelector("#user-btn");
    let profileDropdown = document.querySelector(".profile");

    userBtn.addEventListener("click", function() {
        profileDropdown.classList.toggle("active");
    });

    // Hide the dropdown when clicking outside
    document.addEventListener("click", function(e) {
        if (!userBtn.contains(e.target) && !profileDropdown.contains(e.target)) {
            profileDropdown.classList.remove("active");
        }
    });
});
