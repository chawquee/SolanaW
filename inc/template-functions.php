<?php
/**
 * Functions which enhance the theme by hooking into WordPress core.
 *
 * @package SolanaWP
 * @since SolanaWP 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'solanawp_body_classes' ) ) :
    /**
     * Adds custom classes to the array of body classes.
     *
     * @param array $classes Classes for the body element.
     * @return array Modified body classes.
     */
    function solanawp_body_classes( $classes ) {
        // Adds a class of hfeed to non-singular pages.
        if ( ! is_singular() ) {
            $classes[] = 'hfeed';
        }

        // Adds a class of no-sidebar when there is no active sidebar.
        // Check all registered sidebars that are expected to affect layout.
        if ( ! is_active_sidebar( 'sidebar-1' ) && ! is_active_sidebar( 'ad-banner-sidebar' ) ) {
            $classes[] = 'no-sidebar';
        } else {
            $classes[] = 'has-sidebar';
        }

        // Adds a class if the page uses the Solana Address Checker template.
        // This helps in targeting page-specific styles if needed.
        if ( is_page_template( 'templates/template-address-checker.php' ) ) {
            $classes[] = 'page-template-address-checker';
        }
        // Add a class for the front page, regardless of template, if it's the checker
        if ( is_front_page() ) {
            $classes[] = 'solanawp-front-page';
            // If front page IS the checker, add specific class
            if ( is_page_template( 'templates/template-address-checker.php' ) || ( get_option( 'show_on_front' ) == 'page' && get_option( 'page_on_front' ) && get_page_template_slug( get_option( 'page_on_front' ) ) === 'templates/template-address-checker.php') ) {
                $classes[] = 'front-page-is-checker';
            }
        }

        return array_unique( $classes ); // Ensure no duplicate classes
    }
endif;
// The action hook `add_filter( 'body_class', 'solanawp_body_classes' );`
// will be called from the main SolanaWP/functions.php file.


if ( ! function_exists( 'solanawp_pingback_header' ) ) :
    /**
     * Add a pingback url auto-discovery header for single posts, pages, or attachments.
     */
    function solanawp_pingback_header() {
        if ( is_singular() && pings_open() ) {
            printf( '<link rel="pingback" href="%s">' . "\n", esc_url( get_bloginfo( 'pingback_url' ) ) );
        }
    }
endif;
// The action hook `add_action( 'wp_head', 'solanawp_pingback_header' );`
// will be called from the main SolanaWP/functions.php file.


if ( ! function_exists( 'solanawp_excerpt_more' ) ) :
    /**
     * Customize the [...] (more tag) for excerpts.
     * Replaces the default [...] with a 'Read More' link.
     *
     * @param string $more The current 'more' text.
     * @return string Modified 'more' text with a link.
     */
    function solanawp_excerpt_more( $more ) {
        if ( is_admin() ) { // Don't modify in admin area
            return $more;
        }
        // The read-more-button class can be styled similar to .check-btn from hannisolsvelte.html
        return sprintf(
            ' &hellip; <a class="read-more-link read-more-button" href="%1$s">%2$s <span class="screen-reader-text"> "%3$s"</span></a>',
            esc_url( get_permalink( get_the_ID() ) ),
            esc_html__( 'Read More', 'solanawp' ),
            esc_html( get_the_title( get_the_ID() ) )
        );
    }
endif;
// The action hook `add_filter( 'excerpt_more', 'solanawp_excerpt_more' );`
// will be called from the main SolanaWP/functions.php file.


if ( ! function_exists( 'solanawp_custom_excerpt_length' ) ) :
    /**
     * Customize excerpt length.
     *
     * @param int $length Default excerpt length.
     * @return int Modified excerpt length.
     */
    function solanawp_custom_excerpt_length( $length ) {
        if ( is_admin() ) { // Don't modify in admin area
            return $length;
        }
        return 30; // Set custom excerpt length to 30 words. Adjust as needed.
    }
endif;
// The action hook `add_filter( 'excerpt_length', 'solanawp_custom_excerpt_length', 999 );`
// will be called from the main SolanaWP/functions.php file.
