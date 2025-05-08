<?php
/**
 * Sandbaai Crime Statistics Dashboard
 */

class Sandbaai_Crime_Statistics_Dashboard {

    public function __construct() {
        // Remove admin menu registration to avoid duplication.
        // The main plugin file will handle adding the menu item.
    }

    /**
     * Render the statistics page.
     */
    public function render_statistics_page() {
        ?>
        <div class="wrap sandbaai-crime-statistics">
            <h1>Crime Statistics Dashboard</h1>
            
            <form id="crime-stats-filters">
                <label for="filter-month">Month:</label>
                <select id="filter-month" name="filter-month">
                    <option value="all">All</option>
                    <?php for ($m = 1; $m <= 12; $m++): ?>
                        <option value="<?php echo $m; ?>"><?php echo date('F', mktime(0, 0, 0, $m, 1)); ?></option>
                    <?php endfor; ?>
                </select>

                <label for="filter-year">Year:</label>
                <select id="filter-year" name="filter-year">
                    <option value="all">All</option>
                    <?php for ($y = date('Y'); $y >= 2000; $y--): ?>
                        <option value="<?php echo $y; ?>"><?php echo $y; ?></option>
                    <?php endfor; ?>
                </select>

                <label for="filter-category">Crime Category:</label>
                <select id="filter-category" name="filter-category">
                    <option value="all">All</option>
                    <option value="theft">Theft</option>
                    <option value="vandalism">Vandalism</option>
                    <option value="assault">Assault</option>
                    <option value="other">Other</option>
                </select>

                <button type="button" id="filter-apply">Apply Filters</button>
            </form>

            <div id="crime-stats-visualizations">
                <canvas id="crime-stats-graph" width="400" height="200"></canvas>
                <canvas id="crime-stats-pie" width="400" height="200"></canvas>
            </div>

            <div id="crime-stats-list">
                <h2>Crime Reports</h2>
                <table class="widefat">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Date</th>
                            <th>Location</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Data will be populated via JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>

        <style>
            /* Ensure all styles override Astra theme */
            .sandbaai-crime-statistics #crime-stats-filters label {
                font-weight: bold !important;
                margin-right: 10px !important;
            }

            .sandbaai-crime-statistics #crime-stats-filters select,
            .sandbaai-crime-statistics #crime-stats-filters button {
                margin-right: 20px !important;
            }

            .sandbaai-crime-statistics #crime-stats-visualizations {
                margin-top: 20px !important;
            }

            .sandbaai-crime-statistics table.widefat {
                margin-top: 20px !important;
            }
        </style>

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const ctxGraph = document.getElementById('crime-stats-graph').getContext('2d');
                const ctxPie = document.getElementById('crime-stats-pie').getContext('2d');

                // Example data for testing
                const exampleData = {
                    graph: {
                        labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                        data: [5, 10, 3, 8, 6, 2, 4]
                    },
                    pie: {
                        labels: ['Theft', 'Vandalism', 'Assault', 'Other'],
                        data: [20, 15, 10, 5]
                    }
                };

                // Bar graph
                new Chart(ctxGraph, {
                    type: 'bar',
                    data: {
                        labels: exampleData.graph.labels,
                        datasets: [{
                            label: 'Crimes by Day',
                            data: exampleData.graph.data,
                            backgroundColor: 'rgba(75, 192, 192, 0.2)',
                            borderColor: 'rgba(75, 192, 192, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true
                    }
                });

                // Pie chart
                new Chart(ctxPie, {
                    type: 'pie',
                    data: {
                        labels: exampleData.pie.labels,
                        datasets: [{
                            data: exampleData.pie.data,
                            backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0']
                        }]
                    },
                    options: {
                        responsive: true
                    }
                });

                // Example: Populate list
                const tableBody = document.querySelector('#crime-stats-list table tbody');
                tableBody.innerHTML = `
                    <tr>
                        <td>Burglary in Zone A</td>
                        <td>Theft</td>
                        <td>2025-05-07</td>
                        <td>Zone A</td>
                    </tr>
                    <tr>
                        <td>Vandalism at Park</td>
                        <td>Vandalism</td>
                        <td>2025-05-06</td>
                        <td>Sandbaai Park</td>
                    </tr>
                `;
            });
        </script>
        <?php
    }
}