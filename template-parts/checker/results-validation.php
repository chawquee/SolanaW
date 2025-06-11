<?php
/**
 * Template part for displaying the "Address Validation" card for the Solana Checker.
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
<div class="card" id="addressValidationCard"> <?php // Structure & ID from hannisolsvelte.html ?>
    <div class="card-header"> <?php // Class from hannisolsvelte.html ?>
        <svg class="icon text-green" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"> <?php // Class and SVG from hannisolsvelte.html ?>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <h2 class="card-title"><?php esc_html_e( 'Address Validation', 'solanawp' ); ?></h2> <?php // Text from hannisolsvelte.html ?>
    </div>
    <div class="card-content"> <?php // Class from hannisolsvelte.html ?>
        <div class="validation-grid"> <?php // Class from hannisolsvelte.html ?>
            <div class="validation-item"><span><strong><?php esc_html_e( 'Status:', 'solanawp' ); ?></strong> <span id="validationStatus">-</span></span></div> <?php // Placeholder for JS, text from hannisolsvelte.html ?>
            <div class="validation-item"><span><strong><?php esc_html_e( 'Format:', 'solanawp' ); ?></strong> <span id="validationFormat">-</span></span></div> <?php // Placeholder for JS, text from hannisolsvelte.html ?>
            <div class="validation-item"><span><strong><?php esc_html_e( 'Length:', 'solanawp' ); ?></strong> <span id="validationLength">-</span></span></div> <?php // Placeholder for JS, text from hannisolsvelte.html ?>
            <div class="validation-item"><span><strong><?php esc_html_e( 'Type:', 'solanawp' ); ?></strong> <span id="validationType">-</span></span></div> <?php // Placeholder for JS, text from hannisolsvelte.html ?>
        </div>
        <div class="success-banner" style="display:none;" id="validationNoteBanner"> <?php // Class & ID from hannisolsvelte.html, initially hidden ?>
            <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"> <?php // Class and SVG from hannisolsvelte.html ?>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <strong id="validationNoteText"><?php esc_html_e( 'Note: Valid Solana address', 'solanawp' ); ?></strong> <?php // Placeholder for JS, text from hannisolsvelte.html ?>
        </div>
    </div>
</div>
