<?php

// Function to generate audio from text using OpenAI TTS API
function wp_adventure_game_generate_audio($text) {
    $api_key = get_option('wp_adventure_gameopenai_api_key');
    $voice = get_option('wp_adventure_gamechatgpt_audio_version');

    if (empty($api_key)) {
        error_log('Error: API key not set.');
        return null;
    }

    // Prepare API request data for TTS
    $postData = [
        'model' => 'tts-1', // or 'tts-1-hd', depending on your preference
        'input' => $text,
        'voice' => $voice, // Replace with the desired voice: alloy, echo, fable, onyx, nova, shimmer
        // Optional parameters
        'response_format' => 'mp3', // Can be 'mp3', 'opus', 'aac', 'flac', 'wav', or 'pcm'
        'speed' => 1, // Speed of the generated audio (0.25 to 4.0)
    ];

    // Execute the cURL request to OpenAI TTS API
    $ch = curl_init('https://api.openai.com/v1/audio/speech');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        "Authorization: Bearer $api_key",
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        $error_msg = curl_error($ch);
        error_log("cURL Error: $error_msg");
        return null;
    }

    // Check HTTP status code
    $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($http_status != 200) {
        error_log("OpenAI TTS API Error: HTTP Status $http_status, Response: $response");
        return null;
    }

    curl_close($ch);

    // The API returns the audio file content directly, so we can save it as is
    $audio_data = $response;

    // Generate a unique filename for the audio file
    $upload_dir = wp_upload_dir();
    $audio_filename = 'adventure_game_audio_' . time() . '.mp3';
    $audio_file_path = $upload_dir['basedir'] . '/' . $audio_filename;

    // Save the audio content to a file
    file_put_contents($audio_file_path, $audio_data);

    // Return the URL to the audio file
    $audio_url = $upload_dir['baseurl'] . '/' . $audio_filename;

    return $audio_url;
}
