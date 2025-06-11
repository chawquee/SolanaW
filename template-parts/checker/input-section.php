<?php
/**
 * Template part for displaying the address input section for the Solana Checker.
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
<div class="input-section"> <?php // Class from hannisolsvelte.html ?>
    <div class="input-container"> <?php // Class from hannisolsvelte.html ?>
        <input
            type="text"
            class="address-input" <?php // Class from hannisolsvelte.html ?>
            id="solanaAddressInput" <?php // ID for JavaScript targeting (main.js) ?>
            placeholder="<?php esc_attr_e( 'Enter Solana address to analyze... (e.g., 5DF4D...3RcB3gf3Z)', 'solanawp' ); ?>" <?php // Placeholder text from hannisolsvelte.html ?>
            aria-label="<?php esc_attr_e( 'Solana Address Input', 'solanawp' ); ?>"
        />
        <button class="check-btn" id="checkAddressBtn"> <?php // Class from hannisolsvelte.html and ID for JavaScript targeting ?>
            <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"> <?php // Class and SVG from hannisolsvelte.html ?>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            <?php esc_html_e( 'Check Address', 'solanawp' ); ?> <?php // Button text from hannisolsvelte.html ?>
        </button>
    </div>
</div>
