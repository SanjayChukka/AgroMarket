const pictureInput = document.getElementById("profilePicture");
const preview = document.querySelector(".buyer-profile-preview");
const savedContents = Array.from(preview.childNodes);
let previewUrl = null;

pictureInput.addEventListener("change", () => {
    if (previewUrl) URL.revokeObjectURL(previewUrl);
    previewUrl = null;
    preview.style.backgroundImage = "";
    preview.replaceChildren(...savedContents);

    const file = pictureInput.files[0];
    if (!file) return;
    previewUrl = URL.createObjectURL(file);
    preview.replaceChildren();
    preview.style.backgroundImage = `url("${previewUrl}")`;
});
