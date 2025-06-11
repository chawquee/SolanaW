<?php
/**
 * Custom template tags for the SolanaWP theme.
 *
 * These tags are used to display common elements like post meta, thumbnails, etc.
 *
 * @package SolanaWP
 * @since SolanaWP 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'solanawp_posted_on' ) ) :
    /**
     * Prints HTML with meta information for the current post-date/time.
     */
    function solanawp_posted_on() {
        $time_string = '<time class="entry-date published updated" datetime="%1$s">%2$s</time>';
        if ( get_the_time( 'U' ) !== get_the_modified_time( 'U' ) ) {
            // If the post was modified, show both published and updated dates.
            $time_string = '<time class="entry-date published" datetime="%1$s">%2$s</time><time class="updated screen-reader-text" datetime="%3$s">%4$s (' . esc_html__('Updated', 'solanawp') . ')</time>';
        }

        $time_string = sprintf(
            $time_string,
            esc_attr( get_the_date( DATE_W3C ) ), // Published date in ISO 8601 format.
            esc_html( get_the_date() ),           // Published date in localized format.
            esc_attr( get_the_modified_date( DATE_W3C ) ), // Modified date in ISO 8601 format.
            esc_html( get_the_modified_date() )    // Modified date in localized format.
        );

        $posted_on = sprintf(
        /* translators: %s: post date. */
            esc_html_x( 'Published on %s', 'post date', 'solanawp' ),
            '<a href="' . esc_url( get_permalink() ) . '" rel="bookmark">' . $time_string . '</a>'
        );

        echo '<span class="posted-on">' . $posted_on . '</span>';
    }
endif;

if ( ! function_exists( 'solanawp_posted_by' ) ) :
    /**
     * Prints HTML with meta information for the current author.
     */
    function solanawp_posted_by() {
        $byline = sprintf(
        /* translators: %s: post author. */
            esc_html_x( 'by %s', 'post author', 'solanawp' ),
            '<span class="author vcard"><a class="url fn n" href="' . esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ) . '">' . esc_html( get_the_author() ) . '</a></span>'
        );

        echo '<span class="byline"> ' . $byline . '</span>';
    }
endif;

if ( ! function_exists( 'solanawp_entry_meta_footer' ) ) :
    /**
     * Prints HTML with meta information for the categories, tags and comments.
     * Replaces a more generic 'entry_footer' to be more specific.
     */
    function solanawp_entry_meta_footer() {
        // Hide category and tag text for pages.
        if ( 'post' === get_post_type() ) {
            /* translators: used between list items, there is a space after the comma */
            $categories_list = get_the_category_list( esc_html__( ', ', 'solanawp' ) );
            if ( $categories_list ) {
                printf(
                    '<span class="cat-links">' . esc_html__( 'Posted in: %1$s', 'solanawp' ) . '</span>',
                    $categories_list // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped (get_the_category_list() escapes)
                );
            }

            /* translators: used between list items, there is a space after the comma */
            $tags_list = get_the_tag_list( '', esc_html_x( ', ', 'list item separator', 'solanawp' ) );
            if ( $tags_list ) {
                printf(
                    '<span class="tags-links">' . esc_html__( 'Tagged: %1$s', 'solanawp' ) . '</span>',
                    $tags_list // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped (get_the_tag_list() escapes)
                );
            }
        }

        // Show comment count unless on a single page with comments open (where they are visible below).
        if ( ! is_single() && ! post_password_required() && ( comments_open() || get_comments_number() ) ) {
            echo '<span class="comments-link">';
            comments_popup_link(
                sprintf(
                    wp_kses(
                    /* translators: %s: post title */
                        __( 'Leave a Comment<span class="screen-reader-text"> on %s</span>', 'solanawp' ),
                        array(
                            'span' => array(
                                'class' => array(),
                            ),
                        )
                    ),
                    get_the_title()
                )
            );
            echo '</span>';
        }

        // Edit post link.
        edit_post_link(
            sprintf(
                wp_kses(
                /* translators: %s: Name of current post. Only visible to screen readers */
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
    }
endif;

if ( ! function_exists( 'solanawp_post_thumbnail' ) ) :
    /**
     * Displays an optional post thumbnail.
     *
     * Wraps the post thumbnail in an anchor element on index views, or a div
     * element on single views.
     */
    function solanawp_post_thumbnail( $size = 'post-thumbnail', $attr = array() ) {
        if ( post_password_required() || is_attachment() || ! has_post_thumbnail() ) {
            return;
        }

        // Default attributes.
        $default_attr = array(
            'alt' => the_title_attribute( array( 'echo' => false ) ),
            // Add more default attributes if needed
        );
        $attr = wp_parse_args( $attr, $default_attr );


        echo '<div class="post-thumbnail">'; // Common wrapper for styling
        if ( is_singular() ) :
            the_post_thumbnail( $size, $attr );
        else :
            echo '<a href="' . esc_url( get_permalink() ) . '" aria-hidden="true" tabindex="-1">';
            the_post_thumbnail( $size, $attr );
            echo '</a>';
        endif; // End is_singular().
        echo '</div>';
    }
endif;

if ( ! function_exists( 'solanawp_get_logo_or_fallback' ) ) :
    /**
     * Displays the custom logo or a fallback structure.
     * This centralizes the logo logic used in header.php or template parts.
     * Based on Hannisol branding and logo structure in hannisolsvelte.html.
     */
    function solanawp_get_logo_or_fallback_html() {
        ob_start();
        if ( function_exists( 'the_custom_logo' ) && has_custom_logo() ) {
            the_custom_logo();
        } else {
            // Fallback to the "H" logo structure from hannisolsvelte.html
            // Logo colors from solanacheckerplan.txt are applied via CSS.
            ?>
            <div class="logo">
                <div class="logo-h">H</div>
            </div>
            <?php
        }
        return ob_get_clean();
    }
endif;

/**
 * Shim for wp_body_open, ensuring backward compatibility.
 */
if ( ! function_exists( 'wp_body_open' ) ) {
    function wp_body_open() {
        do_action( 'wp_body_open' );
    }
}
