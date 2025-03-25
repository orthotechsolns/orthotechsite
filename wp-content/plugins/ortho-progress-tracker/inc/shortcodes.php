<?php
/**
 * Shortcodes for the Progress Tracker
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main shortcode for displaying a progress tracker
 */
function opt_progress_shortcode($atts) {
    // Parse attributes
    $atts = shortcode_atts(array(
        'program_id' => 0,
        'show_charts' => 'yes',
        'show_notes' => 'yes',
        'show_videos' => 'yes',
        'theme' => 'default',
    ), $atts, 'ortho_progress');
    
    // Check if user is logged in
    if (!is_user_logged_in()) {
        return opt_get_login_message();
    }
    
    // Get user ID
    $user_id = get_current_user_id();
    
    // Get program ID
    $program_id = absint($atts['program_id']);
    
    // If no program ID provided, show user's programs
    if (!$program_id) {
        return opt_render_user_programs($user_id);
    }
    
    // Get program data
    $program = get_post($program_id);
    
    // Check if program exists
    if (!$program || $program->post_type !== 'recovery_program' || $program->post_status !== 'publish') {
        return '<div class="opt-error">' . __('Program not found.', 'ortho-progress-tracker') . '</div>';
    }
    
    // Get user progress for this program
    $progress_data = opt_get_user_program_progress($user_id, $program_id);
    
    // Ensure the user is enrolled in this program
    if (empty($progress_data['summary']['user_id'])) {
        // Check if auto-enrollment is possible
        $product_id = get_post_meta($program_id, '_opt_product_id', true);
        $can_auto_enroll = true;
        
        // If there's an associated product, check if user has purchased it
        if ($product_id && class_exists('WooCommerce')) {
            $can_auto_enroll = wc_customer_bought_product($user_id, $user_id, $product_id);
        }
        
        if ($can_auto_enroll) {
            // Auto-enroll user
            opt_enroll_user_in_program($user_id, $program_id);
            // Get progress data again
            $progress_data = opt_get_user_program_progress($user_id, $program_id);
        } else {
            // Show enrollment message
            return opt_render_enrollment_message($program_id, $program);
        }
    }
    
    // Start output buffer
    ob_start();
    
    // Get additional program data
    $duration = get_post_meta($program_id, '_opt_duration', true);
    $duration_unit = get_post_meta($program_id, '_opt_duration_unit', true) ?: 'weeks';
    $expected_improvement = get_post_meta($program_id, '_opt_expected_improvement', true);
    
    // Get theme class
    $theme_class = 'opt-theme-' . sanitize_html_class($atts['theme']);
    
    // Output HTML
    ?>
    <div class="opt-progress-tracker <?php echo esc_attr($theme_class); ?>" data-program-id="<?php echo esc_attr($program_id); ?>">
        <div class="opt-program-header">
            <div class="opt-program-info">
                <h2 class="opt-program-title"><?php echo esc_html($program->post_title); ?></h2>
                
                <?php if (!empty($duration)) : ?>
                <div class="opt-program-duration">
                    <span class="opt-label"><?php _e('Duration:', 'ortho-progress-tracker'); ?></span>
                    <span class="opt-value"><?php echo esc_html($duration . ' ' . $duration_unit); ?></span>
                </div>
                <?php endif; ?>
                
                <div class="opt-program-progress">
                    <div class="opt-progress-bar">
                        <div class="opt-progress-fill" style="width: <?php echo esc_attr($progress_data['summary']['completion_percentage']); ?>%"></div>
                    </div>
                    <div class="opt-progress-percentage"><?php echo esc_html($progress_data['summary']['completion_percentage']); ?>% <?php _e('Complete', 'ortho-progress-tracker'); ?></div>
                </div>
                
                <?php if (!empty($expected_improvement)) : ?>
                <div class="opt-expected-improvement">
                    <div class="opt-label"><?php _e('Expected Improvements:', 'ortho-progress-tracker'); ?></div>
                    <div class="opt-value"><?php echo esc_html($expected_improvement); ?></div>
                </div>
                <?php endif; ?>
            </div>
            
            <?php if (has_post_thumbnail($program_id)) : ?>
            <div class="opt-program-image">
                <?php echo get_the_post_thumbnail($program_id, 'medium'); ?>
            </div>
            <?php endif; ?>
        </div>
        
        <?php if ($atts['show_charts'] === 'yes' && !empty($progress_data['pain_trend'])) : ?>
        <div class="opt-charts-section">
            <h3><?php _e('Pain Level Trends', 'ortho-progress-tracker'); ?></h3>
            <div class="opt-chart-container">
                <canvas id="opt-pain-chart-<?php echo esc_attr($program_id); ?>" width="400" height="200"></canvas>
            </div>
            <script>
                jQuery(document).ready(function($) {
                    var ctx = document.getElementById('opt-pain-chart-<?php echo esc_attr($program_id); ?>').getContext('2d');
                    var painData = <?php echo json_encode($progress_data['pain_trend']); ?>;
                    
                    var labels = painData.map(function(item) { return item.date; });
                    var data = painData.map(function(item) { return item.avg_pain; });
                    
                    var painChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: '<?php _e('Average Pain Level', 'ortho-progress-tracker'); ?>',
                                data: data,
                                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                                borderColor: 'rgba(255, 99, 132, 1)',
                                borderWidth: 2,
                                tension: 0.1
                            }]
                        },
                        options: {
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    max: 10,
                                    title: {
                                        display: true,
                                        text: '<?php _e('Pain Level (0-10)', 'ortho-progress-tracker'); ?>'
                                    }
                                },
                                x: {
                                    title: {
                                        display: true,
                                        text: '<?php _e('Date', 'ortho-progress-tracker'); ?>'
                                    }
                                }
                            }
                        }
                    });
                });
            </script>
        </div>
        <?php endif; ?>
        
        <div class="opt-steps-section">
            <h3><?php _e('Program Steps', 'ortho-progress-tracker'); ?></h3>
            
            <?php if (empty($progress_data['steps'])) : ?>
                <p class="opt-no-steps"><?php _e('No steps have been added to this program yet.', 'ortho-progress-tracker'); ?></p>
            <?php else : ?>
                <div class="opt-steps-list">
                    <?php foreach ($progress_data['steps'] as $step) : ?>
                        <div class="opt-step <?php echo ($step['completed'] ? 'opt-step-completed' : ''); ?>" data-step-id="<?php echo esc_attr($step['step_id']); ?>">
                            <div class="opt-step-header">
                                <div class="opt-step-status">
                                    <label class="opt-checkbox-label">
                                        <input type="checkbox" class="opt-step-checkbox" <?php checked($step['completed'], true); ?>>
                                        <span class="opt-checkmark"></span>
                                    </label>
                                </div>
                                <div class="opt-step-info">
                                    <h4 class="opt-step-title">
                                        <?php echo esc_html($step['step_number'] . '. ' . $step['step_title']); ?>
                                    </h4>
                                    <?php if (!empty($step['duration'])) : ?>
                                        <span class="opt-step-duration">
                                            <?php echo esc_html($step['duration'] . ' ' . ucfirst($step['duration_unit'])); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div class="opt-step-toggle">
                                    <button type="button" class="opt-toggle-details"><?php _e('Details', 'ortho-progress-tracker'); ?></button>
                                </div>
                            </div>
                            
                            <div class="opt-step-details" style="display: none;">
                                <div class="opt-step-content">
                                    <?php echo apply_filters('the_content', get_post_field('post_content', $step['step_id'])); ?>
                                </div>
                                
                                <?php if ($atts['show_videos'] === 'yes' && !empty($step['video_url'])) : ?>
                                <div class="opt-step-video">
                                    <h5><?php _e('Instructional Video', 'ortho-progress-tracker'); ?></h5>
                                    <div class="opt-video-wrapper">
                                        <?php 
                                        // Check video type and embed accordingly
                                        if (strpos($step['video_url'], 'youtube.com') !== false || strpos($step['video_url'], 'youtu.be') !== false) {
                                            echo wp_oembed_get($step['video_url']);
                                        } elseif (strpos($step['video_url'], 'vimeo.com') !== false) {
                                            echo wp_oembed_get($step['video_url']);
                                        } else {
                                            echo do_shortcode('[video src="' . esc_url($step['video_url']) . '"]');
                                        }
                                        ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($atts['show_notes'] === 'yes') : ?>
                                <div class="opt-step-tracking">
                                    <h5><?php _e('Track Your Progress', 'ortho-progress-tracker'); ?></h5>
                                    
                                    <div class="opt-pain-level">
                                        <label for="opt-pain-input-<?php echo esc_attr($step['step_id']); ?>"><?php _e('Pain Level (0-10):', 'ortho-progress-tracker'); ?></label>
                                        <input type="range" id="opt-pain-input-<?php echo esc_attr($step['step_id']); ?>" class="opt-pain-input" min="0" max="10" value="<?php echo esc_attr($step['pain_level']); ?>">
                                        <span class="opt-pain-value"><?php echo esc_html($step['pain_level']); ?></span>
                                    </div>
                                    
                                    <div class="opt-notes">
                                        <label for="opt-notes-input-<?php echo esc_attr($step['step_id']); ?>"><?php _e('Notes:', 'ortho-progress-tracker'); ?></label>
                                        <textarea id="opt-notes-input-<?php echo esc_attr($step['step_id']); ?>" class="opt-notes-input" placeholder="<?php esc_attr_e('Add notes about your progress...', 'ortho-progress-tracker'); ?>"><?php echo esc_textarea($step['notes']); ?></textarea>
                                    </div>
                                    
                                    <button type="button" class="opt-save-progress"><?php _e('Save Progress', 'ortho-progress-tracker'); ?></button>
                                    <div class="opt-save-message"></div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php
    
    // Return the output
    return ob_get_clean();
}
add_shortcode('ortho_progress', 'opt_progress_shortcode');

