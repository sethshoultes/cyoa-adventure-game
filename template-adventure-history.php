<?php
/*
Template Name: Adventure History
*/

if (!is_user_logged_in()) {
    echo '<p>You must be logged in to view your adventure history.</p>';
    return;
}

get_header();

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

// Reset post data after query
wp_reset_postdata();

get_footer();
