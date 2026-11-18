
const profileToggle = document.getElementById("profileToggle");
const profilePopup = document.getElementById("profilePopup");

if (profileToggle && profilePopup) {
    profileToggle.addEventListener("click", function () {
        profilePopup.classList.toggle("show");
    });

    document.addEventListener("click", function (event) {
        if (!event.target.closest(".profile-menu")) {
            profilePopup.classList.remove("show");
        }
    });
}