/**
 * Program list shortcode
 */
function opt_program_list_shortcode($atts) {
    // Parse attributes
    $atts = shortcode_atts(array(
        'category' => '',
        'difficulty' => '',
        'limit' => -1,
        'columns' => 3,
        'show_enrolled' => 'no',
        'show_enroll' => 'yes',
    ), $atts, 'ortho_program_list');
    
    // Check if user is logged in for user-specific functionality
    $user_id = is_user_logged_in() ? get_current_user_id() : 0;
    
    // Get user enrolled programs if needed
    $user_program_ids = array();
    if ($user_id && $atts['show_enrolled'] === 'no') {
        $user_programs = opt_get_user_programs($user_id);
        $user_program_ids = array_column($user_programs, 'program_id');
    }
    
    // Query args
    $args = array(
        'post_type' => 'recovery_program',
        'post_status' => 'publish',
        'posts_per_page' => $atts['limit'],
    );
    
    // Add taxonomy queries if specified
    $tax_query = array();
    
    if (!empty($atts['category'])) {
        $tax_query[] = array(
            'taxonomy' => 'program_category',
            'field' => 'slug',
            'terms' => explode(',', $atts['category']),
        );
    }
    
    if (!empty($atts['difficulty'])) {
        $tax_query[] = array(
            'taxonomy' => 'program_difficulty',
            'field' => 'slug',
            'terms' => explode(',', $atts['difficulty']),
        );
    }
    
    if (!empty($tax_query)) {
        $args['tax_query'] = $tax_query;
    }
    
    // Exclude user enrolled programs if needed
    if (!empty($user_program_ids) && $atts['show_enrolled'] === 'no') {
        $args['post__not_in'] = $user_program_ids;
    }
    
    // Get programs
    $programs = get_posts($args);
    
    if (empty($programs)) {
        return '<p class="opt-no-programs">' . __('No programs found.', 'ortho-progress-tracker') . '</p>';
    }
    
    // Start output buffer
    ob_start();
    
    // Calculate column class
    $columns = max(1, min(4, absint($atts['columns'])));
    $column_class = 'opt-col-' . $columns;
    
    // Output HTML
    ?>
    <div class="opt-program-grid <?php echo esc_attr($column_class); ?>">
        <?php foreach ($programs as $program) : 
            // Get program metadata
            $duration = get_post_meta($program->ID, '_opt_duration', true);
            $duration_unit = get_post_meta($program->ID, '_opt_duration_unit', true) ?: 'weeks';
            $product_id = get_post_meta($program->ID, '_opt_product_id', true);
            
            // Get taxonomies
            $categories = wp_get_post_terms($program->ID, 'program_category', array('fields' => 'names'));
            $difficulty = wp_get_post_terms($program->ID, 'program_difficulty', array('fields' => 'names'));
            
            // Check if user is enrolled
            $is_enrolled = in_array($program->ID, $user_program_ids);
            
            // Check if product is required and purchased
            $show_enroll_button = $atts['show_enroll'] === 'yes' && $user_id;
            $requires_purchase = false;
            $has_purchased = false;
            
            if ($product_id && class_exists('WooCommerce')) {
                $requires_purchase = true;
                $has_purchased = $user_id ? wc_customer_bought_product($user_id, $user_id, $product_id) : false;
                $product = wc_get_product($product_id);
            }
        ?>
            <div class="opt-program-card">
                <?php if (has_post_thumbnail($program->ID)) : ?>
                    <div class="opt-program-image">
                        <a href="<?php echo esc_url(get_permalink($program->ID)); ?>">
                            <?php echo get_the_post_thumbnail($program->ID, 'medium'); ?>
                        </a>
                    </div>
                <?php endif; ?>
                
                <div class="opt-program-content">
                    <h3 class="opt-program-title">
                        <a href="<?php echo esc_url(get_permalink($program->ID)); ?>">
                            <?php echo esc_html($program->post_title); ?>
                        </a>
                    </h3>
                    
                    <?php if (!empty($categories) || !empty($difficulty)) : ?>
                        <div class="opt-program-meta">
                            <?php if (!empty($categories)) : ?>
                                <span class="opt-program-category">
                                    <?php echo esc_html(implode(', ', $categories)); ?>
                                </span>
                            <?php endif; ?>
                            
                            <?php if (!empty($difficulty)) : ?>
                                <span class="opt-program-difficulty">
                                    <?php _e('Difficulty:', 'ortho-progress-tracker'); ?> <?php echo esc_html(reset($difficulty)); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($duration)) : ?>
                        <div class="opt-program-duration">
                            <?php _e('Duration:', 'ortho-progress-tracker'); ?> <?php echo esc_html($duration . ' ' . $duration_unit); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($program->post_excerpt)) : ?>
                        <div class="opt-program-excerpt">
                            <?php echo wp_kses_post($program->post_excerpt); ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="opt-program-actions">
                        <a href="<?php echo esc_url(get_permalink($program->ID)); ?>" class="opt-view-program"><?php _e('View Details', 'ortho-progress-tracker'); ?></a>
                        
                        <?php if ($show_enroll_button) : ?>
                            <?php if ($is_enrolled) : ?>
                                <a href="<?php echo esc_url(add_query_arg('view', 'program', get_permalink($program->ID))); ?>" class="opt-view-progress"><?php _e('View Progress', 'ortho-progress-tracker'); ?></a>
                            <?php elseif ($requires_purchase && !$has_purchased && $product) : ?>
                                <a href="<?php echo esc_url($product->get_permalink()); ?>" class="opt-purchase-program"><?php _e('Purchase Required', 'ortho-progress-tracker'); ?></a>
                            <?php else : ?>
                                <button type="button" class="opt-enroll-button" data-program-id="<?php echo esc_attr($program->ID); ?>"><?php _e('Enroll Now', 'ortho-progress-tracker'); ?></button>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <?php if ($user_id && $atts['show_enroll'] === 'yes') : ?>
        <script>
            jQuery(document).ready(function($) {
                $('.opt-enroll-button').on('click', function() {
                    var $button = $(this);
                    var programId = $button.data('program-id');
                    
                    $button.prop('disabled', true).text('<?php _e('Enrolling...', 'ortho-progress-tracker'); ?>');
                    
                    $.ajax({
                        url: opt_data.ajax_url,
                        type: 'POST',
                        data: {
                            action: 'opt_enroll_in_program',
                            program_id: programId,
                            nonce: opt_data.nonce
                        },
                        success: function(response) {
                            if (response.success) {
                                $button.replaceWith('<a href="<?php echo esc_url(home_url()); ?>?p=' + programId + '&view=program" class="opt-view-progress"><?php _e('View Progress', 'ortho-progress-tracker'); ?></a>');
                            } else {
                                $button.prop('disabled', false).text('<?php _e('Enroll Now', 'ortho-progress-tracker'); ?>');
                                alert('<?php _e('Error: ', 'ortho-progress-tracker'); ?>' + response.data);
                            }
                        },
                        error: function() {
                            $button.prop('disabled', false).text('<?php _e('Enroll Now', 'ortho-progress-tracker'); ?>');
                            alert('<?php _e('Error enrolling in program. Please try again.', 'ortho-progress-tracker'); ?>');
                        }
                    });
                });
            });
        </script>
    <?php endif; ?>
    <?php
    
    return ob_get_clean();
}
add_shortcode('ortho_program_list', 'opt_program_list_shortcode');

