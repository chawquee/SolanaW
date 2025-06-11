<?php
/**
 * The template for displaying comments.
 *
 * This is the template that displays the area of the page that contains both the current comments
 * and the comment form.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package SolanaWP
 * @since SolanaWP 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/*
 * If the current post is protected by a password and
 * the visitor has not yet entered the password,
 * return early without loading the comments.
 */
if ( post_password_required() ) {
    return;
}
?>

<div id="comments" class="comments-area">

    <?php if ( have_comments() ) : ?>
        <h2 class="comments-title">
        <?php
        $solanawp_comment_count = get_comments_number();
        if ( '1' === $solanawp_comment_count ) {
            printf(
            /* translators: 1: Post title. */
                esc_html__( 'One thought on &ldquo;%1$s&rdquo;', 'solanawp' ),
                '<span>' . wp_kses_post( get_the_title() ) . '</span>'
            );
        } else {
            printf(
            /* translators: 1: Comment count number, 2: Post title. */
                esc_html( _nx( '%1$s thought on &ldquo;%2$s&rdquo;', '%1$s thoughts on &ldquo;%2$s&rdquo;', $solanawp_comment_count, 'comments title', 'solanawp' ) ),
                number_format_i18n( $solanawp_comment_count ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                '<span>' . wp_kses_post( get_the_title() ) . '</span>'
            );
        }
        ?>
        </h2><?php the_comments_navigation(); ?>

        <ol class="comment-list">
        <?php
        wp_list_comments(
            array(
                'style'       => 'ol', // Use ordered list.
                'short_ping'  => true, // Use short pingbacks.
                'avatar_size' => 60,   // Avatar size in pixels.
                'reply_text'  => esc_html__( 'Reply', 'solanawp' ) . ' &rarr;',
                // You can add a callback for custom comment markup if needed.
                // 'callback' => 'solanawp_comment_callback',
            )
        );
        ?>
        </ol><?php
        the_comments_navigation();

        // If comments are closed and there are comments, let's leave a little note.
        if ( ! comments_open() && get_comments_number() && post_type_supports( get_post_type(), 'comments' ) ) :
            ?>
            <p class="no-comments"><?php esc_html_e( 'Comments are closed.', 'solanawp' ); ?></p>
        <?php
        endif;

    endif; // Check for have_comments().

    // Display comment form.
    comment_form(
        array(
            'title_reply_before' => '<h2 id="reply-title" class="comment-reply-title">',
            'title_reply_after'  => '</h2>',
        )
    );
    ?>

</div>
<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 * Also includes the essential wp_footer() hook.
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
    // This template part (footer-widgets.php) was defined earlier.
    if ( is_active_sidebar( 'footer-widgets' ) ) { // Registered in inc/widget-areas.php
        get_template_part( 'template-parts/layout/footer-widgets' );
    }
    ?>

    <div class="site-info"> <?php // For copyright text and theme credits. ?>
        <?php
        // Get custom copyright text from Customizer or use a default.
        // The setting 'solanawp_footer_copyright_text' is defined in inc/customizer.php.
        $default_copyright = sprintf(
            esc_html__( '&copy; %1$s %2$s. All rights reserved. Theme by %3$s.', 'solanawp' ),
            date_i18n( 'Y' ), // Current year, localized.
            esc_html( get_bloginfo( 'name' ) ), // Site name.
            '<a href="https://www.worldgpl.com/" target="_blank" rel="noopener noreferrer author">WORLDGPL</a>' // Author credit.
        );
        $copyright_text = get_theme_mod( 'solanawp_footer_copyright_text', $default_copyright );
        echo wp_kses_post( $copyright_text ); // Output copyright text, allowing basic HTML.
        ?>
        <span class="sep"> | </span>
        <?php
        printf(
        /* translators: %s: WordPress link. */
            esc_html__( 'Powered by %s.', 'solanawp' ),
            '<a href="' . esc_url( __( 'https://wordpress.org/', 'solanawp' ) ) . '" target="_blank" rel="noopener noreferrer">WordPress</a>'
        );
        ?>
    </div></footer></div><?php wp_footer(); // Essential WordPress hook. Do not remove. ?>
</body>
</html>
