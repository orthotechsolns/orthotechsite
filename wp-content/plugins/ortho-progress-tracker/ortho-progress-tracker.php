<?php
/**
 * Plugin Name: Ortho Progress Tracker
 * Plugin URI: https://yourwebsite.com/ortho-progress-tracker
 * Description: An interactive progress tracker for orthopedic recovery and rehabilitation programs.
 * Version: 1.0.0
 * Author: Gennesis Bethelmy
 * Author URI: https://yourwebsite.com
 * Text Domain: ortho-progress-tracker
 * Domain Path: /languages
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('OPT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('OPT_PLUGIN_URL', plugin_dir_url(__FILE__));
define('OPT_PLUGIN_VERSION', '1.0.0');

// Include required files
require_once OPT_PLUGIN_DIR . 'inc/post-types.php';
require_once OPT_PLUGIN_DIR . 'inc/shortcodes.php';
require_once OPT_PLUGIN_DIR . 'inc/user-progress.php';
require_once OPT_PLUGIN_DIR . 'inc/settings.php';

// Register activation and deactivation hooks
register_activation_hook(__FILE__, 'opt_activate');
register_deactivation_hook(__FILE__, 'opt_deactivate');

/**
 * Plugin activation function
 */
function opt_activate() {
    // Create database tables
    require_once OPT_PLUGIN_DIR . 'inc/user-progress.php';
    opt_create_tables();
    
    // Register post types
    require_once OPT_PLUGIN_DIR . 'inc/post-types.php';
    opt_register_post_types();
    
    // Flush rewrite rules
    flush_rewrite_rules();
}

/**
 * Plugin deactivation function
 */
function opt_deactivate() {
    // Flush rewrite rules
    flush_rewrite_rules();
}

/**
 * Load plugin text domain for translation
 */
function opt_load_textdomain() {
    load_plugin_textdomain('ortho-progress-tracker', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('plugins_loaded', 'opt_load_textdomain');

/**
 * Enqueue scripts and styles
 */
function opt_enqueue_scripts() {
    // Only load on relevant pages
    if (!is_singular('recovery_program') && !is_post_type_archive('recovery_program') && !has_shortcode(get_the_content(), 'ortho_progress')) {
        return;
    }
    
    // CSS
    wp_enqueue_style('opt-styles', OPT_PLUGIN_URL . 'assets/css/opt-styles.css', array(), OPT_PLUGIN_VERSION);
    
    // JS
    wp_enqueue_script('chart-js', 'https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js', array(), '3.7.1', true);
    wp_enqueue_script('opt-scripts', OPT_PLUGIN_URL . 'assets/js/opt-scripts.js', array('jquery', 'chart-js'), OPT_PLUGIN_VERSION, true);
    
    // Localize script with AJAX URL and nonce
    wp_localize_script('opt-scripts', 'opt_data', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('opt-ajax-nonce'),
    ));
}
add_action('wp_enqueue_scripts', 'opt_enqueue_scripts');

/**
 * AJAX handler for updating progress
 */
function opt_update_progress() {
    // Check nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'opt-ajax-nonce')) {
        wp_send_json_error('Invalid nonce');
    }
    
    // Check if user is logged in
    if (!is_user_logged_in()) {
        wp_send_json_error('User not logged in');
    }
    
    // Get data from request
    $program_id = isset($_POST['program_id']) ? intval($_POST['program_id']) : 0;
    $step_id = isset($_POST['step_id']) ? intval($_POST['step_id']) : 0;
    $completed = isset($_POST['completed']) ? (bool) $_POST['completed'] : false;
    $pain_level = isset($_POST['pain_level']) ? intval($_POST['pain_level']) : 0;
    $notes = isset($_POST['notes']) ? sanitize_textarea_field($_POST['notes']) : '';
    
    // Validate data
    if (!$program_id || !$step_id) {
        wp_send_json_error('Invalid program or step ID');
    }
    
    // Get user ID
    $user_id = get_current_user_id();
    
    // Update progress
    $result = opt_save_user_progress($user_id, $program_id, $step_id, $completed, $pain_level, $notes);
    
    if ($result) {
        // Get updated overall progress
        $progress = opt_get_user_program_progress($user_id, $program_id);
        
        wp_send_json_success(array(
            'message' => 'Progress updated successfully',
            'progress' => $progress,
        ));
    } else {
        wp_send_json_error('Failed to update progress');
    }
}
add_action('wp_ajax_opt_update_progress', 'opt_update_progress');

/**
 * Add a progress link/button to My Account menu in WooCommerce
 */
function opt_add_woocommerce_account_menu_item($menu_items) {
    // Add the new menu item after the Dashboard
    $new_menu_items = array();
    
    foreach ($menu_items as $key => $label) {
        $new_menu_items[$key] = $label;
        
        if ($key === 'dashboard') {
            $new_menu_items['recovery-progress'] = __('Recovery Progress', 'ortho-progress-tracker');
        }
    }
    
    return $new_menu_items;
}
add_filter('woocommerce_account_menu_items', 'opt_add_woocommerce_account_menu_item');

/**
 * Add endpoint for the Recovery Progress page
 */
function opt_add_woocommerce_endpoint() {
    add_rewrite_endpoint('recovery-progress', EP_ROOT | EP_PAGES);
}
add_action('init', 'opt_add_woocommerce_endpoint');

/**
 * Recovery Progress page content
 */
function opt_woocommerce_account_recovery_progress_content() {
    // Include the template
    include OPT_PLUGIN_DIR . 'templates/account-progress.php';
}
add_action('woocommerce_account_recovery-progress_endpoint', 'opt_woocommerce_account_recovery_progress_content');

/**
 * Add settings link to plugin page
 */
function opt_add_settings_link($links) {
    $settings_link = '<a href="' . admin_url('admin.php?page=ortho-progress-tracker') . '">' . __('Settings', 'ortho-progress-tracker') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
}
$plugin = plugin_basename(__FILE__);
add_filter("plugin_action_links_$plugin", 'opt_add_settings_link');