<?php
/**
 * User progress tracking functionality
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Create tables for storing user progress data
 */
function opt_create_tables() {
    global $wpdb;
    
    $charset_collate = $wpdb->get_charset_collate();
    
    // Table for tracking step progress
    $table_name = $wpdb->prefix . 'opt_user_progress';
    
    $sql = "CREATE TABLE $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        program_id bigint(20) NOT NULL,
        step_id bigint(20) NOT NULL,
        completed tinyint(1) DEFAULT 0,
        pain_level tinyint(1) DEFAULT 0,
        notes text DEFAULT '',
        date_completed datetime DEFAULT NULL,
        last_updated datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        KEY user_program (user_id, program_id),
        KEY user_step (user_id, step_id)
    ) $charset_collate;";
    
    // Table for tracking program progress summary
    $table_name_summary = $wpdb->prefix . 'opt_program_summary';
    
    $sql .= "CREATE TABLE $table_name_summary (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        program_id bigint(20) NOT NULL,
        start_date datetime DEFAULT CURRENT_TIMESTAMP,
        last_activity datetime DEFAULT CURRENT_TIMESTAMP,
        completion_percentage int(3) DEFAULT 0,
        status varchar(20) DEFAULT 'in_progress',
        PRIMARY KEY  (id),
        UNIQUE KEY user_program (user_id, program_id)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

/**
 * Save user progress for a specific step
 * 
 * @param int $user_id The user ID
 * @param int $program_id The program ID
 * @param int $step_id The step ID
 * @param bool $completed Whether the step is completed
 * @param int $pain_level The pain level (0-10)
 * @param string $notes User notes
 * @return bool Success or failure
 */
function opt_save_user_progress($user_id, $program_id, $step_id, $completed, $pain_level = 0, $notes = '') {
    global $wpdb;
    
    // Sanitize inputs
    $user_id = absint($user_id);
    $program_id = absint($program_id);
    $step_id = absint($step_id);
    $completed = (bool) $completed;
    $pain_level = min(10, max(0, absint($pain_level)));
    $notes = sanitize_textarea_field($notes);
    
    // Check if record exists
    $existing = $wpdb->get_row($wpdb->prepare(
        "SELECT id, completed FROM {$wpdb->prefix}opt_user_progress 
         WHERE user_id = %d AND program_id = %d AND step_id = %d",
        $user_id, $program_id, $step_id
    ));
    
    // Get current time in MySQL format
    $current_time = current_time('mysql');
    
    // If record exists, update it
    if ($existing) {
        $data = array(
            'completed' => $completed ? 1 : 0,
            'pain_level' => $pain_level,
            'notes' => $notes,
            'last_updated' => $current_time
        );
        
        // Set completion date if completing for the first time
        if ($completed && !$existing->completed) {
            $data['date_completed'] = $current_time;
        }
        
        // Clear completion date if unchecking completed
        if (!$completed && $existing->completed) {
            $data['date_completed'] = null;
        }
        
        $result = $wpdb->update(
            $wpdb->prefix . 'opt_user_progress',
            $data,
            array(
                'user_id' => $user_id,
                'program_id' => $program_id,
                'step_id' => $step_id
            ),
            array('%d', '%d', '%s', '%s', '%s'),
            array('%d', '%d', '%d')
        );
    } else {
        // Insert new record
        $result = $wpdb->insert(
            $wpdb->prefix . 'opt_user_progress',
            array(
                'user_id' => $user_id,
                'program_id' => $program_id,
                'step_id' => $step_id,
                'completed' => $completed ? 1 : 0,
                'pain_level' => $pain_level,
                'notes' => $notes,
                'date_completed' => $completed ? $current_time : null,
                'last_updated' => $current_time
            ),
            array('%d', '%d', '%d', '%d', '%d', '%s', '%s', '%s')
        );
    }
    
    if ($result) {
        // Update program summary
        opt_update_program_summary($user_id, $program_id);
        return true;
    }
    
    return false;
}

