<?php
/**
 * Template part for displaying the header branding (logo, site title/tagline) - UPDATED
 * Called from header.php.
 * CHANGES: Title changed to "Solana Coins Address Checker"
 *
 * @package SolanaWP
 * @since SolanaWP 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="site-branding">
    <div class="logo-container">
        <?php
        if ( function_exists( 'solanawp_get_logo_or_fallback_html' ) ) {
            echo solanawp_get_logo_or_fallback_html();
        } elseif ( function_exists( 'the_custom_logo' ) && has_custom_logo() ) {
            the_custom_logo();
        } else {
            ?>
            <div class="logo">
                <div class="logo-h">H</div>
            </div>
            <?php
        }
        ?>
    </div>

    <div class="site-title-tagline">
        <?php if ( get_bloginfo( 'name' ) && ( is_front_page() && is_home() ) ) : ?>
            <h1 class="brand-name"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php echo esc_html__( 'HANNISOL', 'solanawp' ); ?></a></h1>
        <?php else : ?>
            <p class="brand-name"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php echo esc_html__( 'HANNISOL', 'solanawp' ); ?></a></p>
        <?php endif; ?>

        <?php
        $solanawp_description = get_bloginfo( 'description', 'display' );
        if ( $solanawp_description || is_customize_preview() ) :
            ?>
            <p class="site-description screen-reader-text"><?php echo $solanawp_description; ?></p>
        <?php endif; ?>
    </div>
</div>
