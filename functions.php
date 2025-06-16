<?php
/**
 * SolanaWP functions and definitions v2.0
 * Independent Left/Right Sidebar System with Optimized Layout
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
require get_template_directory() . '/inc/theme-setup.php';
if ( function_exists( 'solanawp_setup' ) ) {
    add_action( 'after_setup_theme', 'solanawp_setup' );
}

// --- Enqueue Scripts and Styles ---
require get_template_directory() . '/inc/enqueue.php';
if ( function_exists( 'solanawp_scripts_styles' ) ) {
    add_action( 'wp_enqueue_scripts', 'solanawp_scripts_styles' );
}
if ( function_exists( 'solanawp_admin_scripts_styles' ) ) {
    add_action( 'admin_enqueue_scripts', 'solanawp_admin_scripts_styles' );
}

// --- Widget Areas (Sidebars) ---
require get_template_directory() . '/inc/widget-areas.php';
if ( function_exists( 'solanawp_widgets_init' ) ) {
    add_action( 'widgets_init', 'solanawp_widgets_init' );
}

// --- Custom Widgets ---
require get_template_directory() . '/inc/custom-widgets.php';
if ( function_exists( 'solanawp_register_custom_widgets' ) ) {
    add_action( 'widgets_init', 'solanawp_register_custom_widgets' );
}

// --- Theme Customizer Additions ---
if (file_exists(get_template_directory() . '/inc/customizer.php')) {
    require get_template_directory() . '/inc/customizer.php';
    if ( function_exists( 'solanawp_customize_register' ) ) {
        add_action( 'customize_register', 'solanawp_customize_register' );
    }
    if ( function_exists( 'solanawp_customize_preview_js' ) ) {
        add_action( 'customize_preview_init', 'solanawp_customize_preview_js' );
    }
}

// --- Custom Nav Walker (if used) ---
require get_template_directory() . '/inc/navwalker.php';

// --- Custom Template Tags ---
require get_template_directory() . '/inc/template-tags.php';

// --- Template Functions ---
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

// --- AJAX Handlers - THIS WAS MISSING! ---
require get_template_directory() . '/inc/ajax-handlers.php';

// --- Admin Settings ---
require get_template_directory() . '/inc/admin-settings.php';

// --- Breadcrumbs Functionality ---
require get_template_directory() . '/inc/breadcrumbs.php';

if ( ! isset( $content_width ) ) {
    $content_width = 800;
}

// --- Advanced Left Sidebar Ad Rendering Function ---
function solanawp_render_left_sidebar_ads() {
    $ads_count = get_theme_mod( 'solanawp_sidebar_ads_count', 6 );
    $output = '';

    // Process odd numbered ads (1, 3, 5, 7...) for left sidebar
    for ( $i = 1; $i <= $ads_count; $i += 2 ) {
        $output .= solanawp_render_single_sidebar_ad($i);
    }

    return $output;
}

// --- Advanced Right Sidebar Ad Rendering Function ---
function solanawp_render_right_sidebar_ads() {
    $ads_count = get_theme_mod( 'solanawp_sidebar_ads_count', 6 );
    $output = '';

    // Process even numbered ads (2, 4, 6, 8...) for right sidebar
    for ( $i = 2; $i <= $ads_count; $i += 2 ) {
        $output .= solanawp_render_single_sidebar_ad($i);
    }

    return $output;
}

// --- Single Sidebar Ad Rendering Function ---
function solanawp_render_single_sidebar_ad($ad_number) {
    $prefix = "solanawp_sidebar_ad_{$ad_number}";

    // Check if this ad has content
    $has_content = false;
    $content_type = get_theme_mod( "{$prefix}_content_type", 'text' );

    switch ($content_type) {
        case 'text':
            $main_text = get_theme_mod( "{$prefix}_text_main", '' );
            $sub_text = get_theme_mod( "{$prefix}_text_sub", '' );
            $has_content = !empty($main_text) || !empty($sub_text);
            break;
        case 'image':
            $image_url = get_theme_mod( "{$prefix}_content_image", '' );
            $has_content = !empty($image_url);
            break;
        case 'slider':
            $slider_code = get_theme_mod( "{$prefix}_content_slider", '' );
            $has_content = !empty($slider_code);
            break;
        case 'html':
            $html_content = get_theme_mod( "{$prefix}_content_html", '' );
            $has_content = !empty($html_content);
            break;
    }

    if (!$has_content) {
        return '';
    }

    // Get settings
    $banner_url = get_theme_mod( "{$prefix}_url", '' );
    $size = get_theme_mod( "{$prefix}_size", 'large' );
    $bg_type = get_theme_mod( "{$prefix}_bg_type", 'color' );
    $bg_color = get_theme_mod( "{$prefix}_bg_color", '#ffffff' );
    $bg_gradient_start = get_theme_mod( "{$prefix}_bg_gradient_start", '#3b82f6' );
    $bg_gradient_end = get_theme_mod( "{$prefix}_bg_gradient_end", '#8b5cf6' );
    $bg_image = get_theme_mod( "{$prefix}_bg_image", '' );
    $alignment = get_theme_mod( "{$prefix}_text_alignment", 'center' );
    $position_x = get_theme_mod( "{$prefix}_position_x", 50 );
    $position_y = get_theme_mod( "{$prefix}_position_y", 50 );
    $font_family = get_theme_mod( "{$prefix}_font_family", 'inherit' );
    $font_weight = get_theme_mod( "{$prefix}_font_weight", '400' );
    $font_size_main = get_theme_mod( "{$prefix}_font_size_main", '' );
    $font_size_sub = get_theme_mod( "{$prefix}_font_size_sub", '' );
    $font_color = get_theme_mod( "{$prefix}_font_color", '#111827' );
    $animation = get_theme_mod( "{$prefix}_animation", 'none' );

    // Build banner classes
    $banner_classes = 'ad-banner';
    if ( $size === 'small' ) {
        $banner_classes .= ' small';
    }

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

    // Positioning
    if ($position_x != 50 || $position_y != 50) {
        $banner_styles[] = "display: flex";
        $banner_styles[] = "align-items: center";
        $banner_styles[] = "justify-content: center";
    }

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
    $output = '<div class="' . esc_attr($banner_classes) . '"' . $style_attr . $click_attr . '>';

    // Render content based on type
    switch ($content_type) {
        case 'image':
            $image_url = get_theme_mod( "{$prefix}_content_image", '' );
            if (!empty($image_url)) {
                $output .= '<img src="' . esc_url($image_url) . '" alt="' . esc_attr__('Sidebar Ad Image', 'solanawp') . '" style="width:100%; height:auto; display:block;">';
            }
            break;

        case 'slider':
            $slider_code = get_theme_mod( "{$prefix}_content_slider", '' );
            if (!empty($slider_code) && function_exists('do_shortcode')) {
                $output .= do_shortcode(wp_kses_post($slider_code));
            }
            break;

        case 'html':
            $html_content = get_theme_mod( "{$prefix}_content_html", '' );
            if (!empty($html_content)) {
                $output .= wp_kses_post($html_content);
            }
            break;

        default: // text
            $main_text = get_theme_mod( "{$prefix}_text_main", '' );
            $sub_text = get_theme_mod( "{$prefix}_text_sub", '' );

            if (!empty($main_text) || !empty($sub_text)) {
                $content_styles = array();
                $content_styles[] = "padding: 10px";
                $content_styles[] = "height: 100%";
                $content_styles[] = "display: flex";
                $content_styles[] = "flex-direction: column";
                $content_styles[] = "justify-content: center";
                $content_styles[] = "align-items: center";

                if ($position_x != 50 || $position_y != 50) {
                    $content_styles[] = "transform: translate(" . ($position_x - 50) . "%, " . ($position_y - 50) . "%)";
                }

                $output .= '<div style="' . esc_attr(implode('; ', $content_styles)) . '">';

                if (!empty($main_text)) {
                    $main_styles = array();
                    if (!empty($font_size_main)) {
                        $main_styles[] = "font-size: {$font_size_main}px";
                    } else {
                        $main_styles[] = $size === 'small' ? "font-size: 16px" : "font-size: 18px";
                    }
                    $main_styles[] = "margin-bottom: 8px";
                    $main_styles[] = "font-weight: bold";

                    $main_attrs = 'style="' . esc_attr(implode('; ', $main_styles)) . '"';
                    if ($animation !== 'none') {
                        $main_attrs .= ' data-animation="' . esc_attr($animation) . '"';
                    }

                    $output .= '<div ' . $main_attrs . '>' . wp_kses_post($main_text) . '</div>';
                }

                if (!empty($sub_text)) {
                    $sub_styles = array();
                    if (!empty($font_size_sub)) {
                        $sub_styles[] = "font-size: {$font_size_sub}px";
                    } else {
                        $sub_styles[] = $size === 'small' ? "font-size: 14px" : "font-size: 16px";
                    }

                    $sub_attrs = 'style="' . esc_attr(implode('; ', $sub_styles)) . '"';
                    if ($animation !== 'none') {
                        $sub_attrs .= ' data-animation="' . esc_attr($animation) . '"';
                    }

                    $output .= '<div ' . $sub_attrs . '>' . wp_kses_post($sub_text) . '</div>';
                }

                $output .= '</div>';
            }
            break;
    }

    // Admin config link
    if ( current_user_can( 'customize' ) ) {
        $customizer_link = admin_url( 'customize.php?autofocus[section]=solanawp_sidebar_ad_' . $ad_number . '_section' );
        $position = ($ad_number % 2 === 1) ? 'Left' : 'Right';
        $output .= '<div class="admin-configure-ad-link">';
        $output .= '<a href="' . esc_url( $customizer_link ) . '" title="' . esc_attr(sprintf(__('Configure Ad %d (%s)', 'solanawp'), $ad_number, $position)) . '">&#9881;</a>';
        $output .= '</div>';
    }

    $output .= '</div>';
    return $output;
}

// Function to get the right sidebar (uses independent right ads)
if ( ! function_exists( 'solanawp_get_right_sidebar' ) ) {
    function solanawp_get_right_sidebar() {
        ob_start();
        ?>
        <aside id="secondary-right" class="widget-area sidebar-right" role="complementary">
            <?php
            $right_ads_output = solanawp_render_right_sidebar_ads();
            echo $right_ads_output;

            // Fallback content if no right ads are configured
            if ( empty(trim($right_ads_output)) && current_user_can( 'customize' ) ) :
                ?>
                <div class="ad-banner" style="background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); border: 2px solid #3b82f6; display:flex; align-items:center; justify-content:center;">
                    <div style="text-align:center;">
                        <div style="font-size: 16px; margin-bottom: 8px;">⚙️ <?php esc_html_e('Setup Right Sidebar Ads', 'solanawp'); ?></div>
                        <div style="font-size: 14px;">
                            <a href="<?php echo esc_url( admin_url( 'customize.php?autofocus[section]=solanawp_sidebar_ads_section' ) ); ?>" style="color: #3b82f6; text-decoration: none; font-weight: 600;">
                                <?php esc_html_e('Even numbers (2,4,6,8...) go here','solanawp'); ?>
                            </a>
                        </div>
                    </div>
                </div>
            <?php
            endif;
            ?>
        </aside>
        <?php
        return ob_get_clean();
    }
}

// --- Enhanced Customizer CSS Output with Layout Optimization ---
if ( ! function_exists( 'solanawp_enhanced_customizer_css_output' ) ) :
    function solanawp_enhanced_customizer_css_output() {
        $header_height_scale = get_theme_mod( 'solanawp_header_height', 100 );
        $logo_size = get_theme_mod( 'solanawp_logo_size', 80 );
        $brand_name_font_size = get_theme_mod( 'solanawp_brand_name_font_size', 20 );
        $primary_color = get_theme_mod( 'solanawp_primary_accent_color', '#3b82f6' );
        $secondary_color = get_theme_mod( 'solanawp_secondary_accent_color', '#8b5cf6' );

        $css = '<style type="text/css" id="solanawp-enhanced-customizer-css">';

        // --- Enhanced Banner Styles ---
        $banners_to_style = array(
            'hero' => array('selector' => '.hero-sub-banner', 'prefix' => 'solanawp_hero'),
            'analyzer' => array('selector' => '.solana-coins-analyzer-section', 'prefix' => 'solanawp_analyzer'),
            'content_banner' => array('selector' => '.content-area-banner', 'prefix' => 'solanawp_content_banner')
        );

        foreach ($banners_to_style as $key => $config) {
            $bg_type = get_theme_mod("{$config['prefix']}_bg_type", 'color');
            $bg_color = get_theme_mod("{$config['prefix']}_bg_color", '#ffffff');
            $bg_gradient_start = get_theme_mod("{$config['prefix']}_bg_gradient_start", '#3b82f6');
            $bg_gradient_end = get_theme_mod("{$config['prefix']}_bg_gradient_end", '#8b5cf6');
            $bg_image = get_theme_mod("{$config['prefix']}_bg_image", '');

            // Background styles
            switch ($bg_type) {
                case 'gradient':
                    $css .= "{$config['selector']} { background: linear-gradient(135deg, " . esc_attr($bg_gradient_start) . " 0%, " . esc_attr($bg_gradient_end) . " 100%); }";
                    break;
                case 'image':
                    if (!empty($bg_image)) {
                        $css .= "{$config['selector']} { background-image: url(" . esc_url($bg_image) . "); background-size: cover; background-position: center; }";
                    }
                    break;
                default: // color
                    $css .= "{$config['selector']} { background-color: " . esc_attr($bg_color) . "; }";
                    break;
            }

            // Font styles
            $font_family = get_theme_mod("{$config['prefix']}_font_family", 'inherit');
            $font_weight = get_theme_mod("{$config['prefix']}_font_weight", '400');
            $font_size_main = get_theme_mod("{$config['prefix']}_font_size_main", '');
            $font_size_sub = get_theme_mod("{$config['prefix']}_font_size_sub", '');
            $font_color = get_theme_mod("{$config['prefix']}_font_color", '#111827');
            $text_alignment = get_theme_mod("{$config['prefix']}_text_alignment", 'center');
            $animation = get_theme_mod("{$config['prefix']}_animation", 'none');

            $text_selector = "{$config['selector']} h2, {$config['selector']} p, {$config['selector']} div:not([class*='container'])";
            $css .= "{$text_selector} {";
            if ($font_family !== 'inherit') { $css .= "font-family: " . esc_attr($font_family) . " !important;"; }
            if ($font_weight !== '400') { $css .= "font-weight: " . esc_attr($font_weight) . " !important;"; }
            if (!empty($font_color)) { $css .= "color: " . esc_attr($font_color) . " !important;"; }
            $css .= "text-align: " . esc_attr($text_alignment) . " !important;";
            $css .= "}";

            if (!empty($font_size_main)) {
                $main_text_selector = "{$config['selector']} h2";
                $css .= "{$main_text_selector} { font-size: " . esc_attr($font_size_main) . "px !important; }";
            }

            if (!empty($font_size_sub)) {
                $sub_text_selector = "{$config['selector']} p, {$config['selector']} div:not([class*='container']):not(h2)";
                $css .= "{$sub_text_selector} { font-size: " . esc_attr($font_size_sub) . "px !important; }";
            }

            // Animation styles
            if ($animation !== 'none') {
                $css .= "{$config['selector']} [data-animation='{$animation}'] { animation: {$animation} 1s ease-in-out; }";
            }
        }

        // --- Layout Grid System Optimization ---
        // Reduce outer margins by 3cm (113px) and expand analyzer frame
        $css .= '
        .main-container {
            grid-template-columns: 300px 1fr 300px;
            gap: 24px;
            max-width: 100vw;
            margin: 0 auto;
            padding: 24px 0px !important; /* Reduced outer padding from 24px to 0px */
            box-sizing: border-box;
            width: 100%;
            align-items: start;
        }';

        // --- Content Area Banner Width Synchronization ---
        $css .= '
        .content-area-banner {
            margin: 113px auto !important;
            width: calc(100vw - 600px - 48px) !important; /* Matches expanded analyzer frame width */
            max-width: calc(100vw - 648px) !important;
            border-radius: 16px !important;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06) !important;
            border: 1px solid #e5e7eb !important;
            transition: all 0.3s ease !important;
        }
        .content-area-banner:hover {
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1) !important;
            transform: translateY(-2px) !important;
        }
        @media (max-width: 1024px) {
            .content-area-banner {
                max-width: calc(100vw - 32px) !important;
                margin: 50px auto !important;
            }
            .main-container {
                grid-template-columns: 1fr;
                padding: 16px 0px !important;
            }
        }';

        // --- Animation Keyframes ---
        $css .= '
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes slideInLeft { from { transform: translateX(-100%); } to { transform: translateX(0); } }
        @keyframes slideInRight { from { transform: translateX(100%); } to { transform: translateX(0); } }
        @keyframes slideInUp { from { transform: translateY(100%); } to { transform: translateY(0); } }
        @keyframes bounce { 0%, 20%, 53%, 80%, 100% { transform: translate3d(0,0,0); } 40%, 43% { transform: translate3d(0, -30px, 0); } 70% { transform: translate3d(0, -15px, 0); } 90% { transform: translate3d(0, -4px, 0); } }
        @keyframes pulse { 0% { transform: scale(1); } 50% { transform: scale(1.05); } 100% { transform: scale(1); } }';

        // --- General Layout Styles ---
        if ( $header_height_scale !== 100 ) {
            $scale = $header_height_scale / 100;
            $css .= '.site-header .header { padding: ' . (16 * $scale) . 'px ' . (24 * $scale) . 'px; }';
            $css .= '.site-header .logo-h { font-size: ' . (36 * $scale) . 'px; }';
            $css .= '.logo-container::after { width: ' . (60 * $scale) . 'px; height: ' . (3 * $scale) . 'px; }';
        }

        if ( $logo_size !== 80 ) {
            $css .= '.custom-logo-link img, .site-header .logo { width: ' . esc_attr( $logo_size ) . 'px; height: ' . esc_attr( $logo_size ) . 'px; }';
        }

        if ( $brand_name_font_size !== 20 ) {
            $css .= '.site-header .brand-name, .site-header .brand-name a { font-size: ' . esc_attr( $brand_name_font_size ) . 'px; }';
        }

        // --- CSS Variables and Theme Colors ---
        $css .= '
        :root {
            --solanawp-primary-accent-color: ' . esc_attr( $primary_color ) . ';
            --solanawp-secondary-accent-color: ' . esc_attr( $secondary_color ) . ';
        }';

        $css .= '</style>';

        if (str_replace(array('<style type="text/css" id="solanawp-enhanced-customizer-css">', '</style>'), '', trim($css)) !== '') {
            echo $css;
        }
    }
