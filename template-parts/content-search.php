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
                    echo '<span class="posted-on">' . esc_html__( 'Posted on ', 'solanawp' ) . esc_html( get_the_date() ) . '</span>';
                }
                ?>
            </div>
        <?php endif; ?>
    </header>

    <?php
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
    </div>

    <footer class="entry-footer" style="padding: 15px 24px 24px 24px; text-align: left; border-top: 1px solid #f1f5f9;"> <?php // Consistent padding and separator for card footer ?>
        <a href="<?php echo esc_url( get_permalink() ); ?>" class="read-more-button"> <?php // General .read-more-button class (styled in main.css) ?>
            <?php esc_html_e( 'Read More', 'solanawp' ); ?>
            <span class="screen-reader-text"> <?php echo esc_html( get_the_title() ); // For accessibility ?></span>
            <span aria-hidden="true">&nbsp;&rarr;</span>
        </a>
    </footer>
</article>
