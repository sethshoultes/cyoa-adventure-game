<?php
/*
Plugin Name: Text Adventure Game with OpenAI Streaming and User Accounts
Plugin URI: https://github.com/sethshoultes/cyoa-adventure-game
Description: A text adventure game powered by OpenAI's API with user accounts. Use the shortcode [wp_adventure_game] or [wp_adventure_game role_id=64 game_state_id=66] to play. Use [adventure_game_history] to view past adventures. Starting games using a custom game state and role is possible using URL parameters ?new_adventure=1&role_id=124&game_state_id=123. This plugin requires an OpenAI API key to function.
Version: 1.1.5
Author: Seth Shoultes
Author URI: https://adventurebuildr.com/
License: GPL2
*/
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
/**
 * Initialize the plugin update checker.
 */
function wp_adventure_game_plugin_auto_update() {
    // Include the library if it's not already included
    if ( ! class_exists( '\\YahnisElsts\\PluginUpdateChecker\\PluginUpdateChecker' ) ) {
        require_once plugin_dir_path( __FILE__ ) . 'includes/plugin-update-checker/plugin-update-checker.php';
    }

    // Replace these variables with your own repository details
    $github_username   = 'sethshoultes';
    $github_repository = 'cyoa-adventure-game';
    $plugin_slug       = 'cyoa-adventure-game'; // This should match the plugin's folder name

    // Initialize the update checker
    $updateChecker = PucFactory::buildUpdateChecker(
        "https://github.com/{$github_username}/{$github_repository}/",
        __FILE__,
        $plugin_slug
    );

    /*
     * Create a new release using the "Releases" feature on GitHub. The tag name and release title don't matter. 
     * The description is optional, but if you do provide one, it will be displayed when the user clicks the 
     * "View version x.y.z details" link on the "Plugins" page. Note that PUC ignores releases marked as 
     * "This is a pre-release".
     *
     * If you want to use release assets, call the enableReleaseAssets() method after creating the update checker instance:
     */
    //$updateChecker->getVcsApi()->enableReleaseAssets();

    // Optional: Set the branch that contains the stable release
    $updateChecker->setBranch('main'); // Change 'main' to the branch you use

    // Optional: If your repository is private, add your access token
    // $updateChecker->setAuthentication('your_github_access_token');
}
add_action( 'init', 'wp_adventure_game_plugin_auto_update' );

// Enqueue Styles
function wp_adventure_game_enqueue_styles() {
    wp_enqueue_style('adventure-game-styles', plugins_url('assets/adventure-game.css', __FILE__));
}
add_action('wp_enqueue_scripts', 'wp_adventure_game_enqueue_styles');
add_action('enqueue_block_editor_assets', 'wp_adventure_game_enqueue_styles');

require_once plugin_dir_path( __FILE__ ) . 'includes/default-game-instructions.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/audio.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/audio-file-cleanup.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/settings.php';

// Add this new function to handle creating a new game based on URL parameters
function wp_adventure_game_handle_new_game_via_url() {
    // Only proceed if the user is logged in
    if (!is_user_logged_in()) {
        return;
    }

    // Check if the 'new_adventure' parameter is set in the URL
    if (isset($_GET['new_adventure'])) {
        $user_id = get_current_user_id();

        // Get the game_state_id and role_id from URL parameters if provided
        $game_state_id = isset($_GET['game_state_id']) ? intval($_GET['game_state_id']) : null;
        $role_id = isset($_GET['role_id']) ? intval($_GET['role_id']) : null;

        // Create a new game with the provided or default parameters
        wp_adventure_game_create_new_game($user_id, $game_state_id, $role_id);

        // After creating the game, redirect to remove query parameters
        $redirect_url = remove_query_arg(['new_adventure', 'game_state_id', 'role_id']);

        // Ensure that no output has been sent before redirecting
        if (!headers_sent()) {
            wp_safe_redirect($redirect_url);
            exit;
        }
    }
}
// Hook the function into 'template_redirect' so it runs before the template is loaded
add_action('template_redirect', 'wp_adventure_game_handle_new_game_via_url');