endif;

// --- Existing Theme Functions (Preserved) ---
add_action('wp_head', 'hide_default_wordpress_content');
function hide_default_wordpress_content() {
    if (is_front_page()) {
        echo '<style>
        .front-page-content-area .hentry,
        .front-page-content-area article.post,
        .front-page-content-area .entry-content p,
        body.home .site-main article.post,
        body.page-template-address-checker .entry-content {
            display: none !important;
        }
        </style>';
    }
}

add_action('pre_get_posts', 'remove_default_posts_from_front_page');
function remove_default_posts_from_front_page($query) {
    if ($query->is_main_query() && is_front_page() && isset($query->is_posts_page) && $query->is_posts_page) {
        $query->set('post__in', array(0));
    }
}

add_action('wp_head', 'hannisol_center_alignment_css');
function hannisol_center_alignment_css() {
    echo '<style>
    .input-section { display: flex; justify-content: center; align-items: center; min-height: 120px; }
    .input-container { width: 100%; max-width: 800px; display: flex; justify-content: center; align-items: center; gap: 16px; }
    .main-container { display: grid; grid-template-columns: 300px 1fr 300px; gap: 24px; max-width: 100vw; margin: 0 auto; padding: 24px 0px; align-items: start; }
    .sidebar, .sidebar-right { display: flex; flex-direction: column; gap: 16px; align-items: stretch; }
    .sidebar .ad-banner, .sidebar-right .ad-banner { height: 250px; width: 100%; box-sizing: border-box; }
    .sidebar .ad-banner.small, .sidebar-right .ad-banner.small { height: 120px; width: 100%; box-sizing: border-box; }
    .content-area { align-self: start; width: 100%; }
    .results-section { padding: 32px; width: 100%; max-width: 100%; margin: 0 auto; }
    </style>';
}

