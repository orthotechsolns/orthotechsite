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
    // Shortcode attributes
    $atts = shortcode_atts(
        array(
            'category' => '',
            'limit' => -1,
            'columns' => 3,
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
    
    // Get all categories for filter
    $categories = get_terms(array(
        'taxonomy' => 'gallery_category',
        'hide_empty' => true,
    ));
    
    // Start output buffering
    ob_start();
    
    // Display category filters if there are categories
    if (!empty($categories) && !is_wp_error($categories)) {
        ?>
        <div class="opg-filters">
            <ul>
                <li><a href="#" data-category="all" class="active"><?php _e('All', 'ortho-photo-gallery'); ?></a></li>
                <?php foreach ($categories as $category) : ?>
                    <li><a href="#" data-category="<?php echo esc_attr($category->slug); ?>"><?php echo esc_html($category->name); ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php
    }
    
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
                
                // Get category slugs for filtering
                $item_categories = get_the_terms($post_id, 'gallery_category');
                $category_classes = '';
                $category_names = array();
                
                if (!empty($item_categories) && !is_wp_error($item_categories)) {
                    foreach ($item_categories as $cat) {
                        $category_classes .= ' category-' . $cat->slug;
                        $category_names[] = $cat->name;
                    }
                }
                
                // Check if the post has a featured image
                if (has_post_thumbnail()) {
                    $image_id = get_post_thumbnail_id();
                    $image_url = wp_get_attachment_image_url($image_id, 'large');
                    $image_thumb = wp_get_attachment_image_url($image_id, 'medium');
                    ?>
                    <div class="opg-item<?php echo esc_attr($category_classes); ?>">
                        <a href="<?php echo esc_url($image_url); ?>" data-lightbox="gallery" data-title="<?php echo esc_attr($title); ?>">
                            <img src="<?php echo esc_url($image_thumb); ?>" alt="<?php echo esc_attr($title); ?>">
                            <div class="opg-item-overlay">
                                <h3><?php echo esc_html($title); ?></h3>
                                <?php if (!empty($caption)) : ?>
                                    <p><?php echo esc_html($caption); ?></p>
                                <?php endif; ?>
                                <?php if (!empty($category_names)) : ?>
                                    <span class="opg-categories"><?php echo esc_html(implode(', ', $category_names)); ?></span>
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

// AJAX handler for filtering gallery items
function opg_filter_gallery() {
    // Check nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'opg-nonce')) {
        wp_send_json_error('Invalid nonce');
    }
    
    // Get category slug from AJAX request
    $category = isset($_POST['category']) ? sanitize_text_field($_POST['category']) : 'all';
    
    // Set up query arguments
    $args = array(
        'post_type' => 'gallery_item',
        'posts_per_page' => -1,
    );
    
    // Filter by category if specified
    if ($category !== 'all') {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'gallery_category',
                'field' => 'slug',
                'terms' => $category,
            ),
        );
    }
    
    // Get gallery items
    $query = new WP_Query($args);
    
    // Prepare response
    $response = array();
    
    // Check if there are gallery items
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            
            // Get post data
            $post_id = get_the_ID();
            $title = get_the_title();
            $caption = get_post_meta($post_id, '_opg_caption', true);
            
            // Get category slugs for filtering
            $item_categories = get_the_terms($post_id, 'gallery_category');
            $category_names = array();
            
            if (!empty($item_categories) && !is_wp_error($item_categories)) {
                foreach ($item_categories as $cat) {
                    $category_names[] = $cat->name;
                }
            }
            
            // Check if the post has a featured image
            if (has_post_thumbnail()) {
                $image_id = get_post_thumbnail_id();
                $image_url = wp_get_attachment_image_url($image_id, 'large');
                $image_thumb = wp_get_attachment_image_url($image_id, 'medium');
                
                // Add item to response
                $response[] = array(
                    'id' => $post_id,
                    'title' => $title,
                    'caption' => $caption,
                    'categories' => $category_names,
                    'image_url' => $image_url,
                    'image_thumb' => $image_thumb,
                );
            }
        }
    }
    
    // Restore original post data
    wp_reset_postdata();
    
    // Send response
    wp_send_json_success($response);
}
add_action('wp_ajax_opg_filter_gallery', 'opg_filter_gallery');
add_action('wp_ajax_nopriv_opg_filter_gallery', 'opg_filter_gallery');