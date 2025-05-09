<?php
/**
 * Plugin Name: Sandbaai Crime Tracker
 * Plugin URI: https://github.com/reidsart/SBCrime
 * Description: A comprehensive plugin for crime reporting and statistics tracking in Sandbaai.
 * Version: 1.0.0
 * Author: reidsart
 * Author URI: https://github.com/reidsart
 * License: GPL2
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// Activation and deactivation hooks.
register_activation_hook(__FILE__, 'sandbaai_crime_tracker_activate');
register_deactivation_hook(__FILE__, 'sandbaai_crime_tracker_deactivate');

// Include necessary files
require_once plugin_dir_path(__FILE__) . 'includes/class-sandbaai-crime-reporting-form.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-sandbaai-crime-statistics-dashboard.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-sandbaai-manage-crime-reports.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-sandbaai-manage-security-groups.php';

function sandbaai_crime_tracker_activate() {
    sandbaai_crime_tracker_create_tables();
    flush_rewrite_rules();
}

function sandbaai_crime_tracker_deactivate() {
    flush_rewrite_rules();
}

function sandbaai_crime_tracker_create_tables() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    $crime_reports_table = $wpdb->prefix . 'crime_reports';
    $security_groups_table = $wpdb->prefix . 'security_groups';

    $sql = "
        CREATE TABLE $crime_reports_table (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            title VARCHAR(255) NOT NULL,
            description TEXT NOT NULL,
            category VARCHAR(100) NOT NULL,
            date_time DATETIME NOT NULL,
            location VARCHAR(255) NOT NULL,
            reporter VARCHAR(255),
            result_status VARCHAR(50) NOT NULL,
            security_group_id BIGINT(20) UNSIGNED,
            photo_url VARCHAR(2083),
            PRIMARY KEY (id)
        ) $charset_collate;

        CREATE TABLE $security_groups_table (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            title VARCHAR(255) NOT NULL,
            logo_url VARCHAR(2083),
            contact_numbers TEXT,
            email VARCHAR(100),
            address TEXT,
            website_url VARCHAR(2083),
            description TEXT NOT NULL,
            PRIMARY KEY (id)
        ) $charset_collate;
    ";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// Register custom post types.
add_action('init', 'sandbaai_crime_tracker_register_post_types');

function sandbaai_crime_tracker_register_post_types() {
    register_post_type('crime_report', [
        'labels' => [
            'name' => 'Crime Reports',
            'singular_name' => 'Crime Report',
            'add_new' => 'Add New Report',
            'add_new_item' => 'Add New Crime Report',
            'edit_item' => 'Edit Crime Report',
            'new_item' => 'New Crime Report',
            'view_item' => 'View Crime Report',
            'search_items' => 'Search Crime Reports',
            'not_found' => 'No crime reports found',
            'not_found_in_trash' => 'No crime reports found in trash',
            'all_items' => 'All Crime Reports',
        ],
        'public' => true,
        'has_archive' => true,
        'menu_icon' => 'dashicons-warning',
        'supports' => ['title', 'editor', 'custom-fields', 'excerpt', 'thumbnail', 'comments'],
        'capability_type' => 'post',
        'rewrite' => ['slug' => 'crime-reports'],
    ]);

    register_post_type('security_group', [
        'labels' => [
            'name' => 'Security Groups',
            'singular_name' => 'Security Group',
        ],
        'public' => true,
        'has_archive' => true,
        'supports' => ['title', 'editor', 'custom-fields'],
    ]);
}

// Add admin menu
add_action('admin_menu', 'sandbaai_crime_tracker_add_admin_menu');

function sandbaai_crime_tracker_add_admin_menu() {
    // Main menu for "Crime Tracker"
    add_menu_page(
        'Sandbaai Crime Tracker',
        'Crime Tracker',
        'manage_options',
        'sandbaai-crime-tracker',
        'sandbaai_crime_tracker_dashboard_page',
        'dashicons-shield',
        6
    );

    // Submenu for "Manage Crime Reports"
    add_submenu_page(
        'sandbaai-crime-tracker',
        'Manage Crime Reports',
        'Manage Crime Reports',
        'manage_options',
        'sandbaai-crime-reports',
        'sandbaai_manage_crime_reports_page'
    );

    // Submenu for "Manage Security Groups"
    add_submenu_page(
        'sandbaai-crime-tracker',
        'Manage Security Groups',
        'Manage Security Groups',
        'manage_options',
        'sandbaai-security-groups',
        'sandbaai_manage_security_groups_page'
    );

    // Submenu for "Crime Statistics"
    add_submenu_page(
        'sandbaai-crime-tracker',
        'Crime Statistics',
        'Crime Statistics',
        'manage_options',
        'sandbaai-crime-statistics',
        'sandbaai_crime_statistics_page'
    );

    // Submenu for "Settings"
    add_submenu_page(
        'sandbaai-crime-tracker',
        'Settings',
        'Settings',
        'manage_options',
        'sandbaai-settings',
        'sandbaai_crime_tracker_settings_page'
    );
}

function sandbaai_crime_tracker_dashboard_page() {
    // Display a simple dashboard page
    echo '<div class="wrap"><h1>Sandbaai Crime Tracker Dashboard</h1><p>Welcome to the Sandbaai Crime Tracker plugin. Use the submenus to manage reports, security groups, and view statistics.</p></div>';
}

function sandbaai_crime_tracker_settings_page() {
    echo '<div class="wrap"><h1>Settings</h1><p>Configure the plugin settings here.</p></div>';
}

// Callback for "Crime Statistics" submenu
function sandbaai_crime_statistics_page() {
    static $statistics_dashboard = null;
    if ($statistics_dashboard === null) {
        $statistics_dashboard = new Sandbaai_Crime_Statistics_Dashboard();
    }
    $statistics_dashboard->render_statistics_page();
}

// Initialize the Crime Reporting Form
new Sandbaai_Crime_Reporting_Form();