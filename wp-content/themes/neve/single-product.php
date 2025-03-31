<?php
/**
 * The template for displaying single product pages
 *
 * @package Neve
 */

$container_class = apply_filters( 'neve_container_class_filter', 'container', 'single-product' );

get_header();
?>

<div class="<?php echo esc_attr( $container_class ); ?> single-product-container">
    <div class="row">
        <div class="nv-single-page-wrap col">
            <?php
            if (have_posts()) :
                while (have_posts()) : the_post();
                    
                    // Get product categories
                    $product_cats = get_the_terms(get_the_ID(), 'product_category');
                    $category_name = !empty($product_cats) ? $product_cats[0]->name : '';
                    $category_link = !empty($product_cats) ? get_term_link($product_cats[0]) : '';
            ?>
                    <!-- Breadcrumbs -->
                    <div class="metabox metabox--position-up metabox--with-home-link">
                        <p>
                            <a class="metabox__blog-home-link" style="background-color: #0D3B66; color: #FFF; border-radius: 3px; padding: 10px 15px; display: inline-block; font-weight: normal;" href="<?php echo get_post_type_archive_link('product'); ?>">
                                <i class="fa fa-home" aria-hidden="true"></i> Products
                            </a>
                            <?php if (!empty($category_name)) : ?>
                            <a class="metabox__blog-home-link" style="background-color: #0D3B66; color: #FFF; border-radius: 3px; padding: 10px 15px; display: inline-block; font-weight: normal; margin-left: 5px;" href="<?php echo $category_link; ?>">
                                <?php echo $category_name; ?>
                            </a>
                            <?php endif; ?>
                            <span class="metabox__main" style="background-color:rgb(44, 44, 44); color: #FFF; border-radius: 3px; padding: 10px 15px; display: inline-block; margin-bottom: 30px; box-shadow: 2px 2px 1px rgba(0, 0, 0, .07);"><?php the_title(); ?></span>
                        </p>
                    </div>

                    <div class="single-product-content">
                        <h1 class="product-title"><?php the_title(); ?></h1>
                        
                        <div class="product-layout">
                            <div class="product-image-container">
                                <?php
                                // Format image path
                                $image_name = str_replace(' ', '-', get_the_title());
                                $product_image_url = site_url('/wp-content/uploads/2025/03/' . $image_name . '-300x300.jpg');
                                ?>
                                <img src="<?php echo esc_url($product_image_url); ?>" alt="<?php echo get_the_title(); ?>" class="product-image" />
                            </div>
                            
                            <div class="product-details-container">
                                <?php if (function_exists('get_field')) : ?>
                                    <?php if (get_field('product_description')) : ?>
                                        <div class="product-description">
                                            <?php echo get_field('product_description'); ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (get_field('product_features')) : ?>
                                        <div class="product-features">
                                            <h3>Features</h3>
                                            <?php echo get_field('product_features'); ?>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                                
                                <div class="product-content">
                                    <?php the_content(); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php
                endwhile;
            endif;
            ?>
        </div>
    </div>
</div>

<style>
.single-product-container {
    padding: 40px 0;
}

.product-title {
    font-size: 32px;
    margin-bottom: 30px;
}

.product-layout {
    display: flex;
    flex-wrap: wrap;
    gap: 40px;
    margin-bottom: 40px;
}

.product-image-container {
    flex: 0 0 300px;
}

.product-image {
    width: 100%;
    height: auto;
    border: 1px solid #eee;
    padding: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    background-color: white;
}

.product-details-container {
    flex: 1;
    min-width: 300px;
}

.product-description {
    margin-bottom: 30px;
    line-height: 1.6;
}

.product-features {
    margin-bottom: 30px;
    background-color: #f9f9f9;
    padding: 20px;
    border-left: 4px solid #0073aa;
}

.product-features h3 {
    margin-top: 0;
    color: #0073aa;
}

.product-content {
    line-height: 1.6;
}

@media (max-width: 768px) {
    .product-layout {
        flex-direction: column;
    }
    
    .product-image-container {
        margin-bottom: 20px;
        align-self: center;
    }
}
</style>

<?php get_footer(); ?>
