<?php
/**
 * Template part for displaying the "Affiliate Marketing Section" for the Solana Checker.
 * Called by template-address-checker.php or front-page.php.
 * Structure from hannisolsvelte.html. Links are dynamic via Customizer.
 * Supports monetization strategy.
 *
 * @package SolanaWP
 * @since SolanaWP 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define affiliate items based on Customizer settings or defaults.
// Keys match those used in inc/customizer.php.
$affiliate_items_config = array(
    'ledger' => array(
        'title' => esc_html__( 'Ledger Wallet', 'solanawp' ), // Text from hannisolsvelte.html
        'desc'  => esc_html__( 'Hardware Security', 'solanawp' ), // Text from hannisolsvelte.html
    ),
    'vpn'    => array(
        'title' => esc_html__( 'VPN Service', 'solanawp' ), // Text from hannisolsvelte.html
        'desc'  => esc_html__( 'Privacy Protection', 'solanawp' ), // Text from hannisolsvelte.html
    ),
    'guide'  => array(
        'title' => esc_html__( 'Security Guide', 'solanawp' ), // Text from hannisolsvelte.html
        'desc'  => esc_html__( 'Learn More', 'solanawp' ), // Text from hannisolsvelte.html
    ),
    'course' => array(
        'title' => esc_html__( 'Crypto Course', 'solanawp' ), // Text from hannisolsvelte.html
        'desc'  => esc_html__( 'Education', 'solanawp' ), // Text from hannisolsvelte.html
    ),
);

$active_affiliate_items = array();
foreach ($affiliate_items_config as $key => $config) {
    $url = get_theme_mod( "solanawp_affiliate_{$key}_url", '' ); // Get URL from Customizer
    if ( ! empty( $url ) && '#' !== $url ) { // Only include if URL is set and not just '#'
        $active_affiliate_items[] = array(
            'title'    => $config['title'],
            'desc'     => $config['desc'],
            'link_url' => $url,
        );
    }
}

// Only display the section if there are active affiliate links.
if ( empty( $active_affiliate_items ) ) {
    // Optionally, you could hide the entire #affiliateSection via JS if this part returns nothing,
    // or ensure the parent template part (template-address-checker.php) hides #affiliateSection initially.
    // For now, this template part simply won't output if no links are configured beyond '#'.
    return;
}
?>
<div class="affiliate-section" id="affiliateSection"> <?php // Structure & ID from hannisolsvelte.html, display controlled by JS or based on content ?>
    <div class="affiliate-title"><?php echo esc_html__( '🔐 Recommended Security Tools', 'solanawp' ); ?></div> <?php // Text from hannisolsvelte.html ?>
    <div class="affiliate-grid"> <?php // Class from hannisolsvelte.html ?>
        <?php
        foreach ( $active_affiliate_items as $item ) :
            // Call the affiliate-item template part for each item
            get_template_part('template-parts/affiliate/affiliate-item', null, array(
                'title'       => $item['title'],
                'description' => $item['desc'],
                'link_url'    => $item['link_url'],
                // target and rel will use defaults from affiliate-item.php
            ));
        endforeach;
        ?>
    </div>
</div>
