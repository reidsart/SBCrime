document.addEventListener("DOMContentLoaded", () => {
    const filterForm = document.getElementById("crime-stats-filter-form");
    const applyFiltersButton = document.getElementById("apply-filters");

    applyFiltersButton.addEventListener("click", () => {
        const formData = new FormData(filterForm);

        fetch(ajaxurl, {
            method: "POST",
            credentials: "same-origin",
            headers: {
                "X-WP-Nonce": crimeStatistics.nonce,
            },
            body: new URLSearchParams({
                action: "fetch_crime_statistics",
                security: crimeStatistics.nonce,
                ...Object.fromEntries(formData),
            }),
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
                    // Update the charts, map, and list view with new data
                    console.log("Filtered Data:", data.data);
                } else {
                    console.error("Error fetching data:", data.data);
                }
            })
            .catch((error) => console.error("Fetch error:", error));
    });
});