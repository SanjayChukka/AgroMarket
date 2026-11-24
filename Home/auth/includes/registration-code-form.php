<div class="registration-email-code">
    <input type="hidden" name="code_csrf" value="<?= htmlspecialchars($_SESSION['code_csrf']) ?>">
    <button type="button" data-code-action="send">Send email code</button>
    <label for="registration-code">Email verification code</label>
    <input id="registration-code" inputmode="numeric" autocomplete="one-time-code" maxlength="6" placeholder="6-digit code">
    <button type="button" data-code-action="verify">Verify email</button>
    <small id="code-notice" role="status"></small>
</div>
<script type="module" src="js/registration-code.js"></script>
