<?php
/**
 * SolanaWP Theme Customizer - UPDATED
 * Adds options to the WordPress Theme Customizer.
 * CHANGES: Added ad banner customization settings
 *
 * @package SolanaWP
 * @since SolanaWP 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'solanawp_customize_register' ) ) :
    /**
     * Add settings, controls, and sections to the Theme Customizer.
     *
     * @param WP_Customize_Manager $wp_customize Theme Customizer object.
     */
    function solanawp_customize_register( $wp_customize ) {

        // --- Site Identity Panel (Standard WordPress panel) ---
        $wp_customize->get_setting( 'blogname' )->transport        = 'postMessage';
        $wp_customize->get_setting( 'blogdescription' )->transport = 'postMessage';

        if ( isset( $wp_customize->selective_refresh ) ) {
            $wp_customize->selective_refresh->add_partial(
                'blogname',
                array(
                    'selector'        => '.site-header .brand-name',
                    'render_callback' => 'solanawp_customize_partial_blogname',
                    'fallback_refresh' => false,
                )
            );

            $wp_customize->selective_refresh->add_partial( 'custom_logo', array(
                'selector' => '.custom-logo-link, .site-header .logo-container .logo',
                'render_callback' => 'solanawp_customize_partial_custom_logo_or_fallback',
                'fallback_refresh' => false,
            ));
        }

        // --- SolanaWP Theme Options Panel ---
        $wp_customize->add_panel( 'solanawp_theme_options_panel', array(
            'title'    => __( 'SolanaWP Theme Options', 'solanawp' ),
            'priority' => 30,
            'description' => __( 'Customize various aspects of the SolanaWP theme.', 'solanawp' ),
        ) );

        // --- Colors Section ---
        $wp_customize->add_section(
            'solanawp_colors_section',
            array(
                'title'    => __( 'Theme Colors', 'solanawp' ),
                'panel'    => 'solanawp_theme_options_panel',
                'priority' => 10,
                'description' => __( 'Manage primary color schemes for the theme.', 'solanawp' ),
            )
        );

        // Setting: Primary Accent Color
        $wp_customize->add_setting(
            'solanawp_primary_accent_color',
            array(
                'default'           => '#7c3aed',
                'sanitize_callback' => 'sanitize_hex_color',
                'transport'         => 'postMessage',
            )
        );
        $wp_customize->add_control(
            new WP_Customize_Color_Control(
                $wp_customize,
                'solanawp_primary_accent_color',
                array(
                    'label'   => __( 'Primary Accent Color', 'solanawp' ),
                    'description' => __( 'Main accent color for buttons, active links, and highlights.', 'solanawp'),
                    'section' => 'solanawp_colors_section',
                )
            )
        );

        // Setting: Secondary Accent Color
        $wp_customize->add_setting(
            'solanawp_secondary_accent_color',
            array(
                'default'           => '#a855f7',
                'sanitize_callback' => 'sanitize_hex_color',
                'transport'         => 'refresh',
            )
        );
        $wp_customize->add_control(
            new WP_Customize_Color_Control(
                $wp_customize,
                'solanawp_secondary_accent_color',
                array(
                    'label'   => __( 'Secondary Accent Color (Gradients)', 'solanawp' ),
                    'description' => __( 'Used as the secondary color in gradients, like the main "Check Address" button.', 'solanawp'),
                    'section' => 'solanawp_colors_section',
                )
            )
        );

        // --- NEW: Ad Banners Section ---
        $wp_customize->add_section( 'solanawp_ad_banners_section', array(
            'title'       => __( 'Ad Banners', 'solanawp' ),
            'panel'       => 'solanawp_theme_options_panel',
            'priority'    => 15,
            'description' => __( 'Customize the ad banners displayed on the left and right sides of the analyzer.', 'solanawp'),
        ));

        // LEFT BANNER SETTINGS
        $wp_customize->add_setting( 'solanawp_left_banner_1_title', array(
            'default'           => 'Premium Ads',
            'sanitize_callback' => 'sanitize_text_field',
            'transport'         => 'refresh',
        ));
        $wp_customize->add_control( 'solanawp_left_banner_1_title', array(
            'label'    => __( 'Left Banner 1 - Title', 'solanawp' ),
            'section'  => 'solanawp_ad_banners_section',
            'type'     => 'text',
            'priority' => 10,
        ));

        $wp_customize->add_setting( 'solanawp_left_banner_1_description', array(
            'default'           => 'Crypto Ad Network',
            'sanitize_callback' => 'sanitize_text_field',
            'transport'         => 'refresh',
        ));
        $wp_customize->add_control( 'solanawp_left_banner_1_description', array(
            'label'    => __( 'Left Banner 1 - Description', 'solanawp' ),
            'section'  => 'solanawp_ad_banners_section',
            'type'     => 'text',
            'priority' => 11,
        ));

        $wp_customize->add_setting( 'solanawp_left_banner_1_details', array(
            'default'           => '$0.50-$2.00 CPM',
            'sanitize_callback' => 'sanitize_text_field',
            'transport'         => 'refresh',
        ));
        $wp_customize->add_control( 'solanawp_left_banner_1_details', array(
            'label'    => __( 'Left Banner 1 - Details', 'solanawp' ),
            'section'  => 'solanawp_ad_banners_section',
            'type'     => 'text',
            'priority' => 12,
        ));

        $wp_customize->add_setting( 'solanawp_left_banner_1_url', array(
            'default'           => '#',
            'sanitize_callback' => 'esc_url_raw',
            'transport'         => 'refresh',
        ));
        $wp_customize->add_control( 'solanawp_left_banner_1_url', array(
            'label'    => __( 'Left Banner 1 - URL', 'solanawp' ),
            'section'  => 'solanawp_ad_banners_section',
            'type'     => 'url',
            'priority' => 13,
        ));

        // Left Banner 2
        $wp_customize->add_setting( 'solanawp_left_banner_2_title', array(
            'default'           => 'A-ADS',
            'sanitize_callback' => 'sanitize_text_field',
            'transport'         => 'refresh',
        ));
        $wp_customize->add_control( 'solanawp_left_banner_2_title', array(
            'label'    => __( 'Left Banner 2 - Title', 'solanawp' ),
            'section'  => 'solanawp_ad_banners_section',
            'type'     => 'text',
            'priority' => 20,
        ));

        $wp_customize->add_setting( 'solanawp_left_banner_2_description', array(
            'default'           => 'Bitcoin Advertising',
            'sanitize_callback' => 'sanitize_text_field',
            'transport'         => 'refresh',
        ));
        $wp_customize->add_control( 'solanawp_left_banner_2_description', array(
            'label'    => __( 'Left Banner 2 - Description', 'solanawp' ),
            'section'  => 'solanawp_ad_banners_section',
            'type'     => 'text',
            'priority' => 21,
        ));

        $wp_customize->add_setting( 'solanawp_left_banner_2_details', array(
            'default'           => 'High CTR Rates',
            'sanitize_callback' => 'sanitize_text_field',
            'transport'         => 'refresh',
        ));
        $wp_customize->add_control( 'solanawp_left_banner_2_details', array(
            'label'    => __( 'Left Banner 2 - Details', 'solanawp' ),
            'section'  => 'solanawp_ad_banners_section',
            'type'     => 'text',
            'priority' => 22,
        ));

        $wp_customize->add_setting( 'solanawp_left_banner_2_url', array(
            'default'           => '#',
            'sanitize_callback' => 'esc_url_raw',
            'transport'         => 'refresh',
        ));
        $wp_customize->add_control( 'solanawp_left_banner_2_url', array(
            'label'    => __( 'Left Banner 2 - URL', 'solanawp' ),
            'section'  => 'solanawp_ad_banners_section',
            'type'     => 'url',
            'priority' => 23,
        ));

        // RIGHT BANNER SETTINGS
        $wp_customize->add_setting( 'solanawp_right_banner_1_title', array(
            'default'           => 'Coinzilla',
            'sanitize_callback' => 'sanitize_text_field',
            'transport'         => 'refresh',
        ));
        $wp_customize->add_control( 'solanawp_right_banner_1_title', array(
            'label'    => __( 'Right Banner 1 - Title', 'solanawp' ),
            'section'  => 'solanawp_ad_banners_section',
            'type'     => 'text',
            'priority' => 30,
        ));

        $wp_customize->add_setting( 'solanawp_right_banner_1_description', array(
            'default'           => 'Premium Crypto Ads',
            'sanitize_callback' => 'sanitize_text_field',
            'transport'         => 'refresh',
        ));
        $wp_customize->add_control( 'solanawp_right_banner_1_description', array(
            'label'    => __( 'Right Banner 1 - Description', 'solanawp' ),
            'section'  => 'solanawp_ad_banners_section',
            'type'     => 'text',
            'priority' => 31,
        ));

        $wp_customize->add_setting( 'solanawp_right_banner_1_details', array(
            'default'           => 'Advanced Targeting',
            'sanitize_callback' => 'sanitize_text_field',
            'transport'         => 'refresh',
        ));
        $wp_customize->add_control( 'solanawp_right_banner_1_details', array(
            'label'    => __( 'Right Banner 1 - Details', 'solanawp' ),
            'section'  => 'solanawp_ad_banners_section',
            'type'     => 'text',
            'priority' => 32,
        ));

        $wp_customize->add_setting( 'solanawp_right_banner_1_url', array(
            'default'           => '#',
            'sanitize_callback' => 'esc_url_raw',
            'transport'         => 'refresh',
        ));
        $wp_customize->add_control( 'solanawp_right_banner_1_url', array(
            'label'    => __( 'Right Banner 1 - URL', 'solanawp' ),
            'section'  => 'solanawp_ad_banners_section',
            'type'     => 'url',
            'priority' => 33,
        ));

        // Right Banner 2
        $wp_customize->add_setting( 'solanawp_right_banner_2_title', array(
            'default'           => 'Trading Bot',
            'sanitize_callback' => 'sanitize_text_field',
            'transport'         => 'refresh',
        ));
        $wp_customize->add_control( 'solanawp_right_banner_2_title', array(
            'label'    => __( 'Right Banner 2 - Title', 'solanawp' ),
            'section'  => 'solanawp_ad_banners_section',
            'type'     => 'text',
            'priority' => 40,
        ));

        $wp_customize->add_setting( 'solanawp_right_banner_2_description', array(
            'default'           => 'Automated Trading',
            'sanitize_callback' => 'sanitize_text_field',
            'transport'         => 'refresh',
        ));
        $wp_customize->add_control( 'solanawp_right_banner_2_description', array(
            'label'    => __( 'Right Banner 2 - Description', 'solanawp' ),
            'section'  => 'solanawp_ad_banners_section',
            'type'     => 'text',
            'priority' => 41,
        ));

        $wp_customize->add_setting( 'solanawp_right_banner_2_details', array(
            'default'           => '24/7 Trading',
            'sanitize_callback' => 'sanitize_text_field',
            'transport'         => 'refresh',
        ));
        $wp_customize->add_control( 'solanawp_right_banner_2_details', array(
            'label'    => __( 'Right Banner 2 - Details', 'solanawp' ),
            'section'  => 'solanawp_ad_banners_section',
            'type'     => 'text',
            'priority' => 42,
        ));

        $wp_customize->add_setting( 'solanawp_right_banner_2_url', array(
            'default'           => '#',
            'sanitize_callback' => 'esc_url_raw',
            'transport'         => 'refresh',
        ));
        $wp_customize->add_control( 'solanawp_right_banner_2_url', array(
            'label'    => __( 'Right Banner 2 - URL', 'solanawp' ),
            'section'  => 'solanawp_ad_banners_section',
            'type'     => 'url',
            'priority' => 43,
        ));

        // --- Affiliate Links Section (existing code unchanged) ---
        $wp_customize->add_section( 'solanawp_affiliate_links_section', array(
            'title'       => __( 'Affiliate Links (Checker Page)', 'solanawp' ),
            'panel'       => 'solanawp_theme_options_panel',
            'priority'    => 20,
            'description' => __( 'Configure URLs for the "Recommended Security Tools" section displayed on the address checker page. These are used in template-parts/checker/results-affiliate.php.', 'solanawp'),
        ));

        $affiliate_items_config = array(
            'ledger' => array(
                'label' => __('Ledger Wallet URL', 'solanawp'),
                'default' => '#'
            ),
            'vpn'    => array(
                'label' => __('VPN Service URL', 'solanawp'),
                'default' => '#'
            ),
            'guide'  => array(
                'label' => __('Security Guide URL', 'solanawp'),
                'default' => '#'
            ),
            'course' => array(
                'label' => __('Crypto Course URL', 'solanawp'),
                'default' => '#'
            ),
        );

        $item_priority = 10;
        foreach ( $affiliate_items_config as $key => $config ) {
            $setting_id = "solanawp_affiliate_{$key}_url";
            $wp_customize->add_setting( $setting_id, array(
                'default'           => esc_url_raw( $config['default'] ),
                'transport'         => 'refresh',
                'sanitize_callback' => 'esc_url_raw',
            ));
            $wp_customize->add_control( $setting_id, array(
                'label'    => $config['label'],
                'section'  => 'solanawp_affiliate_links_section',
                'type'     => 'url',
                'priority' => $item_priority,
            ));
            $item_priority += 10;
        }

        // --- Footer Settings Section ---
        $wp_customize->add_section('solanawp_footer_settings_section', array(
            'title'    => __('Footer Settings', 'solanawp'),
            'panel'    => 'solanawp_theme_options_panel',
            'priority' => 30,
        ));

        $wp_customize->add_setting('solanawp_footer_copyright_text', array(
            'default'           => sprintf(
                esc_html__( '&copy; %1$s %2$s. All rights reserved. Theme by %3$s.', 'solanawp' ),
                date_i18n('Y'),
                esc_html( get_bloginfo('name') ),
                '<a href="https://www.worldgpl.com/" target="_blank" rel="noopener">WORLDGPL</a>'
            ),
            'sanitize_callback' => 'wp_kses_post',
            'transport'         => 'postMessage',
        ));
        $wp_customize->add_control('solanawp_footer_copyright_text', array(
            'label'       => __('Footer Copyright Text', 'solanawp'),
            'section'     => 'solanawp_footer_settings_section',
            'type'        => 'textarea',
        ));
        if ( isset( $wp_customize->selective_refresh ) ) {
            $wp_customize->selective_refresh->add_partial( 'solanawp_footer_copyright_text', array(
                'selector'        => '.site-footer .site-info',
                'render_callback' => 'solanawp_customize_partial_footer_copyright',
                'fallback_refresh' => false,
            ));
        }
    }
