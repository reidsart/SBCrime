<?php
/**
 * Sandbaai Manage Crime Reports
 */

class Sandbaai_Manage_Crime_Reports {
    public function render_manage_crime_reports_page() {
        global $wpdb;
        $crime_reports_table = $wpdb->prefix . 'crime_reports';

        // Debugging: Check if the table exists
        error_log("Checking if table $crime_reports_table exists...");
        if ($wpdb->get_var("SHOW TABLES LIKE '$crime_reports_table'") != $crime_reports_table) {
            error_log("Error: Table $crime_reports_table does not exist.");
            echo '<div class="wrap"><h1>Manage Crime Reports</h1><p>Error: The crime reports table does not exist.</p></div>';
            return;
        }

        // Fetch crime reports
        error_log("Fetching data from $crime_reports_table...");
        $reports = $wpdb->get_results("SELECT * FROM $crime_reports_table");

        // Debugging: Check if the query returned any results
        if ($wpdb->last_error) {
            error_log("Database Error: " . $wpdb->last_error);
            echo '<div class="wrap"><h1>Manage Crime Reports</h1><p>Error: ' . esc_html($wpdb->last_error) . '</p></div>';
            return;
        }

        error_log("Fetched " . count($reports) . " records from $crime_reports_table.");
        echo '<div class="wrap"><h1>Manage Crime Reports</h1>';
        if (empty($reports)) {
            echo '<p>No crime reports found.</p></div>';
            return;
        }

        echo '<table class="widefat"><thead><tr><th>Title</th><th>Category</th><th>Location</th><th>Reporter</th><th>Actions</th></tr></thead><tbody>';
        foreach ($reports as $report) {
            echo '<tr><td>' . esc_html($report->title) . '</td><td>' . esc_html($report->category) . '</td><td>' . esc_html($report->location) . '</td><td>' . esc_html($report->reporter) . '</td><td><a href="' . esc_url(admin_url('admin.php?page=sandbaai-crime-reports&edit=' . $report->id)) . '">Edit</a></td></tr>';
        }
        echo '</tbody></table></div>';
    }
}