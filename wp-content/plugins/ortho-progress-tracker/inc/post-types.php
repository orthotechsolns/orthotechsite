<?php
/**
 * Register custom post types for the Progress Tracker
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register custom post types
 */
function opt_register_post_types() {
    // Recovery Program post type
    $program_labels = array(
        'name'                  => _x('Recovery Programs', 'Post Type General Name', 'ortho-progress-tracker'),
        'singular_name'         => _x('Recovery Program', 'Post Type Singular Name', 'ortho-progress-tracker'),
        'menu_name'             => __('Recovery Programs', 'ortho-progress-tracker'),
        'name_admin_bar'        => __('Recovery Program', 'ortho-progress-tracker'),
        'archives'              => __('Program Archives', 'ortho-progress-tracker'),
        'attributes'            => __('Program Attributes', 'ortho-progress-tracker'),
        'parent_item_colon'     => __('Parent Program:', 'ortho-progress-tracker'),
        'all_items'             => __('All Programs', 'ortho-progress-tracker'),
        'add_new_item'          => __('Add New Program', 'ortho-progress-tracker'),
        'add_new'               => __('Add New', 'ortho-progress-tracker'),
        'new_item'              => __('New Program', 'ortho-progress-tracker'),
        'edit_item'             => __('Edit Program', 'ortho-progress-tracker'),
        'update_item'           => __('Update Program', 'ortho-progress-tracker'),
        'view_item'             => __('View Program', 'ortho-progress-tracker'),
        'view_items'            => __('View Programs', 'ortho-progress-tracker'),
        'search_items'          => __('Search Program', 'ortho-progress-tracker'),
    );
    
    $program_args = array(
        'label'                 => __('Recovery Program', 'ortho-progress-tracker'),
        'description'           => __('Orthopedic recovery and rehabilitation programs', 'ortho-progress-tracker'),
        'labels'                => $program_labels,
        'supports'              => array('title', 'editor', 'thumbnail', 'excerpt'),
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 5,
        'menu_icon'             => 'dashicons-chart-line',
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'has_archive'           => true,
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'post',
        'show_in_rest'          => true,
    );
    
    register_post_type('recovery_program', $program_args);
    
    // Recovery Step post type
    $step_labels = array(
        'name'                  => _x('Recovery Steps', 'Post Type General Name', 'ortho-progress-tracker'),
        'singular_name'         => _x('Recovery Step', 'Post Type Singular Name', 'ortho-progress-tracker'),
        'menu_name'             => __('Recovery Steps', 'ortho-progress-tracker'),
        'name_admin_bar'        => __('Recovery Step', 'ortho-progress-tracker'),
        'archives'              => __('Step Archives', 'ortho-progress-tracker'),
        'attributes'            => __('Step Attributes', 'ortho-progress-tracker'),
        'parent_item_colon'     => __('Parent Step:', 'ortho-progress-tracker'),
        'all_items'             => __('All Steps', 'ortho-progress-tracker'),
        'add_new_item'          => __('Add New Step', 'ortho-progress-tracker'),
        'add_new'               => __('Add New', 'ortho-progress-tracker'),
        'new_item'              => __('New Step', 'ortho-progress-tracker'),
        'edit_item'             => __('Edit Step', 'ortho-progress-tracker'),
        'update_item'           => __('Update Step', 'ortho-progress-tracker'),
        'view_item'             => __('View Step', 'ortho-progress-tracker'),
        'view_items'            => __('View Steps', 'ortho-progress-tracker'),
        'search_items'          => __('Search Step', 'ortho-progress-tracker'),
    );
    
    $step_args = array(
        'label'                 => __('Recovery Step', 'ortho-progress-tracker'),
        'description'           => __('Individual steps within a recovery program', 'ortho-progress-tracker'),
        'labels'                => $step_labels,
        'supports'              => array('title', 'editor', 'thumbnail'),
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => 'edit.php?post_type=recovery_program',
        'menu_position'         => 5,
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'has_archive'           => false,
        'exclude_from_search'   => true,
        'publicly_queryable'    => true,
        'capability_type'       => 'post',
        'show_in_rest'          => true,
    );
    
    register_post_type('recovery_step', $step_args);
    
    // Register category taxonomy for programs
    $program_cat_labels = array(
        'name'                       => _x('Program Categories', 'Taxonomy General Name', 'ortho-progress-tracker'),
        'singular_name'              => _x('Program Category', 'Taxonomy Singular Name', 'ortho-progress-tracker'),
        'menu_name'                  => __('Program Categories', 'ortho-progress-tracker'),
        'all_items'                  => __('All Categories', 'ortho-progress-tracker'),
        'parent_item'                => __('Parent Category', 'ortho-progress-tracker'),
        'parent_item_colon'          => __('Parent Category:', 'ortho-progress-tracker'),
        'new_item_name'              => __('New Category Name', 'ortho-progress-tracker'),
        'add_new_item'               => __('Add New Category', 'ortho-progress-tracker'),
        'edit_item'                  => __('Edit Category', 'ortho-progress-tracker'),
        'update_item'                => __('Update Category', 'ortho-progress-tracker'),
        'view_item'                  => __('View Category', 'ortho-progress-tracker'),
        'separate_items_with_commas' => __('Separate categories with commas', 'ortho-progress-tracker'),
        'add_or_remove_items'        => __('Add or remove categories', 'ortho-progress-tracker'),
        'choose_from_most_used'      => __('Choose from the most used', 'ortho-progress-tracker'),
    );
    
    $program_cat_args = array(
        'labels'                     => $program_cat_labels,
        'hierarchical'               => true,
        'public'                     => true,
        'show_ui'                    => true,
        'show_admin_column'          => true,
        'show_in_nav_menus'          => true,
        'show_tagcloud'              => false,
        'show_in_rest'               => true,
    );
    
    register_taxonomy('program_category', array('recovery_program'), $program_cat_args);
    
    // Register difficulty taxonomy for programs
    $difficulty_labels = array(
        'name'                       => _x('Difficulty Levels', 'Taxonomy General Name', 'ortho-progress-tracker'),
        'singular_name'              => _x('Difficulty Level', 'Taxonomy Singular Name', 'ortho-progress-tracker'),
        'menu_name'                  => __('Difficulty Levels', 'ortho-progress-tracker'),
        'all_items'                  => __('All Difficulty Levels', 'ortho-progress-tracker'),
        'parent_item'                => __('Parent Difficulty Level', 'ortho-progress-tracker'),
        'parent_item_colon'          => __('Parent Difficulty Level:', 'ortho-progress-tracker'),
        'new_item_name'              => __('New Difficulty Level Name', 'ortho-progress-tracker'),
        'add_new_item'               => __('Add New Difficulty Level', 'ortho-progress-tracker'),
        'edit_item'                  => __('Edit Difficulty Level', 'ortho-progress-tracker'),
        'update_item'                => __('Update Difficulty Level', 'ortho-progress-tracker'),
        'view_item'                  => __('View Difficulty Level', 'ortho-progress-tracker'),
        'separate_items_with_commas' => __('Separate difficulty levels with commas', 'ortho-progress-tracker'),
        'add_or_remove_items'        => __('Add or remove difficulty levels', 'ortho-progress-tracker'),
        'choose_from_most_used'      => __('Choose from the most used', 'ortho-progress-tracker'),
    );
    
    $difficulty_args = array(
        'labels'                     => $difficulty_labels,
        'hierarchical'               => true,
        'public'                     => true,
        'show_ui'                    => true,
        'show_admin_column'          => true,
        'show_in_nav_menus'          => true,
        'show_tagcloud'              => false,
        'show_in_rest'               => true,
    );
    
    register_taxonomy('program_difficulty', array('recovery_program'), $difficulty_args);
}
add_action('init', 'opt_register_post_types');

