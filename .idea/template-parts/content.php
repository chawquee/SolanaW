<?php
/**
 * Template part for displaying posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package SolanaWP
 * @since SolanaWP 1.0.0
 */

?>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'card solanawp-article' ); // Using .card class from hannisolsvelte.html ?>>
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
            </div><?php endif; ?>
    </header><?php
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
    </div><footer class="entry-footer" style="padding: 0 24px 24px 24px;"> <?php // Consistent padding with card-content ?>
        <?php
        if ( function_exists( 'solanawp_entry_meta_footer' ) ) {
            solanawp_entry_meta_footer();
        }
        ?>
    </footer></article>

<?php
/**
 * Template part for displaying page content in page.php
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package SolanaWP
 * @since SolanaWP 1.0.0
 */

?>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'solanawp-page-content' ); ?>>
    <?php
    // Do not display the page title if it's the front page using the Address Checker template,
    // as the header in template-address-checker.php or header.php already handles the main title.
    $is_checker_front_page = ( is_front_page() && ( is_page_template( 'templates/template-address-checker.php' ) || ( get_option( 'show_on_front' ) == 'page' && get_option( 'page_on_front' ) && get_page_template_slug( get_option( 'page_on_front' ) ) === 'templates/template-address-checker.php' ) ) );

    if ( ! $is_checker_front_page && get_the_title() ) : // Display title for other pages
        ?>
        <header class="entry-header page-entry-header">
        <?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
        </header><?php endif; ?>

    <?php
    if ( function_exists( 'solanawp_post_thumbnail' ) && ! $is_checker_front_page ) { // Don't show post thumbnail on checker front page by default
        solanawp_post_thumbnail('large'); // Or your preferred size for pages
    }
    ?>

    <div class="entry-content">
        <?php
        the_content();

        wp_link_pages(
            array(
                'before'   => '<nav class="page-links" aria-label="' . esc_attr__( 'Page', 'solanawp' ) . '"><span class="page-links-title">' . esc_html__( 'Pages:', 'solanawp' ) . '</span>',
                'after'    => '</nav>',
                'pagelink' => '<span class="screen-reader-text">' . esc_html__( 'Page', 'solanawp' ) . ' </span>%',
                'separator' => '<span class="separator" aria-hidden="true">, </span>',
            )
        );
        ?>
    </div><?php if ( get_edit_post_link() ) : ?>
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
        </footer><?php endif; ?>
</article>
<?php
/**
 * Template part for displaying single posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package SolanaWP
 * @since SolanaWP 1.0.0
 */

?>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'card single-post-card' ); ?>> <?php // Added .card class from hannisolsvelte.html ?>
    <header class="entry-header card-header"> <?php // Added .card-header from hannisolsvelte.html ?>
        <?php the_title( '<h1 class="entry-title card-title">', '</h1>' ); ?> <?php // Added .card-title from hannisolsvelte.html ?>

        <div class="entry-meta">
            <?php
            if ( function_exists( 'solanawp_posted_on' ) ) {
                solanawp_posted_on();
            }
            if ( function_exists( 'solanawp_posted_by' ) ) {
                solanawp_posted_by();
            }
            ?>
        </div></header><?php
    if ( function_exists( 'solanawp_post_thumbnail' ) ) {
        solanawp_post_thumbnail('full'); // Display full size featured image for single posts
    }
    ?>

    <div class="entry-content card-content"> <?php // Added .card-content from hannisolsvelte.html ?>
        <?php
        the_content();

        wp_link_pages(
            array(
                'before'   => '<nav class="page-links" aria-label="' . esc_attr__( 'Page', 'solanawp' ) . '"><span class="page-links-title">' . esc_html__( 'Pages:', 'solanawp' ) . '</span>',
                'after'    => '</nav>',
                'pagelink' => '<span class="screen-reader-text">' . esc_html__( 'Page', 'solanawp' ) . ' </span>%',
                'separator' => '<span class="separator" aria-hidden="true">, </span>',
            )
        );
        ?>
    </div><footer class="entry-footer" style="padding: 24px; border-top: 1px solid #e5e7eb;"> <?php // Consistent padding and separator like card-header ?>
        <?php
        if ( function_exists( 'solanawp_entry_meta_footer' ) ) {
            solanawp_entry_meta_footer();
        }
        ?>
    </footer></article>
<?php
/**
 * Template part for displaying a message that posts cannot be found
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package SolanaWP
 * @since SolanaWP 1.0.0
 */

?>

<section class="no-results not-found card"> <?php // Added .card class from hannisolsvelte.html for styling consistency ?>
    <header class="page-header card-header"> <?php // Added .card-header from hannisolsvelte.html ?>
        <h1 class="page-title card-title"><?php esc_html_e( 'Nothing Found', 'solanawp' ); ?></h1> <?php // Added .card-title from hannisolsvelte.html ?>
    </header><div class="page-content card-content"> <?php // Added .card-content from hannisolsvelte.html ?>
        <?php if ( is_home() && current_user_can( 'publish_posts' ) ) : ?>

            <p>
                <?php
                printf(
                    wp_kses(
                    /* translators: 1: link to WP admin new post page. */
                        __( 'Ready to publish your first post? <a href="%1$s">Get started here</a>.', 'solanawp' ),
                        array(
                            'a' => array(
                                'href' => array(),
                            ),
                        )
                    ),
                    esc_url( admin_url( 'post-new.php' ) )
                );
                ?>
            </p>

        <?php elseif ( is_search() ) : ?>

            <p><?php esc_html_e( 'Sorry, but nothing matched your search terms. Please try again with some different keywords.', 'solanawp' ); ?></p>
            <?php get_search_form(); ?>

        <?php else : ?>

            <p><?php esc_html_e( 'It seems we can&rsquo;t find what you&rsquo;re looking for. Perhaps searching can help.', 'solanawp' ); ?></p>
            <?php get_search_form(); ?>

        <?php endif; ?>
    </div></section>
