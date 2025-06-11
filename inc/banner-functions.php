<?php
/**
 * Enhanced Banner Rendering Functions for SolanaWP
 * Version 6: Complete rewrite with advanced banner rendering
 *
 * This file should be saved as: /inc/banner-functions.php
 *
 * @package SolanaWP
 * @since SolanaWP 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Render an advanced customizable banner
 *
 * @param string $prefix The Customizer setting prefix
 * @param array $defaults Default values
 * @param array $options Additional rendering options
 * @return string Rendered banner HTML
 */
if ( ! function_exists( 'solanawp_render_advanced_banner' ) ) :
    function solanawp_render_advanced_banner( $prefix, $defaults = array(), $options = array() ) {
        // Check if banner is enabled
        $enabled = get_theme_mod( "{$prefix}_enabled", $defaults['enabled'] ?? true );
        if ( ! $enabled ) {
            return '';
        }

        $banner_id = str_replace( 'solanawp_', '', $prefix ) . '_banner';
        $content_type = get_theme_mod( "{$prefix}_content_type", 'text' );
        $banner_url = get_theme_mod( "{$prefix}_url", '' );
        $url_target = get_theme_mod( "{$prefix}_url_target", '_blank' );

        // Start building the banner
        ob_start();

        // Banner container with dynamic classes
        $banner_classes = array( 'solanawp-advanced-banner' );
        if ( isset( $options['css_class'] ) ) {
            $banner_classes[] = $options['css_class'];
        }
        if ( ! empty( $banner_url ) ) {
            $banner_classes[] = 'banner-clickable';
        }

        echo '<div id="' . esc_attr( $banner_id ) . '" class="' . esc_attr( implode( ' ', $banner_classes ) ) . '">';

        // If banner has URL, make entire banner clickable
        if ( ! empty( $banner_url ) && filter_var( $banner_url, FILTER_VALIDATE_URL ) ) {
            echo '<a href="' . esc_url( $banner_url ) . '" target="' . esc_attr( $url_target ) . '" rel="noopener noreferrer" class="banner-link-overlay">';
        }

        // Banner content wrapper
        echo '<div class="banner-content-wrapper">';

        // Render content based on type
        switch ( $content_type ) {
            case 'image':
                $img_url = get_theme_mod( "{$prefix}_content_image", '' );
                if ( ! empty( $img_url ) ) {
                    echo '<div class="banner-image-content">';
                    echo '<img src="' . esc_url( $img_url ) . '" alt="' . esc_attr__( 'Banner Content Image', 'solanawp' ) . '" />';
                    echo '</div>';
                }
                break;

            case 'slider':
                $slider_shortcode = get_theme_mod( "{$prefix}_content_slider", '' );
                if ( ! empty( $slider_shortcode ) && function_exists( 'do_shortcode' ) ) {
                    echo '<div class="banner-slider-content">';
                    echo do_shortcode( wp_kses_post( $slider_shortcode ) );
                    echo '</div>';
                }
                break;

            case 'html':
                $html_content = get_theme_mod( "{$prefix}_content_html", '' );
                if ( ! empty( $html_content ) ) {
                    echo '<div class="banner-html-content">';
                    echo wp_kses_post( $html_content );
                    echo '</div>';
                }
                break;

            default: // 'text' case
                $main_text = get_theme_mod( "{$prefix}_text_main", $defaults['main_text'] ?? '' );
                $sub_text = get_theme_mod( "{$prefix}_text_sub", $defaults['sub_text'] ?? '' );
                $text_alignment = get_theme_mod( "{$prefix}_text_alignment", 'center' );
                $text_animation = get_theme_mod( "{$prefix}_text_animation", 'none' );

                if ( ! empty( $main_text ) || ! empty( $sub_text ) ) {
                    $animation_class = $text_animation !== 'none' ? 'banner-animate-' . $text_animation : '';
                    echo '<div class="banner-text-content text-align-' . esc_attr( $text_alignment ) . ' ' . esc_attr( $animation_class ) . '">';

                    if ( ! empty( $main_text ) ) {
                        echo '<h2 class="banner-main-text">' . wp_kses_post( $main_text ) . '</h2>';
                    }

                    if ( ! empty( $sub_text ) ) {
                        echo '<div class="banner-sub-text">' . wp_kses_post( $sub_text ) . '</div>';
                    }

                    echo '</div>';
                }
                break;
        }

        echo '</div>'; // Close banner-content-wrapper

        // Close link if applicable
        if ( ! empty( $banner_url ) && filter_var( $banner_url, FILTER_VALIDATE_URL ) ) {
            echo '</a>';
        }

        echo '</div>'; // Close banner container

        return ob_get_clean();
    }
