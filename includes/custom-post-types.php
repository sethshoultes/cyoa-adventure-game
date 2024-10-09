<?php
function register_adventure_game_cpts() {
    
    // Register Adventure Game CPT
    //This post type will be used to store the game state for each adventure game.
    register_post_type('wp_adventure_game', [
        'labels' => [
            'name' => 'Adventure Games',
            'singular_name' => 'Adventure Game',
        ],
        'public' => false,
        'has_archive' => false,
        'rewrite' => false,
        'supports' => ['title', 'editor', 'author'],
    ]);
    
    // Register Game State CPT
    register_post_type('game_state', [
        'labels' => [
            'name' => 'Game States',
            'singular_name' => 'Game State',
        ],
        'public' => true,
        'has_archive' => false,
        'rewrite' => ['slug' => 'game-state'],
        'supports' => ['title', 'editor'],
    ]);

    // Register Role CPT
    register_post_type('game_role', [
        'labels' => [
            'name' => 'Game Roles',
            'singular_name' => 'Game Role',
        ],
        'public' => true,
        'has_archive' => false,
        'rewrite' => ['slug' => 'game-role'],
        'supports' => ['title', 'editor'],
    ]);

    
}
add_action('init', 'register_adventure_game_cpts');

// Register Custom Post Type for Game State
function wp_adventure_game_register_custom_post_types() {
    // Game State Custom Post Type
    $game_state_labels = [
        'name' => 'Game States',
        'singular_name' => 'Game State',
        'menu_name' => 'Game States',
        'name_admin_bar' => 'Game State',
        'add_new' => 'Add New',
        'add_new_item' => 'Add New Game State',
        'new_item' => 'New Game State',
        'edit_item' => 'Edit Game State',
        'view_item' => 'View Game State',
        'all_items' => 'All Game States',
        'search_items' => 'Search Game States',
        'not_found' => 'No Game States found.',
    ];

    $game_state_args = [
        'labels' => $game_state_labels,
        'public' => true,
        'show_in_menu' => true,
        'supports' => ['title', 'editor'],
        'menu_icon' => 'dashicons-clipboard',
        'has_archive' => true,
    ];

    register_post_type('game_state', $game_state_args);

    // Role Custom Post Type
    $role_labels = [
        'name' => 'Game Roles',
        'singular_name' => 'Game Role',
        'menu_name' => 'Game Roles',
        'name_admin_bar' => 'Game Role',
        'add_new' => 'Add New',
        'add_new_item' => 'Add New Game Role',
        'new_item' => 'New Game Role',
        'edit_item' => 'Edit Game Role',
        'view_item' => 'View Game Role',
        'all_items' => 'All Game Roles',
        'search_items' => 'Search Game Roles',
        'not_found' => 'No Game Roles found.',
    ];

    $role_args = [
        'labels' => $role_labels,
        'public' => true,
        'show_in_menu' => true,
        'supports' => ['title', 'editor'],
        'menu_icon' => 'dashicons-groups',
        'has_archive' => true,
    ];

    register_post_type('game_role', $role_args);
}
add_action('init', 'wp_adventure_game_register_custom_post_types');


// Function to create default game state and role when the plugin is activated
function wp_adventure_game_create_default_posts() {
    // Check if the default game state already exists
    $default_game_state = get_posts([
        'post_type' => 'game_state',
        'title'     => 'Default Game State',
        'post_status' => 'publish',
        'numberposts' => 1,
    ]);

    if (empty($default_game_state)) {
        // Create default game state
        $new_game_state_id = wp_insert_post([
            'post_title'   => 'Default Game State',
            'post_content' => WP_ADVENTURE_GAME_DEFAULT_STATE,// Initial game state
            'post_status'  => 'publish',
            'post_type'    => 'game_state',
        ]);
    }

    // Check if the default role already exists
    $default_game_role = get_posts([
        'post_type' => 'game_role',
        'title'     => 'Default Game Role',
        'post_status' => 'publish',
        'numberposts' => 1,
    ]);

    if (empty($default_game_role)) {
        // Create default role
        $new_game_role_id = wp_insert_post([
            'post_title'   => 'Default Game Role',
            'post_content' => WP_ADVENTURE_GAME_DEFAULT_ROLE,
            'post_status'  => 'publish',
            'post_type'    => 'game_role',
        ]);
    }
}

// Hook to run the function when the plugin is activated
register_activation_hook(__FILE__, 'wp_adventure_game_create_default_posts');
