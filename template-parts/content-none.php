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
    </header>
    <div class="page-content card-content"> <?php // Using .card-content from hannisolsvelte.html ?>
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
    </div>
</section>