/**
 * Progress summary shortcode
 */
function opt_progress_summary_shortcode($atts) {
    // Parse attributes
    $atts = shortcode_atts(array(
        'limit' => 3,
        'show_enroll_button' => 'yes',
    ), $atts, 'ortho_progress_summary');
    
    // Check if user is logged in
    if (!is_user_logged_in()) {
        return opt_get_login_message();
    }
    
    // Get user ID
    $user_id = get_current_user_id();
    
    // Get user programs
    $user_programs = opt_get_user_programs($user_id);
    
    // Start output buffer
    ob_start();
    
    if (empty($user_programs)) {
        ?>
        <div class="opt-no-programs-message">
            <p><?php _e('You are not currently enrolled in any recovery programs.', 'ortho-progress-tracker'); ?></p>
            
            <?php if ($atts['show_enroll_button'] === 'yes') : ?>
                <p>
                    <a href="<?php echo esc_url(get_post_type_archive_link('recovery_program')); ?>" class="opt-browse-programs">
                        <?php _e('Browse Available Programs', 'ortho-progress-tracker'); ?>
                    </a>
                </p>
            <?php endif; ?>
        </div>
        <?php
    } else {
        // Limit the number of programs to display
        $user_programs = array_slice($user_programs, 0, absint($atts['limit']));
        ?>
        <div class="opt-progress-summary">
            <h3 class="opt-summary-title"><?php _e('Your Recovery Programs', 'ortho-progress-tracker'); ?></h3>
            
            <div class="opt-program-cards">
                <?php foreach ($user_programs as $program) : ?>
                    <div class="opt-program-card">
                        <div class="opt-card-header" <?php echo !empty($program['image']) ? 'style="background-image: url(' . esc_url($program['image']) . ');"' : ''; ?>>
                            <div class="opt-card-overlay">
                                <h4 class="opt-card-title"><?php echo esc_html($program['program_title']); ?></h4>
                                
                                <div class="opt-progress-indicator">
                                    <div class="opt-progress-circle" data-progress="<?php echo esc_attr($program['completion_percentage']); ?>">
                                        <svg viewBox="0 0 36 36" class="opt-circular-chart">
                                            <path class="opt-circle-bg" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"></path>
                                            <path class="opt-circle" stroke-dasharray="<?php echo esc_attr($program['completion_percentage']); ?>, 100" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"></path>
                                            <text x="18" y="20.35" class="opt-percentage"><?php echo esc_html($program['completion_percentage']); ?>%</text>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="opt-card-content">
                            <?php if (!empty($program['duration'])) : ?>
                                <div class="opt-program-duration">
                                    <span class="opt-label"><?php _e('Duration:', 'ortho-progress-tracker'); ?></span>
                                    <span class="opt-value"><?php echo esc_html($program['duration']); ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <div class="opt-program-status">
                                <span class="opt-label"><?php _e('Status:', 'ortho-progress-tracker'); ?></span>
                                <span class="opt-value opt-status-<?php echo esc_attr($program['status']); ?>">
                                    <?php 
                                    if ($program['status'] == 'completed') {
                                        _e('Completed', 'ortho-progress-tracker');
                                    } else {
                                        _e('In Progress', 'ortho-progress-tracker');
                                    }
                                    ?>
                                </span>
                            </div>
                            
                            <div class="opt-last-activity">
                                <span class="opt-label"><?php _e('Last Activity:', 'ortho-progress-tracker'); ?></span>
                                <span class="opt-value">
                                    <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($program['last_activity']))); ?>
                                </span>
                            </div>
                            
                            <div class="opt-card-actions">
                                <a href="<?php echo esc_url(add_query_arg('view', 'program', get_permalink($program['program_id']))); ?>" class="opt-view-details">
                                    <?php _e('Continue Program', 'ortho-progress-tracker'); ?>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <?php if (count($user_programs) > absint($atts['limit'])) : ?>
                <div class="opt-view-all">
                    <a href="<?php echo esc_url(wc_get_account_endpoint_url('recovery-progress')); ?>" class="opt-view-all-link">
                        <?php _e('View All Programs', 'ortho-progress-tracker'); ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }
    
    return ob_get_clean();
}
add_shortcode('ortho_progress_summary', 'opt_progress_summary_shortcode');

