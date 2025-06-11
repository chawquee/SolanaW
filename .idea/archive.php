<?php
/**
 * The template for displaying archive pages.
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

<?php // Using .main-container structure from hannisolsvelte.html for layout consistency. ?>
<div class="main-container">
    <div id="primary" class="content-area archive-content-area"> <?php // Added .archive-content-area for specific styling if needed. ?>
    <main id="main" class="site-main" role="main">

        <?php if ( have_posts() ) : ?>

            <header class="page-header card"> <?php // Using .card styling for the archive header from hannisolsvelte.html ?>
            <div class="card-header"> <?php // Using .card-header styling ?>
                <?php
                // Display the archive title (e.g., "Category: News", "Author: John Doe").
                the_archive_title( '<h1 class="page-title card-title">', '</h1>' ); // Using .card-title
                ?>
            </div>
            <?php
            // Display the archive description, if it exists.
            $archive_description = get_the_archive_description();
            if ( $archive_description ) :
                ?>
                <div class="archive-description card-content"> <?php // Using .card-content styling ?>
                    <?php echo wp_kses_post( $archive_description ); ?>
                </div>
            <?php endif; ?>
            </header><?php
            // Start the WordPress Loop to display posts.
            while ( have_posts() ) :
                the_post();

                /*
                 * Include the Post-Format-specific template for the content.
                 * If you want to override this in a child theme, then include a file
                 * called content-___.php (where ___ is the Post Format) and that will be used instead.
                 * For archives, we typically use 'content.php' or a more specific 'content-archive.php'.
                 * We will use 'content.php' which has been styled with .card class.
                 */
                get_template_part( 'template-parts/content', get_post_type() ); // get_post_type() allows for CPT specific content parts if created

            endwhile;

            // Display pagination if more posts are available.
            the_posts_pagination(
                array(
                    'prev_text'          => esc_html__( '&larr; Previous Page', 'solanawp' ),
                    'next_text'          => esc_html__( 'Next Page &rarr;', 'solanawp' ),
                    'before_page_number' => '<span class="meta-nav screen-reader-text">' . esc_html__( 'Page', 'solanawp' ) . ' </span>',
                )
            );

        else :
            // If no content is found for the archive, display the "No posts found" template part.
            get_template_part( 'template-parts/content', 'none' );

        endif;
        ?>
    </main></div><?php get_sidebar(); // Includes sidebar.php, maintaining consistent layout. ?>
</div><?php
get_footer(); // Includes footer.php
