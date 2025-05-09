<?php
/**
 * Sandbaai Crime Statistics Dashboard
 */

class Sandbaai_Crime_Statistics_Dashboard {
    public function render_statistics_page() {
        global $wpdb;
        $crime_reports_table = $wpdb->prefix . 'crime_reports';

        // Debugging: Check if the table exists
        error_log("Checking if table $crime_reports_table exists...");
        if ($wpdb->get_var("SHOW TABLES LIKE '$crime_reports_table'") != $crime_reports_table) {
            error_log("Error: Table $crime_reports_table does not exist.");
            echo '<div class="wrap"><h1>Crime Statistics Dashboard</h1><p>Error: The crime reports table does not exist.</p></div>';
            return;
        }

        // Fetch crime statistics
        error_log("Fetching crime statistics from $crime_reports_table...");
        $reports = $wpdb->get_results("SELECT category, COUNT(*) as count FROM $crime_reports_table GROUP BY category");

        // Debugging: Check if the query returned any results
        if ($wpdb->last_error) {
            error_log("Database Error: " . $wpdb->last_error);
            echo '<div class="wrap"><h1>Crime Statistics Dashboard</h1><p>Error: ' . esc_html($wpdb->last_error) . '</p></div>';
            return;
        }

        error_log("Fetched " . count($reports) . " category statistics from $crime_reports_table.");
        echo '<div class="wrap"><h1>Crime Statistics Dashboard</h1>';
        if (empty($reports)) {
            echo '<p>No crime statistics available.</p></div>';
            return;
        }

        echo '<table class="widefat"><thead><tr><th>Category</th><th>Count</th></tr></thead><tbody>';
        foreach ($reports as $report) {
            echo '<tr><td>' . esc_html($report->category) . '</td><td>' . esc_html($report->count) . '</td></tr>';
        }
        echo '</tbody></table></div>';
    }
}