// Handle Form Submissions and Redirects
function wp_adventure_game_handle_form_submissions() {
    if (!is_user_logged_in()) {
        return;
    }

    $user_id = get_current_user_id();

    // Handle Starting a New Game
    if (isset($_POST['new_adventure'])) {
        // Check if custom parameters are passed via the shortcode
        $game_state_id = isset($_POST['game_state']) ? intval($_POST['game_state']) : null;
        $role_id = isset($_POST['role']) ? intval($_POST['role']) : null;//Not currently using roles in this function

        // Check if the game state ID exists and is valid, otherwise use the default constant
        if ($game_state_id) {
            $game_state_post = get_post($game_state_id);
            if ($game_state_post && $game_state_post->post_type === 'game_state') {
                $game_state_post = get_post($game_state_id);
                //$new_game_state = $game_state_post->post_content;
                $game_state_post = $game_state_post ? $game_state_post->post_content : WP_ADVENTURE_GAME_DEFAULT_STATE;
            } else {
                $game_state_post = WP_ADVENTURE_GAME_DEFAULT_STATE; // Fallback
            }
        }else {
            $game_state_post = WP_ADVENTURE_GAME_DEFAULT_STATE; // Fallback
        }

        // Check if the role ID exists and is valid, otherwise use the default constant
        if ($role_id) {
            $role_post = get_post($role_id);
            if ($role_post && $role_post->post_type === 'game_role') {
                $role_post = $role_post->post_content;
            } else {
                $role_post = WP_ADVENTURE_GAME_DEFAULT_ROLE; // Fallback to default role
            }
        } else {
            $role_post = WP_ADVENTURE_GAME_DEFAULT_ROLE; // Fallback to default role
        }

        // Create the new game as a post
        $game_id = wp_insert_post([
            'post_title'   => 'Adventure Game',
            'post_content' => $game_state_post,
            'post_status'  => 'publish',
            'post_type'    => 'wp_adventure_game',
            'post_author'  => $user_id,
            'meta_input'   => [
                'game_state_id' => $game_state_id,
                'role_id'       => $role_id,
            ]
        ]);

        if (is_wp_error($game_id)) {
            wp_die('Error: Could not create a new adventure game.');
        }

        // Save the current game ID in user meta
        update_user_meta($user_id, 'wp_adventure_game_current', $game_id);

        // Redirect to the game page to avoid form resubmission
        wp_redirect(add_query_arg('game', $game_id, get_permalink()));
        exit;
    }

    // Handle Resuming a Game
    if (isset($_GET['resume_game'])) {
        $game_id = intval($_GET['resume_game']);

        // Check if the game belongs to the user
        $game_post = get_post($game_id);
        if ($game_post && $game_post->post_author == $user_id) {
            // Set this game as the current game
            update_user_meta($user_id, 'wp_adventure_game_current', $game_id);

            // Redirect to remove the query parameter
            wp_safe_redirect(remove_query_arg('resume_game'));
            exit;
        }
    }
}
add_action('template_redirect', 'wp_adventure_game_handle_form_submissions');

