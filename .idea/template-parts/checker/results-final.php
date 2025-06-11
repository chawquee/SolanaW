<?php
/**
 * Template part for displaying the "Final Results" card for the Solana Checker.
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
<div class="card" id="finalResultsCard" style="display:none;"> <?php // Structure & ID from hannisolsvelte.html, initially hidden ?>
    <div class="card-header"> <?php // Class from hannisolsvelte.html ?>
        <svg class="icon text-green" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"> <?php // Class and SVG from hannisolsvelte.html ?>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
        </svg>
        <h2 class="card-title"><?php esc_html_e( 'Final Results', 'solanawp' ); ?></h2> <?php // Text from hannisolsvelte.html ?>
    </div>
    <div class="card-content"> <?php // Class from hannisolsvelte.html ?>
        <div class="final-scores-grid"> <?php // Class from main.css, structure from hannisolsvelte.html ?>
            <div class="score-item trust"> <?php // Added class for specific icon styling ?>
                <div class="score-icon-wrapper"> <?php // Class from main.css ?>
                    <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"> <?php // SVG from hannisolsvelte.html ?>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <div class="score-value" id="finalTrustScore">-</div> <?php // Placeholder for JS ?>
                <div class="score-label"><?php esc_html_e( 'Trust Score', 'solanawp' ); ?></div> <?php // Text from hannisolsvelte.html ?>
            </div>
            <div class="score-item reliability"> <?php // Added class for specific icon styling ?>
                <div class="score-icon-wrapper">
                    <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"> <?php // SVG from hannisolsvelte.html ?>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="score-value" id="finalReliabilityScore">-</div> <?php // Placeholder for JS ?>
                <div class="score-label"><?php esc_html_e( 'Reliability Score', 'solanawp' ); ?></div> <?php // Text from hannisolsvelte.html ?>
            </div>
            <div class="score-item rating"> <?php // Added class for specific icon styling ?>
                <div class="score-icon-wrapper">
                    <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"> <?php // SVG from hannisolsvelte.html ?>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                    </svg>
                </div>
                <div class="score-value" id="finalOverallRating">-</div> <?php // Placeholder for JS ?>
                <div class="score-label"><?php esc_html_e( 'Overall Rating', 'solanawp' ); ?></div> <?php // Text from hannisolsvelte.html ?>
            </div>
        </div>

        <div class="summary-box"> <?php // Class from main.css, structure from hannisolsvelte.html ?>
            <h3><?php esc_html_e( 'Summary', 'solanawp' ); ?></h3> <?php // Text from hannisolsvelte.html ?>
            <p id="finalSummaryText">
                <?php // Summary text will be populated by JS. Placeholder from hannisolsvelte.html: ?>
                <?php // esc_html_e( 'This Solana address appears to be a legitimate Nonce Account with moderate activity and no suspicious behavior detected. The account has been active since August 2021 and maintains a healthy balance with multiple token holdings. Community engagement is high with positive growth indicators. Overall, this address receives an A- rating based on our comprehensive analysis.', 'solanawp' ); ?>
            </p>
        </div>
    </div>
</div>
