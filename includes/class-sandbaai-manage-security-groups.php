<?php
/**
 * Sandbaai Manage Security Groups
 */

class Sandbaai_Manage_Security_Groups {
    public function __construct() {
        add_action('admin_menu', [$this, 'add_security_groups_page']);
        add_action('admin_post_update_security_group', [$this, 'update_security_group']);
    }

    /** Add the Manage Security Groups page */
    public function add_security_groups_page() {
        add_submenu_page(
            'sandbaai-crime-tracker',
            'Manage Security Groups',
            'Manage Security Groups',
            'manage_options',
            'sandbaai-security-groups',
            [$this, 'render_security_groups_page']
        );
    }

    /** Render the Manage Security Groups page */
    public function render_security_groups_page() {
        global $wpdb;
        $security_groups_table = $wpdb->prefix . 'security_groups';
        $groups = $wpdb->get_results("SELECT * FROM $security_groups_table");
        ?>

        <div class="wrap">
            <h1>Manage Security Groups</h1>
            <table class="widefat">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Contact Numbers</th>
                        <th>Email</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($groups as $group): ?>
                        <tr>
                            <td><?php echo esc_html($group->title); ?></td>
                            <td><?php echo esc_html($group->contact_numbers); ?></td>
                            <td><?php echo esc_html($group->email); ?></td>
                            <td>
                                <a href="<?php echo esc_url(admin_url('admin.php?page=sandbaai-security-groups&edit=' . $group->id)); ?>">Edit</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php if (isset($_GET['edit'])): ?>
                <?php
                $group_id = intval($_GET['edit']);
                $group = $wpdb->get_row($wpdb->prepare("SELECT * FROM $security_groups_table WHERE id = %d", $group_id));
                ?>
                <h2>Edit Security Group</h2>
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                    <input type="hidden" name="action" value="update_security_group">
                    <input type="hidden" name="group_id" value="<?php echo esc_attr($group->id); ?>">
                    <table class="form-table">
                        <tr>
                            <th><label for="title">Title</label></th>
                            <td><input type="text" name="title" id="title" value="<?php echo esc_attr($group->title); ?>" required></td>
                        </tr>
                        <tr>
                            <th><label for="contact_numbers">Contact Numbers</label></th>
                            <td><input type="text" name="contact_numbers" id="contact_numbers" value="<?php echo esc_attr($group->contact_numbers); ?>"></td>
                        </tr>
                        <tr>
                            <th><label for="email">Email</label></th>
                            <td><input type="email" name="email" id="email" value="<?php echo esc_attr($group->email); ?>"></td>
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

    /** Handle the update security group action */
    public function update_security_group() {
        global $wpdb;
        $security_groups_table = $wpdb->prefix . 'security_groups';

        $group_id = intval($_POST['group_id']);
        $title = sanitize_text_field($_POST['title']);
        $contact_numbers = sanitize_text_field($_POST['contact_numbers']);
        $email = sanitize_email($_POST['email']);

        $wpdb->update(
            $security_groups_table,
            [
                'title' => $title,
                'contact_numbers' => $contact_numbers,
                'email' => $email
            ],
            ['id' => $group_id]
        );

        wp_redirect(admin_url('admin.php?page=sandbaai-security-groups'));
        exit;
    }
}

new Sandbaai_Manage_Security_Groups();