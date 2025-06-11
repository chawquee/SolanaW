<?php
/**
 * Breadcrumbs functionality for SolanaWP.
 *
 * @package SolanaWP
 * @since SolanaWP 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'solanawp_breadcrumbs' ) ) :
    /**
     * Displays breadcrumb navigation.
     *
     * @param array $args Optional. Arguments to customize breadcrumb output.
     */
    function solanawp_breadcrumbs( $args = array() ) {
        if ( is_front_page() ) { // Don't display on the front page.
            return;
        }

        $defaults = array(
            'show_home'   => esc_html__( 'Home', 'solanawp' ),
            'home_url'    => home_url( '/' ),
            'separator'   => '<span class="breadcrumb-separator"> &raquo; </span>', // Or use an icon like '>'
            'show_current'=> true,
            'before'      => '<nav class="solanawp-breadcrumbs" aria-label="' . esc_attr__( 'Breadcrumb', 'solanawp' ) . '"><span class="breadcrumbs-prefix">' . esc_html__( 'You are here: ', 'solanawp' ) . '</span>',
            'after'       => '</nav>',
            'post_ancestors_args' => array(),
        );

        $args = wp_parse_args( $args, $defaults );

        $crumbs = array();

        // Home link.
        if ( $args['show_home'] ) {
            $crumbs[] = '<a class="breadcrumb-home" href="' . esc_url( $args['home_url'] ) . '" rel="home">' . esc_html( $args['show_home'] ) . '</a>';
        }

        if ( is_home() && ! is_front_page() ) { // Blog page.
            $blog_page_id = get_option( 'page_for_posts' );
            if ( $blog_page_id ) {
                $crumbs[] = '<a href="' . esc_url( get_permalink( $blog_page_id ) ) . '">' . esc_html( get_the_title( $blog_page_id ) ) . '</a>';
            }
        } elseif ( is_category() ) {
            $category = get_queried_object();
            if ( $category->parent ) {
                $parent_categories = get_category_parents( $category->parent, true, $args['separator'] );
                // Remove trailing separator from get_category_parents.
                if (is_string($parent_categories)) {
                    $parent_categories = rtrim( $parent_categories, $args['separator'] );
                    $crumbs[] = $parent_categories;
                }
            }
            $crumbs[] = single_cat_title( '', false );

        } elseif ( is_tag() ) {
            $crumbs[] = single_tag_title( esc_html__( 'Posts tagged &quot;', 'solanawp' ), false ) . '&quot;';

        } elseif ( is_author() ) {
            $author = get_queried_object();
            $crumbs[] = esc_html__( 'Author: ', 'solanawp' ) . esc_html( $author->display_name );

        } elseif ( is_day() ) {
            $crumbs[] = '<a href="' . esc_url( get_year_link( get_the_time( 'Y' ) ) ) . '">' . esc_html( get_the_time( 'Y' ) ) . '</a>';
            $crumbs[] = '<a href="' . esc_url( get_month_link( get_the_time( 'Y' ), get_the_time( 'm' ) ) ) . '">' . esc_html( get_the_time( 'F' ) ) . '</a>';
            $crumbs[] = esc_html( get_the_time( 'd' ) );

        } elseif ( is_month() ) {
            $crumbs[] = '<a href="' . esc_url( get_year_link( get_the_time( 'Y' ) ) ) . '">' . esc_html( get_the_time( 'Y' ) ) . '</a>';
            $crumbs[] = esc_html( get_the_time( 'F' ) );

        } elseif ( is_year() ) {
            $crumbs[] = esc_html( get_the_time( 'Y' ) );

        } elseif ( is_search() ) {
            $crumbs[] = esc_html__( 'Search results for &quot;', 'solanawp' ) . esc_html( get_search_query() ) . '&quot;';

        } elseif ( is_404() ) {
            $crumbs[] = esc_html__( 'Error 404', 'solanawp' );

        } elseif ( is_page() || is_single() ) {
            $post_type = get_post_type_object( get_post_type() );

            if ( is_page() && ! is_attachment() ) {
                $page_id = get_queried_object_id();
                $ancestors = get_post_ancestors( $page_id );
                $ancestors = array_reverse( $ancestors ); // Reverse to display top-level parent first.

                if ( ! empty( $ancestors ) ) {
                    foreach ( $ancestors as $ancestor_id ) {
                        $crumbs[] = '<a href="' . esc_url( get_permalink( $ancestor_id ) ) . '">' . esc_html( get_the_title( $ancestor_id ) ) . '</a>';
                    }
                }
            } elseif ( is_single() && 'post' !== get_post_type() && $post_type && $post_type->has_archive ) {
                // For Custom Post Types with an archive page.
                $archive_link = get_post_type_archive_link( get_post_type() );
                if ($archive_link) {
                    $crumbs[] = '<a href="' . esc_url( $archive_link ) . '">' . esc_html( $post_type->labels->name ) . '</a>';
                }
            } elseif ( is_single() && 'post' === get_post_type() ) {
                // For regular posts, show category.
                $categories = get_the_category();
                if ( ! empty( $categories ) ) {
                    // Get the first category. For more complex logic (e.g., primary category), a plugin or more code is needed.
                    $category = $categories[0];
                    if ( $category->parent ) {
                        $parent_categories = get_category_parents( $category->parent, true, $args['separator'] );
                        if (is_string($parent_categories)) {
                            $parent_categories = rtrim( $parent_categories, $args['separator'] );
                            $crumbs[] = $parent_categories;
                        }
                    }
                    $crumbs[] = '<a href="' . esc_url( get_category_link( $category->term_id ) ) . '">' . esc_html( $category->name ) . '</a>';
                }
            }
            // Current page/post title for singular views.
            if ( $args['show_current'] ) {
                $crumbs[] = get_the_title();
            }
        }

        // Output the breadcrumbs.
        if ( ! empty( $crumbs ) ) {
            echo wp_kses_post( $args['before'] ); // Kses for allowed HTML in 'before'.
            echo implode( wp_kses_post( $args['separator'] ), $crumbs ); // Kses for allowed HTML in separator.
            echo wp_kses_post( $args['after'] );  // Kses for allowed HTML in 'after'.
        }
    }
endif;

// How to use in a template file (e.g., header.php or just before the content loop):
// if ( function_exists( 'solanawp_breadcrumbs' ) ) {
//     solanawp_breadcrumbs();
// }
//
// You'll also need to add CSS for .solanawp-breadcrumbs, .breadcrumb-separator etc. in main.css
// Example CSS:
// .solanawp-breadcrumbs { font-size: 0.9em; color: #6b7280; margin-bottom: 1.5em; padding: 10px 0; border-bottom: 1px solid #e5e7eb; }
// .solanawp-breadcrumbs a { color: #374151; }
// .solanawp-breadcrumbs a:hover { color: #7c3aed; }
// .solanawp-breadcrumbs .breadcrumb-separator { margin: 0 0.5em; }
// .solanawp-breadcrumbs .breadcrumbs-prefix { font-weight: bold; }
