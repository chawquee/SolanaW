<?php
/**
 * Template Name: Full Width Page
 * Template Post Type: page, post
 *
 * This template is for displaying a page or post without a sidebar,
 * allowing the content to take up the full width of the main content area.
 *
 * @package SolanaWP
 * @since SolanaWP 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();
?>
<?php // For full-width, we might use .main-container but ensure it knows to be single-column,
// or use a different top-level wrapper.
// Using .main-container and relying on body_class or a template-specific class to adjust grid.
?>
<div class="main-container main-container-full-width"> <?php // Added -full-width for specific CSS targeting ?>
    <div id="primary" class="content-area content-area-full-width"> <?php // .content-area for base style, -full-width for overrides ?>
    <main id="main" class="site-main" role="main">

        <?php
        // Start the Loop.
        while ( have_posts() ) :
            the_post();

            if ( 'post' === get_post_type() ) {
                // For single posts using this template, use content-single or a dedicated full-width version.
                get_template_part( 'template-parts/content', 'single' );
            } else {
                // For pages using this template.
                get_template_part( 'template-parts/content', 'page' );
            }

            // If comments are open or there is at least one comment, load up the comment template.
            if ( comments_open() || get_comments_number() ) {
                comments_template();
            }

        endwhile; // End the Loop.
        wp_reset_postdata();
        ?>

    </main></div><?php // NO get_sidebar() call for a full-width template ?>

</div><?php
get_footer();
