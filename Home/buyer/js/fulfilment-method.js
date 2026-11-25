
// Get fulfilment method and delivery field elements.
const method = document.getElementById("fulfilment-method");
const address = document.getElementById("delivery-fields");


// Update delivery fields based on the selected fulfilment method.
function updateDeliveryFields() {

    // Hide delivery fields when pickup is selected.
    address.hidden = method.value === "pickup";

    // Update the disabled and required attributes.
    for (const field of address.querySelectorAll("[data-delivery-required]")) {

        field.disabled = address.hidden;

        field.required = !address.hidden;
    }
}


// Update delivery fields when the fulfilment method changes.
method.addEventListener("change", updateDeliveryFields);


// Restore the correct field state during browser navigation.
window.addEventListener("pageshow", updateDeliveryFields);


// Initialize delivery fields when the page loads.
updateDeliveryFields();
