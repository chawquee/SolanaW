<?php
/**
 * Enhanced front-page.php with Token Analytics section added
 * Version 10: Added Token Analytics between validation and balance sections
 * File location: front-page.php (root directory)
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
    <div class="main-container">
        <?php get_sidebar(); // This now uses advanced customizer settings ?>

        <div id="primary" class="content-area front-page-content-area address-checker-content">
            <main id="main" class="site-main solanawp-checker-main" role="main">
                <?php // --- Input Section for the address checker --- ?>
                <?php get_template_part( 'template-parts/checker/input-section' ); ?>

                <?php // --- Results Section container --- ?>
                <div class="results-section" id="resultsSection">
                    <?php get_template_part( 'template-parts/checker/results-validation' ); ?>

                    <?php // NEW: Token Analytics section - positioned after validation ?>
                    <?php get_template_part( 'template-parts/checker/results-token-analytics' ); ?>

                    <?php get_template_part( 'template-parts/checker/results-balance' ); ?>
                    <?php get_template_part( 'template-parts/checker/results-transactions' ); ?>

                    <div id="accountAndSecurityOuterGrid" class="account-security-grid-wrapper" style="display:none;">
                        <?php get_template_part( 'template-parts/checker/results-account-details' ); ?>
                        <?php get_template_part( 'template-parts/checker/results-security' ); ?>
                    </div>

                    <?php get_template_part( 'template-parts/checker/results-rugpull' ); ?>
                    <?php get_template_part( 'template-parts/checker/results-website-social' ); ?>
                    <?php /* DEACTIVATED: Community section - Keep for future updates
                    get_template_part( 'template-parts/checker/results-community' );
                    */ ?>
                    <?php get_template_part( 'template-parts/checker/results-affiliate' ); ?>
                    <?php get_template_part( 'template-parts/checker/results-final' ); ?>
                </div>
            </main>
        </div>

        <?php
        // Right sidebar with advanced ads
        if ( function_exists( 'solanawp_get_right_sidebar' ) ) {
            echo solanawp_get_right_sidebar();
        }
        ?>
    </div>

<?php
// --- Advanced Content Area Banner (Independent, Below Main Container) ---
solanawp_render_content_area_banner();
?>

    <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "WebApplication",
            "name": "Hannisol Solana Address Checker",
            "url": "<?php echo esc_url( home_url( '/' ) ); ?>",
            "description": "Comprehensive validation and analysis for Solana addresses. Hannisol's Insight, Navigating Crypto Like Hannibal Crossed the Alps.",
            "applicationCategory": "Cryptocurrency Tool",
            "operatingSystem": "Web Browser",
            "offers": {
                "@type": "Offer",
                "price": "0",
                "priceCurrency": "USD"
            },
            "creator": {
                "@type": "Organization",
                "name": "Hannisol",
                "url": "<?php echo esc_url( home_url( '/' ) ); ?>"
            }
        }
    </script>

<?php
get_footer(); // Includes footer.php

/**
 * Render the advanced content area banner with full customization
 * This function is also used in template-address-checker.php
 */
