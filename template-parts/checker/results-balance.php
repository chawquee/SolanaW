<?php
/**
 * Template part for displaying the "Balance & Holdings" card for the Solana Checker.
 * Called by template-address-checker.php or front-page.php.
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
<div class="card" id="balanceHoldingsCard" style="display:none;"> <?php // Structure & ID from hannisolsvelte.html, initially hidden ?>
    <div class="card-header"> <?php // Class from hannisolsvelte.html ?>
        <svg class="icon text-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"> <?php // Class and SVG from hannisolsvelte.html ?>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
        </svg>
        <h2 class="card-title"><?php esc_html_e( 'Balance & Holdings', 'solanawp' ); ?></h2> <?php // Text from hannisolsvelte.html ?>
    </div>
    <div class="card-content"> <?php // Class from hannisolsvelte.html ?>
        <div class="balance-holdings-grid"> <?php // Class defined in main.css based on inline styles from hannisolsvelte.html ?>
            <div class="balance-item">
                <h4><?php esc_html_e( 'SOL Balance', 'solanawp' ); ?></h4> <?php // Text from hannisolsvelte.html ?>
                <div class="balance-data">
                    <div class="sol-balance-value" id="solBalanceValue">- SOL</div> <?php // Placeholder for JS, class from main.css, text from hannisolsvelte.html ?>
                    <div class="sol-balance-usd" id="solBalanceUsdValue">$ - USD</div> <?php // Placeholder for JS, class from main.css, text from hannisolsvelte.html ?>
                </div>
            </div>
            <div class="holdings-item">
                <h4><?php esc_html_e( 'Token Holdings', 'solanawp' ); ?></h4> <?php // Text from hannisolsvelte.html ?>
                <div class="holdings-data">
                    <div class="token-holdings-item">
                        <span><?php esc_html_e( 'Tokens:', 'solanawp' ); ?></span> <?php // Text from hannisolsvelte.html ?>
                        <span id="tokenCount">-</span> <?php // Placeholder for JS ?>
                    </div>
                    <div class="token-holdings-item">
                        <span><?php esc_html_e( 'NFTs:', 'solanawp' ); ?></span> <?php // Text from hannisolsvelte.html ?>
                        <span id="nftCount">-</span> <?php // Placeholder for JS ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
