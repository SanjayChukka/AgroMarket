const method = document.getElementById('fulfilment-method');
const address = document.getElementById('delivery-fields');
function updateDeliveryFields() {
    address.hidden = method.value === 'pickup';
    for (const field of address.querySelectorAll('[data-delivery-required]')) {
        field.disabled = address.hidden;
        field.required = !address.hidden;
    }
}
method.addEventListener('change', updateDeliveryFields);
window.addEventListener('pageshow', updateDeliveryFields);
updateDeliveryFields();
