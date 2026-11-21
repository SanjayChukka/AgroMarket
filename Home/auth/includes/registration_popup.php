<div id="popupCard" class="popup-overlay"
     data-type="<?= htmlspecialchars($popupType, ENT_QUOTES, 'UTF-8') ?>"
     data-message="<?= htmlspecialchars($popupMessage, ENT_QUOTES, 'UTF-8') ?>">
    <div class="popup-card">
        <div id="popupIcon" class="popup-icon"></div>
        <h2 id="popupTitle"></h2>
        <p id="popupText"></p>
        <button type="button" id="closePopup">OK</button>
    </div>
</div>
