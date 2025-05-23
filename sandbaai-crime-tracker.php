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
require_once plugin_dir_path(__FILE__) . 'includes/class-sandbaai-crime-statistics-dashboard.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-sandbaai-crime-reporting-form.php';


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
        CREATE TABLE IF NOT EXISTS $crime_reports_table (
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

        CREATE TABLE IF NOT EXISTS $security_groups_table (
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

// Register custom post types and taxonomies
add_action('init', 'sandbaai_crime_tracker_register_custom_types');

function sandbaai_crime_tracker_register_custom_types() {
    // Register "Crime Reports" post type
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
        'show_ui' => true,
        'show_in_menu' => false, // Hide from top-level menu
        'supports' => ['title', 'editor', 'custom-fields', 'excerpt', 'thumbnail'],
        'capability_type' => 'post',
        'taxonomies' => ['crime_category', 'post_tag'] // Link taxonomy
    ]);

    // Register "Security Groups" post type
    register_post_type('security_group', [
        'labels' => [
            'name' => 'Security Groups',
            'singular_name' => 'Security Group',
            'add_new' => 'Add New Group',
            'add_new_item' => 'Add New Security Group',
            'edit_item' => 'Edit Security Group',
            'new_item' => 'New Security Group',
            'view_item' => 'View Security Group',
            'search_items' => 'Search Security Groups',
            'not_found' => 'No security groups found',
            'not_found_in_trash' => 'No security groups found in trash',
            'all_items' => 'All Security Groups',
        ],
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => false, // Hide from top-level menu
        'supports' => ['title', 'editor', 'custom-fields'],
    ]);

    // Register "Crime Categories" taxonomy
    register_taxonomy('crime_category', ['crime_report'], [
        'labels' => [
            'name' => 'Crime Categories',
            'singular_name' => 'Crime Category',
            'search_items' => 'Search Crime Categories',
            'all_items' => 'All Crime Categories',
            'edit_item' => 'Edit Crime Category',
            'update_item' => 'Update Crime Category',
            'add_new_item' => 'Add New Crime Category',
            'new_item_name' => 'New Crime Category Name',
            'menu_name' => 'Crime Categories',
        ],
        'hierarchical' => true,
        'show_ui' => true,
        'show_admin_column' => true,
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

    // Submenu for "Crime Reports"
    add_submenu_page(
        'sandbaai-crime-tracker',
        'Crime Reports',
        'Crime Reports',
        'manage_options',
        'edit.php?post_type=crime_report'
    );

    // Submenu for "Security Groups"
    add_submenu_page(
        'sandbaai-crime-tracker',
        'Security Groups',
        'Security Groups',
        'manage_options',
        'edit.php?post_type=security_group'
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

    // Submenu for "Crime Categories"
    add_submenu_page(
        'sandbaai-crime-tracker',
        'Crime Categories',
        'Crime Categories',
        'manage_options',
        'edit-tags.php?taxonomy=crime_category&post_type=crime_report'
    );
}

function sandbaai_crime_tracker_dashboard_page() {
    echo '<div class="wrap"><h1>Sandbaai Crime Tracker Dashboard</h1><p>Welcome to the Sandbaai Crime Tracker plugin. Use the submenus to manage reports, categories, tags, security groups, and view statistics.</p></div>';
}

function sandbaai_crime_statistics_page() {
    $statistics_dashboard = new Sandbaai_Crime_Statistics_Dashboard();
    $statistics_dashboard->render_statistics_page();
}

// Modify admin columns for Crime Reports
add_filter('manage_crime_report_posts_columns', 'add_crime_report_columns');
function add_crime_report_columns($columns) {
    $columns['submitted_by'] = 'Submitted By';
    $columns['date_time'] = 'Reported Date';
    return $columns;
}

add_action('manage_crime_report_posts_custom_column', 'populate_crime_report_columns', 10, 2);
function populate_crime_report_columns($column, $post_id) {
    if ($column == 'submitted_by') {
        $user = get_post_meta($post_id, '_submitted_by', true);
        echo $user ? esc_html($user) : '—';
    }
    if ($column == 'date_time') {
        $date = get_post_meta($post_id, 'date_time', true);
        echo $date ? esc_html($date) : '—';
    }
}

// Enqueue scripts and styles
add_action('admin_enqueue_scripts', 'sandbaai_crime_tracker_enqueue_scripts');
function sandbaai_crime_tracker_enqueue_scripts($hook) {
    // Log the current admin page hook
    error_log('Current admin page hook: ' . $hook);

    // Only load on the Crime Statistics page
    if ($hook !== 'crime-tracker_page_sandbaai-crime-statistics') {
        return;
    }

    // Enqueue Chart.js library
    wp_enqueue_script(
        'chart-js',
        'https://cdn.jsdelivr.net/npm/chart.js', // Official CDN for Chart.js
        [],
        null,
        true
    );

    // Use the exact URL of the working JavaScript file
    $script_url = 'https://scvid.co.za/wp-content/plugins/SBCrime/assets/js/crime-statistics.js';
    wp_enqueue_script(
        'crime-statistics',
        $script_url,
        ['chart-js'], // Chart.js is a dependency
        null,
        true
    );

    // Log the resolved path for debugging
    error_log('Resolved Crime Statistics JS Path: ' . $script_url);

    // Pass AJAX URL and nonce to JavaScript
    wp_localize_script('crime-statistics', 'crimeStatistics', [
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('crime_statistics_nonce'),
    ]);
}
new Sandbaai_Crime_Reporting_Form();