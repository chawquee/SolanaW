<?php
/**
 * Theme setup functions.
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * @package SolanaWP
 * @since SolanaWP 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! defined( 'SOLANAWP_VERSION' ) ) {
    /**
     * Define Theme Version.
     * Used for cache-busting of styles and scripts.
     */
    define( 'SOLANAWP_VERSION', '1.0.0' );
}

if ( ! function_exists( 'solanawp_setup' ) ) :
    /**
     * Sets up theme defaults and registers support for WordPress features.
     *
     * This function is hooked into the after_setup_theme hook, which
     * runs before the init hook. The init hook is too late for some features,
     * such as indicating support for post thumbnails.
     */
    function solanawp_setup() {
        /*
         * Make theme available for translation.
         * Translations can be filed in the /languages/ directory.
         * The text domain 'solanawp' should match the theme's slug.
         */
        load_theme_textdomain( 'solanawp', get_template_directory() . '/languages' );

        // Add default posts and comments RSS feed links to head.
        add_theme_support( 'automatic-feed-links' );

        /*
         * Let WordPress manage the document title.
         * By adding theme support, we declare that this theme does not use a
         * hard-coded <title> tag in the document head, and expect WordPress to
         * provide it for us.
         */
        add_theme_support( 'title-tag' );

        /*
         * Enable support for Post Thumbnails on posts and pages.
         * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
         */
        add_theme_support( 'post-thumbnails' );
        // Example: set_post_thumbnail_size( 1200, 800, true ); // Default Post Thumbnail size (cropped).
        // Example: add_image_size( 'solanawp-card-thumbnail', 400, 250, true ); // Custom image size for cards

        /*
         * Enable support for Custom Logo.
         * Allows users to upload their own logo in the Customizer.
         * The Hannisol branding includes a specific logo design.
         * Dimensions are based on the logo in hannisolsvelte.html.
         */
        add_theme_support(
            'custom-logo',
            array(
                'height'      => 120, // Corresponds to .logo height in hannisolsvelte.html
                'width'       => 120, // Corresponds to .logo width in hannisolsvelte.html
                'flex-height' => true,
                'flex-width'  => true,
                'header-text' => array( 'site-title', 'site-description' ), // Classes to hide if logo is used.
                'unlink-homepage-logo' => false, // Keep logo linked on homepage by default.
            )
        );

        // Register navigation menus.
        // This theme uses wp_nav_menu() in the header.
        register_nav_menus(
            array(
                'primary' => esc_html__( 'Primary Menu', 'solanawp' ), // For main site navigation
                'footer'  => esc_html__( 'Footer Menu (Optional)', 'solanawp' ), // Optional footer navigation
            )
        );

        /*
         * Switch default core markup for search form, comment form, and comments
         * to output valid HTML5.
         */
        add_theme_support(
            'html5',
            array(
                'search-form',
                'comment-form',
                'comment-list',
                'gallery',
                'caption',
                'style', // Add support for <style> and <script> tags in HTML5.
                'script',
                'navigation-widgets', // For block-based navigation widgets if used.
            )
        );

        // Add theme support for selective refresh for widgets in the Customizer.
        add_theme_support( 'customize-selective-refresh-widgets' );

        // Add support for core WordPress Block Styles.
        add_theme_support( 'wp-block-styles' );

        // Add support for full and wide alignment for blocks.
        add_theme_support( 'align-wide' );

        // Add support for editor styles.
        // This allows you to style the Gutenberg editor to match the frontend.
        // add_theme_support( 'editor-styles' );
        // Create 'assets/css/editor-style.css' and enqueue it with add_editor_style() if needed.
        // add_editor_style( 'assets/css/editor-style.css' );

        // Add support for responsive embedded content (e.g., YouTube videos).
        add_theme_support( 'responsive-embeds' );

        // Set content width.
        // This is a general best practice. Adjust value based on your main content area's max width.
        // $GLOBALS['content_width'] = apply_filters( 'solanawp_content_width', 800 );
    }
endif;
// The action hook `add_action( 'after_setup_theme', 'solanawp_setup' );`
// will be called from the main SolanaWP/functions.php file.
