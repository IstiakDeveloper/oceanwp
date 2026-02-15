<?php
/**
 * NGO Projects Custom Post Type
 * 
 * Handles the registration of Projects custom post type and taxonomies
 * for displaying ongoing and previous projects
 *
 * @package OceanWP
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register Projects Custom Post Type
 */
function oceanwp_register_projects_post_type() {
    
    $labels = array(
        'name'                  => _x( 'Projects', 'Post Type General Name', 'oceanwp' ),
        'singular_name'         => _x( 'Project', 'Post Type Singular Name', 'oceanwp' ),
        'menu_name'             => __( 'Projects', 'oceanwp' ),
        'name_admin_bar'        => __( 'Project', 'oceanwp' ),
        'archives'              => __( 'Project Archives', 'oceanwp' ),
        'attributes'            => __( 'Project Attributes', 'oceanwp' ),
        'parent_item_colon'     => __( 'Parent Project:', 'oceanwp' ),
        'all_items'             => __( 'All Projects', 'oceanwp' ),
        'add_new_item'          => __( 'Add New Project', 'oceanwp' ),
        'add_new'               => __( 'Add New', 'oceanwp' ),
        'new_item'              => __( 'New Project', 'oceanwp' ),
        'edit_item'             => __( 'Edit Project', 'oceanwp' ),
        'update_item'           => __( 'Update Project', 'oceanwp' ),
        'view_item'             => __( 'View Project', 'oceanwp' ),
        'view_items'            => __( 'View Projects', 'oceanwp' ),
        'search_items'          => __( 'Search Project', 'oceanwp' ),
        'not_found'             => __( 'Not found', 'oceanwp' ),
        'not_found_in_trash'    => __( 'Not found in Trash', 'oceanwp' ),
        'featured_image'        => __( 'Project Image', 'oceanwp' ),
        'set_featured_image'    => __( 'Set project image', 'oceanwp' ),
        'remove_featured_image' => __( 'Remove project image', 'oceanwp' ),
        'use_featured_image'    => __( 'Use as project image', 'oceanwp' ),
        'insert_into_item'      => __( 'Insert into project', 'oceanwp' ),
        'uploaded_to_this_item' => __( 'Uploaded to this project', 'oceanwp' ),
        'items_list'            => __( 'Projects list', 'oceanwp' ),
        'items_list_navigation' => __( 'Projects list navigation', 'oceanwp' ),
        'filter_items_list'     => __( 'Filter projects list', 'oceanwp' ),
    );
    
    $args = array(
        'label'                 => __( 'Project', 'oceanwp' ),
        'description'           => __( 'NGO Projects', 'oceanwp' ),
        'labels'                => $labels,
        'supports'              => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ),
        'taxonomies'            => array( 'project_category', 'project_status' ),
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 5,
        'menu_icon'             => 'dashicons-portfolio',
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'has_archive'           => true,
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'post',
        'show_in_rest'          => true,
        'rewrite'               => array( 'slug' => 'projects' ),
    );
    
    register_post_type( 'ngo_project', $args );
}
add_action( 'init', 'oceanwp_register_projects_post_type', 0 );

/**
 * Register Project Status Taxonomy (Ongoing/Previous)
 */
function oceanwp_register_project_status_taxonomy() {
    
    $labels = array(
        'name'                       => _x( 'Project Status', 'Taxonomy General Name', 'oceanwp' ),
        'singular_name'              => _x( 'Project Status', 'Taxonomy Singular Name', 'oceanwp' ),
        'menu_name'                  => __( 'Project Status', 'oceanwp' ),
        'all_items'                  => __( 'All Status', 'oceanwp' ),
        'parent_item'                => __( 'Parent Status', 'oceanwp' ),
        'parent_item_colon'          => __( 'Parent Status:', 'oceanwp' ),
        'new_item_name'              => __( 'New Status Name', 'oceanwp' ),
        'add_new_item'               => __( 'Add New Status', 'oceanwp' ),
        'edit_item'                  => __( 'Edit Status', 'oceanwp' ),
        'update_item'                => __( 'Update Status', 'oceanwp' ),
        'view_item'                  => __( 'View Status', 'oceanwp' ),
        'separate_items_with_commas' => __( 'Separate status with commas', 'oceanwp' ),
        'add_or_remove_items'        => __( 'Add or remove status', 'oceanwp' ),
        'choose_from_most_used'      => __( 'Choose from the most used', 'oceanwp' ),
        'popular_items'              => __( 'Popular Status', 'oceanwp' ),
        'search_items'               => __( 'Search Status', 'oceanwp' ),
        'not_found'                  => __( 'Not Found', 'oceanwp' ),
        'no_terms'                   => __( 'No status', 'oceanwp' ),
        'items_list'                 => __( 'Status list', 'oceanwp' ),
        'items_list_navigation'      => __( 'Status list navigation', 'oceanwp' ),
    );
    
    $args = array(
        'labels'                     => $labels,
        'hierarchical'               => true,
        'public'                     => true,
        'show_ui'                    => true,
        'show_admin_column'          => true,
        'show_in_nav_menus'          => true,
        'show_tagcloud'              => false,
        'show_in_rest'               => true,
        'rewrite'                    => array( 'slug' => 'project-status' ),
    );
    
    register_taxonomy( 'project_status', array( 'ngo_project' ), $args );
}
add_action( 'init', 'oceanwp_register_project_status_taxonomy', 0 );

