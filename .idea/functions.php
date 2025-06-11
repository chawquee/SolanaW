<?php
/**
 * SolanaWP functions and definitions
 *
 * This file acts as a hub for including all the theme's functional components,
 * ensuring cleaner code organization and adherence to WordPress best practices.
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package SolanaWP
 * @since SolanaWP 1.0.0
 */

// Exit if accessed directly to prevent direct access to the file.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// --- Core Theme Setup ---
// Includes theme supports, navigation menus, image sizes, etc.
require get_template_directory() . '/inc/theme-setup.php';
if ( function_exists( 'solanawp_setup' ) ) {
    add_action( 'after_setup_theme', 'solanawp_setup' );
}

// --- Enqueue Scripts and Styles ---
// Manages loading of all CSS and JavaScript files for front-end and admin.
require get_template_directory() . '/inc/enqueue.php';
if ( function_exists( 'solanawp_scripts_styles' ) ) {
    add_action( 'wp_enqueue_scripts', 'solanawp_scripts_styles' );
}
if ( function_exists( 'solanawp_admin_scripts_styles' ) ) {
    add_action( 'admin_enqueue_scripts', 'solanawp_admin_scripts_styles' );
}

// --- Widget Areas (Sidebars) ---
// Registers all widgetized areas for the theme.
require get_template_directory() . '/inc/widget-areas.php';
if ( function_exists( 'solanawp_widgets_init' ) ) {
    add_action( 'widgets_init', 'solanawp_widgets_init' );
}

// --- Custom Widgets ---
// Defines and registers custom widgets for the theme.
require get_template_directory() . '/inc/custom-widgets.php';
if ( function_exists( 'solanawp_register_custom_widgets' ) ) {
    add_action( 'widgets_init', 'solanawp_register_custom_widgets' );
}

// --- Theme Customizer Additions ---
// Adds options and controls to the WordPress Theme Customizer.
require get_template_directory() . '/inc/customizer.php';
if ( function_exists( 'solanawp_customize_register' ) ) {
    add_action( 'customize_register', 'solanawp_customize_register' );
}
if ( function_exists( 'solanawp_customize_preview_js' ) ) { // For live preview JS
    add_action( 'customize_preview_init', 'solanawp_customize_preview_js' );
}
if ( function_exists( 'solanawp_customizer_css_output' ) ) { // For outputting Customizer CSS to head
    add_action( 'wp_head', 'solanawp_customizer_css_output' );
}

// --- Custom Nav Walker (if used) ---
// Includes the custom navigation walker class.
require get_template_directory() . '/inc/navwalker.php';
// Note: The walker itself needs to be specified in wp_nav_menu() arguments in header.php to be used.

// --- Custom Template Tags ---
// Helper functions primarily for outputting HTML in template files.
require get_template_directory() . '/inc/template-tags.php';

// --- Template Functions ---
// Helper functions that hook into WordPress to modify behavior or add classes.
require get_template_directory() . '/inc/template-functions.php';
if ( function_exists( 'solanawp_body_classes' ) ) {
    add_filter( 'body_class', 'solanawp_body_classes' );
}
if ( function_exists( 'solanawp_pingback_header' ) ) {
    add_action( 'wp_head', 'solanawp_pingback_header' );
}
if ( function_exists( 'solanawp_excerpt_more' ) ) {
    add_filter( 'excerpt_more', 'solanawp_excerpt_more' );
}
if ( function_exists( 'solanawp_custom_excerpt_length' ) ) {
    add_filter( 'excerpt_length', 'solanawp_custom_excerpt_length', 999 );
}

// --- Breadcrumbs Functionality ---
// Includes the breadcrumbs generation function.
require get_template_directory() . '/inc/breadcrumbs.php';
// To display breadcrumbs, call solanawp_breadcrumbs() in your template files.


// --- Optional: WordPress AJAX Handler for Solana Checker ---
// This section would contain the PHP functions to handle AJAX requests from assets/js/main.js
// for fetching and processing Solana address data securely on the server-side.
/*
function solanawp_ajax_check_solana_address() {
    // 1. Verify nonce for security (created with wp_localize_script).
    check_ajax_referer( 'solanawp_solana_checker_nonce', 'nonce' );

    // 2. Get and sanitize the address from the AJAX request.
    $address = isset( $_POST['address'] ) ? sanitize_text_field( wp_unslash( $_POST['address'] ) ) : '';

    if ( empty( $address ) ) {
        wp_send_json_error( array( 'message' => esc_html__( 'No address provided.', 'solanawp' ) ) );
    }

    // 3. IMPLEMENT YOUR LOGIC HERE to:
    //    - Call Solana RPC endpoints (e.g., using wp_remote_get or wp_remote_post).
    //    - Call any third-party APIs for risk assessment, token data, etc.
    //    - Process the data.
    //    - IMPORTANT: Store API keys securely (e.g., constants in wp-config.php, not directly in theme files).

    // Example: Simulating a successful data fetch
    // $solana_data = my_custom_solana_api_fetch_function( $address );

    // if ( is_wp_error( $solana_data ) ) {
    //     wp_send_json_error( array( 'message' => $solana_data->get_error_message() ) );
    // } elseif ( empty( $solana_data ) ) {
    //     wp_send_json_error( array( 'message' => esc_html__( 'Could not retrieve data for this address.', 'solanawp' ) ) );
    // } else {
    //     // Structure $solana_data to match what assets/js/main.js expects for populateResults()
    //     wp_send_json_success( $solana_data );
    // }

    // Fallback for simulation if above is not implemented
    $mock_validation_message = ($address && strlen($address) > 30) ? 'Address appears valid (Simulated Server Response)' : 'Invalid address format (Simulated Server Response)';
    $mock_is_valid = ($address && strlen($address) > 30);

    wp_send_json_success( array( //
        'validation' => array(
            'isValid' => $mock_is_valid,
            'address' => esc_html($address),
            'format' => 'Base58 (Simulated Server)',
            'length' => strlen($address) . ' characters (Simulated Server)',
            'type' => 'Wallet Account (Simulated Server)',
            'message' => $mock_validation_message
        ),
        // ... include other mock data sections (balanceHoldings, transactionAnalysis, etc.)
        // if isValid is true, to fully test the JS population.
        // This should mirror the mockData structure in main.js
    ) );


	wp_die(); // This is required to terminate immediately and correctly an AJAX handler.
}
add_action( 'wp_ajax_solanawp_check_solana_address', 'solanawp_ajax_check_solana_address' );       // For logged-in users
add_action( 'wp_ajax_nopriv_solanawp_check_solana_address', 'solanawp_ajax_check_solana_address' ); // For non-logged-in users (visitors)
*/

// Set global content width (if not set in theme-setup.php or if a different context is needed here).
if ( ! isset( $content_width ) ) {
    $content_width = 800; // Example width in pixels for main content area.
}
?>
