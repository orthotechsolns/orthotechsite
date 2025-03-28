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
                $faq_question = get_field('faq_question');
                $faq_answer = get_field('faq_answer');
                $related_products = get_field('related_products');
                ?>
                <div class="faq-item">
                    <div class="faq-question">
                        <h3>Q: <?php echo $faq_question ? $faq_question : get_the_title(); ?></h3>
                        <button class="toggle-answer">+</button>
                    </div>
                    <div class="faq-answer">
                        <div class="answer-content">
                            <h3>A:</h3><?php echo $faq_answer ? $faq_answer : get_the_content(); ?>
                        </div>
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
    padding: 10px;
    background: #f9f9f9;
    cursor: pointer;
    border-left: 5px solid #333;
    transition: background 0.3s, border-color 0.3s;
}

.faq-question:hover {
    background: #e6e6e6;
    border-color: #0073aa;
}

.faq-question h3 {
    margin: 0;
    font-weight: bold;
}

.faq-answer {
    display: none;
    padding: 15px;
    background: #fff;
    border-left: 5px solid #0073aa;
    transition: background 0.3s, border-color 0.3s;
}

.faq-answer h3 {
    margin: 0 0 10px;
    font-weight: bold;
    color: #0073aa;
}

.faq-related-products {
    margin-top: 15px;
    padding: 10px;
    background: #f9f9f9;
    border: 1px solid #ddd;
    border-radius: 5px;
}

.faq-related-products strong {
    display: block;
    margin-bottom: 10px;
    font-weight: bold;
}

.faq-related-products ul {
    margin: 0;
    padding: 0;
    list-style: none;
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.faq-related-products ul li {
    margin: 0;
    padding: 5px 10px;
    background: #0073aa;
    color: #fff;
    border-radius: 3px;
    font-size: 14px;
}

.faq-related-products ul li a {
    color: #fff;
    text-decoration: none;
}

.faq-related-products ul li a:hover {
    text-decoration: underline;
}

.toggle-answer {
    background: lightskyblue;
    border: none;
    font-size: 18px;
    cursor: pointer;
    padding: 5px 10px;
    border-radius: 3px;
    transition: background 0.3s;
}

.toggle-answer:hover {
    background: #0073aa;
    color: #fff;
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
            // Close all other answers
            faqItems.forEach(otherItem => {
                if (otherItem !== item) {
                    otherItem.querySelector('.faq-answer').style.display = 'none';
                    otherItem.querySelector('.toggle-answer').textContent = '+';
                }
            });

            // Toggle the current answer
            const isOpen = answer.style.display === 'block';
            answer.style.display = isOpen ? 'none' : 'block';
            toggle.textContent = isOpen ? '+' : '-';
        });
    });
});
</script>

<?php get_footer(); ?>