endif;

/**
 * Render sidebar ad banners
 *
 * @param int $max_count Maximum number of ads to render
 * @return string Rendered ads HTML
 */
if ( ! function_exists( 'solanawp_render_advanced_sidebar_ads' ) ) :
    function solanawp_render_advanced_sidebar_ads( $max_count = 20 ) {
        $ads_count = get_theme_mod( 'solanawp_sidebar_ads_count', 3 );
        $ads_count = min( $ads_count, $max_count ); // Limit to max_count

        ob_start();

        for ( $i = 1; $i <= $ads_count; $i++ ) {
            $prefix = "solanawp_sidebar_ad_{$i}";
            $enabled = get_theme_mod( "{$prefix}_enabled", false );

            if ( $enabled ) {
                echo '<div class="sidebar-ad-wrapper">';
                echo solanawp_render_advanced_banner( $prefix, array(
                    'enabled' => false,
                    'main_text' => sprintf( __( 'Ad Banner %d', 'solanawp' ), $i ),
                    'sub_text' => __( 'Your advertisement content here', 'solanawp' ),
                ), array(
                    'css_class' => 'sidebar-ad-banner',
                ));
                echo '</div>';
            }
        }

        // Fallback content if no ads are configured and user can customize
        $output = ob_get_clean();
        if ( empty( trim( $output ) ) && current_user_can( 'customize' ) ) {
            ob_start();
            ?>
            <div class="sidebar-ad-wrapper">
                <div class="ad-banner sidebar-ad-banner placeholder-ad" style="background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); border: 2px solid #3b82f6;">
                    <div class="banner-content-wrapper">
                        <div class="banner-text-content text-align-center">
                            <h3 style="font-size: 16px; margin-bottom: 8px;">⚙️ <?php esc_html_e('Setup Sidebar Ads', 'solanawp'); ?></h3>
                            <div style="font-size: 14px;">
                                <a href="<?php echo esc_url( admin_url( 'customize.php?autofocus[panel]=solanawp_sidebar_ads_panel' ) ); ?>"
                                   style="color: #3b82f6; text-decoration: none; font-weight: 600;">
                                    <?php esc_html_e('Click to Configure Advanced Ads', 'solanawp'); ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php
            return ob_get_clean();
        }

        return $output;
    }
endif;

/**
 * Generate dynamic CSS for advanced banners
 *
 * @return string CSS output
 */