// Continue with all existing functions...
add_action('init', 'remove_default_hello_world_post');
function remove_default_hello_world_post() {
    $hello_world_post = get_posts(array('title' => 'Hello world!', 'post_status' => 'any', 'numberposts' => 1));
    if (!empty($hello_world_post)) {
        wp_delete_post($hello_world_post[0]->ID, true);
    }
}

add_action('wp_head', 'hannisol_responsive_improvements');
function hannisol_responsive_improvements() {
    echo '<style>
    @media (max-width: 1200px) { .main-container { grid-template-columns: 250px 1fr 250px; gap: 20px; padding: 20px 0px; } }
    @media (max-width: 1024px) { .main-container { grid-template-columns: 1fr; gap: 16px; padding: 16px 0px; } .sidebar, .sidebar-right { order: -1; display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 16px; } .content-area { order: 0; } .ad-banner { height: 120px !important; } }
    @media (max-width: 768px) { .sidebar, .sidebar-right { grid-template-columns: 1fr; } .main-container { padding: 12px 0px; } .input-section { padding: 24px 16px; } .results-section { padding: 24px 16px; } }
    </style>';
}

// All existing navigation disabling functions preserved...
add_action('after_setup_theme', 'solanawp_disable_navigation_menus', 20);
function solanawp_disable_navigation_menus() {
    remove_theme_support('menus');
    global $_wp_registered_nav_menus;
    $_wp_registered_nav_menus = array();
}