// Add Shortcode for Adventure Game
function wp_adventure_game_shortcode($atts) {
    // Check if the user is logged in
    if (!is_user_logged_in()) {
        return '<p>You must be logged in to play the adventure game.</p>';
    }
    //Get the current user ID
    $user_id = get_current_user_id();

    // Get the current game ID from user meta
    $current_game_id = get_user_meta($user_id, 'wp_adventure_game_current', true);
    

    // Extract attributes from shortcode
    $atts = shortcode_atts([
        'game_state_id' => null, // Custom game state ID
        'role_id' => null,       // Custom role ID
    ], $atts, 'wp_adventure_game');
    
    // If custom game_state and role are passed, set a flag
    $is_custom_game = !empty($atts['game_state']) && !empty($atts['role']);

     // Handle loading the game based on shortcode attributes
     if ($is_custom_game) {
        // If a custom game state and role are passed, load the custom game

        // Check if a game already exists for this user with the custom game state and role
        $args = [
            'post_type' => 'wp_adventure_game',
            'author' => $user_id,
            'meta_query' => [
                'relation' => 'AND',
                [
                    'key' => 'game_state_id',
                    'value' => $atts['game_state'],
                ],
                [
                    'key' => 'role_id',
                    'value' => $atts['role'],
                ]
            ],
            'posts_per_page' => 1
        ];

        $custom_game = get_posts($args);

        if (!empty($custom_game)) {
            // If a custom game exists, load it
            $current_game_id = $custom_game[0]->ID;
        } else {
            // If no game exists, create a new game with the custom state and role
            return wp_adventure_game_create_new_game($user_id, $atts['game_state'], $atts['role']);
        }
    }
    // If no current game ID is found, use the latest/default
    if (!$current_game_id) {
        // Default logic to start a new game using the default game state and role
        return wp_adventure_game_create_new_game($user_id);
    }

    // Get the current game state
    $current_game_post = get_post($current_game_id);
    $current_state = $current_game_post ? $current_game_post->post_content : '';
    $parsed_state = wp_adventure_game_parse_state($current_state);

    // If there's no current game, prompt to start a new one
    if (!$current_game_id) {
        ob_start();
        ?>
        <form method="POST">
            <input type="hidden" name="game_state" value="<?php echo esc_attr($atts['game_state_id']); ?>" />
            <input type="hidden" name="role" value="<?php echo esc_attr($atts['role_id']); ?>" />
            <input type="submit" name="new_adventure" value="Start New Adventure" class="start-new-adventure-button" />
        </form>
        <?php
        return ob_get_clean();
    }

    // Get the current game state
    $current_game_post = get_post($current_game_id);
    $current_state = $current_game_post ? $current_game_post->post_content : '';
    $parsed_state = wp_adventure_game_parse_state($current_state);
    if (!isset($parsed_state['Possible Commands']) || empty($parsed_state['Possible Commands'])) {
        $parsed_state['Possible Commands'] = ['1. Wait and observe', '2. Explore the surroundings', '3. Rest'];
    }

    // Output the current game state and the form for the next command
    ob_start();
    ?>
    <div class="adventure-game-container">
        <h2>Text Adventure Game</h2>
        <div class="game-state">
            <?php
            $template_path = get_template_directory() . '/adventure-game-state-template.php';
            if (file_exists($template_path)) {
                include $template_path;
            } else {
                include plugin_dir_path(__FILE__) . 'adventure-game-state-template.php';
            }
            ?>
        </div>
        <div class="spinner" style="display: none;">
            <div class="spinner-icon"></div>
            <p>Generating content...</p>
        </div>
        <form id="adventure-game-form">
            <label for="user_command">Enter your next action:</label>
            <input type="text" name="user_command" id="user_command" placeholder="e.g., 1, 2, explore" required />
            <input type="submit" value="Submit" />
        </form>
        <form method="POST" style="margin-top: 10px;">
            <input type="hidden" name="game_state" value="<?php echo esc_attr($atts['game_state_id']); ?>" />
            <input type="hidden" name="role" value="<?php echo esc_attr($atts['role_id']); ?>" />
            <input type="submit" name="new_adventure" value="Start New Adventure" class="start-new-adventure-button" />
        </form>
       
        </form>
            <form method="POST" style="margin-top: 10px;">
            <input type="hidden" name="clear_history" value="1">
            <?php wp_nonce_field('clear_adventure_history', 'clear_history_nonce'); ?>
            <input type="submit" value="Clear Adventure History" class="clear-history-button" onclick="return confirm('Are you sure you want to clear your adventure history? This action cannot be undone.');" />
        </form>
        <h3>Your Past Adventures</h3>
        <?php
        // Get past games
        $args = [
            'post_type'      => 'wp_adventure_game',
            'author'         => $user_id,
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'orderby'        => 'date',
            'order'          => 'DESC',
        ];
        $past_games = get_posts($args);
        if ($past_games) {
            echo '<ul>';
            foreach ($past_games as $game) {
                // Highlight the current game
                if ($game->ID == $current_game_id) {
                    echo '<li><strong>Current Adventure (' . get_the_date('F j, Y g:i a', $game) . ')</strong></li>';
                } else {
                    echo '<li>';
                    echo '<a href="' . esc_url(add_query_arg('resume_game', $game->ID)) . '">Adventure from ' . esc_html(get_the_date('F j, Y g:i a', $game)) . '</a>';
                    echo '</li>';
                }
            }
            echo '</ul>';
        } else {
            echo '<p>No past adventures found.</p>';
        }
        ?>
    </div>

    <script>
    document.getElementById('adventure-game-form').addEventListener('submit', function(e) {
        e.preventDefault();  // Prevent the form from submitting the normal way
        var userCommand = document.getElementById('user_command').value.trim();

        if (userCommand === '') {
            alert('Please enter a command.');
            return;
        }

        // Prepare the data to send
        var data = new FormData();
        data.append('action', 'wp_adventure_game_stream');  // Ensure this matches the registered AJAX action
        data.append('user_command', userCommand);

        // Clear the input field
        document.getElementById('user_command').value = '';

        // Disable the submit button to prevent multiple submissions
        var submitButton = document.querySelector('#adventure-game-form input[type="submit"]');
        submitButton.disabled = true;
        submitButton.value = 'Processing...';

        // Prepare the game state display
        var gameStateContainer = document.querySelector('.game-state');
        if (!gameStateContainer) {
            console.error('Element with class "game-state" not found.');
            // Re-enable the submit button
            submitButton.disabled = false;
            submitButton.value = 'Submit';
            return;
        }

        // Show the spinner
        var spinner = document.querySelector('.spinner');
        spinner.style.display = 'flex';


        fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
            method: 'POST',
            body: data,
            credentials: 'same-origin',
        })
        .then(response => response.json())
        .then(data => {
        if (data.success) {
            var html = data.data.html;
            var audioUrl = data.data.audio_url;

            // Update the game state content
            gameStateContainer.innerHTML = html;

            // Update the audio player
            if (audioUrl) {
                var audioPlayer = document.getElementById('game-audio-player');
                if (audioPlayer) {
                    audioPlayer.src = audioUrl;
                    // Optionally, play the audio automatically if allowed
                    audioPlayer.play().catch(function(error) {
                        // Autoplay might be blocked; handle errors if needed
                        console.log('Autoplay was prevented:', error);
                    });
                } else {
                    console.error('Audio player element not found.');
                }
            }

            // Hide the spinner
            spinner.style.display = 'none';

            // Re-enable the submit button
            submitButton.disabled = false;
            submitButton.value = 'Submit';

            // Re-focus on the input field
            document.getElementById('user_command').focus();
        } else {
            // Handle error
            console.error('Error:', data.data);
            gameStateContainer.innerHTML = '<p>An error occurred. Please try again.</p>';
            // Hide the spinner
            spinner.style.display = 'none';
            // Re-enable the submit button
            submitButton.disabled = false;
            submitButton.value = 'Submit';
        }
    })
            .catch(error => {
                console.error(error);
                gameStateContainer.innerHTML = '<p>An error occurred. Please try again.</p>';
                // Hide the spinner
                spinner.style.display = 'none';
                // Re-enable the submit button
                submitButton.disabled = false;
                submitButton.value = 'Submit';
            });
        });

        // Event listener for command buttons using event delegation
        document.addEventListener('click', function(e) {
            if (e.target && e.target.classList.contains('game-command-button')) {
                var commandText = e.target.textContent.trim();
                // Set the command in the input field
                var userCommandInput = document.getElementById('user_command');
                if (userCommandInput) {
                    userCommandInput.value = commandText;
                    // Submit the form
                    document.getElementById('adventure-game-form').dispatchEvent(new Event('submit', { cancelable: true }));
                } else {
                    console.error('Input field with id "user_command" not found.');
                }
            }
    });
