
/* Request and verify the registration email code without leaving the form. */

const emailField = document.getElementById("email");
const notice = document.getElementById("code-notice");
const codeField = document.getElementById("registration-code");
const sentDialog = document.getElementById("otp-sent-dialog");


// Close the OTP success dialog.
document.getElementById("otp-continue").addEventListener("click", () => {
    sentDialog.close();
});


// Focus on the OTP input after closing the dialog.
sentDialog.addEventListener("close", () => {
    codeField.focus();
});


// Clear the previous verification message and code when the email changes.
emailField.addEventListener("input", () => {
    notice.textContent = "";
    codeField.value = "";
});


// Handle Send Email Code and Verify Email button actions.
for (const button of document.querySelectorAll("[data-code-action]")) {

    button.addEventListener("click", async () => {

        // Validate the email address.
        if (!emailField.value.trim() || !emailField.reportValidity()) {
            emailField.focus();
            return;
        }

        const email = emailField.value;

        // Prevent repeated clicks while processing the request.
        button.disabled = true;

        try {

            // Send the verification request to the PHP endpoint.
            const response = await fetch("registration_code.php", {
                method: "POST",

                body: new URLSearchParams({
                    action: button.dataset.codeAction,
                    email,
                    otp: codeField.value.trim(),
                    code_csrf: button.form.elements.code_csrf.value
                })
            });

            // Convert the server response into a JavaScript object.
            const result = await response.json();

            // Ignore the response if the email address has changed.
            if (emailField.value !== email) {
                return;
            }

            // Display the verification message.
            notice.textContent = result.message;

            // Display the success popup when the OTP is sent.
            if (
                response.ok &&
                button.dataset.codeAction === "send" &&
                result.sent === true
            ) {
                codeField.value = "";

                document.getElementById("otp-recipient").textContent = email;

                sentDialog.showModal();
            }

        } catch {

            // Handle network or unexpected response errors.
            notice.textContent = "Unable to reach email verification. Try again.";

        } finally {

            // Re-enable the button after completing the request.
            button.disabled = false;
        }
    });
}
