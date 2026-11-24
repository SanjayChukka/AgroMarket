const emailField = document.getElementById("email");
const notice = document.getElementById("code-notice");
emailField.addEventListener("input", () => {
  notice.textContent = "";
});
for (const button of document.querySelectorAll("[data-code-action]")) {
  button.addEventListener("click", async () => {
    if (!emailField.value.trim() || !emailField.reportValidity()) {
      emailField.focus();
      return;
    }
    const email = emailField.value;
    button.disabled = true;
    try {
      const response = await fetch("registration_code.php", {
        method: "POST",
        body: new URLSearchParams({
          action: button.dataset.codeAction,
          email,
          otp: document.getElementById("registration-code").value.trim(),
          code_csrf: button.form.elements.code_csrf.value,
        }),
      });
      const result = await response.json();
      if (emailField.value === email) notice.textContent = result.message;
    } catch {
      notice.textContent = "Unable to reach email verification. Try again.";
    } finally {
      button.disabled = false;
    }
  });
}