</script>

    <?php
    return ob_get_clean();
}
add_shortcode('wp_adventure_game', 'wp_adventure_game_shortcode');

// Function to create a new game post
function wp_adventure_game_create_new_game($user_id, $game_state_id = null, $role_id = null) {
    // Fetch game state
    if ($game_state_id) {
        $game_state_post = get_post($game_state_id);
        $new_game_state = $game_state_post ? $game_state_post->post_content : WP_ADVENTURE_GAME_DEFAULT_STATE;
    } else {
        $new_game_state = WP_ADVENTURE_GAME_DEFAULT_STATE;
    }

    // Fetch role
    if ($role_id) {
        $role_post = get_post($role_id);
        $role = $role_post ? $role_post->post_content : WP_ADVENTURE_GAME_DEFAULT_ROLE;
    } else {
        $role = WP_ADVENTURE_GAME_DEFAULT_ROLE;
    }

    // Create new game post
    $game_id = wp_insert_post([
        'post_title'   => 'Adventure Game',
        'post_content' => $new_game_state,
        'post_status'  => 'publish',
        'post_type'    => 'wp_adventure_game',
        'post_author'  => $user_id,
        'meta_input'   => [
            'game_state_id' => $game_state_id,
            'role_id'       => $role_id
        ]
    ]);

    if (is_wp_error($game_id)) {
        wp_die('Error: Could not create a new adventure game.');
    }

    // Save the game ID in user meta
    update_user_meta($user_id, 'wp_adventure_game_current', $game_id);

}