add_action('wp_head', 'solanawp_force_hide_navigation', 999);
function solanawp_force_hide_navigation() {
    echo '<style>
   .main-navigation, .site-navigation, #site-navigation, .primary-menu, #primary-menu, .menu, nav, .nav, .navigation, .nav-menu, .navigation-menu, .menu-toggle, .menu-item, .nav-links, .navigation-links, ul.menu, ol.menu, .wp-block-navigation, .wp-block-navigation__container, .wp-block-navigation-link, .has-child .wp-block-navigation__submenu-container, [role="navigation"], .navbar, .nav-bar, .top-menu, .header-menu, .header-navigation, .site-header nav, .site-header .menu, .site-header ul, .site-header ol, header nav, header .menu, header ul:not(.no-hide), header ol:not(.no-hide), .menu-primary-container, .menu-header-container, .menu-main-container, #menu-primary, #menu-header, #menu-main, .primary-navigation, .header-nav, .main-nav, .top-nav { display: none !important; visibility: hidden !important; height: 0 !important; width: 0 !important; overflow: hidden !important; opacity: 0 !important; position: absolute !important; left: -9999px !important; top: -9999px !important; }
   .site-header ul, .site-header ol, header ul, header ol { list-style: none !important; margin: 0 !important; padding: 0 !important; }
   .site-header .menu *, .site-header nav *, header .menu *, header nav * { display: none !important; }
   </style>';
}

