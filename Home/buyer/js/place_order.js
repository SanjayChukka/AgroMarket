const quantityInput = document.getElementById("quantity");
const currency = new Intl.NumberFormat("en-IN", { style: "currency", currency: "INR" });

function calculateTotal() {
    const quantity = Number(quantityInput.value) || 0;
    const milliUnits = Math.round(quantity * 1000);
    const pricePaise = Math.round(Number(quantityInput.dataset.unitPrice) * 100);
    const subtotal = Math.round(milliUnits * pricePaise / 1000);
    const buyerFee = Math.round(subtotal * Number(quantityInput.dataset.buyerRate) / 100);
    document.getElementById("cropSubtotal").value = currency.format(subtotal / 100);
    document.getElementById("buyerFee").value = currency.format(buyerFee / 100);
    document.getElementById("totalAmount").value = currency.format((subtotal + buyerFee) / 100);
}

if (quantityInput) {
    quantityInput.addEventListener("input", calculateTotal);
    calculateTotal();
}
