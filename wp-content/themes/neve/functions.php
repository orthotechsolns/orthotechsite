<?php
/**
 * Neve functions.php file
 *
 * Author:          Andrei Baicus <andrei@themeisle.com>
 * Created on:      17/08/2018
 *
 * @package Neve
 */

define( 'NEVE_VERSION', '4.0.1' );
define( 'NEVE_INC_DIR', trailingslashit( get_template_directory() ) . 'inc/' );
define( 'NEVE_ASSETS_URL', trailingslashit( get_template_directory_uri() ) . 'assets/' );
define( 'NEVE_MAIN_DIR', get_template_directory() . '/' );
define( 'NEVE_BASENAME', basename( NEVE_MAIN_DIR ) );
define( 'NEVE_PLUGINS_DIR', plugin_dir_path( dirname( __DIR__ ) ) . 'plugins/' );

if ( ! defined( 'NEVE_DEBUG' ) ) {
	define( 'NEVE_DEBUG', false );
}
define( 'NEVE_NEW_DYNAMIC_STYLE', true );
/**
 * Buffer which holds errors during theme inititalization.
 *
 * @var WP_Error $_neve_bootstrap_errors
 */
global $_neve_bootstrap_errors;

$_neve_bootstrap_errors = new WP_Error();

if ( version_compare( PHP_VERSION, '7.0' ) < 0 ) {
	$_neve_bootstrap_errors->add(
		'minimum_php_version',
		sprintf(
		/* translators: %s message to upgrade PHP to the latest version */
			__( "Hey, we've noticed that you're running an outdated version of PHP which is no longer supported. Make sure your site is fast and secure, by %1\$s. Neve's minimal requirement is PHP%2\$s.", 'neve' ),
			sprintf(
			/* translators: %s message to upgrade PHP to the latest version */
				'<a href="https://wordpress.org/support/upgrade-php/">%s</a>',
				__( 'upgrading PHP to the latest version', 'neve' )
			),
			'7.0'
		)
	);
}
/**
 * A list of files to check for existance before bootstraping.
 *
 * @var array Files to check for existance.
 */

$_files_to_check = defined( 'NEVE_IGNORE_SOURCE_CHECK' ) ? [] : [
	NEVE_MAIN_DIR . 'vendor/autoload.php',
	NEVE_MAIN_DIR . 'style-main-new.css',
	NEVE_MAIN_DIR . 'assets/js/build/modern/frontend.js',
	NEVE_MAIN_DIR . 'assets/apps/dashboard/build/dashboard.js',
	NEVE_MAIN_DIR . 'assets/apps/customizer-controls/build/controls.js',
];
foreach ( $_files_to_check as $_file_to_check ) {
	if ( ! is_file( $_file_to_check ) ) {
		$_neve_bootstrap_errors->add(
			'build_missing',
			sprintf(
			/* translators: %s: commands to run the theme */
				__( 'You appear to be running the Neve theme from source code. Please finish installation by running %s.', 'neve' ), // phpcs:ignore WordPress.Security.EscapeOutput
				'<code>composer install --no-dev &amp;&amp; yarn install --frozen-lockfile &amp;&amp; yarn run build</code>'
			)
		);
		break;
	}
}
/**
 * Adds notice bootstraping errors.
 *
 * @internal
 * @global WP_Error $_neve_bootstrap_errors
 */