endif;

// --- Render Callbacks for Selective Refresh (unchanged) ---
if ( ! function_exists( 'solanawp_customize_partial_blogname' ) ) :
    function solanawp_customize_partial_blogname() {
        bloginfo( 'name' );
    }
endif;

if ( ! function_exists( 'solanawp_customize_partial_blogdescription' ) ) :
    function solanawp_customize_partial_blogdescription() {
        bloginfo( 'description' );
    }
endif;

if ( ! function_exists( 'solanawp_customize_partial_custom_logo_or_fallback' ) ) :
    function solanawp_customize_partial_custom_logo_or_fallback() {
        ob_start();
        if ( function_exists( 'the_custom_logo' ) && has_custom_logo() ) {
            the_custom_logo();
        } else {
            ?>
            <div class="logo">
                <div class="logo-h">H</div>
            </div>
            <?php
        }
        return ob_get_clean();
    }
endif;

if ( ! function_exists( 'solanawp_customize_partial_footer_copyright' ) ) :
    function solanawp_customize_partial_footer_copyright() {
        $copyright_text = get_theme_mod( 'solanawp_footer_copyright_text', sprintf(
            esc_html__( '&copy; %1$s %2$s. All rights reserved. Theme by %3$s.', 'solanawp' ),
            date_i18n('Y'),
            esc_html( get_bloginfo('name') ),
            '<a href="https://www.worldgpl.com/" target="_blank" rel="noopener">WORLDGPL</a>'
        ) );
        echo wp_kses_post( $copyright_text );
    }
