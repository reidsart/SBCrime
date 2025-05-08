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

require_once plugin_dir_path(__FILE__) . 'includes/class-sandbaai-crime-reporting-form.php';

function sandbaai_crime_tracker_activate() {
    // Code to run on plugin activation.
    sandbaai_crime_tracker_create_tables();
    flush_rewrite_rules();
}

function sandbaai_crime_tracker_deactivate() {
    // Code to run on plugin deactivation.
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
    // Register "Crime Reports" post type.
    register_post_type('crime_report', [
        'labels' => [
            'name' => 'Crime Reports',
            'singular_name' => 'Crime Report',
        ],
        'public' => true,
        'has_archive' => true,
        'supports' => ['title', 'editor', 'custom-fields'],
    ]);

    // Register "Security Groups" post type.
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

// Add admin menu.
add_action('admin_menu', 'sandbaai_crime_tracker_add_admin_menu');

function sandbaai_crime_tracker_add_admin_menu() {
    add_menu_page(
        'Sandbaai Crime Tracker',
        'Crime Tracker',
        'manage_options',
        'sandbaai-crime-tracker',
        'sandbaai_crime_tracker_dashboard_page',
        'dashicons-shield',
        6
    );

    add_submenu_page(
        'sandbaai-crime-tracker',
        'Crime Reports',
        'Manage Crime Reports',
        'manage_options',
        'sandbaai-crime-tracker-crime-reports',
        'sandbaai_crime_tracker_crime_reports_page'
    );

    add_submenu_page(
        'sandbaai-crime-tracker',
        'Security Groups',
        'Manage Security Groups',
        'manage_options',
        'sandbaai-crime-tracker-security-groups',
        'sandbaai_crime_tracker_security_groups_page'
    );

    add_submenu_page(
        'sandbaai-crime-tracker',
        'Settings',
        'Settings',
        'manage_options',
        'sandbaai-crime-tracker-settings',
        'sandbaai_crime_tracker_settings_page'
    );
}

function sandbaai_crime_tracker_dashboard_page() {
    echo '<div class="wrap"><h1>Sandbaai Crime Tracker Dashboard</h1><p>Welcome to the Sandbaai Crime Tracker plugin.</p></div>';
}

function sandbaai_crime_tracker_crime_reports_page() {
    echo '<div class="wrap"><h1>Manage Crime Reports</h1><p>Here you can manage all crime reports.</p></div>';
}

function sandbaai_crime_tracker_security_groups_page() {
    echo '<div class="wrap"><h1>Manage Security Groups</h1><p>Here you can manage all security groups.</p></div>';
}

function sandbaai_crime_tracker_settings_page() {
    echo '<div class="wrap"><h1>Settings</h1><p>Configure the plugin settings here.</p></div>';
}
