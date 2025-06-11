<?php
/**
 * Template part for displaying the "Security Analysis" card for the Solana Checker.
 * Called within the account-security grid in template-address-checker.php.
 * Structure and classes from hannisolsvelte.html.
 *
 * @package SolanaWP
 * @since SolanaWP 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="card" id="securityAnalysisCard" style="display:none;"> <?php // Structure & ID from hannisolsvelte.html, initially hidden by parent grid ?>
    <div class="card-header"> <?php // Class from hannisolsvelte.html ?>
        <svg class="icon text-yellow" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"> <?php // Class and SVG from hannisolsvelte.html ?>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
        </svg>
        <h2 class="card-title"><?php esc_html_e( 'Security Analysis', 'solanawp' ); ?></h2> <?php // Text from hannisolsvelte.html ?>
    </div>
    <div class="card-content"> <?php // Class from hannisolsvelte.html ?>
        <div> <?php // Wrapper for detail items ?>
            <div class="security-detail-item"> <?php // Class for styling from main.css ?>
                <span><?php esc_html_e( 'Risk Level:', 'solanawp' ); ?></span> <?php // Text from hannisolsvelte.html ?>
                <span id="secRiskLevel">-</span> <?php // Placeholder for JS, color applied via JS or main.css based on value ?>
            </div>
            <div class="security-detail-item">
                <span><?php esc_html_e( 'Known Scam:', 'solanawp' ); ?></span> <?php // Text from hannisolsvelte.html ?>
                <div id="secKnownScam"> <?php // JS Target for icon + text ?>
                    <span class="value-placeholder">-</span> <?php // Default placeholder, JS will replace this with icon+text ?>
                </div>
            </div>
            <div class="security-detail-item">
                <span><?php esc_html_e( 'Suspicious Activity:', 'solanawp' ); ?></span> <?php // Text from hannisolsvelte.html ?>
                <div id="secSuspiciousActivity"> <?php // JS Target for icon + text ?>
                    <span class="value-placeholder">-</span> <?php // Default placeholder, JS will replace this with icon+text ?>
                </div>
            </div>
        </div>
    </div>
</div>