// All other existing functions preserved...
add_filter('wp_nav_menu', '__return_empty_string', 999);
add_filter('wp_nav_menu_args', '__return_empty_array', 999);

add_action('init', 'solanawp_remove_navigation_locations');
function solanawp_remove_navigation_locations() {
    unregister_nav_menu('primary');
    unregister_nav_menu('footer');
    unregister_nav_menu('header');
    unregister_nav_menu('main');
    unregister_nav_menu('secondary');
    unregister_nav_menu('social');
}

add_action('customize_register', 'solanawp_remove_menu_customizer_section', 20);
function solanawp_remove_menu_customizer_section($wp_customize) {
    $wp_customize->remove_section('nav');
    $wp_customize->remove_panel('nav_menus');
}

add_action('admin_menu', 'solanawp_hide_menu_admin_pages', 999);
function solanawp_hide_menu_admin_pages() {
    remove_submenu_page('themes.php', 'nav-menus.php');
}

add_action('wp_head', 'solanawp_enhanced_content_hiding');
function solanawp_enhanced_content_hiding() {
    if (is_front_page() || is_page_template('templates/template-address-checker.php')) {
        echo '<style>
       .front-page-content-area .hentry, .front-page-content-area article, .front-page-content-area .post, .front-page-content-area .entry-content, .front-page-content-area .page-content, .address-checker-content .entry-content, .address-checker-content .page-content, body.home .site-main article, body.home .site-main .post, body.page-template-address-checker .entry-content, body.page-template-address-checker .page-content, .page-intro-content, .solanawp-page-content .entry-content, .wp-block-post-content, .entry-summary, .post-content, .page-content p, .entry-content p:first-child, article.post, article.page, .type-post, .type-page { display: none !important; visibility: hidden !important; height: 0 !important; overflow: hidden !important; position: absolute !important; left: -9999px !important; }
       .input-section, .results-section, #resultsSection, .solanawp-checker-main, .checker-input-section, .address-checker-content .input-section, .address-checker-content .results-section { display: block !important; visibility: visible !important; position: static !important; height: auto !important; overflow: visible !important; }
       </style>';
    }
}