// Function to convert Markdown-like content to HTML
function wp_adventure_game_format_game_content($content) {
    // Convert **text** to <strong>text</strong>
    $content = str_replace('**', '<strong>', $content);
    // Replace double space with the closing </strong>
    $content = preg_replace('/\s{2}/', '</strong>', $content);

    // Handle the new line characters as <br> tags
    return nl2br($content);
}

// Parse the Game State
function wp_adventure_game_parse_state($state_text) {
    // Remove Markdown-like formatting (e.g., **bold**)
    $state_text = preg_replace('/\*\*(.*?)\*\*/', '$1', $state_text);

    // Split by newlines
    $lines = preg_split('/\r\n|\r|\n/', $state_text);
    $parsed_state = [];
    $current_key = null;

    foreach ($lines as $line) {
        $line = trim($line);

        // Skip empty lines
        if (empty($line)) {
            continue;
        }

        // Look for key-value pairs like "Health: 20/20" or "Turn number: 1"
        if (preg_match('/^([^\:]+):\s*(.*)$/', $line, $matches)) {
            $key = trim($matches[1]);
            $value = trim($matches[2]);

            // Handle 'Possible Commands' separately
            if (stripos($key, 'Possible Commands') !== false) {
                $current_key = 'Possible Commands';
                $parsed_state[$current_key] = [];
            } else {
                $parsed_state[$key] = $value;
                $current_key = $key;
            }
        } else {
            // Handle multiline values
            if ($current_key) {
                if ($current_key === 'Possible Commands') {
                    $parsed_state[$current_key][] = $line;
                } else {
                    $parsed_state[$current_key] .= " $line";
                }
            }
        }
    }

    return $parsed_state;
}

