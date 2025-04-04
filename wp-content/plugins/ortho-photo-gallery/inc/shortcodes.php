<?php
/**
 * Shortcodes for the photo gallery
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Register shortcode for displaying the gallery
function opg_gallery_shortcode($atts) {
    // Get plugin settings for default values
    $options = get_option('opg_gallery_settings', array(
        'default_columns' => 3 // Default to 3 columns if not set
    ));
    
    // Get default columns from settings
    $default_columns = isset($options['default_columns']) ? intval($options['default_columns']) : 3;
    
    // Shortcode attributes
    $atts = shortcode_atts(
        array(
            'category' => '',
            'limit' => -1,
            'columns' => $default_columns, // Use the value from settings
        ),
        $atts,
        'ortho_gallery'
    );
    
    // Set up query arguments
    $args = array(
        'post_type' => 'gallery_item',
        'posts_per_page' => $atts['limit'],
    );
    
    // Filter by category if specified
    if (!empty($atts['category'])) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'gallery_category',
                'field' => 'slug',
                'terms' => explode(',', $atts['category']),
            ),
        );
    }
    
    // Start output buffering
    ob_start();
    
    // Get gallery items
    $query = new WP_Query($args);
    
    // Check if there are gallery items
    if ($query->have_posts()) {
        ?>
        <div class="opg-gallery" data-columns="<?php echo esc_attr($atts['columns']); ?>">
            <?php
            while ($query->have_posts()) {
                $query->the_post();
                
                // Get post data
                $post_id = get_the_ID();
                $title = get_the_title();
                $caption = get_post_meta($post_id, '_opg_caption', true);
                
                // Check if the post has a featured image
                if (has_post_thumbnail()) {
                    $image_id = get_post_thumbnail_id();
                    $image_url = wp_get_attachment_image_url($image_id, 'large');
                    $image_thumb = wp_get_attachment_image_url($image_id, 'medium');
                    ?>
                    <div class="opg-item">
                        <a href="<?php echo esc_url($image_url); ?>" data-lightbox="gallery" data-title="<?php echo esc_attr($title); ?>">
                            <img src="<?php echo esc_url($image_thumb); ?>" alt="<?php echo esc_attr($title); ?>">
                            <div class="opg-item-overlay">
                                <h3><?php echo esc_html($title); ?></h3>
                                <?php if (!empty($caption)) : ?>
                                    <p><?php echo esc_html($caption); ?></p>
                                <?php endif; ?>
                            </div>
                        </a>
                    </div>
                    <?php
                }
            }
            ?>
        </div>
        <?php
    } else {
        ?>
        <p class="opg-no-items"><?php _e('No gallery items found.', 'ortho-photo-gallery'); ?></p>
        <?php
    }
    
    // Restore original post data
    wp_reset_postdata();
    
    // Return the buffered content
    return ob_get_clean();
}
add_shortcode('ortho_gallery', 'opg_gallery_shortcode');