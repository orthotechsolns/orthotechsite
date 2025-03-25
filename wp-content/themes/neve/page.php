<?php
/**
 * The template for displaying all pages.
 *
 * @package Neve
 * @since   1.0.0
 */
$container_class = apply_filters( 'neve_container_class_filter', 'container', 'single-page' );

get_header();

$context = class_exists( 'WooCommerce', false ) && ( is_cart() || is_checkout() || is_account_page() ) ? 'woo-page' : 'single-page';
?>
<div class="<?php echo esc_attr( $container_class ); ?> single-page-container">
	<div class="row">
		<?php do_action( 'neve_do_sidebar', $context, 'left' ); ?>
		<div class="nv-single-page-wrap col">
			<?php
			/**
			 * Executes actions before the page header.
			 *
			 * @since 2.4.0
			 */
			do_action( 'neve_before_page_header' );

			/**
			 * Executes the rendering function for the page header.
			 *
			 * @param string $context The displaying location context.
			 *
			 * @since 1.0.7
			 */
			do_action( 'neve_page_header', $context );

			/**
			 * Executes actions before the page content.
			 *
			 * @param string $context The displaying location context.
			 *
			 * @since 1.0.7
			 */
			do_action( 'neve_before_content', $context );
			?>
			
		<?php
 			$theParent = wp_get_post_parent_ID(get_the_ID());
			if($theParent){ ?>
	<div class="metabox metabox--position-up metabox--with-home-link">
		
 	<p><a class="metabox__blog-home-link" style="background-color: #0D3B66; color: #FFF; border-radius: 3px; padding: 10px 15px; display: inline-block; font-weight: normal;" href="<?php echo
get_permalink($theParent); ?>">
 	<i class="fa fa-home" aria-hidden="true"></i> Back to <?php
echo get_the_title($theParent); ?></a>
 	<span class="metabox__main" style="background-color:rgb(44, 44, 44); color: #FFF; border-radius: 3px; padding: 10px 15px; display: inline-block; margin-bottom: 30px; box-shadow: 2px 2px 1px rgba(0, 0, 0, .07);">
		<?php echo the_title(); ?> 
	</span></p>
 	</div>
 		<?php }
 		?>

		<?php
			if ( is_page( 'splints-immobilizers' ) ) {
				$args = array(
					'post_type' => 'product',
					'posts_per_page' => -1,
					'tax_query' => array(
						array(
							'taxonomy' => 'product_category',
							'field'    => 'slug',
							'terms'    => 'splints-and-immobilizers',
							'operator' => 'IN',
						),
					),
				);

				$query = new WP_Query( $args );

				if ( $query->have_posts() ) :
					echo '<div class="products-list">';
					while ( $query->have_posts() ) :
						$query->the_post();
						?>
						<div class="product-item">
							<h2><?php the_title(); ?></h2>
							<div class="product-image">
								<?php the_post_thumbnail(); ?>
							</div>
							<div class="product-description">
								<?php the_excerpt(); ?>
							</div>
						</div>
						<?php
					endwhile;
					echo '</div>';
				else :
					echo '<p>No products found in this category.</p>';
				endif;

				wp_reset_postdata();

			} else {

			if ( have_posts() ) {
				while ( have_posts() ) {
					the_post();
					get_template_part( 'template-parts/content', 'page' );
				}
			} else {
				get_template_part( 'template-parts/content', 'none' );
			}
		}

			/**
			 * Executes actions after the page content.
			 *
			 * @param string $context The displaying location context.
			 *
			 * @since 1.0.7
			 */

			do_action( 'neve_after_content', $context );
			?>
		</div>
		<?php do_action( 'neve_do_sidebar', $context, 'right' ); ?>
	</div>
</div>
<?php get_footer(); ?>
