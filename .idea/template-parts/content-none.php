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
