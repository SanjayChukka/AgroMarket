
<dialog
    id="popupCard"
    class="registration-result"
    aria-labelledby="popupTitle"
    aria-describedby="popupText"
    data-type="<?php echo htmlspecialchars($popupType, ENT_QUOTES, 'UTF-8'); ?>"
    data-message="<?php echo htmlspecialchars($popupMessage, ENT_QUOTES, 'UTF-8'); ?>"
>
    <div class="popup-card">

        <div
            id="popupIcon"
            class="popup-icon"
            aria-hidden="true"
        ></div>

        <h2 id="popupTitle"></h2>

        <p id="popupText"></p>

        <button
            type="button"
            id="closePopup"
            autofocus
        >
            OK
        </button>

    </div>
</dialog>