/**
 * Update program summary for a user
 * 
 * @param int $user_id The user ID
 * @param int $program_id The program ID
 * @return bool Success or failure
 */
function opt_update_program_summary($user_id, $program_id) {
    global $wpdb;
    
    // Sanitize inputs
    $user_id = absint($user_id);
    $program_id = absint($program_id);
    
    // Get total steps in program
    $total_steps = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->posts} p
         JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
         WHERE p.post_type = 'recovery_step'
         AND p.post_status = 'publish'
         AND pm.meta_key = '_opt_program_id'
         AND pm.meta_value = %d",
        $program_id
    ));
    
    if (!$total_steps) {
        $total_steps = 1; // Avoid division by zero
    }
    
    // Get completed steps
    $completed_steps = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}opt_user_progress
         WHERE user_id = %d AND program_id = %d AND completed = 1",
        $user_id, $program_id
    ));
    
    // Calculate completion percentage
    $completion_percentage = min(100, round(($completed_steps / $total_steps) * 100));
    
    // Set status
    $status = $completion_percentage == 100 ? 'completed' : 'in_progress';
    
    // Check if summary record exists
    $existing = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM {$wpdb->prefix}opt_program_summary
         WHERE user_id = %d AND program_id = %d",
        $user_id, $program_id
    ));
    
    // Get current time
    $current_time = current_time('mysql');
    
    if ($existing) {
        // Update existing record
        $result = $wpdb->update(
            $wpdb->prefix . 'opt_program_summary',
            array(
                'last_activity' => $current_time,
                'completion_percentage' => $completion_percentage,
                'status' => $status
            ),
            array(
                'user_id' => $user_id,
                'program_id' => $program_id
            ),
            array('%s', '%d', '%s'),
            array('%d', '%d')
        );
    } else {
        // Insert new record
        $result = $wpdb->insert(
            $wpdb->prefix . 'opt_program_summary',
            array(
                'user_id' => $user_id,
                'program_id' => $program_id,
                'start_date' => $current_time,
                'last_activity' => $current_time,
                'completion_percentage' => $completion_percentage,
                'status' => $status
            ),
            array('%d', '%d', '%s', '%s', '%d', '%s')
        );
    }
    
    return (bool) $result;
}

/**
 * Get user progress for a specific program
 * 
 * @param int $user_id The user ID
 * @param int $program_id The program ID
 * @return array User progress data
 */
