
// Get the quantity input element.
const quantityInput = document.getElementById("quantity");


// Configure Indian currency formatting.
const currency = new Intl.NumberFormat("en-IN", {
    style: "currency",
    currency: "INR"
});


// Calculate crop subtotal, buyer commission, and total amount.
function calculateTotal() {

    // Retrieve the entered quantity.
    const quantity = Number(quantityInput.value) || 0;

    // Convert quantity into milliunits for calculation precision.
    const milliUnits = Math.round(quantity * 1000);

    // Convert the unit price from rupees to paise.
    const pricePaise = Math.round(
        Number(quantityInput.dataset.unitPrice) * 100
    );

    // Calculate the crop subtotal in paise.
    const subtotal = Math.round(
        (milliUnits * pricePaise) / 1000
    );

    // Calculate the buyer commission.
    const buyerFee = Math.round(
        (subtotal * Number(quantityInput.dataset.buyerRate)) / 100
    );


    // Display the crop subtotal.
    document.getElementById("cropSubtotal").value = currency.format(
        subtotal / 100
    );

    // Display the buyer commission.
    document.getElementById("buyerFee").value = currency.format(
        buyerFee / 100
    );

    // Display the total payable amount.
    document.getElementById("totalAmount").value = currency.format(
        (subtotal + buyerFee) / 100
    );
}


// Initialize calculation and update totals when quantity changes.
if (quantityInput) {

    quantityInput.addEventListener("input", calculateTotal);

    calculateTotal();
}