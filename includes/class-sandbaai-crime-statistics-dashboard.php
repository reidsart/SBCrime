<?php
/**
 * Sandbaai Crime Statistics Dashboard
 */

if (!class_exists('Sandbaai_Crime_Statistics_Dashboard')) {
    class Sandbaai_Crime_Statistics_Dashboard {
        public function __construct() {
            // Register AJAX handlers
            add_action('wp_ajax_fetch_crime_statistics', [$this, 'fetch_crime_statistics']);
            add_action('wp_ajax_nopriv_fetch_crime_statistics', [$this, 'fetch_crime_statistics']); // For non-logged-in users
        }

public function render_statistics_page() {
    ?>
    <div class="wrap">
        <h1>Crime Statistics Dashboard</h1>
        <div id="crime-stats-filters">
            <form id="crime-stats-filter-form">
                <button id="apply-filters">Apply Filters</button>
            </form>
        </div>
        <div id="crime-stats-list-view">
            <h2>Crime Reports</h2>
            <ul id="crimeList"></ul>
        </div>
        <div id="crime-stats-visualizations">
            <h2>Charts</h2>
            <canvas id="crimeCategoriesPieChart" width="400" height="400"></canvas>
            <canvas id="crimesByDayChart" width="400" height="400"></canvas>
        </div>
    </div>
    <?php
}

 public function fetch_crime_statistics() {
    global $wpdb;
    $crime_reports_table = $wpdb->prefix . 'crime_reports';

    // Fetch all crime reports
    $query = "SELECT * FROM $crime_reports_table";
    $reports = $wpdb->get_results($query);

    $categories = [];
    $dates = [];
    foreach ($reports as $report) {
        // Aggregate categories
        $categories[$report->category] = ($categories[$report->category] ?? 0) + 1;

        // Aggregate dates
        $date = date('Y-m-d', strtotime($report->date_time));
        $dates[$date] = ($dates[$date] ?? 0) + 1;
    }

    // Prepare data for charts
    $chart_data = [
        'categories' => $categories,
        'dates' => $dates,
        'reports' => $reports,
    ];

    // Debugging: Log the output
    error_log('Crime Statistics Data: ' . print_r($chart_data, true));

    wp_send_json_success($chart_data);
}
    }
}