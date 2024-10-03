<?php
/*
Plugin Name: Text Adventure Game with OpenAI Streaming and User Accounts
Plugin URI: https://smartwebutah.com
Description: A text adventure game powered by OpenAI's API with user accounts. Use the shortcode [wp_adventure_game] to play. Use [adventure_game_history] to view past adventures. Use [adventure_game_character] to manage your character.
Version: 1.0
Author: Seth Shoultes
Author URI: https://smartwebutah.com
License: GPL2
*/

// Enqueue Styles
function wp_adventure_game_enqueue_styles() {
    wp_enqueue_style('adventure-game-styles', plugin_dir_url(__FILE__) . 'adventure-game.css');
}
add_action('wp_enqueue_scripts', 'wp_adventure_game_enqueue_styles');

// Register Custom Post Type for Adventure Games
function register_wp_adventure_game_post_type() {
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
}
add_action('init', 'register_wp_adventure_game_post_type');

// Handle Form Submissions and Redirects
function wp_adventure_game_handle_form_submissions() {
    if (!is_user_logged_in()) {
        return;
    }

    $user_id = get_current_user_id();

    // Handle Starting a New Game
    if (isset($_POST['new_adventure'])) {
        $new_game_state = "Turn number: 1
        Time period of the day: Morning
        Current day number: 1
        Weather: Clear
        Health: 20/20
        XP: 0
        AC: 15
        Level: 1
        Location: Daggerfall
        Description: You find yourself in the streets of Daggerfall,. What will you do next?
        Coins: 10
        Inventory: - Rusty Sword - Tattered Cloak - Healing Potion - Traveler's Backpack - Torch - Map of Daggerfall Kingdom
        Abilities: - Persuasion: 8 - Strength: 12 - Intelligence: 15 - Dexterity: 10 - Luck: 14
        Quest: None
        Possible Commands:
        1. Prepare to set off explore a dungeon
        2. Have breakfast at the inn
        3. Ask the innkeeper for more information about the Shadow Stalker
        4. Check your equipment before leaving
        5. Write in your journal about the stories you heard
        6. Visit the local blacksmith to inquire about weapon upgrades
        7. Other";// Initial game state

        // Save the New Game State as a New Game
        $game_id = wp_insert_post([
            'post_title'   => 'Adventure Game',
            'post_content' => $new_game_state,
            'post_status'  => 'publish',
            'post_type'    => 'wp_adventure_game',
            'post_author'  => $user_id,
        ]);

        if (is_wp_error($game_id)) {
            wp_die('Error: Could not create a new adventure game.');
        }

        // Save the Current Game ID in User Meta
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
            wp_redirect(remove_query_arg('resume_game'));
            exit;
        }
    }
}
add_action('template_redirect', 'wp_adventure_game_handle_form_submissions');