add_action('pre_get_posts', 'solanawp_remove_front_page_content');
function solanawp_remove_front_page_content($query) {
    if (!is_admin() && $query->is_main_query()) {
        if (is_front_page() || is_home()) {
            $query->set('post__in', array(0));
            $query->set('posts_per_page', 0);
        }
    }
}

add_action('wp_head', 'solanawp_edge_to_edge_layout');
function solanawp_edge_to_edge_layout() {
    echo '<style>
   body { margin: 0 !important; padding: 0 !important; }
   .site { width: 100vw; max-width: 100vw; overflow-x: hidden; }
   .site-content { width: 100%; max-width: 100%; margin: 0; padding: 0; }
   .main-container { width: 100vw; max-width: 100vw; margin: 0; box-sizing: border-box; }
   @media (min-width: 1600px) { .main-container { grid-template-columns: 320px 1fr 320px; gap: 16px; padding: 12px 0px; } .ad-banner { height: 160px; } .ad-banner.small { height: 90px; } }
   @media (min-width: 2000px) { .main-container { grid-template-columns: 400px 1fr 400px; gap: 20px; padding: 16px 0px; } .ad-banner { height: 180px; } .ad-banner.small { height: 100px; } }
   </style>';
}

add_action('wp_loaded', 'solanawp_remove_default_content');
function solanawp_remove_default_content() {
    $hello_post = get_posts(array('title' => 'Hello world!', 'post_status' => 'any', 'numberposts' => 1));
    if (!empty($hello_post)) { wp_delete_post($hello_post[0]->ID, true); }
    $sample_page = get_posts(array('title' => 'Sample Page', 'post_type' => 'page', 'post_status' => 'any', 'numberposts' => 1));
    if (!empty($sample_page)) { wp_delete_post($sample_page[0]->ID, true); }
}