/**
 * Helper function to get login message
 */
function opt_get_login_message() {
    ob_start();
    ?>
    <div class="opt-login-required">
        <p><?php _e('You need to be logged in to view your recovery programs and track your progress.', 'ortho-progress-tracker'); ?></p>
        <p>
            <a href="<?php echo esc_url(wp_login_url(get_permalink())); ?>" class="opt-login-link"><?php _e('Log In', 'ortho-progress-tracker'); ?></a>
            
            <?php if (get_option('users_can_register')) : ?>
                <span class="opt-login-separator">|</span>
                <a href="<?php echo esc_url(wp_registration_url()); ?>" class="opt-register-link"><?php _e('Create Account', 'ortho-progress-tracker'); ?></a>
            <?php endif; ?>
        </p>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Helper function to render user programs
 */
function opt_render_user_programs($user_id) {
    // Get user programs
    $user_programs = opt_get_user_programs($user_id);
    
    // Get available programs
    $available_programs = opt_get_available_programs($user_id);
    
    // Start output buffer
    ob_start();
    ?>
    <div class="opt-user-programs">
        <?php if (!empty($user_programs)) : ?>
            <div class="opt-section">
                <h2 class="opt-section-title"><?php _e('Your Recovery Programs', 'ortho-progress-tracker'); ?></h2>
                
                <div class="opt-program-grid opt-col-3">
                    <?php foreach ($user_programs as $program) : ?>
                        <div class="opt-program-card">
                            <?php if (!empty($program['image'])) : ?>
                                <div class="opt-program-image">
                                    <img src="<?php echo esc_url($program['image']); ?>" alt="<?php echo esc_attr($program['program_title']); ?>">
                                </div>
                            <?php endif; ?>
                            
                            <div class="opt-program-content">
                                <h3 class="opt-program-title"><?php echo esc_html($program['program_title']); ?></h3>
                                
                                <div class="opt-program-progress">
                                    <div class="opt-progress-bar">
                                        <div class="opt-progress-fill" style="width: <?php echo esc_attr($program['completion_percentage']); ?>%"></div>
                                    </div>
                                    <div class="opt-progress-text">
                                        <?php echo esc_html($program['completion_percentage']); ?>% <?php _e('Complete', 'ortho-progress-tracker'); ?>
                                    </div>
                                </div>
                                
                                <?php if (!empty($program['duration'])) : ?>
                                    <div class="opt-program-duration">
                                        <?php _e('Duration:', 'ortho-progress-tracker'); ?> <?php echo esc_html($program['duration']); ?>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="opt-program-actions">
                                    <a href="<?php echo esc_url(add_query_arg('view', 'program', get_permalink($program['program_id']))); ?>" class="opt-view-program">
                                        <?php _e('Continue Program', 'ortho-progress-tracker'); ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($available_programs)) : ?>
            <div class="opt-section">
                <h2 class="opt-section-title"><?php _e('Available Programs', 'ortho-progress-tracker'); ?></h2>
                
                <div class="opt-program-grid opt-col-3">
                    <?php foreach ($available_programs as $program) : ?>
                        <div class="opt-program-card">
                            <?php if (!empty($program['image'])) : ?>
                                <div class="opt-program-image">
                                    <a href="<?php echo esc_url(get_permalink($program['id'])); ?>">
                                        <img src="<?php echo esc_url($program['image']); ?>" alt="<?php echo esc_attr($program['title']); ?>">
                                    </a>
                                </div>
                            <?php endif; ?>
                            
                            <div class="opt-program-content">
                                <h3 class="opt-program-title">
                                    <a href="<?php echo esc_url(get_permalink($program['id'])); ?>">
                                        <?php echo esc_html($program['title']); ?>
                                    </a>
                                </h3>
                                
                                <?php if (!empty($program['categories']) || !empty($program['difficulty'])) : ?>
                                    <div class="opt-program-meta">
                                        <?php if (!empty($program['categories'])) : ?>
                                            <span class="opt-program-category">
                                                <?php echo esc_html(implode(', ', $program['categories'])); ?>
                                            </span>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($program['difficulty'])) : ?>
                                            <span class="opt-program-difficulty">
                                                <?php _e('Difficulty:', 'ortho-progress-tracker'); ?> <?php echo esc_html(reset($program['difficulty'])); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($program['duration'])) : ?>
                                    <div class="opt-program-duration">
                                        <?php _e('Duration:', 'ortho-progress-tracker'); ?> <?php echo esc_html($program['duration']); ?>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($program['excerpt'])) : ?>
                                    <div class="opt-program-excerpt">
                                        <?php echo wp_kses_post($program['excerpt']); ?>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="opt-program-actions">
                                    <a href="<?php echo esc_url(get_permalink($program['id'])); ?>" class="opt-view-program">
                                        <?php _e('View Details', 'ortho-progress-tracker'); ?>
                                    </a>
                                    
                                    <?php if ($program['product_id'] && !$program['is_purchased']) : ?>
                                        <a href="<?php echo esc_url(get_permalink($program['product_id'])); ?>" class="opt-purchase-program">
                                            <?php _e('Purchase Required', 'ortho-progress-tracker'); ?>
                                        </a>
                                    <?php else : ?>
                                        <button type="button" class="opt-enroll-button" data-program-id="<?php echo esc_attr($program['id']); ?>">
                                            <?php _e('Enroll Now', 'ortho-progress-tracker'); ?>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <script>
                jQuery(document).ready(function($) {
                    $('.opt-enroll-button').on('click', function() {
                        var $button = $(this);
                        var programId = $button.data('program-id');
                        
                        $button.prop('disabled', true).text('<?php _e('Enrolling...', 'ortho-progress-tracker'); ?>');
                        
                        $.ajax({
                            url: opt_data.ajax_url,
                            type: 'POST',
                            data: {
                                action: 'opt_enroll_in_program',
                                program_id: programId,
                                nonce: opt_data.nonce
                            },
                            success: function(response) {
                                if (response.success) {
                                    window.location.reload();
                                } else {
                                    $button.prop('disabled', false).text('<?php _e('Enroll Now', 'ortho-progress-tracker'); ?>');
                                    alert('<?php _e('Error: ', 'ortho-progress-tracker'); ?>' + response.data);
                                }
                            },
                            error: function() {
                                $button.prop('disabled', false).text('<?php _e('Enroll Now', 'ortho-progress-tracker'); ?>');
                                alert('<?php _e('Error enrolling in program. Please try again.', 'ortho-progress-tracker'); ?>');
                            }
                        });
                    });
                });
            </script>
        <?php endif; ?>
        
        <?php if (empty($user_programs) && empty($available_programs)) : ?>
            <div class="opt-no-programs-message">
                <p><?php _e('No recovery programs are available at this time.', 'ortho-progress-tracker'); ?></p>
            </div>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Helper function to render enrollment message
 */
