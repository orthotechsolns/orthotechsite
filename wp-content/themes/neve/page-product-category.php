<?php
/**
 * Template Name: Product Category Page
 * 
 * A simple template for displaying products from a specific category
 *
 * @package Neve
 */

$container_class = apply_filters( 'neve_container_class_filter', 'container', 'single-page' );

// Get the category slug from the page slug
$page_slug = get_post_field('post_name', get_post());
$category = get_term_by('slug', $page_slug, 'product_category'); // Updated to product_category
$category_name = $category ? $category->name : get_the_title();
$category_id = $category ? $category->term_id : 0;

get_header();

// Custom query parameters
$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
$args = array(
    'post_type' => 'product',
    'posts_per_page' => 12,
    'paged' => $paged,
    'orderby' => 'title',
    'order' => 'ASC'
);

// Add category filter if we have a valid category
if ($category_id) {
    $args['tax_query'] = array(
        array(
            'taxonomy' => 'product_category', // Updated to product_category
            'field'    => 'term_id',
            'terms'    => $category_id,
        )
    );
}

// Custom query
$products_query = new WP_Query($args);
?>

<div class="<?php echo esc_attr( $container_class ); ?> products-container">
    <div class="row">
        <div class="nv-single-page-wrap col">
            <div class="products-header">
                <h1 class="page-title"><?php echo esc_html($category_name); ?></h1>
                <div class="products-description">
                    <?php 
                    if ($category && !empty($category->description)) {
                        echo '<p>' . esc_html($category->description) . '</p>';
                    } else {
                        echo '<p>' . get_the_content() . '</p>';
                    }
                    ?>
                </div>
            </div>

            <!-- Display products count -->
            <p class="products-count">Showing <?php echo $products_query->found_posts; ?> products</p>

            <?php if ($products_query->have_posts()) : ?>
                <div class="products-grid">
                    <?php while ($products_query->have_posts()) : $products_query->the_post(); 
                        // Format product title for image path (fallback)
                        $product_title = get_the_title();
                        $image_name = str_replace(' ', '-', $product_title);
                        
                        // Try to get featured image
                        if (has_post_thumbnail()) {
                            $product_image_url = get_the_post_thumbnail_url(get_the_ID(), 'medium');
                        } else {
                            $product_image_url = site_url('/wp-content/uploads/2025/03/' . $image_name . '-300x300.jpg');
                        }
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
                                <?php else : ?>
                                    <div class="product-excerpt">
                                        <?php echo wp_trim_words(get_the_excerpt(), 15); ?>
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
                    <p>No products found in this category. Please check back soon for our updated inventory.</p>
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
}

.custom-pagination .page-numbers li {
    margin: 0 2px;
}

.custom-pagination .page-numbers a,
.custom-pagination .page-numbers span {
    display: inline-block;
    padding: 10px 15px;
    background-color: #f5f5f5;
    color: #333;
    border-radius: 4px;
    text-decoration: none;
    transition: all 0.3s ease;
}

.custom-pagination .page-numbers a:hover {
    background-color: #e0e0e0;
}

.custom-pagination .page-numbers .current {
    background-color: #0073aa;
    color: white;
}

.no-products {
    text-align: center;
    padding: 50px 20px;
    font-size: 18px;
    color: #666;
    background-color: #f9f9f9;
    border-radius: 8px;
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
