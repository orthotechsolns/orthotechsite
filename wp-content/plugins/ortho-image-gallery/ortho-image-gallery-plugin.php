<?php

/*
Plugin Name: My Photo Gallery
Description: A simple photo gallery plugin for OrthoTech Solutions which gives insight into the curation of our products.
Version: 1.0
Author: Isabell Munroe
Author URI: https://example.com
*/

function ortho_gallery_enqueue_styles()
{
    wp_enqueue_style(
        'ortho-gallery-styles',
        plugins_url('ortho-gallery-styling.css', __FILE__),
    );
}
add_action('wp_enqueue_scripts', 'ortho_gallery_enqueue_styles');

function ortho_photo_gallery()
{
    $images = [
        wp_get_attachment_url(1378),
        wp_get_attachment_url(1369),
        wp_get_attachment_url(1373),
        wp_get_attachment_url(1377),
        wp_get_attachment_url(1425),
        wp_get_attachment_url(1424),
        wp_get_attachment_url(1423),
        wp_get_attachment_url(1422),
        wp_get_attachment_url(1421),
        wp_get_attachment_url(1420),
        wp_get_attachment_url(1426),
        wp_get_attachment_url(1418)
    ];

    $styling = '<div class="ortho-gallery">';
    foreach ($images as $img) {
        $styling .= '<div class="ortho-gallery-item">
            <img class="ortho-gallery-image" src="' . esc_url($img) . '" alt="Gallery Image" />
        </div>';
    }
    $styling .= '</div>';

    return $styling;
}
add_shortcode('ortho_photo_gallery', 'ortho_photo_gallery');

?>