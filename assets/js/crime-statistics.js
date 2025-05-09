document.addEventListener("DOMContentLoaded", () => {
    // Ensure the "apply-filters" button exists
    const applyFiltersButton = document.getElementById("apply-filters");
    if (!applyFiltersButton) {
        console.error("Error: Apply Filters button not found. Ensure the button exists in the HTML.");
        return;
    }

    // Set up event listener for the Apply Filters button
    applyFiltersButton.addEventListener("click", () => {
        console.log("Fetching crime statistics...");
        fetchCrimeStatistics();
    });

    // Function to fetch and display crime statistics
    function fetchCrimeStatistics() {
        fetch(ajaxurl, {
            method: "POST",
            credentials: "same-origin",
            headers: {
                "X-WP-Nonce": crimeStatistics.nonce,
            },
            body: new URLSearchParams({
                action: "fetch_crime_statistics",
            }),
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
                    console.log("Fetched Data:", data.data);
                    const { categories, dates, reports } = data.data;

                    // Populate the list view
                    populateCrimeList(reports);

                    // Render charts
                    renderCategoryPieChart(categories);
                    renderCrimesByDateChart(dates);
                } else {
                    console.error("Error fetching data:", data.data);
                }
            })
            .catch((error) => console.error("Fetch error:", error));
    }

    // Function to populate the list view with crime reports
    function populateCrimeList(reports) {
        const crimeList = document.getElementById("crimeList");
        crimeList.innerHTML = ""; // Clear existing content
        reports.forEach((report) => {
            const li = document.createElement("li");
            li.textContent = `${report.title} (${report.category}) - ${report.location}`;
            crimeList.appendChild(li);
        });
    }

    // Function to render the category pie chart
    function renderCategoryPieChart(categories) {
        const pieCtx = document.getElementById("crimeCategoriesPieChart").getContext("2d");
        new Chart(pieCtx, {
            type: "pie",
            data: {
                labels: Object.keys(categories),
                datasets: [
                    {
                        data: Object.values(categories),
                        backgroundColor: ["#FF6384", "#36A2EB", "#FFCE56", "#4BC0C0", "#9966FF", "#FF9F40"],
                    },
                ],
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: "top",
                    },
                    title: {
                        display: true,
                        text: "Crime Categories Breakdown",
                    },
                },
            },
        });
    }

    // Function to render the crimes by date chart
    function renderCrimesByDateChart(dates) {
        const lineCtx = document.getElementById("crimesByDayChart").getContext("2d");
        new Chart(lineCtx, {
            type: "line",
            data: {
                labels: Object.keys(dates),
                datasets: [
                    {
                        label: "Crimes by Date",
                        data: Object.values(dates),
                        borderColor: "#36A2EB",
                        fill: false,
                    },
                ],
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: "top",
                    },
                    title: {
                        display: true,
                        text: "Crimes by Date",
                    },
                },
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: "Date",
                        },
                    },
                    y: {
                        title: {
                            display: true,
                            text: "Number of Crimes",
                        },
                        beginAtZero: true,
                    },
                },
            });
        }
    }

    // Fetch and display statistics on page load
    fetchCrimeStatistics();
});