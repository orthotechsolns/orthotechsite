<?php
/**
 * Admin functions for the photo gallery
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Add admin menu page for gallery settings
function opg_admin_menu() {
    add_submenu_page(
        'edit.php?post_type=gallery_item',
        __('Gallery Settings', 'ortho-photo-gallery'),
        __('Settings', 'ortho-photo-gallery'),
        'manage_options',
        'opg-settings',
        'opg_settings_page'
    );
}
add_action('admin_menu', 'opg_admin_menu');

// Settings page callback
function opg_settings_page() {
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <form method="post" action="options.php">
            <?php
            // Output security fields
            settings_fields('opg_settings');
            
            // Output setting sections
            do_settings_sections('opg-settings');
            
            // Submit button
            submit_button();
            ?>
        </form>
        
        <div class="opg-shortcode-info">
            <h2><?php _e('Shortcode Usage', 'ortho-photo-gallery'); ?></h2>
            <p><?php _e('Use the following shortcode to display the gallery in your pages or posts:', 'ortho-photo-gallery'); ?></p>
            <code>[ortho_gallery]</code>
            
            <h3><?php _e('Available Attributes', 'ortho-photo-gallery'); ?></h3>
            <ul>
                <li><code>category</code> - <?php _e('Filter by category slug (comma-separated for multiple categories)', 'ortho-photo-gallery'); ?></li>
                <li><code>limit</code> - <?php _e('Limit the number of items to display (default: -1, all items)', 'ortho-photo-gallery'); ?></li>
                <li><code>columns</code> - <?php _e('Number of columns to display (default: 3)', 'ortho-photo-gallery'); ?></li>
            </ul>
            
            <h3><?php _e('Examples', 'ortho-photo-gallery'); ?></h3>
            <p><code>[ortho_gallery category="braces,supports" limit="6" columns="4"]</code></p>
        </div>
    </div>
    <?php
}

// Register plugin settings
function opg_register_settings() {
    // Register settings
    register_setting('opg_settings', 'opg_gallery_settings');
    
    // Add settings section
    add_settings_section(
        'opg_general_settings',
        __('General Settings', 'ortho-photo-gallery'),
        'opg_general_settings_callback',
        'opg-settings'
    );
    
    // Add settings field for columns only (lightbox setting removed)
    add_settings_field(
        'opg_default_columns',
        __('Default Columns', 'ortho-photo-gallery'),
        'opg_default_columns_callback',
        'opg-settings',
        'opg_general_settings'
    );
}
add_action('admin_init', 'opg_register_settings');

// Settings section callback
function opg_general_settings_callback() {
    echo '<p>' . __('Configure general gallery settings.', 'ortho-photo-gallery') . '</p>';
}

// Default columns setting callback
function opg_default_columns_callback() {
    $options = get_option('opg_gallery_settings', array(
        'default_columns' => 3, // Default to 3 columns
    ));
    
    $default_columns = isset($options['default_columns']) ? $options['default_columns'] : 3;
    
    ?>
    <select name="opg_gallery_settings[default_columns]">
        <?php for ($i = 1; $i <= 6; $i++) : ?>
            <option value="<?php echo $i; ?>" <?php selected($default_columns, $i); ?>><?php echo $i; ?></option>
        <?php endfor; ?>
    </select>
    <p class="description"><?php _e('Set the default number of columns for the gallery grid.', 'ortho-photo-gallery'); ?></p>
    <?php
}

// Add settings link to plugins page
function opg_add_settings_link($links) {
    $settings_link = '<a href="edit.php?post_type=gallery_item&page=opg-settings">' . __('Settings', 'ortho-photo-gallery') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
}
$plugin = plugin_basename(dirname(dirname(__FILE__)) . '/ortho-photo-gallery.php');
add_filter("plugin_action_links_$plugin", 'opg_add_settings_link');