// Parse the Game State
function wp_adventure_game_parse_state($state_text) {
    // Remove Markdown-like formatting (e.g., **bold**)
    $state_text = preg_replace('/\*\*(.*?)\*\*/', '$1', $state_text);

    // Split by newlines
    $lines = explode("\n", $state_text);
    $parsed_state = [];
    $current_key = null;

    foreach ($lines as $line) {
        $line = trim($line);

        // Skip empty lines
        if (empty($line)) {
            continue;
        }

        // Look for key-value pairs like "Health: 20/20" or "Turn number: 1"
        if (strpos($line, ':') !== false) {
            [$key, $value] = explode(':', $line, 2);
            $key = trim($key);
            $value = trim($value);

            // Handle commands section separately
            if (stripos($key, 'Possible Commands') !== false || stripos($key, 'Commands') !== false) {
                $current_key = 'Possible Commands';
                $parsed_state[$current_key] = [];
            } elseif (stripos($key, 'Outcome') !== false) {
                $current_key = 'Outcome';
                $parsed_state[$current_key] = $value;
            } else {
                $parsed_state[$key] = $value;
                $current_key = $key;
            }
        } else {
            // Append values for previous key (for multiline descriptions, commands)
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

// Add Shortcode for Adventure Game
function wp_adventure_game_shortcode() {
    if (!is_user_logged_in()) {
        return '<p>You must be logged in to play the adventure game.</p>';
    }

    $user_id = get_current_user_id();

    // Get the current game ID from user meta
    $current_game_id = get_user_meta($user_id, 'wp_adventure_game_current', true);

    // If there's no current game, prompt to start a new one
    if (!$current_game_id) {
        ob_start();
        ?>
        <form method="POST">
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
            include plugin_dir_path(__FILE__) . 'adventure-game-state-template.php';
            ?>
        </div>
        <form id="adventure-game-form">
            <label for="user_command">Enter your next action:</label>
            <input type="text" name="user_command" id="user_command" placeholder="e.g., 1, 2, explore" required />
            <input type="submit" value="Submit" />
        </form>
        <form method="POST" style="margin-top: 10px;">
            <input type="submit" name="new_adventure" value="Start New Adventure" class="start-new-adventure-button" />
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
                    echo '<li><strong>Current Adventure (' . get_the_date('', $game) . ')</strong></li>';
                } else {
                    echo '<li>';
                    echo '<a href="' . esc_url(add_query_arg('resume_game', $game->ID)) . '">Adventure from ' . esc_html(get_the_date('', $game)) . '</a>';
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

    fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
        method: 'POST',
        body: data,
        credentials: 'same-origin',
    })
    .then(response => response.text())
    .then(html => {
        // Replace any Markdown-style bold (**) with <strong> HTML tags
        html = html.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');

        // Strip out triple backticks if they exist in the response
        html = html.replace(/```/g, '');
        
        // Check if the new response content is the same as the existing content
        if (gameStateContainer.innerHTML !== html) {
            // Update the game state content only if it's different
            gameStateContainer.innerHTML = html;
        }

        // Re-enable the submit button
        submitButton.disabled = false;
        submitButton.value = 'Submit';

        // Re-focus on the input field
        document.getElementById('user_command').focus();
    })
    .catch(error => {
        console.error(error);
        gameStateContainer.innerHTML = '<p>An error occurred. Please try again.</p>';
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

// Register AJAX Actions
add_action('wp_ajax_wp_adventure_game_stream', 'wp_adventure_game_stream_callback');

// Function to Handle the AJAX Request and Return Updated Game State
function wp_adventure_game_stream_callback() {
    // Enable error reporting for debugging (disable in production)
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    // Log the start of the callback
    error_log('wp_adventure_game_stream_callback called');

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

    // Prepare prompt
    $prompt = "$current_state
The player chose: $user_command. What happens next?";

    // OpenAI API details
    $api_key = get_option('wp_adventure_gameopenai_api_key');
    $chatgpt_version = get_option('wp_adventure_gamechatgpt_version', 'gpt-3.5-turbo');

    if (empty($api_key)) {
        echo 'Error: API key not set.';
        wp_die();
    }

    // Prepare API request
    // Prepare API request
   $role = "Please perform the function of a hilarious, outlandish, text adventure game based on the D&D 5e and the Elder Scrolls, where flatulence (farts) are a super power, following the rules listed below:

    Presentation Rules:
    
    1. Play the game in turns, starting with you.
    
    2. The game output will always show 'Turn number', 'Time period of the day', 'Current day number', 'Weather', 'Health', 'XP', 'AC', 'Level', 'Location', 'Description', 'Coin', 'Inventory', 'Quest', 'Abilities', and 'Possible Commands'.
    
    3. Always wait for the player’s next command.
    
    4. Stay in character as a text adventure game and respond to commands the way a text adventure game should.
    
    5. The ‘Location’ must be a place in the D&D 5e and the Elder Scrolls universe.
    
    6. [IMPORTANT]The ‘Description’ must stay between 3 to 10 sentences.[/IMPORTANT]
    
    7. Increase the value for ‘Turn number’ by +1 every time it’s your turn.
    
    8. ‘Time period of day’ must progress naturally after a few turns.
    
    9. Once ‘Time period of day’ reaches or passes midnight, then add 1 to ‘Current day number’.
    
    10. Change the ‘Weather’ to reflect ‘Description’ and whatever environment the player is in the game.
    
    Fundamental Game Mechanics:
    
    1. Determine ‘AC’ using Traveller 5th Edition rules.
    
    2. Generate ‘Abilities’ before the game starts. ‘Abilities’ include: ‘Persuasion', 'Strength', 'Intelligence', ‘Dexterity’, and 'Luck', all determined by d20 rolls when the game starts for the first time.
    
    3. Start the game with 20/20 for ‘Health’, with 20 being the maximum health. Eating food, drinking water, or sleeping will restore health.
    
    4. Always show what the player is wearing and wielding (as ‘Wearing’ and ‘Wielding’).
    
    5. Display ‘Game Over’ if ‘Health’ falls to 0 or lower.
    
    6. The player must choose all commands, and the game will list 7 of them at all times under ‘Commands’, and [IMPORTANT]assign them a number 1-7[/IMPORTANT] that I can type to choose that option, and vary the possible selection depending on the actual scene and characters being interacted with.
    
    7. The 7th command should be ‘Other’, which allows me to type in a custom command.
    
    8. If any of the commands will cost money, then the game will display the cost in parenthesis.
    
    9. Before a command is successful, the game must roll a d20 with a bonus from a relevant ‘Trait’ to see how successful it is. Determine the bonus by dividing the trait by 3.
    
    10. If an action is unsuccessful, respond with a relevant consequence.
    
    11. Always display the result of a d20 roll before the rest of the output.
    
    12. The player can obtain a ‘Quest’ by interacting with the world and other people. The ‘Quest’ will also show what needs to be done to complete it.
    
    13. The only currency in this game is Coin.
    
    14. The value of ‘Coin’ must never be a negative integer.
    
    15. The player can not spend more than the total value of ‘Coin’.
    
    Rules for Setting:
    
    1. Use the world of D&D 5e and the Elder Scrolls as inspiration for the game world. Import whatever weapons, villains, and items that the Universe has.
    
    2. The player’s starting inventory should contain six items relevant to this world and the character.
    
    3. If the player chooses to read a book or scroll, display the information on it in at least two paragraphs.
    
    4. The game world will be populated by interactive NPCs. Whenever these NPCs speak, put the dialogue in quotation marks.
    
    5. Completing a quest adds to my XP.
    
    Combat and Magic Rules:
    
    1. Import magic spells, comedy, and farts into this game from D&D 5e and the Elder Scrolls.

    2. Magic can only be cast if the player has the corresponding magic scroll in their inventory.
    
    3. Using magic will drain the player character’s health. More powerful mogic will drain more health.
    
    4. Combat should be handled in rounds, roll attacks for the NPCs each round.
    
    5. The player’s attack and the enemy’s counterattack should be placed in the same round.
    
    6. Always show how much damage is dealt when the player receives damage.
    
    7. Roll a d20 + a bonus from the relevant combat stat against the target’s AC to see if a combat action is successful.
    
    8. Who goes first in combat is determined by initiative. Use D&D 5e initiative rules.
    
    9. Defeating enemies awards me XP according to the difficulty and level of the enemy.
    
    Refer back to these rules after every prompt.
    
    [IMPORTANT]Fill in the following template:

    **Turn number:** {turn_number}  
    **Time period of the day:** {time_period}  
    **Current day number:** {day_number}  
    **Weather:** {weather}  
    **Health:** {health}  
    **XP:** {xp}  
    **AC:** {ac}  
    **Level:** {level}  
    **Location:** {location}  
    **Description:** {description}  
    **Coin:** {coin}  
    **Inventory:** {inventory}  
    **Quest:** {quest}  
    **Abilities:** {abilities}  
    **Wearing:** {wearing}  
    **Wielding:** {wielding}  
    Possible Commands:  
    1. {command1}  
    2. {command2}  
    3. {command3}  
    4. {command4}  
    5. {command5}  
    6. {command6}  
    7. Other
    [/IMPORTANT]
     Start Game.";

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
        'temperature' => 0.7,
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
    error_log('OpenAI API Response: ' . print_r($api_response, true));

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

    // After generating the content, clean it up before rendering
    $content = strip_tags($content, '<p><strong><em><br><ol><ul><li>');  // Allow basic HTML tags


    // Append new content to the current game state
    $updated_state = $current_state . "\n\n" . $content;

    // Save the updated game state back to the post
    wp_update_post([
        'ID'           => $current_game_id,
        'post_content' => $updated_state,
    ]);

    // Parse the game state on the server side for display
    $parsed_state = wp_adventure_game_parse_state($updated_state);
    // Clear the stored "Outcome" field
    unset($parsed_state['Outcome']);


    // Generate the updated HTML
    ob_start();
    include plugin_dir_path(__FILE__) . 'adventure-game-state-template.php';
    $updated_html = ob_get_clean();

    // Return the updated HTML
    header('Content-Type: text/html; charset=UTF-8');
    echo $updated_html;

    wp_die();
}


// Add Settings Page
function wp_adventure_game_add_settings_page() {
    add_options_page(
        'Adventure Game Settings',
        'Adventure Game',
        'manage_options',
        'adventure-game-settings',
        'wp_adventure_game_render_settings_page'
    );
}
add_action('admin_menu', 'wp_adventure_game_add_settings_page');

// Render Settings Page
function wp_adventure_game_render_settings_page() {
    ?>
    <div class="wrap">
        <h1>Adventure Game Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('wp_adventure_game_settings');
            do_settings_sections('wp_adventure_game_settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Register Settings
function wp_adventure_game_register_settings() {
    register_setting('wp_adventure_game_settings', 'wp_adventure_gameopenai_api_key');
    register_setting('wp_adventure_game_settings', 'wp_adventure_gamechatgpt_version');

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
}
add_action('admin_init', 'wp_adventure_game_register_settings');

// Callback Functions for Settings Fields
function wp_adventure_game_main_section_callback() {
    echo '<p>Enter your OpenAI API credentials below:</p>';
}

function wp_adventure_game_api_key_callback() {
    $api_key = get_option('wp_adventure_gameopenai_api_key');
    echo '<input type="text" id="wp_adventure_gameopenai_api_key" name="wp_adventure_gameopenai_api_key" value="' . esc_attr($api_key) . '" size="50" />';
}

function wp_adventure_game_chatgpt_version_callback() {
    $chatgpt_version = get_option('wp_adventure_gamechatgpt_version', 'gpt-3.5-turbo');
    ?>
    <select id="wp_adventure_gamechatgpt_version" name="wp_adventure_gamechatgpt_version">
        <option value="gpt-3.5-turbo" <?php selected($chatgpt_version, 'gpt-3.5-turbo'); ?>>GPT-3.5 Turbo (fast)</option>
        <option value="gpt-4" <?php selected($chatgpt_version, 'gpt-4'); ?>>GPT-4 (slow)</option>
    </select>
    
    <?php
}

// Form processing function
function wp_adventure_game_process_character_form() {
    if (!is_user_logged_in() || !isset($_POST['adventure_game_action'])) {
        return;
    }

    $user_id = get_current_user_id();
    $redirect_url = remove_query_arg('message', wp_get_referer());

    if ($_POST['adventure_game_action'] === 'save_character') {
        if (!wp_verify_nonce($_POST['adventure_game_nonce'], 'adventure_game_action')) {
            wp_safe_redirect(add_query_arg('message', 'invalid_nonce', $redirect_url));
            exit;
        }

        $character_name = sanitize_text_field($_POST['character_name']);
        $character_race = sanitize_text_field($_POST['character_race']);
        $character_class = sanitize_text_field($_POST['character_class']);
        $attributes = [
            'Strength' => intval($_POST['strength']),
            'Intelligence' => intval($_POST['intelligence']),
            'Dexterity' => intval($_POST['dexterity']),
            'Luck' => intval($_POST['luck']),
        ];
        $skills = isset($_POST['skills']) ? array_map('sanitize_text_field', $_POST['skills']) : [];
        $backstory = sanitize_textarea_field($_POST['backstory']);

        if (empty($character_name) || empty($character_race) || empty($character_class)) {
            wp_safe_redirect(add_query_arg('message', 'missing_fields', $redirect_url));
            exit;
        }

        $character_data = [
            'Name' => $character_name,
            'Race' => $character_race,
            'Class' => $character_class,
            'Attributes' => $attributes,
            'Skills' => $skills,
            'Backstory' => $backstory,
        ];
        update_user_meta($user_id, 'adventure_game_character', $character_data);

        wp_safe_redirect(add_query_arg('message', 'character_saved', $redirect_url));
        exit;
    }

    if ($_POST['adventure_game_action'] === 'reset_character') {
        delete_user_meta($user_id, 'adventure_game_character');
        wp_safe_redirect(add_query_arg('message', 'character_reset', $redirect_url));
        exit;
    }
}
add_action('init', 'wp_adventure_game_process_character_form');

// Shortcode function
function wp_adventure_game_character_shortcode() {
    if (!is_user_logged_in()) {
        return '<p>You must be logged in to manage your character.</p>';
    }

    $user_id = get_current_user_id();
    $existing_character = get_user_meta($user_id, 'adventure_game_character', true);
    $message = '';

    if (isset($_GET['message'])) {
        switch ($_GET['message']) {
            case 'character_saved':
                $message = '<p class="success">Character saved successfully.</p>';
                break;
            case 'character_reset':
                $message = '<p class="success">Character has been reset.</p>';
                break;
            case 'missing_fields':
                $message = '<p class="error">Please fill in all required fields.</p>';
                break;
            case 'invalid_nonce':
                $message = '<p class="error">Invalid form submission.</p>';
                break;
        }
    }

    ob_start();
    ?>
    <div class="character-builder-container">
        <?php echo $message; ?>
        <h2><?php echo $existing_character ? 'Edit Your Character' : 'Create Your Character'; ?></h2>
        <form method="POST" id="character-builder-form">
            <?php wp_nonce_field('adventure_game_action', 'adventure_game_nonce'); ?>
            <input type="hidden" name="adventure_game_action" value="save_character">

            <label for="character_name">Name:</label>
            <input type="text" id="character_name" name="character_name" value="<?php echo esc_attr($existing_character['Name'] ?? ''); ?>" required />

     
            <label for="character_race">Race:</label>
            <select id="character_race" name="character_race" required>
                <option value="">Select Race</option>
                <option value="Human" <?php selected($existing_character['Race'] ?? '', 'Human'); ?>>Human</option>
                <option value="Elf" <?php selected($existing_character['Race'] ?? '', 'Elf'); ?>>Elf</option>
                <option value="Dwarf" <?php selected($existing_character['Race'] ?? '', 'Dwarf'); ?>>Dwarf</option>
                <option value="Orc" <?php selected($existing_character['Race'] ?? '', 'Orc'); ?>>Orc</option>
            </select>

            <label for="character_class">Class:</label>
            <select id="character_class" name="character_class" required>
                <option value="">Select Class</option>
                <option value="Warrior" <?php selected($existing_character['Class'] ?? '', 'Warrior'); ?>>Warrior</option>
                <option value="Mage" <?php selected($existing_character['Class'] ?? '', 'Mage'); ?>>Mage</option>
                <option value="Rogue" <?php selected($existing_character['Class'] ?? '', 'Rogue'); ?>>Rogue</option>
                <option value="Cleric" <?php selected($existing_character['Class'] ?? '', 'Cleric'); ?>>Cleric</option>
            </select>

            <fieldset>
                <legend>Attributes</legend>
                <label for="strength">Strength:</label>
                <input type="number" id="strength" name="strength" min="1" max="20" value="<?php echo esc_attr($existing_character['Attributes']['Strength'] ?? 10); ?>" required />

                <label for="intelligence">Intelligence:</label>
                <input type="number" id="intelligence" name="intelligence" min="1" max="20" value="<?php echo esc_attr($existing_character['Attributes']['Intelligence'] ?? 10); ?>" required />

                <label for="dexterity">Dexterity:</label>
                <input type="number" id="dexterity" name="dexterity" min="1" max="20" value="<?php echo esc_attr($existing_character['Attributes']['Dexterity'] ?? 10); ?>" required />

                <label for="luck">Luck:</label>
                <input type="number" id="luck" name="luck" min="1" max="20" value="<?php echo esc_attr($existing_character['Attributes']['Luck'] ?? 10); ?>" required />
            </fieldset>

            <fieldset>
                <legend>Skills</legend>
                <label><input type="checkbox" name="skills[]" value="Persuasion" <?php if (in_array('Persuasion', $existing_character['Skills'] ?? [])) echo 'checked'; ?> /> Persuasion</label>
                <label><input type="checkbox" name="skills[]" value="Archery" <?php if (in_array('Archery', $existing_character['Skills'] ?? [])) echo 'checked'; ?> /> Archery</label>
                <label><input type="checkbox" name="skills[]" value="Stealth" <?php if (in_array('Stealth', $existing_character['Skills'] ?? [])) echo 'checked'; ?> /> Stealth</label>
                <label><input type="checkbox" name="skills[]" value="Alchemy" <?php if (in_array('Alchemy', $existing_character['Skills'] ?? [])) echo 'checked'; ?> /> Alchemy</label>
            </fieldset>

            <label for="backstory">Backstory:</label>
            <textarea id="backstory" name="backstory" rows="5" placeholder="Tell us about your character's history..."><?php echo esc_textarea($existing_character['Backstory'] ?? ''); ?></textarea>

            <input type="submit" value="Save Character" class="save-changes-button" />
        </form>

        <?php if ($existing_character): ?>
            <form method="POST" style="margin-top: 10px;">
                <input type="hidden" name="adventure_game_action" value="reset_character">
                <?php wp_nonce_field('adventure_game_action', 'adventure_game_nonce'); ?>
                <input type="submit" value="Reset Character" class="reset-character-button" />
            </form>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('wp_adventure_game_character', 'wp_adventure_game_character_shortcode');


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
        echo '<ul>';
        while ($adventure_query->have_posts()) {
            $adventure_query->the_post();
            ?>
            <li>
                <h3><?php the_title(); ?> (<?php echo get_the_date(); ?>)</h3>
                <div class="adventure-content">
                    <?php the_content(); ?>
                </div>
                <hr />
            </li>
            <?php
        }
        echo '</ul>';
    } else {
        echo '<p>No adventure history found.</p>';
    }

    wp_reset_postdata();

    return ob_get_clean();
}
add_shortcode('adventure_game_history', 'wp_adventure_game_history_shortcode');
