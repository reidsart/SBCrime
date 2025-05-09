<?php
/**
 * Sandbaai Crime Statistics Dashboard
 */

class Sandbaai_Crime_Statistics_Dashboard {

    public function __construct() {
        add_action('admin_menu', [$this, 'add_statistics_page']);
        add_action('wp_ajax_get_filtered_crime_data', [$this, 'get_filtered_crime_data']);
    }

    /** Add the statistics page to the admin menu. */
    public function add_statistics_page() {
        add_submenu_page(
            'sandbaai-crime-tracker',
            'Crime Statistics',
            'Crime Statistics',
            'manage_options',
            'sandbaai-crime-statistics',
            [$this, 'render_statistics_page']
        );
    }

    /** Render the statistics page. */
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
                const filters = document.getElementById('crime-stats-filters');
                const graphCanvas = document.getElementById('crime-stats-graph').getContext('2d');
                const pieCanvas = document.getElementById('crime-stats-pie').getContext('2d');
                const tableBody = document.querySelector('#crime-stats-list table tbody');

                const fetchCrimeData = () => {
                    const formData = new FormData(filters);
                    fetch(ajaxurl + '?action=get_filtered_crime_data', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        // Update charts
                        new Chart(graphCanvas, {
                            type: 'bar',
                            data: {
                                labels: data.graph.labels,
                                datasets: [{
                                    label: 'Crimes by Day',
                                    data: data.graph.values,
                                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                                    borderColor: 'rgba(75, 192, 192, 1)',
                                    borderWidth: 1
                                }]
                            },
                            options: { responsive: true }
                        });

                        new Chart(pieCanvas, {
                            type: 'pie',
                            data: {
                                labels: data.pie.labels,
                                datasets: [{
                                    data: data.pie.values,
                                    backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0']
                                }]
                            },
                            options: { responsive: true }
                        });

                        // Update table
                        tableBody.innerHTML = data.reports.map(report => `
                            <tr>
                                <td>${report.title}</td>
                                <td>${report.category}</td>
                                <td>${report.date}</td>
                                <td>${report.location}</td>
                            </tr>
                        `).join('');
                    });
                };

                document.getElementById('filter-apply').addEventListener('click', fetchCrimeData);
                fetchCrimeData(); // Initial load
            });
        </script>
        <?php
    }

    /** Fetch filtered crime data */
    public function get_filtered_crime_data() {
        global $wpdb;

        $month = isset($_POST['filter-month']) ? sanitize_text_field($_POST['filter-month']) : 'all';
        $year = isset($_POST['filter-year']) ? sanitize_text_field($_POST['filter-year']) : 'all';
        $category = isset($_POST['filter-category']) ? sanitize_text_field($_POST['filter-category']) : 'all';

        $query = "SELECT * FROM {$wpdb->prefix}crime_reports WHERE 1=1";
        $params = [];

        if ($month !== 'all') {
            $query .= " AND MONTH(date_time) = %d";
            $params[] = $month;
        }

        if ($year !== 'all') {
            $query .= " AND YEAR(date_time) = %d";
            $params[] = $year;
        }

        if ($category !== 'all') {
            $query .= " AND category = %s";
            $params[] = $category;
        }

        $results = $wpdb->get_results($wpdb->prepare($query, $params));

        wp_send_json([
            'graph' => [
                'labels' => array_column($results, 'date_time'),
                'values' => array_column($results, 'id')
            ],
            'pie' => [
                'labels' => array_unique(array_column($results, 'category')),
                'values' => array_count_values(array_column($results, 'category'))
            ],
            'reports' => array_map(function ($row) {
                return [
                    'title' => $row->title,
                    'category' => $row->category,
                    'date' => $row->date_time,
                    'location' => $row->location
                ];
            }, $results)
        ]);
    }
}

new Sandbaai_Crime_Statistics_Dashboard();