/**
 * Register Project Category Taxonomy
 */
function oceanwp_register_project_category_taxonomy() {
    
    $labels = array(
        'name'                       => _x( 'Project Categories', 'Taxonomy General Name', 'oceanwp' ),
        'singular_name'              => _x( 'Project Category', 'Taxonomy Singular Name', 'oceanwp' ),
        'menu_name'                  => __( 'Categories', 'oceanwp' ),
        'all_items'                  => __( 'All Categories', 'oceanwp' ),
        'parent_item'                => __( 'Parent Category', 'oceanwp' ),
        'parent_item_colon'          => __( 'Parent Category:', 'oceanwp' ),
        'new_item_name'              => __( 'New Category Name', 'oceanwp' ),
        'add_new_item'               => __( 'Add New Category', 'oceanwp' ),
        'edit_item'                  => __( 'Edit Category', 'oceanwp' ),
        'update_item'                => __( 'Update Category', 'oceanwp' ),
        'view_item'                  => __( 'View Category', 'oceanwp' ),
        'separate_items_with_commas' => __( 'Separate categories with commas', 'oceanwp' ),
        'add_or_remove_items'        => __( 'Add or remove categories', 'oceanwp' ),
        'choose_from_most_used'      => __( 'Choose from the most used', 'oceanwp' ),
        'popular_items'              => __( 'Popular Categories', 'oceanwp' ),
        'search_items'               => __( 'Search Categories', 'oceanwp' ),
        'not_found'                  => __( 'Not Found', 'oceanwp' ),
        'no_terms'                   => __( 'No categories', 'oceanwp' ),
        'items_list'                 => __( 'Categories list', 'oceanwp' ),
        'items_list_navigation'      => __( 'Categories list navigation', 'oceanwp' ),
    );
    
    $args = array(
        'labels'                     => $labels,
        'hierarchical'               => true,
        'public'                     => true,
        'show_ui'                    => true,
        'show_admin_column'          => true,
        'show_in_nav_menus'          => true,
        'show_tagcloud'              => true,
        'show_in_rest'               => true,
        'rewrite'                    => array( 'slug' => 'project-category' ),
    );
    
    register_taxonomy( 'project_category', array( 'ngo_project' ), $args );
}
add_action( 'init', 'oceanwp_register_project_category_taxonomy', 0 );

/**
 * Add custom meta boxes for project details
 */
function oceanwp_add_project_meta_boxes() {
    add_meta_box(
        'project_details',
        __( 'Project Details', 'oceanwp' ),
        'oceanwp_render_project_details_meta_box',
        'ngo_project',
        'normal',
        'high'
    );
}
add_action( 'add_meta_boxes', 'oceanwp_add_project_meta_boxes' );

/**
 * Render Project Details Meta Box
 */