// Action hooks
remove_action( 'wp_head', 'solanawp_customizer_css_output' );
add_action( 'wp_head', 'solanawp_enhanced_customizer_css_output' );

// Helper functions for color manipulation
if ( ! function_exists( 'solanawp_hex_to_rgba' ) ) :
    function solanawp_hex_to_rgba( $hex, $alpha = 1 ) {
        $hex = str_replace( '#', '', $hex );
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

if ( ! function_exists( 'solanawp_adjust_brightness' ) ) :
    function solanawp_adjust_brightness($hex, $steps) {
        $hex = str_replace('#', '', $hex);
        $r = hexdec(substr($hex,0,2));
        $g = hexdec(substr($hex,2,2));
        $b = hexdec(substr($hex,4,2));
        $r = max(0, min(255, $r + $steps));
        $g = max(0, min(255, $g + $steps));
        $b = max(0, min(255, $b + $steps));
        return '#'.str_pad(dechex($r), 2, '0', STR_PAD_LEFT)
            .str_pad(dechex($g), 2, '0', STR_PAD_LEFT)
            .str_pad(dechex($b), 2, '0', STR_PAD_LEFT);
    }
endif;

// WordPress Dashboard Integration
add_action( 'admin_bar_menu', 'solanawp_add_customizer_to_admin_bar', 999 );
function solanawp_add_customizer_to_admin_bar( $wp_admin_bar ) {
    if ( ! current_user_can( 'customize' ) ) { return; }
    $wp_admin_bar->add_node( array(
        'id'    => 'solanawp-customize',
        'title' => '<span class="ab-icon dashicons dashicons-admin-customizer"></span>SolanaWP Options',
        'href'  => admin_url( 'customize.php?autofocus[panel]=solanawp_theme_options_panel' ),
        'meta'  => array( 'title' => __( 'Customize SolanaWP Theme', 'solanawp' ), ),
    ) );
}
// Add this line once, then remove it
add_action( 'admin_menu', 'solanawp_add_customizer_menu' );
function solanawp_add_customizer_menu() {
    add_theme_page(
        __( 'SolanaWP Theme Options', 'solanawp' ),
        __( 'Theme Options', 'solanawp' ),
        'customize',
        'customize.php?autofocus[panel]=solanawp_theme_options_panel'
    );
}

add_action( 'wp_dashboard_setup', 'solanawp_add_dashboard_widget' );
function solanawp_add_dashboard_widget() {
    wp_add_dashboard_widget('solanawp_dashboard_widget', __('SolanaWP Theme Options', 'solanawp'), 'solanawp_dashboard_widget_content');
}

function solanawp_dashboard_widget_content() {
    echo '<div style="text-align: center; padding: 20px;"><h3>' . esc_html__('Customize Your Solana Address Checker', 'solanawp') . '</h3><p>' . esc_html__('Independent left/right sidebar ad management with optimized layout.', 'solanawp') . '</p><div style="margin: 20px 0;"><a href="' . esc_url(admin_url('customize.php?autofocus[section]=solanawp_layout_section')) . '" class="button button-primary" style="margin: 5px;">' . esc_html__('Layout & Design', 'solanawp') . '</a> <a href="' . esc_url(admin_url('customize.php?autofocus[section]=solanawp_sidebar_ads_section')) . '" class="button" style="margin: 5px;">' . esc_html__('Sidebar Ads', 'solanawp') . '</a> <a href="' . esc_url(admin_url('customize.php?autofocus[section]=solanawp_colors_section')) . '" class="button" style="margin: 5px;">' . esc_html__('Accent Colors', 'solanawp') . '</a></div><div style="background: #f0f9ff; padding: 15px; border-radius: 8px; margin-top: 15px;"><strong>' . esc_html__('Independent Sidebar System:', 'solanawp') . '</strong><ul style="text-align: left; margin: 10px 0;"><li>' . esc_html__('Odd numbers (1,3,5...) = Left sidebar', 'solanawp') . '</li><li>' . esc_html__('Even numbers (2,4,6...) = Right sidebar', 'solanawp') . '</li><li>' . esc_html__('Each ad has independent customization', 'solanawp') . '</li><li>' . esc_html__('Optimized layout with 3cm edge spacing', 'solanawp') . '</li></ul></div></div>';
}
?>
