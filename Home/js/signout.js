const signoutDialog = document.getElementById("signout-dialog");
document.getElementById("signout-link").addEventListener("click", (event) => {
  if (typeof signoutDialog.showModal === "function") {
    event.preventDefault();
    signoutDialog.showModal();
  }
});
document
  .getElementById("stay-signed-in")
  .addEventListener("click", () => signoutDialog.close());
