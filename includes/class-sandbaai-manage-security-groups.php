<?php
/**
 * Sandbaai Manage Security Groups
 */

class Sandbaai_Manage_Security_Groups {
    public function render_security_groups_page() {
        global $wpdb;
        $security_groups_table = $wpdb->prefix . 'security_groups';

        // Debugging: Check if the table exists
        error_log("Checking if table $security_groups_table exists...");
        if ($wpdb->get_var("SHOW TABLES LIKE '$security_groups_table'") != $security_groups_table) {
            error_log("Error: Table $security_groups_table does not exist.");
            echo '<div class="wrap"><h1>Manage Security Groups</h1><p>Error: The security groups table does not exist.</p></div>';
            return;
        }

        // Fetch security groups
        error_log("Fetching data from $security_groups_table...");
        $groups = $wpdb->get_results("SELECT * FROM $security_groups_table");

        // Debugging: Check if the query returned any results
        if ($wpdb->last_error) {
            error_log("Database Error: " . $wpdb->last_error);
            echo '<div class="wrap"><h1>Manage Security Groups</h1><p>Error: ' . esc_html($wpdb->last_error) . '</p></div>';
            return;
        }

        error_log("Fetched " . count($groups) . " records from $security_groups_table.");
        echo '<div class="wrap"><h1>Manage Security Groups</h1>';
        if (empty($groups)) {
            echo '<p>No security groups found.</p></div>';
            return;
        }

        echo '<table class="widefat"><thead><tr><th>Title</th><th>Contact Numbers</th><th>Email</th><th>Actions</th></tr></thead><tbody>';
        foreach ($groups as $group) {
            echo '<tr><td>' . esc_html($group->title) . '</td><td>' . esc_html($group->contact_numbers) . '</td><td>' . esc_html($group->email) . '</td><td><a href="' . esc_url(admin_url('admin.php?page=sandbaai-security-groups&edit=' . $group->id)) . '">Edit</a></td></tr>';
        }
        echo '</tbody></table></div>';
    }
}