// Function to Handle the AJAX Request and Return Updated Game State
function wp_adventure_game_stream_callback() {
    // Only allow logged-in users
    if (!is_user_logged_in()) {
        echo 'Error: You must be logged in to play.';
        wp_die();
    }

    // Get current user and game ID
    $user_id = get_current_user_id();
    $current_game_id = get_user_meta($user_id, 'wp_adventure_game_current', true);

    if (!$current_game_id) {
        echo 'Error: No active game found.';
        wp_die();
    }

    // Get current game state
    $current_game_post = get_post($current_game_id);
    if (!$current_game_post || $current_game_post->post_author != $user_id) {
        echo 'Error: Invalid game or permission denied.';
        wp_die();
    }
    $current_state = $current_game_post->post_content;

    // Get user command
    $user_command = isset($_POST['user_command']) ? sanitize_text_field($_POST['user_command']) : '';

    if (empty($user_command)) {
        echo 'Error: No command provided.';
        wp_die();
    }

    // Check if custom game state and role IDs were passed through the form/shortcode
    $game_state_id = isset($_POST['game_state']) ? intval($_POST['game_state']) : null;
    $role_id = isset($_POST['role']) ? intval($_POST['role']) : null;

    // Fallback to default game state and role if no IDs were provided
    if (!$game_state_id) {
        $default_game_state_id = get_option('wp_adventure_game_default_state_id');
        if ($default_game_state_id) {
            $game_state_id = $default_game_state_id;
        }
    }

    if (!$role_id) {
        $default_role_id = get_option('wp_adventure_game_default_role_id');
        if ($default_role_id) {
            $role_id = $default_role_id;
        }
    }

    // Check if the role ID exists and is valid, otherwise use the default constant
    if ($role_id) {
        $role_post = get_post($role_id);
        if ($role_post && $role_post->post_type === 'role') {
            $role = $role_post->post_content;
        } else {
            // Default role as fallback
            $role = WP_ADVENTURE_GAME_DEFAULT_ROLE;
        }
    } else {
        // Default role as fallback
        $role = WP_ADVENTURE_GAME_DEFAULT_ROLE;
    }

    // Prepare the prompt with the selected game state and user command
    $prompt = "$current_state\n\nThe player chose: $user_command. What happens next?";

    // OpenAI API details
    $api_key = get_option('wp_adventure_gameopenai_api_key');
    $chatgpt_version = get_option('wp_adventure_gamechatgpt_version', 'gpt-3.5-turbo');

    if (empty($api_key)) {
        echo 'Error: API key not set.';
        wp_die();
    }

    // Prepare API request data
    $postData = [
        'model' => $chatgpt_version,
        'messages' => [
            [
                'role' => 'system',
                'content' => $role
            ],
            [
                'role' => 'user',
                'content' => $prompt
            ]
        ],
        'temperature' => 0.5,
        // 'stream' => true, // Removed for non-streaming
    ];

    // Execute the cURL request
    $ch = curl_init('https://api.openai.com/v1/chat/completions');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        "Authorization: Bearer $api_key",
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Get the response as a string

    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        $error_msg = curl_error($ch);
        echo "cURL Error: $error_msg";
        error_log("cURL Error: $error_msg");
        wp_die();
    }
    curl_close($ch);

    // Decode the JSON response
    $api_response = json_decode($response, true);

    // Log the API response for debugging
    //error_log('OpenAI API Response: ' . print_r($api_response, true));

    // Check for errors in the response
    if (isset($api_response['error'])) {
        echo 'OpenAI API Error: ' . esc_html($api_response['error']['message']);
        wp_die();
    }

    // Extract the content
    // Check that the response contains valid content
    if (isset($api_response['choices'][0]['message']['content'])) {
        $content = $api_response['choices'][0]['message']['content'];
        // Clean up the content before sending it to the frontend
        $content = strip_tags($content, '<p><strong><em><br><ol><ul><li>');  // Allow basic HTML tags
    } else {
        echo 'Error: Invalid response from OpenAI API.';
        error_log('Error: Invalid response from OpenAI API.');
        wp_die();
    }

    // Parse the new content to extract 'Description' field
    $parsed_new_content = wp_adventure_game_parse_state($content);

    // Extract the 'Description' field from the new content
    $description_text = isset($parsed_new_content['Description']) ? $parsed_new_content['Description'] : '';

    // Now, generate the audio using the 'Description' field
    if (!empty($description_text)) {
        $audio_url = wp_adventure_game_generate_audio($description_text);
    } else {
        $audio_url = null;
    }

    // Append new content to the current game state
    $updated_state = $current_state . "\n\n" . $content;

    // Save the updated game state back to the post
    wp_update_post([
        'ID'           => $current_game_id,
        'post_content' => $updated_state,
    ]);

    // Parse the game state on the server side for display
    $parsed_state = wp_adventure_game_parse_state($updated_state);

    // Generate the updated HTML
    ob_start();
    $template_path = get_template_directory() . '/adventure-game-state-template.php';
    if (file_exists($template_path)) {
        include $template_path;
    } else {
        include plugin_dir_path(__FILE__) . 'adventure-game-state-template.php';
    }
    $updated_html = ob_get_clean();


    // Prepare the response data
    $response_data = [
        'html' => $updated_html,
        'audio_url' => $audio_url,
    ];

    // Return the response as JSON
    wp_send_json_success($response_data);
}

