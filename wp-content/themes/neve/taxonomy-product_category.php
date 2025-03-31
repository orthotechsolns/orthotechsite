<?php
/**
 * The template for displaying product category archives
 * 
 * This replaces the separate archive templates for hand-braces and foot-braces
 * with a single, reusable template file for all product categories.
 *
 * @package Neve
 */

$container_class = apply_filters( 'neve_container_class_filter', 'container', 'taxonomy-product_category' );

get_header();

// Get the current category
$queried_object = get_queried_object();
$category_name = $queried_object->name;
$category_id = $queried_object->term_id;

// Get current page for pagination
$paged = (get_query_var('paged')) ? absint(get_query_var('paged')) : 1;

// Create custom query for products in this category
$args = array(
    'post_type'      => 'product',
    'posts_per_page' => 4,
    'paged'          => $paged,
    'tax_query'      => array(
        array(
            'taxonomy' => 'product_category',
            'field'    => 'term_id',
            'terms'    => $category_id,
        ),
    ),
);

$products_query = new WP_Query($args);
?>

<div class="<?php echo esc_attr( $container_class ); ?> products-container">
    <div class="row">
        <div class="nv-index-posts col">
            <!-- Breadcrumbs -->
            <div class="metabox metabox--position-up metabox--with-home-link">
                <p>
                    <a class="metabox__blog-home-link" style="background-color: #0D3B66; color: #FFF; border-radius: 3px; padding: 10px 15px; display: inline-block; font-weight: normal;" href="<?php echo get_post_type_archive_link('product'); ?>">
                        <i class="fa fa-home" aria-hidden="true"></i> Back to Products
                    </a>
                    <span class="metabox__main" style="background-color:rgb(44, 44, 44); color: #FFF; border-radius: 3px; padding: 10px 15px; display: inline-block; margin-bottom: 30px; box-shadow: 2px 2px 1px rgba(0, 0, 0, .07);"><?php echo esc_html($category_name); ?></span>
                </p>
            </div>
            
            <div class="products-header">
                <h1 class="page-title"><?php echo esc_html($category_name); ?></h1>
            </div>

            <?php
            // Display products count
            echo '<p class="products-count">Showing ' . $products_query->found_posts . ' products in ' . esc_html($category_name) . '</p>';
            
            if ($products_query->have_posts()) {
                echo '<div class="products-grid">';
                
                while ($products_query->have_posts()) {
                    $products_query->the_post();
                    
                    // Format image path
                    $image_name = str_replace(' ', '-', get_the_title());
                    $product_image_url = site_url('/wp-content/uploads/2025/03/' . $image_name . '-300x300.jpg');
                    
                    echo '<div class="product-card">';
                    
                    // Product image
                    echo '<div class="product-image">';
                    echo '<a href="' . get_permalink() . '">';
                    echo '<img src="' . esc_url($product_image_url) . '" alt="' . get_the_title() . '" />';
                    echo '</a>';
                    echo '</div>';
                    
                    // Product details
                    echo '<div class="product-details">';
                    echo '<h2 class="product-title"><a href="' . get_permalink() . '">' . get_the_title() . '</a></h2>';
                    
                    // Product description (if available)
                    if (has_excerpt()) {
                        echo '<div class="product-excerpt">' . get_the_excerpt() . '</div>';
                    } elseif (function_exists('get_field') && get_field('product_description')) {
                        echo '<div class="product-excerpt">' . wp_trim_words(get_field('product_description'), 15) . '</div>';
                    }
                    
                    echo '<a href="' . get_permalink() . '" class="product-link">View Details</a>';
                    echo '</div>';
                    
                    echo '</div>'; // End product-card
                }
                
                echo '</div>'; // End products-grid
            
                echo '<div class="products-pagination"><div class="custom-pagination">';
                
                echo paginate_links(array(
                    'total' => $products_query->max_num_pages,
                    'prev_text' => '&laquo; Previous',
                    'next_text' => 'Next &raquo;',
                    'type' => 'list',
                ));
                
                echo '</div></div>';
                
            } else {
                echo '<div class="no-products"><p>No products found in this category.</p></div>';
            }
            
            wp_reset_postdata();
            ?>
        </div>
    </div>
</div>

<style>
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