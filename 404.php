<?php
/**
 * The template for displaying 404 pages (not found).
 *
 * @link https://codex.wordpress.org/Creating_an_Error_404_Page
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
    <div id="primary" class="content-area error-404-content"> <?php // Added .error-404-content for specific styling if needed. ?>
    <main id="main" class="site-main" role="main">

        <section class="error-404 not-found card"> <?php // Using .card class from hannisolsvelte.html for styling. ?>
            <header class="page-header card-header"> <?php // Using .card-header from hannisolsvelte.html. ?>
                <h1 class="page-title card-title"><?php esc_html_e( 'Oops! Page Not Found.', 'solanawp' ); ?></h1> <?php // Using .card-title from hannisolsvelte.html. ?>
            </header><div class="page-content card-content"> <?php // Using .card-content from hannisolsvelte.html. ?>
                <p><?php esc_html_e( 'It looks like nothing was found at this location. The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.', 'solanawp' ); ?></p>

                <p><?php esc_html_e( 'You could try one of the following:', 'solanawp' ); ?></p>
                <ul>
                    <li><?php printf( wp_kses( __( 'Return to the <a href="%s">homepage</a>.', 'solanawp' ), array( 'a' => array( 'href' => array() ) ) ), esc_url( home_url( '/' ) ) ); ?></li>
                    <li><?php esc_html_e( 'Use the search form below to find what you are looking for.', 'solanawp' ); ?></li>
                    <li><?php esc_html_e( 'Check the URL for any typos.', 'solanawp' ); ?></li>
                </ul>

                <?php get_search_form(); // Displays the WordPress search form. ?>

            </div></section></main></div><?php get_sidebar(); // Includes sidebar.php, maintaining consistent layout. ?>
</div><?php
get_footer(); // Includes footer.php
