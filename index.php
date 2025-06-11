<?php
/**
 * The main template file.
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists and 'Your latest posts' is selected in Reading Settings.
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

get_header(); // Includes header.php
?>

<?php // .main-container wraps .content-area and .sidebar in hannisolsvelte.html ?>
    <div class="main-container">
        <div id="primary" class="content-area index-content-area"> <?php // .content-area class from hannisolsvelte.html ?>
            <main id="main" class="site-main" role="main">

                <?php
                // On the blog posts index, display a title if it's not the site's front page.
                if ( is_home() && ! is_front_page() ) :
                    ?>
                    <header class="page-header card"> <?php // Using .card styling for consistency ?>
                        <div class="card-header">
                            <h1 class="page-title card-title"><?php single_post_title(); // Displays the title for the posts page (e.g., "Blog") ?></h1>
                        </div>
                    </header>
                <?php
                endif;

                if ( have_posts() ) : // Check if there are posts to display.

                    // Start the WordPress Loop.
                    while ( have_posts() ) :
                        the_post();

                        /*
                         * Include the Post-Format-specific template for the content.
                         * If you want to override this in a child theme, then include a file
                         * called content-___.php (where ___ is the Post Format) and that will be used instead.
                         * We use 'content.php' by default.
                         */
                        get_template_part( 'template-parts/content', get_post_format() );

                    endwhile; // End of the loop.

                    // Display pagination if more posts are available.
                    the_posts_pagination(
                        array(
                            'prev_text'          => esc_html__( '&larr; Newer Posts', 'solanawp' ), // Or 'Previous Page'
                            'next_text'          => esc_html__( 'Older Posts &rarr;', 'solanawp' ), // Or 'Next Page'
                            'before_page_number' => '<span class="meta-nav screen-reader-text">' . esc_html__( 'Page', 'solanawp' ) . ' </span>',
                        )
                    );

                else : // If no posts are found.
                    get_template_part( 'template-parts/content', 'none' ); // Include template-parts/content-none.php

                endif;
                ?>
            </main>
        </div>

        <?php get_sidebar(); // Includes sidebar.php ?>
    </div>

<?php
get_footer(); // Includes footer.php
