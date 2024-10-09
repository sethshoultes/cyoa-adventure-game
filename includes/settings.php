<?php
// Define the parent menu slug
define('ADVENTURE_GAME_MENU_SLUG', 'adventure-game-menu');

// Add the top-level menu and dashboard page
// Add the top-level menu and dashboard page
function wp_adventure_game_add_admin_menu() {
    add_menu_page(
        'Adventure Game',                   // Page title
        'Adventure Game',                   // Menu title
        'manage_options',                   // Capability
        ADVENTURE_GAME_MENU_SLUG,           // Menu slug
        'wp_adventure_game_render_dashboard_page', // Callback function
        'dashicons-book',                   // Icon URL
        6                                   // Position
    );

    // Add the dashboard page as a submenu
    add_submenu_page(
        ADVENTURE_GAME_MENU_SLUG,           // Parent slug
        'Dashboard',                        // Page title
        'Dashboard',                        // Menu title
        'manage_options',                   // Capability
        ADVENTURE_GAME_MENU_SLUG,           // Menu slug (same as top-level menu slug)
        'wp_adventure_game_render_dashboard_page' // Callback function
    );

    // Add settings page under the same menu
    add_submenu_page(
        ADVENTURE_GAME_MENU_SLUG,           // Parent slug
        'Settings',                         // Page title
        'Settings',                         // Menu title
        'manage_options',                   // Capability
        'adventure-game-settings',          // Menu slug
        'wp_adventure_game_render_settings_page' // Callback function
    );
}
add_action('admin_menu', 'wp_adventure_game_add_admin_menu');

// Dashboard page callback function
function wp_adventure_game_render_dashboard_page() {
    echo '<div class="wrap"><h1>Adventure Game Dashboard</h1>';
    echo '<p>Welcome to the Adventure Game plugin dashboard. Use the menu on the left to navigate through settings and game configurations.</p></div>';
}

// Settings page rendering function
function wp_adventure_game_render_settings_page() {
    ?>
    <div class="wrap">
        <h1>Adventure Game Settings</h1>
        <form method="post" action="options.php">
            <?php
            // Output security fields for the registered setting "wp_adventure_game_settings"
            settings_fields('wp_adventure_game_settings');
            // Output setting sections and their fields
            do_settings_sections('wp_adventure_game_settings');
            // Output save settings button
            submit_button();
            ?>
        </form>
        <a href="?run_cleanup" target="_blank" class="button">Run Audio File Cleanup</a>
    </div>
    <?php
}

// Register Settings
function wp_adventure_game_register_settings() {
    register_setting('wp_adventure_game_settings', 'wp_adventure_gameopenai_api_key');
    register_setting('wp_adventure_game_settings', 'wp_adventure_gamechatgpt_version');
    register_setting('wp_adventure_game_settings', 'wp_adventure_gamechatgpt_audio_version');

    add_settings_section(
        'wp_adventure_game_main_section',
        'Main Settings',
        'wp_adventure_game_main_section_callback',
        'wp_adventure_game_settings'
    );

    add_settings_field(
        'wp_adventure_gameopenai_api_key',
        'OpenAI API Key',
        'wp_adventure_game_api_key_callback',
        'wp_adventure_game_settings',
        'wp_adventure_game_main_section'
    );

    add_settings_field(
        'wp_adventure_gamechatgpt_version',
        'ChatGPT Model Version',
        'wp_adventure_game_chatgpt_version_callback',
        'wp_adventure_game_settings',
        'wp_adventure_game_main_section'
    );

    add_settings_field(
        'wp_adventure_gamechatgpt_audio_version',
        'ChatGPT Audio Version',
        'wp_adventure_game_chatgpt_audio_version_callback',
        'wp_adventure_game_settings',
        'wp_adventure_game_main_section'
    );
}
add_action('admin_init', 'wp_adventure_game_register_settings');

// OpenAI Callback Functions for Settings Fields
function wp_adventure_game_main_section_callback() {
    echo '<p>Enter your OpenAI API credentials below:</p>';
}

// OpenAI Settings Fields
function wp_adventure_game_api_key_callback() {
    $api_key = get_option('wp_adventure_gameopenai_api_key');
    echo '<input type="text" id="wp_adventure_gameopenai_api_key" name="wp_adventure_gameopenai_api_key" value="' . esc_attr($api_key) . '" size="50" />';
}

// ChatGPT Version Settings Field
function wp_adventure_game_chatgpt_version_callback() {
    $chatgpt_version = get_option('wp_adventure_gamechatgpt_version', 'gpt-3.5-turbo');
    ?>
    <select id="wp_adventure_gamechatgpt_version" name="wp_adventure_gamechatgpt_version">
        <option value="gpt-3.5-turbo" <?php selected($chatgpt_version, 'gpt-3.5-turbo'); ?>>GPT-3.5 Turbo (fast)</option>
        <option value="gpt-4" <?php selected($chatgpt_version, 'gpt-4'); ?>>GPT-4 (slow)</option>
    </select>
    
    <?php
}

// ChatGPT Audio Version Settings Field
function wp_adventure_game_chatgpt_audio_version_callback() {
    $chatgpt_version = get_option('wp_adventure_gamechatgpt_audio_version', 'alloy');
    ?>
    <select id="wp_adventure_gamechatgpt_audio_version" name="wp_adventure_gamechatgpt_audio_version">
        <option value="alloy" <?php selected($chatgpt_version, 'alloy'); ?>>Alloy</option>
        <option value="echo" <?php selected($chatgpt_version, 'echo'); ?>>Echo</option>
        <option value="fable" <?php selected($chatgpt_version, 'fable'); ?>>Fable</option>
        <option value="onyx" <?php selected($chatgpt_version, 'onyx'); ?>>Onyx</option>
        <option value="nova" <?php selected($chatgpt_version, 'nova'); ?>>Nova</option>
        <option value="shimmer" <?php selected($chatgpt_version, 'shimmer'); ?>>Shimmer</option>
    </select>
    
    <?php
}

// Register CPTs under the top-level menu
function register_adventure_game_cpts() {
    // Register Adventure Game CPT (Hidden from menu)
    register_post_type('wp_adventure_game', [
        'labels' => [
            'name' => 'Adventure Games',
            'singular_name' => 'Adventure Game',
        ],
        'public' => false,
        'has_archive' => false,
        'rewrite' => false,
        'supports' => ['title', 'editor', 'author'],
        'show_in_menu' => false, // Hide from menu
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
        'show_in_menu' => ADVENTURE_GAME_MENU_SLUG,
    ]);

    // Register Game Role CPT
    register_post_type('game_role', [
        'labels' => [
            'name' => 'Game Roles',
            'singular_name' => 'Game Role',
        ],
        'public' => true,
        'has_archive' => false,
        'rewrite' => ['slug' => 'game-role'],
        'supports' => ['title', 'editor'],
        'show_in_menu' => ADVENTURE_GAME_MENU_SLUG,
    ]);
}
add_action('init', 'register_adventure_game_cpts');
