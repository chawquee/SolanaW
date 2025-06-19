<?php
/**
 * Template part for displaying the ENHANCED "Account Details" card for the Solana Checker.
 * PHASE 1 IMPLEMENTATION - Enhanced with Alchemy API data display
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
<div class="card" id="accountDetailsCard" style="display:none;">
    <div class="card-header">
        <svg class="icon text-blue" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>
        <h2 class="card-title"><?php esc_html_e( 'Account Details', 'solanawp' ); ?></h2>
    </div>
    <div class="card-content">
        <!-- PHASE 1: Enhanced Account Details Grid -->
        <div class="metrics-grid" style="grid-template-columns: repeat(2, 1fr); gap: 16px;">
            <!-- OWNER - PHASE 1 REQUIREMENT -->
            <div class="metric-card">
                <div class="metric-label"><?php esc_html_e( 'Owner:', 'solanawp' ); ?></div>
                <div class="metric-value" id="accOwner" style="font-size: 0.9em; word-break: break-all;">-</div>
            </div>

            <!-- EXECUTABLES - PHASE 1 REQUIREMENT -->
            <div class="metric-card">
                <div class="metric-label"><?php esc_html_e( 'Executable:', 'solanawp' ); ?></div>
                <div class="metric-value" id="accExecutable">-</div>
            </div>

            <!-- DATA SIZE - PHASE 1 REQUIREMENT -->
            <div class="metric-card">
                <div class="metric-label"><?php esc_html_e( 'Data Size (bytes):', 'solanawp' ); ?></div>
                <div class="metric-value" id="accDataSize">-</div>
            </div>

            <!-- RENT EPOCH - PHASE 1 REQUIREMENT -->
            <div class="metric-card">
                <div class="metric-label"><?php esc_html_e( 'Rent Epoch:', 'solanawp' ); ?></div>
                <div class="metric-value" id="accRentEpoch">-</div>
            </div>
        </div>


        <div style="margin-top: 16px; padding: 12px; background: #f3f4f6; border-radius: 8px;">
            <div style="display: flex; align-items: center; gap: 8px;">
               <svg class="icon" style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>-->
                <span style="font-weight: 600; font-size: 0.9em;"><?php esc_html_e( '', 'solanawp' ); ?></span>
                <span id="accAccountType" style="font-size: 0.9em;"></span>
            </div>
        </div>
    </div>
</div>