function opt_get_user_program_progress($user_id, $program_id) {
    global $wpdb;
    
    // Sanitize inputs
    $user_id = absint($user_id);
    $program_id = absint($program_id);
    
    // Get program summary
    $summary = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}opt_program_summary
         WHERE user_id = %d AND program_id = %d",
        $user_id, $program_id
    ), ARRAY_A);
    
    if (!$summary) {
        // Create default summary if doesn't exist
        $summary = array(
            'start_date' => current_time('mysql'),
            'last_activity' => current_time('mysql'),
            'completion_percentage' => 0,
            'status' => 'in_progress'
        );
    }
    
    // Get steps progress
    $steps_progress = $wpdb->get_results($wpdb->prepare(
        "SELECT up.*, rs.post_title as step_title, pm.meta_value as step_number
         FROM {$wpdb->prefix}opt_user_progress up
         JOIN {$wpdb->posts} rs ON up.step_id = rs.ID
         LEFT JOIN {$wpdb->postmeta} pm ON rs.ID = pm.post_id AND pm.meta_key = '_opt_step_number'
         WHERE up.user_id = %d AND up.program_id = %d
         ORDER BY CAST(pm.meta_value AS UNSIGNED) ASC",
        $user_id, $program_id
    ), ARRAY_A);
    
    // Get all program steps to include ones not yet started
    $all_steps = $wpdb->get_results($wpdb->prepare(
        "SELECT p.ID as step_id, p.post_title as step_title, 
                pm_num.meta_value as step_number,
                pm_dur.meta_value as duration,
                pm_unit.meta_value as duration_unit,
                pm_vid.meta_value as video_url
         FROM {$wpdb->posts} p
         JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_opt_program_id' AND pm.meta_value = %d
         LEFT JOIN {$wpdb->postmeta} pm_num ON p.ID = pm_num.post_id AND pm_num.meta_key = '_opt_step_number'
         LEFT JOIN {$wpdb->postmeta} pm_dur ON p.ID = pm_dur.post_id AND pm_dur.meta_key = '_opt_step_duration'
         LEFT JOIN {$wpdb->postmeta} pm_unit ON p.ID = pm_unit.post_id AND pm_unit.meta_key = '_opt_step_duration_unit'
         LEFT JOIN {$wpdb->postmeta} pm_vid ON p.ID = pm_vid.post_id AND pm_vid.meta_key = '_opt_video_url'
         WHERE p.post_type = 'recovery_step'
         AND p.post_status = 'publish'
         ORDER BY CAST(pm_num.meta_value AS UNSIGNED) ASC",
        $program_id
    ), ARRAY_A);
    
    // Combine step data with progress data
    $complete_steps_data = array();
    
    foreach ($all_steps as $step) {
        $step_id = $step['step_id'];
        $progress_data = array_filter($steps_progress, function($item) use ($step_id) {
            return $item['step_id'] == $step_id;
        });
        
        $progress = !empty($progress_data) ? reset($progress_data) : array(
            'completed' => 0,
            'pain_level' => 0,
            'notes' => '',
            'date_completed' => null,
            'last_updated' => null
        );
        
        $complete_steps_data[] = array_merge($step, $progress);
    }
    
    // Get pain level trend data
    $pain_trend = $wpdb->get_results($wpdb->prepare(
        "SELECT DATE(last_updated) as date, AVG(pain_level) as avg_pain
         FROM {$wpdb->prefix}opt_user_progress
         WHERE user_id = %d AND program_id = %d AND pain_level > 0
         GROUP BY DATE(last_updated)
         ORDER BY DATE(last_updated) ASC
         LIMIT 30",
        $user_id, $program_id
    ), ARRAY_A);
    
    return array(
        'summary' => $summary,
        'steps' => $complete_steps_data,
        'pain_trend' => $pain_trend
    );
}

/**
 * Get all user programs with progress
 * 
 * @param int $user_id The user ID
 * @return array User programs with progress data
 */
function opt_get_user_programs($user_id) {
    global $wpdb;
    
    // Sanitize input
    $user_id = absint($user_id);
    
    // Get program summaries
    $summaries = $wpdb->get_results($wpdb->prepare(
        "SELECT ps.*, p.post_title as program_title, p.post_excerpt as program_excerpt
         FROM {$wpdb->prefix}opt_program_summary ps
         JOIN {$wpdb->posts} p ON ps.program_id = p.ID
         WHERE ps.user_id = %d
         ORDER BY ps.last_activity DESC",
        $user_id
    ), ARRAY_A);
    
    if (empty($summaries)) {
        return array();
    }
    
    // Get additional program data
    foreach ($summaries as &$summary) {
        // Get program image
        $summary['image'] = get_the_post_thumbnail_url($summary['program_id'], 'medium');
        
        // Get program categories
        $categories = wp_get_post_terms($summary['program_id'], 'program_category', array('fields' => 'names'));
        $summary['categories'] = $categories;
        
        // Get program difficulty
        $difficulty = wp_get_post_terms($summary['program_id'], 'program_difficulty', array('fields' => 'names'));
        $summary['difficulty'] = !empty($difficulty) ? reset($difficulty) : '';
        
        // Get program duration
        $duration = get_post_meta($summary['program_id'], '_opt_duration', true);
        $duration_unit = get_post_meta($summary['program_id'], '_opt_duration_unit', true) ?: 'weeks';
        $summary['duration'] = $duration ? "$duration $duration_unit" : '';
    }
    
    return $summaries;
}

/**
 * Get available programs for a user
 * 
 * @param int $user_id The user ID
 * @return array Available programs
 */
