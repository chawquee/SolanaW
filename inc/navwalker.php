<?php
/**
 * Custom Nav Walker for SolanaWP.
 *
 * Extends the Walker_Nav_Menu class to provide more control over the
 * output of wp_nav_menu(), such as adding custom classes for styling.
 *
 * @package SolanaWP
 * @since SolanaWP 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'SolanaWP_Nav_Walker' ) ) {
    /**
     * Class SolanaWP_Nav_Walker.
     *
     * Custom WordPress nav walker to add BEM-style classes and depth classes.
     */
    class SolanaWP_Nav_Walker extends Walker_Nav_Menu {

        /**
         * Starts the list before the elements are added.
         * Adds classes to the <ul> element.
         *
         * @see Walker::start_lvl()
         *
         * @param string   $output Used to append additional content (passed by reference).
         * @param int      $depth  Depth of menu item. Used for padding.
         * @param stdClass $args   An object of wp_nav_menu() arguments.
         */
        public function start_lvl( &$output, $depth = 0, $args = null ) {
            if ( isset( $args->item_spacing ) && 'discard' === $args->item_spacing ) {
                $t = '';
                $n = '';
            } else {
                $t = "\t";
                $n = "\n";
            }
            $indent = str_repeat( $t, $depth );

            // Default class for sub-menus.
            $classes = array( 'sub-menu' );

            // Add a depth-specific class, e.g., 'sub-menu-depth-1'.
            $classes[] = 'sub-menu-depth-' . ( $depth + 1 );

            // Add a custom class from wp_nav_menu args if provided.
            if ( isset( $args->sub_menu_class ) && ! empty( $args->sub_menu_class ) ) {
                $classes[] = $args->sub_menu_class;
            }

            $class_names = join( ' ', apply_filters( 'nav_menu_submenu_css_class', $classes, $args, $depth ) );
            $class_names = $class_names ? ' class="' . esc_attr( $class_names ) . '"' : '';

            $output .= "{$n}{$indent}<ul{$class_names}>{$n}";
        }

        /**
         * Starts the element output for a single menu item.
         * Adds classes to the <li> and <a> elements.
         *
         * @see Walker::start_el()
         *
         * @param string   $output            Used to append additional content (passed by reference).
         * @param WP_Post  $data_object       Menu item data object. (Renamed to $menu_item for clarity in WP 5.3+)
         * @param int      $depth             Depth of menu item. Used for padding.
         * @param stdClass $args              An object of wp_nav_menu() arguments.
         * @param int      $id                Current item ID. Default 0.
         */
        public function start_el( &$output, $menu_item, $depth = 0, $args = null, $id = 0 ) {
            if ( isset( $args->item_spacing ) && 'discard' === $args->item_spacing ) {
                $t = '';
                $n = '';
            } else {
                $t = "\t";
                $n = "\n";
            }
            $indent = ( $depth ) ? str_repeat( $t, $depth ) : '';

            // --- List Item (<li>) Classes ---
            $li_classes   = empty( $menu_item->classes ) ? array() : (array) $menu_item->classes;

            // Add our custom depth class, e.g., 'menu-item-depth-0', 'menu-item-depth-1'.
            $li_classes[] = 'menu-item-depth-' . $depth;

            // Add 'menu-item-has-children' class if the item has children.
            if ( $args->walker->has_children ) {
                $li_classes[] = 'menu-item-has-children';
            }

            // Add a custom class to all <li> elements if specified in $args.
            if ( isset( $args->li_class ) && ! empty( $args->li_class ) ) {
                $li_classes[] = $args->li_class;
            }
            // Add a custom class for menu item links if specified in $args
            // This is usually applied to the <a> tag, but could influence <li> as well.
            if ( isset( $args->link_class ) && ! empty( $args->link_class ) ) {
                // $li_classes[] = 'has-' . $args->link_class; // Example if <li> needs to know about link class
            }


            // Join all <li> classes.
            $li_class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $li_classes ), $menu_item, $args, $depth ) );
            $li_class_names = $li_class_names ? ' class="' . esc_attr( $li_class_names ) . '"' : '';

            // Item ID.
            $item_id = apply_filters( 'nav_menu_item_id', 'menu-item-' . $menu_item->ID, $menu_item, $args, $depth );
            $item_id = $item_id ? ' id="' . esc_attr( $item_id ) . '"' : '';

            $output .= $indent . '<li' . $item_id . $li_class_names . '>';

            // --- Link (<a>) Attributes ---
            $atts           = array();
            $atts['title']  = ! empty( $menu_item->attr_title ) ? $menu_item->attr_title : '';
            $atts['target'] = ! empty( $menu_item->target ) ? $menu_item->target : '';
            if ( '_blank' === $menu_item->target && empty( $menu_item->xfn ) ) {
                $atts['rel'] = 'noopener noreferrer'; // Add rel for blank targets for security.
            } else {
                $atts['rel'] = ! empty( $menu_item->xfn ) ? $menu_item->xfn : '';
            }
            $atts['href']         = ! empty( $menu_item->url ) ? $menu_item->url : '';
            $atts['aria-current'] = $menu_item->current ? 'page' : '';


            // --- Link (<a>) Classes ---
            $link_classes = array();
            if ( isset( $args->link_class ) && ! empty( $args->link_class ) ) {
                $link_classes[] = $args->link_class;
            }
            // Add a depth specific class to the link if desired.
            // $link_classes[] = 'menu-link-depth-' . $depth;

            if ( ! empty( $link_classes ) ) {
                $atts['class'] = join(' ', array_filter( $link_classes ) );
            }


            // Apply filters to link attributes.
            $atts = apply_filters( 'nav_menu_link_attributes', $atts, $menu_item, $args, $depth );

            $attributes = '';
            foreach ( $atts as $attr => $value ) {
                if ( ! empty( $value ) ) {
                    $value       = ( 'href' === $attr ) ? esc_url( $value ) : esc_attr( $value );
                    $attributes .= ' ' . $attr . '="' . $value . '"';
                }
            }

            // --- Item Output ---
            $item_output = isset( $args->before ) ? $args->before : '';
            $item_output .= '<a' . $attributes . '>';
            $item_output .= isset( $args->link_before ) ? $args->link_before : '';
            $item_output .= apply_filters( 'the_title', $menu_item->title, $menu_item->ID );
            $item_output .= isset( $args->link_after ) ? $args->link_after : '';
            $item_output .= '</a>';
            $item_output .= isset( $args->after ) ? $args->after : '';

            $output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $menu_item, $depth, $args );
        }

        /**
         * Ends the element output, if needed.
         *
         * @see Walker::end_el()
         *
         * @param string   $output Used to append additional content (passed by reference).
         * @param WP_Post  $data_object Menu item data object. (Renamed to $menu_item for clarity in WP 5.3+)
         * @param int      $depth  Depth of menu item. Used for padding.
         * @param stdClass $args   An object of wp_nav_menu() arguments.
         */
        public function end_el( &$output, $menu_item, $depth = 0, $args = null ) {
            if ( isset( $args->item_spacing ) && 'discard' === $args->item_spacing ) {
                $t = '';
                $n = '';
            } else {
                $t = "\t";
                $n = "\n";
            }
            $output .= "</li>{$n}";
        }

        /**
         * Ends the list of after the elements are added.
         *
         * @see Walker::end_lvl()
         *
         * @param string   $output Used to append additional content (passed by reference).
         * @param int      $depth  Depth of menu item. Used for padding.
         * @param stdClass $args   An object of wp_nav_menu() arguments.
         */
        public function end_lvl( &$output, $depth = 0, $args = null ) {
            if ( isset( $args->item_spacing ) && 'discard' === $args->item_spacing ) {
                $t = '';
                $n = '';
            } else {
                $t = "\t";
                $n = "\n";
            }
            $indent  = str_repeat( $t, $depth );
            $output .= "$indent</ul>{$n}";
        }
    }
}

// To use this custom walker in your theme (e.g., in header.php):
// wp_nav_menu( array(
// 'theme_location' => 'primary',
// 'menu_class'     => 'primary-menu', // Your existing menu class
// 'container'      => false, // Or your preferred container
// 'walker'         => new SolanaWP_Nav_Walker(),
//      'li_class'       => 'primary-menu__item', // Custom class for <li>
//      'link_class'     => 'primary-menu__link', // Custom class for <a>
//      'sub_menu_class' => 'primary-menu__sub-menu' // Custom class for <ul> (sub-menu)
// ) );
