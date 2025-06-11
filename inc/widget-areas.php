<?php
/**
 * Register widget areas (sidebars) for SolanaWP.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 *
 * @package SolanaWP
 * @since SolanaWP 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'solanawp_widgets_init' ) ) :
    /**
     * Initialize widget areas.
     *
     * This function is hooked into 'widgets_init'.
     */
    function solanawp_widgets_init() {
        // Main Sidebar: Default sidebar for general widgets.
        register_sidebar(
            array(
                'name'          => esc_html__( 'Main Sidebar', 'solanawp' ),
                'id'            => 'sidebar-1', // Primary sidebar ID.
                'description'   => esc_html__( 'Add widgets here to appear in your main sidebar.', 'solanawp' ),
                'before_widget' => '<section id="%1$s" class="widget %2$s">', // Default widget wrapper.
                'after_widget'  => '</section>',
                'before_title'  => '<h2 class="widget-title">', // Default widget title wrapper.
                'after_title'   => '</h2>',
            )
        );

        // Ad Banner Sidebar Area: Specifically for ad widgets, matching the sidebar in hannisolsvelte.html.
        // This supports the monetization strategy.
        register_sidebar(
            array(
                'name'          => esc_html__( 'Ad Banner Area (Sidebar)', 'solanawp' ),
                'id'            => 'ad-banner-sidebar',
                'description'   => esc_html__( 'Recommended for "SolanaWP Ad Banner" widgets or Custom HTML widgets for ads. Styles for .ad-banner are in main.css.', 'solanawp' ),
                'before_widget' => '<div id="%1$s" class="widget solanawp-ad-widget %2$s">', // Wrapper for ad widgets. Add .ad-banner class inside the widget or via template part.
                'after_widget'  => '</div>',
                'before_title'  => '<h3 class="ad-title screen-reader-text">', // Ad titles are often visually hidden.
                'after_title'   => '</h3>',
            )
        );

        // Optional Footer Widgets Area: Register if your design incorporates footer widgets.
        // Example for a 3-column footer widget area.
        register_sidebar(
            array(
                'name'          => esc_html__( 'Footer Widgets', 'solanawp' ),
                'id'            => 'footer-widgets',
                'description'   => esc_html__( 'Add widgets here to appear in your footer. Best used with multiple widgets for a column layout (styled in main.css).', 'solanawp' ),
                'before_widget' => '<section id="%1$s" class="widget footer-widget %2$s">',
                'after_widget'  => '</section>',
                'before_title'  => '<h4 class="widget-title">', // h4 for footer widget titles.
                'after_title'   => '</h4>',
            )
        );
    }
endif;
// The action hook `add_action( 'widgets_init', 'solanawp_widgets_init' );`
// will be called from the main SolanaWP/functions.php file.
