// Profile picture preview
const profilePicture = document.getElementById("profilePicture");
const profilePreview = document.querySelector(".buyer-profile-preview");

profilePicture.addEventListener("change", function() {
    const file = this.files[0];

    if (file) {
        profilePreview.style.backgroundImage =
            `url('${URL.createObjectURL(file)}')`;
        profilePreview.textContent = "";
    }
});
