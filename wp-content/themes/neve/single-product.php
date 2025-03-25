<?php
/**
 * Author:          Andrei Baicus <andrei@themeisle.com>
 * Created on:      28/08/2018
 *
 * @package Neve
 */

$container_class = apply_filters( 'neve_container_class_filter', 'container', 'single-post' );

get_header();

?>
	<div class="<?php echo esc_attr( $container_class ); ?> single-post-container">
		<div class="row">
			<?php do_action( 'neve_do_sidebar', 'single-post', 'left' ); ?>
			<article id="post-<?php echo esc_attr( get_the_ID() ); ?>"
					class="<?php echo esc_attr( join( ' ', get_post_class( 'nv-single-post-wrap col' ) ) ); ?>">

				<?php
				/**
				 * Executes actions before the post content.
				 *
				 * @since 2.3.8
				 */
				do_action( 'neve_before_post_content' );


 			$theParent = wp_get_post_parent_ID(get_the_ID());
			if($theParent){ ?>
				<div class="metabox metabox--position-up metabox--with-home-link">
					<p><a class="metabox__blog-home-link" style="background-color: #0D3B66; color: #FFF; border-radius: 3px; padding: 10px 15px; display: inline-block; font-weight: normal;" href="<?php echo get_permalink($theParent); ?>">
 					<i class="fa fa-home" aria-hidden="true"></i> Back to <?php echo get_the_title($theParent); ?></a>
 					<span class="metabox__main" style="background-color:rgb(44, 44, 44); color: #FFF; border-radius: 3px; padding: 10px 15px; display: inline-block; margin-bottom: 30px; box-shadow: 2px 2px 1px rgba(0, 0, 0, .07);">
					<?php echo the_title(); ?> </span></p>
 				</div>
 					<?php } ?>

			<?php
				if ( have_posts() ) {
					while ( have_posts() ) {
						the_post();
						
						echo '<h1>' . get_the_title() . '</h1>';

						if ( has_post_thumbnail() ) {
							the_post_thumbnail( 'full' );
						}

						the_content();
					}
				} else {
					get_template_part( 'template-parts/content', 'none' );
				}

				remove_action( 'neve_single_post_author', 'neve_post_author', 10 );

				/**
				 * Executes actions after the post content.
				 *
				 * @since 2.3.8
				 */
				do_action( 'neve_after_post_content' );
				?>
			</article>
			<?php do_action( 'neve_do_sidebar', 'single-post', 'right' ); ?>
		</div>
	</div>
<?php
get_footer();
