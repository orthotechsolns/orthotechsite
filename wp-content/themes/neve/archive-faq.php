<?php

$query = new WP_Query(array(
    'post_type'      => 'faq',
    'posts_per_page' => -1,
    'orderby'        => 'date',
    'order'          => 'DESC'
));

if ($query->have_posts()) {
    echo '<div class="faq-list">';
    while ($query->have_posts()) {
        $query->the_post();
         '<p>' . get_the_title() . '<p>';
         '<p>' . get_the_content() . '<p>';
    }
    wp_reset_postdata();
} 
?>