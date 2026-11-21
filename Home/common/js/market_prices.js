const searchInput = document.getElementById("cropSearch");
const categoryFilter = document.getElementById("categoryFilter");
const locationFilter = document.getElementById("locationFilter");

const cards = document.querySelectorAll(".market-card");
const noProducts = document.getElementById("noProducts");

function filterProducts() {

    const searchValue = searchInput.value.toLowerCase().trim();
    const categoryValue = categoryFilter.value;
    const locationValue = locationFilter.value;

    let visibleCount = 0;

    cards.forEach(card => {

        const cropName = card.querySelector("h3").textContent.toLowerCase();

        const matchesSearch = cropName.includes(searchValue);

        const matchesCategory =
            categoryValue === "all" ||
            card.dataset.category === categoryValue;

        const matchesLocation =
            locationValue === "all" ||
            card.dataset.location === locationValue;

        const shouldDisplay =
            matchesSearch && matchesCategory && matchesLocation;

        card.hidden = !shouldDisplay;

        if (shouldDisplay) {
            visibleCount++;
        }

    });

    noProducts.hidden = visibleCount !== 0;
}

searchInput.addEventListener("input", filterProducts);
categoryFilter.addEventListener("change", filterProducts);
locationFilter.addEventListener("change", filterProducts);
