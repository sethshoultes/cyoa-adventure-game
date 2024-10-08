# Text Adventure Game with OpenAI Streaming and User Accounts

A WordPress plugin that brings an interactive text adventure game to your website, powered by OpenAI's API. Users can play the game, manage their characters, and view their adventure history—all integrated seamlessly with WordPress user accounts.

## Table of Contents

- [Features](#features)
- [Installation](#installation)
- [Usage](#usage)
  - [Shortcodes](#shortcodes)
  - [Creating a New Adventure](#creating-a-new-adventure)
  - [Managing Your Character](#managing-your-character)
  - [Viewing Adventure History](#viewing-adventure-history)
- [Settings](#settings)
- [Contributing](#contributing)
- [License](#license)

## Features

- **Interactive Gameplay**: An engaging text adventure game inspired by D&D 5e and The Elder Scrolls.
- **User Accounts**: Each user can start, resume, and manage their own adventures.
- **OpenAI Integration**: Powered by OpenAI's GPT models for dynamic storytelling.
- **Character Management**: Users can create and customize their own characters.
- **Adventure History**: View past adventures and continue where you left off.
- **Custom Game States and Roles**: Administrators can define custom game states and roles.
- **Shortcodes**: Easy integration with WordPress pages and posts using shortcodes.
- **Settings Page**: Configure OpenAI API keys and model versions from the WordPress admin dashboard.

## Installation

1. **Download the Plugin:**
   - Clone or download this repository to your local machine.

2. **Upload to WordPress:**
   - Upload the plugin files to the `/wp-content/plugins/` directory of your WordPress installation.

3. **Activate the Plugin:**
   - Go to the **Plugins** menu in WordPress and activate the **Text Adventure Game with OpenAI Streaming and User Accounts** plugin.

4. **Configure OpenAI API Key:**
   - Navigate to **Settings > Adventure Game** in the WordPress admin dashboard.
   - Enter your OpenAI API Key and select the desired ChatGPT model version.
   - **Note**: You must have an OpenAI API key to use this plugin.

## Usage

### Shortcodes

- `[wp_adventure_game]`: Displays the adventure game interface.
- `[adventure_game_history]`: Shows the user's past adventures.
- `[adventure_game_character]`: Allows users to manage their character.

### Creating a New Adventure

1. **Start the Game:**
   - Insert the `[wp_adventure_game]` shortcode into a page or post.
   - Users must be logged in to start a new adventure.
2. **Gameplay:**
   - The game presents a scenario with possible commands.
   - Users enter a command to progress through the adventure.
3. **Saving Progress:**
   - The game state is saved automatically after each action.
   - Users can resume their game anytime by revisiting the game page.

### Managing Your Character

1. **Access the Character Builder:**
   - Insert the `[adventure_game_character]` shortcode into a page or post.
2. **Create or Edit Character:**
   - Fill in character details like name, race, class, attributes, skills, and backstory.
   - Save the character to update your game profile.

### Viewing Adventure History

1. **Access Adventure History:**
   - Insert the `[adventure_game_history]` shortcode into a page or post.
2. **View Past Adventures:**
   - Users can see a list of their previous adventures.
   - Each adventure displays the date and the content of the game at that point.

### Clearing Adventure History

- Users can clear their adventure history from the game interface.
- **Note**: This action cannot be undone.

## Settings

Access the plugin settings by navigating to **Settings > Adventure Game** in the WordPress admin dashboard.

- **OpenAI API Key**: Enter your OpenAI API key.
- **ChatGPT Model Version**: Choose between `gpt-3.5-turbo` (fast) and `gpt-4` (slow).

## Contributing

Contributions are welcome! Please submit a pull request or open an issue to discuss any changes or enhancements.

## License

This plugin is licensed under the GPL2 license. See the [LICENSE](https://www.gnu.org/licenses/gpl-2.0.html) file for details.

---

**Disclaimer**: This plugin requires an OpenAI API key, which may incur costs based on usage. Please monitor your OpenAI account to avoid unexpected charges.

# Shortcode Examples

- **Display the Adventure Game:**

  ```html
  [wp_adventure_game]
  ```

- **Display Adventure History:**

  ```html
  [adventure_game_history]
  ```

- **Display Character Management Form:**

  ```html
  [adventure_game_character]
  ```

# Screenshots

*(Screenshots coming soon)*

1. **Game Interface:** The main adventure game screen where users input commands.
2. **Character Builder:** The form for creating or editing a character.
3. **Adventure History:** A list of past adventures with timestamps.
4. **Settings Page:** The admin settings page for configuring OpenAI API keys.

# Changelog

### Version 1.0

- Initial release of the Text Adventure Game with OpenAI Streaming and User Accounts plugin.

---

**Note:** This plugin integrates with the OpenAI API. Ensure you comply with OpenAI's policies and terms of service when using this plugin.

# Feedback and Support

For issues, suggestions, or contributions, please open an issue on the [GitHub repository](#).

# Acknowledgements

- Inspired by Dungeons & Dragons 5e and The Elder Scrolls series.
- Powered by [OpenAI](https://www.openai.com/) GPT models.

# Roadmap

- **Feature Enhancements:**
  - Implement game state serialization for improved performance.
  - Add more customization options for game mechanics and rules.
- **Localization:**
  - Provide translations for internationalization.

# Author

Developed by [Seth Shoultes](https://smartwebutah.com).

# Donation

If you find this plugin useful, consider supporting its development:

- [PayPal](#)
- [Buy Me a Coffee](#)

# Disclaimer

This is an open-source project provided as-is. The developer is not responsible for any unintended consequences arising from its use.

---

Thank you for using the Text Adventure Game with OpenAI Streaming and User Accounts plugin!