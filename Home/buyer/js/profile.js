
// Get the profile picture input and preview elements.
const pictureInput = document.getElementById("profilePicture");
const preview = document.querySelector(".buyer-profile-preview");


// Store the original profile preview contents.
const savedContents = Array.from(preview.childNodes);

let previewUrl = null;


// Update the profile picture preview when a file is selected.
pictureInput.addEventListener("change", () => {

    // Release the previous object URL to prevent memory leaks.
    if (previewUrl) {
        URL.revokeObjectURL(previewUrl);
    }

    previewUrl = null;


    // Restore the original profile preview.
    preview.style.backgroundImage = "";

    preview.replaceChildren(...savedContents);


    // Retrieve the selected image file.
    const file = pictureInput.files[0];

    // Stop processing if no file is selected.
    if (!file) {
        return;
    }


    // Create a temporary URL for the selected image.
    previewUrl = URL.createObjectURL(file);


    // Remove the existing preview contents.
    preview.replaceChildren();


    // Display the selected profile picture.
    preview.style.backgroundImage = `url("${previewUrl}")`;

});
