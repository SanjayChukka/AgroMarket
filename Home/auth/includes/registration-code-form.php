<div class="registration-email-code">
    <input type="hidden" name="code_csrf" value="<?= htmlspecialchars($_SESSION['code_csrf']) ?>">
    <button type="button" data-code-action="send">Send email code</button>
    <label for="registration-code">Email verification code</label>
    <input id="registration-code" inputmode="numeric" autocomplete="one-time-code" maxlength="6" placeholder="6-digit code">
    <button type="button" data-code-action="verify">Verify email</button>
    <small id="code-notice" role="status"></small>
</div>
<dialog id="otp-sent-dialog" class="registration-result" aria-labelledby="otp-title" aria-describedby="otp-description">
    <div class="popup-card">
        <h2 id="otp-title">OTP sent successfully</h2>
        <p id="otp-description">Check <strong id="otp-recipient"></strong> for your six-digit code. It expires in 10 minutes.</p>
        <button type="button" id="otp-continue">Enter code</button>
    </div>
</dialog>
<script type="module" src="js/registration-code.js"></script>
