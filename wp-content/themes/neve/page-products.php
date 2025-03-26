<?php
/**
 * Template Name: Products Page
 * 
 * Custom template for displaying all products in a grid layout
 *
 * @package Neve
 */

// Add debug output to verify template is loading
echo '<!-- Products Page Template Loaded -->'; 

$container_class = apply_filters( 'neve_container_class_filter', 'container', 'single-page' );

get_header();

// Force using the correct page ID and query
global $post;
$page_id = get_queried_object_id();

// Custom query parameters
$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
$args = array(
    'post_type' => 'product',
    'posts_per_page' => 12,
    'paged' => $paged,
    'orderby' => 'title',
    'order' => 'ASC'
);

// Custom query
$products_query = new WP_Query($args);

// Debug output to check if query is working
echo '<!-- Products found: ' . $products_query->found_posts . ' -->';
?>

<div class="<?php echo esc_attr( $container_class ); ?> products-container">
    <div class="row">
        <div class="nv-single-page-wrap col">
            <div class="products-header">
                <h1 class="page-title">Products</h1>
                <div class="products-description">
                    <?php 
                    // Get content from the actual Products page
                    $products_page = get_post($page_id);
                    if ($products_page) {
                        echo apply_filters('the_content', $products_page->post_content);
                    }
                    ?>
                </div>
            </div>

            <!-- Display products count -->
            <p class="products-count">Showing <?php echo $products_query->found_posts; ?> products</p>

            <?php if ($products_query->have_posts()) : ?>
                <div class="products-grid">
                    <?php while ($products_query->have_posts()) : $products_query->the_post(); 
                        // Format product title for image path
                        $product_title = get_the_title();
                        $image_name = str_replace(' ', '-', $product_title);
                        $product_image_url = site_url('/wp-content/uploads/2025/03/' . $image_name . '-300x300.jpg');
                    ?>
                        <div class="product-card">
                            <div class="product-image">
                                <a href="<?php the_permalink(); ?>">
                                    <img src="<?php echo esc_url($product_image_url); ?>" alt="<?php the_title_attribute(); ?>" />
                                </a>
                            </div>
                            <div class="product-details">
                                <h2 class="product-title">
                                    <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                </h2>
                                <?php 
                                // Display product description if available using ACF
                                if (function_exists('get_field') && get_field('product_description')) : ?>
                                    <div class="product-excerpt">
                                        <?php echo wp_trim_words(get_field('product_description'), 15); ?>
                                    </div>
                                <?php endif; ?>
                                <a href="<?php the_permalink(); ?>" class="product-link">View Details</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>

                <div class="products-pagination">
                    <?php 
                    $total_pages = $products_query->max_num_pages;
                    if ($total_pages > 1) {
                        $current_page = max(1, get_query_var('paged'));
                        
                        echo '<div class="custom-pagination">';
                        
                        echo paginate_links(array(
                            'base' => get_pagenum_link(1) . '%_%',
                            'format' => 'page/%#%',
                            'current' => $current_page,
                            'total' => $total_pages,
                            'prev_text' => '&laquo; Previous',
                            'next_text' => 'Next &raquo;',
                            'type' => 'list'
                        ));
                        
                        echo '</div>';
                    }
                    ?>
                </div>
                <?php wp_reset_postdata(); ?>
            <?php else : ?>
                <div class="no-products">
                    <p>No products found. Please check your product post type configuration.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
/* Basic styling for product grid */
.products-header {
    margin-bottom: 40px;
    text-align: center;
}

.page-title {
    font-size: 36px;
    margin-bottom: 20px;
}

.products-count {
    text-align: center;
    margin-bottom: 30px;
    font-size: 16px;
    color: #666;
}

.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    grid-gap: 30px;
    margin-bottom: 50px;
}

.product-card {
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    overflow: hidden;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    background-color: white;
}

.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
}

.product-image {
    height: 200px;
    overflow: hidden;
    background-color: #f9f9f9;
    display: flex;
    align-items: center;
    justify-content: center;
}

.product-image img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.product-details {
    padding: 15px;
}

.product-title {
    font-size: 18px;
    margin-top: 0;
    margin-bottom: 10px;
}

.product-title a {
    color: #333;
    text-decoration: none;
}

.product-excerpt {
    font-size: 14px;
    color: #666;
    margin-bottom: 15px;
}

.product-link {
    display: inline-block;
    padding: 8px 16px;
    background-color: #0073aa;
    color: white !important;
    text-decoration: none;
    border-radius: 4px;
    font-size: 14px;
    transition: background-color 0.3s ease;
}

.product-link:hover {
    background-color: #005b8a;
}

/* Enhanced pagination styling */
.custom-pagination {
    text-align: center;
    margin-top: 30px;
}

.custom-pagination .page-numbers {
    display: inline-flex;
    list-style: none;
    padding: 0;
    margin: 0;
    border-radius: 4px;
    overflow: hidden;
}

.custom-pagination .page-numbers li {
    margin: 0;
}

.custom-pagination .page-numbers a,
.custom-pagination .page-numbers span {
    display: inline-block;
    padding: 10px 15px;
    background-color: #f5f5f5;
    color: #333;
    text-decoration: none;
    border: 1px solid #ddd;
    margin-left: -1px;
    font-size: 16px;
    transition: all 0.3s ease;
}

.custom-pagination .page-numbers a:hover {
    background-color: #e9e9e9;
    color: #0073aa;
}

.custom-pagination .page-numbers .current {
    background-color: #0073aa;
    color: white;
    border-color: #0073aa;
}

.custom-pagination .page-numbers .prev,
.custom-pagination .page-numbers .next {
    background-color: #fff;
    font-weight: bold;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .products-grid {
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    }
}

@media (max-width: 480px) {
    .products-grid {
        grid-template-columns: 1fr;
    }
    
    .custom-pagination .page-numbers a,
    .custom-pagination .page-numbers span {
        padding: 8px 12px;
        font-size: 14px;
    }
}
</style>

<?php get_footer(); ?>
