<?php
/**
 * The sidebar containing the main widget area - UPDATED
 * This template is called by get_sidebar().
 * CHANGES: Now uses WordPress Customizer settings for ad banners
 *
 * @package SolanaWP
 * @since SolanaWP 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<aside id="secondary" class="widget-area sidebar" role="complementary">
    <?php
    // LEFT SIDEBAR BANNERS - Using Customizer Settings
    ?>
    <div class="ad-banner-widget-area">
        <?php
        // Left Banner 1 - Get settings from customizer
        $left_banner_1_title = get_theme_mod('solanawp_left_banner_1_title', 'Premium Ads');
        $left_banner_1_desc = get_theme_mod('solanawp_left_banner_1_description', 'Crypto Ad Network');
        $left_banner_1_details = get_theme_mod('solanawp_left_banner_1_details', '$0.50-$2.00 CPM');
        $left_banner_1_url = get_theme_mod('solanawp_left_banner_1_url', '#');

        if (!empty($left_banner_1_title) || !empty($left_banner_1_desc)) :
            ?>
            <div class="widget solanawp-ad-widget">
                <div class="ad-banner">
                    <?php if (!empty($left_banner_1_url) && $left_banner_1_url !== '#') : ?>
                    <a href="<?php echo esc_url($left_banner_1_url); ?>" target="_blank" rel="noopener noreferrer" style="color: inherit; text-decoration: none;">
                        <?php endif; ?>

                        <div>
                            <?php if (!empty($left_banner_1_title)) : ?>
                                <div class="ad-title"><?php echo esc_html($left_banner_1_title); ?></div>
                            <?php endif; ?>

                            <?php if (!empty($left_banner_1_desc)) : ?>
                                <div class="ad-description"><?php echo esc_html($left_banner_1_desc); ?></div>
                            <?php endif; ?>

                            <?php if (!empty($left_banner_1_details)) : ?>
                                <div class="ad-details"><?php echo esc_html($left_banner_1_details); ?></div>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($left_banner_1_url) && $left_banner_1_url !== '#') : ?>
                    </a>
                <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php
        // Left Banner 2 - Get settings from customizer
        $left_banner_2_title = get_theme_mod('solanawp_left_banner_2_title', 'A-ADS');
        $left_banner_2_desc = get_theme_mod('solanawp_left_banner_2_description', 'Bitcoin Advertising');
        $left_banner_2_details = get_theme_mod('solanawp_left_banner_2_details', 'High CTR Rates');
        $left_banner_2_url = get_theme_mod('solanawp_left_banner_2_url', '#');

        if (!empty($left_banner_2_title) || !empty($left_banner_2_desc)) :
            ?>
            <div class="widget solanawp-ad-widget">
                <div class="ad-banner small">
                    <?php if (!empty($left_banner_2_url) && $left_banner_2_url !== '#') : ?>
                    <a href="<?php echo esc_url($left_banner_2_url); ?>" target="_blank" rel="noopener noreferrer" style="color: inherit; text-decoration: none;">
                        <?php endif; ?>

                        <div>
                            <?php if (!empty($left_banner_2_title)) : ?>
                                <div class="ad-title"><?php echo esc_html($left_banner_2_title); ?></div>
                            <?php endif; ?>

                            <?php if (!empty($left_banner_2_desc)) : ?>
                                <div class="ad-description"><?php echo esc_html($left_banner_2_desc); ?></div>
                            <?php endif; ?>

                            <?php if (!empty($left_banner_2_details)) : ?>
                                <div class="ad-details"><?php echo esc_html($left_banner_2_details); ?></div>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($left_banner_2_url) && $left_banner_2_url !== '#') : ?>
                    </a>
                <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php
    // Display the "Main Sidebar" if it has widgets
    if ( is_active_sidebar( 'sidebar-1' ) ) :
        ?>
        <div class="main-sidebar-widget-area">
            <?php dynamic_sidebar( 'sidebar-1' ); ?>
        </div>
    <?php endif; ?>
</aside>

<aside id="tertiary" class="widget-area sidebar sidebar-right" role="complementary">
    <?php
    // RIGHT SIDEBAR BANNERS - Using Customizer Settings
    ?>
    <div class="ad-banner-widget-area">
        <?php
        // Right Banner 1 - Get settings from customizer
        $right_banner_1_title = get_theme_mod('solanawp_right_banner_1_title', 'Coinzilla');
        $right_banner_1_desc = get_theme_mod('solanawp_right_banner_1_description', 'Premium Crypto Ads');
        $right_banner_1_details = get_theme_mod('solanawp_right_banner_1_details', 'Advanced Targeting');
        $right_banner_1_url = get_theme_mod('solanawp_right_banner_1_url', '#');

        if (!empty($right_banner_1_title) || !empty($right_banner_1_desc)) :
            ?>
            <div class="widget solanawp-ad-widget">
                <div class="ad-banner">
                    <?php if (!empty($right_banner_1_url) && $right_banner_1_url !== '#') : ?>
                    <a href="<?php echo esc_url($right_banner_1_url); ?>" target="_blank" rel="noopener noreferrer" style="color: inherit; text-decoration: none;">
                        <?php endif; ?>

                        <div>
                            <?php if (!empty($right_banner_1_title)) : ?>
                                <div class="ad-title"><?php echo esc_html($right_banner_1_title); ?></div>
                            <?php endif; ?>

                            <?php if (!empty($right_banner_1_desc)) : ?>
                                <div class="ad-description"><?php echo esc_html($right_banner_1_desc); ?></div>
                            <?php endif; ?>

                            <?php if (!empty($right_banner_1_details)) : ?>
                                <div class="ad-details"><?php echo esc_html($right_banner_1_details); ?></div>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($right_banner_1_url) && $right_banner_1_url !== '#') : ?>
                    </a>
                <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php
        // Right Banner 2 - Get settings from customizer
        $right_banner_2_title = get_theme_mod('solanawp_right_banner_2_title', 'Trading Bot');
        $right_banner_2_desc = get_theme_mod('solanawp_right_banner_2_description', 'Automated Trading');
        $right_banner_2_details = get_theme_mod('solanawp_right_banner_2_details', '24/7 Trading');
        $right_banner_2_url = get_theme_mod('solanawp_right_banner_2_url', '#');

        if (!empty($right_banner_2_title) || !empty($right_banner_2_desc)) :
            ?>
            <div class="widget solanawp-ad-widget">
                <div class="ad-banner small">
                    <?php if (!empty($right_banner_2_url) && $right_banner_2_url !== '#') : ?>
                    <a href="<?php echo esc_url($right_banner_2_url); ?>" target="_blank" rel="noopener noreferrer" style="color: inherit; text-decoration: none;">
                        <?php endif; ?>

                        <div>
                            <?php if (!empty($right_banner_2_title)) : ?>
                                <div class="ad-title"><?php echo esc_html($right_banner_2_title); ?></div>
                            <?php endif; ?>

                            <?php if (!empty($right_banner_2_desc)) : ?>
                                <div class="ad-description"><?php echo esc_html($right_banner_2_desc); ?></div>
                            <?php endif; ?>

                            <?php if (!empty($right_banner_2_details)) : ?>
                                <div class="ad-details"><?php echo esc_html($right_banner_2_details); ?></div>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($right_banner_2_url) && $right_banner_2_url !== '#') : ?>
                    </a>
                <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php
    // Display the "Ad Banner Sidebar" if it has widgets
    if ( is_active_sidebar( 'ad-banner-sidebar' ) ) :
        ?>
        <div class="additional-ad-sidebar-widget-area">
            <?php dynamic_sidebar( 'ad-banner-sidebar' ); ?>
        </div>
    <?php endif; ?>
</aside>
