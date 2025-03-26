<?php
/**
 * Author:          Uriahs Victor
 * Created on:      14/07/2021 (d/m/y)
 *
 * @package Neve
 */

$container_class = apply_filters( 'neve_container_class_filter', 'container', 'download-archive' );

get_header();

?>

<div id= "nv-edd-download-archive-container" class="<?php echo esc_attr( $container_class ); ?>">
    

		<div id="wrapper">
			<?php
			/**
			 * Executes actions before the download content.
			 *
			 * @since 3.0.0
			 */
			do_action( 'neve_before_download_archive' );
			?>
			<div id="nv-edd-grid-container">

				<?php
					
					$query = new WP_Query(array(
						'post_type'      => 'product',
						'posts_per_page' => -1, 
						'tax_query'      => array(
							array(
								'taxonomy' => 'product_category',
								'field'    => 'slug',
								'terms'    => array('foot-brace', 'hand-brace'), 
								'operator' => 'IN',
							)
						)
					));
					
					if ($query->have_posts()) {
						while ($query->have_posts()) {
							$query->the_post();
							$description = get_post_meta(get_the_ID(), 'product-description', true);
							$product_image = get_the_post_thumbnail(get_the_ID(), 'medium');

							'<p>' . get_the_title() . '</p><br>';
						}
						wp_reset_postdata();
					} 
					else {
						get_template_part( 'template-parts/content', 'none' );
					}
				?>
			</div>
				<?php 
				/**
				 * Executes actions after the post content.
				 *
				 * @since 3.0.0
				 */
				do_action( 'neve_after_download_archive' );

				/**
				 * Download pagination
				 */
				neve_edd_download_nav();        
				?>
		</div>

</div>

<?php
get_footer();