endif;

if ( ! function_exists( 'solanawp_customize_preview_js' ) ) :
    function solanawp_customize_preview_js() {
        wp_enqueue_script(
            'solanawp-customizer-preview',
            get_template_directory_uri() . '/assets/js/customizer.js',
            array( 'customize-preview', 'jquery' ),
            SOLANAWP_VERSION,
            true
        );
    }
endif;

// --- Helper: Output Customizer CSS (unchanged) ---
if ( ! function_exists( 'solanawp_customizer_css_output' ) ) :
    function solanawp_customizer_css_output() {
        $primary_accent_color = get_theme_mod( 'solanawp_primary_accent_color', '#7c3aed' );
        $secondary_accent_color = get_theme_mod( 'solanawp_secondary_accent_color', '#a855f7' );

        $css = '<style type="text/css" id="solanawp-customizer-css">';

        if ( $primary_accent_color !== '#7c3aed' ) {
            $css .= 'a, .primary-menu li a:hover, .primary-menu .current-menu-item > a, .primary-menu .current-menu-ancestor > a, .entry-title a:hover, .comment-metadata a:hover, .widget-area .widget ul li a:hover, .site-footer .site-info a:hover, .text-purple, .sol-balance-value, .reply .comment-reply-link, .widget-area .widget_search .search-submit:hover { color: ' . esc_attr( $primary_accent_color ) . '; }';
            $css .= '.check-btn, .form-submit .submit, .read-more-button { background: linear-gradient(135deg, ' . esc_attr( $primary_accent_color ) . ' 0%, ' . esc_attr( $secondary_accent_color ) . ' 100%); }';
            $css .= '.check-btn:focus, .address-input:focus { border-color: ' . esc_attr( $primary_accent_color ) . '; box-shadow: 0 0 0 4px ' . esc_attr( solanawp_hex_to_rgba($primary_accent_color, 0.1) ) . '; }';
            $css .= '.the-posts-pagination .nav-links .page-numbers:hover, .the-posts-pagination .nav-links .page-numbers.current { background-color: ' . esc_attr( $primary_accent_color ) . '; border-color: ' . esc_attr( $primary_accent_color ) . '; }';
            $css .= '.reply .comment-reply-link:hover { background-color: ' . esc_attr( $primary_accent_color ) . '; border-color: ' . esc_attr( $primary_accent_color ) . '; }';
            $css .= '.comment-form input[type="text"]:focus, .comment-form input[type="email"]:focus, .comment-form input[type="url"]:focus, .comment-form textarea:focus { border-color: ' . esc_attr( $primary_accent_color ) . '; box-shadow: 0 0 0 3px ' . esc_attr( solanawp_hex_to_rgba($primary_accent_color, 0.1) ) . '; }';
        }

        if ( $secondary_accent_color !== '#a855f7' && $primary_accent_color === '#7c3aed' ) {
            $css .= '.check-btn, .form-submit .submit, .read-more-button { background: linear-gradient(135deg, #7c3aed 0%, ' . esc_attr( $secondary_accent_color ) . ' 100%); }';
        }

        $css .= '</style>';

        if ( str_replace(array('<style type="text/css" id="solanawp-customizer-css">', '</style>'), '', $css) !== '' ) {
            echo $css;
        }
    }
endif;

// Helper function to convert hex to rgba (unchanged)
if ( ! function_exists( 'solanawp_hex_to_rgba' ) ) :
    function solanawp_hex_to_rgba( $hex, $alpha = 1 ) {
        $hex      = str_replace( '#', '', $hex );
        if ( strlen( $hex ) == 3 ) {
            $r = hexdec( substr( $hex, 0, 1 ) . substr( $hex, 0, 1 ) );
            $g = hexdec( substr( $hex, 1, 1 ) . substr( $hex, 1, 1 ) );
            $b = hexdec( substr( $hex, 2, 1 ) . substr( $hex, 2, 1 ) );
        } else {
            $r = hexdec( substr( $hex, 0, 2 ) );
            $g = hexdec( substr( $hex, 2, 2 ) );
            $b = hexdec( substr( $hex, 4, 2 ) );
        }
        return 'rgba(' . $r . ',' . $g . ',' . $b . ',' . $alpha . ')';
    }
endif;