/**
 * Add meta boxes for recovery programs and steps
 */
function opt_add_meta_boxes() {
    // Program details meta box
    add_meta_box(
        'opt_program_details',
        __('Program Details', 'ortho-progress-tracker'),
        'opt_program_details_callback',
        'recovery_program',
        'normal',
        'high'
    );
    
    // Step details meta box
    add_meta_box(
        'opt_step_details',
        __('Step Details', 'ortho-progress-tracker'),
        'opt_step_details_callback',
        'recovery_step',
        'normal',
        'high'
    );
    
    // Program steps meta box
    add_meta_box(
        'opt_program_steps',
        __('Program Steps', 'ortho-progress-tracker'),
        'opt_program_steps_callback',
        'recovery_program',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'opt_add_meta_boxes');

/**
 * Program details meta box callback
 */
function opt_program_details_callback($post) {
    // Add nonce for security
    wp_nonce_field('opt_save_program_details', 'opt_program_details_nonce');
    
    // Get current values
    $duration = get_post_meta($post->ID, '_opt_duration', true);
    $duration_unit = get_post_meta($post->ID, '_opt_duration_unit', true) ?: 'weeks';
    $expected_improvement = get_post_meta($post->ID, '_opt_expected_improvement', true);
    $product_id = get_post_meta($post->ID, '_opt_product_id', true);
    
    // Output fields
    ?>
    <div class="opt-meta-field">
        <label for="opt_duration"><?php _e('Program Duration:', 'ortho-progress-tracker'); ?></label>
        <input type="number" id="opt_duration" name="opt_duration" value="<?php echo esc_attr($duration); ?>" min="1" step="1" class="small-text">
        <select name="opt_duration_unit" id="opt_duration_unit">
            <option value="days" <?php selected($duration_unit, 'days'); ?>><?php _e('Days', 'ortho-progress-tracker'); ?></option>
            <option value="weeks" <?php selected($duration_unit, 'weeks'); ?>><?php _e('Weeks', 'ortho-progress-tracker'); ?></option>
            <option value="months" <?php selected($duration_unit, 'months'); ?>><?php _e('Months', 'ortho-progress-tracker'); ?></option>
        </select>
    </div>
    
    <div class="opt-meta-field">
        <label for="opt_expected_improvement"><?php _e('Expected Improvement:', 'ortho-progress-tracker'); ?></label>
        <textarea id="opt_expected_improvement" name="opt_expected_improvement" class="large-text" rows="3"><?php echo esc_textarea($expected_improvement); ?></textarea>
        <p class="description"><?php _e('Describe the expected improvements after completing this program.', 'ortho-progress-tracker'); ?></p>
    </div>
    
    <?php if (class_exists('WooCommerce')) : ?>
        <div class="opt-meta-field">
            <label for="opt_product_id"><?php _e('Associated Product:', 'ortho-progress-tracker'); ?></label>
            <select id="opt_product_id" name="opt_product_id">
                <option value=""><?php _e('-- Select Product --', 'ortho-progress-tracker'); ?></option>
                <?php
                // Get products
                $products = wc_get_products(array(
                    'limit' => -1,
                    'status' => 'publish',
                ));
                
                foreach ($products as $product) {
                    printf(
                        '<option value="%s" %s>%s</option>',
                        esc_attr($product->get_id()),
                        selected($product_id, $product->get_id(), false),
                        esc_html($product->get_name())
                    );
                }
                ?>
            </select>
            <p class="description"><?php _e('Link this program to a specific product in your store.', 'ortho-progress-tracker'); ?></p>
        </div>
    <?php endif; ?>
    <?php
}

/**
 * Step details meta box callback
 */
function opt_step_details_callback($post) {
    // Add nonce for security
    wp_nonce_field('opt_save_step_details', 'opt_step_details_nonce');
    
    // Get current values
    $program_id = get_post_meta($post->ID, '_opt_program_id', true);
    $step_number = get_post_meta($post->ID, '_opt_step_number', true);
    $duration = get_post_meta($post->ID, '_opt_step_duration', true);
    $duration_unit = get_post_meta($post->ID, '_opt_step_duration_unit', true) ?: 'days';
    $video_url = get_post_meta($post->ID, '_opt_video_url', true);
    
    // Get programs
    $programs = get_posts(array(
        'post_type' => 'recovery_program',
        'numberposts' => -1,
        'orderby' => 'title',
        'order' => 'ASC',
    ));
    
    // Output fields
    ?>
    <div class="opt-meta-field">
        <label for="opt_program_id"><?php _e('Program:', 'ortho-progress-tracker'); ?></label>
        <select id="opt_program_id" name="opt_program_id" required>
            <option value=""><?php _e('-- Select Program --', 'ortho-progress-tracker'); ?></option>
            <?php
            foreach ($programs as $program) {
                printf(
                    '<option value="%s" %s>%s</option>',
                    esc_attr($program->ID),
                    selected($program_id, $program->ID, false),
                    esc_html($program->post_title)
                );
            }
            ?>
        </select>
    </div>
    
    <div class="opt-meta-field">
        <label for="opt_step_number"><?php _e('Step Number:', 'ortho-progress-tracker'); ?></label>
        <input type="number" id="opt_step_number" name="opt_step_number" value="<?php echo esc_attr($step_number); ?>" min="1" step="1" class="small-text" required>
        <p class="description"><?php _e('The order of this step in the program.', 'ortho-progress-tracker'); ?></p>
    </div>
    
    <div class="opt-meta-field">
        <label for="opt_step_duration"><?php _e('Step Duration:', 'ortho-progress-tracker'); ?></label>
        <input type="number" id="opt_step_duration" name="opt_step_duration" value="<?php echo esc_attr($duration); ?>" min="1" step="1" class="small-text">
        <select name="opt_step_duration_unit" id="opt_step_duration_unit">
            <option value="days" <?php selected($duration_unit, 'days'); ?>><?php _e('Days', 'ortho-progress-tracker'); ?></option>
            <option value="weeks" <?php selected($duration_unit, 'weeks'); ?>><?php _e('Weeks', 'ortho-progress-tracker'); ?></option>
        </select>
    </div>
    
    <div class="opt-meta-field">
        <label for="opt_video_url"><?php _e('Video URL:', 'ortho-progress-tracker'); ?></label>
        <input type="url" id="opt_video_url" name="opt_video_url" value="<?php echo esc_attr($video_url); ?>" class="regular-text">
        <p class="description"><?php _e('URL to an instructional video for this step (YouTube, Vimeo, etc.).', 'ortho-progress-tracker'); ?></p>
    </div>
    <?php
}

/**
 * Program steps meta box callback
 */
function opt_program_steps_callback($post) {
    // Get existing steps for this program
    $steps = get_posts(array(
        'post_type' => 'recovery_step',
        'numberposts' => -1,
        'meta_key' => '_opt_program_id',
        'meta_value' => $post->ID,
        'meta_query' => array(
            array(
                'key' => '_opt_step_number',
                'type' => 'NUMERIC',
            ),
        ),
        'orderby' => 'meta_value_num',
        'order' => 'ASC',
    ));
    
    // Display steps table
    if (!empty($steps)) {
        ?>
        <table class="widefat fixed striped">
            <thead>
                <tr>
                    <th><?php _e('Step #', 'ortho-progress-tracker'); ?></th>
                    <th><?php _e('Title', 'ortho-progress-tracker'); ?></th>
                    <th><?php _e('Duration', 'ortho-progress-tracker'); ?></th>
                    <th><?php _e('Actions', 'ortho-progress-tracker'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($steps as $step) : 
                    $step_number = get_post_meta($step->ID, '_opt_step_number', true);
                    $duration = get_post_meta($step->ID, '_opt_step_duration', true);
                    $duration_unit = get_post_meta($step->ID, '_opt_step_duration_unit', true) ?: 'days';
                ?>
                    <tr>
                        <td><?php echo esc_html($step_number); ?></td>
                        <td>
                            <a href="<?php echo get_edit_post_link($step->ID); ?>"><?php echo esc_html($step->post_title); ?></a>
                        </td>
                        <td>
                            <?php
                            if ($duration) {
                                echo esc_html("$duration " . ucfirst($duration_unit));
                            } else {
                                echo '&mdash;';
                            }
                            ?>
                        </td>
                        <td>
                            <a href="<?php echo get_edit_post_link($step->ID); ?>" class="button button-small"><?php _e('Edit', 'ortho-progress-tracker'); ?></a>
                            <a href="<?php echo get_delete_post_link($step->ID); ?>" class="button button-small"><?php _e('Delete', 'ortho-progress-tracker'); ?></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php
    } else {
        echo '<p>' . __('No steps have been added to this program yet.', 'ortho-progress-tracker') . '</p>';
    }
    
    // Add new step button
    ?>
    <p>
        <a href="<?php echo admin_url('post-new.php?post_type=recovery_step&program_id=' . $post->ID); ?>" class="button button-primary">
            <?php _e('Add New Step', 'ortho-progress-tracker'); ?>
        </a>
    </p>
    <?php
}

/**
 * Pre-fill program ID when creating a new step
 */
function opt_prefill_program_id() {
    global $pagenow;
    
    // Check if we're on the new step page with a program ID parameter
    if ($pagenow === 'post-new.php' && isset($_GET['post_type']) && $_GET['post_type'] === 'recovery_step' && isset($_GET['program_id'])) {
        // Add inline script to prefill the program ID
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                $('#opt_program_id').val('<?php echo intval($_GET['program_id']); ?>');
            });
        </script>
        <?php
    }
}
add_action('admin_footer', 'opt_prefill_program_id');

