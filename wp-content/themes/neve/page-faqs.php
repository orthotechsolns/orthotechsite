<?php
/**
 * Template Name: FAQs Page
 */

get_header(); ?>

<div class="faq-container">
    <h1 class="faq-title"><?php the_title(); ?></h1>
    <div class="faq-list">
        <?php
        // Query FAQs
        $faq_query = new WP_Query(array(
            'post_type' => 'faq',
            'posts_per_page' => -1,
            'orderby' => 'menu_order',
            'order' => 'ASC',
        ));

        if ($faq_query->have_posts()) :
            while ($faq_query->have_posts()) : $faq_query->the_post();
                // Get the related product using ACF relationship field
                $related_products = get_field('related_products'); // Using the ACF relationship field
                ?>
                <div class="faq-item">
                    <div class="faq-question">
                        <h3><?php the_title(); ?></h3>
                        <button class="toggle-answer">+</button>
                    </div>
                    <div class="faq-answer">
                        <?php the_content(); ?>
                        <?php if ($related_products) : ?>
                            <div class="faq-related-products">
                                <strong>Related Products:</strong>
                                <ul>
                                    <?php foreach ($related_products as $product) : ?>
                                        <li>
                                            <a href="<?php echo get_permalink($product->ID); ?>">
                                                <?php echo get_the_title($product->ID); ?>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php
            endwhile;
            wp_reset_postdata();
        else :
            echo '<p>No FAQs found.</p>';
        endif;
        ?>
    </div>
</div>

<style>
/* Styling for the FAQ accordion */
.faq-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
}

.faq-title {
    text-align: center;
    margin-bottom: 20px;
}

.faq-list {
    border: 1px solid #ddd;
    border-radius: 5px;
    overflow: hidden;
}

.faq-item {
    border-bottom: 1px solid #ddd;
}

.faq-item:last-child {
    border-bottom: none;
}

.faq-question {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
    background: #f9f9f9;
    cursor: pointer;
}

.faq-answer {
    display: none;
    padding: 15px;
    background: #fff;
}

.faq-related-products ul {
    margin: 10px 0 0;
    padding: 0;
    list-style: none;
}

.faq-related-products ul li {
    margin: 5px 0;
}

.toggle-answer {
    background: none;
    border: none;
    font-size: 18px;
    cursor: pointer;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const faqItems = document.querySelectorAll('.faq-item');

    faqItems.forEach(item => {
        const question = item.querySelector('.faq-question');
        const answer = item.querySelector('.faq-answer');
        const toggle = item.querySelector('.toggle-answer');

        question.addEventListener('click', () => {
            const isOpen = answer.style.display === 'block';
            answer.style.display = isOpen ? 'none' : 'block';
            toggle.textContent = isOpen ? '+' : '-';
        });
    });
});
</script>

<?php get_footer(); ?>
