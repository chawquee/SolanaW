<?php
/**
 * The template for displaying the front page.
 *
 * This is the template that displays the front page of the WordPress site.
 * It's configured to display the Solana Address Checker functionality.
 * Features based on solanacheckerplan.txt.
 * Design based on hannisolsvelte.html.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#front-page-display
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
    <div id="primary" class="content-area front-page-content-area address-checker-content"> <?php // Classes for styling, including specific ones from hannisolsvelte.html ?>
    <main id="main" class="site-main solanawp-checker-main" role="main">

        <?php
        // Loop to display content if any is added to the Front Page in the WP editor.
        // Useful for an introduction above the checker.
        if ( have_posts() ) :
            while ( have_posts() ) :
                the_post();
                if ( get_the_content() ) { // Only display if there's actual content.
                    echo '<div class="entry-content page-intro-content">'; // Use .entry-content for consistent styling.
                    the_content();
                    echo '</div>';
                }
            endwhile;
            wp_reset_postdata();
        endif;
        ?>

        <?php // --- Input Section for the address checker --- ?>
        <?php get_template_part( 'template-parts/checker/input-section' ); ?>

        <?php // --- Results Section container --- ?>
        <div class="results-section" id="resultsSection"> <?php // Class and ID from hannisolsvelte.html for JS targeting ?>

            <?php // Load all individual result card template parts from template-parts/checker/ ?>
            <?php get_template_part( 'template-parts/checker/results-validation' ); ?>
            <?php get_template_part( 'template-parts/checker/results-balance' ); ?>
            <?php get_template_part( 'template-parts/checker/results-transactions' ); ?>

            <?php // Account Details & Security Analysis Cards Grid Wrapper from hannisolsvelte.html ?>
            <div id="accountAndSecurityOuterGrid" class="account-security-grid-wrapper" style="display:none;">
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
