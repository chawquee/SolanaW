<?php
/**
 * Template part for displaying the "Account Details" card for the Solana Checker.
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
<div class="card" id="accountDetailsCard" style="display:none;"> <?php // Structure & ID from hannisolsvelte.html, initially hidden by parent grid ?>
    <div class="card-header"> <?php // Class from hannisolsvelte.html ?>
        <svg class="icon text-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"> <?php // Class and SVG from hannisolsvelte.html ?>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
        <h2 class="card-title"><?php esc_html_e( 'Account Details', 'solanawp' ); ?></h2> <?php // Text from hannisolsvelte.html ?>
    </div>
    <div class="card-content"> <?php // Class from hannisolsvelte.html ?>
        <div> <?php // Wrapper for detail items ?>
            <div class="account-detail-item"> <?php // Class for styling from main.css ?>
                <span><?php esc_html_e( 'Owner:', 'solanawp' ); ?></span> <?php // Text from hannisolsvelte.html ?>
                <span id="accOwner">-</span> <?php // Placeholder for JS ?>
            </div>
            <div class="account-detail-item">
                <span><?php esc_html_e( 'Executable:', 'solanawp' ); ?></span> <?php // Text from hannisolsvelte.html ?>
                <span id="accExecutable">-</span> <?php // Placeholder for JS ?>
            </div>
            <div class="account-detail-item">
                <span><?php esc_html_e( 'Data Size:', 'solanawp' ); ?></span> <?php // Text from hannisolsvelte.html ?>
                <span id="accDataSize">-</span> <?php // Placeholder for JS ?>
            </div>
            <div class="account-detail-item">
                <span><?php esc_html_e( 'Rent Epoch:', 'solanawp' ); ?></span> <?php // Text from hannisolsvelte.html ?>
                <span id="accRentEpoch">-</span> <?php // Placeholder for JS ?>
            </div>
        </div>
    </div>
</div>
