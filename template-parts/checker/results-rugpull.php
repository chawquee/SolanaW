<?php
/**
 * Template part for displaying the "Rug Pull Risk Analysis" card for the Solana Checker.
 * Called by template-address-checker.php or front-page.php.
 * Structure and classes from hannisolsvelte.html.
 *
 * UPDATED: Token Distribution section removed and moved to standalone results-token-distribution.php
 * UPDATED: Changed "Risk Level" to "Risks" and added dedicated Risks subsection
 *
 * @package SolanaWP
 * @since SolanaWP 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="card" id="rugPullRiskCard" style="display:none;">
    <div class="card-header">
        <span style="font-size: 1.5rem;">⚠️</span>
        <h2 class="card-title"><?php esc_html_e( 'Rug Pull Risk Analysis', 'solanawp' ); ?></h2>
    </div>
    <div class="card-content">

        <!-- Main Risk Metrics (Top Row) -->
        <div class="rug-metrics-grid">
            <div class="rug-metric-card">
                <div class="rug-metric-label"><?php esc_html_e( 'Rug-Pull Risk Score', 'solanawp' ); ?></div>
                <div class="rug-metric-value text-yellow" id="rugOverallScore">-</div>
                <div class="rug-metric-sublabel">(0-100)</div>
            </div>
            <div class="rug-metric-card">
                <div class="rug-metric-label"><?php esc_html_e( 'Detected Risks', 'solanawp' ); ?></div> <?php // UPDATED: Changed from "Risk Level" to "Risks" ?>
                <div class="rug-metric-value text-green" id="rugRisksLevel">-</div>
            </div>
            <div class="rug-metric-card">
                <div class="rug-metric-label"><?php esc_html_e( 'Rugged Status', 'solanawp' ); ?></div>
                <div class="rug-metric-value text-red" id="ruggedStatus">-</div>
                <div class="rug-metric-sublabel" id="ruggedDate"></div>
            </div>
            <div class="rug-metric-card">
                <div class="rug-metric-label"><?php esc_html_e( 'Mutable', 'solanawp' ); ?></div>
                <div class="rug-metric-value text-purple" id="mutableStatus">-</div>
            </div>
        </div>

        <!-- Risk Explanations -->
        <div class="rug-explanations-grid">
            <div class="rug-explanation-box rug-explanation-blue">
                <strong><?php esc_html_e( 'Score:', 'solanawp' ); ?></strong> <?php esc_html_e( 'Lower scores indicate safer investments.', 'solanawp' ); ?>
            </div>
            <div class="rug-explanation-box rug-explanation-green">
                <strong><?php esc_html_e( 'Good:', 'solanawp' ); ?></strong> <?php esc_html_e( 'Low risk levels and renounced authorities reduce rug pull risk.', 'solanawp' ); ?>
            </div>
            <div class="rug-explanation-box rug-explanation-red">
                <strong><?php esc_html_e( 'Warning:', 'solanawp' ); ?></strong> <?php esc_html_e( 'A "Yes" indicates the project has been identified as a rug pull.', 'solanawp' ); ?>
            </div>
            <div class="rug-explanation-box rug-explanation-yellow">
                <strong><?php esc_html_e( 'Warning:', 'solanawp' ); ?></strong> <?php esc_html_e( '"Yes" means metadata can be changed, potentially misleading investors.', 'solanawp' ); ?>
            </div>
        </div>

        <!-- TWO-COLUMN LAYOUT -->
        <div class="two-column">

            <!-- LEFT COLUMN: Security & Liquidity -->
            <div class="left-column">

                <!-- Security & Liquidity Section -->
                <div class="security-liquidity-section section-card">
                    <div class="section-header">
                        <span style="font-size: 1.5rem;">🔒</span>
                        <h3 class="section-title"><?php esc_html_e( 'Liquidity Analysis', 'solanawp' ); ?></h3>
                    </div>

                    <!-- Security Metrics -->
                    <div class="security-metrics">
                        <div class="risk-metric-card" id="mintAuthorityContainer">
                            <div class="metric-info">
                                <span class="metric-icon" id="mintAuthorityIcon">🔨</span>
                                <div class="metric-title"><?php esc_html_e( 'Mint Authority', 'solanawp' ); ?></div>
                                <div class="metric-status" id="mintAuthorityStatus"><?php esc_html_e( 'Checking...', 'solanawp' ); ?></div>
                            </div>
                            <div class="metric-description" id="mintAuthorityExplanation"><?php esc_html_e( 'Analyzing mint authority status...', 'solanawp' ); ?></div>
                        </div>

                        <div class="risk-metric-card" id="freezeAuthorityContainer">
                            <div class="metric-info">
                                <span class="metric-icon" id="freezeAuthorityIcon">🧊</span>
                                <div class="metric-title"><?php esc_html_e( 'Freeze Authority', 'solanawp' ); ?></div>
                                <div class="metric-status" id="freezeAuthorityStatus"><?php esc_html_e( 'Checking...', 'solanawp' ); ?></div>
                            </div>
                            <div class="metric-description" id="freezeAuthorityExplanation"><?php esc_html_e( 'Analyzing freeze authority status...', 'solanawp' ); ?></div>
                        </div>

                        <div class="risk-metric-card" id="liquidityContainer">
                            <div class="metric-info">
                                <span class="metric-icon" id="liquidityIcon">💧</span>
                                <div class="metric-title"><?php esc_html_e( 'Liquidity Lock', 'solanawp' ); ?></div>
                                <div class="metric-status" id="liquidityStatus"><?php esc_html_e( 'Checking...', 'solanawp' ); ?></div>
                            </div>
                            <div class="metric-percentage" id="liquidityPercentage"></div>
                            <div class="metric-description" id="liquidityExplanation"><?php esc_html_e( 'Analyzing liquidity lock status...', 'solanawp' ); ?></div>
                        </div>
                    </div>

                    </div>
                <!-- Lockers & Vesting Section -->
                <div class="lockers-section section-card">
                    <div class="section-header">
                        <span style="font-size: 1.5rem;">🔐</span>
                        <h3 class="section-title"><?php esc_html_e( 'Lockers & Vesting', 'solanawp' ); ?></h3>
                    </div>
                    <div class="lockers-container" id="lockersContainer">
                        <div class="loading-placeholder"><?php esc_html_e( 'Loading locker information...', 'solanawp' ); ?></div>
                    </div>
                </div>

            </div>

            <!-- RIGHT COLUMN: Risks + Risk Indicators + Creator History + Insider Networks + Lockers -->
            <div class="right-column">

                <!-- NEW: Dedicated Risks Section from RugCheck API -->
<!--                <div class="risks-section section-card">-->
<!--                    <div class="section-header">-->
<!--                        <span style="font-size: 1.5rem;">🚨</span>-->
<!--                        <h3 class="section-title">--><?php //esc_html_e( 'Risks from RugCheck Analysis', 'solanawp' ); ?><!--</h3>-->
<!--                    </div>-->
<!--                    <div class="risks-container" id="rugCheckRisksContainer">-->
<!--                        <div class="loading-placeholder">--><?php //esc_html_e( 'Analyzing risks...', 'solanawp' ); ?><!--</div>-->
<!--                    </div>-->
<!--                </div>-->

                <div class="risk-indicators-section section-card">
                    <div class="section-header">
                        <span style="font-size: 1.5rem;">⚠️</span>
                        <h3 class="section-title"><?php esc_html_e( 'Risk Indicators', 'solanawp' ); ?></h3>
                    </div>
                    <div class="risk-indicators-container" id="keyRiskIndicators">
                        <div class="loading-placeholder"><?php esc_html_e( 'Analyzing risk factors...', 'solanawp' ); ?></div>
                    </div>
                </div>
                <!-- Insider Networks Section -->
                <div class="insider-networks-section section-card">
                    <div class="section-header">
                        <span style="font-size: 1.5rem;">🕸️</span>
                        <h3 class="section-title"><?php esc_html_e( 'Insider Networks Analysis', 'solanawp' ); ?></h3>
                    </div>
                    <div class="insider-status">
                        <strong><?php esc_html_e( '  Insiders Detected:', 'solanawp' ); ?></strong>
                        <span id="insidersDetectedStatus"><?php esc_html_e( 'Analyzing...', 'solanawp' ); ?></span>
                    </div>
                    <div class="insider-networks-container" id="insiderNetworksContainer">
                        <div class="loading-placeholder"><?php esc_html_e( 'Analyzing insider networks...', 'solanawp' ); ?></div>
                    </div>
                </div>
                <!-- Creator History Section -->
                <div class="creator-history-section section-card">
                    <div class="section-header">
                        <span style="font-size: 1.5rem;">👤</span>
                        <h3 class="section-title"><?php esc_html_e( 'Creator Token History', 'solanawp' ); ?></h3>
                    </div>
                    <div class="creator-history-container" id="creatorTokensContainer">
                        <div class="loading-placeholder"><?php esc_html_e( 'Loading creator history...', 'solanawp' ); ?></div>
                    </div>
                </div>





            </div>

        </div>

    </div>
</div>
