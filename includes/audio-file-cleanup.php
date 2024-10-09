<?php
// Schedule the cleanup event
function wp_adventure_game_schedule_audio_cleanup() {
    if ( ! wp_next_scheduled( 'wp_adventure_game_cleanup_audio_files' ) ) {
        wp_schedule_event( time(), 'daily', 'wp_adventure_game_cleanup_audio_files' );
    }
}
add_action( 'init', 'wp_adventure_game_schedule_audio_cleanup' );

// Unschedule the event on plugin deactivation
function wp_adventure_game_deactivation() {
    wp_clear_scheduled_hook( 'wp_adventure_game_cleanup_audio_files' );
}
register_deactivation_hook( __FILE__, 'wp_adventure_game_deactivation' );

// Cleanup function
function wp_adventure_game_cleanup_audio_files() {
    $max_age = 30; // 1 day in seconds
    $upload_dir = wp_upload_dir();
    $upload_path = $upload_dir['basedir'];
    $pattern = $upload_path . '/adventure_game_audio_*.mp3';
    $audio_files = glob( $pattern );

    if ( ! empty( $audio_files ) ) {
        foreach ( $audio_files as $file ) {
            $file_mod_time = filemtime( $file );
            if ( time() - $file_mod_time > $max_age ) {
                if ( strpos( realpath( $file ), realpath( $upload_path ) ) === 0 ) {
                    unlink( $file );
                }
            }
        }
    }
}
add_action( 'wp_adventure_game_cleanup_audio_files', 'wp_adventure_game_cleanup_audio_files' );
