<?php
/**
 * Register custom post types for the photo gallery
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Register Gallery custom post type
function opg_register_post_types() {
    $labels = array(
        'name'                  => _x('Gallery Items', 'Post Type General Name', 'ortho-photo-gallery'),
        'singular_name'         => _x('Gallery Item', 'Post Type Singular Name', 'ortho-photo-gallery'),
        'menu_name'             => __('Photo Gallery', 'ortho-photo-gallery'),
        'name_admin_bar'        => __('Gallery Item', 'ortho-photo-gallery'),
        'archives'              => __('Item Archives', 'ortho-photo-gallery'),
        'attributes'            => __('Item Attributes', 'ortho-photo-gallery'),
        'all_items'             => __('All Gallery Items', 'ortho-photo-gallery'),
        'add_new_item'          => __('Add New Gallery Item', 'ortho-photo-gallery'),
        'add_new'               => __('Add New', 'ortho-photo-gallery'),
        'new_item'              => __('New Gallery Item', 'ortho-photo-gallery'),
        'edit_item'             => __('Edit Gallery Item', 'ortho-photo-gallery'),
        'update_item'           => __('Update Gallery Item', 'ortho-photo-gallery'),
        'view_item'             => __('View Gallery Item', 'ortho-photo-gallery'),
        'view_items'            => __('View Gallery Items', 'ortho-photo-gallery'),
        'search_items'          => __('Search Gallery Item', 'ortho-photo-gallery'),
    );
    
    $args = array(
        'label'                 => __('Gallery Item', 'ortho-photo-gallery'),
        'description'           => __('Photo gallery items', 'ortho-photo-gallery'),
        'labels'                => $labels,
        'supports'              => array('title', 'editor', 'thumbnail'),
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 5,
        'menu_icon'             => 'dashicons-format-gallery',
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'has_archive'           => true,
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'post',
        'show_in_rest'          => true,
    );
    
    register_post_type('gallery_item', $args);
    
    // Register category taxonomy for gallery items
    $tax_labels = array(
        'name'                       => _x('Categories', 'Taxonomy General Name', 'ortho-photo-gallery'),
        'singular_name'              => _x('Category', 'Taxonomy Singular Name', 'ortho-photo-gallery'),
        'menu_name'                  => __('Categories', 'ortho-photo-gallery'),
        'all_items'                  => __('All Categories', 'ortho-photo-gallery'),
        'parent_item'                => __('Parent Category', 'ortho-photo-gallery'),
        'parent_item_colon'          => __('Parent Category:', 'ortho-photo-gallery'),
        'new_item_name'              => __('New Category Name', 'ortho-photo-gallery'),
        'add_new_item'               => __('Add New Category', 'ortho-photo-gallery'),
        'edit_item'                  => __('Edit Category', 'ortho-photo-gallery'),
        'update_item'                => __('Update Category', 'ortho-photo-gallery'),
        'view_item'                  => __('View Category', 'ortho-photo-gallery'),
        'separate_items_with_commas' => __('Separate categories with commas', 'ortho-photo-gallery'),
        'add_or_remove_items'        => __('Add or remove categories', 'ortho-photo-gallery'),
        'choose_from_most_used'      => __('Choose from the most used', 'ortho-photo-gallery'),
        'popular_items'              => __('Popular Categories', 'ortho-photo-gallery'),
        'search_items'               => __('Search Categories', 'ortho-photo-gallery'),
        'not_found'                  => __('Not Found', 'ortho-photo-gallery'),
    );
    
    $tax_args = array(
        'labels'                     => $tax_labels,
        'hierarchical'               => true,
        'public'                     => true,
        'show_ui'                    => true,
        'show_admin_column'          => true,
        'show_in_nav_menus'          => true,
        'show_tagcloud'              => true,
        'show_in_rest'               => true,
    );
    
    register_taxonomy('gallery_category', array('gallery_item'), $tax_args);
}
add_action('init', 'opg_register_post_types');

// Add custom meta box for gallery item details
function opg_add_meta_boxes() {
    add_meta_box(
        'opg_gallery_details',
        __('Gallery Item Details', 'ortho-photo-gallery'),
        'opg_gallery_details_callback',
        'gallery_item',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'opg_add_meta_boxes');

// Meta box callback function
function opg_gallery_details_callback($post) {
    // Add nonce for security
    wp_nonce_field('opg_save_gallery_details', 'opg_gallery_details_nonce');
    
    // Get existing meta values
    $caption = get_post_meta($post->ID, '_opg_caption', true);
    
    // Meta box content
    ?>
    <p>
        <label for="opg_caption"><?php _e('Caption:', 'ortho-photo-gallery'); ?></label>
        <textarea id="opg_caption" name="opg_caption" class="large-text" rows="3"><?php echo esc_textarea($caption); ?></textarea>
        <span class="description"><?php _e('Enter a caption for this gallery item.', 'ortho-photo-gallery'); ?></span>
    </p>
    <?php
}

// Save meta box data
function opg_save_meta_boxes($post_id) {
    // Check if nonce is set
    if (!isset($_POST['opg_gallery_details_nonce'])) {
        return;
    }
    
    // Verify nonce
    if (!wp_verify_nonce($_POST['opg_gallery_details_nonce'], 'opg_save_gallery_details')) {
        return;
    }
    
    // If this is an autosave, don't do anything
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    // Check user permissions
    if ('gallery_item' === $_POST['post_type']) {
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
    }
    
    // Save caption
    if (isset($_POST['opg_caption'])) {
        update_post_meta($post_id, '_opg_caption', sanitize_textarea_field($_POST['opg_caption']));
    }
}
add_action('save_post', 'opg_save_meta_boxes');