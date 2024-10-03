<?php
// Ensure $parsed_state is available
if (!isset($parsed_state)) {
    return;
}
?>
<div class="game-status">
    <h3>Game Status</h3>
    <p><strong>Turn:</strong> <?php echo esc_html($parsed_state['Turn number'] ?? ''); ?></p>
    <p><strong>Time:</strong> <?php echo esc_html($parsed_state['Time period of the day'] ?? ''); ?></p>
    <p><strong>Day:</strong> <?php echo esc_html($parsed_state['Current day number'] ?? ''); ?></p>
    <p><strong>Weather:</strong> <?php echo esc_html($parsed_state['Weather'] ?? ''); ?></p>
    <p><strong>Health:</strong> <?php echo esc_html($parsed_state['Health'] ?? ''); ?></p>
    <p><strong>XP:</strong> <?php echo esc_html($parsed_state['XP'] ?? ''); ?></p>
    <p><strong>AC:</strong> <?php echo esc_html($parsed_state['AC'] ?? ''); ?></p>
    <p><strong>Level:</strong> <?php echo esc_html($parsed_state['Level'] ?? ''); ?></p>
    <p><strong>Location:</strong> <?php echo esc_html($parsed_state['Location'] ?? ''); ?></p>
    <p><strong>FartCoin:</strong> <?php echo esc_html($parsed_state['FartCoin'] ?? ''); ?></p>
    <p><strong>Quest:</strong> <?php echo esc_html($parsed_state['Quest'] ?? ''); ?></p>
    <p><strong>Inventory:</strong> <?php echo esc_html($parsed_state['Inventory'] ?? ''); ?></p>
    <p><strong>Abilities:</strong> <?php echo esc_html($parsed_state['Abilities'] ?? ''); ?></p>
</div>
<div class="game-description">
    <h3>Description</h3>
    <p><?php echo nl2br(esc_html($parsed_state['Description'] ?? '')); ?></p>
</div>
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
