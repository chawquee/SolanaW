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
        </header><?php endif; ?>

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
    </div><?php if ( get_edit_post_link() ) : // Display Edit link for logged-in users with edit capabilities ?>
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
            } else { // Fallback if template tag isn't loaded/available for some reason
                echo '<span class="posted-on">' . esc_html( get_the_date() ) . '</span>';
            }
            ?>
            </div><?php endif; ?>
    </header><?php
    // Display post thumbnail if available, using the custom template tag.
    if ( function_exists( 'solanawp_post_thumbnail' ) ) {
        // Using 'thumbnail' size for search results for better performance and layout.
        solanawp_post_thumbnail( 'thumbnail', array( 'class' => 'search-result-thumbnail' ) );
    } elseif ( has_post_thumbnail() ) { // Fallback if template tag isn't available
        echo '<div class="post-thumbnail search-result-thumbnail">';
        echo '<a href="' . esc_url( get_permalink() ) . '" aria-hidden="true" tabindex="-1">';
        the_post_thumbnail( 'thumbnail' ); // WordPress 'thumbnail' size
        echo '</a>';
        echo '</div>';
    }
    ?>

    <div class="entry-summary card-content"> <?php // Using .card-content from hannisolsvelte.html ?>
        <?php the_excerpt(); // Displays the post excerpt. ?>
    </div><footer class="entry-footer" style="padding: 15px 24px 24px 24px; text-align: left; border-top: 1px solid #f1f5f9;"> <?php // Consistent padding and separator for card footer ?>
        <a href="<?php echo esc_url( get_permalink() ); ?>" class="read-more-button"> <?php // General .read-more-button class (styled in main.css) ?>
            <?php esc_html_e( 'Read More', 'solanawp' ); ?>
            <span class="screen-reader-text"> <?php echo esc_html(get_the_title()); // For accessibility ?></span>
            <span aria-hidden="true">&nbsp;&rarr;</span>
        </a>
    </footer>

</article>
