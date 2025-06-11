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
        </div></header><?php
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
    </div><footer class="entry-footer" style="padding: 20px 24px; border-top: 1px solid #e5e7eb;"> <?php // Style for consistency from main.css card styling ?>
        <?php
        // Display categories, tags, and edit link using the custom template tag.
        if ( function_exists( 'solanawp_entry_meta_footer' ) ) {
            solanawp_entry_meta_footer(); // Defined in inc/template-tags.php
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

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<section class="no-results not-found card"> <?php // Using .card class from hannisolsvelte.html ?>
    <header class="page-header card-header"> <?php // Using .card-header from hannisolsvelte.html ?>
        <h1 class="page-title card-title"><?php esc_html_e( 'Nothing Found', 'solanawp' ); ?></h1> <?php // Using .card-title from hannisolsvelte.html ?>
    </header><div class="page-content card-content"> <?php // Using .card-content from hannisolsvelte.html ?>
        <?php if ( is_home() && current_user_can( 'publish_posts' ) ) : // If on blog page (and not static front page) and user can publish ?>

            <p>
                <?php
                printf(
                    wp_kses(
                    /* translators: 1: link to WP admin new post page. */
                        __( 'Ready to publish your first post? <a href="%1$s">Get started here</a>.', 'solanawp' ),
                        array(
                            'a' => array( // Allow <a> tags with href attribute
                                'href' => array(),
                            ),
                        )
                    ),
                    esc_url( admin_url( 'post-new.php' ) ) // Link to add new post
                );
                ?>
            </p>

        <?php elseif ( is_search() ) : // If on a search results page with no results ?>

            <p><?php esc_html_e( 'Sorry, but nothing matched your search terms. Please try again with some different keywords.', 'solanawp' ); ?></p>
            <?php get_search_form(); // Display search form ?>

        <?php else : // For other archives or contexts with no results ?>

            <p><?php esc_html_e( 'It seems we can&rsquo;t find what you&rsquo;re looking for. Perhaps searching can help.', 'solanawp' ); ?></p>
            <?php get_search_form(); // Display search form ?>

        <?php endif; ?>
    </div></section>
<?php
/**
 * Template part for displaying results in search pages.
 * Called from search.php.
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

<article id="post-<?php the_ID(); ?>" <?php post_class( 'search-result-item card solanawp-article' ); ?>> <?php // Using .card class from hannisolsvelte.html ?>
    <header class="entry-header card-header"> <?php // Using .card-header from hannisolsvelte.html ?>
        <?php the_title( sprintf( '<h2 class="entry-title card-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h2>' ); ?> <?php // Using .card-title from hannisolsvelte.html ?>

        <?php if ( 'post' === get_post_type() ) : ?>
            <div class="entry-meta">
            <?php
            if ( function_exists( 'solanawp_posted_on' ) ) { // Using template tag from inc/template-tags.php
                solanawp_posted_on();
            }
            ?>
            </div><?php endif; ?>
    </header><?php
    // Display post thumbnail if available, using the custom template tag.
    if ( function_exists( 'solanawp_post_thumbnail' ) ) {
        // Using 'thumbnail' size for search results for better performance and layout.
        solanawp_post_thumbnail( 'thumbnail', array( 'class' => 'search-result-thumbnail' ) );
    }
    ?>

    <div class="entry-summary card-content"> <?php // Using .card-content from hannisolsvelte.html ?>
        <?php the_excerpt(); // Displays the post excerpt. ?>
    </div><footer class="entry-footer" style="padding: 15px 24px 24px 24px; text-align: left; border-top: 1px solid #f1f5f9;"> <?php // Style for consistency ?>
        <a href="<?php echo esc_url( get_permalink() ); ?>" class="read-more-button"> <?php // General .read-more-button class (styled in main.css) ?>
            <?php esc_html_e( 'Read More', 'solanawp' ); ?>
            <span class="screen-reader-text"> <?php echo esc_html(get_the_title()); ?></span>
            <span aria-hidden="true">&nbsp;&rarr;</span>
        </a>
    </footer>

</article>
