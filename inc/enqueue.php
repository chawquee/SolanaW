<?php
/**
 * Enqueue scripts and styles for the SolanaWP theme.
 *
 * @package SolanaWP
 * @since SolanaWP 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'solanawp_scripts_styles' ) ) :
    /**
     * Enqueue scripts and styles for the frontend.
     *
     * This function is hooked into 'wp_enqueue_scripts'.
     */
    function solanawp_scripts_styles() {
        // --- Stylesheets ---

        // 1. Theme's root stylesheet (SolanaWP/style.css)
        // Contains theme metadata and very basic global CSS.
        wp_enqueue_style(
            'solanawp-style', // Handle for the stylesheet.
            get_stylesheet_uri(), // Gets the URI of the current theme's style.css file.
            array(), // No dependencies for this primary stylesheet.
            SOLANAWP_VERSION // Theme version for cache busting. Defined in theme-setup.php
        );

        // 2. Main theme stylesheet (assets/css/main.css)
        // Contains all detailed theme styling from hannisolsvelte.html.
        wp_enqueue_style(
            'solanawp-main-styles',
            get_template_directory_uri() . '/assets/css/main.css',
            array( 'solanawp-style' ), // Depends on the root style.css to ensure correct order.
            SOLANAWP_VERSION
        );

        // 3. Responsive stylesheet (assets/css/responsive.css)
        // Contains @media queries for responsiveness from hannisolsvelte.html.
        wp_enqueue_style(
            'solanawp-responsive-styles',
            get_template_directory_uri() . '/assets/css/responsive.css',
            array( 'solanawp-main-styles' ), // Depends on main-styles to override or add to them.
            SOLANAWP_VERSION
        );

        // 4. Google Fonts (as imported in hannisolsvelte.html and new Montserrat)
        // The font "Times" is specified for branding in solanacheckerplan.txt.
        // Added "Montserrat" for the new "Solana Coins Analyzer" section.
        wp_enqueue_style(
            'solanawp-google-fonts',
            'https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,400;0,700;1,400&family=Times:wght@400;600;700&display=swap', // URL updated to include Montserrat
            array(), // No local stylesheet dependencies.
            null // No version number for external resources like Google Fonts.
        );

        // --- JavaScript Files ---

        // 1. Main theme JavaScript (assets/js/main.js)
        // Contains logic for Solana address checker simulation and other theme interactions.
        wp_enqueue_script(
            'solanawp-main-js', // Handle for the script.
            get_template_directory_uri() . '/assets/js/main.js',
            array( 'jquery' ), // Depends on jQuery (WordPress includes jQuery by default).
            SOLANAWP_VERSION,
            true // Load the script in the footer for better performance.
        );

        // Localize script for AJAX: Pass PHP variables to main.js.
        // This is essential if/when main.js needs to make secure calls to WordPress backend
        // for Solana data, using WordPress AJAX. The nonce is for security.
        // The action name 'solanawp_check_solana_address' must match the action hook in your PHP AJAX handler.
        wp_localize_script(
            'solanawp-main-js', // Handle of the script to attach data to.
            'solanaWP_ajax_object', // Name of the JavaScript object that will contain the data.
            array(
                'ajax_url' => admin_url( 'admin-ajax.php' ), // WordPress AJAX handler URL.
                'nonce'    => wp_create_nonce( 'solanawp_solana_checker_nonce' ), // Nonce for security verification.
                'checking_text' => esc_html__('Checking...', 'solanawp'),
                'check_address_text' => esc_html__('Check Address', 'solanawp'),
                'error_enter_address' => esc_html__('Please enter a Solana address.', 'solanawp'),
                'error_general_ajax' => esc_html__('An error occurred. Please try again.', 'solanawp'),
            )
        );

        // 2. Comment Reply Script
        // Only load on singular pages (posts/pages) where comments are open and threaded comments are enabled.
        if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
            wp_enqueue_script( 'comment-reply' );
        }
    }
endif;

if ( ! function_exists( 'solanawp_admin_scripts_styles' ) ) :
    /**
     * Enqueue scripts and styles for the WordPress admin area.
     * Specifically for theme options page or customizer enhancements.
     */
    function solanawp_admin_scripts_styles( $hook_suffix ) {
        // Enqueue admin stylesheet (assets/css/admin-style.css).
        wp_enqueue_style(
            'solanawp-admin-styles',
            get_template_directory_uri() . '/assets/css/admin-style.css',
            array(), // No dependencies for this specific admin style.
            SOLANAWP_VERSION
        );

        // Example: Enqueue scripts needed for advanced admin features if you build them.
        // This could be for a theme options page that uses a color picker or media uploader.
        // if ( 'appearance_page_yourtheme-options-slug' === $hook_suffix ) { // Check for specific admin page slug
        // wp_enqueue_media();
        // wp_enqueue_style( 'wp-color-picker' );
        // wp_enqueue_script( 'mytheme-admin-options-js', get_template_directory_uri() . '/assets/js/admin-options.js', array( 'jquery', 'wp-color-picker' ), SOLANAWP_VERSION, true );
        // }
    }
endif;

// The action hooks:
// `add_action( 'wp_enqueue_scripts', 'solanawp_scripts_styles' );`
// `add_action( 'admin_enqueue_scripts', 'solanawp_admin_scripts_styles' );`
// will be called from the main SolanaWP/functions.php file.
