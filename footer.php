<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 * Also includes the essential wp_footer() hook.
 * The overall page structure is based on hannisolsvelte.html.
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
</div><footer id="colophon" class="site-footer" role="contentinfo">
    <?php
    // Check if the 'footer-widgets' sidebar is active and display it.
    // 'footer-widgets' area is registered in inc/widget-areas.php.
    // The template part template-parts/layout/footer-widgets.php handles its display.
    if ( is_active_sidebar( 'footer-widgets' ) ) {
        get_template_part( 'template-parts/layout/footer-widgets' );
    }
    ?>

    <div class="site-info"> <?php // For copyright text and theme credits. Styled in main.css. ?>
        <?php
        // Get custom copyright text from Customizer or use a default.
        // The setting 'solanawp_footer_copyright_text' is defined in inc/customizer.php.
        // Author credit is for WORLDGPL, website www.worldgpl.com as per your previous instructions.
        $default_copyright = sprintf(
            esc_html__( '&copy; %1$s %2$s. All rights reserved. Theme by %3$s.', 'solanawp' ),
            date_i18n( 'Y' ), // Current year, localized.
            esc_html( get_bloginfo( 'name' ) ), // Site name.
            '<a href="https://www.worldgpl.com/" target="_blank" rel="noopener noreferrer author">WORLDGPL</a>'
        );
        $copyright_text = get_theme_mod( 'solanawp_footer_copyright_text', $default_copyright );

        // Output copyright text, allowing basic HTML (like links) defined in the Customizer setting.
        echo wp_kses_post( $copyright_text );
        ?>
        <span class="sep"> | </span>
        <?php
        printf(
        /* translators: %s: WordPress link. */
            esc_html__( 'Powered by %s.', 'solanawp' ),
            '<a href="' . esc_url( __( 'https://wordpress.org/', 'solanawp' ) ) . '" target="_blank" rel="noopener noreferrer">WordPress</a>'
        );
        ?>
    </div></footer></div><?php wp_footer(); // Essential WordPress hook. Do not remove. Plugins and themes use this to add scripts and markup. ?>
</body>
</html>
