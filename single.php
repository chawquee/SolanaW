<?php
/**
 * The template for displaying all single posts.
 * This template is used when a visitor requests a single post.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package SolanaWP
 * @since SolanaWP 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header(); // Includes header.php
?>
<?php // Using .main-container structure from hannisolsvelte.html for layout consistency. ?>
<div class="main-container">
    <div id="primary" class="content-area single-post-content-area"> <?php // Added .single-post-content-area for specific styling if needed. ?>
    <main id="main" class="site-main" role="main">

        <?php
        // Start the WordPress Loop to display the single post.
        while ( have_posts() ) :
            the_post();

            // Include the content-single.php template part to display the post's content.
            // This template part (content-single.php) uses card styling from hannisolsvelte.html.
            get_template_part( 'template-parts/content', 'single' );

            // Display previous/next post navigation.
            the_post_navigation(
                array(
                    'prev_text' => '<span class="nav-subtitle">' . esc_html__( 'Previous Post:', 'solanawp' ) . '</span> <span class="nav-title">%title</span>',
                    'next_text' => '<span class="nav-subtitle">' . esc_html__( 'Next Post:', 'solanawp' ) . '</span> <span class="nav-title">%title</span>',
                    'screen_reader_text' => esc_html__( 'Post navigation', 'solanawp' ), // Accessibility text.
                )
            );

            // If comments are open or there is at least one comment, load up the comment template.
            if ( comments_open() || get_comments_number() ) :
                comments_template(); // Includes comments.php
            endif;

        endwhile; // End of the loop.
        wp_reset_postdata(); // Good practice after custom loops, though the main loop here might not strictly need it.
        ?>
    </main></div><?php get_sidebar();  ?>
</div><?php
get_footer(); // Includes footer.php
