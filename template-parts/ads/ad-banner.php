<?php
/**
 * Template part for displaying a generic ad banner structure.
 * This can be used by custom widgets or directly in templates.
 *
 * @package SolanaWP
 * @since SolanaWP 1.0.0
 *
 * @param array $args {
 * Optional. Array of arguments passed to the template part.
 * @type string $size            Optional. Size of the ad banner ('small' or 'large'/'default').
 * @type string $ad_title        Optional. Title/Network for the ad.
 * @type string $ad_description  Optional. Description or type of ad.
 * @type string $ad_details      Optional. Additional details like CPM/CPC.
 * @type string $ad_code         Optional. Actual ad code HTML/JS.
 * @type string $ad_url          Optional. URL for text-based ads.
 * @type string $custom_classes  Optional. Additional custom classes for the ad banner div.
 * @type string $ad_section_slug Optional. Slug for the Customizer ad section (e.g., 'solanawp_left_ads_section').
 * }
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$default_args = array(
    'size'           => '',
    'ad_title'       => '',
    'ad_description' => '',
    'ad_details'     => '',
    'ad_code'        => '',
    'ad_url'         => '#',
    'custom_classes' => '',
    'ad_section_slug'=> '', // New argument for admin link
);
$args = wp_parse_args( isset($args) ? $args : array(), $default_args );

$banner_classes = 'ad-banner';
if ( ! empty( $args['size'] ) && 'small' === $args['size'] ) {
    $banner_classes .= ' small';
}
if ( ! empty( $args['custom_classes'] ) ) {
    $banner_classes .= ' ' . esc_attr($args['custom_classes']);
}

$title_style = ('small' === $args['size']) ? 'font-size: 16px; margin-bottom: 4px; font-weight: bold;' : 'font-size: 18px; margin-bottom: 8px; font-weight: bold;';
$desc_style = ('small' === $args['size']) ? 'font-size: 14px;' : '';
$details_style = 'font-size: 12px; opacity: 0.7; margin-top: 4px;';

$link_attrs = ( $args['ad_url'] !== '#' && filter_var($args['ad_url'], FILTER_VALIDATE_URL) ) ? 'href="' . esc_url( $args['ad_url'] ) . '" target="_blank" rel="noopener noreferrer sponsored"' : '';

?>

<div class="<?php echo esc_attr( $banner_classes ); ?>">
    <?php if ( ! empty( $args['ad_code'] ) ) : ?>
        <?php echo $args['ad_code']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped (Ad code from trusted source) ?>
    <?php else : ?>
        <?php if ( $link_attrs ) : ?><a <?php echo $link_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> style="text-decoration: none; color: inherit; display: block; width: 100%; height: 100%; padding: 10px; box-sizing: border-box;"><?php endif; ?>
        <div style="display: flex; flex-direction: column; justify-content: center; align-items: center; height: 100%;">
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
        <?php if ( $link_attrs ) : ?></a><?php endif; ?>
    <?php endif; ?>

    <?php // Admin Config Link - This would be included if the rendering function passes 'ad_section_slug' ?>
    <?php if ( current_user_can( 'customize' ) && !empty( $args['ad_section_slug'] ) ) :
        $customizer_link = admin_url( 'customize.php?autofocus[section]=' . esc_attr($args['ad_section_slug']) );
        ?>
        <div class="admin-configure-ad-link">
            <a href="<?php echo esc_url( $customizer_link ); ?>" title="<?php esc_attr_e('Configure this ad', 'solanawp'); ?>">&#9881;</a>
        </div>
    <?php endif; ?>
</div>
