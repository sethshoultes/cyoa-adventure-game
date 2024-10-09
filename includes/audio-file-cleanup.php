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
    $max_age = 120; // Adjust the time interval as needed (e.g., 300 for 5 minutes)
    $upload_dir = wp_upload_dir();
    $upload_path = $upload_dir['basedir'];
    $pattern = $upload_path . '/adventure_game_audio_*.mp3';
    $audio_files = glob( $pattern );

    // Log that the cleanup function was called
    error_log( 'Cleanup function called at ' . date( 'Y-m-d H:i:s' ) );

    if ( ! empty( $audio_files ) ) {
        error_log( 'Found ' . count( $audio_files ) . ' audio files.' );
        foreach ( $audio_files as $file ) {
            $file_mod_time = filemtime( $file );
            $file_age = time() - $file_mod_time;

            // Log file details
            error_log( 'Checking file: ' . $file );
            error_log( 'File modification time: ' . date( 'Y-m-d H:i:s', $file_mod_time ) );
            error_log( 'File age in seconds: ' . $file_age );

            if ( $file_age > $max_age ) {
                if ( strpos( realpath( $file ), realpath( $upload_path ) ) === 0 ) {
                    $deleted = unlink( $file );
                    if ( $deleted ) {
                        error_log( 'Deleted file: ' . $file );
                    } else {
                        error_log( 'Failed to delete file: ' . $file );
                    }
                } else {
                    error_log( 'File outside of upload path, not deleted: ' . $file );
                }
            } else {
                error_log( 'File not old enough to delete: ' . $file );
            }
        }
    } else {
        error_log( 'No audio files found for cleanup.' );
    }
}

add_action( 'wp_adventure_game_cleanup_audio_files', 'wp_adventure_game_cleanup_audio_files' );

// Manual cleanup trigger
add_action( 'init', 'wp_adventure_game_manual_cleanup_trigger' );
function wp_adventure_game_manual_cleanup_trigger() {
    if ( isset( $_GET['run_cleanup'] ) && current_user_can( 'manage_options' ) ) {
        wp_adventure_game_cleanup_audio_files();
        echo 'Cleanup function executed.';
        exit;
    }
}
//  Log function
function wp_adventure_game_log( $message ) {
    $log_file = WP_CONTENT_DIR . '/uploads/adventure_game_cleanup.log';
    $date = date( 'Y-m-d H:i:s' );
    file_put_contents( $log_file, "[$date] $message\n", FILE_APPEND );
}