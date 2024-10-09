<?php
// Ensure $parsed_state is available
if (!isset($parsed_state)) {
    echo '<p>Error: Game state not found.</p>';
    return;
}

// Fetch character data from user meta
$user_id = get_current_user_id();
$character = get_user_meta($user_id, 'adventure_game_character', true);

// Process each part of the game state
$game_status = isset($parsed_state['GameStatus']) ? wp_adventure_game_format_game_content($parsed_state['GameStatus']) : '';
$description = isset($parsed_state['Description']) ? wp_adventure_game_format_game_content($parsed_state['Description']) : '';
$commands = isset($parsed_state['Possible Commands']) ? $parsed_state['Possible Commands'] : [];

if ($character) {
    $character_name = esc_html($character['Name'] ?? 'Unknown');
    $character_race = esc_html($character['Race'] ?? 'Unknown');
    $character_class = esc_html($character['Class'] ?? 'Unknown');
    $attributes = $character['Attributes'] ?? [];
    $skills = $character['Skills'] ?? [];
    $backstory = esc_html($character['Backstory'] ?? 'No backstory available.');
}
?>

<div class="game-status">
    <h3>Game Status</h3>
    <?php if (isset($parsed_state['Stardate'])): ?><p><strong>Stardate:</strong> <?php echo esc_html($parsed_state['Stardate']); ?></p><?php endif; ?>
    <?php if (isset($parsed_state['Ship Location'])): ?><p><strong>Ship Location:</strong> <?php echo esc_html($parsed_state['Ship Location']); ?></p><?php endif; ?>
    <?php if (isset($parsed_state['Mission day'])): ?><p><strong>Mission Day:</strong> <?php echo esc_html($parsed_state['Mission day']); ?></p><?php endif; ?>
    <?php if (isset($parsed_state['Space conditions'])): ?><p><strong>Space Conditions:</strong> <?php echo esc_html($parsed_state['Space conditions']); ?></p><?php endif; ?>
    <?php if (isset($parsed_state['Shields'])): ?><p><strong>Shields:</strong> <?php echo esc_html($parsed_state['Shields']); ?></p><?php endif; ?>
    <?php if (isset($parsed_state['Rank'])): ?><p><strong>Rank:</strong> <?php echo esc_html($parsed_state['Rank']); ?></p><?php endif; ?>
    <?php if (isset($parsed_state['Location'])): ?><p><strong>Location:</strong> <?php echo esc_html($parsed_state['Location']); ?></p><?php endif; ?>
    <?php if (isset($parsed_state['Weather'])): ?><p><strong>Weather:</strong> <?php echo esc_html($parsed_state['Weather']); ?></p><?php endif; ?>
    <?php if (isset($parsed_state['Turn number'])): ?><p><strong>Turn:</strong> <?php echo esc_html($parsed_state['Turn number']); ?></p><?php endif; ?>
    <?php if (isset($parsed_state['Time period of the day'])): ?><p><strong>Time:</strong> <?php echo esc_html($parsed_state['Time period of the day']); ?></p><?php endif; ?>
    <?php if (isset($parsed_state['Current day number'])): ?><p><strong>Day:</strong> <?php echo esc_html($parsed_state['Current day number']); ?></p><?php endif; ?>
    <?php if (isset($parsed_state['Weather'])): ?><p><strong>Weather:</strong> <?php echo esc_html($parsed_state['Weather']); ?></p><?php endif; ?>
    <?php if (isset($parsed_state['Health'])): ?><p><strong>Health:</strong> <?php echo esc_html($parsed_state['Health']); ?></p><?php endif; ?>
    <?php if (isset($parsed_state['XP'])): ?><p><strong>XP:</strong> <?php echo esc_html($parsed_state['XP']); ?></p><?php endif; ?>
    <?php if (isset($parsed_state['AC'])): ?><p><strong>AC:</strong> <?php echo esc_html($parsed_state['AC']); ?></p><?php endif; ?>
    <?php if (isset($parsed_state['Level'])): ?><p><strong>Level:</strong> <?php echo esc_html($parsed_state['Level']); ?></p><?php endif; ?>
    <?php if (isset($parsed_state['Location'])): ?><p><strong>Location:</strong> <?php echo esc_html($parsed_state['Location']); ?></p><?php endif; ?>
    <?php if (isset($parsed_state['Coins'])): ?><p><strong>Coins:</strong> <?php echo esc_html($parsed_state['Coins']); ?></p><?php endif; ?>
    <?php if (isset($parsed_state['FartCoins'])): ?><p><strong>FartCoins:</strong> <?php echo esc_html($parsed_state['FartCoins']); ?></p><?php endif; ?>
    <?php if (isset($parsed_state['Quest'])) echo '<p><strong>Quest:</strong> ' . esc_html($parsed_state['Quest']) . '</p>'; ?>
    <?php if (isset($parsed_state['Inventory'])) echo '<p><strong>Inventory:</strong> ' . esc_html($parsed_state['Inventory']) . '</p>'; ?>
    <?php if (isset($parsed_state['Abilities'])) echo '<p><strong>Abilities:</strong> ' . esc_html($parsed_state['Abilities']) . '</p>'; ?>
</div>

<?php if (!empty($character)): ?>
    <div class="character-data">
        <h3>Character Data</h3>
        <p><strong>Name:</strong> <?php echo $character_name; ?></p>
        <p><strong>Race:</strong> <?php echo $character_race; ?></p>
        <p><strong>Class:</strong> <?php echo $character_class; ?></p>

        <h4>Attributes</h4>
        <ul>
            <?php foreach ($attributes as $attr_name => $attr_value): ?>
                <li><strong><?php echo esc_html($attr_name); ?>:</strong> <?php echo esc_html($attr_value); ?></li>
            <?php endforeach; ?>
        </ul>

        <h4>Skills</h4>
        <ul>
            <?php foreach ($skills as $skill): ?>
                <li><?php echo esc_html($skill); ?></li>
            <?php endforeach; ?>
        </ul>

        <h4>Backstory</h4>
        <p><?php echo $backstory; ?></p>
    </div>
<?php endif; ?>

<div class="game-description">
    <h3>Description</h3>
    <p><?php echo nl2br(esc_html($parsed_state['Description'] ?? '')); ?></p>
</div>
<?php if (isset($parsed_state['Outcome'])): ?>
    <div class="game-outcome">
        <h3>Outcome</h3>
        <p><?php echo nl2br(esc_html($parsed_state['Outcome'])); ?></p>
    </div>
<?php endif; ?>

<?php if (!empty($parsed_state['Possible Commands'])): ?>
    <div class="game-commands">
        <h3>Possible Commands</h3>
        <div class="command-buttons">
            <?php foreach ($parsed_state['Possible Commands'] as $command): ?>
                <button type="button" class="game-command-button"><?php echo esc_html($command); ?></button>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>