function opt_render_enrollment_message($program_id, $program) {
    // Check if a product is associated with this program
    $product_id = get_post_meta($program_id, '_opt_product_id', true);
    
    // Start output buffer
    ob_start();
    ?>
    <div class="opt-enrollment-required">
        <h3><?php _e('Enrollment Required', 'ortho-progress-tracker'); ?></h3>
        
        <p><?php printf(__('You need to enroll in "%s" to track your progress.', 'ortho-progress-tracker'), esc_html($program->post_title)); ?></p>
        
        <?php if ($product_id && class_exists('WooCommerce')) : 
            $product = wc_get_product($product_id);
            if ($product) :
            ?>
                <p><?php _e('This program requires purchase of the following product:', 'ortho-progress-tracker'); ?></p>
                <div class="opt-required-product">
                    <h4><?php echo esc_html($product->get_name()); ?></h4>
                    
                    <?php if ($product->get_price()) : ?>
                        <div class="opt-product-price">
                            <?php echo wp_kses_post($product->get_price_html()); ?>
                        </div>
                    <?php endif; ?>
                    
                    <a href="<?php echo esc_url($product->get_permalink()); ?>" class="opt-purchase-button">
                        <?php _e('Purchase Product', 'ortho-progress-tracker'); ?>
                    </a>
                </div>
            <?php else : ?>
                <p><?php _e('The product associated with this program is not available.', 'ortho-progress-tracker'); ?></p>
                <button type="button" class="opt-enroll-button" data-program-id="<?php echo esc_attr($program_id); ?>">
                    <?php _e('Enroll Now', 'ortho-progress-tracker'); ?>
                </button>
            <?php endif; ?>
        <?php else : ?>
            <button type="button" class="opt-enroll-button" data-program-id="<?php echo esc_attr($program_id); ?>">
                <?php _e('Enroll Now', 'ortho-progress-tracker'); ?>
            </button>
        <?php endif; ?>
        
        <script>
            jQuery(document).ready(function($) {
                $('.opt-enroll-button').on('click', function() {
                    var $button = $(this);
                    var programId = $button.data('program-id');
                    
                    $button.prop('disabled', true).text('<?php _e('Enrolling...', 'ortho-progress-tracker'); ?>');
                    
                    $.ajax({
                        url: opt_data.ajax_url,
                        type: 'POST',
                        data: {
                            action: 'opt_enroll_in_program',
                            program_id: programId,
                            nonce: opt_data.nonce
                        },
                        success: function(response) {
                            if (response.success) {
                                window.location.reload();
                            } else {
                                $button.prop('disabled', false).text('<?php _e('Enroll Now', 'ortho-progress-tracker'); ?>');
                                alert('<?php _e('Error: ', 'ortho-progress-tracker'); ?>' + response.data);
                            }
                        },
                        error: function() {
                            $button.prop('disabled', false).text('<?php _e('Enroll Now', 'ortho-progress-tracker'); ?>');
                            alert('<?php _e('Error enrolling in program. Please try again.', 'ortho-progress-tracker'); ?>');
                        }
                    });
                });
            });
        </script>
    </div>
    <?php
    return ob_get_clean();
}