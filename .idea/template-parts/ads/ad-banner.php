<?php
/**
 * Template part for displaying a generic ad banner structure.
 * This can be used by custom widgets (like SolanaWP_Ad_Banner_Widget) or directly in templates.
 * The styling for .ad-banner and .ad-banner.small comes from main.css,
 * which is based on hannisolsvelte.html.
 * This supports the monetization strategy.
 *
 * @package SolanaWP
 * @since SolanaWP 1.0.0
 *
 * @param array $args {
 * Optional. Array of arguments passed to the template part.
 *
 * @type string $size            Optional. Size of the ad banner ('small' or 'large'/'default').
 * If 'small', the class '.small' is added to '.ad-banner'.
 * @type string $ad_title        Optional. Title/Network for the ad (e.g., "A-ADS").
 * @type string $ad_description  Optional. Description or type of ad (e.g., "Crypto Ad Network").
 * @type string $ad_details      Optional. Additional details like CPM/CPC (e.g., "$0.50-$2.00 CPM").
 * @type string $ad_code         Optional. Actual ad code HTML/JS. If provided, title/desc/details are ignored for display.
 * @type string $custom_classes  Optional. Additional custom classes for the ad banner div.
 * }
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Set defaults for arguments
$default_args = array(
    'size'           => '', // 'small' or empty for default/large (250px height)
    'ad_title'       => '',
    'ad_description' => '',
    'ad_details'     => '',
    'ad_code'        => '',
    'custom_classes' => '',
);
// Allow $args to be passed via set_query_var or directly if using include.
// For get_template_part, $args is the third parameter.
$args = wp_parse_args( isset($args) ? $args : array(), $default_args );


$banner_classes = 'ad-banner'; // Base class from hannisolsvelte.html
if ( ! empty( $args['size'] ) && 'small' === $args['size'] ) {
    $banner_classes .= ' small'; // Adds .small class for 120px height, from hannisolsvelte.html
}
if ( ! empty( $args['custom_classes'] ) ) {
    $banner_classes .= ' ' . esc_attr($args['custom_classes']);
}

// Determine font sizes based on banner size, mimicking hannisolsvelte.html structure examples
$title_style = '';
$desc_style = '';
$details_style = 'font-size: 12px; opacity: 0.7;'; // Consistent for both sizes in HTML examples

if ( 'small' === $args['size'] ) {
    $title_style = 'font-size: 16px; margin-bottom: 4px;'; // From .ad-banner.small examples in hannisolsvelte.html
    $desc_style = 'font-size: 14px;'; // From .ad-banner.small examples in hannisolsvelte.html
} else {
    $title_style = 'font-size: 18px; margin-bottom: 8px;'; // From .ad-banner (large) examples in hannisolsvelte.html
    // Description for large banner in HTML example uses default font size, so $desc_style can be empty.
}

?>

<div class="<?php echo esc_attr( $banner_classes ); ?>">
    <?php if ( ! empty( $args['ad_code'] ) ) : ?>
        <?php echo $args['ad_code']; // IMPORTANT: Ad code often contains <script> tags.
        // Ensure this is only populated from trusted sources (e.g., admin settings
        // where only users with 'unfiltered_html' capability can save it).
        // No direct esc_html() here as it would break scripts. ?>
    <?php else : // Display structured text if no ad_code is provided ?>
        <div>
            <?php if ( ! empty( $args['ad_title'] ) ) : ?>
                <div style="<?php echo esc_attr($title_style); ?>">
                    <?php echo esc_html( $args['ad_title'] ); ?>
                </div>
            <?php endif; ?>

            <?php if ( ! empty( $args['ad_description'] ) ) : ?>
                <div style="<?php echo esc_attr($desc_style); ?>">
                    <?php echo esc_html( $args['ad_description'] ); ?>
                </div>
            <?php endif; ?>

            <?php if ( ! empty( $args['ad_details'] ) ) : ?>
                <div style="<?php echo esc_attr($details_style); ?>">
                    <?php echo esc_html( $args['ad_details'] ); ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
