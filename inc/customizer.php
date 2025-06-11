<?php
/**
 * Enhanced SolanaWP Theme Customizer v2.0 - Independent Sidebar System
 * Implements independent left/right sidebar ad management with reorganized interface
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

        // --- Enhanced Site Identity Panel ---
        $wp_customize->get_setting( 'blogname' )->transport        = 'postMessage';
        $wp_customize->get_setting( 'blogdescription' )->transport = 'postMessage';

        if ( isset( $wp_customize->selective_refresh ) ) {
            $wp_customize->selective_refresh->add_partial(
                'blogname',
                array(
                    'selector'        => '.site-header .brand-name a',
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

        // --- Helper Functions ---
        function solanawp_get_font_choices() {
            return array(
                'inherit' => __( 'Theme Default', 'solanawp' ),
                'Arial, sans-serif' => 'Arial',
                "'Times New Roman', Times, serif" => 'Times New Roman',
                "'Courier New', Courier, monospace" => 'Courier New',
                'Georgia, serif' => 'Georgia',
                'Verdana, sans-serif' => 'Verdana',
                'Tahoma, sans-serif' => 'Tahoma',
                "'Helvetica Neue', Helvetica, sans-serif" => 'Helvetica',
                "'Roboto', sans-serif" => 'Roboto',
                "'Open Sans', sans-serif" => 'Open Sans',
                "'Montserrat', sans-serif" => 'Montserrat',
            );
        }

        function solanawp_get_font_weight_choices() {
            return array(
                '300' => __( 'Light', 'solanawp' ),
                '400' => __( 'Normal', 'solanawp' ),
                '500' => __( 'Medium', 'solanawp' ),
                '600' => __( 'Semi Bold', 'solanawp' ),
                '700' => __( 'Bold', 'solanawp' ),
                '800' => __( 'Extra Bold', 'solanawp' ),
            );
        }

        function solanawp_get_alignment_choices() {
            return array(
                'left' => __( 'Left', 'solanawp' ),
                'center' => __( 'Center', 'solanawp' ),
                'right' => __( 'Right', 'solanawp' ),
            );
        }

        function solanawp_get_animation_choices() {
            return array(
                'none' => __( 'None', 'solanawp' ),
                'fadeIn' => __( 'Fade In', 'solanawp' ),
                'slideInLeft' => __( 'Slide In Left', 'solanawp' ),
                'slideInRight' => __( 'Slide In Right', 'solanawp' ),
                'slideInUp' => __( 'Slide In Up', 'solanawp' ),
                'bounce' => __( 'Bounce', 'solanawp' ),
                'pulse' => __( 'Pulse', 'solanawp' ),
            );
        }

        // --- Advanced Banner Creation Function ---
        function solanawp_create_advanced_banner_controls( $wp_customize, $section_id, $prefix, $title, $description ) {
            $wp_customize->add_section( $section_id, array(
                'title'       => $title,
                'panel'       => 'solanawp_theme_options_panel',
                'description' => $description,
            ));

            // Banner URL (makes entire banner clickable)
            $wp_customize->add_setting( "{$prefix}_url", array(
                'default' => '',
                'sanitize_callback' => 'esc_url_raw',
                'transport' => 'refresh',
            ));
            $wp_customize->add_control( "{$prefix}_url", array(
                'label' => __( 'Banner URL (makes entire banner clickable)', 'solanawp' ),
                'section' => $section_id,
                'type' => 'url',
                'priority' => 5,
            ));

            // Background Settings
            $wp_customize->add_setting( "{$prefix}_bg_type", array(
                'default' => 'color',
                'sanitize_callback' => 'sanitize_text_field',
                'transport' => 'refresh',
            ));
            $wp_customize->add_control( "{$prefix}_bg_type", array(
                'label' => __( 'Background Type', 'solanawp' ),
                'section' => $section_id,
                'type' => 'radio',
                'choices' => array(
                    'color' => __( 'Color', 'solanawp' ),
                    'gradient' => __( 'Gradient', 'solanawp' ),
                    'image' => __( 'Image', 'solanawp' ),
                ),
                'priority' => 10,
            ));

            $wp_customize->add_setting( "{$prefix}_bg_color", array(
                'default' => '#ffffff',
                'sanitize_callback' => 'sanitize_hex_color',
                'transport' => 'postMessage',
            ));
            $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, "{$prefix}_bg_color", array(
                'label' => __( 'Background Color', 'solanawp' ),
                'section' => $section_id,
                'priority' => 15,
            )));

            $wp_customize->add_setting( "{$prefix}_bg_gradient_start", array(
                'default' => '#3b82f6',
                'sanitize_callback' => 'sanitize_hex_color',
                'transport' => 'postMessage',
            ));
            $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, "{$prefix}_bg_gradient_start", array(
                'label' => __( 'Gradient Start Color', 'solanawp' ),
                'section' => $section_id,
                'priority' => 20,
            )));

            $wp_customize->add_setting( "{$prefix}_bg_gradient_end", array(
                'default' => '#8b5cf6',
                'sanitize_callback' => 'sanitize_hex_color',
                'transport' => 'postMessage',
            ));
            $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, "{$prefix}_bg_gradient_end", array(
                'label' => __( 'Gradient End Color', 'solanawp' ),
                'section' => $section_id,
                'priority' => 25,
            )));

            $wp_customize->add_setting( "{$prefix}_bg_image", array(
                'sanitize_callback' => 'esc_url_raw',
                'transport' => 'refresh',
            ));
            $wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, "{$prefix}_bg_image", array(
                'label' => __( 'Background Image', 'solanawp' ),
                'section' => $section_id,
                'priority' => 30,
            )));

            // Content Type
            $wp_customize->add_setting( "{$prefix}_content_type", array(
                'default' => 'text',
                'sanitize_callback' => 'sanitize_text_field',
                'transport' => 'refresh',
            ));
            $wp_customize->add_control( "{$prefix}_content_type", array(
                'label' => __( 'Content Type', 'solanawp' ),
                'section' => $section_id,
                'type' => 'radio',
                'choices' => array(
                    'text' => __( 'Text', 'solanawp' ),
                    'image' => __( 'Image', 'solanawp' ),
                    'slider' => __( 'Slider Shortcode', 'solanawp' ),
                    'html' => __( 'Custom HTML', 'solanawp' ),
                ),
                'priority' => 35,
            ));

            // Text Content
            $wp_customize->add_setting( "{$prefix}_text_main", array(
                'default' => '',
                'sanitize_callback' => 'wp_kses_post',
                'transport' => 'postMessage',
            ));
            $wp_customize->add_control( "{$prefix}_text_main", array(
                'label' => __( 'Main Text', 'solanawp' ),
                'section' => $section_id,
                'type' => 'textarea',
                'priority' => 40,
            ));

            $wp_customize->add_setting( "{$prefix}_text_sub", array(
                'default' => '',
                'sanitize_callback' => 'wp_kses_post',
                'transport' => 'postMessage',
            ));
            $wp_customize->add_control( "{$prefix}_text_sub", array(
                'label' => __( 'Sub Text', 'solanawp' ),
                'section' => $section_id,
                'type' => 'textarea',
                'priority' => 45,
            ));

            // Advanced Text Settings
            $wp_customize->add_setting( "{$prefix}_text_alignment", array(
                'default' => 'center',
                'sanitize_callback' => 'sanitize_text_field',
                'transport' => 'postMessage',
            ));
            $wp_customize->add_control( "{$prefix}_text_alignment", array(
                'label' => __( 'Text Alignment', 'solanawp' ),
                'section' => $section_id,
                'type' => 'select',
                'choices' => solanawp_get_alignment_choices(),
                'priority' => 50,
            ));

            // Positioning Controls
            $wp_customize->add_setting( "{$prefix}_position_x", array(
                'default' => 50,
                'sanitize_callback' => 'absint',
                'transport' => 'postMessage',
            ));
            $wp_customize->add_control( "{$prefix}_position_x", array(
                'label' => __( 'Horizontal Position (%)', 'solanawp' ),
                'section' => $section_id,
                'type' => 'range',
                'input_attrs' => array( 'min' => 0, 'max' => 100, 'step' => 1 ),
                'priority' => 55,
            ));

            $wp_customize->add_setting( "{$prefix}_position_y", array(
                'default' => 50,
                'sanitize_callback' => 'absint',
                'transport' => 'postMessage',
            ));
            $wp_customize->add_control( "{$prefix}_position_y", array(
                'label' => __( 'Vertical Position (%)', 'solanawp' ),
                'section' => $section_id,
                'type' => 'range',
                'input_attrs' => array( 'min' => 0, 'max' => 100, 'step' => 1 ),
                'priority' => 60,
            ));

            // Font Settings
            $wp_customize->add_setting( "{$prefix}_font_family", array(
                'default' => 'inherit',
                'sanitize_callback' => 'sanitize_text_field',
                'transport' => 'postMessage',
            ));
            $wp_customize->add_control( "{$prefix}_font_family", array(
                'label' => __( 'Font Family', 'solanawp' ),
                'section' => $section_id,
                'type' => 'select',
                'choices' => solanawp_get_font_choices(),
                'priority' => 65,
            ));

            $wp_customize->add_setting( "{$prefix}_font_weight", array(
                'default' => '400',
                'sanitize_callback' => 'sanitize_text_field',
                'transport' => 'postMessage',
            ));
            $wp_customize->add_control( "{$prefix}_font_weight", array(
                'label' => __( 'Font Weight', 'solanawp' ),
                'section' => $section_id,
                'type' => 'select',
                'choices' => solanawp_get_font_weight_choices(),
                'priority' => 70,
            ));

            $wp_customize->add_setting( "{$prefix}_font_size_main", array(
                'default' => '',
                'sanitize_callback' => 'absint',
                'transport' => 'postMessage',
            ));
            $wp_customize->add_control( "{$prefix}_font_size_main", array(
                'label' => __( 'Main Text Font Size (px)', 'solanawp' ),
                'section' => $section_id,
                'type' => 'number',
                'input_attrs' => array( 'min' => 10, 'max' => 100 ),
                'priority' => 75,
            ));

            $wp_customize->add_setting( "{$prefix}_font_size_sub", array(
                'default' => '',
                'sanitize_callback' => 'absint',
                'transport' => 'postMessage',
            ));
            $wp_customize->add_control( "{$prefix}_font_size_sub", array(
                'label' => __( 'Sub Text Font Size (px)', 'solanawp' ),
                'section' => $section_id,
                'type' => 'number',
                'input_attrs' => array( 'min' => 8, 'max' => 80 ),
                'priority' => 80,
            ));

            $wp_customize->add_setting( "{$prefix}_font_color", array(
                'default' => '#111827',
                'sanitize_callback' => 'sanitize_hex_color',
                'transport' => 'postMessage',
            ));
            $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, "{$prefix}_font_color", array(
                'label' => __( 'Text Color', 'solanawp' ),
                'section' => $section_id,
                'priority' => 85,
            )));

            // Animation Settings
            $wp_customize->add_setting( "{$prefix}_animation", array(
                'default' => 'none',
                'sanitize_callback' => 'sanitize_text_field',
                'transport' => 'postMessage',
            ));
            $wp_customize->add_control( "{$prefix}_animation", array(
                'label' => __( 'Text Animation', 'solanawp' ),
                'section' => $section_id,
                'type' => 'select',
                'choices' => solanawp_get_animation_choices(),
                'priority' => 90,
            ));

            // Image Content
            $wp_customize->add_setting( "{$prefix}_content_image", array(
                'sanitize_callback' => 'esc_url_raw',
                'transport' => 'refresh',
            ));
            $wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, "{$prefix}_content_image", array(
                'label' => __( 'Content Image', 'solanawp' ),
                'section' => $section_id,
                'priority' => 95,
            )));

            // Slider/HTML Content
            $wp_customize->add_setting( "{$prefix}_content_slider", array(
                'default' => '',
                'sanitize_callback' => 'sanitize_text_field',
                'transport' => 'refresh',
            ));
            $wp_customize->add_control( "{$prefix}_content_slider", array(
                'label' => __( 'Slider Shortcode', 'solanawp' ),
                'section' => $section_id,
                'type' => 'text',
                'priority' => 100,
            ));

            $wp_customize->add_setting( "{$prefix}_content_html", array(
                'default' => '',
                'sanitize_callback' => 'wp_kses_post',
                'transport' => 'refresh',
            ));
            $wp_customize->add_control( "{$prefix}_content_html", array(
                'label' => __( 'Custom HTML Content', 'solanawp' ),
                'section' => $section_id,
                'type' => 'textarea',
                'priority' => 105,
            ));

            // Size control for sidebar ads
            if (strpos($prefix, 'sidebar_ad_') !== false) {
                $wp_customize->add_setting( "{$prefix}_size", array(
                    'default' => 'large',
                    'sanitize_callback' => 'sanitize_text_field',
                    'transport' => 'refresh',
                ));
                $wp_customize->add_control( "{$prefix}_size", array(
                    'label' => __( 'Banner Size', 'solanawp' ),
                    'section' => $section_id,
                    'type' => 'select',
                    'choices' => array(
                        'large' => __( 'Large (Default)', 'solanawp' ),
                        'small' => __( 'Small', 'solanawp' ),
                    ),
                    'priority' => 110,
                ));
            }
        }

        // --- Create Advanced Banner Sections ---
        solanawp_create_advanced_banner_controls(
            $wp_customize,
            'solanawp_hero_banner_section',
            'solanawp_hero',
            __( 'Platform Banner', 'solanawp' ),
            __( 'Customize the "Advanced Blockchain Analysis Platform" banner with advanced options.', 'solanawp')
        );

        solanawp_create_advanced_banner_controls(
            $wp_customize,
            'solanawp_analyzer_banner_section',
            'solanawp_analyzer',
            __( 'Analyzer Title Banner', 'solanawp' ),
            __( 'Customize the "Solana Coins Analyzer" title banner with advanced options.', 'solanawp')
        );

        solanawp_create_advanced_banner_controls(
            $wp_customize,
            'solanawp_content_banner_section',
            'solanawp_content_banner',
            __( 'Content Area Banner', 'solanawp' ),
            __( 'Configure the banner that appears below the analyzer frame with advanced customization.', 'solanawp')
        );

        // --- Reorganized Sidebar Ads Section (Main Menu) ---
        $wp_customize->add_section( 'solanawp_sidebar_ads_section', array(
            'title'       => __( 'Sidebar Ads', 'solanawp' ),
            'panel'       => 'solanawp_theme_options_panel',
            'priority'    => 20,
            'description' => __( 'Manage all sidebar ad banners. Sequential numbering: Ad 1→Left, Ad 2→Right, Ad 3→Left, etc.', 'solanawp'),
        ));

        // Number of sidebar ads control
        $wp_customize->add_setting( 'solanawp_sidebar_ads_count', array(
            'default' => 6,
            'sanitize_callback' => 'absint',
            'transport' => 'refresh',
        ));
        $wp_customize->add_control( 'solanawp_sidebar_ads_count', array(
            'label' => __( 'Number of Sidebar Ads', 'solanawp' ),
            'description' => __( 'Ads are distributed automatically: 1→Left, 2→Right, 3→Left, 4→Right, etc.', 'solanawp' ),
            'section' => 'solanawp_sidebar_ads_section',
            'type' => 'number',
            'input_attrs' => array( 'min' => 0, 'max' => 20 ),
            'priority' => 5,
        ));

        // Create individual sidebar ad sections
        for ($i = 1; $i <= 20; $i++) {
            $position = ($i % 2 === 1) ? 'Left' : 'Right';
            $section_id = "solanawp_sidebar_ad_{$i}_section";
            $prefix = "solanawp_sidebar_ad_{$i}";

            solanawp_create_advanced_banner_controls(
                $wp_customize,
                $section_id,
                $prefix,
                sprintf(__( 'Sidebar Ad %d (%s)', 'solanawp' ), $i, $position),
                sprintf(__( 'Customize sidebar ad banner %d positioned on the %s sidebar with full advanced options.', 'solanawp' ), $i, strtolower($position))
            );
        }

        // --- Layout & Design Section ---
        $wp_customize->add_section(
            'solanawp_layout_section',
            array(
                'title'    => __( 'Layout & Design', 'solanawp' ),
                'panel'    => 'solanawp_theme_options_panel',
                'priority' => 5,
                'description' => __( 'Control header elements, spacing, and general layout options.', 'solanawp' ),
            )
        );

        // Header Height Control
        $wp_customize->add_setting(
            'solanawp_header_height',
            array(
                'default'           => 100,
                'sanitize_callback' => 'absint',
                'transport'         => 'postMessage',
            )
        );
        $wp_customize->add_control(
            'solanawp_header_height',
            array(
                'label'   => __( 'Overall Header Height Scale (%)', 'solanawp' ),
                'description' => __( 'Adjusts overall scaling of some header elements.', 'solanawp'),
                'section' => 'solanawp_layout_section',
                'type'    => 'range',
                'input_attrs' => array( 'min'  => 30, 'max'  => 150, 'step' => 10, ),
                'priority'    => 10,
            )
        );

        // Logo Size
        $wp_customize->add_setting( 'solanawp_logo_size', array(
            'default'           => 80,
            'sanitize_callback' => 'absint',
            'transport'         => 'postMessage',
        ));
        $wp_customize->add_control( 'solanawp_logo_size', array(
            'label'       => __( 'Logo Size (px)', 'solanawp' ),
            'description' => __( 'Set the width and height of the logo.', 'solanawp' ),
            'section'     => 'solanawp_layout_section',
            'type'        => 'number',
            'input_attrs' => array( 'min' => 30, 'max' => 150, 'step' => 1 ),
            'priority'    => 15,
        ));

        // Brand Name Font Size
        $wp_customize->add_setting( 'solanawp_brand_name_font_size', array(
            'default'           => 20,
            'sanitize_callback' => 'absint',
            'transport'         => 'postMessage',
        ));
        $wp_customize->add_control( 'solanawp_brand_name_font_size', array(
            'label'       => __( 'Brand Name Font Size (px)', 'solanawp' ),
            'description' => __( 'Set the font size for the HANNISOL brand name.', 'solanawp' ),
            'section'     => 'solanawp_layout_section',
            'type'        => 'number',
            'input_attrs' => array( 'min' => 10, 'max' => 50, 'step' => 1 ),
            'priority'    => 20,
        ));

        // --- Colors Section (Accent Colors) ---
        $wp_customize->add_section(
            'solanawp_colors_section',
            array(
                'title'    => __( 'Theme Accent Colors', 'solanawp' ),
                'panel'    => 'solanawp_theme_options_panel',
                'priority' => 10,
                'description' => __( 'Customize theme accent colors for buttons, links, etc.', 'solanawp' ),
            )
        );

        $wp_customize->add_setting(
            'solanawp_primary_accent_color',
            array(
                'default'           => '#3b82f6',
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
                    'section' => 'solanawp_colors_section',
                )
            )
        );

        $wp_customize->add_setting(
            'solanawp_secondary_accent_color',
            array(
                'default'           => '#8b5cf6',
                'sanitize_callback' => 'sanitize_hex_color',
                'transport'         => 'postMessage',
            )
        );
        $wp_customize->add_control(
            new WP_Customize_Color_Control(
                $wp_customize,
                'solanawp_secondary_accent_color',
                array(
                    'label'   => __( 'Secondary Accent Color (Gradients)', 'solanawp' ),
                    'section' => 'solanawp_colors_section',
                )
            )
        );

        // --- Affiliate Links Section ---
        $wp_customize->add_section( 'solanawp_affiliate_links_section', array(
            'title'       => __( 'Affiliate Links (Checker Page)', 'solanawp' ),
            'panel'       => 'solanawp_theme_options_panel',
            'priority'    => 30,
            'description' => __( 'Configure affiliate URLs for the "Recommended Security Tools" section.', 'solanawp'),
        ));

        $affiliate_items_config = array(
            'ledger' => array('label' => __('Ledger Wallet URL', 'solanawp'), 'default' => '#'),
            'vpn'    => array('label' => __('VPN Service URL', 'solanawp'), 'default' => '#'),
            'guide'  => array('label' => __('Security Guide URL', 'solanawp'), 'default' => '#'),
            'course' => array('label' => __('Crypto Course URL', 'solanawp'), 'default' => '#'),
        );

        $item_priority = 10;
        foreach ( $affiliate_items_config as $key => $config ) {
            $setting_id = "solanawp_affiliate_{$key}_url";
            $wp_customize->add_setting( $setting_id, array(
                'default' => esc_url_raw( $config['default'] ), 'transport' => 'refresh', 'sanitize_callback' => 'esc_url_raw',
            ));
            $wp_customize->add_control( $setting_id, array(
                'label' => $config['label'], 'section' => 'solanawp_affiliate_links_section', 'type' => 'url', 'priority' => $item_priority,
            ));
            $item_priority += 10;
        }

        // --- Footer Settings Section ---
        $wp_customize->add_section('solanawp_footer_settings_section', array(
            'title'    => __('Footer Settings', 'solanawp'),
            'panel'    => 'solanawp_theme_options_panel',
            'priority' => 35,
        ));

        $wp_customize->add_setting('solanawp_footer_copyright_text', array(
            'default'           => sprintf(
                esc_html__( '&copy; %1$s %2$s. All rights reserved. Theme by %3$s.', 'solanawp' ),
                date_i18n('Y'), esc_html( get_bloginfo('name') ),
                '<a href="https://www.worldgpl.com/" target="_blank" rel="noopener noreferrer author">WORLDGPL</a>'
            ),
            'sanitize_callback' => 'wp_kses_post', 'transport' => 'postMessage',
        ));
        $wp_customize->add_control('solanawp_footer_copyright_text', array(
            'label' => __('Footer Copyright Text', 'solanawp'), 'section' => 'solanawp_footer_settings_section', 'type' => 'textarea',
        ));

        if ( isset( $wp_customize->selective_refresh ) ) {
            $wp_customize->selective_refresh->add_partial( 'solanawp_footer_copyright_text', array(
                'selector' => '.site-footer .site-info',
                'render_callback' => 'solanawp_customize_partial_footer_copyright',
                'fallback_refresh' => false,
            ));
        }
    }
endif; // solanawp_customize_register


// Render Callbacks
if ( ! function_exists( 'solanawp_customize_partial_blogname' ) ) :
    function solanawp_customize_partial_blogname() {
        bloginfo( 'name' );
    }
endif;

if ( ! function_exists( 'solanawp_customize_partial_custom_logo_or_fallback' ) ) :
    function solanawp_customize_partial_custom_logo_or_fallback() {
        if ( function_exists( 'solanawp_get_logo_or_fallback_html' ) ) {
            return solanawp_get_logo_or_fallback_html();
        }
        ob_start();
        if ( function_exists( 'the_custom_logo' ) && has_custom_logo() ) {
            the_custom_logo();
        } else {
            echo '<div class="logo"><div class="logo-h">H</div></div>';
        }
        return ob_get_clean();
    }
endif;

if ( ! function_exists( 'solanawp_customize_partial_footer_copyright' ) ) :
    function solanawp_customize_partial_footer_copyright() {
        $default_copyright = sprintf(
            esc_html__( '&copy; %1$s %2$s. All rights reserved. Theme by %3$s.', 'solanawp' ),
            date_i18n('Y'), esc_html( get_bloginfo('name') ),
            '<a href="https://www.worldgpl.com/" target="_blank" rel="noopener noreferrer author">WORLDGPL</a>'
        );
        $copyright_text = get_theme_mod( 'solanawp_footer_copyright_text', $default_copyright );
        echo wp_kses_post( $copyright_text );
    }
endif;

// Preview JavaScript
if ( ! function_exists( 'solanawp_customize_preview_js' ) ) :
    function solanawp_customize_preview_js() {
        wp_enqueue_script(
            'solanawp-customizer-preview',
            get_template_directory_uri() . '/assets/js/customizer.js',
            array( 'customize-preview', 'jquery' ),
            defined('SOLANAWP_VERSION') ? SOLANAWP_VERSION : '1.0.0',
            true
        );
    }
endif;
