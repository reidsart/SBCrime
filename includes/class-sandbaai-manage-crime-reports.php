<?php
/**
 * Sandbaai Manage Crime Reports
 */

class Sandbaai_Manage_Crime_Reports {
    public function __construct() {
        add_action('admin_menu', [$this, 'add_manage_crime_reports_page']);
        add_action('admin_post_update_crime_report', [$this, 'update_crime_report']);
    }

    /** Add the Manage Crime Reports page */
    public function add_manage_crime_reports_page() {
        add_submenu_page(
            'sandbaai-crime-tracker',
            'Manage Crime Reports',
            'Manage Crime Reports',
            'manage_options',
            'sandbaai-crime-reports',
            [$this, 'render_manage_crime_reports_page']
        );
    }

    /** Render the Manage Crime Reports page */
    public function render_manage_crime_reports_page() {
        global $wpdb;
        $crime_reports_table = $wpdb->prefix . 'crime_reports';
        $reports = $wpdb->get_results("SELECT * FROM $crime_reports_table");
        ?>

        <div class="wrap">
            <h1>Manage Crime Reports</h1>
            <table class="widefat">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Location</th>
                        <th>Reporter</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reports as $report): ?>
                        <tr>
                            <td><?php echo esc_html($report->title); ?></td>
                            <td><?php echo esc_html($report->category); ?></td>
                            <td><?php echo esc_html($report->location); ?></td>
                            <td><?php echo esc_html($report->reporter); ?></td>
                            <td>
                                <a href="<?php echo esc_url(admin_url('admin.php?page=sandbaai-crime-reports&edit=' . $report->id)); ?>">Edit</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php if (isset($_GET['edit'])): ?>
                <?php
                $report_id = intval($_GET['edit']);
                $report = $wpdb->get_row($wpdb->prepare("SELECT * FROM $crime_reports_table WHERE id = %d", $report_id));
                ?>
                <h2>Edit Crime Report</h2>
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                    <input type="hidden" name="action" value="update_crime_report">
                    <input type="hidden" name="report_id" value="<?php echo esc_attr($report->id); ?>">
                    <table class="form-table">
                        <tr>
                            <th><label for="title">Title</label></th>
                            <td><input type="text" name="title" id="title" value="<?php echo esc_attr($report->title); ?>" required></td>
                        </tr>
                        <tr>
                            <th><label for="category">Category</label></th>
                            <td>
                                <select name="category" id="category" required>
                                    <option value="theft" <?php selected($report->category, 'theft'); ?>>Theft</option>
                                    <option value="vandalism" <?php selected($report->category, 'vandalism'); ?>>Vandalism</option>
                                    <option value="assault" <?php selected($report->category, 'assault'); ?>>Assault</option>
                                    <option value="other" <?php selected($report->category, 'other'); ?>>Other</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="location">Location</label></th>
                            <td><input type="text" name="location" id="location" value="<?php echo esc_attr($report->location); ?>" required></td>
                        </tr>
                    </table>
                    <p class="submit">
                        <input type="submit" class="button-primary" value="Save Changes">
                    </p>
                </form>
            <?php endif; ?>
        </div>
        <?php
    }

    /** Handle the update crime report action */
    public function update_crime_report() {
        global $wpdb;
        $crime_reports_table = $wpdb->prefix . 'crime_reports';

        $report_id = intval($_POST['report_id']);
        $title = sanitize_text_field($_POST['title']);
        $category = sanitize_text_field($_POST['category']);
        $location = sanitize_text_field($_POST['location']);

        $wpdb->update(
            $crime_reports_table,
            [
                'title' => $title,
                'category' => $category,
                'location' => $location
            ],
            ['id' => $report_id]
        );

        wp_redirect(admin_url('admin.php?page=sandbaai-crime-reports'));
        exit;
    }
}

new Sandbaai_Manage_Crime_Reports();