function solanawp_render_content_area_banner() {
    $prefix = 'solanawp_content_banner';
    $content_type = get_theme_mod("{$prefix}_content_type", 'text');

    // Check if banner has any content
    $has_content = false;
    switch ($content_type) {
        case 'text':
            $main_text = get_theme_mod("{$prefix}_text_main", '');
            $sub_text = get_theme_mod("{$prefix}_text_sub", '');
            $has_content = !empty($main_text) || !empty($sub_text);
            break;
        case 'image':
            $image_url = get_theme_mod("{$prefix}_content_image", '');
            $has_content = !empty($image_url);
            break;
        case 'slider':
            $slider_code = get_theme_mod("{$prefix}_content_slider", '');
            $has_content = !empty($slider_code);
            break;
        case 'html':
            $html_content = get_theme_mod("{$prefix}_content_html", '');
            $has_content = !empty($html_content);
            break;
    }

    if (!$has_content) {
        return;
    }

    // Get banner settings
    $banner_url = get_theme_mod("{$prefix}_url", '');
    $bg_type = get_theme_mod("{$prefix}_bg_type", 'color');
    $bg_color = get_theme_mod("{$prefix}_bg_color", '#ffffff');
    $bg_gradient_start = get_theme_mod("{$prefix}_bg_gradient_start", '#3b82f6');
    $bg_gradient_end = get_theme_mod("{$prefix}_bg_gradient_end", '#8b5cf6');
    $bg_image = get_theme_mod("{$prefix}_bg_image", '');
    $alignment = get_theme_mod("{$prefix}_text_alignment", 'center');
    $position_x = get_theme_mod("{$prefix}_position_x", 50);
    $position_y = get_theme_mod("{$prefix}_position_y", 50);
    $font_family = get_theme_mod("{$prefix}_font_family", 'inherit');
    $font_weight = get_theme_mod("{$prefix}_font_weight", '400');
    $font_size_main = get_theme_mod("{$prefix}_font_size_main", '');
    $font_size_sub = get_theme_mod("{$prefix}_font_size_sub", '');
    $font_color = get_theme_mod("{$prefix}_font_color", '#111827');
    $animation = get_theme_mod("{$prefix}_animation", 'none');

    // Build banner styles
    $banner_styles = array();

    // Background styling
    switch ($bg_type) {
        case 'gradient':
            $banner_styles[] = "background: linear-gradient(135deg, {$bg_gradient_start} 0%, {$bg_gradient_end} 100%)";
            break;
        case 'image':
            if (!empty($bg_image)) {
                $banner_styles[] = "background-image: url(" . esc_url($bg_image) . ")";
                $banner_styles[] = "background-size: cover";
                $banner_styles[] = "background-position: center";
            }
            break;
        default: // color
            $banner_styles[] = "background-color: {$bg_color}";
            break;
    }

    // Text styling
    if ($font_family !== 'inherit') {
        $banner_styles[] = "font-family: {$font_family}";
    }
    if ($font_weight !== '400') {
        $banner_styles[] = "font-weight: {$font_weight}";
    }
    $banner_styles[] = "color: {$font_color}";
    $banner_styles[] = "text-align: {$alignment}";

    // Clickable functionality
    $clickable = !empty($banner_url) && filter_var($banner_url, FILTER_VALIDATE_URL);
    if ($clickable) {
        $banner_styles[] = "cursor: pointer";
    }

    // Animation
    if ($animation !== 'none') {
        $banner_styles[] = "animation: {$animation} 1s ease-in-out";
    }

    $style_attr = !empty($banner_styles) ? ' style="' . esc_attr(implode('; ', $banner_styles)) . '"' : '';
    $click_attr = $clickable ? ' onclick="window.open(\'' . esc_url($banner_url) . '\', \'_blank\')"' : '';

    // Start banner output
    echo '<div class="content-area-banner card"' . $style_attr . $click_attr . '>';
    echo '<div class="card-content">';

    // Render content based on type
    switch ($content_type) {
        case 'image':
            $image_url = get_theme_mod("{$prefix}_content_image", '');
            if (!empty($image_url)) {
                echo '<img src="' . esc_url($image_url) . '" alt="' . esc_attr__('Content Banner Image', 'solanawp') . '" style="width:100%; height:auto; display:block;">';
            }
            break;

        case 'slider':
            $slider_code = get_theme_mod("{$prefix}_content_slider", '');
            if (!empty($slider_code) && function_exists('do_shortcode')) {
                echo do_shortcode(wp_kses_post($slider_code));
            }
            break;

        case 'html':
            $html_content = get_theme_mod("{$prefix}_content_html", '');
            if (!empty($html_content)) {
                echo wp_kses_post($html_content);
            }
            break;

        default: // text
            $main_text = get_theme_mod("{$prefix}_text_main", '');
            $sub_text = get_theme_mod("{$prefix}_text_sub", '');

            if (!empty($main_text) || !empty($sub_text)) {
                $content_styles = array();
                $content_styles[] = "display: flex";
                $content_styles[] = "flex-direction: column";
                $content_styles[] = "justify-content: center";
                $content_styles[] = "align-items: center";

                if ($position_x != 50 || $position_y != 50) {
                    $content_styles[] = "transform: translate(" . ($position_x - 50) . "%, " . ($position_y - 50) . "%)";
                }

                echo '<div style="' . esc_attr(implode('; ', $content_styles)) . '">';

                if (!empty($main_text)) {
                    $main_styles = array();
                    if (!empty($font_size_main)) {
                        $main_styles[] = "font-size: {$font_size_main}px";
                    } else {
                        $main_styles[] = "font-size: 24px";
                    }
                    $main_styles[] = "margin-bottom: 12px";
                    $main_styles[] = "font-weight: bold";

                    $main_attrs = 'style="' . esc_attr(implode('; ', $main_styles)) . '"';
                    if ($animation !== 'none') {
                        $main_attrs .= ' data-animation="' . esc_attr($animation) . '"';
                    }

                    echo '<h3 ' . $main_attrs . '>' . wp_kses_post($main_text) . '</h3>';
                }

                if (!empty($sub_text)) {
                    $sub_styles = array();
                    if (!empty($font_size_sub)) {
                        $sub_styles[] = "font-size: {$font_size_sub}px";
                    } else {
                        $sub_styles[] = "font-size: 16px";
                    }

                    $sub_attrs = 'style="' . esc_attr(implode('; ', $sub_styles)) . '"';
                    if ($animation !== 'none') {
                        $sub_attrs .= ' data-animation="' . esc_attr($animation) . '"';
                    }

                    echo '<div ' . $sub_attrs . '>' . wp_kses_post($sub_text) . '</div>';
                }

                echo '</div>';
            }
            break;
    }

    // Admin config link
    if ( current_user_can( 'customize' ) ) {
        $customizer_link = admin_url( 'customize.php?autofocus[section]=solanawp_content_banner_section' );
        echo '<div class="admin-configure-ad-link">';
        echo '<a href="' . esc_url( $customizer_link ) . '" title="' . esc_attr__('Configure Content Banner', 'solanawp') . '">&#9881;</a>';
        echo '</div>';
    }

    echo '</div>';
    echo '</div>';
}
?>
