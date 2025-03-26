<?php
/**
 * The template for displaying single product posts
 *
 * @package Neve
 */

$container_class = apply_filters( 'neve_container_class_filter', 'container', 'single-product' );

get_header();

?>
<div class="<?php echo esc_attr( $container_class ); ?> single-product-container">
    <div class="row">
        <?php do_action( 'neve_do_sidebar', 'single-product', 'left' ); ?>
        <article id="post-<?php echo esc_attr( get_the_ID() ); ?>"
                class="<?php echo esc_attr( join( ' ', get_post_class( 'nv-single-post-wrap col' ) ) ); ?>">
            
            <?php
            /**
             * Executes actions before the post content.
             *
             * @since 2.3.8
             */
            do_action( 'neve_before_post_content' );

            // Display parent page link if available
            $theParent = wp_get_post_parent_ID(get_the_ID());
            if($theParent){ ?>
                <div class="metabox metabox--position-up metabox--with-home-link">
                    <p><a class="metabox__blog-home-link" style="background-color: #0D3B66; color: #FFF; border-radius: 3px; padding: 10px 15px; display: inline-block; font-weight: normal;" href="<?php echo get_permalink($theParent); ?>">
                    <i class="fa fa-home" aria-hidden="true"></i> Back to <?php echo get_the_title($theParent); ?></a>
                    <span class="metabox__main" style="background-color:rgb(44, 44, 44); color: #FFF; border-radius: 3px; padding: 10px 15px; display: inline-block; margin-bottom: 30px; box-shadow: 2px 2px 1px rgba(0, 0, 0, .07);">
                    <?php echo the_title(); ?> </span></p>
                </div>
            <?php } ?>

            <div class="entry-content">
                <div class="product-single">
                    <h1 class="product-title"><?php the_title(); ?></h1>
                    
                    <div class="product-image-container">
                        <?php
                        // Display full size image if available
                        if (has_post_thumbnail()) {
                            the_post_thumbnail('full', ['class' => 'product-image', 'alt' => get_the_title()]);
                        } else {
                            // Fallback to manually constructed image path if no featured image
                            $product_title = get_the_title();
                            $image_name = str_replace(' ', '-', $product_title);
                            $product_image_url = site_url('/wp-content/uploads/2025/03/' . $image_name . '.jpg');
                            echo '<img src="' . esc_url($product_image_url) . '" alt="' . esc_attr(get_the_title()) . '" class="product-image">';
                        }
                        ?>
                    </div>
                    
                    <div class="product-description">
                        <h3>Product Description</h3>
                        <?php
                        // Display product description if available using ACF
                        if (function_exists('get_field') && get_field('product_description')) {
                            echo '<div class="description-content">';
                            echo get_field('product_description');
                            echo '</div>';
                        } else {
                            // Fallback to post content if ACF field not available
                            the_content();
                        }
                        ?>
                    </div>
                </div>
            </div>

            <?php
            /**
             * Executes actions after the post content.
             *
             * @since 2.3.8
             */
            do_action( 'neve_after_post_content' );
            ?>
        </article>
        <?php do_action( 'neve_do_sidebar', 'single-product', 'right' ); ?>
    </div>
</div>

<style>
    .single-product-container {
        padding: 40px 0;
    }
    
    .product-title {
        margin-bottom: 30px;
        font-size: 36px;
        color: #333;
    }
    
    .product-image-container {
        margin-bottom: 30px;
        text-align: center;
    }
    
    .product-image {
        max-width: 100%;
        height: auto;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    
    .product-description {
        margin-top: 30px;
    }
    
    .product-description h3 {
        margin-bottom: 20px;
        font-size: 24px;
        color: #333;
    }
    
    .description-content {
        line-height: 1.8;
        color: #444;
    }
    
    @media (min-width: 768px) {
        .product-image {
            max-height: 600px;
            width: auto;
        }
    }
</style>

<?php get_footer(); ?>
