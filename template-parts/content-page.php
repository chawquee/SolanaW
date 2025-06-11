<?php
/**
 * Template part for displaying page content in page.php
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
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'solanawp-page-content' ); ?>>
    <?php
    // Determine if this is the front page using the Address Checker template
    // to avoid showing the WordPress page title on top of the checker's own title.
    $is_checker_on_front = false;
    if ( is_front_page() ) {
        if ( is_page_template( 'templates/template-address-checker.php' ) ) {
            $is_checker_on_front = true;
        } elseif ( get_option( 'show_on_front' ) === 'page' ) {
            $front_page_id = get_option( 'page_on_front' );
            if ( $front_page_id && 'templates/template-address-checker.php' === get_page_template_slug( $front_page_id ) ) {
                $is_checker_on_front = true;
            }
        }
    }

    // Display the page title unless it's the front page with the address checker template.
    if ( ! $is_checker_on_front && get_the_title() ) :
        ?>
        <header class="entry-header page-entry-header">
            <?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
        </header>
    <?php endif; ?>

    <?php
    // Display post thumbnail if available, but not on the checker front page.
    if ( function_exists( 'solanawp_post_thumbnail' ) && ! $is_checker_on_front && has_post_thumbnail() ) {
        solanawp_post_thumbnail('large'); // Use 'large' or another appropriate size for pages
    } elseif ( has_post_thumbnail() && ! $is_checker_on_front ) { // Fallback if template tag isn't loaded/available
        echo '<div class="post-thumbnail page-thumbnail">';
        the_post_thumbnail('large');
        echo '</div>';
    }
    ?>

    <div class="entry-content">
        <?php
        the_content(); // Main page content

        wp_link_pages( // For paginated pages (using Quicktag)
            array(
                'before'   => '<nav class="page-links" aria-label="' . esc_attr__( 'Page', 'solanawp' ) . '"><span class="page-links-title">' . esc_html__( 'Pages:', 'solanawp' ) . '</span>',
                'after'    => '</nav>',
                'pagelink' => '<span class="screen-reader-text">' . esc_html__( 'Page', 'solanawp' ) . ' </span>%',
                'separator' => '<span class="separator" aria-hidden="true">, </span>',
            )
        );
        ?>
    </div>

    <?php if ( get_edit_post_link() ) : // Display Edit link for logged-in users with edit capabilities ?>
        <footer class="entry-footer">
            <?php
            edit_post_link(
                sprintf(
                    wp_kses(
                    /* translators: %s: Name of current post. Only visible to screen readers. */
                        __( 'Edit <span class="screen-reader-text">%s</span>', 'solanawp' ),
                        array(
                            'span' => array(
                                'class' => array(),
                            ),
                        )
                    ),
                    get_the_title()
                ),
                '<span class="edit-link">',
                '</span>'
            );
            ?>
        </footer>
    <?php endif; ?>
</article>
