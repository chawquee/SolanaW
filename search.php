<?php
/**
 * The template for displaying search results pages.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#search-result
 *
 * @package SolanaWP
 * @since SolanaWP 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header(); // Includes header.php
?>
<?php // Using .main-container structure from hannisolsvelte.html for layout consistency. ?>
<div class="main-container">
    <div id="primary" class="content-area search-content-area"> <?php // Added .search-content-area for specific styling if needed. ?>
    <main id="main" class="site-main" role="main">

        <?php if ( have_posts() ) : ?>

            <header class="page-header card"> <?php // Using .card styling for the header ?>
            <div class="card-header"> <?php // Using .card-header styling ?>
                <h1 class="page-title card-title"> <?php // Using .card-title styling ?>
                    <?php
                    /* translators: %s: search query. */
                    printf( esc_html__( 'Search Results for: %s', 'solanawp' ), '<span>' . esc_html( get_search_query() ) . '</span>' );
                    ?>
                </h1>
            </div>
            </header><?php
            // Start the WordPress Loop to display search results.
            while ( have_posts() ) :
                the_post();

                /**
                 * Include the content-search.php template part to display each search result.
                 * This allows for custom formatting of search results.
                 */
                get_template_part( 'template-parts/content', 'search' );

            endwhile; // End of the loop.

            // Display pagination if more search results are available.
            the_posts_pagination(
                array(
                    'prev_text'          => esc_html__( '&larr; Previous Page', 'solanawp' ),
                    'next_text'          => esc_html__( 'Next Page &rarr;', 'solanawp' ),
                    'before_page_number' => '<span class="meta-nav screen-reader-text">' . esc_html__( 'Page', 'solanawp' ) . ' </span>',
                )
            );

        else : // If no posts are found matching the search query.
            get_template_part( 'template-parts/content', 'none' ); // Include template-parts/content-none.php
            ?>
            <div class="no-results-extra-search-form card-content" style="text-align:center; padding-top: 20px;"> <?php // Added for another search try ?>
                <p><?php esc_html_e( 'Want to try searching again?', 'solanawp' ); ?></p>
                <?php get_search_form(); ?>
            </div>
        <?php
        endif;
        ?>
    </main></div><?php get_sidebar(); // Includes sidebar.php ?>
</div><?php
get_footer(); // Includes footer.php