function oceanwp_render_project_details_meta_box( $post ) {
    // Add nonce for security
    wp_nonce_field( 'oceanwp_project_details_nonce', 'project_details_nonce' );
    
    // Get existing values
    $project_location = get_post_meta( $post->ID, '_project_location', true );
    $project_duration = get_post_meta( $post->ID, '_project_duration', true );
    $project_budget = get_post_meta( $post->ID, '_project_budget', true );
    $project_donor = get_post_meta( $post->ID, '_project_donor', true );
    $project_goals = get_post_meta( $post->ID, '_project_goals', true );
    $project_beneficiaries = get_post_meta( $post->ID, '_project_beneficiaries', true );
    $project_outcomes = get_post_meta( $post->ID, '_project_outcomes', true );
    ?>
    
    <div class="project-meta-fields">
        <p>
            <label for="project_location"><strong><?php _e( 'Project Location:', 'oceanwp' ); ?></strong></label><br>
            <input type="text" id="project_location" name="project_location" value="<?php echo esc_attr( $project_location ); ?>" class="widefat">
        </p>
        
        <p>
            <label for="project_duration"><strong><?php _e( 'Duration:', 'oceanwp' ); ?></strong></label><br>
            <input type="text" id="project_duration" name="project_duration" value="<?php echo esc_attr( $project_duration ); ?>" class="widefat" placeholder="e.g., April 2024 - March 2026">
        </p>
        
        <p>
            <label for="project_budget"><strong><?php _e( 'Budget:', 'oceanwp' ); ?></strong></label><br>
            <input type="text" id="project_budget" name="project_budget" value="<?php echo esc_attr( $project_budget ); ?>" class="widefat" placeholder="e.g., $100,000">
        </p>
        
        <p>
            <label for="project_donor"><strong><?php _e( 'Donor/Funded By:', 'oceanwp' ); ?></strong></label><br>
            <input type="text" id="project_donor" name="project_donor" value="<?php echo esc_attr( $project_donor ); ?>" class="widefat">
        </p>
        
        <p>
            <label for="project_beneficiaries"><strong><?php _e( 'Target Beneficiaries:', 'oceanwp' ); ?></strong></label><br>
            <input type="text" id="project_beneficiaries" name="project_beneficiaries" value="<?php echo esc_attr( $project_beneficiaries ); ?>" class="widefat" placeholder="e.g., 10,000 households">
        </p>
        
        <p>
            <label for="project_goals"><strong><?php _e( 'Project Goals:', 'oceanwp' ); ?></strong></label><br>
            <textarea id="project_goals" name="project_goals" rows="4" class="widefat"><?php echo esc_textarea( $project_goals ); ?></textarea>
        </p>
        
        <p>
            <label for="project_outcomes"><strong><?php _e( 'Key Outcomes (one per line):', 'oceanwp' ); ?></strong></label><br>
            <textarea id="project_outcomes" name="project_outcomes" rows="6" class="widefat"><?php echo esc_textarea( $project_outcomes ); ?></textarea>
        </p>
    </div>
    
    <style>
        .project-meta-fields p { margin-bottom: 15px; }
        .project-meta-fields label { display: block; margin-bottom: 5px; }
    </style>
    <?php
}

/**
 * Save Project Details Meta Box Data
 */
function oceanwp_save_project_details_meta( $post_id ) {
    // Check if nonce is set
    if ( ! isset( $_POST['project_details_nonce'] ) ) {
        return;
    }
    
    // Verify nonce
    if ( ! wp_verify_nonce( $_POST['project_details_nonce'], 'oceanwp_project_details_nonce' ) ) {
        return;
    }
    
    // Check if autosave
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    
    // Check user permissions
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }
    
    // Save fields
    $fields = array(
        'project_location',
        'project_duration',
        'project_budget',
        'project_donor',
        'project_goals',
        'project_beneficiaries',
        'project_outcomes'
    );
    
    foreach ( $fields as $field ) {
        if ( isset( $_POST[ $field ] ) ) {
            update_post_meta( $post_id, '_' . $field, sanitize_text_field( $_POST[ $field ] ) );
        }
    }
}
add_action( 'save_post_ngo_project', 'oceanwp_save_project_details_meta' );

/**
 * Flush rewrite rules on theme activation
 */
function oceanwp_projects_rewrite_flush() {
    oceanwp_register_projects_post_type();
    oceanwp_register_project_status_taxonomy();
    oceanwp_register_project_category_taxonomy();
    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'oceanwp_projects_rewrite_flush' );

/**
 * Enqueue Projects Styles and Scripts
 */
function oceanwp_enqueue_projects_assets() {
    // Enqueue projects CSS
    wp_enqueue_style(
        'oceanwp-ngo-projects',
        OCEANWP_THEME_URI . '/assets/css/ngo-projects.css',
        array(),
        '1.0.0'
    );
    
    // Enqueue jQuery if not already loaded
    wp_enqueue_script( 'jquery' );
}
add_action( 'wp_enqueue_scripts', 'oceanwp_enqueue_projects_assets' );
