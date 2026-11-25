// Preview the selected profile picture
const profilePicture = document.getElementById("profilePicture");
const profilePreview = document.getElementById("profilePreview");
const profileInitialElement = document.getElementById("profileInitial");

profilePicture.addEventListener("change", function () {
  const file = this.files[0];

  if (!file) {
    return;
  }

  const allowedTypes = ["image/jpeg", "image/png", "image/webp"];

  if (!allowedTypes.includes(file.type)) {
    alert("Please select a JPG, PNG or WEBP image.");
    this.value = "";
    return;
  }

  if (file.size > 5 * 1024 * 1024) {
    alert("Profile picture must be smaller than 5 MB.");
    this.value = "";
    return;
  }

  const reader = new FileReader();

  reader.onload = function (event) {
    profilePreview.src = event.target.result;
    profilePreview.hidden = false;
    profileInitialElement.hidden = true;
  };

  reader.readAsDataURL(file);
});
