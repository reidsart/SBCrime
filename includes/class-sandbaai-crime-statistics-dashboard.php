<?php
/**
 * Sandbaai Crime Statistics Dashboard
 */

class Sandbaai_Crime_Statistics_Dashboard {

    public function __construct() {
        // Add dashboard page to admin menu
        add_action('admin_menu', [$this, 'add_statistics_page']);
    }

    /**
     * Add the statistics page to the admin menu.
     */
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

    /**
     * Render the statistics page.
     */
    public function render_statistics_page() {
        global $wpdb;

        // Fetch crime reports data
        $crime_reports_table = $wpdb->prefix . 'posts';
        $meta_table = $wpdb->prefix . 'postmeta';

        // Query for crime reports with necessary meta data
        $crime_reports_query = "
            SELECT p.ID, p.post_title, p.post_date, 
                   meta_location.meta_value AS location, 
                   meta_category.meta_value AS category, 
                   meta_date_time.meta_value AS date_time
            FROM $crime_reports_table p
            LEFT JOIN $meta_table meta_location ON (p.ID = meta_location.post_id AND meta_location.meta_key = 'location')
            LEFT JOIN $meta_table meta_category ON (p.ID = meta_category.post_id AND meta_category.meta_key = 'category')
            LEFT JOIN $meta_table meta_date_time ON (p.ID = meta_date_time.post_id AND meta_date_time.meta_key = 'date_time')
            WHERE p.post_type = 'crime_report'
            AND p.post_status IN ('pending', 'publish')
            ORDER BY p.post_date DESC
            LIMIT 10
        ";

        $crime_reports = $wpdb->get_results($crime_reports_query);

        // Group crime data by category for charting
        $categories_data = [];
        foreach ($crime_reports as $report) {
            $category = $report->category;
            if (!isset($categories_data[$category])) {
                $categories_data[$category] = 0;
            }
            $categories_data[$category]++;
        }

        ?>

        <div class="wrap sandbaai-crime-statistics">
            <h1>Crime Statistics Dashboard</h1>

            <!-- Visualization Section -->
            <div id="crime-stats-visualizations">
                <canvas id="crime-stats-graph" width="400" height="200"></canvas>
                <canvas id="crime-stats-pie" width="400" height="200"></canvas>
            </div>

            <!-- Recent Reports Section -->
            <div id="crime-stats-list">
                <h2>Recent Crime Reports</h2>
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
                        <?php if (!empty($crime_reports)): ?>
                            <?php foreach ($crime_reports as $report): ?>
                                <tr>
                                    <td><?php echo esc_html($report->post_title); ?></td>
                                    <td><?php echo esc_html($report->category); ?></td>
                                    <td><?php echo esc_html($report->date_time); ?></td>
                                    <td><?php echo esc_html($report->location); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="4">No crime reports available.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Chart.js Integration -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const ctxGraph = document.getElementById('crime-stats-graph').getContext('2d');
                const ctxPie = document.getElementById('crime-stats-pie').getContext('2d');

                // Chart Data
                const crimeData = <?php echo json_encode($categories_data); ?>;
                const labels = Object.keys(crimeData);
                const dataCounts = Object.values(crimeData);

                // Bar Chart
                new Chart(ctxGraph, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Number of Crimes',
                            data: dataCounts,
                            backgroundColor: 'rgba(75, 192, 192, 0.2)',
                            borderColor: 'rgba(75, 192, 192, 1)',
                            borderWidth: 1,
                        }],
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                display: true,
                            },
                        },
                    },
                });

                // Pie Chart
                new Chart(ctxPie, {
                    type: 'pie',
                    data: {
                        labels: labels,
                        datasets: [{
                            data: dataCounts,
                            backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40'],
                        }],
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                display: true,
                            },
                        },
                    },
                });
            });
        </script>
        <?php
    }
}

new Sandbaai_Crime_Statistics_Dashboard();