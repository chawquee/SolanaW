<?php
/**
 * Template Name: Sidebar on Left
 * Template Post Type: page, post
 *
 * This template displays the sidebar on the left side of the main content.
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
<?php // Using .main-container, but will add a class for CSS to reorder grid items.
// The actual reordering is handled by CSS.
?>
<div class="main-container layout-sidebar-left"> <?php // Added .layout-sidebar-left for CSS targeting ?>

<?php get_sidebar(); // Call sidebar first for left sidebar layout in source order (can also be controlled by CSS grid order) ?>

    <div id="primary" class="content-area"> <?php // .content-area from hannisolsvelte.html ?>
        <main id="main" class="site-main" role="main">

            <?php
            // Start the Loop.
            while ( have_posts() ) :
                the_post();

                if ( 'post' === get_post_type() ) {
                    get_template_part( 'template-parts/content', 'single' );
                } else {
                    get_template_part( 'template-parts/content', 'page' );
                }

                // If comments are open or there is at least one comment, load up the comment template.
                if ( comments_open() || get_comments_number() ) {
                    comments_template();
                }

            endwhile; // End the Loop.
            wp_reset_postdata();
            ?>

        </main></div></div><?php
get_footer();
