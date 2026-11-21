const quantityInput = document.getElementById("quantity");
const totalAmount = document.getElementById("totalAmount");



function calculateTotal() {
    const quantity = Number(quantityInput.value) || 0;
    const amount = quantity * Number(quantityInput.dataset.unitPrice);

    totalAmount.value = "₹" + amount.toLocaleString("en-IN", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

if (quantityInput) {
    quantityInput.addEventListener("input", calculateTotal);
    calculateTotal();
}
