/* Request and verify the registration email code without leaving the form. */
const emailField = document.getElementById("email");
const notice = document.getElementById("code-notice");
const codeField = document.getElementById("registration-code");
const sentDialog = document.getElementById("otp-sent-dialog");
document.getElementById("otp-continue").addEventListener("click", () => sentDialog.close());
sentDialog.addEventListener("close", () => codeField.focus());

emailField.addEventListener("input", () => {
    notice.textContent = "";
    codeField.value = "";
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
                    otp: codeField.value.trim(),
                    code_csrf: button.form.elements.code_csrf.value,
                }),
            });
            const result = await response.json();
            if (emailField.value !== email) return;
            notice.textContent = result.message;
            if (response.ok && button.dataset.codeAction === "send" && result.sent === true) {
                codeField.value = "";
                document.getElementById("otp-recipient").textContent = email;
                sentDialog.showModal();
            }
        } catch {
            notice.textContent = "Unable to reach email verification. Try again.";
        } finally {
            button.disabled = false;
        }
    });
}
