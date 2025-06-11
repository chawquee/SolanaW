<?php
/**
 * Template part for displaying single posts
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

<article id="post-<?php the_ID(); ?>" <?php post_class( 'card single-post-card solanawp-article' ); ?>> <?php // Using .card class from hannisolsvelte.html ?>
    <header class="entry-header card-header"> <?php // Using .card-header from hannisolsvelte.html ?>
        <?php the_title( '<h1 class="entry-title card-title">', '</h1>' ); ?> <?php // Using .card-title from hannisolsvelte.html ?>

        <div class="entry-meta">
            <?php
            if ( function_exists( 'solanawp_posted_on' ) ) {
                solanawp_posted_on(); // Defined in inc/template-tags.php
            }
            if ( function_exists( 'solanawp_posted_by' ) ) {
                solanawp_posted_by(); // Defined in inc/template-tags.php
            }
            ?>
        </div>
    </header>

    <?php
    // Display post thumbnail if available, using the custom template tag.
    if ( function_exists( 'solanawp_post_thumbnail' ) ) {
        solanawp_post_thumbnail('full'); // Display full size featured image for single posts, defined in inc/template-tags.php
    }
    ?>

    <div class="entry-content card-content"> <?php // Using .card-content from hannisolsvelte.html ?>
        <?php
        the_content(
            sprintf( // Adding a "Continue reading" link for content that uses the tag
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

        wp_link_pages( // For paginated posts (using Quicktag)
            array(
                'before'   => '<nav class="page-links" aria-label="' . esc_attr__( 'Page', 'solanawp' ) . '"><span class="page-links-title">' . esc_html__( 'Pages:', 'solanawp' ) . '</span>',
                'after'    => '</nav>',
                'pagelink' => '<span class="screen-reader-text">' . esc_html__( 'Page', 'solanawp' ) . ' </span>%',
                'separator' => '<span class="separator" aria-hidden="true">, </span>',
            )
        );
        ?>
    </div>

    <footer class="entry-footer" style="padding: 20px 24px; border-top: 1px solid #e5e7eb;"> <?php // Style for consistency from main.css card styling ?>
        <?php
        // Display categories, tags, and edit link using the custom template tag.
        if ( function_exists( 'solanawp_entry_meta_footer' ) ) {
            solanawp_entry_meta_footer(); // Defined in inc/template-tags.php
        }
        ?>
    </footer>
</article>
