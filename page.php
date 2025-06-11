<?php
/**
 * The template for displaying all static pages.
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site may use a
 * different template if assigned.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
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
<?php // .main-container wraps .content-area and .sidebar in hannisolsvelte.html ?>
<div class="main-container">
    <div id="primary" class="content-area page-content-area"> <?php // .content-area class from hannisolsvelte.html ?>
    <main id="main" class="site-main" role="main">

        <?php
        // Start the WordPress Loop to display page content.
        while ( have_posts() ) :
            the_post();

            // Include the page content template part.
            get_template_part( 'template-parts/content', 'page' );

            // If comments are open or there is at least one comment, load up the comment template.
            // (Typically, comments are disabled on pages, but this provides support if enabled.)
            if ( comments_open() || get_comments_number() ) :
                comments_template();
            endif;

        endwhile; // End of the loop.
        wp_reset_postdata();
        ?>

    </main></div><?php get_sidebar(); // Includes sidebar.php - can be conditionally removed if a full-width page template is used. ?>
</div><?php
get_footer(); // Includes footer.php
