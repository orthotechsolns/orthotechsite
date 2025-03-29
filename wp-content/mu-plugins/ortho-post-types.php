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
        'has_archive' => true,
        'menu_icon' => 'dashicons-products',
        'supports' => array('title', 'excerpt', 'custom-fields'),
        'rewrite' => array('slug' => 'products'),
    ));

    register_post_type('photo', array(
        'capability_type' => 'photo',
        'map_meta_cap' => true,
        'public' => true,
        'labels' => array(
            'name' => 'Photos',
            'add_new_item' => 'Add New Photo',
            'edit_item' => 'Edit Photo',
            'all_items' => 'All Photos',
            'singular_name' => 'Photo'
        ),
        'has_archive' => true,
        'menu_icon' => 'dashicons-camera',
        'supports' => array('custom-fields'),
        'rewrite' => array('slug' => 'photos'),
    ));

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
}
add_action('init', 'ortho_post_types');
?>