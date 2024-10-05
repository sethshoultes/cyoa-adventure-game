# Choose Your Own Adventure Game
Choose Your Own Adventure Text-Based Game with OpenAI Streaming and User Accounts for WordPress

## Introduction

Welcome to the Text Adventure Game with OpenAI Streaming and User Accounts WordPress plugin! This plugin allows you to play a text-based adventure game powered by OpenAI's API, with support for user accounts and customizable game states and roles.

## Installation

1. Download the plugin files and upload them to your WordPress plugins directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Go to the plugin settings page and enter your OpenAI API key and select the desired ChatGPT model version.

## Shortcodes

The plugin provides the following shortcodes:

- `[wp_adventure_game]`: Displays the main adventure game interface. You can optionally pass the `game_state`, `role`, and `game_id` attributes to specify custom game state, role, and game ID.
- `[adventure_game_history]`: Displays the user's adventure game history.
- `[adventure_game_character]`: Allows users to create and manage their game characters.

## Playing the Game

To start playing the game, simply use the `[wp_adventure_game]` shortcode on any page or post. If you're not logged in, you'll be prompted to log in first.

If you haven't started a game yet, you'll see a "Start New Adventure" button. Click on it to begin a new game.

During the game, you'll see the current game state, including your character's stats, inventory, and available commands. To perform an action, either type your command in the input field and click "Submit," or click on one of the provided command buttons.

The game will generate a response based on your action and update the game state accordingly.

## Loading Specific Games

You can load a specific game by passing the `game_id` attribute to the `[wp_adventure_game]` shortcode. For example:

```
[wp_adventure_game game_id="123"]
```

This will load the game with the specified ID. If no `game_id` is provided, the latest game for the user will be loaded or a new game will be created.

## Customizing Game States and Roles

The plugin allows you to create custom game states and roles using the WordPress admin interface.

### Game States

1. Go to the "Game States" menu in the WordPress admin sidebar.
2. Click "Add New" to create a new game state.
3. Enter a title and the desired game state content in the editor.
4. Publish the game state.

### Roles

1. Go to the "Game Roles" menu in the WordPress admin sidebar.
2. Click "Add New" to create a new role.
3. Enter a title and the desired role content in the editor.
4. Publish the role.

To use a custom game state or role, pass the respective ID as the `game_state` or `role` attribute in the `[wp_adventure_game]` shortcode.

## Character Management

Users can create and manage their game characters using the `[adventure_game_character]` shortcode.

They can set their character's name, race, class, attributes, skills, and backstory. The character information will be used during the game to determine the outcomes of certain actions.

## Adventure History

Users can view their past adventures using the `[adventure_game_history]` shortcode. This will display a list of their previous games, along with the date and time they were played.

Users can also clear their adventure history by clicking the "Clear Adventure History" button on the main game page.

## Settings

The plugin settings can be accessed through the WordPress admin menu under "Adventure Game."

Here, you can enter your OpenAI API key and select the desired ChatGPT model version (GPT-3.5 Turbo or GPT-4).

## Conclusion

That's it! You're now ready to embark on exciting text adventures powered by OpenAI. Have fun, and happy gaming!