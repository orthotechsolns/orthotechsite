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
			<div class="ortho-breadcrumbs">
				<a href="<?php echo esc_url(home_url('/')); ?>">Home</a> &raquo;
				<a href="<?php echo get_permalink($theParent); ?>"><?php echo get_the_title($theParent); ?></a> &raquo;
				<span class="current"><?php echo the_title(); ?></span>
			</div>
 	<?php }
 		?>

			<?php
				$testArray = get_pages(array(
		 			'child_of' => get_the_ID()
 			)); 

 				if($theParent or $testArray){ ?>
 					<div class="page-links" style="background: #f0f4f8; border-left: 4px solid #0073aa; padding: 15px; margin-left: 20px; width: 30%; float: right; margin-top: 30px;">
 					<h2 class="page-links__title" style="margin: 0 0 10px;">
 					<a href="<?php echo get_permalink($theParent); ?>" style="color: #0073aa; text-decoration: none;">
 					<?php echo get_the_title($theParent); ?>
 					</a>
 					</h2>

 					<ul class="min-list: none; padding: 0;">
 			<?php
 					if($theParent){
	 					$findChildrenOf = $theParent;
 					}
 					else{
 						$findChildrenOf = get_the_ID();
 					}
		
			 		wp_list_pages(array(
						'title_li' => NULL ,
						'child_of' => $findChildrenOf, 
						'sort_column' => 'menu_order'
 					));
 			?>
 					</ul>
 					</div>
 	<?php } ?>

			<?php
			if ( have_posts() ) {
				while ( have_posts() ) {
					the_post();
					get_template_part( 'template-parts/content', 'page' );
				}
			} else {
				get_template_part( 'template-parts/content', 'none' );
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