if ( ! function_exists( 'solanawp_generate_advanced_banner_css' ) ) :
    function solanawp_generate_advanced_banner_css() {
        $css = '';

        // Banner prefixes to generate CSS for
        $banner_configs = array(
            'solanawp_hero' => '.hero-sub-banner',
            'solanawp_analyzer' => '.solana-coins-analyzer-section',
            'solanawp_content_banner' => '.content-area-banner',
        );

        // Add dynamic sidebar ads
        $ads_count = get_theme_mod( 'solanawp_sidebar_ads_count', 3 );
        for ( $i = 1; $i <= $ads_count; $i++ ) {
            $banner_configs["solanawp_sidebar_ad_{$i}"] = ".sidebar-ad-banner:nth-child({$i})";
        }

        foreach ( $banner_configs as $prefix => $selector ) {
            $enabled = get_theme_mod( "{$prefix}_enabled", true );
            if ( ! $enabled && strpos( $prefix, 'sidebar_ad' ) === false ) {
                $css .= "{$selector} { display: none !important; }";
                continue;
            }

            // Background styles
            $bg_type = get_theme_mod( "{$prefix}_bg_type", 'color' );
            $bg_color = get_theme_mod( "{$prefix}_bg_color", '#ffffff' );

            switch ( $bg_type ) {
                case 'gradient':
                    $gradient_start = get_theme_mod( "{$prefix}_bg_gradient_start", '#3b82f6' );
                    $gradient_end = get_theme_mod( "{$prefix}_bg_gradient_end", '#8b5cf6' );
                    $gradient_direction = get_theme_mod( "{$prefix}_bg_gradient_direction", '135deg' );
                    $css .= "{$selector} { background: linear-gradient({$gradient_direction}, {$gradient_start} 0%, {$gradient_end} 100%); }";
                    break;

                case 'image':
                    $bg_image = get_theme_mod( "{$prefix}_bg_image", '' );
                    if ( ! empty( $bg_image ) ) {
                        $bg_size = get_theme_mod( "{$prefix}_bg_image_size", 'cover' );
                        $bg_position = get_theme_mod( "{$prefix}_bg_image_position", 'center center' );
                        $css .= "{$selector} {
                            background-image: url(" . esc_url( $bg_image ) . ");
                            background-size: {$bg_size};
                            background-position: {$bg_position};
                            background-repeat: no-repeat;
                        }";
                    }
                    break;

                default: // color
                    $css .= "{$selector} { background-color: {$bg_color}; }";
                    break;
            }

            // Font and text styles
            $font_family = get_theme_mod( "{$prefix}_font_family", 'inherit' );
            $font_size = get_theme_mod( "{$prefix}_font_size", 24 );
            $font_weight = get_theme_mod( "{$prefix}_font_weight", '400' );
            $font_color = get_theme_mod( "{$prefix}_font_color", '#111827' );
            $text_alignment = get_theme_mod( "{$prefix}_text_alignment", 'center' );
            $text_pos_x = get_theme_mod( "{$prefix}_text_position_x", 50 );
            $text_pos_y = get_theme_mod( "{$prefix}_text_position_y", 50 );

            $text_selector = "{$selector} .banner-text-content";
            $css .= "{$text_selector} {";
            if ( $font_family !== 'inherit' ) {
                $css .= "font-family: {$font_family} !important;";
            }
            $css .= "color: {$font_color} !important;";
            $css .= "text-align: {$text_alignment};";
            $css .= "position: relative;";
            if ( $text_pos_x != 50 || $text_pos_y != 50 ) {
                $css .= "transform: translate({$text_pos_x}%, {$text_pos_y}%);";
            }
            $css .= "}";

            // Main text styles
            $css .= "{$text_selector} .banner-main-text {";
            $css .= "font-size: {$font_size}px !important;";
            $css .= "font-weight: {$font_weight} !important;";
            $css .= "margin-bottom: 0.5em;";
            $css .= "}";

            // Sub text styles
            $css .= "{$text_selector} .banner-sub-text {";
            $css .= "font-size: " . ($font_size * 0.7) . "px !important;";
            $css .= "opacity: 0.9;";
            $css .= "}";

            // Spacing for banners that support it
            $padding_top = get_theme_mod( "{$prefix}_padding_top", null );
            $padding_bottom = get_theme_mod( "{$prefix}_padding_bottom", null );
            if ( $padding_top !== null || $padding_bottom !== null ) {
                $css .= "{$selector} {";
                if ( $padding_top !== null ) {
                    $css .= "padding-top: {$padding_top}px !important;";
                }
                if ( $padding_bottom !== null ) {
                    $css .= "padding-bottom: {$padding_bottom}px !important;";
                }
                $css .= "}";
            }

            // Animation styles
            $animation = get_theme_mod( "{$prefix}_text_animation", 'none' );
            $animation_duration = get_theme_mod( "{$prefix}_animation_duration", 1 );

            if ( $animation !== 'none' ) {
                $css .= "{$text_selector}.banner-animate-{$animation} {";
                $css .= "animation: solanawp-{$animation} {$animation_duration}s ease-in-out;";
                $css .= "}";
            }
        }

        // Add animation keyframes
        $css .= "
        @keyframes solanawp-fade-in { from { opacity: 0; } to { opacity: 1; } }
        @keyframes solanawp-slide-up { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes solanawp-slide-down { from { opacity: 0; transform: translateY(-30px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes solanawp-slide-left { from { opacity: 0; transform: translateX(30px); } to { opacity: 1; transform: translateX(0); } }
        @keyframes solanawp-slide-right { from { opacity: 0; transform: translateX(-30px); } to { opacity: 1; transform: translateX(0); } }
        @keyframes solanawp-zoom-in { from { opacity: 0; transform: scale(0.8); } to { opacity: 1; transform: scale(1); } }
        @keyframes solanawp-bounce {
            0%, 20%, 53%, 80%, 100% { transform: translateY(0); }
            40%, 43% { transform: translateY(-15px); }
            70% { transform: translateY(-7px); }
        }
        @keyframes solanawp-pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }";

        // Content Area Banner specific positioning
        $content_banner_enabled = get_theme_mod( 'solanawp_content_banner_enabled', false );
        if ( $content_banner_enabled ) {
            $css .= "
            .content-area-banner {
                position: relative;
                width: 100%;
                max-width: 100%;
                margin: 113px auto; /* 3cm spacing */
                border: 2px solid #e5e7eb;
                border-radius: 16px;
                background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
                box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
                transition: all 0.3s ease;
                overflow: hidden;
            }
            .content-area-banner:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
                border-color: var(--solanawp-primary-accent-color, #3b82f6);
            }
            .content-area-banner .banner-link-overlay {
                display: block;
                width: 100%;
                height: 100%;
                text-decoration: none;
                color: inherit;
            }
            .content-area-banner .banner-content-wrapper {
                padding: 20px;
                text-align: center;
                position: relative;
                z-index: 1;
            }";
        }

        return $css;
    }
endif;

/**
 * Get the enhanced right sidebar content
 *
 * @return string Sidebar HTML
 */
if ( ! function_exists( 'solanawp_get_enhanced_right_sidebar' ) ) :
    function solanawp_get_enhanced_right_sidebar() {
        ob_start();
        ?>
        <aside id="secondary-right" class="widget-area sidebar-right" role="complementary">
            <?php echo solanawp_render_advanced_sidebar_ads( 20 ); ?>
        </aside>
        <?php
        return ob_get_clean();
    }
endif;

/**
 * Enhanced header banner rendering function
 *
 * @param string $prefix The banner prefix
 * @param array $defaults Default values
 * @param string $fallback_class Fallback CSS class
 */
if ( ! function_exists( 'solanawp_render_header_banner' ) ) :
    function solanawp_render_header_banner( $prefix, $defaults, $fallback_class ) {
        $enabled = get_theme_mod( "{$prefix}_enabled", $defaults['enabled'] ?? true );
        if ( ! $enabled ) {
            return;
        }

        echo solanawp_render_advanced_banner( $prefix, $defaults, array(
            'css_class' => $fallback_class
        ));
    }
endif;

/**
 * Render content area banner if enabled
 */
if ( ! function_exists( 'solanawp_render_content_banner' ) ) :
    function solanawp_render_content_banner() {
        $enabled = get_theme_mod( 'solanawp_content_banner_enabled', false );
        if ( ! $enabled ) {
            return '';
        }

        return solanawp_render_advanced_banner( 'solanawp_content_banner', array(
            'enabled' => false,
            'main_text' => __( 'Your Banner Title', 'solanawp' ),
            'sub_text' => __( 'Your banner description or call to action.', 'solanawp' ),
        ), array(
            'css_class' => 'content-area-banner'
        ));
    }
endif;
