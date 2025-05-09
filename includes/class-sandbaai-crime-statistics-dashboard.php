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
                <h1>Crime Statistics</h1>
                
                <!-- Filter Section -->
                <div id="crime-stats-filters" style="margin-bottom: 20px;">
                    <h2>Filters</h2>
                    <form id="crime-stats-filter-form">
                        <label for="month">Month:</label>
                        <select id="month" name="month">
                            <option value="">All</option>
                            <option value="1">January</option>
                            <option value="2">February</option>
                            <!-- Add other months -->
                        </select>
                        
                        <label for="year">Year:</label>
                        <select id="year" name="year">
                            <option value="">All</option>
                            <?php for ($i = 2020; $i <= date('Y'); $i++): ?>
                                <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                            <?php endfor; ?>
                        </select>
                        
                        <label for="crime-type">Crime Type:</label>
                        <select id="crime-type" name="crime_type">
                            <option value="">All</option>
                            <option value="theft">Theft</option>
                            <option value="assault">Assault</option>
                            <!-- Add other crime types -->
                        </select>
                        
                        <label for="response-result">Response Result:</label>
                        <select id="response-result" name="response_result">
                            <option value="">All</option>
                            <option value="resolved">Resolved</option>
                            <option value="unresolved">Unresolved</option>
                            <!-- Add other response results -->
                        </select>
                        
                        <label for="time-range">Time Range:</label>
                        <input type="time" id="time-range-start" name="time_range_start">
                        to
                        <input type="time" id="time-range-end" name="time_range_end">
                        
                        <button type="button" id="apply-filters">Apply Filters</button>
                    </form>
                </div>
                
                <!-- Data Visualization Section -->
                <div id="crime-stats-visualizations">
                    <h2>Data Visualizations</h2>
                    <div id="crimes-by-day-graph" style="margin-bottom: 20px;">
                        <h3>Crimes by Day</h3>
                        <canvas id="crimesByDayChart"></canvas>
                    </div>
                    
                    <div id="crime-categories-pie-chart" style="margin-bottom: 20px;">
                        <h3>Crime Categories</h3>
                        <canvas id="crimeCategoriesPieChart"></canvas>
                    </div>
                    
                    <div id="crime-locations-map" style="margin-bottom: 20px;">
                        <h3>Crime Locations</h3>
                        <div id="crimeMap" style="height: 400px;"></div>
                    </div>
                </div>
                
                <!-- List View Section -->
                <div id="crime-stats-list-view">
                    <h2>Crime List View</h2>
                    <ul id="crimeList">
                        <!-- List items will be dynamically populated -->
                    </ul>
                </div>
            </div>
            <?php
        }

        public function fetch_crime_statistics() {
            // Verify AJAX request
            check_ajax_referer('crime_statistics_nonce', 'security');

            // Fetch filter values
            $month = isset($_POST['month']) ? intval($_POST['month']) : '';
            $year = isset($_POST['year']) ? intval($_POST['year']) : '';
            $crime_type = isset($_POST['crime_type']) ? sanitize_text_field($_POST['crime_type']) : '';
            $response_result = isset($_POST['response_result']) ? sanitize_text_field($_POST['response_result']) : '';
            $time_range_start = isset($_POST['time_range_start']) ? sanitize_text_field($_POST['time_range_start']) : '';
            $time_range_end = isset($_POST['time_range_end']) ? sanitize_text_field($_POST['time_range_end']) : '';

            // Fetch filtered data from the database (example logic)
            global $wpdb;
$query = "SELECT DATE(date_time) as crime_date, COUNT(*) as crime_count 
          FROM {$wpdb->prefix}crime_reports 
          WHERE 1=1";

// Add filters only if they are provided
if ($month) $query .= " AND MONTH(date_time) = $month";
if ($year) $query .= " AND YEAR(date_time) = $year";
if ($crime_type) $query .= $wpdb->prepare(" AND category = %s", $crime_type);
if ($response_result) $query .= $wpdb->prepare(" AND result_status = %s", $response_result);
if ($time_range_start && $time_range_end) {
    $query .= $wpdb->prepare(" AND TIME(date_time) BETWEEN %s AND %s", $time_range_start, $time_range_end);
}

$query .= " GROUP BY DATE(date_time) ORDER BY crime_date ASC";

            $results = $wpdb->get_results($query);

            // Format data for Chart.js
            $data = [
                'labels' => [],
                'values' => []
            ];

            foreach ($results as $row) {
                $data['labels'][] = $row->crime_date;
                $data['values'][] = $row->crime_count;
            }

            // Return data as JSON
            wp_send_json_success($data);
        }
    }
}