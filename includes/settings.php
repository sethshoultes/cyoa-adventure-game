<?php
// Define the parent menu slug
define('ADVENTURE_GAME_MENU_SLUG', 'adventure-game-menu');

// Add the top-level menu
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
        'Usage',                        // Page title
        'Usage',                        // Menu title
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

// Usage page callback function
function wp_adventure_game_render_dashboard_page() {
    ?>
    <div class="wrap">
    <h1>Text Adventure Game with OpenAI Streaming and User Accounts</h1>

    <p>A WordPress plugin that brings an interactive text adventure game to your website, powered by OpenAI's API. Users can play the game, manage their characters, and view their adventure history—all integrated seamlessly with WordPress user accounts.</p>

    <h2>Table of Contents</h2>
    <ul>
        <li><a href="#features">Features</a></li>
        <li><a href="#installation">Installation</a></li>
        <li><a href="#usage">Usage</a>
            <ul>
                <li><a href="#shortcodes">Shortcodes</a></li>
                <li><a href="#creating-a-new-adventure">Creating a New Adventure</a></li>
                <li><a href="#managing-your-character">Managing Your Character</a></li>
                <li><a href="#viewing-adventure-history">Viewing Adventure History</a></li>
            </ul>
        </li>
        <li><a href="#settings">Settings</a></li>
        <li><a href="#contributing">Contributing</a></li>
        <li><a href="#license">License</a></li>
    </ul>

    <h2 id="features">Features</h2>
    <ul>
        <li><strong>Interactive Gameplay</strong>: An engaging text adventure game inspired by D&D 5e and The Elder Scrolls.</li>
        <li><strong>User Accounts</strong>: Each user can start, resume, and manage their own adventures.</li>
        <li><strong>OpenAI Integration</strong>: Powered by OpenAI's GPT models for dynamic storytelling.</li>
        <li><strong>Character Management</strong>: Users can create and customize their own characters.</li>
        <li><strong>Adventure History</strong>: View past adventures and continue where you left off.</li>
        <li><strong>Custom Game States and Roles</strong>: Administrators can define custom game states and roles.</li>
        <li><strong>Shortcodes</strong>: Easy integration with WordPress pages and posts using shortcodes.</li>
        <li><strong>Settings Page</strong>: Configure OpenAI API keys and model versions from the WordPress admin dashboard.</li>
    </ul>

    <h2 id="installation">Installation</h2>
    <ol>
        <li><strong>Configure OpenAI API Key:</strong>
            <ul>
                <li>Navigate to <strong>Settings > Adventure Game</strong> in the WordPress admin dashboard.</li>
                <li>Enter your OpenAI API Key and select the desired ChatGPT model version.</li>
                <li><strong>Note:</strong> You must have an OpenAI API key to use this plugin.</li>
            </ul>
        </li>
    </ol>

    <h2 id="usage">Usage</h2>

    <h3 id="shortcodes">Shortcodes</h3>
    <ul>
        <li><code>[wp_adventure_game]</code>: Displays the adventure game interface.</li>
        <li><code>[adventure_game_history]</code>: Shows the user's past adventures.</li>
        <li><code>[adventure_game_character]</code>: Allows users to manage their character.</li>
    </ul>

    <h3 id="creating-a-new-adventure">Creating a New Adventure</h3>
    <ol>
        <li><strong>Start the Game:</strong>
            <ul>
                <li>Insert the <code>[wp_adventure_game]</code> shortcode into a page or post.</li>
                <li>Users must be logged in to start a new adventure.</li>
            </ul>
        </li>
        <li><strong>Gameplay:</strong>
            <ul>
                <li>The game presents a scenario with possible commands.</li>
                <li>Users enter a command to progress through the adventure.</li>
            </ul>
        </li>
        <li><strong>Saving Progress:</strong>
            <ul>
                <li>The game state is saved automatically after each action.</li>
                <li>Users can resume their game anytime by revisiting the game page.</li>
            </ul>
        </li>
    </ol>

    <h3 id="managing-your-character">Managing Your Character</h3>
    <ol>
        <li><strong>Access the Character Builder:</strong>
            <ul>
                <li>Insert the <code>[adventure_game_character]</code> shortcode into a page or post.</li>
            </ul>
        </li>
        <li><strong>Create or Edit Character:</strong>
            <ul>
                <li>Fill in character details like name, race, class, attributes, skills, and backstory.</li>
                <li>Save the character to update your game profile.</li>
            </ul>
        </li>
    </ol>

    <h3 id="viewing-adventure-history">Viewing Adventure History</h3>
    <ol>
        <li><strong>Access Adventure History:</strong>
            <ul>
                <li>Insert the <code>[adventure_game_history]</code> shortcode into a page or post.</li>
            </ul>
        </li>
        <li><strong>View Past Adventures:</strong>
            <ul>
                <li>Users can see a list of their previous adventures.</li>
                <li>Each adventure displays the date and the content of the game at that point.</li>
            </ul>
        </li>
    </ol>

    <h3 id="clearing-adventure-history">Clearing Adventure History</h3>
    <ul>
        <li>Users can clear their adventure history from the game interface.</li>
        <li><strong>Note:</strong> This action cannot be undone.</li>
    </ul>

    <h2 id="settings">Settings</h2>
    <p>Access the plugin settings by navigating to <strong>Settings > Adventure Game</strong> in the WordPress admin dashboard.</p>
    <ul>
        <li><strong>OpenAI API Key:</strong> Enter your OpenAI API key.</li>
        <li><strong>ChatGPT Model Version:</strong> Choose between <code>gpt-3.5-turbo</code> (fast) and <code>gpt-4</code> (slow).</li>
    </ul>

    <h2 id="contributing">Contributing</h2>
    <p>Contributions are welcome! Please submit a pull request or open an issue to discuss any changes or enhancements.</p>

    <h2 id="license">License</h2>
    <p>This plugin is licensed under the GPL2 license. See the <a href="https://www.gnu.org/licenses/gpl-2.0.html">LICENSE</a> file for details.</p>

    <hr>

    <p><strong>Disclaimer:</strong> This plugin requires an OpenAI API key, which may incur costs based on usage. Please monitor your OpenAI account to avoid unexpected charges.</p>

    <h2>Shortcode Examples</h2>
    <ul></ul>
        <li><strong>Display the Adventure Game:</strong>
            <pre><code>[wp_adventure_game]</code></pre>
        </li>
        <li><strong>Display Adventure History:</strong>
            <pre><code>[adventure_game_history]</code></pre>
        </li>
        <li><strong>Display Character Management Form:</strong>
            <pre><code>[adventure_game_character]</code></pre>
        </li>
    </ul>

    <h2>Screenshots</h2>
    <p><em>(Screenshots coming soon)</em></p>
    <ol>
        <li><strong>Game Interface:</strong> The main adventure game screen where users input commands.</li>
        <li><strong>Character Builder:</strong> The form for creating or editing a character.</li>
        <li><strong>Adventure History:</strong> A list of past adventures with timestamps.</li>
        <li><strong>Settings Page:</strong> The admin settings page for configuring OpenAI API keys.</li>
    </ol>

    <h2>Changelog</h2>
    <h3>Version 1.0</h3>
    <ul></ul>
        <li>Initial release of the Text Adventure Game with OpenAI Streaming and User Accounts plugin.</li>
    </ul>

    <hr>

    <h2>Feedback and Support</h2>
    <p>For issues, suggestions, or contributions, please open an issue on the <a href="https://github.com/sethshoultes/cyoa-adventure-game/issues">GitHub repository</a>.</p>

    <h2>Acknowledgements</h2>
    <ul>
        <li>Inspired by Dungeons & Dragons 5e and The Elder Scrolls series.</li>
        <li>Powered by <a href="https://www.openai.com/">OpenAI</a> GPT models.</li>
    </ul>

    <h2>Roadmap</h2>
    <ul>
        <li><strong>Feature Enhancements:</strong>
            <ul>
                <li>Implement game state serialization for improved performance.</li>
                <li>Add more customization options for game mechanics and rules.</li>
            </ul>
        </li>
        <li><strong>Localization:</strong>
            <ul>
                <li>Provide translations for internationalization.</li>
            </ul>
        </li>
    </ul>

    <h2>Author</h2>
    <p>Developed by <a href="https://adventurebuildr.com/">Seth Shoultes</a>.</p>

    <h2>Disclaimer</h2>
    <p>This is an open-source project provided as-is. The developer is not responsible for any unintended consequences arising from its use.</p>

    <hr>

    <p>Thank you for using the Text Adventure Game with OpenAI Streaming and User Accounts plugin!</p></ul>
    </div>
    <?php
}