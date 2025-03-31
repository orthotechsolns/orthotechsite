<?php
/**
 * The template for displaying testimonial category archives
 *
 * @package Neve
 */

get_header();

// Get current category
$current_category = get_queried_object();
?>

<div class="container">
    <div class="row">
        <div class="nv-content-wrap col">
            <!-- Breadcrumbs -->
            <div class="ortho-breadcrumbs">
                <a href="<?php echo esc_url(home_url('/')); ?>">Home</a> &raquo; 
                <a href="<?php echo esc_url(get_post_type_archive_link('testimonial')); ?>">Testimonials</a> &raquo; 
                <span class="current"><?php echo esc_html($current_category->name); ?></span>
            </div>
            
            <h1 class="page-title"><?php echo esc_html($current_category->name); ?> Testimonials</h1>
            
            <?php
            // Get current page for pagination
            $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
            
            $query = new WP_Query(array(
                'post_type'      => 'testimonial',
                'posts_per_page' => 4,
                'orderby'        => 'date',
                'order'          => 'DESC',
                'paged'          => $paged,
                'tax_query'      => array(
                    array(
                        'taxonomy' => 'testimonial_category',
                        'field'    => 'slug',
                        'terms'    => $current_category->slug,
                    ),
                ),
            ));
            
            if ($query->have_posts()) {
                echo '<div class="testimonials-container">';
                while ($query->have_posts()) {
                    $query->the_post();
                    
                    $customer_name = get_post_meta(get_the_ID(), 'customer_name', true);
                    if (empty($customer_name)) {
                        $customer_name = get_the_title();
                    }
                    
                    $customer_review = get_post_meta(get_the_ID(), 'customer_review', true);
                    if (empty($customer_review)) {
                        $customer_review = get_the_content();
                    }
                    
                    $review_date = get_field('review_date', get_the_ID());

                    if ($review_date) {
                        $review_date = date('dS M Y', strtotime($review_date));
                    } else {
                        $review_date = get_the_date('d M Y');
                    }
                    
                    $verified_purchase = get_post_meta(get_the_ID(), 'verified_purchase', true);
                    
                    // Display testimonial card
                    echo '<div class="testimonial-card">';
                    echo '<h3 class="customer-name">' . esc_html($customer_name) . '</h3>';
                    
                    // Display verified purchase status if true
                    if ($verified_purchase === 'Yes' || $verified_purchase === '1' || $verified_purchase === 1 || $verified_purchase === true) {
                        echo '<span class="verified-badge"><span class="dashicons dashicons-yes"></span> Verified Purchase</span>';
                    }
                    
                    echo '<div class="review-content">' . wpautop(wp_kses_post($customer_review)) . '</div>';
                    echo '<div class="review-date">' . esc_html($review_date) . '</div>';
                    
                    // Display related products if available
                    $related_products = get_field('related_products', get_the_ID());
                    if ($related_products && !empty($related_products)) {
                        echo '<div class="testimonial-related-products">';
                        echo '<h6>Related Products</h6>';
                        echo '<div class="related-products-grid">';
                        
                        foreach ($related_products as $product) {
                            echo '<div class="related-product-card">';
                            echo '<a href="' . get_permalink($product->ID) . '">';
                            
                            if (has_post_thumbnail($product->ID)) {
                                echo '<div class="related-product-image">';
                                echo get_the_post_thumbnail($product->ID, 'thumbnail');
                                echo '</div>';
                            }
                            
                            echo '<h5>' . get_the_title($product->ID) . '</h5>';
                            echo '</a>';
                            echo '</div>';
                        }
                        
                        echo '</div>'; // End related-products-grid
                        echo '</div>'; // End testimonial-related-products
                    }
                    
                    echo '</div>'; // End testimonial-card
                }
                echo '</div>'; // End testimonials-container
                
                // Add pagination
                $pagination = paginate_links(array(
                    'base'         => str_replace(999999999, '%#%', esc_url(get_pagenum_link(999999999))),
                    'total'        => $query->max_num_pages,
                    'current'      => max(1, $paged),
                    'format'       => '?paged=%#%',
                    'show_all'     => false,
                    'type'         => 'plain',
                    'end_size'     => 2,
                    'mid_size'     => 1,
                    'prev_next'    => true,
                    'prev_text'    => '&laquo; Previous',
                    'next_text'    => 'Next &raquo;',
                    'add_args'     => false,
                    'add_fragment' => '',
                ));
                
                if ($pagination) {
                    echo '<div class="testimonials-pagination">';
                    echo $pagination;
                    echo '</div>';
                }
                
                wp_reset_postdata();
            } else {
                echo '<p>No testimonials found in this category.</p>';
                
                // If you're an admin, show a helpful message
                if (current_user_can('manage_options')) {
                    echo '<div class="admin-notice" style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-left: 4px solid #007cba;">';
                    echo '<p>' . esc_html__('Admin notice: No testimonials found in this category. Try adding some from the WordPress admin area.', 'neve') . '</p>';
                    echo '<a href="' . esc_url(admin_url('post-new.php?post_type=testimonial')) . '" class="button button-primary">' . esc_html__('Add New Testimonial', 'neve') . '</a>';
                    echo '</div>';
                }
            }
            ?>
            
            <style>
                .ortho-breadcrumbs {
                    margin-bottom: 20px;
                    padding: 10px 0;
                    font-size: 14px;
                    color: #777;
                }
                
                .ortho-breadcrumbs a {
                    color: #0073aa;
                    text-decoration: none;
                }
                
                .ortho-breadcrumbs a:hover {
                    text-decoration: underline;
                }
                
                .ortho-breadcrumbs .current {
                    color: #333;
                    font-weight: bold;
                }
                
                .testimonials-container {
                    display: grid;
                    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
                    gap: 20px;
                    margin: 20px 0;
                }
                .testimonial-card {
                    padding: 20px;
                    border-radius: 5px;
                    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
                    background-color: #fff;
                }
                .customer-name {
                    margin-top: 0;
                    margin-bottom: 10px;
                    font-weight: bold;
                }
                .verified-badge {
                    display: inline-flex;
                    align-items: center;
                    background: #e7f7ea;
                    color: #28a745;
                    padding: 2px 8px;
                    border-radius: 3px;
                    margin-bottom: 10px;
                    font-size: 14px;
                }
                .verified-badge .dashicons {
                    font-size: 16px;
                    width: 16px;
                    height: 16px;
                    margin-right: 5px;
                }
                .review-content {
                    margin-bottom: 15px;
                }
                .review-date {
                    color: #777;
                    font-size: 14px;
                    font-style: italic;
                }
                .testimonial-related-products {
                    margin-top: 15px;
                }
                .related-products-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
                    gap: 10px;
                }
                .related-product-card {
                    text-align: center;
                }
                .related-product-image {
                    margin-bottom: 10px;
                }
                .testimonials-pagination {
                    margin-top: 30px;
                    text-align: center;
                }
                .testimonials-pagination .page-numbers {
                    padding: 5px 10px;
                    margin: 0 5px;
                    border: 1px solid #ddd;
                    border-radius: 3px;
                    display: inline-block;
                    text-decoration: none;
                    color: #333;
                }
                .testimonials-pagination .page-numbers.current {
                    background-color: #0073aa;
                    color: white;
                    border-color: #0073aa;
                }
                .testimonials-pagination .page-numbers:hover:not(.current) {
                    background-color: #f5f5f5;
                }
            </style>
        </div>
    </div>
</div>

<?php
get_footer();
?>