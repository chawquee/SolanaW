<?php
/**
 * Template part for displaying posts
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

<article id="post-<?php the_ID(); ?>" <?php post_class( 'card solanawp-article' ); ?>> <?php // Using .card class from hannisolsvelte.html ?>
    <header class="entry-header card-header"> <?php // Using .card-header from hannisolsvelte.html ?>
        <?php
        if ( is_singular() ) : // Should not typically happen for 'content.php', usually 'content-single.php' is used.
            the_title( '<h1 class="entry-title card-title">', '</h1>' ); // Using .card-title from hannisolsvelte.html
        else :
            the_title( sprintf( '<h2 class="entry-title card-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h2>' ); // Using .card-title from hannisolsvelte.html
        endif;
        ?>

        <?php if ( 'post' === get_post_type() ) : ?>
            <div class="entry-meta">
                <?php
                if ( function_exists( 'solanawp_posted_on' ) ) {
                    solanawp_posted_on();
                }
                if ( function_exists( 'solanawp_posted_by' ) ) {
                    solanawp_posted_by();
                }
                ?>
            </div>
        <?php endif; ?>
    </header>

    <?php
    if ( function_exists( 'solanawp_post_thumbnail' ) ) {
        solanawp_post_thumbnail('medium_large'); // Display post thumbnail using custom size or 'medium_large'
    }
    ?>

    <div class="entry-content card-content"> <?php // Using .card-content from hannisolsvelte.html ?>
        <?php
        // On archive pages, display the excerpt.
        if ( is_archive() || is_search() || is_home() && !is_front_page() ) {
            the_excerpt();
        } else {
            the_content(
                sprintf(
                    wp_kses(
                    /* translators: %s: Name of current post. Only visible to screen readers. */
                        __( 'Continue reading<span class="screen-reader-text"> "%s"</span>', 'solanawp' ),
                        array(
                            'span' => array(
                                'class' => array(),
                            ),
                        )
                    ),
                    get_the_title()
                )
            );

            wp_link_pages(
                array(
                    'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'solanawp' ),
                    'after'  => '</div>',
                )
            );
        }
        ?>
    </div>

    <footer class="entry-footer" style="padding: 0 24px 24px 24px;"> <?php // Consistent padding with card-content ?>
        <?php
        if ( function_exists( 'solanawp_entry_meta_footer' ) ) {
            solanawp_entry_meta_footer();
        }
        ?>
    </footer>
</article>