function _neve_bootstrap_errors() {
	global $_neve_bootstrap_errors;
	printf( '<div class="notice notice-error"><p>%1$s</p></div>', $_neve_bootstrap_errors->get_error_message() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

if ( $_neve_bootstrap_errors->has_errors() ) {
	/**
	 * Add notice for PHP upgrade.
	 */
	add_filter( 'template_include', '__return_null', 99 );
	switch_theme( WP_DEFAULT_THEME );
	unset( $_GET['activated'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	add_action( 'admin_notices', '_neve_bootstrap_errors' );

	return;
}

/**
 * Themeisle SDK filter.
 *
 * @param array $products products array.
 *
 * @return array
 */
function neve_filter_sdk( $products ) {
	$products[] = get_template_directory() . '/style.css';

	return $products;
}

add_filter( 'themeisle_sdk_products', 'neve_filter_sdk' );
add_filter(
	'themeisle_sdk_compatibilities/' . NEVE_BASENAME,
	function ( $compatibilities ) {

		$compatibilities['NevePro'] = [
			'basefile'  => defined( 'NEVE_PRO_BASEFILE' ) ? NEVE_PRO_BASEFILE : '',
			'required'  => '2.9',
			'tested_up' => '3.0',
		];

		return $compatibilities;
	}
);
require_once 'globals/migrations.php';
require_once 'globals/utilities.php';
require_once 'globals/hooks.php';
require_once 'globals/sanitize-functions.php';
require_once get_template_directory() . '/start.php';

/**
 * If the new widget editor is available,
 * we re-assign the widgets to hfg_footer
 */
if ( neve_is_new_widget_editor() ) {
	/**
	 * Re-assign the widgets to hfg_footer
	 *
	 * @param array  $section_args The section arguments.
	 * @param string $section_id The section ID.
	 * @param string $sidebar_id The sidebar ID.
	 *
	 * @return mixed
	 */
	function neve_customizer_custom_widget_areas( $section_args, $section_id, $sidebar_id ) {
		if ( strpos( $section_id, 'widgets-footer' ) ) {
			$section_args['panel'] = 'hfg_footer';
		}

		return $section_args;
	}

	add_filter( 'customizer_widgets_section_args', 'neve_customizer_custom_widget_areas', 10, 3 );
}

require_once get_template_directory() . '/header-footer-grid/loader.php';

add_filter(
	'neve_welcome_metadata',
	function() {
		return [
			'is_enabled' => ! defined( 'NEVE_PRO_VERSION' ),
			'pro_name'   => 'Neve Pro Addon',
			'logo'       => get_template_directory_uri() . '/assets/img/dashboard/logo.svg',
			'cta_link'   => tsdk_translate_link( tsdk_utmify( 'https://themeisle.com/themes/neve/upgrade/?discount=LOYALUSER582&dvalue=50', 'neve-welcome', 'notice' ), 'query' ),
		];
	}
);

add_filter( 'themeisle_sdk_enable_telemetry', '__return_true' );

/**
 * Register custom post types and taxonomies
 */
// function ortho_post_types() {
//     register_post_type('product', array(
//         'capability_type' => 'product',
//         'map_meta_cap' => true,
//         'public' => true,
//         'labels' => array(
//             'name' => 'Products',
//             'add_new_item' => 'Add New Product',
//             'edit_item' => 'Edit Product',
//             'all_items' => 'All Products',
//             'singular_name' => 'Product'
//         ),
//         'has_archive' => true,
//         'menu_icon' => 'dashicons-products',
//         'supports' => array('title', 'editor', 'custom-fields', 'thumbnail'),
//         'rewrite' => array('slug' => 'product'),
//     ));
    
//     // Flush rewrite rules only when needed
//     if (get_option('ortho_flush_rewrite_rules')) {
//         flush_rewrite_rules();
//         delete_option('ortho_flush_rewrite_rules');
//     }
// }
// add_action('init', 'ortho_post_types');

// Set flag to flush rewrite rules on theme activation
function ortho_activate() {
    add_option('ortho_flush_rewrite_rules', true);
}
register_activation_hook(__FILE__, 'ortho_activate');

/**
 * Custom template routing for product categories
 */
function ortho_category_template($template) {
    // For the categories we want to use our unified template
    if (is_tax('product_category', array('foot-braces', 'hand-braces', 'knee-braces'))) {
        $new_template = locate_template(array('taxonomy-products.php'));
        if ('' != $new_template) {
            return $new_template;
        }
    }
    
    return $template;
}
add_filter('template_include', 'ortho_category_template');

// Remove the old duplicate filter that's causing issues
// add_filter('template_include', function ($template) {
//     if (is_tax('product_category', 'hand-braces')) {
//         $new_template = locate_template(array('archive-hand-braces.php'));
//         if ($new_template) {
//             return $new_template;
//         }
//     }
//     if (is_tax('product_category', 'knee-braces')) {
//         $new_template = locate_template(array('archive-knee-braces.php'));
//         if ($new_template) {
//             return $new_template;
//         }
//     }
//     return $template;
// });

add_action('template_redirect', function() {
    global $template;
    if (is_user_logged_in() && current_user_can('administrator')) {
        echo '<!-- Current template: ' . $template . ' -->';
    }
});