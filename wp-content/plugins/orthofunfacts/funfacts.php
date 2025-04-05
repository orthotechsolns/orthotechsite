<?php

/**
 * Plugin Name: Ortho Fun Facts Plugin
 * Description: Displays a random orthopedic fun fact.
 * Version: 1.1
 * Author: Gennesis Bethelmy
 */

// Register and enqueue the CSS file
function fun_fact_enqueue_styles()
{
    $plugin_url = plugin_dir_url(__FILE__);
    wp_enqueue_style(handle: 'fun-facts-style', src: $plugin_url . '/assets/css/fun-facts.css', deps: array(), ver: '1.0.0');
}
add_action('wp_enqueue_scripts', 'fun_fact_enqueue_styles');


function show_fun_fact()
{
    $facts = array( // list of fun ortho facts
        'The kneecap, or patella, is the largest sesamoid bone in the human body.',
        '3D printing allows orthopedic devices to be custom-made in just hours!',
        'The human foot has 26 bones, 33 joints, and over 100 muscles, tendons, and ligaments.',
        'Orthopedic braces date back to ancient Egypt!',
        'AI is now used to design orthopedic products that fit your exact body structure.',
        'Your knee is the biggest joint in your body!',
        'The foot has 26 bones — that is more than your whole spine!',
        '3D printers can make braces that fit you perfectly — like a glove for your bones.',
        'People have been using splints and braces since ancient Egypt!',
        'Your wrist is made up of 8 tiny bones that help you move and twist.',
        'Some braces today are made with smart materials that adapt to your body.',
        'AI helps doctors choose the best treatment based on your body type.',
        'Custom insoles are now made by scanning your feet, not guessing sizes.',
        'The knee cap (patella) is a floating bone — it does not attach to other bones directly!',
        'Orthopedic products help you heal faster because they fit you better.',
        'Robots can help doctors during surgery to make things more accurate.',
        'People who wear custom braces often feel more comfortable than with regular ones.',
        'Your feet carry your weight every day — that is why good support matters!',
        'Some braces are so light, you might forget you are even wearing them.',
        'With 3D printing, braces can now be made in just a few hours, not days.'
    );

    $random = $facts[array_rand($facts)];

    echo '<div class="fun-fact-container">
        <div class="fun-fact-content">
            <span class="fun-fact-icon">💡</span> 
            <span class="fun-fact-text"><strong>Fun Fact:</strong> ' . $random . '</span>
        </div>
    </div>';
}

add_action('neve_before_footer_hook', 'show_fun_fact');
