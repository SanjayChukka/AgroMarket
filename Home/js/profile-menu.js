const profileButton = document.getElementById("profileToggle");
const profilePopup = document.getElementById("profilePopup");

function setProfileOpen(open) {
  profilePopup.classList.toggle("show", open);
  profileButton.setAttribute("aria-expanded", String(open));
}

profileButton.addEventListener("click", () => {
  setProfileOpen(!profilePopup.classList.contains("show"));
});

document.addEventListener("click", (event) => {
  if (!event.target.closest(".profile-menu")) setProfileOpen(false);
});
