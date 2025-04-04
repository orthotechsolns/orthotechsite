<?php
/**
 * Plugin Name: Ortho Photo Gallery
 * Plugin URI: https://yourwebsite.com/ortho-photo-gallery
 * Description: A simple photo gallery plugin for displaying product images with filtering options.
 * Version: 1.1.0
 * Author: Isabell Munroe
 * Author URI: https://yourwebsite.com
 * Text Domain: ortho-photo-gallery
 * Domain Path: /languages
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('OPG_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('OPG_PLUGIN_URL', plugin_dir_url(__FILE__));
define('OPG_PLUGIN_VERSION', '1.0.0');

// Include required files
require_once OPG_PLUGIN_DIR . 'inc/post-types.php';
require_once OPG_PLUGIN_DIR . 'inc/shortcodes.php';
require_once OPG_PLUGIN_DIR . 'inc/admin-functions.php';

// Register activation and deactivation hooks
register_activation_hook(__FILE__, 'opg_activate');
register_deactivation_hook(__FILE__, 'opg_deactivate');

// Activation function
function opg_activate() {
    // Flush rewrite rules
    flush_rewrite_rules();
}

// Deactivation function
function opg_deactivate() {
    // Flush rewrite rules
    flush_rewrite_rules();
}

// Enqueue scripts and styles
function opg_enqueue_scripts() {
    // Enqueue main CSS
    wp_enqueue_style('opg-styles', OPG_PLUGIN_URL . 'assets/css/opg-styles.css', array(), OPG_PLUGIN_VERSION);
    
    // Enqueue lightbox CSS
    wp_enqueue_style('opg-lightbox', OPG_PLUGIN_URL . 'assets/css/lightbox.min.css', array(), OPG_PLUGIN_VERSION);
    
    // Enqueue main JS
    wp_enqueue_script('opg-scripts', OPG_PLUGIN_URL . 'assets/js/opg-scripts.js', array('jquery'), OPG_PLUGIN_VERSION, true);
    
    // Enqueue lightbox JS
    wp_enqueue_script('opg-lightbox', OPG_PLUGIN_URL . 'assets/js/lightbox.min.js', array('jquery'), OPG_PLUGIN_VERSION, true);
    
    // Localize script with AJAX URL
    wp_localize_script('opg-scripts', 'opg_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('opg-nonce')
    ));
}
add_action('wp_enqueue_scripts', 'opg_enqueue_scripts');