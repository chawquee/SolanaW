<?php
/**
 * Template part for displaying the header branding (logo, site title/tagline).
 * Called from header.php.
 *
 * @package SolanaWP
 * @since SolanaWP 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="site-branding"> <?php // Standard WordPress class for this section ?>
    <div class="logo-container"> <?php // Class from hannisolsvelte.html ?>
        <?php
        // Uses the solanawp_get_logo_or_fallback_html() function from inc/template-tags.php
        // to display either the custom logo or the 'H' fallback.
        // The fallback H logo design is from hannisolsvelte.html
        // and aligns with Hannisol branding.
        if ( function_exists( 'solanawp_get_logo_or_fallback_html' ) ) {
            echo solanawp_get_logo_or_fallback_html();
        } elseif ( function_exists( 'the_custom_logo' ) && has_custom_logo() ) {
            the_custom_logo();
        } else {
            // Fallback if function doesn't exist, directly outputting the structure
            // (though the function is preferred for consistency).
            ?>
            <div class="logo"> <?php // Class from hannisolsvelte.html ?>
                <div class="logo-h">H</div> <?php // Class from hannisolsvelte.html ?>
            </div>
            <?php
        }
        ?>
    </div>

    <div class="site-title-tagline">
        <?php // The .brand-name displays "HANNISOL" as per hannisolsvelte.html & solanacheckerplan.txt ?>
        <?php if ( get_bloginfo( 'name' ) && ( is_front_page() && is_home() ) ) : // Display as H1 on home ?>
            <h1 class="brand-name"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php echo esc_html__( 'HANNISOL', 'solanawp' ); ?></a></h1>
        <?php else : // Display as P on other pages ?>
            <p class="brand-name"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php echo esc_html__( 'HANNISOL', 'solanawp' ); ?></a></p>
        <?php endif; ?>

        <?php
        // The WordPress site tagline (description) is now removed as per request.
        /*
        $solanawp_description = get_bloginfo( 'description', 'display' );
        if ( $solanawp_description || is_customize_preview() ) :
            ?>
            <p class="site-description screen-reader-text"><?php echo $solanawp_description; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
        <?php endif;
        */
        ?>
    </div>
</div>