function opt_get_available_programs($user_id) {
    // Get current user programs
    $user_programs = opt_get_user_programs($user_id);
    $user_program_ids = array_column($user_programs, 'program_id');
    
    // Query for all published programs
    $args = array(
        'post_type' => 'recovery_program',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC',
    );
    
    // Exclude programs the user is already enrolled in
    if (!empty($user_program_ids)) {
        $args['post__not_in'] = $user_program_ids;
    }
    
    $programs = get_posts($args);
    $available_programs = array();
    
    foreach ($programs as $program) {
        $duration = get_post_meta($program->ID, '_opt_duration', true);
        $duration_unit = get_post_meta($program->ID, '_opt_duration_unit', true) ?: 'weeks';
        $product_id = get_post_meta($program->ID, '_opt_product_id', true);
        
        // Check if this program is associated with a product the user has purchased
        $is_purchased = false;
        if ($product_id && class_exists('WooCommerce')) {
            $is_purchased = wc_customer_bought_product($user_id, $user_id, $product_id);
        }
        
        $program_data = array(
            'id' => $program->ID,
            'title' => $program->post_title,
            'excerpt' => $program->post_excerpt,
            'image' => get_the_post_thumbnail_url($program->ID, 'medium'),
            'duration' => $duration ? "$duration $duration_unit" : '',
            'categories' => wp_get_post_terms($program->ID, 'program_category', array('fields' => 'names')),
            'difficulty' => wp_get_post_terms($program->ID, 'program_difficulty', array('fields' => 'names')),
            'product_id' => $product_id,
            'is_purchased' => $is_purchased,
            'steps_count' => count(get_posts(array(
                'post_type' => 'recovery_step',
                'numberposts' => -1,
                'meta_key' => '_opt_program_id',
                'meta_value' => $program->ID,
            ))),
        );
        
        $available_programs[] = $program_data;
    }
    
    return $available_programs;
}

/**
 * Enroll user in a program
 * 
 * @param int $user_id The user ID
 * @param int $program_id The program ID
 * @return bool Success or failure
 */
function opt_enroll_user_in_program($user_id, $program_id) {
    global $wpdb;
    
    // Sanitize inputs
    $user_id = absint($user_id);
    $program_id = absint($program_id);
    
    // Check if already enrolled
    $existing = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM {$wpdb->prefix}opt_program_summary
         WHERE user_id = %d AND program_id = %d",
        $user_id, $program_id
    ));
    
    if ($existing) {
        return true; // Already enrolled
    }
    
    // Get current time
    $current_time = current_time('mysql');
    
    // Insert summary record
    $result = $wpdb->insert(
        $wpdb->prefix . 'opt_program_summary',
        array(
            'user_id' => $user_id,
            'program_id' => $program_id,
            'start_date' => $current_time,
            'last_activity' => $current_time,
            'completion_percentage' => 0,
            'status' => 'in_progress'
        ),
        array('%d', '%d', '%s', '%s', '%d', '%s')
    );
    
    return (bool) $result;
}

/**
 * AJAX handler for enrolling in a program
 */
function opt_ajax_enroll_in_program() {
    // Check nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'opt-ajax-nonce')) {
        wp_send_json_error('Invalid nonce');
    }
    
    // Check if user is logged in
    if (!is_user_logged_in()) {
        wp_send_json_error('User not logged in');
    }
    
    // Get data from request
    $program_id = isset($_POST['program_id']) ? intval($_POST['program_id']) : 0;
    
    if (!$program_id) {
        wp_send_json_error('Invalid program ID');
    }
    
    // Get user ID
    $user_id = get_current_user_id();
    
    // Enroll user
    $result = opt_enroll_user_in_program($user_id, $program_id);
    
    if ($result) {
        wp_send_json_success(array('message' => 'Successfully enrolled in program'));
    } else {
        wp_send_json_error('Failed to enroll in program');
    }
}
add_action('wp_ajax_opt_enroll_in_program', 'opt_ajax_enroll_in_program');