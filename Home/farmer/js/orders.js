let selectedAction = null;

const confirmationOverlay = document.getElementById("confirmationOverlay");

const confirmationTitle = document.getElementById("confirmationTitle");

const confirmationMessage = document.getElementById("confirmationMessage");

const termsArea = document.getElementById("termsArea");

const termsCheckbox = document.getElementById("termsCheckbox");

const confirmButton = document.getElementById("confirmAction");

function openConfirmation(orderId, action) {
  selectedAction = action;

  document.getElementById("selectedOrderId").value = orderId;

  document.getElementById("selectedAction").value = action;

  document.getElementById("termsAccepted").value = "0";

  termsCheckbox.checked = false;

  if (action === "accept") {
    confirmationTitle.textContent = "Accept Order?";

    confirmationMessage.textContent =
      "Are you sure you want to accept this order? Please review the order details before confirming.";

    termsArea.style.display = "block";

    confirmButton.textContent = "Accept Order";
  } else {
    confirmationTitle.textContent = "Reject Order?";

    confirmationMessage.textContent =
      "Are you sure you want to reject this buyer's order?";

    termsArea.style.display = "none";

    confirmButton.textContent = "Reject Order";
  }

  confirmationOverlay.style.display = "flex";
}

function closeConfirmation() {
  confirmationOverlay.style.display = "none";

  selectedAction = null;
}

// --------------------------------
// Submit Decision to PHP
// --------------------------------

confirmButton.addEventListener("click", function () {
  if (selectedAction === "accept") {
    if (!termsCheckbox.checked) {
      alert("Please accept the terms and conditions first.");

      return;
    }

    document.getElementById("termsAccepted").value = "1";
  } else {
    document.getElementById("termsAccepted").value = "0";
  }

  document.getElementById("orderActionForm").submit();
});

// Close popup when clicking outside
confirmationOverlay.addEventListener("click", function (event) {
  if (event.target === confirmationOverlay) {
    closeConfirmation();
  }
});

document.querySelectorAll("[data-order-action]").forEach((button) => {
  button.addEventListener("click", () =>
    openConfirmation(button.dataset.orderId, button.dataset.orderAction),
  );
});
document.querySelectorAll("[data-close-confirmation]").forEach((button) => {
  button.addEventListener("click", closeConfirmation);
});
