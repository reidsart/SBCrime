<?php
/**
 * Sandbaai Crime Reporting Form
 */

class Sandbaai_Crime_Reporting_Form {

    public function __construct() {
        // Register shortcode
        add_shortcode('crime_reporting_form', [$this, 'render_crime_reporting_form']);

        // Handle form submission
        add_action('admin_post_nopriv_submit_crime_report', [$this, 'handle_form_submission']);
        add_action('admin_post_submit_crime_report', [$this, 'handle_form_submission']);
    }

    /**
     * Render the crime reporting form.
     */
    public function render_crime_reporting_form() {
        ob_start();
        ?>
        <form id="crime-reporting-form" method="post" enctype="multipart/form-data" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <input type="hidden" name="action" value="submit_crime_report">
            <?php wp_nonce_field('crime_reporting_form', 'crime_reporting_nonce'); ?>

            <!-- Step 1: Location -->
            <div class="form-step" id="step-1">
                <h3>Step 1: Location</h3>
                <label for="location">Address or Zone:</label>
                <input type="text" id="location" name="location" required>
                <button type="button" class="next-step" data-next="step-2">Next</button>
            </div>

            <!-- Step 2: Title and Crime Category -->
            <div class="form-step" id="step-2" style="display:none;">
                <h3>Step 2: Crime Details</h3>
                <label for="title">Title:</label>
                <input type="text" id="title" name="title" required>
                
                <label for="category">Crime Category:</label>
                <select id="category" name="category" required>
                    <option value="theft">Theft</option>
                    <option value="vandalism">Vandalism</option>
                    <option value="assault">Assault</option>
                    <option value="other">Other</option>
                </select>
                <button type="button" class="prev-step" data-prev="step-1">Back</button>
                <button type="button" class="next-step" data-next="step-3">Next</button>
            </div>

            <!-- Step 3: Date, Time, and Security Groups -->
            <div class="form-step" id="step-3" style="display:none;">
                <h3>Step 3: Date, Time, and Groups</h3>
                <label for="date_time">Date and Time:</label>
                <input type="datetime-local" id="date_time" name="date_time" value="<?php echo date('Y-m-d\TH:i'); ?>" required>
                
                <label for="security_groups">Security Groups Involved:</label>
                <input type="text" id="security_groups" name="security_groups">
                
                <button type="button" class="prev-step" data-prev="step-2">Back</button>
                <button type="button" class="next-step" data-next="step-4">Next</button>
            </div>

            <!-- Step 4: Description and Photo -->
            <div class="form-step" id="step-4" style="display:none;">
                <h3>Step 4: Description and Photo</h3>
                <label for="description">Description:</label>
                <textarea id="description" name="description" required></textarea>
                
                <label for="photo">Photo (optional):</label>
                <input type="file" id="photo" name="photo" accept="image/*">
                
                <button type="button" class="prev-step" data-prev="step-3">Back</button>
                <button type="submit">Submit</button>
            </div>
        </form>

        <script>
            (function() {
                document.querySelectorAll('.next-step').forEach(button => {
                    button.addEventListener('click', function() {
                        const currentStep = document.getElementById(this.dataset.next.replace('step-', 'step-'));
                        document.querySelectorAll('.form-step').forEach(step => step.style.display = 'none');
                        currentStep.style.display = 'block';
                    });
                });

                document.querySelectorAll('.prev-step').forEach(button => {
                    button.addEventListener('click', function() {
                        const previousStep = document.getElementById(this.dataset.prev);
                        document.querySelectorAll('.form-step').forEach(step => step.style.display = 'none');
                        previousStep.style.display = 'block';
                    });
                });
            })();
        </script>
        <?php
        return ob_get_clean();
    }

    /**
     * Handle form submission.
     */
    public function handle_form_submission() {
        if (!isset($_POST['crime_reporting_nonce']) || !wp_verify_nonce($_POST['crime_reporting_nonce'], 'crime_reporting_form')) {
            wp_die('Nonce verification failed.');
        }

        $post_data = [
            'post_title'   => sanitize_text_field($_POST['title']),
            'post_content' => sanitize_textarea_field($_POST['description']),
            'post_type'    => 'crime_report',
            'post_status'  => 'pending',
        ];

        $post_id = wp_insert_post($post_data);

        if ($post_id) {
            update_post_meta($post_id, 'location', sanitize_text_field($_POST['location']));
            update_post_meta($post_id, 'category', sanitize_text_field($_POST['category']));
            update_post_meta($post_id, 'date_time', sanitize_text_field($_POST['date_time']));
            update_post_meta($post_id, 'security_groups', sanitize_text_field($_POST['security_groups']));

            if (!empty($_FILES['photo']['name'])) {
                $attachment_id = media_handle_upload('photo', $post_id);
                if (!is_wp_error($attachment_id)) {
                    update_post_meta($post_id, 'photo', $attachment_id);
                }
            }
        }

        wp_redirect(home_url('/thank-you'));
        exit;
    }
}

new Sandbaai_Crime_Reporting_Form();