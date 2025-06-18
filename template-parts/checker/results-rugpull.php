<?php
/**
 * Template part for displaying the ENHANCED "Rug Pull Risk Analysis" card - DASHBOARD LAYOUT
 * UPDATED: Enhanced with icons, better status handling, and animated loading states
 * NEW UPDATES: Rugged/Mutable color logic, Risk Score improvements, Insider Networks enhancements
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
        <span style="font-size: 2rem;">⚠️</span>
        <h1 class="card-title"><?php esc_html_e( '🔍 Rug Pull Risk Analysis', 'solanawp' ); ?></h1>
    </div>
    <div class="card-content">

        <!-- Main Risk Metrics Grid -->
        <div class="rug-metrics-grid">
            <div class="rug-metric-card">
                <div class="rug-metric-label"><?php esc_html_e( 'Rug Risk Score', 'solanawp' ); ?></div>
                <div class="rug-metric-value risk-high" id="rugOverallScore">-</div>
                <div class="rug-metric-sublabel"><?php esc_html_e( '(0-100)', 'solanawp' ); ?></div>
            </div>

            <div class="rug-metric-card">
                <div class="rug-metric-label"><?php esc_html_e( 'Rugged Status', 'solanawp' ); ?></div>
                <div class="rug-metric-value" id="ruggedStatus">-</div>
                <div class="rug-metric-sublabel" id="ruggedDate"></div>
            </div>

            <div class="rug-metric-card">
                <div class="rug-metric-label"><?php esc_html_e( 'Mutable', 'solanawp' ); ?></div>
                <div class="rug-metric-value" id="mutableStatus">-</div>
            </div>

            <div class="rug-metric-card">
                <div class="rug-metric-label"><?php esc_html_e( 'Risks Score', 'solanawp' ); ?></div>
                <div class="rug-metric-value" id="risksScore">-</div>
                <hr style="margin: 10px 0; border: 1px solid #e5e5e5;">
                <div class="rug-metric-label"><?php esc_html_e( 'Risk Level', 'solanawp' ); ?></div>
                <div class="rug-metric-value" style="font-size: 1.5rem;" id="rugRiskLevel">-</div>
            </div>
        </div>

        <!-- Explanations -->
        <div class="rug-explanations-grid">
            <div class="rug-explanation-box rug-explanation-blue">
                <?php esc_html_e( 'A score from 0 (low risk) to 100 (high risk) based on multiple factors.', 'solanawp' ); ?>
            </div>
            <div class="rug-explanation-box rug-explanation-red">
                <strong><?php esc_html_e( 'Warning:', 'solanawp' ); ?></strong> <?php esc_html_e( 'A "Yes" indicates the project has been identified as a rug pull.', 'solanawp' ); ?>
            </div>
            <div class="rug-explanation-box rug-explanation-yellow">
                <strong><?php esc_html_e( 'Warning:', 'solanawp' ); ?></strong> <?php esc_html_e( '"Yes" means metadata can be changed, potentially misleading investors.', 'solanawp' ); ?>
            </div>
            <div class="rug-explanation-box rug-explanation-blue">
                <?php esc_html_e( 'Provides a quick, at-a-glance summary of the overall risk score.', 'solanawp' ); ?>
            </div>
        </div>

        <!-- Token Distribution Analysis -->
        <h2 class="rug-section-title">📊 <?php esc_html_e( 'Token Distribution Analysis', 'solanawp' ); ?></h2>

        <div class="rug-distribution-grid">
            <!-- Total Holders (RugCheck API) -->
            <div class="rug-distribution-card">
                <div class="rug-distribution-value" id="totalHoldersCount">-</div>
                <div class="rug-distribution-label"><?php esc_html_e( 'Total Holders', 'solanawp' ); ?></div>
            </div>

            <!-- NEW: Top Holders (RugCheck API) -->
            <div class="rug-distribution-card">
                <div class="rug-distribution-value" id="topHoldersCount">-</div>
                <div class="rug-distribution-label"><?php esc_html_e( 'Top Holders', 'solanawp' ); ?></div>
            </div>

            <!-- Top 1 Holder (Alchemy API - Keep existing) -->
            <div class="rug-distribution-card">
                <div class="rug-distribution-value" id="concentrationTop1">-</div>
                <div class="rug-distribution-label"><?php esc_html_e( 'Top 1 Holder', 'solanawp' ); ?></div>
            </div>

            <!-- Top 5 Holders (Alchemy API - Keep existing) -->
            <div class="rug-distribution-card">
                <div class="rug-distribution-value" id="concentrationTop5">-</div>
                <div class="rug-distribution-label"><?php esc_html_e( 'Top 5 Holders', 'solanawp' ); ?></div>
            </div>

            <!-- Top 20 Holders (Alchemy API - Keep existing) -->
            <div class="rug-distribution-card">
                <div class="rug-distribution-value" id="concentrationTop20">-</div>
                <div class="rug-distribution-label"><?php esc_html_e( 'Top 20 Holders', 'solanawp' ); ?></div>
            </div>
        </div>

        <!-- Holder Distribution (Alchemy API - Keep existing) -->
        <div class="rug-content-card">
            <h3 class="rug-card-title">👥 <?php esc_html_e( 'Holder Distribution', 'solanawp' ); ?></h3>
            <div id="rugTokenDistribution">
                <div class="loading-placeholder"><?php esc_html_e( 'Loading holder distribution...', 'solanawp' ); ?></div>
            </div>

            <!-- Risk Assessment (Dynamic) -->
            <div id="distributionRiskContainer">
                <div class="rug-risk-warning">
                    <h4 id="distributionRiskLevel"><?php esc_html_e( 'Distribution Risk: Analyzing...', 'solanawp' ); ?></h4>
                    <p id="distributionRiskExplanation"><?php esc_html_e( 'Analyzing token distribution for concentration risks...', 'solanawp' ); ?></p>
                </div>
            </div>
        </div>

        <!-- Creator's Other Tokens -->
        <div class="rug-content-card">
            <h3 class="rug-card-title">👤 <?php esc_html_e( "Creator's Other Tokens", 'solanawp' ); ?></h3>
            <p style="color: #666; margin-bottom: 20px; font-size: 0.9rem;">
                <?php esc_html_e( 'This shows other tokens created by the same wallet. A history of abandoned or rugged projects is a major red flag.', 'solanawp' ); ?>
            </p>

            <div id="creatorTokensContainer">
                <div class="loading-placeholder"><?php esc_html_e( 'Analyzing creator history...', 'solanawp' ); ?></div>
            </div>
        </div>

        <!-- Two Column Section -->
        <div class="rug-two-column">
            <!-- Lockers & Vesting -->
            <div class="rug-content-card">
                <h3 class="rug-card-title">🔒 <?php esc_html_e( 'Lockers & Vesting', 'solanawp' ); ?></h3>

                <div id="lockersContainer">
                    <div class="loading-placeholder animated-loading"><?php esc_html_e( 'Loading locker information...', 'solanawp' ); ?></div>
                </div>

                <div class="rug-info-box">
                    <h5><?php esc_html_e( 'What it means:', 'solanawp' ); ?></h5>
                    <p><?php esc_html_e( 'Token lockers and vesting schedules lock up tokens for a set time to prevent immediate selling after launch.', 'solanawp' ); ?></p>
                    <h5 style="color: #d97706;"><?php esc_html_e( 'Warning:', 'solanawp' ); ?></h5>
                    <p style="color: #92400e;"><?php esc_html_e( 'Pay attention to unlock dates. Short lock-up periods can still pose a risk, as large token amounts could be sold when the lock expires.', 'solanawp' ); ?></p>
                </div>
            </div>

            <!-- Insiders Networks -->
            <div class="rug-content-card">
                <h3 class="rug-card-title">🕵️ <?php esc_html_e( 'Insiders Networks', 'solanawp' ); ?></h3>
                <p><strong><?php esc_html_e( 'Insiders Detected:', 'solanawp' ); ?></strong> <span id="insidersDetectedStatus">-</span></p>

                <div id="insiderNetworksContainer">
                    <div class="loading-placeholder animated-loading"><?php esc_html_e( 'Analyzing insider networks...', 'solanawp' ); ?></div>
                </div>

                <div class="rug-warning-box">
                    <h5><?php esc_html_e( 'What it means:', 'solanawp' ); ?></h5>
                    <p><?php esc_html_e( 'This analysis identifies wallets linked to the token deployer. These may belong to the dev team or related individuals.', 'solanawp' ); ?></p>
                    <h5 style="color: #dc2626;"><?php esc_html_e( 'DANGER:', 'solanawp' ); ?></h5>
                    <p style="color: #7f1d1d;"><?php esc_html_e( 'A high number of insider wallets holding a significant supply is a major red flag and increases the risk of a coordinated \'dump\'.', 'solanawp' ); ?></p>
                </div>
            </div>
        </div>

        <!-- Liquidity & Authorities Analysis -->
        <div class="rug-content-card">
            <h3 class="rug-card-title">💧 <?php esc_html_e( 'Liquidity & Authorities Analysis', 'solanawp' ); ?></h3>

            <div style="margin-bottom: 20px;">
                <p><strong><?php esc_html_e( 'Liquidity Status:', 'solanawp' ); ?></strong> <span class="rug-status-neutral" id="liquidityStatus">-</span></p>
                <p><strong><?php esc_html_e( 'Ownership Status:', 'solanawp' ); ?></strong> <span class="rug-status-locked" id="ownershipStatus">-</span></p>
                <p><strong><?php esc_html_e( 'Total Liquidity Providers:', 'solanawp' ); ?></strong> <span style="color: #7c3aed;" id="totalLiquidityProviders">-</span></p>
            </div>

            <h4 style="margin-bottom: 15px; color: #333;">🔐 <?php esc_html_e( 'Authority Analysis', 'solanawp' ); ?></h4>

            <div class="rug-authority-item">
                <span class="rug-authority-label"><?php esc_html_e( 'Mint Authority:', 'solanawp' ); ?></span>
                <span class="rug-status-neutral" id="mintAuthorityStatus">-</span>
            </div>

            <div class="rug-authority-item">
                <span class="rug-authority-label"><?php esc_html_e( 'Freeze Authority:', 'solanawp' ); ?></span>
                <span class="rug-status-neutral" id="freezeAuthorityStatus">-</span>
            </div>

            <div style="margin-top: 25px; border-top: 1px solid #e5e5e5; padding-top: 20px;">
                <h4 style="margin-bottom: 15px; color: #333;">📋 <?php esc_html_e( 'Key Indicators', 'solanawp' ); ?></h4>

                <ul class="rug-indicators-list">
                    <li class="rug-indicator-positive">
                        <span class="rug-indicator-icon">✅</span>
                        <div class="rug-indicator-text">
                            <div class="rug-indicator-title"><?php esc_html_e( 'Liquidity Locked:', 'solanawp' ); ?></div>
                            <div class="rug-indicator-description"><?php esc_html_e( 'A high percentage of liquidity is locked, which is a strong positive signal against a rug pull.', 'solanawp' ); ?></div>
                        </div>
                    </li>

                    <li class="rug-indicator-positive">
                        <span class="rug-indicator-icon">✅</span>
                        <div class="rug-indicator-text">
                            <div class="rug-indicator-title"><?php esc_html_e( 'Mint Authority Renounced:', 'solanawp' ); ?></div>
                            <div class="rug-indicator-description"><?php esc_html_e( 'No new tokens can be minted, preventing supply inflation and developer manipulation.', 'solanawp' ); ?></div>
                        </div>
                    </li>

                    <li class="rug-indicator-positive">
                        <span class="rug-indicator-icon">✅</span>
                        <div class="rug-indicator-text">
                            <div class="rug-indicator-title"><?php esc_html_e( 'Freeze Authority Renounced:', 'solanawp' ); ?></div>
                            <div class="rug-indicator-description"><?php esc_html_e( 'The creator cannot freeze holder accounts, ensuring tokens remain transferable.', 'solanawp' ); ?></div>
                        </div>
                    </li>

                    <li class="rug-indicator-negative">
                        <span class="rug-indicator-icon">⚠️</span>
                        <div class="rug-indicator-text">
                            <div class="rug-indicator-title"><?php esc_html_e( 'Liquidity Unlocked:', 'solanawp' ); ?></div>
                            <div class="rug-indicator-description"><?php esc_html_e( 'Devs can remove most of the liquidity at any time. This is a critical risk factor.', 'solanawp' ); ?></div>
                        </div>
                    </li>

                    <li class="rug-indicator-negative">
                        <span class="rug-indicator-icon">⚠️</span>
                        <div class="rug-indicator-text">
                            <div class="rug-indicator-title"><?php esc_html_e( 'Mint Authority Active (Un-Renounced):', 'solanawp' ); ?></div>
                            <div class="rug-indicator-description"><?php esc_html_e( 'The creator can mint new tokens at will, which can devalue the token and is a major red flag.', 'solanawp' ); ?></div>
                        </div>
                    </li>

                    <li class="rug-indicator-negative">
                        <span class="rug-indicator-icon">⚠️</span>
                        <div class="rug-indicator-text">
                            <div class="rug-indicator-title"><?php esc_html_e( 'Freeze Authority Active (Un-Renounced):', 'solanawp' ); ?></div>
                            <div class="rug-indicator-description"><?php esc_html_e( 'The creator can freeze individual wallets, preventing holders from selling their tokens.', 'solanawp' ); ?></div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Key Risk Indicators Section -->
        <div class="rug-content-card">
            <h3 class="rug-card-title">⚠️ <?php esc_html_e( 'Key Risk Indicators', 'solanawp' ); ?></h3>
            <div id="keyRiskIndicators">
                <div class="loading-placeholder"><?php esc_html_e( 'Analyzing risk factors...', 'solanawp' ); ?></div>
            </div>
        </div>

    </div>
</div>