// Register AJAX Actions
add_action('wp_ajax_wp_adventure_game_stream', 'wp_adventure_game_stream_callback');


// Shortcode to display the adventure history
function wp_adventure_game_history_shortcode() {
    if (!is_user_logged_in()) {
        return '<p>You must be logged in to view your adventure history.</p>';
    }

    // Get the current user ID
    $user_id = get_current_user_id();

    // Query all adventure posts for this user
    $args = [
        'post_type'      => 'wp_adventure_game',
        'author'         => $user_id,
        'posts_per_page' => -1, // Retrieve all adventures
        'post_status'    => 'publish',
        'orderby'        => 'date',
        'order'          => 'DESC',
    ];
    $adventure_query = new WP_Query($args);

    ob_start();

    if ($adventure_query->have_posts()) {
        echo '<h2>Your Adventure History</h2>';
        echo '<div class="adventure-history">';
        while ($adventure_query->have_posts()) {
            $adventure_query->the_post();
            ?>
            <div class="adventure-item">
                <h3><?php the_title(); ?></h3>
                <p class="adventure-date"><?php echo get_the_date('F j, Y  g:i a'); ?> at <?php echo get_the_time('g:i a'); ?></p>
                <div class="adventure-content">
                    <?php the_content(); ?>
                </div>
            </div>
            <?php
        }
        echo '</div>';
    } else {
        echo '<p>No adventure history found.</p>';
    }

    wp_reset_postdata();

    return ob_get_clean();
}
add_shortcode('adventure_game_history', 'wp_adventure_game_history_shortcode');

// Clear Adventure History
function wp_adventure_game_clear_history() {
    if (isset($_POST['clear_history']) && wp_verify_nonce($_POST['clear_history_nonce'], 'clear_adventure_history')) {
        $user_id = get_current_user_id();

        $args = [
            'post_type'      => 'wp_adventure_game',
            'author'         => $user_id,
            'posts_per_page' => -1,
            'post_status'    => 'publish',
        ];

        $posts = get_posts($args);

        foreach ($posts as $post) {
            wp_delete_post($post->ID, true);
        }

        wp_safe_redirect(remove_query_arg('clear_history'));
        exit;
    }
}
add_action('template_redirect', 'wp_adventure_game_clear_history');

// Register the block
function wp_adventure_game_register_block() {
    if (!function_exists('register_block_type')) {
        return;
    }

    wp_register_script(
        'wp-adventure-game-block-editor',
        plugins_url('assets/block.js', __FILE__),
        array('wp-blocks', 'wp-element', 'wp-components', 'wp-editor'),
        filemtime(plugin_dir_path(__FILE__) . 'assets/block.js')
    );

    register_block_type('cyoa-adventure-game/adventure-game', array(
        'editor_script' => 'wp-adventure-game-block-editor',
        'render_callback' => 'wp_adventure_game_shortcode'
    ));
    
    register_block_type('cyoa-adventure-game/adventure-game-history', array(
        'editor_script' => 'wp-adventure-game-block-editor',
        'render_callback' => 'wp_adventure_game_history_shortcode'
    ));
}
add_action('init', 'wp_adventure_game_register_block');

// Enqueue block editor assets
function wp_adventure_game_enqueue_block_editor_assets() {
    wp_enqueue_script(
        'wp-adventure-game-block-editor',
        plugins_url('assets/block.js', __FILE__),
        array('wp-blocks', 'wp-element', 'wp-components', 'wp-editor'),
        filemtime(plugin_dir_path(__FILE__) . 'assets/block.js')
    );

    wp_enqueue_style(
        'wp-adventure-game-block-editor-style',
        plugins_url('assets/editor-styles.css', __FILE__),
        array(),
        filemtime(plugin_dir_path(__FILE__) . 'assets/editor-styles.css')
    );
}
add_action('enqueue_block_editor_assets', 'wp_adventure_game_enqueue_block_editor_assets');