/**
 * Save program details meta box data
 */
function opt_save_program_details($post_id) {
    // Check if nonce is set
    if (!isset($_POST['opt_program_details_nonce'])) {
        return;
    }
    
    // Verify nonce
    if (!wp_verify_nonce($_POST['opt_program_details_nonce'], 'opt_save_program_details')) {
        return;
    }
    
    // If this is an autosave, don't do anything
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    // Check user permissions
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    // Save data
    if (isset($_POST['opt_duration'])) {
        update_post_meta($post_id, '_opt_duration', intval($_POST['opt_duration']));
    }
    
    if (isset($_POST['opt_duration_unit'])) {
        update_post_meta($post_id, '_opt_duration_unit', sanitize_text_field($_POST['opt_duration_unit']));
    }
    
    if (isset($_POST['opt_expected_improvement'])) {
        update_post_meta($post_id, '_opt_expected_improvement', sanitize_textarea_field($_POST['opt_expected_improvement']));
    }
    
    if (isset($_POST['opt_product_id'])) {
        update_post_meta($post_id, '_opt_product_id', intval($_POST['opt_product_id']));
    }
}
add_action('save_post_recovery_program', 'opt_save_program_details');

/**
 * Save step details meta box data
 */
function opt_save_step_details($post_id) {
    // Check if nonce is set
    if (!isset($_POST['opt_step_details_nonce'])) {
        return;
    }
    
    // Verify nonce
    if (!wp_verify_nonce($_POST['opt_step_details_nonce'], 'opt_save_step_details')) {
        return;
    }
    
    // If this is an autosave, don't do anything
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    // Check user permissions
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    
    // Save data
    if (isset($_POST['opt_program_id'])) {
        update_post_meta($post_id, '_opt_program_id', intval($_POST['opt_program_id']));
    }
    
    if (isset($_POST['opt_step_number'])) {
        update_post_meta($post_id, '_opt_step_number', intval($_POST['opt_step_number']));
    }
    
    if (isset($_POST['opt_step_duration'])) {
        update_post_meta($post_id, '_opt_step_duration', intval($_POST['opt_step_duration']));
    }
    
    if (isset($_POST['opt_step_duration_unit'])) {
        update_post_meta($post_id, '_opt_step_duration_unit', sanitize_text_field($_POST['opt_step_duration_unit']));
    }
    
    if (isset($_POST['opt_video_url'])) {
        update_post_meta($post_id, '_opt_video_url', esc_url_raw($_POST['opt_video_url']));
    }
}
add_action('save_post_recovery_step', 'opt_save_step_details');