<?php
/**
 * Template part for displaying a single affiliate item.
 *
 * Can be used in the affiliate section (e.g., template-parts/checker/results-affiliate.php)
 * by calling get_template_part with $args.
 *
 * @package SolanaWP
 * @since SolanaWP 1.0.0
 *
 * @param array $args {
 * Optional. Array of arguments.
 *
 * @type string $title       The title of the affiliate item (e.g., "Ledger Wallet").
 * @type string $description The short description (e.g., "Hardware Security").
 * @type string $link_url    The affiliate link URL.
 * @type string $target      Link target (e.g., "_blank").
 * @type string $rel         Link rel attribute (e.g., "noopener noreferrer sponsored").
 * }
 */

// Set defaults for arguments
$default_args = array(
    'title'       => esc_html__( 'Affiliate Product', 'solanawp' ),
    'description' => '',
    'link_url'    => '#',
    'target'      => '_blank',
    'rel'         => 'noopener noreferrer sponsored',
);
$args = wp_parse_args( $args, $default_args );

// Classes and styles are based on .affiliate-item in hannisolsvelte.html
?>
<div class="affiliate-item">
    <a href="<?php echo esc_url( $args['link_url'] ); ?>"
       target="<?php echo esc_attr( $args['target'] ); ?>"
       <?php if ( ! empty( $args['rel'] ) ) : ?>rel="<?php echo esc_attr( $args['rel'] ); ?>"<?php endif; ?>>
        <strong><?php echo esc_html( $args['title'] ); ?></strong>
        <?php if ( ! empty( $args['description'] ) ) : ?>
            <div style="font-size: 12px; color: #0c4a6e;"><?php echo esc_html( $args['description'] ); ?></div> <?php // Style from hannisolsvelte.html ?>
        <?php endif; ?>
    </a>
</div>
