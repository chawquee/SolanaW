<?php
/**
 * Custom Widgets for SolanaWP Theme.
 *
 * @package SolanaWP
 * @since SolanaWP 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'SolanaWP_Ad_Banner_Widget' ) ) :
    /**
     * SolanaWP Ad Banner Widget class.
     *
     * Creates a widget to display an ad banner, using the structure from hannisolsvelte.html.
     * This helps with the monetization strategy.
     */
    class SolanaWP_Ad_Banner_Widget extends WP_Widget {

        /**
         * Constructor. Sets up the widget.
         */
        public function __construct() {
            parent::__construct(
                'solanawp_ad_banner_widget', // Base ID
                esc_html__( 'SolanaWP Ad Banner', 'solanawp' ), // Name
                array(
                    'description'                 => esc_html__( 'Displays a styled ad banner. Use with "Ad Banner Area (Sidebar)".', 'solanawp' ),
                    'customize_selective_refresh' => true,
                )
            );
        }

        /**
         * Outputs the content of the widget on the frontend.
         *
         * @param array $args     Display arguments including 'before_widget', 'after_widget',
         * 'before_title', and 'after_title'.
         * @param array $instance The settings for the particular instance of the widget.
         */
        public function widget( $args, $instance ) {
            $title       = ! empty( $instance['title'] ) ? $instance['title'] : ''; // This title is for admin, usually hidden on frontend by widget area args.
            $ad_size     = ! empty( $instance['ad_size'] ) ? $instance['ad_size'] : 'large';
            $ad_title    = ! empty( $instance['ad_title'] ) ? $instance['ad_title'] : '';
            $ad_desc     = ! empty( $instance['ad_desc'] ) ? $instance['ad_desc'] : '';
            $ad_details  = ! empty( $instance['ad_details'] ) ? $instance['ad_details'] : '';
            $ad_code     = ! empty( $instance['ad_code'] ) ? $instance['ad_code'] : ''; // Raw HTML/JS ad code

            echo $args['before_widget']; // From register_sidebar (e.g., <div id="%1$s" class="widget solanawp-ad-widget %2$s">)

            if ( ! empty( $title ) ) { // Admin title, usually hidden by `before_title` class.
                echo $args['before_title'] . esc_html( $title ) . $args['after_title'];
            }

            // Prepare arguments for the template part.
            $template_args = array(
                'size'        => $ad_size,
                'title'       => $ad_title,
                'description' => $ad_desc,
                'details'     => $ad_details,
                'ad_code'     => $ad_code, // Pass the raw ad code
            );

            // Use the ad-banner template part for consistent rendering.
            // The ad-banner.php uses classes from hannisolsvelte.html.
            get_template_part( 'template-parts/ads/ad-banner', null, $template_args );

            echo $args['after_widget'];
        }

        /**
         * Outputs the settings update form in the admin.
         *
         * @param array $instance Current settings.
         */
        public function form( $instance ) {
            $title       = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : esc_html__( 'Ad Banner', 'solanawp' );
            $ad_size     = isset( $instance['ad_size'] ) ? $instance['ad_size'] : 'large';
            $ad_title    = isset( $instance['ad_title'] ) ? esc_attr( $instance['ad_title'] ) : '';
            $ad_desc     = isset( $instance['ad_desc'] ) ? esc_attr( $instance['ad_desc'] ) : '';
            $ad_details  = isset( $instance['ad_details'] ) ? esc_attr( $instance['ad_details'] ) : '';
            $ad_code     = isset( $instance['ad_code'] ) ? esc_textarea( $instance['ad_code'] ) : '';
            ?>
            <p>
                <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Admin Title (optional):', 'solanawp' ); ?></label>
                <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo $title; ?>" />
            </p>
            <p>
                <label for="<?php echo esc_attr( $this->get_field_id( 'ad_size' ) ); ?>"><?php esc_html_e( 'Ad Banner Size:', 'solanawp' ); ?></label>
                <select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'ad_size' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'ad_size' ) ); ?>">
                    <option value="large" <?php selected( $ad_size, 'large' ); ?>><?php esc_html_e( 'Large (250px height)', 'solanawp' ); ?></option>
                    <option value="small" <?php selected( $ad_size, 'small' ); ?>><?php esc_html_e( 'Small (120px height)', 'solanawp' ); ?></option>
                </select>
            </p>
            <hr>
            <p><em><?php esc_html_e( 'Fill EITHER the fields below OR the Ad Code field, but not both. Ad Code takes precedence.', 'solanawp' ); ?></em></p>
            <p>
                <label for="<?php echo esc_attr( $this->get_field_id( 'ad_title' ) ); ?>"><?php esc_html_e( 'Ad Display Title:', 'solanawp' ); ?></label>
                <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'ad_title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'ad_title' ) ); ?>" type="text" value="<?php echo $ad_title; ?>" />
            </p>
            <p>
                <label for="<?php echo esc_attr( $this->get_field_id( 'ad_desc' ) ); ?>"><?php esc_html_e( 'Ad Description:', 'solanawp' ); ?></label>
                <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'ad_desc' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'ad_desc' ) ); ?>" type="text" value="<?php echo $ad_desc; ?>" />
            </p>
            <p>
                <label for="<?php echo esc_attr( $this->get_field_id( 'ad_details' ) ); ?>"><?php esc_html_e( 'Ad Details (e.g., CPM/CPC):', 'solanawp' ); ?></label>
                <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'ad_details' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'ad_details' ) ); ?>" type="text" value="<?php echo $ad_details; ?>" />
            </p>
            <hr>
            <p>
                <label for="<?php echo esc_attr( $this->get_field_id( 'ad_code' ) ); ?>"><?php esc_html_e( 'Raw Ad Code (HTML/JS):', 'solanawp' ); ?></label>
                <textarea class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'ad_code' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'ad_code' ) ); ?>" rows="6"><?php echo $ad_code; ?></textarea>
                <small><?php esc_html_e( 'If you paste ad code here (e.g., from AdSense or A-ADS), it will be used directly. Otherwise, the fields above will be used to construct a simple text-based ad.', 'solanawp' ); ?></small>
            </p>
            <?php
        }

        /**
         * Sanitize widget form values as they are saved.
         *
         * @see WP_Widget::update()
         *
         * @param array $new_instance Values just sent to be saved.
         * @param array $old_instance Previously saved values from database.
         *
         * @return array Updated safe values to be saved.
         */
        public function update( $new_instance, $old_instance ) {
            $instance             = $old_instance;
            $instance['title']    = sanitize_text_field( $new_instance['title'] );
            $instance['ad_size']  = ( in_array( $new_instance['ad_size'], array( 'small', 'large' ) ) ) ? $new_instance['ad_size'] : 'large';
            $instance['ad_title'] = sanitize_text_field( $new_instance['ad_title'] );
            $instance['ad_desc']  = sanitize_text_field( $new_instance['ad_desc'] );
            $instance['ad_details'] = sanitize_text_field( $new_instance['ad_details'] );
            // Ad code can contain scripts, so use kses with script context or similar,
            // or trust admins to paste valid code. For simplicity, using kses_post to allow basic HTML.
            // For full script support, you might need to use `unfiltered_html` capability check or a more permissive kses.
            // However, `wp_kses_scripts` is not a thing. `wp_kses_post` is often used for content with HTML.
            // Ad codes often require unfiltered_html. Let's assume admins are trusted.
            if ( current_user_can( 'unfiltered_html' ) ) {
                $instance['ad_code'] = $new_instance['ad_code'];
            } else {
                $instance['ad_code'] = wp_kses_post( $new_instance['ad_code'] );
            }
            return $instance;
        }
    }
endif;

/**
 * Register the custom widget.
 * This function is hooked into 'widgets_init'.
 */
function solanawp_register_custom_widgets() {
    register_widget( 'SolanaWP_Ad_Banner_Widget' );
    // Register other custom widgets here
}

