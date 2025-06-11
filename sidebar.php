<?php
/**
 * The sidebar (left) containing independent left sidebar ads.
 * Version 2.0: Independent left/right sidebar system
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
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
    // Display left sidebar ads (odd numbers: 1, 3, 5, 7...)
    if ( function_exists( 'solanawp_render_left_sidebar_ads' ) ) {
        $left_ads_output = solanawp_render_left_sidebar_ads();
        echo $left_ads_output;

        // Fallback content if no left ads are configured AND user can customize
        if ( empty(trim($left_ads_output)) && current_user_can('customize') ) :
            ?>
            <div class="ad-banner" style="background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); border: 2px solid #3b82f6; display:flex; align-items:center; justify-content:center;">
                <div style="text-align:center;">
                    <div style="font-size: 16px; margin-bottom: 8px;">⚙️ <?php esc_html_e('Setup Left Sidebar Ads', 'solanawp'); ?></div>
                    <div style="font-size: 14px;">
                        <a href="<?php echo esc_url( admin_url( 'customize.php?autofocus[section]=solanawp_sidebar_ads_section' ) ); ?>"
                           style="color: #3b82f6; text-decoration: none; font-weight: 600;">
                            <?php esc_html_e('Odd numbers (1,3,5,7...) go here', 'solanawp'); ?>
                        </a>
                    </div>
                    <div style="font-size: 12px; margin-top: 8px; opacity: 0.8;">
                        <?php esc_html_e('✨ Independent left sidebar system', 'solanawp'); ?>
                    </div>
                </div>
            </div>
        <?php endif;
    } else {
        // Fallback if function doesn't exist
        ?>
        <div class="ad-banner" style="background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%); border: 2px solid #ef4444; display:flex; align-items:center; justify-content:center;">
            <div style="text-align:center;">
                <div style="font-size: 16px; margin-bottom: 8px;">⚠️ <?php esc_html_e('Function Missing', 'solanawp'); ?></div>
                <div style="font-size: 14px; color: #dc2626;">
                    <?php esc_html_e('Left sidebar rendering function not available', 'solanawp'); ?>
                </div>
            </div>
        </div>
        <?php
    }
    ?>
</aside>
