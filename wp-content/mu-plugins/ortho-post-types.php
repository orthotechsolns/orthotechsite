<?php

function ortho_post_types()
{
    register_post_type('product', array(
        'capability_type' => 'product',
        'map_meta_cap' => true,
        'public' => true,
        'labels' => array(
            'name' => 'Products',
            'add_new_item' => 'Add New Product',
            'edit_item' => 'Edit Product',
            'all_items' => 'All Products',
            'singular_name' => 'Product'
        ),
        'has_archive' => 'products', // Use 'products' as the archive URL
        'menu_icon' => 'dashicons-products',
        'supports' => array('title', 'excerpt', 'custom-fields', 'thumbnail'),
        'rewrite' => array('slug' => 'product'), // Keep single posts as 'product'
    ));
    
    // Register product category taxonomy
    register_taxonomy('product_category', 'product', array(
        'label' => 'Product Categories',
        'rewrite' => array(
            'slug' => 'products', 
            'with_front' => false,
            'hierarchical' => true
        ),
        'hierarchical' => true,
        'show_admin_column' => true,
    ));

    // NOTE Deprecated - no longer used for site design
    // register_post_type('photo', array(
    //     'capability_type' => 'photo',
    //     'map_meta_cap' => true,
    //     'public' => true,
    //     'labels' => array(
    //         'name' => 'Photos',
    //         'add_new_item' => 'Add New Photo',
    //         'edit_item' => 'Edit Photo',
    //         'all_items' => 'All Photos',
    //         'singular_name' => 'Photo'
    //     ),
    //     'has_archive' => true,
    //     'menu_icon' => 'dashicons-camera',
    //     'supports' => array('custom-fields'),
    //     'rewrite' => array('slug' => 'photos'),
    // ));


    register_post_type('faq', array(
        'capability_type' => 'faq',
        'map_meta_cap' => true,
        'public' => true,
        'labels' => array(
            'name' => 'FAQs',
            'add_new_item' => 'Add New FAQ',
            'edit_item' => 'Edit FAQ',
            'all_items' => 'All FAQs',
            'singular_name' => 'FAQ'
        ),
		'has_archive' => true, //(Did not create a single-faq.php, as all FAQs can be displayed on one page)
		'menu_icon' => 'dashicons-editor-help',
        'supports' => array('title', 'custom-fields'),
        'rewrite' => array('slug' => 'photos'),
    ));

    register_post_type('testimonial', array(
        'capability_type' => 'testimonial',
        'map_meta_cap' => true,
        'public' => true,
        'labels' => array(
            'name' => 'Testimonials',
            'add_new_item' => 'Add New Testimonial',
            'edit_item' => 'Edit Testimonial',
            'all_items' => 'All Testimonials',
            'singular_name' => 'Testimonial'
        ),
        'has_archive' => true,
        'menu_icon' => 'dashicons-format-quote',
        'supports' => array('title', 'custom-fields'),
        'rewrite' => array('slug' => 'testimonials'),
    ));

    register_taxonomy('testimonial_category', 'testimonial', array(
        'label' => 'Testimonial Categories',
        'rewrite' => array(
            'slug' => 'testimonials', 
            'with_front' => false,
            'hierarchical' => true
        ),
        'hierarchical' => true,
        'show_admin_column' => true,
    ));
}
add_action('init', 'ortho_post_types');

/**
 * Add custom rewrite rules to handle product categories and single products
 */
function ortho_add_custom_rewrite_rules() {
    // Add rewrite rule for product categories
    add_rewrite_rule(
        'products/category/([^/]+)/?$',
        'index.php?product_category=$matches[1]',
        'top'
    );
    
    // Add rewrite rule for product category pagination
    add_rewrite_rule(
        'products/category/([^/]+)/page/([0-9]{1,})/?$',
        'index.php?product_category=$matches[1]&paged=$matches[2]',
        'top'
    );
    
    // Ensure single product URLs work correctly
    add_rewrite_rule(
        'product/([^/]+)/?$',
        'index.php?product=$matches[1]',
        'top'
    );
    
    // Add rewrite rule for testimonial categories
    add_rewrite_rule(
        'testimonials/([^/]+)/?$',
        'index.php?testimonial_category=$matches[1]',
        'top'
    );
    
    // Add rewrite rule for testimonial category pagination
    add_rewrite_rule(
        'testimonials/([^/]+)/page/([0-9]{1,})/?$',
        'index.php?testimonial_category=$matches[1]&paged=$matches[2]',
        'top'
    );
}
add_action('init', 'ortho_add_custom_rewrite_rules');

/**
 * Flush rewrite rules when plugin is activated
 * Only run this once when needed, not on every page load
 */
function ortho_flush_rewrite_rules() {
    // Check if we need to flush
    if (get_option('ortho_flush_needed') == true) {
        flush_rewrite_rules();
        update_option('ortho_flush_needed', false);
    }
}
add_action('init', 'ortho_flush_rewrite_rules');

// Set flush flag when plugin activated
register_activation_hook(__FILE__, 'ortho_set_flush_flag');
function ortho_set_flush_flag() {
    update_option('ortho_flush_needed', true);
}

// Force flush rewrite rules once to fix product permalinks
function ortho_force_flush_rewrite_rules() {
    update_option('ortho_flush_needed', true);
}
// Force flush rewrite rules
add_action('init', 'ortho_force_flush_rewrite_rules', 20);