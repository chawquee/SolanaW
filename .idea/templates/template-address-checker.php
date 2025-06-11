<?php
/**
 * Template Name: Solana Address Checker
 * Template Post Type: page
 *
 * This is the template for the main Solana Address Checker page.
 * It assembles the various parts of the checker interface by calling template parts.
 * Design and features based on hannisolsvelte.html and solanacheckerplan.txt.
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
<?php // The .content-area contains the input and results sections from hannisolsvelte.html ?>
    <div id="primary" class="content-area address-checker-content"> <?php // Added .address-checker-content for potential specific styling ?>

    <main id="main" class="site-main solanawp-checker-main" role="main">

        <?php
        // Standard WordPress loop to display page content (if any added in the editor for this page)
        while ( have_posts() ) :
            the_post();
            // Optionally display the_content() if you want to add introductory text or other content via the WordPress editor for this page.
            // Example:
            // if ( get_the_content() ) {
            // echo '<div class="page-intro-content entry-content card-content">'; // Using .card-content for consistent padding
            // the_content();
            // echo '</div>';
            // }
        endwhile; // End of the loop.
        wp_reset_postdata(); // Important after custom loops or if the_post() was called.
        ?>

        <?php // --- Input Section for the address checker --- ?>
        <?php get_template_part( 'template-parts/checker/input-section' ); ?>

        <?php // --- Results Section container --- ?>
        <div class="results-section" id="resultsSection"> <?php // Class and ID from hannisolsvelte.html for JS targeting ?>

            <?php // Individual result cards - these template parts contain structure from hannisolsvelte.html ?>
            <?php get_template_part( 'template-parts/checker/results-validation' ); ?>
            <?php get_template_part( 'template-parts/checker/results-balance' ); ?>
            <?php get_template_part( 'template-parts/checker/results-transactions' ); ?>

            <?php // Account Details & Security Analysis Cards Grid Wrapper ?>
            <div id="accountAndSecurityOuterGrid" class="account-security-grid-wrapper" style="display:none;"> <?php // Outer grid div from hannisolsvelte.html. ID for JS, initially hidden. Styles for grid in main.css ?>
                <?php get_template_part( 'template-parts/checker/results-account-details' ); ?>
                <?php get_template_part( 'template-parts/checker/results-security' ); ?>
            </div>

            <?php get_template_part( 'template-parts/checker/results-rugpull' ); ?>
            <?php get_template_part( 'template-parts/checker/results-community' ); ?>
            <?php get_template_part( 'template-parts/checker/results-affiliate' ); ?>
            <?php get_template_part( 'template-parts/checker/results-final' ); ?>

        </div></main></div><?php get_sidebar(); // Includes sidebar.php ?>

</div><?php
get_footer(); // Includes footer.php
