<?php
/**
 * Template Name: Legal Page
 * Template Post Type: page
 *
 * This template is designed for legal pages like Privacy Policy, Terms of Service, etc.
 * It may feature a slightly narrower content width for better readability of text-heavy content.
 * Based on the need for legal pages for compliance.
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
<?php // Using .main-container, but the inner .content-area might have specific width constraints. ?>
<div class="main-container">
<?php // For legal pages, we might want a single column (full-width content area, no sidebar by default)
// or keep the sidebar for navigation/ads. Here, we assume a full-width approach like a document.
?>
    <div id="primary" class="content-area content-area-legal-page"> <?php // Specific class for styling ?>
    <main id="main" class="site-main" role="main">

        <?php
        // Start the Loop.
        while ( have_posts() ) :
            the_post();

            // We use content-page.php as it's suitable for displaying page content.
            // The specific styling for legal pages will be handled by .content-area-legal-page.
            get_template_part( 'template-parts/content', 'page' );

            // Comments are usually disabled on legal pages.
            // if ( comments_open() || get_comments_number() ) {
            // comments_template();
            // }

        endwhile; // End the Loop.
        wp_reset_postdata();
        ?>

    </main></div><?php // Optionally, include sidebar if desired for legal pages, or omit for focused reading.
// get_sidebar();
?>

</div><?php
get_footer();
